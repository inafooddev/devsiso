<?php

namespace App\Livewire\Mapping\Salesman;

use Livewire\Component;
use App\Models\SalesmanMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\Salesman;
use App\Exports\SalesmanMappingsExport;
use App\Imports\SalesmanMappingsImport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Filter properties
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $search = '';

    // Data dropdown
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // Modal states
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $isEditModalOpen = false;
    
    public $mappingIdToDelete;
    public $file;

    // Properti Form Edit (Fokus pada mapping)
    public $selectedMappingId;
    public $distributor_name; // Hanya untuk tampilan
    public $salesman_code_dist; // Hanya untuk tampilan
    public $salesman_name_dist; // Hanya untuk tampilan
    public $distributor_code;
    public $salesman_code_prc;
    public $principalSalesmans = [];

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
        // 1. Terapkan akses region ke dropdown
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national'
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
        }

        // Restore filter dari session jika ada
        if (session()->has('salesman_mapping_filters')) {
            $filters = session('salesman_mapping_filters');
            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? null;
            $this->distributorFilter = $filters['distributorFilter'] ?? null;
            $this->search = $filters['search'] ?? '';
            $this->hasAppliedFilters = $filters['hasAppliedFilters'] ?? false;

            // Load ulang dropdown dependen dengan proteksi akses
            if ($this->regionFilter) {
                $areaQuery = MasterArea::where('region_code', $this->regionFilter);
                $this->applyRegionAccess($areaQuery);
                $this->areas = $areaQuery->orderBy('area_name')->get();
            }
            if ($this->areaFilter) {
                $distQuery = MasterDistributor::where('area_code', $this->areaFilter);
                $this->applyRegionAccess($distQuery);
                $this->distributors = $distQuery->orderBy('is_active', 'desc')->get();
            }
        } elseif ($this->regionFilter) {
            // Jika tidak ada session tapi ada auto-select region
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    // --- LOGIKA FILTER ---
    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        
        $query = MasterArea::query();
        if ($value) {
            $query->where('region_code', $value);
        }
        
        // Amankan dropdown area
        $this->applyRegionAccess($query);
        
        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        
        $query = MasterDistributor::query();
        if ($value) {
            $query->where('area_code', $value);
        }
        
        // Amankan dropdown distributor
        $this->applyRegionAccess($query);
        
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
        session()->forget('salesman_mapping_filters');

        // Auto-select ulang setelah reset jika user biasa
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('salesman_mapping_filters', [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            'hasAppliedFilters' => $this->hasAppliedFilters,
        ]);
    }

    // --- LOGIKA EDIT (FOKUS MAPPING) ---
    public function edit($id)
    {
        $mapping = SalesmanMapping::with('masterDistributor')->findOrFail($id);
        
        // Security Check: Pastikan mapping ini berada di distributor yang boleh diakses user
        if (!$this->checkDistributorAccess($mapping->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk mengedit data di wilayah ini.');
            return;
        }

        $this->selectedMappingId = $id;
        $this->distributor_code = $mapping->distributor_code;
        $this->distributor_name = $mapping->masterDistributor->distributor_name ?? 'N/A';
        $this->salesman_code_dist = $mapping->salesman_code_dist;
        $this->salesman_name_dist = $mapping->salesman_name_dist;
        $this->salesman_code_prc = $mapping->salesman_code_prc;

        // Load daftar salesman principal berdasarkan distributor tetap ini
        $this->loadPrincipalSalesmans();
        
        $this->isEditModalOpen = true;
    }

    public function loadPrincipalSalesmans()
    {
        $this->principalSalesmans = $this->distributor_code 
            ? Salesman::where('distributor_code', $this->distributor_code)->orderBy('salesman_name')->get() 
            : [];
    }

    public function update()
    {
        // Security Check Ekstra: Pastikan distributor_code yang sedang di-edit masih valid untuk user ini
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk memperbarui data ini.');
            $this->isEditModalOpen = false;
            return;
        }

        $this->validate([
            'salesman_code_prc' => 'nullable|exists:salesmans,salesman_code',
        ]);

        $mapping = SalesmanMapping::findOrFail($this->selectedMappingId);
        $mapping->update([
            'salesman_code_prc' => $this->salesman_code_prc,
        ]);

        $this->isEditModalOpen = false;
        session()->flash('message', 'Pemetaan Salesman Principal berhasil diperbarui.');
    }

    // --- CRUD LAINNYA ---
    public function confirmDelete($mappingId)
    {
        $this->mappingIdToDelete = $mappingId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $mapping = SalesmanMapping::find($this->mappingIdToDelete);

        if ($mapping) {
            // Security Check: Pastikan data yang dihapus masih di bawah otoritas region user
            if (!$this->checkDistributorAccess($mapping->distributor_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus data di wilayah ini.');
                $this->isDeleteModalOpen = false;
                return;
            }

            $mapping->delete();
            session()->flash('message', 'Pemetaan Salesman berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
    }

    public function render()
    {
        $mappings = collect();

        if ($this->hasAppliedFilters) {
            $query = SalesmanMapping::query()
                ->with(['masterDistributor', 'principalSalesman'])
                ->join('master_distributors', 'salesman_mappings.distributor_code', '=', 'master_distributors.distributor_code');
            
            // --- PROTEKSI KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'master_distributors.region_code');

            if ($this->regionFilter) $query->where('master_distributors.region_code', $this->regionFilter);
            if ($this->areaFilter) $query->where('master_distributors.area_code', $this->areaFilter);
            if ($this->distributorFilter) $query->where('salesman_mappings.distributor_code', $this->distributorFilter);
            
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('salesman_mappings.salesman_code_dist', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('salesman_mappings.salesman_name_dist', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('salesman_mappings.salesman_code_prc', 'ILIKE', '%' . $this->search . '%')
                      ->orWhereHas('principalSalesman', fn($sq) => $sq->where('salesman_name', 'ILIKE', '%' . $this->search . '%'));
                });
            }

            $mappings = $query->select('salesman_mappings.*')->latest('salesman_mappings.created_at')->paginate(10);
        }

        return view('livewire.mapping.salesman.index', [
            'mappings' => $mappings,
        ])->layout('layouts.app');
    }

    public function export()
    {
        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu.');
             return;
        }

        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

        // Validasi ekstra untuk Export (Mencegah manipulasi user biasa)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            // Jika user iseng menginjeksi region lain yang tidak dia miliki, reset ke kosong
            if (!empty($finalRegionFilter) && !in_array($finalRegionFilter, $user->region_code)) {
                $finalRegionFilter = ''; 
            }
        }

        $filters = [
            'regionFilter' => $finalRegionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            // Parameter khusus untuk proteksi Array Scope pada proses Excel Export
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [],
        ];

        return Excel::download(new \App\Exports\SalesmanMappingsExport($filters), 'salesman_mappings.xlsx');
    }
}