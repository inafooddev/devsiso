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
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'salesman-mappings.index';

    // Properties for filter (List)
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $search = '';

    // Data dropdown (List)
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
    public $salesman_code_dist;
    public $salesman_name_dist;
    public $salesman_code_prc;
    public $salesmanSearch = '';
    public $selectedSalesmanName = '';

    // Data Dropdown (Form)
    public $formRegions = [];
    public $formAreas = [];
    public $formDistributors = [];
    public $principalSalesmans = [];

    public $mappingIdToDelete;
    public $file;

    protected $queryString = ['search'];

    /**
     * Helper to filter Query based on user region access.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }
        return $query;
    }

    /**
     * Helper to ensure distributor is within user's region
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // 1. Initial regions with access control
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA');
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();
        $this->formRegions = $this->regions;

        // 2. Auto-select if only 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->formRegionFilter = $this->regions->first()->region_code;
            $this->loadFormAreas($this->formRegionFilter);
        }

        // 3. Restore filters from session
        if (session()->has('salesman_mapping_filters')) {
            $filters = session()->get('salesman_mapping_filters');
            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? null;
            $this->distributorFilter = $filters['distributorFilter'] ?? null;
            $this->search = $filters['search'] ?? '';
            $this->hasAppliedFilters = $filters['hasAppliedFilters'] ?? false;

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
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    // --- FILTER LOGIC (LIST) ---
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
        session()->forget('salesman_mapping_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    // --- FORM LOGIC (CREATE/EDIT) ---
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

    public function updatedDistributorCode($value)
    {
        $this->reset('salesman_code_prc');
        $this->loadPrincipalSalesmans($value);
    }

    private function loadPrincipalSalesmans($distributorCode)
    {
        if (!$distributorCode) {
            $this->principalSalesmans = collect();
            return;
        }
        $this->principalSalesmans = Salesman::where('distributor_code', $distributorCode)
                                    ->orderBy('salesman_name')
                                    ->get();
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
        $mapping = SalesmanMapping::with('masterDistributor.area.region')->findOrFail($mappingId);

        if (!$this->checkDistributorAccess($mapping->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk mengedit data ini.');
            return;
        }

        $this->editingId = $mapping->id;
        $this->distributor_code = $mapping->distributor_code;
        $this->salesman_code_dist = $mapping->salesman_code_dist;
        $this->salesman_name_dist = $mapping->salesman_name_dist;
        $this->salesman_code_prc = $mapping->salesman_code_prc;
        
        if($this->salesman_code_prc) {
            $salesman = Salesman::where('salesman_code', $this->salesman_code_prc)->first();
            $this->selectedSalesmanName = $salesman ? $salesman->salesman_name : '';
        } else {
            $this->selectedSalesmanName = '';
        }

        if ($mapping->masterDistributor && $mapping->masterDistributor->area) {
            $this->formRegionFilter = $mapping->masterDistributor->area->region_code;
            $this->loadFormAreas($this->formRegionFilter);
            $this->formAreaFilter = $mapping->masterDistributor->area_code;
            $this->loadFormDistributors($this->formAreaFilter);
            
            // Load principal salesmans for the edit modal
            $this->loadPrincipalSalesmans($this->distributor_code);
        }

        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->distributor_code = null;
        $this->salesman_code_dist = null;
        $this->salesman_name_dist = null;
        $this->salesman_code_prc = null;
        $this->salesmanSearch = '';
        $this->selectedSalesmanName = '';
        $this->principalSalesmans = collect();
        
        if (auth()->user()->hasRole('admin') || count($this->regions) > 1) {
            $this->formRegionFilter = null;
            $this->formAreaFilter = null;
            $this->formAreas = collect();
            $this->formDistributors = collect();
        }
    }

    protected function rules()
    {
        $uniqueRule = Rule::unique('salesman_mappings')->where(function ($query) {
            return $query->where('distributor_code', $this->distributor_code);
        });

        if ($this->isEditing) {
            $uniqueRule->ignore($this->editingId);
        }

        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'salesman_code_dist' => ['required', 'string', 'max:255', $uniqueRule],
            'salesman_name_dist' => 'nullable|string|max:255',
            'salesman_code_prc' => 'nullable|string|max:15|exists:salesmans,salesman_code',
        ];
    }

    protected $messages = [
        'salesman_code_dist.unique' => 'Kode Salesman Distributor ini sudah dipetakan untuk distributor yang dipilih.',
    ];

    public function save()
    {
        $this->authorizeAction('can_edit');

        $validatedData = $this->validate();

        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk memetakan salesman ke distributor tersebut.');
            return;
        }

        if ($this->isEditing) {
            $mapping = SalesmanMapping::findOrFail($this->editingId);
            $mapping->update($validatedData);
            \App\Helpers\ActivityLogger::log('Update Salesman Mapping', "Memperbarui mapping salesman: {$validatedData['distributor_code']} - {$validatedData['salesman_code_dist']}");
            session()->flash('message', 'Pemetaan Salesman berhasil diperbarui.');
        } else {
            SalesmanMapping::create($validatedData);
            \App\Helpers\ActivityLogger::log('Create Salesman Mapping', "Menambahkan mapping salesman baru: {$validatedData['distributor_code']} - {$validatedData['salesman_code_dist']}");
            session()->flash('message', 'Pemetaan Salesman berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
        
        // Refresh sidebar badges
        \Illuminate\Support\Facades\Cache::forget('unmapped_counts_' . auth()->id());
        
        if ($this->hasAppliedFilters) {
            $this->resetPage();
        }
    }

    // --- OTHER LOGIC ---
    public function render()
    {
        $mappings = collect(); 

        if ($this->hasAppliedFilters) {
            $query = SalesmanMapping::query()
                ->with(['masterDistributor', 'principalSalesman'])
                ->join('master_distributors', 'salesman_mappings.distributor_code', '=', 'master_distributors.distributor_code');

            $this->applyRegionAccess($query, 'master_distributors.region_code');

            if ($this->regionFilter) $query->where('master_distributors.region_code', $this->regionFilter);
            if ($this->areaFilter) $query->where('master_distributors.area_code', $this->areaFilter);
            if ($this->distributorFilter) $query->where('salesman_mappings.distributor_code', $this->distributorFilter);

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('salesman_mappings.salesman_code_dist', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('salesman_mappings.salesman_name_dist', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('salesman_mappings.salesman_code_prc', 'ILIKE', '%' . $this->search . '%')
                      ->orWhereHas('principalSalesman', fn($sq) => $sq->where('salesman_name', 'ILIKE', '%' . $this->search . '%'))
                      ->orWhere('salesman_mappings.distributor_code', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('master_distributors.distributor_name', 'ILIKE', '%' . $this->search . '%');
                });
            }

            $mappings = $query->select('salesman_mappings.*')
                              ->orderBy('master_distributors.distributor_name')
                              ->orderBy('salesman_mappings.salesman_name_dist')
                              ->paginate(100);
        }

        return view('livewire.mapping.salesman.index', [
            'mappings' => $mappings,
        ])->layout('layouts.app');
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu.');
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

        return Excel::download(new SalesmanMappingsExport($filters), 'salesman_mappings.xlsx');
    }

    public function import()
    {
        $this->authorizeAction('can_import');

        $this->validate(['file' => 'required|mimes:xls,xlsx']);

        try {
            $importer = new SalesmanMappingsImport;
            Excel::import($importer, $this->file);
            \App\Helpers\ActivityLogger::log('Import Salesman Mapping', "Mengimpor data pemetaan salesman. Berhasil: {$importer->importedCount}, Dilewati: {$importer->skippedCount}");
            session()->flash('message', "Impor berhasil: {$importer->importedCount} data diproses, {$importer->skippedCount} data dilewati.");
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->reset('file');
        
        if($this->hasAppliedFilters) $this->applyFilters();
    }

    public function confirmDelete($mappingId)
    {
        $this->mappingIdToDelete = $mappingId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_edit');

        $mapping = SalesmanMapping::find($this->mappingIdToDelete);
        if ($mapping) {
            if (!$this->checkDistributorAccess($mapping->distributor_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus data di wilayah ini.');
                $this->isDeleteModalOpen = false;
                return;
            }
            \App\Helpers\ActivityLogger::log('Delete Salesman Mapping', "Menghapus mapping salesman: {$mapping->distributor_code} - {$mapping->salesman_code_dist}");
            $mapping->delete();
            session()->flash('message', 'Pemetaan Salesman berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
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

    public function updatedSearch()
    {
        if ($this->hasAppliedFilters) {
            $this->resetPage();
            $this->saveFiltersToSession();
        }
    }
}