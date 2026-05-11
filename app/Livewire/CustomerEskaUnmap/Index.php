<?php

namespace App\Livewire\CustomerEskaUnmap;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerEskaUnmapExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filter properties (Multi-select support) apa ya 
    public $monthFilter;
    public $regionFilter = [];
    public $areaFilter = [];
    public $distributorFilter = [];
    
    // UI states
    public $search = '';
    public $isFiltered = false;
    public $isFilterModalOpen = false;

    // Dropdown options
    public $regionsOption = [];
    public $areasOption = [];
    public $distributorsOption = [];

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
        $this->monthFilter = date('Y-m');

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
            $this->regionFilter = [$this->regionsOption->first()->region_code];
            $this->updatedRegionFilter();
        }

        // 3. Restore filters from session
        if (session()->has('customer_eska_unmap_filters')) {
            $filters = session()->get('customer_eska_unmap_filters');
            $this->monthFilter = $filters['monthFilter'] ?? $this->monthFilter;
            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? [];
            $this->distributorFilter = $filters['distributorFilter'] ?? [];
            $this->search = $filters['search'] ?? '';
            $this->isFiltered = $filters['isFiltered'] ?? false;

            if (!empty($this->regionFilter)) {
                $areaQuery = DB::table('master_distributors')
                    ->whereIn('region_code', $this->regionFilter)
                    ->select('area_code', 'area_name')
                    ->distinct();
                $this->applyRegionAccess($areaQuery);
                $this->areasOption = $areaQuery->orderBy('area_name')->get();
            }

            if (!empty($this->areaFilter)) {
                $distQuery = DB::table('master_distributors')
                    ->whereIn('region_code', $this->regionFilter)
                    ->whereIn('area_code', $this->areaFilter)
                    ->where('is_active', true)
                    ->select('distributor_code', 'distributor_name')
                    ->distinct();
                $this->applyRegionAccess($distQuery);
                $this->distributorsOption = $distQuery->orderBy('distributor_name')->get();
            }
        }
    }

    // --- MULTI-SELECT HELPERS ---

    public function selectAllRegions()
    {
        $this->regionFilter = $this->regionsOption->pluck('region_code')->toArray();
        $this->updatedRegionFilter();
    }

    public function selectAllAreas()
    {
        $this->areaFilter = $this->areasOption->pluck('area_code')->toArray();
        $this->updatedAreaFilter();
    }

    public function selectAllDistributors()
    {
        $this->distributorFilter = $this->distributorsOption->pluck('distributor_code')->toArray();
    }

    // --- DEPENDENT DROPDOWN ---

    public function updatedRegionFilter()
    {
        $this->reset(['areaFilter', 'distributorFilter', 'areasOption', 'distributorsOption', 'isFiltered', 'search']);
        if (!empty($this->regionFilter)) {
            $query = DB::table('master_distributors')
                ->whereIn('region_code', $this->regionFilter)
                ->select('area_code', 'area_name')
                ->distinct();
            $this->applyRegionAccess($query);
            $this->areasOption = $query->orderBy('area_name')->get();
        }
    }

    public function updatedAreaFilter()
    {
        $this->reset(['distributorFilter', 'distributorsOption', 'isFiltered', 'search']);
        if (!empty($this->areaFilter)) {
            $query = DB::table('master_distributors')
                ->whereIn('region_code', $this->regionFilter)
                ->whereIn('area_code', $this->areaFilter)
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct();
            $this->applyRegionAccess($query);
            $this->distributorsOption = $query->orderBy('distributor_name')->get();
        }
    }

    public function updatedDistributorFilter()
    {
        $this->isFiltered = false;
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
            'monthFilter' => 'required',
            'regionFilter' => 'required|array|min:1',
            'areaFilter' => 'required|array|min:1',
        ]);

        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $unauthorizedRegions = array_diff($this->regionFilter, $user->region_code);
            if (!empty($unauthorizedRegions)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk wilayah yang dipilih.');
                return;
            }
        }

        $this->isFiltered = true;
        $this->isFilterModalOpen = false;
        $this->resetPage();
        $this->saveFiltersToSession();
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'search', 'isFiltered']);
        $this->areasOption = [];
        $this->distributorsOption = [];
        $this->monthFilter = date('Y-m');
        session()->forget('customer_eska_unmap_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regionsOption) === 1) {
            $this->regionFilter = [$this->regionsOption->first()->region_code];
            $this->updatedRegionFilter();
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('customer_eska_unmap_filters', [
            'monthFilter' => $this->monthFilter,
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            'isFiltered' => $this->isFiltered,
        ]);
    }

    public function export()
    {
        $this->validate([
            'monthFilter' => 'required',
            'regionFilter' => 'required|array|min:1',
        ]);

        $dateParam = Carbon::createFromFormat('Y-m', $this->monthFilter)->startOfMonth()->format('Y-m-d');
        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = 'Unmapped_Customer_' . $timestamp . '.xlsx';

        return Excel::download(
            new CustomerEskaUnmapExport(
                $dateParam,
                $this->regionFilter, 
                $this->areaFilter, 
                $this->distributorFilter,
                $this->search
            ), 
            $filename
        );
    }

    public function render()
    {
        $data = collect();

        if ($this->isFiltered) {
            $dateParam = Carbon::createFromFormat('Y-m', $this->monthFilter)->startOfMonth()->format('Y-m-d');

            $query = DB::table('customer_map_eska as cme')
                ->select(
                    'cme.bln',
                    'md.region_name',
                    'md.area_name',
                    'cme.distid',
                    'cme.branch_dist',
                    'cme.custno_dist',
                    'cde.custname as dist_cust_name',
                    'cme.branch',
                    'cme.custno',
                    'cpe.custname as prc_cust_name'
                )
                ->leftJoin('customer_dist_eska as cde', function ($join) {
                    $join->on('cme.distid', '=', 'cde.distid')
                         ->on('cme.branch_dist', '=', 'cde.branch')
                         ->on('cme.custno_dist', '=', 'cde.custno');
                })
                ->leftJoin('customer_prc_eska as cpe', function ($join) {
                    $join->on('cme.branch', '=', 'cpe.kodecabang')
                         ->on('cme.custno', '=', 'cpe.custno');
                })
                ->leftJoin('distributor_implementasi_eskalink as die', function ($join) {
                    $join->on('cme.distid', '=', 'die.eskalink_code_dist')
                         ->on('cme.branch_dist', '=', 'die.eskalink_code_dist')
                         ->on('cme.branch', '=', 'die.eskalink_code');
                })
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->where('cme.bln', $dateParam);

            $this->applyRegionAccess($query, 'md.region_code');

            if (!empty($this->regionFilter)) $query->whereIn('md.region_code', $this->regionFilter);
            if (!empty($this->areaFilter)) $query->whereIn('md.area_code', $this->areaFilter);
            if (!empty($this->distributorFilter)) $query->whereIn('md.distributor_code', $this->distributorFilter);

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('cme.custno_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cme.custno', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cde.custname', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->orderBy('cme.distid', 'asc')->paginate(20);
        }

        return view('livewire.customer-eska-unmap.index', [
            'customers' => $data
        ])->layout('layouts.app');
    }
}