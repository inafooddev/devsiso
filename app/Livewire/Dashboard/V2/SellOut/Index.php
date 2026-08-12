<?php

namespace App\Livewire\Dashboard\V2\SellOut;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Dashboard\Traits\WithAccessFilter;

class Index extends Component
{
    use WithPagination, WithAccessFilter;

    // Filters
    public string $selectedYear = '';
    public array $selectedMonths = []; // multi-select: [] = all months
    public string $selectedRegFest = 'ALL';

    // Breakdown Dimension Toggle
    public string $breakdownBy = 'Nasional'; // Nasional | Region | Area | Supervisor | Cabang

    // Hierarchy Dynamic Filters
    public array $listRegions = [];
    public array $listAreas = [];
    public array $listSupervisors = [];
    public array $listCabangs = [];

    public string $filterRegion = '';
    public string $filterArea = '';
    public string $filterSupervisor = '';
    public string $filterCabang = '';

    // Available years (nanti dari DB)
    public array $yearOptions = [];

    public array $monthOptions = [
        ['id' => '1',  'name' => 'Januari'],
        ['id' => '2',  'name' => 'Februari'],
        ['id' => '3',  'name' => 'Maret'],
        ['id' => '4',  'name' => 'April'],
        ['id' => '5',  'name' => 'Mei'],
        ['id' => '6',  'name' => 'Juni'],
        ['id' => '7',  'name' => 'Juli'],
        ['id' => '8',  'name' => 'Agustus'],
        ['id' => '9',  'name' => 'September'],
        ['id' => '10', 'name' => 'Oktober'],
        ['id' => '11', 'name' => 'November'],
        ['id' => '12', 'name' => 'Desember'],
    ];

    // Charts JSON
    public $chartContribution = '{}';
    public $chartRegionHBar = '{}';
    public $chartCombo = '{}';
    public $chartSalesTrend = '{}';
    public $chartMonthlyBar = '{}';
    public $chartMonthlyCyLy = '{}';
    public $chartAoTrendJson = '{}';
    public $chartMapData = '[]';
    
    // Table Data
    public array $topAch = [];
    public array $topGrowth = [];
    public array $gapTarget = [];
    public array $gapGrowth = [];
    public string $pivotDataJson = '{}';
    public string $pivotGrandTotalJson = '{}';
    public string $pivotLevel1Name = 'Region';
    public string $pivotLevel2Name = 'Area';
    public int $pivotMaxMonth = 12;
    
    // Datatable / List
    public array $listData = [];



    // KPI Card Data (reactive, di-reload setiap filter berubah)
    public array $kpiData = [];

    public function mount()
    {
        $userLevel = auth()->user() ? auth()->user()->getAccessLevel() : 'nasional';
        if ($userLevel === 'region') {
            $this->breakdownBy = 'Region';
        } elseif ($userLevel === 'area') {
            $this->breakdownBy = 'Area';
        } elseif ($userLevel === 'supervisor') {
            $this->breakdownBy = 'Supervisor';
        }

        // Nanti ambil dari DB: $this->yearOptions = DB::table('sales')->selectRaw('YEAR(date) as y')->distinct()->pluck('y')->map(fn($y) => ['id' => $y, 'name' => $y])->toArray();
        // Ambil tahun unik yang tersedia di database, urutkan menurun
        $query = \Illuminate\Support\Facades\DB::table('v_sellout_per_cabang')
            ->selectRaw('EXTRACT(YEAR FROM bulan) as yr')
            ->distinct();
            
        $this->applyAccessFilter($query);
        
        $years = $query->orderBy('yr', 'desc')
            ->pluck('yr')
            ->toArray();

        // Jika DB kosong, default ke tahun sekarang
        if (empty($years)) {
            $years = [date('Y')];
        }

        $this->yearOptions = array_map(function($yr) {
            return ['id' => (string)$yr, 'name' => (string)$yr];
        }, $years);

        // Set default ke tahun terbaru jika belum diset atau tidak valid
        if (!in_array($this->selectedYear, $years)) {
            $this->selectedYear = (string)$years[0];
        }
        
        $this->loadHierarchyFilters();
        $this->loadKpiData();   // KPI dulu agar total_sales tersedia untuk chart
        $this->loadChartData();
        $this->loadPivotData();
    }

    private function loadHierarchyFilters()
    {
        $baseQuery = \Illuminate\Support\Facades\DB::table('v_sellout_per_cabang')
            ->where('region', '!=', 'HOINA');
            
        $this->applyAccessFilter($baseQuery);

        $this->listRegions = (clone $baseQuery)->whereNotNull('region')->where('region', '!=', '')->distinct()->pluck('region')->sort()->values()->toArray();
        $this->listAreas = (clone $baseQuery)->whereNotNull('area')->where('area', '!=', '')->distinct()->pluck('area')->sort()->values()->toArray();
        $this->listSupervisors = (clone $baseQuery)->whereNotNull('supervisor')->where('supervisor', '!=', '')->distinct()->pluck('supervisor')->sort()->values()->toArray();
        $this->listCabangs = (clone $baseQuery)->whereNotNull('cabang')->where('cabang', '!=', '')->distinct()->pluck('cabang')->sort()->values()->toArray();
    }

    // Reactive watchers — setiap filter berubah, grafik langsung update
    public function updatedBreakdownBy()
    {
        // Reset semua filter ketika pindah tab agar tiap tab benar-benar independent (buta terhadap tab lain)
        $this->filterRegion = '';
        $this->filterArea = '';
        $this->filterSupervisor = '';
        $this->filterCabang = '';
        
        $this->reload();
    }

    public function updatedFilterRegion()
    {
        // Reset filter di bawahnya
        $this->filterArea = '';
        $this->filterSupervisor = '';
        $this->filterCabang = '';
        if ($this->filterRegion) {
            $this->breakdownBy = 'Region';
        } else {
            $this->breakdownBy = 'Nasional';
        }
        $this->reload();
    }

    public function updatedFilterArea()
    {
        // Reset filter di bawahnya
        $this->filterSupervisor = '';
        $this->filterCabang = '';
        if ($this->filterArea) {
            $this->breakdownBy = 'Area';
        } else {
            $this->breakdownBy = $this->filterRegion ? 'Region' : 'Nasional';
        }
        $this->reload();
    }

    public function updatedFilterSupervisor()
    {
        // Reset filter di bawahnya
        $this->filterCabang = '';
        if ($this->filterSupervisor) {
            $this->breakdownBy = 'Supervisor';
        } else {
            $this->breakdownBy = $this->filterArea ? 'Area' : ($this->filterRegion ? 'Region' : 'Nasional');
        }
        $this->reload();
    }

    public function updatedFilterCabang()
    {
        if ($this->filterCabang) {
            $this->breakdownBy = 'Cabang';
        } else {
            $this->breakdownBy = $this->filterSupervisor ? 'Supervisor' : ($this->filterArea ? 'Area' : ($this->filterRegion ? 'Region' : 'Nasional'));
        }
        $this->reload();
    }
    public function updatedSelectedYear()   { $this->reload(); }
    public function updatedSelectedMonths() { $this->reload(); }
    public function updatedSelectedRegFest(){ $this->reload(); }

    private function reload()
    {
        $this->loadKpiData();   // KPI dulu agar total_sales tersedia untuk chart
        $this->loadChartData();
        $this->loadPivotData(); // Hitung data untuk pivot table
        $this->dispatch('charts-updated');
    }

    /**
     * SINGLE QUERY OPTIMIZATION
     * Pulls all aggregated raw data in one highly optimized query.
     */
    private function getRawData()
    {
        $cacheKey = "sellout_raw_{$this->breakdownBy}_{$this->selectedYear}_{$this->selectedRegFest}_{$this->filterRegion}_{$this->filterArea}_{$this->filterSupervisor}_{$this->filterCabang}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(6), function() {
            $ty = (int)$this->selectedYear;
            $ly = $ty - 1;
            
            $q = \Illuminate\Support\Facades\DB::table('v_sellout_per_cabang as vspc')
                ->leftJoinSub(function($q3) {
                    $q3->from('master_distributors')
                       ->where('is_active', true)
                       ->select('branch_name')
                       ->selectRaw('AVG(latitude) as lat, AVG(longitude) as lng')
                       ->groupBy('branch_name');
                }, 'md', 'md.branch_name', '=', 'vspc.cabang')
                ->whereIn(\Illuminate\Support\Facades\DB::raw('EXTRACT(YEAR FROM vspc.bulan)'), [$ty, $ly])
                ->where('vspc.region', '!=', 'HOINA');

            $this->applyAccessFilter($q, 'vspc.');

        if ($this->breakdownBy === 'Region' && !empty($this->filterRegion)) {
            $q->where('vspc.region', $this->filterRegion);
        } elseif ($this->breakdownBy === 'Area' && !empty($this->filterArea)) {
            $q->where('vspc.area', $this->filterArea);
        } elseif ($this->breakdownBy === 'Supervisor' && !empty($this->filterSupervisor)) {
            $q->where('vspc.supervisor', $this->filterSupervisor);
        } elseif ($this->breakdownBy === 'Cabang' && !empty($this->filterCabang)) {
            $q->where('vspc.cabang', $this->filterCabang);
        }
                
            if (!empty($this->selectedRegFest)) {
                if ($this->selectedRegFest === 'REG' || $this->selectedRegFest === 'FEST') {
                    $q->where('vspc.reg_fest', $this->selectedRegFest);
                }
            }
            
            $q->select(
                \Illuminate\Support\Facades\DB::raw('EXTRACT(YEAR FROM vspc.bulan) as yr'),
                \Illuminate\Support\Facades\DB::raw('EXTRACT(MONTH FROM vspc.bulan) as mnth'),
                \Illuminate\Support\Facades\DB::raw('vspc.region as region_name'),
                \Illuminate\Support\Facades\DB::raw('vspc.area as area_name'),
                \Illuminate\Support\Facades\DB::raw('vspc.supervisor as supervisor_name'),
                \Illuminate\Support\Facades\DB::raw('vspc.cabang as branch_name'),
                'md.lat', 'md.lng'
            )
            ->selectRaw('SUM(vspc.actual) as actual, SUM(vspc.target) as target')
            ->groupBy(
                \Illuminate\Support\Facades\DB::raw('EXTRACT(YEAR FROM vspc.bulan)'),
                \Illuminate\Support\Facades\DB::raw('EXTRACT(MONTH FROM vspc.bulan)'),
                'vspc.region', 'vspc.area', 'vspc.supervisor', 'vspc.cabang',
                'md.lat', 'md.lng'
            );
            
            return $q->get();
        });
    }

    private function loadKpiData(): void
    {
        $rawData = collect($this->getRawData());
        $ty = (int)$this->selectedYear;
        $ly = $ty - 1;

        $ytdMaxMonth = $rawData->where('yr', $ty)->where('actual', '>', 0)->max('mnth') ?: 12;
        
        $filteredData = $rawData;
        if (!empty($this->selectedMonths)) {
            $filteredData = $rawData->whereIn('mnth', $this->selectedMonths);
        } else {
            $filteredData = $rawData->where('mnth', '<=', $ytdMaxMonth);
        }

        $sales = $filteredData->where('yr', $ty)->sum('actual');
        $target = $filteredData->where('yr', $ty)->sum('target');
        $lySales = $filteredData->where('yr', $ly)->sum('actual');

        $growthPct = $lySales > 0 ? round((($sales - $lySales) / $lySales) * 100, 1) : 0;
        $achievementPct = $target > 0 ? round(($sales / $target) * 100, 1) : 0;
        $salesVsLyPct = $lySales > 0 ? round(($sales / $lySales) * 100, 1) : 0;

        // Sales Average (YTD without month filter)
        $avgData = $rawData->where('mnth', '<=', $ytdMaxMonth);
        $avgSalesTotal = $avgData->where('yr', $ty)->sum('actual');
        $avgLyTotal = $avgData->where('yr', $ly)->sum('actual');

        $avgSales = $ytdMaxMonth > 0 ? (int) ($avgSalesTotal / $ytdMaxMonth) : 0;
        $avgLy = $ytdMaxMonth > 0 ? (int) ($avgLyTotal / $ytdMaxMonth) : 0;
        $avgGrowth = $avgLy > 0 ? round((($avgSales - $avgLy) / $avgLy) * 100, 1) : 0;

        $this->kpiData = [
            'total_actual_ty'  => $sales,
            'total_target'     => $target,
            'total_ly'         => $lySales,
            'growth_pct'       => $growthPct,
            'gap_vs_ly'        => $sales - $lySales,
            'gap_vs_target'    => $sales - $target,
            'achievement_pct'  => $achievementPct,
            'sales_vs_ly_pct'  => $salesVsLyPct,
            'avg_sales_yoy'    => $avgSales,
            'avg_sales_growth' => $avgGrowth,
        ];
    }

    private function loadChartData()
    {
        $rawData = collect($this->getRawData());
        $ty = (int)$this->selectedYear;
        $ly = $ty - 1;
        $ytdMaxMonth = $rawData->where('yr', $ty)->where('actual', '>', 0)->max('mnth') ?: 12;
        
        $filteredData = $rawData;
        if (!empty($this->selectedMonths)) {
            $filteredData = $rawData->whereIn('mnth', $this->selectedMonths);
        } else {
            $filteredData = $rawData->where('mnth', '<=', $ytdMaxMonth);
        }

        $groupCol = '';
        switch ($this->breakdownBy) {
            case 'Nasional':   $groupCol = 'region_name'; break;
            case 'Region':     $groupCol = 'area_name'; break;
            case 'Area':       $groupCol = 'supervisor_name'; break;
            case 'Supervisor': $groupCol = 'branch_name'; break;
            case 'Cabang':     $groupCol = 'branch_name'; break;
            default:           $groupCol = 'region_name'; break;
        }

        $labels = $this->resolveLabels();
        if (count($labels) > 15) {
            $labels = array_slice($labels, 0, 15);
        }
        if (count($labels) === 0) {
            $labels = ['No Data'];
        }

        $seriesContrib = [];
        $actualsHBar   = [];
        $targetsHBar   = [];
        $actualsLYCombo= [];
        $regionData = [];

        foreach ($labels as $label) {
            $rowsTY = $filteredData->where('yr', $ty)->where($groupCol, $label);
            $rowsLY = $filteredData->where('yr', $ly)->where($groupCol, $label);

            $actTY = (int) $rowsTY->sum('actual');
            $tgtTY = (int) $rowsTY->sum('target');
            $actLY = (int) $rowsLY->sum('actual');
            
            $seriesContrib[] = $actTY;
            $actualsHBar[]   = $actTY;
            $targetsHBar[]   = $tgtTY;
            $actualsLYCombo[]= $actLY;

            $achPct = $tgtTY > 0 ? ($actTY / $tgtTY) * 100 : 0;
            $grwPct = $actLY > 0 ? (($actTY - $actLY) / $actLY) * 100 : 0;
            
            $regionData[] = [
                'label'      => $label,
                'sales'      => $actTY,
                'target'     => $tgtTY,
                'last_year'  => $actLY,
                'ach_pct'    => round($achPct, 1),
                'growth_pct' => round($grwPct, 1),
                'gap_target' => $actTY - $tgtTY,
                'gap_ly'     => $actTY - $actLY,
            ];
        }

        $regionCollection = collect($regionData);
        $this->topAch = $regionCollection->sortByDesc('ach_pct')->values()->all();
        $this->topGrowth = $regionCollection->sortByDesc('growth_pct')->values()->all();
        $this->gapTarget = $regionCollection->sortBy('gap_target')->values()->all();
        $this->gapGrowth = $regionCollection->sortBy('gap_ly')->values()->all();

        $this->chartContribution = json_encode([
            'labels'       => $labels,
            'series'       => $seriesContrib,
            'total_sales'  => $this->kpiData['total_actual_ty'] ?? 0,
            'sales_values' => $seriesContrib,
        ]);

        $this->chartRegionHBar = json_encode([
            'labels'  => $labels,
            'actuals' => $actualsHBar,
            'targets' => $targetsHBar,
        ]);

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $actualsCombo = [];
        $targetsCombo = [];
        $actualsTY = [];
        $actualsLY = [];

        foreach (range(1, 12) as $mIdx) {
            $mRowsTY = $rawData->where('yr', $ty)->where('mnth', $mIdx);
            $mRowsLY = $rawData->where('yr', $ly)->where('mnth', $mIdx);

            $actTY = (int) $mRowsTY->sum('actual');
            $tgtTY = (int) $mRowsTY->sum('target');
            $actLY = (int) $mRowsLY->sum('actual');

            $actualsCombo[] = $actTY;
            $targetsCombo[] = $tgtTY;
            $actualsTY[] = $actTY;
            $actualsLY[] = $actLY;
        }

        $this->chartCombo = json_encode([
            'labels'    => $labels,
            'current'   => $actualsHBar,
            'last_year' => $actualsLYCombo,
        ]);

        $trendSeries = [];
        foreach ($labels as $label) {
            $data = [];
            foreach (range(1, 12) as $mIdx) {
                $mRowsLabelTY = $rawData->where('yr', $ty)->where($groupCol, $label)->where('mnth', $mIdx);
                $data[] = (int) $mRowsLabelTY->sum('actual');
            }
            $trendSeries[] = [
                'name' => $label,
                'data' => $data
            ];
        }

        $this->chartSalesTrend = json_encode([
            'labels' => $months,
            'series' => $trendSeries,
        ]);

        $this->chartMonthlyBar = json_encode([
            'labels'  => $months,
            'actuals' => $actualsTY,
            'targets' => $targetsCombo,
        ]);

        $this->chartMonthlyCyLy = json_encode([
            'labels'  => $months,
            'current' => $actualsTY,
            'last_year' => $actualsLY,
        ]);

        // Load AO Trend Data for CY and LY
        $qAoCY = \Illuminate\Support\Facades\DB::table('ao_percabang_perbulan')
            ->where(\Illuminate\Support\Facades\DB::raw('EXTRACT(YEAR FROM bulan)'), $ty);
            
        $this->applyAccessFilter($qAoCY);
        
        $qAoLY = \Illuminate\Support\Facades\DB::table('ao_percabang_perbulan')
            ->where(\Illuminate\Support\Facades\DB::raw('EXTRACT(YEAR FROM bulan)'), $ly);
            
        $this->applyAccessFilter($qAoLY);
        
        if (!empty($this->filterRegion)) { 
            $qAoCY->where('region', $this->filterRegion); 
            $qAoLY->where('region', $this->filterRegion); 
        }
        if (!empty($this->filterArea)) { 
            $qAoCY->where('area', $this->filterArea); 
            $qAoLY->where('area', $this->filterArea); 
        }
        if (!empty($this->filterSupervisor)) {
            $cabangBySpv = \Illuminate\Support\Facades\DB::table('v_sellout_per_cabang')
                ->where('supervisor', $this->filterSupervisor)
                ->distinct()
                ->pluck('cabang')
                ->toArray();
            
            $qAoCY->whereIn('cabang', $cabangBySpv);
            $qAoLY->whereIn('cabang', $cabangBySpv);
        }
        if (!empty($this->filterCabang)) { 
            $qAoCY->where('cabang', $this->filterCabang); 
            $qAoLY->where('cabang', $this->filterCabang); 
        }

        $aoDataCY = $qAoCY->selectRaw('EXTRACT(MONTH FROM bulan) as mnth, SUM(ao) as total_ao')
            ->groupBy(\Illuminate\Support\Facades\DB::raw('EXTRACT(MONTH FROM bulan)'))
            ->get();
            
        $aoDataLY = $qAoLY->selectRaw('EXTRACT(MONTH FROM bulan) as mnth, SUM(ao) as total_ao')
            ->groupBy(\Illuminate\Support\Facades\DB::raw('EXTRACT(MONTH FROM bulan)'))
            ->get();
            
        $aoTrendCY = [];
        $aoTrendLY = [];
        foreach (range(1, 12) as $mIdx) {
            $rowCY = $aoDataCY->firstWhere('mnth', $mIdx);
            $aoTrendCY[] = $rowCY ? (int) $rowCY->total_ao : 0;
            
            $rowLY = $aoDataLY->firstWhere('mnth', $mIdx);
            $aoTrendLY[] = $rowLY ? (int) $rowLY->total_ao : 0;
        }

        $this->chartAoTrendJson = json_encode([
            'labels' => $months,
            'series' => [
                ['name' => 'CY ('.$ty.')', 'data' => $aoTrendCY],
                ['name' => 'LY ('.$ly.')', 'data' => $aoTrendLY]
            ]
        ]);

        $mapRows = $filteredData->where('yr', $ty);
        $groupedMap = $mapRows->groupBy('branch_name');
        
        $mapData = [];
        foreach ($groupedMap as $cabang => $rows) {
            $sales = $rows->sum('actual');
            $lat = $rows->first()->lat ?? null;
            $lng = $rows->first()->lng ?? null;
            
            if ($sales > 0 && $lat && $lng) {
                $mapData[] = [
                    'name' => $cabang,
                    'sales' => (int) $sales,
                    'lat' => (float) $lat,
                    'lng' => (float) $lng
                ];
            }
        }
        $this->chartMapData = json_encode($mapData);
    }
    private function loadPivotData()
    {
        $rawData = $this->getRawData();
        $filteredData = collect($rawData);

        $ty = (int)$this->selectedYear;
        $ly = $ty - 1;

        $lvl1 = 'region_name'; $lvl2 = 'area_name';
        $this->pivotLevel1Name = 'Region'; $this->pivotLevel2Name = 'Area';
        if ($this->breakdownBy === 'Region') {
            $lvl1 = 'area_name'; $lvl2 = 'supervisor_name';
            $this->pivotLevel1Name = 'Area'; $this->pivotLevel2Name = 'Supervisor';
        } elseif ($this->breakdownBy === 'Area') {
            $lvl1 = 'supervisor_name'; $lvl2 = 'branch_name';
            $this->pivotLevel1Name = 'Supervisor'; $this->pivotLevel2Name = 'Cabang';
        } elseif ($this->breakdownBy === 'Supervisor' || $this->breakdownBy === 'Cabang') {
            $lvl1 = 'branch_name'; $lvl2 = 'branch_name';
            $this->pivotLevel1Name = 'Cabang'; $this->pivotLevel2Name = 'Cabang';
        }

        $pivot = [];
        $grandTotal = [];
        $maxMonth = 1;
        
        foreach ($filteredData as $row) {
            $m = (int)$row->mnth;
            $y = (int)$row->yr;
            if ($y === $ty && (float)$row->actual > 0 && $m > $maxMonth) {
                $maxMonth = $m;
            }
        }
        $this->pivotMaxMonth = $maxMonth;

        for ($m = 1; $m <= 12; $m++) {
            $grandTotal[$m] = ['sales' => 0, 'target' => 0, 'ly' => 0];
        }
        $grandTotal['YTD'] = ['sales' => 0, 'target' => 0, 'ly' => 0];

        foreach ($filteredData as $row) {
            $l1 = $row->$lvl1 ?? 'UNKNOWN';
            $l2 = $row->$lvl2 ?? 'UNKNOWN';
            $m = (int)$row->mnth;
            $y = (int)$row->yr;
            
            if ($m < 1 || $m > 12) continue;

            if (!isset($pivot[$l1])) {
                $pivot[$l1] = [
                    'areas' => [],
                    'total' => []
                ];
                for ($i = 1; $i <= 12; $i++) {
                    $pivot[$l1]['total'][$i] = ['sales' => 0, 'target' => 0, 'ly' => 0];
                }
                $pivot[$l1]['total']['YTD'] = ['sales' => 0, 'target' => 0, 'ly' => 0];
            }
            if (!isset($pivot[$l1]['areas'][$l2])) {
                $pivot[$l1]['areas'][$l2] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $pivot[$l1]['areas'][$l2][$i] = ['sales' => 0, 'target' => 0, 'ly' => 0];
                }
                $pivot[$l1]['areas'][$l2]['YTD'] = ['sales' => 0, 'target' => 0, 'ly' => 0];
            }

            if ($y === $ty) {
                $pivot[$l1]['areas'][$l2][$m]['sales'] += (float)$row->actual;
                $pivot[$l1]['areas'][$l2][$m]['target'] += (float)$row->target;
                
                $pivot[$l1]['total'][$m]['sales'] += (float)$row->actual;
                $pivot[$l1]['total'][$m]['target'] += (float)$row->target;
                
                $grandTotal[$m]['sales'] += (float)$row->actual;
                $grandTotal[$m]['target'] += (float)$row->target;
                
                if ($m <= $maxMonth) {
                    $pivot[$l1]['areas'][$l2]['YTD']['sales'] += (float)$row->actual;
                    $pivot[$l1]['areas'][$l2]['YTD']['target'] += (float)$row->target;
                    $pivot[$l1]['total']['YTD']['sales'] += (float)$row->actual;
                    $pivot[$l1]['total']['YTD']['target'] += (float)$row->target;
                    $grandTotal['YTD']['sales'] += (float)$row->actual;
                    $grandTotal['YTD']['target'] += (float)$row->target;
                }
            } elseif ($y === $ly) {
                $pivot[$l1]['areas'][$l2][$m]['ly'] += (float)$row->actual;
                $pivot[$l1]['total'][$m]['ly'] += (float)$row->actual;
                $grandTotal[$m]['ly'] += (float)$row->actual;
                
                if ($m <= $maxMonth) {
                    $pivot[$l1]['areas'][$l2]['YTD']['ly'] += (float)$row->actual;
                    $pivot[$l1]['total']['YTD']['ly'] += (float)$row->actual;
                    $grandTotal['YTD']['ly'] += (float)$row->actual;
                }
            }
        }
        
        $calcPct = function($data) {
            foreach ($data as $m => $v) {
                $s = $v['sales'];
                $t = $v['target'];
                $ly = $v['ly'];
                $data[$m]['vs_tgt'] = $t > 0 ? round(($s / $t) * 100, 1) : 0;
                $data[$m]['vs_ly'] = $ly > 0 ? round((($s - $ly) / $ly) * 100, 1) : 0;
            }
            return $data;
        };

        foreach ($pivot as $l1 => $l1Data) {
            foreach ($l1Data['areas'] as $l2 => $l2Data) {
                $pivot[$l1]['areas'][$l2] = $calcPct($l2Data);
            }
            $pivot[$l1]['total'] = $calcPct($l1Data['total']);
        }
        $grandTotal = $calcPct($grandTotal);

        ksort($pivot);
        foreach ($pivot as $l1 => $l1Data) {
            ksort($pivot[$l1]['areas']);
        }

        $this->pivotDataJson = json_encode(empty($pivot) ? new \stdClass() : $pivot);
        $this->pivotGrandTotalJson = json_encode($grandTotal);
    }

    private function resolveLabels(): array
    {
        $q = \Illuminate\Support\Facades\DB::table('v_sellout_per_cabang')
            ->where('region', '!=', 'HOINA');
            
        $this->applyAccessFilter($q);

        if ($this->breakdownBy === 'Region' && !empty($this->filterRegion)) {
            $q->where('region', $this->filterRegion);
        } elseif ($this->breakdownBy === 'Area' && !empty($this->filterArea)) {
            $q->where('area', $this->filterArea);
        } elseif ($this->breakdownBy === 'Supervisor' && !empty($this->filterSupervisor)) {
            $q->where('supervisor', $this->filterSupervisor);
        } elseif ($this->breakdownBy === 'Cabang' && !empty($this->filterCabang)) {
            $q->where('cabang', $this->filterCabang);
        }
        
        if (!empty($this->selectedRegFest)) {
            if ($this->selectedRegFest === 'REG' || $this->selectedRegFest === 'FEST') {
                $q->where('reg_fest', $this->selectedRegFest);
            }
        }

        switch ($this->breakdownBy) {
            case 'Nasional':
                return (clone $q)
                    ->whereNotNull('region')->where('region', '!=', '')
                    ->distinct()->orderBy('region')
                    ->pluck('region')->values()->toArray();

            case 'Region':
                return (clone $q)
                    ->whereNotNull('area')->where('area', '!=', '')
                    ->distinct()->orderBy('area')
                    ->pluck('area')->values()->toArray();

            case 'Area':
                return (clone $q)
                    ->whereNotNull('supervisor')->where('supervisor', '!=', '')
                    ->distinct()->orderBy('supervisor')
                    ->pluck('supervisor')->values()->toArray();

            case 'Supervisor':
            case 'Cabang':
                return (clone $q)
                    ->whereNotNull('cabang')->where('cabang', '!=', '')
                    ->distinct()->orderBy('cabang')
                    ->pluck('cabang')->values()->toArray();

            default:
                return ['No Data'];
        }
    }

    public function getInsightsProperty()
    {
        return [
            ['title' => 'Top Achievement', 'value' => 'Region 2 (115%)', 'sub' => 'Highest performing', 'type' => 'success'],
            ['title' => 'Biggest Gap', 'value' => 'Area Jateng', 'sub' => 'Below target by 2B', 'type' => 'error'],
            ['title' => 'Fastest Growth', 'value' => 'Cabang Malang', 'sub' => '+45% vs LY', 'type' => 'success'],
            ['title' => 'Avg Ticket', 'value' => 'Rp 15.5M', 'sub' => 'Per transaction', 'type' => 'info'],
        ];
    }

    public function getTopAchDataProperty()
    {
        return [
            ['name' => 'Region 2', 'target' => 10000000, 'actual' => 11500000, 'ach' => 115.0],
            ['name' => 'Region 1', 'target' => 12000000, 'actual' => 12500000, 'ach' => 104.1],
            ['name' => 'Region 5', 'target' => 8000000, 'actual' => 8200000, 'ach' => 102.5],
        ];
    }

    public function getTopGrowthDataProperty()
    {
        return [
            ['name' => 'Region 2', 'ly' => 8000000, 'ty' => 11500000, 'growth' => 43.7],
            ['name' => 'Region 3', 'ly' => 6000000, 'ty' => 7500000, 'growth' => 25.0],
            ['name' => 'Region 1', 'ly' => 11000000, 'ty' => 12500000, 'growth' => 13.6],
        ];
    }

    public function getGapVsTargetDataProperty()
    {
        return [
            ['name' => 'Region 4', 'target' => 15000000, 'actual' => 12000000, 'gap' => -3000000],
            ['name' => 'Region 3', 'target' => 9000000, 'actual' => 7500000, 'gap' => -1500000],
        ];
    }

    public function getGapYoYDataProperty()
    {
        return [
            ['name' => 'Region 4', 'ly' => 13000000, 'ty' => 12000000, 'gap' => -1000000],
        ];
    }

    public function render()
    {
        // Main detail table data
        $details = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach($months as $idx => $m) {
            $ly = rand(2000, 4000) * 1000000;
            $target = rand(2500, 4500) * 1000000;
            $actual = $target * (rand(85, 115) / 100);
            
            $details[] = (object)[
                'bulan_label' => $m,
                'ly_actual' => $ly,
                'target' => $target,
                'actual' => $actual,
                'ach_pct' => ($actual / $target) * 100,
                'growth_pct' => (($actual - $ly) / $ly) * 100,
                'gap_value' => $actual - $target,
                'gap_vs_ly' => $actual - $ly
            ];
        }

        // We paginate details array just to mock pagination
        $paginatedDetails = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($details, 0, 12),
            12,
            12,
            1
        );

        return view('livewire.dashboard.v2.sell-out.index', [
            'details' => $paginatedDetails,
            'pivotData' => json_decode($this->pivotDataJson, true) ?: [],
            'pivotGrandTotal' => json_decode($this->pivotGrandTotalJson, true) ?: []
        ])->layout('layouts.app');
    }
}
