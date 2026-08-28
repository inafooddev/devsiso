<?php

namespace App\Livewire\Report\MonitoringTopItem;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Monitoring extends Component
{
    use WithPagination;

    public $month;
    public $year;
    public $filterBucket = null;
    
    // New Filters
    public $filterRegion = '';
    public $filterArea = '';
    public $filterSupervisor = '';
    public $filterDistributor = '';

    public function mount()
    {
        // Default to current month and year
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function updatedFilterBucket() { $this->resetPage(); }
    
    public function updatedFilterRegion() { 
        $this->filterArea = '';
        $this->filterSupervisor = '';
        $this->filterDistributor = '';
        $this->resetPage(); 
    }
    public function updatedFilterArea() { 
        $this->filterSupervisor = '';
        $this->filterDistributor = '';
        $this->resetPage(); 
    }
    public function updatedFilterSupervisor() { 
        $this->filterDistributor = '';
        $this->resetPage(); 
    }
    public function updatedFilterDistributor() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->month = date('m');
        $this->year = date('Y');
        $this->filterRegion = '';
        $this->filterArea = '';
        $this->filterSupervisor = '';
        $this->filterDistributor = '';
        $this->filterBucket = null;
        $this->resetPage();
    }

    public function export()
    {
        $filters = [
            'month' => $this->month,
            'year' => $this->year,
            'filterRegion' => $this->filterRegion,
            'filterArea' => $this->filterArea,
            'filterSupervisor' => $this->filterSupervisor,
            'filterDistributor' => $this->filterDistributor,
            'filterBucket' => $this->filterBucket,
        ];
        
        $monthName = date('F', mktime(0, 0, 0, $this->month, 10));
        $filename = "Monitoring_Top_Item_NPD_{$monthName}_{$this->year}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MonitoringTopItemExport($filters), $filename);
    }

    public function render()
    {
        $user = auth()->user();
        $accessLevel = $user ? $user->getAccessLevel() : 'nasional';

        // Fetch Master Filter Options with Cascading (Chained) Logic
        $mdQuery = DB::table('master_distributors');
        if ($accessLevel === 'region') {
            $mdQuery->whereIn('region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $mdQuery->whereIn('area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $mdQuery->where('supervisor_code', $user->supervisor_code);
        }
        
        $regions = (clone $mdQuery)->distinct()->pluck('region_name')->filter()->sort();
        
        $areaQuery = clone $mdQuery;
        if ($this->filterRegion) $areaQuery->where('region_name', $this->filterRegion);
        $areas = $areaQuery->distinct()->pluck('area_name')->filter()->sort();

        $supQuery = DB::table('master_distributors as md')
            ->join('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->join('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code');
        if ($accessLevel === 'region') {
            $supQuery->whereIn('md.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $supQuery->whereIn('md.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $supQuery->where('md.supervisor_code', $user->supervisor_code);
        }
        if ($this->filterRegion) $supQuery->where('md.region_name', $this->filterRegion);
        if ($this->filterArea) $supQuery->where('md.area_name', $this->filterArea);
        $supervisors = $supQuery->distinct()->pluck('f.SLSNAME')->filter()->sort();

        $distQuery = DB::table('master_distributors as md')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code');
        if ($accessLevel === 'region') {
            $distQuery->whereIn('md.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $distQuery->whereIn('md.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $distQuery->where('md.supervisor_code', $user->supervisor_code);
        }
        if ($this->filterRegion) $distQuery->where('md.region_name', $this->filterRegion);
        if ($this->filterArea) $distQuery->where('md.area_name', $this->filterArea);
        if ($this->filterSupervisor) $distQuery->where('f.SLSNAME', $this->filterSupervisor);
        $distributors = $distQuery->distinct()->pluck('md.distributor_name')->filter()->sort();

        // 1. Fetch distinct top items dynamically with their category
        $topItems = DB::table('master_produk_lama')
            ->whereIn('kategory', ['NPD', 'TOPITEM'])
            ->select('kategory', 'topitem')
            ->distinct()
            ->orderBy('kategory', 'desc')
            ->orderBy('topitem')
            ->get()
            ->toArray();

        // Build the dynamic SELECT for the pivot
        $selects = [
            'md.region_name',
            'md.area_name',
            'tia.distributor_code',
            'md.distributor_name',
            'tia.uniq_code',
            'timc.customer_name',
            'timc.address',
            DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END) as total_prd_transaksi')
        ];

        foreach ($topItems as $index => $item) {
            $selects[] = DB::raw("SUM(CASE WHEN mpl.topitem = '" . addslashes($item->topitem) . "' THEN tia.qty ELSE 0 END) as prd" . ($index + 1) . "_qty");
        }

        $period = sprintf('%04d-%02d-01', $this->year, $this->month);
        
        $user = auth()->user();
        $accessLevel = $user ? $user->getAccessLevel() : 'nasional';

        // Define a helper closure to apply the master filters to any query that has the appropriate joins
        $applyMasterFilters = function ($query) use ($user, $accessLevel) {
            // Apply Hak Akses
            if ($accessLevel === 'region') {
                $query->whereIn('md.region_code', (array) $user->region_code);
            } elseif ($accessLevel === 'area') {
                $query->whereIn('md.area_code', (array) $user->area_code);
            } elseif ($accessLevel === 'supervisor') {
                $query->where('md.supervisor_code', $user->supervisor_code);
            }

            // Apply User Filter Selections
            if (!empty($this->filterRegion)) $query->where('md.region_name', $this->filterRegion);
            if (!empty($this->filterArea)) $query->where('md.area_name', $this->filterArea);
            if (!empty($this->filterDistributor)) $query->where('md.distributor_name', $this->filterDistributor);
            if (!empty($this->filterSupervisor)) $query->where('f.SLSNAME', $this->filterSupervisor);
        };

        // 1. Hitung keseluruhan total toko aktif di bulan tersebut
        $totalTokoQuery = DB::table('top_item_achievement as tia')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->where('tia.period', $period)
            ->where('tia.qty', '>', 0);
            
        $applyMasterFilters($totalTokoQuery);
        $totalTokoAll = $totalTokoQuery->count(DB::raw("DISTINCT CONCAT(tia.distributor_code, '-', tia.uniq_code)"));

        // KPI Query: Calculate how many top item products each store bought, then group them by bucket (1-6)
        $kpiSubquery = DB::table('top_item_achievement as tia')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->leftJoin('master_produk_lama as mpl', 'tia.pcode_prc', '=', 'mpl.pcode_prc')
            ->where('tia.period', $period)
            ->whereIn('mpl.kategory', ['NPD', 'TOPITEM'])
            ->select('tia.distributor_code', 'tia.uniq_code', DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END) as prd_count'))
            ->groupBy('tia.distributor_code', 'tia.uniq_code');
            
        $applyMasterFilters($kpiSubquery);

        $kpiData = DB::query()
            ->fromSub($kpiSubquery, 'store_stats')
            ->select([
                DB::raw('SUM(CASE WHEN prd_count = 1 THEN 1 ELSE 0 END) as beli_1'),
                DB::raw('SUM(CASE WHEN prd_count = 2 THEN 1 ELSE 0 END) as beli_2'),
                DB::raw('SUM(CASE WHEN prd_count = 3 THEN 1 ELSE 0 END) as beli_3'),
                DB::raw('SUM(CASE WHEN prd_count = 4 THEN 1 ELSE 0 END) as beli_4'),
                DB::raw('SUM(CASE WHEN prd_count = 5 THEN 1 ELSE 0 END) as beli_5'),
                DB::raw('SUM(CASE WHEN prd_count = 6 THEN 1 ELSE 0 END) as beli_6')
            ])
            ->first();
            
        // Attach the all-product active stores count as the denominator
        $kpiData->total_toko = $totalTokoAll;

        // Base query for Table Data
        $query = DB::table('top_item_achievement as tia')
            ->leftJoin('top_item_master_customer as timc', function($join) {
                $join->on('tia.distributor_code', '=', 'timc.distributor_code')
                     ->on('tia.uniq_code', '=', 'timc.uniq_code');
            })
            ->leftJoin('master_produk_lama as mpl', 'tia.pcode_prc', '=', 'mpl.pcode_prc')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->where('tia.period', $period)
            ->whereIn('mpl.kategory', ['NPD', 'TOPITEM']);
            
        $applyMasterFilters($query);

        $query->select($selects)
            ->groupBy(
                'md.region_name',
                'md.area_name',
                'tia.distributor_code',
                'md.distributor_name',
                'tia.uniq_code',
                'timc.customer_name',
                'timc.address'
            );

        // Apply bucket filter if selected
        if ($this->filterBucket !== null && $this->filterBucket !== '') {
            $query->having(DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END)'), '=', (int) $this->filterBucket);
        } else {
            $query->having(DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END)'), '>', 0); // Hide stores that only have minus/zero qty in this period
        }

        // Apply sorting
        $query->orderBy('md.region_name', 'asc')
              ->orderBy('md.area_name', 'asc')
              ->orderBy('md.distributor_name', 'asc');

        // Paginate results (100 per page)
        $data = $query->paginate(100);

        return view('livewire.report.monitoring-top-item.monitoring', [
            'data' => $data,
            'topItems' => $topItems,
            'kpi' => $kpiData,
            'regions' => $regions,
            'areas' => $areas,
            'distributors' => $distributors,
            'supervisors' => $supervisors
        ]);
    }
}
