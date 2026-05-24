<?php

namespace App\Livewire\CustomerEskaMap;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerEskaMapExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'customer-eska-map.index';

    // Filter properties
    public $regionFilter = '';
    public $areaFilter = '';
    public $distributorFilter = '';
    
    // UI states
    public $search = '';
    public $isFiltered = false;
    public $isFilterModalOpen = false;

    // Dropdown data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

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
        $this->regions = $query->orderBy('region_name')->get();

        // 2. Auto-select if only 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }

        // 3. Restore filters from session
        if (session()->has('customer_eska_map_filters')) {
            $filters = session()->get('customer_eska_map_filters');
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
                $this->areas = $areaQuery->orderBy('area_name')->get();
            }

            if ($this->areaFilter) {
                $distQuery = DB::table('master_distributors')
                    ->where('region_code', $this->regionFilter)
                    ->where('area_code', $this->areaFilter)
                    ->where('is_active', true)
                    ->select('distributor_code', 'distributor_name')
                    ->distinct();
                $this->applyRegionAccess($distQuery);
                $this->distributors = $distQuery->orderBy('distributor_name')->get();
            }
        }
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter', 'areas', 'distributors', 'isFiltered', 'search']);
        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $value)
                ->select('area_code', 'area_name')
                ->distinct();
            $this->applyRegionAccess($query);
            $this->areas = $query->orderBy('area_name')->get();
        }
    }

    public function updatedAreaFilter($value)
    {
        $this->reset(['distributorFilter', 'distributors', 'isFiltered', 'search']);
        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->regionFilter)
                ->where('area_code', $value)
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct();
            $this->applyRegionAccess($query);
            $this->distributors = $query->orderBy('distributor_name')->get();
        }
    }

    public function applyFilters()
    {
        $this->validate([
            'regionFilter' => 'required',
            'areaFilter' => 'required',
            'distributorFilter' => 'required',
        ]);

        $this->isFiltered = true;
        $this->isFilterModalOpen = false;
        $this->resetPage();
        $this->saveFiltersToSession();
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'search', 'isFiltered']);
        $this->areas = [];
        $this->distributors = [];
        session()->forget('customer_eska_map_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('customer_eska_map_filters', [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'search' => $this->search,
            'isFiltered' => $this->isFiltered,
        ]);
    }

    public function updatedSearch()
    {
        if ($this->isFiltered) {
            $this->resetPage();
            $this->saveFiltersToSession();
        }
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
        $filename = 'Customer_Eska_Map_' . $timestamp . '.xlsx';

        \App\Helpers\ActivityLogger::log('Export Customer Eska Map', "Mengekspor data pemetaan Customer Eska untuk distributor: {$this->distributorFilter}");

        return Excel::download(
            new CustomerEskaMapExport(
                $this->regionFilter, 
                $this->areaFilter, 
                $this->distributorFilter
            ), 
            $filename
        );
    }

    public function render()
    {
        $data = collect();

        if ($this->isFiltered) {
            $query = DB::table('customer_map_eska as cme')
                ->select(
                    'md.region_name',
                    'md.area_name',
                    'cme.distid',
                    'cme.branch_dist',
                    'cme.custno_dist',
                    'cde.custname as dist_cust_name',
                    'cme.branch',
                    'cme.custno',
                    'cpe.custname as prc_cust_name',
                    'cpe.custadd1 as addrs'
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
                ->where('md.region_code', $this->regionFilter)
                ->where('md.area_code', $this->areaFilter)
                ->where('md.distributor_code', $this->distributorFilter);

            $this->applyRegionAccess($query, 'md.region_code');

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('cme.custno_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cme.custno', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cde.custname', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cpe.custname', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->orderBy('cme.custno_dist')->paginate(10);
        }

        return view('livewire.customer-eska-map.index', [
            'customers' => $data
        ])->layout('layouts.app');
    }
}