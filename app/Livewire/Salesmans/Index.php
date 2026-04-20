<?php

namespace App\Livewire\Salesmans;

use Livewire\Component;
use App\Models\Salesman;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Exports\SalesmansExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Properti Filter
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $search = '';

    // Properti Dropdown Data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // State Modals
    public $isFilterModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isEditModalOpen = false;
    public $hasAppliedFilters = false;

    // Properti Form Edit
    public $originalDistributorCode;
    public $originalSalesmanCode;
    public $distributor_code;
    public $salesman_code;
    public $salesman_name;
    public $is_active;

    // Properti Delete
    public $salesmanCodeToDelete;
    public $distributorCodeToDelete;

    protected $queryString = ['search'];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }

        return $query;
    }

    /**
     * Helper untuk memastikan distributor terkait berada di dalam wilayah user
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // 1. Filter dropdown region saat load
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national'
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
        }

        if (session()->has('salesman_filters')) {
            $filters = session('salesman_filters');
            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? null;
            $this->distributorFilter = $filters['distributorFilter'] ?? null;
            $this->search = $filters['search'] ?? '';
            $this->hasAppliedFilters = $filters['hasAppliedFilters'] ?? false;

            if ($this->regionFilter) {
                $areaQuery = MasterArea::where('region_code', $this->regionFilter);
                $this->applyRegionAccess($areaQuery); // Amankan area
                $this->areas = $areaQuery->orderBy('area_name')->get();
            }
            if ($this->areaFilter) {
                $distQuery = MasterDistributor::where('area_code', $this->areaFilter);
                $this->applyRegionAccess($distQuery); // Amankan distributor
                $this->distributors = $distQuery->orderBy('is_active', 'desc')->get();
            }
        } elseif ($this->regionFilter) {
            // Jika tidak ada session tapi ada auto-select region
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    /**
     * Logika Edit: Memuat data salesman ke dalam modal
     */
    public function edit($distributorCode, $salesmanCode)
    {
        // Security Check: Pastikan salesman berada di distributor yang boleh diakses user
        if (!$this->checkDistributorAccess($distributorCode)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk mengedit data ini.');
            return;
        }

        $salesman = Salesman::where('distributor_code', $distributorCode)
            ->where('salesman_code', $salesmanCode)
            ->first();

        if (!$salesman) {
            session()->flash('error', 'Data salesman tidak ditemukan.');
            return;
        }

        // Simpan kunci asli untuk query update nanti
        $this->originalDistributorCode = $salesman->distributor_code;
        $this->originalSalesmanCode = $salesman->salesman_code;

        // Set value form
        $this->distributor_code = $salesman->distributor_code;
        $this->salesman_code = $salesman->salesman_code;
        $this->salesman_name = $salesman->salesman_name;
        $this->is_active = $salesman->is_active;

        $this->isEditModalOpen = true;
    }

    /**
     * Logika Update: Menggunakan kombinasi distributor & salesman code
     */
    public function update()
    {
        $this->validate([
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'salesman_code' => [
                'required', 'string', 'max:15',
                Rule::unique('salesmans')
                    ->where('distributor_code', $this->distributor_code)
                    ->ignore($this->originalSalesmanCode, 'salesman_code') // Abaikan record yang sedang diedit
            ],
            'salesman_name' => 'required|string|max:150',
            'is_active' => 'required|boolean',
        ]);

        // Security Check: Pastikan user tidak merubah kode distributor ke wilayah lain
        if (!$this->checkDistributorAccess($this->distributor_code) || !$this->checkDistributorAccess($this->originalDistributorCode)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk memindahkan atau mengubah data di distributor ini.');
            return;
        }

        DB::beginTransaction();
        try {
            Salesman::where('distributor_code', $this->originalDistributorCode)
                ->where('salesman_code', $this->originalSalesmanCode)
                ->update([
                    'distributor_code' => $this->distributor_code,
                    'salesman_code'    => $this->salesman_code,
                    'salesman_name'    => $this->salesman_name,
                    'is_active'        => $this->is_active,
                    'updated_at'       => now(),
                ]);

            DB::commit();
            $this->isEditModalOpen = false;
            session()->flash('message', 'Salesman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // --- Helper Filter ---
    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        
        $query = MasterArea::query();
        if ($value) {
            $query->where('region_code', $value);
        }
        $this->applyRegionAccess($query); // Security Scope

        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        
        $query = MasterDistributor::query();
        if ($value) {
            $query->where('area_code', $value);
        }
        $this->applyRegionAccess($query); // Security Scope

        $this->distributors = $value ? $query->orderBy('is_active','desc')->get() : collect();
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->hasAppliedFilters = true;
        $this->isFilterModalOpen = false;
        $this->saveFiltersToSession();
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'search']);
        $this->areas = collect();
        $this->distributors = collect();
        $this->hasAppliedFilters = false;
        session()->forget('salesman_filters');

        // Auto-select ulang setelah reset jika user biasa
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('salesman_filters', [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            'hasAppliedFilters' => $this->hasAppliedFilters,
        ]);
    }

    public function render()
    {
        $salesmans = collect();

        if ($this->hasAppliedFilters) {
            $query = Salesman::query()
                ->with('masterDistributor')
                ->join('master_distributors', 'salesmans.distributor_code', '=', 'master_distributors.distributor_code');
            
            // Terapkan keamanan scope wilayah
            $this->applyRegionAccess($query, 'master_distributors.region_code');
            
            if ($this->regionFilter) $query->where('master_distributors.region_code', $this->regionFilter);
            if ($this->areaFilter) $query->where('master_distributors.area_code', $this->areaFilter);
            if ($this->distributorFilter) $query->where('salesmans.distributor_code', $this->distributorFilter);
            
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('salesmans.salesman_code', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('salesmans.salesman_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.distributor_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.distributor_code', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.branch_name', 'ILIKE', '%' . $this->search . '%');
                });
            }

            $salesmans = $query->select('salesmans.*')->latest('salesmans.created_at')->paginate(10);
        }

        return view('livewire.salesmans.index', ['salesmans' => $salesmans])->layout('layouts.app');
    }

    public function confirmDelete($salesmanCode, $distributorCode)
    {
        $this->salesmanCodeToDelete = $salesmanCode;
        $this->distributorCodeToDelete = $distributorCode;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        // Security Check: Pastikan hanya bisa hapus di distributor miliknya
        if (!$this->checkDistributorAccess($this->distributorCodeToDelete)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus data ini.');
            $this->isDeleteModalOpen = false;
            return;
        }

        Salesman::where('salesman_code', $this->salesmanCodeToDelete)
            ->where('distributor_code', $this->distributorCodeToDelete)
            ->delete();

        $this->reset(['salesmanCodeToDelete', 'distributorCodeToDelete', 'isDeleteModalOpen']);
        session()->flash('message', 'Salesman berhasil dihapus.');
    }

    public function export()
    {
        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu sebelum mengekspor data.');
             return;
        }

        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

        // Validasi ekstra untuk Export
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!empty($finalRegionFilter) && !in_array($finalRegionFilter, $user->region_code)) {
                $finalRegionFilter = ''; 
            }
        }

        $filters = [
            'regionFilter' => $finalRegionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            // Tambahkan parameter allowed_regions untuk class Export Excel-nya
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [], 
        ];
        
        return Excel::download(new SalesmansExport($filters), 'salesmans.xlsx');
    }
}