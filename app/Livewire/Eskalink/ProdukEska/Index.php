<?php

namespace App\Livewire\Eskalink\ProdukEska;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\ProdukEskaExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'produk-eska.index';

    // Main Filters
    public $regionFilter = '';
    public $areaFilter = '';
    public $distributorFilter = '';
    
    // Export-specific Filters (Products)
    public $selectedProducts = []; 

    // UI states
    public $search = '';
    public $isFiltered = false;
    public $isFilterModalOpen = false;
    public $isExportModalOpen = false;

    // Dropdown Data
    public $regionsOption = [];
    public $areasOption = [];
    public $distributorsOption = [];
    public $productOptions = [];

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

    public function mount()
    {
        // 1. Initial regions with access control
        $query = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('region_code', '!=', 'HOINA')
            ->whereNotNull('region_code')
            ->distinct();

        $this->applyRegionAccess($query);
        $this->regionsOption = $query->orderBy('region_name')->get();

        // 2. Auto-select if only 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regionsOption) === 1) {
            $this->regionFilter = $this->regionsOption->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }

        // 3. Restore filters from session
        if (session()->has('produk_eska_filters')) {
            $filters = session()->get('produk_eska_filters');
            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? '';
            $this->distributorFilter = $filters['distributorFilter'] ?? '';
            $this->search = $filters['search'] ?? '';
            $this->isFiltered = $filters['isFiltered'] ?? false;

            if ($this->regionFilter) {
                $areaQuery = DB::table('master_distributors')
                    ->where('region_code', $this->regionFilter)
                    ->select('area_code', 'area_name')
                    ->distinct();
                $this->applyRegionAccess($areaQuery);
                $this->areasOption = $areaQuery->orderBy('area_name')->get();
            }

            if ($this->areaFilter) {
                $distQuery = DB::table('master_distributors')
                    ->where('region_code', $this->regionFilter)
                    ->where('area_code', $this->areaFilter)
                    ->select('distributor_code', 'distributor_name', 'is_active')
                    ->distinct();
                $this->applyRegionAccess($distQuery);
                $this->distributorsOption = $distQuery->orderBy('is_active', 'desc')->orderBy('distributor_name')->get();
            }

            if ($this->isFiltered) {
                $this->loadProducts();
            }
        }
    }

    // --- DEPENDENT DROPDOWN ---

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter', 'areasOption', 'distributorsOption', 'isFiltered', 'selectedProducts', 'productOptions', 'search']);
        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $value)
                ->select('area_code', 'area_name')
                ->distinct();
            $this->applyRegionAccess($query);
            $this->areasOption = $query->orderBy('area_name')->get();
        }
    }

    public function updatedAreaFilter($value)
    {
        $this->reset(['distributorFilter', 'distributorsOption', 'isFiltered', 'selectedProducts', 'productOptions', 'search']);
        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->regionFilter)
                ->where('area_code', $value)
                ->select('distributor_code', 'distributor_name', 'is_active')
                ->distinct();
            $this->applyRegionAccess($query);
            $this->distributorsOption = $query->orderBy('is_active', 'desc')->orderBy('distributor_name')->get();
        }
    }

    public function updatedDistributorFilter()
    {
        $this->isFiltered = false;
        $this->selectedProducts = [];
        $this->loadProducts();
    }

    public function loadProducts()
    {
        if ($this->distributorFilter) {
            $this->productOptions = DB::table('product_mappings')
                ->where('distributor_code', $this->distributorFilter)
                ->select('product_code_dist', 'product_name_dist')
                ->distinct()
                ->orderBy('product_code_dist')
                ->get();
        } else {
            $this->productOptions = [];
        }
    }

    public function selectAllProducts()
    {
        $this->selectedProducts = collect($this->productOptions)->pluck('product_code_dist')->toArray();
    }

    public function updatedSearch()
    {
        if ($this->isFiltered) {
            $this->resetPage();
            $this->saveFiltersToSession();
        }
    }

    // --- ACTIONS ---

    public function applyFilters()
    {
        $this->validate([
            'regionFilter' => 'required',
            'areaFilter' => 'required',
            'distributorFilter' => 'required',
        ]);

        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->regionFilter, $user->region_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk wilayah ini.');
                return;
            }
        }

        $this->isFiltered = true;
        $this->isFilterModalOpen = false;
        $this->resetPage();
        $this->loadProducts();
        $this->saveFiltersToSession();
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'search', 'isFiltered', 'selectedProducts', 'productOptions']);
        $this->areasOption = [];
        $this->distributorsOption = [];
        session()->forget('produk_eska_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regionsOption) === 1) {
            $this->regionFilter = $this->regionsOption->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('produk_eska_filters', [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            'isFiltered' => $this->isFiltered,
        ]);
    }

    public function openExportModal()
    {
        if (!$this->isFiltered) return;
        $this->isExportModalOpen = true;
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        $this->validate([
            'regionFilter' => 'required',
            'areaFilter' => 'required',
            'distributorFilter' => 'required',
        ]);

        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = 'Produk_Eska_' . $timestamp . '.xlsx';
        $this->isExportModalOpen = false;

        \App\Helpers\ActivityLogger::log('Export Product Eska', "Mengekspor data Product Eska untuk distributor: {$this->distributorFilter}");

        return Excel::download(
            new ProdukEskaExport(
                $this->regionFilter, 
                $this->areaFilter, 
                $this->distributorFilter,
                $this->selectedProducts
            ), 
            $filename
        );
    }

    public function render()
    {
        $data = collect();

        if ($this->isFiltered) {
            $query = DB::table('product_mappings as pm')
                ->select(
                    'die.eskalink_code_dist',
                    'pm.product_code_dist',
                    'pm.product_name_dist',
                    'pmm.uom1',
                    'pmm.uom2',
                    'pmm.uom3',
                    'pmm.conv_unit3',
                    'pmm.conv_unit2',
                    'pmm.price_zone1',
                    'pmm.conv_unit1'
                )
                ->leftJoin('distributor_implementasi_eskalink as die', 'pm.distributor_code', '=', 'die.distributor_code')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->leftJoin('product_masters as pmm', 'pm.product_code_prc', '=', 'pmm.product_id')
                ->where('md.region_code', $this->regionFilter)
                ->where('md.area_code', $this->areaFilter)
                ->where('md.distributor_code', $this->distributorFilter);

            $this->applyRegionAccess($query, 'md.region_code');

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('pm.product_name_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('pm.product_code_dist', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->orderBy('pm.product_code_dist')->paginate(100);
        }

        return view('livewire.eskalink.produk-eska.index', [
            'products' => $data
        ])->layout('layouts.app');
    }
}