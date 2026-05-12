<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ======================
    // FILTER STATE
    // ======================
    public int    $selectedYear     = 0;
    public int    $selectedMonthFrom = 1;
    public int    $selectedMonthTo   = 12;
    public array  $selectedRegions  = [];
    public string $selectedRegFest  = 'ALL'; // ALL | REG | FEST

    // ======================
    // OPTIONS
    // ======================
    public array $yearOptions    = [];
    public array $regionsOption  = [];
    public array $regFestOptions = ['ALL', 'REG', 'FEST'];

    // ======================
    // KPI DATA
    // ======================
    public array $kpiData = [];

    // ======================
    // CHART DATA (JSON strings for ApexCharts)
    // ======================
    public string $chartRegionContribution = '[]';
    public string $chartSalesTrend         = '[]';
    public string $chartMonthlyBar         = '[]';
    public string $chartGrowthArea         = '[]';
    public string $chartRegionHBar         = '[]';
    public string $chartCombo              = '[]';

    // ======================
    // TABLE DATA
    // ======================
    public array $topByAch     = [];
    public array $topByGrowth  = [];
    public array $gapVsTarget  = [];
    public array $gapYoY       = [];
    public array $yoyComparison = [];

    // ======================
    // INSIGHTS
    // ======================
    public array $insights = [];

    // ======================
    // UI STATE
    // ======================
    public bool $showFilterModal = false;

    // ======================
    // TABLE SEARCH STRINGS
    // ======================
    public string $searchDetail = '';

    // ======================
    // MOUNT
    // ======================
    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');

        // Build year options (last 5 years)
        $currentYear = (int) date('Y');
        for ($y = $currentYear; $y >= $currentYear - 4; $y--) {
            $this->yearOptions[] = $y;
        }

        // Load region options with access control
        $query = DB::table('v_sellinvstarget')
            ->select('region', 'region_code')
            ->distinct()
            ->orderBy('region');

        $this->applyRegionAccess($query, 'region_code');

        $this->regionsOption = $query->pluck('region', 'region_code')->toArray();

        // Auto-select all regions by default
        $this->selectedRegions = array_keys($this->regionsOption);

        $this->loadDashboardData();
    }

    // ======================
    // REGION ACCESS HELPER
    // ======================
    private function applyRegionAccess($query, string $column = 'region_code'): void
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }
    }

    // ======================
    // BASE FILTER HELPER
    // ======================
    private function applyBaseFilters($query): void
    {
        // Year filter
        $query->whereYear('bulan', $this->selectedYear);

        // Month range
        $query->whereRaw('EXTRACT(MONTH FROM bulan) >= ?', [$this->selectedMonthFrom])
              ->whereRaw('EXTRACT(MONTH FROM bulan) <= ?', [$this->selectedMonthTo]);

        // Region
        if (!empty($this->selectedRegions)) {
            $query->whereIn('region_code', $this->selectedRegions);
        }

        // Reg/Fest
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }

        // User access control
        $this->applyRegionAccess($query, 'region_code');
    }

    private function applyLastYearFilters($query): void
    {
        $lastYear = $this->selectedYear - 1;
        $query->whereYear('bulan', $lastYear);
        $query->whereRaw('EXTRACT(MONTH FROM bulan) >= ?', [$this->selectedMonthFrom])
              ->whereRaw('EXTRACT(MONTH FROM bulan) <= ?', [$this->selectedMonthTo]);

        if (!empty($this->selectedRegions)) {
            $query->whereIn('region_code', $this->selectedRegions);
        }

        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }

        $this->applyRegionAccess($query, 'region_code');
    }

    /**
     * Snapshot: year + date-range + type — NO region selection.
     * Used by: Region Contribution, Region Comparison.
     */
    private function applySnapshotFilters($query): void
    {
        $query->whereYear('bulan', $this->selectedYear)
              ->whereRaw('EXTRACT(MONTH FROM bulan) >= ?', [$this->selectedMonthFrom])
              ->whereRaw('EXTRACT(MONTH FROM bulan) <= ?', [$this->selectedMonthTo]);
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }
        $this->applyRegionAccess($query, 'region_code');
    }

    /**
     * Trend (TY): year + region + type — NO date range (shows full year).
     * Used by: Sales Trend, Monthly Bar, Growth Area, Combo.
     */
    private function applyTrendFilters($query): void
    {
        $query->whereYear('bulan', $this->selectedYear);
        if (!empty($this->selectedRegions)) {
            $query->whereIn('region_code', $this->selectedRegions);
        }
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }
        $this->applyRegionAccess($query, 'region_code');
    }

    /**
     * Trend (LY): previous year + region + type — NO date range.
     */
    private function applyTrendLastYearFilters($query): void
    {
        $query->whereYear('bulan', $this->selectedYear - 1);
        if (!empty($this->selectedRegions)) {
            $query->whereIn('region_code', $this->selectedRegions);
        }
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }
        $this->applyRegionAccess($query, 'region_code');
    }

    // ======================
    // APPLY FILTER (Livewire action)
    // ======================
    public function applyFilter(): void
    {
        // Security: ensure selected regions are valid for user
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $this->selectedRegions = array_values(
                array_intersect($this->selectedRegions, $user->region_code)
            );
            if (empty($this->selectedRegions)) {
                $this->selectedRegions = $user->region_code;
            }
        }

        if (empty($this->selectedRegions)) {
            $this->selectedRegions = array_keys($this->regionsOption);
        }

        $this->resetPage();
        $this->loadDashboardData();
        $this->showFilterModal = false;
        $this->dispatch('charts-updated');
    }

    public function selectAllRegions(): void
    {
        $this->selectedRegions = array_keys($this->regionsOption);
    }

    public function clearAllRegions(): void
    {
        $this->selectedRegions = [];
    }

    public function openFilterModal(): void
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal(): void
    {
        $this->showFilterModal = false;
    }

    // ======================
    // MAIN DATA LOADER
    // ======================
    private function loadDashboardData(): void
    {
        $this->loadKpiData();
        $this->loadChartData();
        $this->loadTableData();
        $this->buildInsights();
    }

    // ======================
    // KPI DATA
    // ======================
    private function loadKpiData(): void
    {
        // Current Year aggregates
        $ty = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($ty);
        $tySums = $ty->selectRaw('
            COALESCE(SUM(actual), 0) AS total_actual,
            COALESCE(SUM(target), 0) AS total_target
        ')->first();

        // Last Year aggregates
        $ly = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($ly);
        $lySums = $ly->selectRaw('COALESCE(SUM(actual), 0) AS total_actual')->first();

        $totalActualTY = (float) ($tySums->total_actual ?? 0);
        $totalTarget   = (float) ($tySums->total_target ?? 0);
        $totalActualLY = (float) ($lySums->total_actual ?? 0);

        // Growth %
        $growthPct = $totalActualLY > 0
            ? (($totalActualTY - $totalActualLY) / $totalActualLY) * 100
            : 0;

        // Achievement %
        $achievementPct = $totalTarget > 0
            ? ($totalActualTY / $totalTarget) * 100
            : 0;

        // Gap vs target
        $gapVsTarget = $totalActualTY - $totalTarget;

        // Gap vs LY
        $gapVsLY = $totalActualTY - $totalActualLY;

        // Avg monthly sales (TY)
        $monthCount = $this->selectedMonthTo - $this->selectedMonthFrom + 1;
        $avgMonthlySales = $monthCount > 0 ? $totalActualTY / $monthCount : 0;

        // Avg monthly LY
        $avgMonthlyLY = $monthCount > 0 ? $totalActualLY / $monthCount : 0;
        $avgMonthlyGrowth = $avgMonthlyLY > 0
            ? (($avgMonthlySales - $avgMonthlyLY) / $avgMonthlyLY) * 100
            : 0;

        $this->kpiData = [
            'total_actual_ty'    => $totalActualTY,
            'total_actual_ly'    => $totalActualLY,
            'total_target'       => $totalTarget,
            'growth_pct'         => round($growthPct, 2),
            'achievement_pct'    => round($achievementPct, 2),
            'gap_vs_target'      => $gapVsTarget,
            'gap_vs_ly'          => $gapVsLY,
            'avg_monthly_sales'  => $avgMonthlySales,
            'avg_monthly_growth' => round($avgMonthlyGrowth, 2),
        ];
    }

    // ======================
    // CHART DATA
    // ======================
    private function loadChartData(): void
    {
        $this->buildRegionContributionChart();
        $this->buildSalesTrendChart();
        $this->buildMonthlyBarChart();
        $this->buildGrowthAreaChart();
        $this->buildRegionHBarChart();
        $this->buildComboChart();
    }

    private function buildRegionContributionChart(): void
    {
        $q = DB::table('v_sellinvstarget');
        $this->applySnapshotFilters($q);
        $rows = $q->selectRaw('region, SUM(actual) AS total')
                  ->groupBy('region')
                  ->orderByDesc('total')
                  ->get();

        $this->chartRegionContribution = json_encode([
            'labels' => $rows->pluck('region')->toArray(),
            'series' => $rows->map(fn($r) => round((float)$r->total))->toArray(),
        ]);
    }

    private function buildSalesTrendChart(): void
    {
        // TY
        $qTY = DB::table('v_sellinvstarget');
        $this->applyTrendFilters($qTY);
        $tyRows = $qTY->selectRaw("EXTRACT(MONTH FROM bulan)::int AS month_num, SUM(actual) AS total")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('total', 'month_num');

        // LY
        $qLY = DB::table('v_sellinvstarget');
        $this->applyTrendLastYearFilters($qLY);
        $lyRows = $qLY->selectRaw("EXTRACT(MONTH FROM bulan)::int AS month_num, SUM(actual) AS total")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('total', 'month_num');

        $months = range(1, 12);
        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $labels = array_map(fn($m) => $monthNames[$m - 1], $months);
        $tyData = array_map(fn($m) => round((float)($tyRows[$m] ?? 0)), $months);
        $lyData = array_map(fn($m) => round((float)($lyRows[$m] ?? 0)), $months);

        $this->chartSalesTrend = json_encode([
            'labels' => $labels,
            'ty'     => $tyData,
            'ly'     => $lyData,
        ]);
    }

    private function buildMonthlyBarChart(): void
    {
        $qTY = DB::table('v_sellinvstarget');
        $this->applyTrendFilters($qTY);
        $rows = $qTY->selectRaw("EXTRACT(MONTH FROM bulan)::int AS month_num, SUM(actual) AS actual, SUM(target) AS target")
                    ->groupBy('month_num')
                    ->orderBy('month_num')
                    ->get()
                    ->keyBy('month_num');

        $months = range(1, 12);
        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $labels  = array_map(fn($m) => $monthNames[$m - 1], $months);
        $actuals = array_map(fn($m) => round((float)($rows[$m]->actual ?? 0)), $months);
        $targets = array_map(fn($m) => round((float)($rows[$m]->target ?? 0)), $months);
        $achievements = array_map(function($m) use ($rows) {
            $act = (float)($rows[$m]->actual ?? 0);
            $tgt = (float)($rows[$m]->target ?? 0);
            if ($tgt <= 0) return null;
            return round(($act / $tgt) * 100, 2);
        }, $months);

        $this->chartMonthlyBar = json_encode([
            'labels'  => $labels,
            'actuals' => $actuals,
            'targets' => $targets,
            'achievements' => $achievements,
        ]);
    }

    private function buildGrowthAreaChart(): void
    {
        $qTY = DB::table('v_sellinvstarget');
        $this->applyTrendFilters($qTY);
        $tyRows = $qTY->selectRaw("EXTRACT(MONTH FROM bulan)::int AS month_num, SUM(actual) AS total")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('total', 'month_num');

        $qLY = DB::table('v_sellinvstarget');
        $this->applyTrendLastYearFilters($qLY);
        $lyRows = $qLY->selectRaw("EXTRACT(MONTH FROM bulan)::int AS month_num, SUM(actual) AS total")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('total', 'month_num');

        $months     = range(1, 12);
        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $labels     = array_map(fn($m) => $monthNames[$m - 1], $months);
        $growthData = array_map(function ($m) use ($tyRows, $lyRows) {
            if (!$tyRows->has($m)) return null;          // No TY data yet — gap in chart
            $ty = (float)$tyRows[$m];
            $ly = (float)($lyRows[$m] ?? 0);
            if ($ly === 0.0) return null;                // Cannot compute growth without LY base
            return round((($ty - $ly) / $ly) * 100, 2);
        }, $months);

        $this->chartGrowthArea = json_encode([
            'labels' => $labels,
            'growth' => $growthData,
        ]);
    }

    private function buildRegionHBarChart(): void
    {
        $q = DB::table('v_sellinvstarget');
        $this->applySnapshotFilters($q);
        $rows = $q->selectRaw('region, SUM(actual) AS actual, SUM(target) AS target')
                  ->groupBy('region')
                  ->orderByDesc('actual')
                  ->get();

        $this->chartRegionHBar = json_encode([
            'labels'  => $rows->pluck('region')->toArray(),
            'actuals' => $rows->map(fn($r) => round((float)$r->actual))->toArray(),
            'targets' => $rows->map(fn($r) => round((float)$r->target))->toArray(),
        ]);
    }

    private function buildComboChart(): void
    {
        $salesTrend = json_decode($this->chartSalesTrend, true);
        $growthArea = json_decode($this->chartGrowthArea, true);

        if (!$salesTrend || !$growthArea) {
            $this->chartCombo = json_encode(['labels' => [], 'ty' => [], 'ly' => [], 'growth' => []]);
            return;
        }

        // Sanitize growth: keep null (= gap in chart), replace non-finite with null
        $growth = array_map(
            fn($v) => $v === null ? null : ((is_numeric($v) && is_finite($v)) ? round((float)$v, 2) : null),
            $growthArea['growth'] ?? []
        );

        $this->chartCombo = json_encode([
            'labels' => $salesTrend['labels'],
            'ty'     => $salesTrend['ty'],
            'ly'     => $salesTrend['ly'],
            'growth' => $growth,
        ]);
    }

    // ======================
    // TABLE DATA
    // ======================

    private function loadTableData(): void
    {
        $this->loadTopByAch();
        $this->loadTopByGrowth();
        $this->loadGapVsTarget();
        $this->loadGapYoY();
        $this->loadYoyComparison();
    }

    private function loadTopByAch(): void
    {
        $q = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($q);
        $rows = $q->selectRaw('region, SUM(actual) AS actual, SUM(target) AS target')
                  ->groupBy('region')
                  ->orderByRaw('(SUM(actual) / NULLIF(SUM(target), 0)) DESC')
                  ->limit(10)
                  ->get();

        $this->topByAch = $rows->map(fn($r) => [
            'region' => $r->region,
            'target' => (float)$r->target,
            'actual' => (float)$r->actual,
            'ach'    => $r->target > 0 ? round(($r->actual / $r->target) * 100, 2) : 0,
        ])->toArray();
    }

    private function loadTopByGrowth(): void
    {
        $qLY = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($qLY);
        $lyMap = $qLY->selectRaw('region, SUM(actual) AS actual')
                     ->groupBy('region')
                     ->pluck('actual', 'region');

        $qTY = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($qTY);
        $rows = $qTY->selectRaw('region, SUM(actual) AS actual')
                    ->groupBy('region')
                    ->get();

        $this->topByGrowth = $rows->map(function ($r) use ($lyMap) {
            $ly = (float)($lyMap[$r->region] ?? 0);
            $ty = (float)$r->actual;
            return [
                'region' => $r->region,
                'ly'     => $ly,
                'ty'     => $ty,
                'growth' => $ly > 0 ? round((($ty - $ly) / $ly) * 100, 2) : 0,
            ];
        })->sortByDesc('growth')->values()->take(10)->toArray();
    }

    private function loadGapVsTarget(): void
    {
        $q = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($q);
        $rows = $q->selectRaw('region, SUM(actual) AS actual, SUM(target) AS target')
                  ->groupBy('region')
                  ->orderByRaw('SUM(actual) - SUM(target) DESC')
                  ->limit(10)
                  ->get();

        $this->gapVsTarget = $rows->map(fn($r) => [
            'region' => $r->region,
            'target' => (float)$r->target,
            'actual' => (float)$r->actual,
            'gap'    => (float)$r->actual - (float)$r->target,
        ])->toArray();
    }

    private function loadGapYoY(): void
    {
        $qLY = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($qLY);
        $lyMap = $qLY->selectRaw('region, SUM(actual) AS actual')
                     ->groupBy('region')
                     ->pluck('actual', 'region');

        $qTY = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($qTY);
        $rows = $qTY->selectRaw('region, SUM(actual) AS actual')
                    ->groupBy('region')
                    ->get();

        $this->gapYoY = $rows->map(function ($r) use ($lyMap) {
            $ly = (float)($lyMap[$r->region] ?? 0);
            $ty = (float)$r->actual;
            return [
                'region' => $r->region,
                'ly'     => $ly,
                'ty'     => $ty,
                'gap'    => $ty - $ly,
            ];
        })->sortByDesc('gap')->values()->take(10)->toArray();
    }

    private function loadYoyComparison(): void
    {
        $qTY = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($qTY);
        $tyRows = $qTY->selectRaw("EXTRACT(MONTH FROM bulan) AS month_num, SUM(actual) AS actual")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('actual', 'month_num');

        $qLY = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($qLY);
        $lyRows = $qLY->selectRaw("EXTRACT(MONTH FROM bulan) AS month_num, SUM(actual) AS actual")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('actual', 'month_num');

        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $this->yoyComparison = [];

        foreach (range($this->selectedMonthFrom, $this->selectedMonthTo) as $m) {
            $ty     = (float)($tyRows[$m] ?? 0);
            $ly     = (float)($lyRows[$m] ?? 0);
            $growth = $ly > 0 ? round((($ty - $ly) / $ly) * 100, 2) : 0;
            $this->yoyComparison[] = [
                'month'      => $monthNames[$m - 1],
                'sales_ly'   => $ly,
                'sales_ty'   => $ty,
                'growth_pct' => $growth,
            ];
        }
    }

    // ======================
    // AUTO INSIGHTS
    // ======================
    private function buildInsights(): void
    {
        // Best performing region (highest ach)
        $best = collect($this->topByAch)->sortByDesc('ach')->first();

        // Worst region (lowest achievement %)
        $worst = collect($this->topByAch)->sortBy('ach')->first();

        // Highest growth region
        $highestGrowth = collect($this->topByGrowth)->sortByDesc('growth')->first();

        // Biggest negative gap
        $biggestNegGap = collect($this->gapVsTarget)->sortBy('gap')->first();

        $this->insights = [
            'best_region' => $best ? [
                'title'    => 'Best Performing Region',
                'value'    => $best['region'],
                'sub'      => 'Achievement: ' . number_format($best['ach'], 2) . '%',
                'type'     => 'success',
            ] : null,
            'worst_region' => $worst ? [
                'title'    => 'Worst Achievement Region',
                'value'    => $worst['region'],
                'sub'      => 'Achievement: ' . number_format($worst['ach'], 2) . '%',
                'type'     => 'error',
            ] : null,
            'highest_growth' => $highestGrowth ? [
                'title'    => 'Highest Growth Region',
                'value'    => $highestGrowth['region'],
                'sub'      => 'Growth: +' . number_format($highestGrowth['growth'], 2) . '%',
                'type'     => 'info',
            ] : null,
            'biggest_neg_gap' => $biggestNegGap ? [
                'title'    => 'Biggest Negative Gap',
                'value'    => $biggestNegGap['region'],
                'sub'      => 'Gap: ' . number_format($biggestNegGap['gap'], 0, ',', '.'),
                'type'     => 'warning',
            ] : null,
        ];
    }

    // ======================
    // RENDER
    // ======================
    public function render()
    {
        // Paginated main detail table
        $detailQuery = DB::table('v_sellinvstarget');
        $this->applyTrendFilters($detailQuery);

        // Apply search to detail table
        if (!empty($this->searchDetail)) {
            $s = $this->searchDetail;
            $detailQuery->whereRaw("TO_CHAR(bulan, 'Mon YYYY') ilike ?", ["%{$s}%"]);
        }

        // LY actuals keyed by (month_num)
        $lyQuery = DB::table('v_sellinvstarget');
        $this->applyTrendLastYearFilters($lyQuery);
        $lyMap = $lyQuery->selectRaw("
                EXTRACT(MONTH FROM bulan) AS month_num,
                SUM(actual) AS ly_actual
            ")
            ->groupBy('month_num')
            ->get()
            ->keyBy(fn($r) => "{$r->month_num}");

        // Apply search to detail table
        if (!empty($this->searchDetail)) {
            $s = $this->searchDetail;
            $detailQuery->whereRaw("TO_CHAR(bulan, 'Mon YYYY') ilike ?", ["%{$s}%"]);
        }

        $details = $detailQuery
            ->selectRaw("
                TO_CHAR(bulan, 'Mon YYYY') AS bulan_label,
                EXTRACT(MONTH FROM bulan) AS month_num,
                SUM(target) AS target,
                SUM(actual) AS actual
            ")
            ->groupBy('bulan_label', 'month_num', 'bulan')
            ->orderBy('month_num')
            ->paginate(20);

        // Attach computed columns
        $details->getCollection()->transform(function ($row) use ($lyMap) {
            $key             = "{$row->month_num}";
            $lyActual        = (float)($lyMap[$key]->ly_actual ?? 0);
            $actual          = (float)$row->actual;
            $target          = (float)$row->target;
            $row->ly_actual  = $lyActual;
            $row->ach_pct    = $target > 0 ? round(($actual / $target) * 100, 2) : 0;
            $row->growth_pct = $lyActual > 0 ? round((($actual - $lyActual) / $lyActual) * 100, 2) : 0;
            $row->gap_value  = $actual - $target;
            $row->gap_vs_ly  = $actual - $lyActual;
            return $row;
        });

        return view('livewire.dashboard.analytics-dashboard', [
            'details'          => $details,
            'topByAchData'     => $this->topByAch,
            'topByGrowthData'  => $this->topByGrowth,
            'gapVsTargetData'  => $this->gapVsTarget,
            'gapYoYData'       => $this->gapYoY,
        ])->layout('layouts.app', ['title' => 'Analytics Dashboard']);
    }
}
