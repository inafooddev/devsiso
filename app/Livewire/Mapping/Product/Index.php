<?php

namespace App\Livewire\Mapping\Product;

use Livewire\Component;
use App\Models\ProductMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Exports\ProductMappingsExport;
use App\Imports\ProductMappingsImport; 
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Properti untuk filter
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $search = '';

    // Properti untuk data dropdown
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // State
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;
    public $isDeleteModalOpen = false;
    public $mappingIdToDelete;
    
    // Properti untuk modal impor
    public $isImportModalOpen = false;
    public $file;

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
        // 1. Terapkan akses region ke dropdown awal
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national'
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
        }

        // 🔥 AMBIL SESSION FILTER
        if (session()->has('salesman_filters')) {
            $filters = session()->get('salesman_filters');

            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? null;
            $this->distributorFilter = $filters['distributorFilter'] ?? null;
            $this->search = $filters['search'] ?? '';
            $this->hasAppliedFilters = $filters['hasAppliedFilters'] ?? false;

            // 🔥 load ulang dropdown dependen dengan proteksi akses
            if ($this->regionFilter) {
                $areaQuery = MasterArea::where('region_code', $this->regionFilter);
                $this->applyRegionAccess($areaQuery);
                $this->areas = $areaQuery->orderBy('area_name')->get();
            }

            if ($this->areaFilter) {
                $distQuery = MasterDistributor::where('area_code', $this->areaFilter);
                $this->applyRegionAccess($distQuery);
                $this->distributors = $distQuery->orderBy('is_active','desc')->get();
            }
        } elseif ($this->regionFilter) {
            // Jika tidak ada session tapi ada auto-select region
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

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

        session()->forget('salesman_filters');

        // Auto-select ulang setelah reset jika user biasa
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    public function render()
    {
        $mappings = collect(); 

        if ($this->hasAppliedFilters) {
            $query = ProductMapping::query()
                ->with('masterDistributor')
                ->join('master_distributors', 'product_mappings.distributor_code', '=', 'master_distributors.distributor_code')
                ->leftJoin('product_masters', 'product_mappings.product_code_prc', '=', 'product_masters.product_id');

            // --- PROTEKSI KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'master_distributors.region_code');

            if ($this->regionFilter) {
                $query->where('master_distributors.region_code', $this->regionFilter);
            }
            if ($this->areaFilter) {
                $query->where('master_distributors.area_code', $this->areaFilter);
            }
            if ($this->distributorFilter) {
                $query->where('product_mappings.distributor_code', $this->distributorFilter);
            }

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('product_mappings.product_code_dist', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('product_mappings.product_name_dist', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('product_mappings.product_code_prc', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('product_masters.product_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('product_mappings.distributor_code', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.distributor_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.distributor_code', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.branch_name', 'ILIKE', '%' . $this->search . '%');
                });
            }

            $mappings = $query->select(
                                'product_mappings.*', 
                                'product_masters.product_name as product_name_prc' 
                               )
                               ->latest('product_mappings.created_at')
                               ->paginate(10);
        }

        return view('livewire.mapping.product.index', [
            'mappings' => $mappings,
        ])->layout('layouts.app');
    }

    public function export()
    {
        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu sebelum mengekspor data.');
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
            // Tambahkan parameter khusus untuk proteksi Array Scope
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [],
        ];

        return Excel::download(new ProductMappingsExport($filters), 'product_mappings.xlsx');
    }

    // Metode untuk memproses impor
    public function import()
    {
        // Fitur import biasanya krusial, sangat disarankan dibatasi minimal per region
        // Namun karena bergantung pada class Importer Anda, kita amankan saja dengan validasi file
        $this->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            $importer = new ProductMappingsImport;
            Excel::import($importer, $this->file);
            
            session()->flash('message', "Impor berhasil: {$importer->importedCount} data diproses, {$importer->skippedCount} data dilewati.");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            session()->flash('error', 'Impor gagal. Periksa kembali format file Anda. Error: ' . $failures[0]->errors()[0]);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->reset('file');
        
        // Muat ulang data jika filter sudah diterapkan
        if($this->hasAppliedFilters) {
            $this->applyFilters();
        }
    }

    public function confirmDelete($mappingId)
    {
        $this->mappingIdToDelete = $mappingId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $mapping = ProductMapping::find($this->mappingIdToDelete);

        if ($mapping) {
            // Security Check: Pastikan data yang dihapus masih di bawah otoritas region user
            if (!$this->checkDistributorAccess($mapping->distributor_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus data di wilayah ini.');
                $this->isDeleteModalOpen = false;
                return;
            }

            $mapping->delete();
            session()->flash('message', 'Pemetaan Produk berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
    }

    protected function saveFiltersToSession()
    {
        // Menggunakan session yang sama untuk menyimpan filter mapping product
        session()->put('salesman_filters', [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            'hasAppliedFilters' => $this->hasAppliedFilters,
        ]);
    }

    public function updatedSearch()
    {
        if ($this->hasAppliedFilters) {
            $this->resetPage();
            $this->saveFiltersToSession();
        }
    }
}