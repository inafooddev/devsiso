<?php

namespace App\Livewire\Mapping\Product;

use Livewire\Component;
use App\Models\ProductMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ProductMaster;
use App\Exports\ProductMappingsExport;
use App\Imports\ProductMappingsImport; 
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'product-mappings.index';

    // Properti untuk filter (List)
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $search = '';

    // Properti untuk data dropdown (List)
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // State Modals
    public $isFilterModalOpen = false;
    public $isFormModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $hasAppliedFilters = false;

    // Form Properties (Create/Edit)
    public $isEditing = false;
    public $editingId = null;
    public $formRegionFilter;
    public $formAreaFilter;
    public $distributor_code;
    public $product_code_dist;
    public $product_name_dist;
    public $product_code_prc;
    public $productSearch = '';
    public $selectedProductName = '';

    // Data Dropdown (Form)
    public $formRegions = [];
    public $formAreas = [];
    public $formDistributors = [];
    public $principalProducts = [];

    public $mappingIdToDelete;
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
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA');
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();
        $this->formRegions = $this->regions;

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->formRegionFilter = $this->regions->first()->region_code;
            $this->updatedFormRegionFilter($this->formRegionFilter);
        }

        // 🔥 AMBIL SESSION FILTER
        if (session()->has('product_mapping_filters')) {
            $filters = session()->get('product_mapping_filters');

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
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    // --- LOGIC FILTER (LIST) ---
    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        $query = MasterArea::query();
        if ($value) $query->where('region_code', $value);
        $this->applyRegionAccess($query);
        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        $query = MasterDistributor::query();
        if ($value) $query->where('area_code', $value);
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
        session()->forget('product_mapping_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    // --- LOGIC FORM (CREATE/EDIT) ---
    public function updatedFormRegionFilter($value)
    {
        $this->reset(['formAreaFilter', 'distributor_code']);
        $this->loadFormAreas($value);
    }

    private function loadFormAreas($regionCode)
    {
        $query = MasterArea::query();
        if ($regionCode) $query->where('region_code', $regionCode);
        $this->applyRegionAccess($query);
        $this->formAreas = $regionCode ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedFormAreaFilter($value)
    {
        $this->reset('distributor_code');
        $this->loadFormDistributors($value);
    }

    private function loadFormDistributors($areaCode)
    {
        $query = MasterDistributor::query();
        if ($areaCode) $query->where('area_code', $areaCode);
        $this->applyRegionAccess($query);
        $this->formDistributors = $areaCode ? $query->orderBy('distributor_name')->get() : collect();
    }

    public function updatedProductSearch($value)
    {
        if(strlen($value) < 2) {
            $this->principalProducts = collect();
            return;
        }
        $this->principalProducts = ProductMaster::where('product_name', 'ILIKE', '%' . $value . '%')
                                    ->orWhere('product_id', 'ILIKE', '%' . $value . '%')
                                    ->orderBy('is_active', 'desc')
                                    ->limit(50)
                                    ->get();
    }

    public function selectProduct($productCode, $productName = null)
    {
        $this->product_code_prc = $productCode;
        if ($productName) {
            $this->selectedProductName = $productName;
        } elseif ($productCode) {
            $product = ProductMaster::find($productCode);
            $this->selectedProductName = $product ? $product->product_name : '';
        } else {
            $this->selectedProductName = '';
        }
        $this->productSearch = '';
        $this->principalProducts = collect();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($mappingId)
    {
        $this->resetValidation();
        $mapping = ProductMapping::with('masterDistributor.area.region')->findOrFail($mappingId);

        // Security Check
        if (!$this->checkDistributorAccess($mapping->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk mengedit data ini.');
            return;
        }

        $this->editingId = $mapping->id;
        $this->distributor_code = $mapping->distributor_code;
        $this->product_code_dist = $mapping->product_code_dist;
        $this->product_name_dist = $mapping->product_name_dist;
        $this->product_code_prc = $mapping->product_code_prc;
        
        if($this->product_code_prc) {
            $product = ProductMaster::find($this->product_code_prc);
            $this->selectedProductName = $product ? $product->product_name : '';
        } else {
            $this->selectedProductName = '';
        }

        // Fill dropdowns for edit without resetting distributor_code
        if ($mapping->masterDistributor && $mapping->masterDistributor->area) {
            $this->formRegionFilter = $mapping->masterDistributor->area->region_code;
            $this->loadFormAreas($this->formRegionFilter);
            $this->formAreaFilter = $mapping->masterDistributor->area_code;
            $this->loadFormDistributors($this->formAreaFilter);
        }

        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->distributor_code = null;
        $this->product_code_dist = null;
        $this->product_name_dist = null;
        $this->product_code_prc = null;
        $this->productSearch = '';
        $this->selectedProductName = '';
        $this->principalProducts = collect();
        
        // Don't reset region if it's auto-selected
        if (auth()->user()->hasRole('admin') || count($this->regions) > 1) {
            $this->formRegionFilter = null;
            $this->formAreaFilter = null;
            $this->formAreas = collect();
            $this->formDistributors = collect();
        }
    }

    protected function rules()
    {
        $uniqueRule = Rule::unique('product_mappings')->where(function ($query) {
            return $query->where('distributor_code', $this->distributor_code);
        });

        if ($this->isEditing) {
            $uniqueRule->ignore($this->editingId);
        }

        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'product_code_dist' => ['nullable', 'string', 'max:255', $uniqueRule],
            'product_name_dist' => 'nullable|string|max:255',
            'product_code_prc' => 'nullable|string|max:255|exists:product_masters,product_id',
        ];
    }

    protected $messages = [
        'product_code_dist.unique' => 'Kode Produk Distributor ini sudah dipetakan untuk distributor yang dipilih.',
    ];

    public function save()
    {
        $this->authorizeAction('can_edit');

        $validatedData = $this->validate();

        // Security Check
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk memetakan produk ke distributor tersebut.');
            return;
        }

        if ($this->isEditing) {
            $mapping = ProductMapping::findOrFail($this->editingId);
            $mapping->update($validatedData);
            \App\Helpers\ActivityLogger::log('Update Product Mapping', "Memperbarui mapping produk: {$validatedData['distributor_code']} - {$validatedData['product_code_dist']}");
            session()->flash('message', 'Pemetaan Produk berhasil diperbarui.');
        } else {
            ProductMapping::create($validatedData);
            \App\Helpers\ActivityLogger::log('Create Product Mapping', "Menambahkan mapping produk baru: {$validatedData['distributor_code']} - {$validatedData['product_code_dist']}");
            session()->flash('message', 'Pemetaan Produk berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
        
        // Refresh sidebar badges
        \Illuminate\Support\Facades\Cache::forget('unmapped_counts_' . auth()->id());
        
        if ($this->hasAppliedFilters) {
            $this->resetPage();
        }
    }

    // --- LOGIC OTHERS ---
    public function render()
    {
        $mappings = collect(); 

        if ($this->hasAppliedFilters) {
            $query = ProductMapping::query()
                ->with('masterDistributor')
                ->join('master_distributors', 'product_mappings.distributor_code', '=', 'master_distributors.distributor_code')
                ->leftJoin('product_masters', 'product_mappings.product_code_prc', '=', 'product_masters.product_id');

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
                      ->orWhere('master_distributors.distributor_name', 'ILIKE', '%' . $this->search . '%');
                });
            }

            $mappings = $query->select(
                                'product_mappings.*', 
                                'product_masters.product_name as product_name_prc' 
                               )
                               ->orderBy('master_distributors.distributor_name')
                               ->orderBy('product_mappings.product_name_dist')
                               ->paginate(100);
        }

        return view('livewire.mapping.product.index', [
            'mappings' => $mappings,
        ])->layout('layouts.app');
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu sebelum mengekspor data.');
             return;
        }

        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

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
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [],
        ];

        return Excel::download(new ProductMappingsExport($filters), 'product_mappings.xlsx');
    }

    public function import()
    {
        $this->authorizeAction('can_import');

        $this->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            $importer = new ProductMappingsImport;
            Excel::import($importer, $this->file);
            \App\Helpers\ActivityLogger::log('Import Product Mapping', "Mengimpor data pemetaan produk. Berhasil: {$importer->importedCount}, Dilewati: {$importer->skippedCount}");
            session()->flash('message', "Impor berhasil: {$importer->importedCount} data diproses, {$importer->skippedCount} data dilewati.");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            session()->flash('error', 'Impor gagal. Periksa format file Anda. Error: ' . $failures[0]->errors()[0]);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->reset('file');
        
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
        $this->authorizeAction('can_edit');

        $mapping = ProductMapping::find($this->mappingIdToDelete);

        if ($mapping) {
            if (!$this->checkDistributorAccess($mapping->distributor_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus data di wilayah ini.');
                $this->isDeleteModalOpen = false;
                return;
            }

            \App\Helpers\ActivityLogger::log('Delete Product Mapping', "Menghapus mapping produk: {$mapping->distributor_code} - {$mapping->product_code_dist}");
            $mapping->delete();
            session()->flash('message', 'Pemetaan Produk berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
    }

    protected function saveFiltersToSession()
    {
        session()->put('product_mapping_filters', [
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