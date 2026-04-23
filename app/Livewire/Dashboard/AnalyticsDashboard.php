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
    public array $underperformingRegions = [];
    public array $topRegions             = [];
    public array $monthlySummary         = [];
    public array $gapAnalysis            = [];
    public array $yoyComparison          = [];

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
    public string $searchDetail        = '';
    public string $searchUnderperform  = '';
    public string $searchTop           = '';
    public string $searchMonthly       = '';
    public string $searchGap           = '';
    public string $searchYoy           = '';

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
            ->select('region')
            ->distinct()
            ->orderBy('region');

        $this->applyRegionAccess($query, 'region');

        $this->regionsOption = $query->pluck('region')->toArray();

        // Auto-select all regions by default
        $this->selectedRegions = $this->regionsOption;

        $this->loadDashboardData();
    }

    // ======================
    // REGION ACCESS HELPER
    // ======================
    private function applyRegionAccess($query, string $column = 'region'): void
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
            $query->whereIn('region', $this->selectedRegions);
        }

        // Reg/Fest
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }

        // User access control
        $this->applyRegionAccess($query, 'region');
    }

    private function applyLastYearFilters($query): void
    {
        $lastYear = $this->selectedYear - 1;
        $query->whereYear('bulan', $lastYear);
        $query->whereRaw('EXTRACT(MONTH FROM bulan) >= ?', [$this->selectedMonthFrom])
              ->whereRaw('EXTRACT(MONTH FROM bulan) <= ?', [$this->selectedMonthTo]);

        if (!empty($this->selectedRegions)) {
            $query->whereIn('region', $this->selectedRegions);
        }

        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }

        $this->applyRegionAccess($query, 'region');
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
        $this->applyRegionAccess($query, 'region');
    }

    /**
     * Trend (TY): year + region + type — NO date range (shows full year).
     * Used by: Sales Trend, Monthly Bar, Growth Area, Combo.
     */
    private function applyTrendFilters($query): void
    {
        $query->whereYear('bulan', $this->selectedYear);
        if (!empty($this->selectedRegions)) {
            $query->whereIn('region', $this->selectedRegions);
        }
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }
        $this->applyRegionAccess($query, 'region');
    }

    /**
     * Trend (LY): previous year + region + type — NO date range.
     */
    private function applyTrendLastYearFilters($query): void
    {
        $query->whereYear('bulan', $this->selectedYear - 1);
        if (!empty($this->selectedRegions)) {
            $query->whereIn('region', $this->selectedRegions);
        }
        if ($this->selectedRegFest !== 'ALL') {
            $query->where('reg_fest', $this->selectedRegFest);
        }
        $this->applyRegionAccess($query, 'region');
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
            $this->selectedRegions = $this->regionsOption;
        }

        $this->resetPage();
        $this->loadDashboardData();
        $this->showFilterModal = false;
        $this->dispatch('charts-updated');
    }

    public function selectAllRegions(): void
    {
        $this->selectedRegions = $this->regionsOption;
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
        $this->loadUnderperforming();
        $this->loadTopRegions();
        $this->loadMonthlySummary();
        $this->loadGapAnalysis();
        $this->loadYoyComparison();
    }

    private function loadUnderperforming(): void
    {
        $q = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($q);
        $rows = $q->selectRaw('cabang, SUM(actual) AS actual, SUM(target) AS target')
                  ->groupBy('cabang')
                  ->havingRaw('SUM(target) > 0')
                  ->orderByRaw('(SUM(actual) / SUM(target)) ASC')
                  ->limit(10)
                  ->get();

        $this->underperformingRegions = $rows->map(function ($r) {
            $ach = $r->target > 0 ? round(($r->actual / $r->target) * 100, 2) : 0;
            $gap = $r->actual - $r->target;
            return [
                'cabang'          => $r->cabang,
                'target'          => (float)$r->target,
                'actual'          => (float)$r->actual,
                'gap'             => $gap,
                'achievement_pct' => $ach,
            ];
        })->toArray();
    }

    private function loadTopRegions(): void
    {
        // Total TY for contribution calculation
        $totalTY = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($totalTY);
        $grandTotal = (float)($totalTY->sum('actual') ?: 1);

        // LY by cabang
        $qLY = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($qLY);
        $lyByCabang = $qLY->selectRaw('cabang, SUM(actual) AS total')
                          ->groupBy('cabang')
                          ->pluck('total', 'cabang');

        $q = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($q);
        $rows = $q->selectRaw('cabang, SUM(actual) AS actual')
                  ->groupBy('cabang')
                  ->orderByDesc('actual')
                  ->limit(10)
                  ->get();

        $this->topRegions = $rows->map(function ($r) use ($lyByCabang, $grandTotal) {
            $ly           = (float)($lyByCabang[$r->cabang] ?? 0);
            $ty           = (float)$r->actual;
            $growth       = $ly > 0 ? round((($ty - $ly) / $ly) * 100, 2) : 0;
            $contribution = $grandTotal > 0 ? round(($ty / $grandTotal) * 100, 2) : 0;
            return [
                'cabang'           => $r->cabang,
                'actual'           => $ty,
                'growth_pct'       => $growth,
                'contribution_pct' => $contribution,
            ];
        })->toArray();
    }

    private function loadMonthlySummary(): void
    {
        $qTY = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($qTY);
        $tyRows = $qTY->selectRaw("EXTRACT(MONTH FROM bulan) AS month_num, SUM(actual) AS actual, SUM(target) AS target")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->get()
                      ->keyBy('month_num');

        $qLY = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($qLY);
        $lyRows = $qLY->selectRaw("EXTRACT(MONTH FROM bulan) AS month_num, SUM(actual) AS actual")
                      ->groupBy('month_num')
                      ->orderBy('month_num')
                      ->pluck('actual', 'month_num');

        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $this->monthlySummary = [];

        foreach (range($this->selectedMonthFrom, $this->selectedMonthTo) as $m) {
            $actual = (float)($tyRows[$m]->actual ?? 0);
            $target = (float)($tyRows[$m]->target ?? 0);
            $ly     = (float)($lyRows[$m] ?? 0);
            $growth = $ly > 0 ? round((($actual - $ly) / $ly) * 100, 2) : 0;
            $ach    = $target > 0 ? round(($actual / $target) * 100, 2) : 0;

            $this->monthlySummary[] = [
                'month'          => $monthNames[$m - 1],
                'actual'         => $actual,
                'target'         => $target,
                'growth_pct'     => $growth,
                'achievement_pct'=> $ach,
            ];
        }
    }

    private function loadGapAnalysis(): void
    {
        $q = DB::table('v_sellinvstarget');
        $this->applyBaseFilters($q);
        $rows = $q->selectRaw("
                cabang,
                TO_CHAR(bulan, 'Mon YYYY') AS month_label,
                EXTRACT(MONTH FROM bulan) AS month_num,
                SUM(actual) AS actual,
                SUM(target) AS target
            ")
            ->groupBy('cabang', 'month_label', 'month_num', 'bulan')
            ->orderBy('month_num')
            ->orderBy('cabang')
            ->limit(50)
            ->get();

        $this->gapAnalysis = $rows->map(function ($r) {
            $gap     = (float)$r->actual - (float)$r->target;
            $gapPct  = $r->target > 0 ? round(($gap / $r->target) * 100, 2) : 0;
            return [
                'cabang'      => $r->cabang,
                'month'       => $r->month_label,
                'gap_value'   => $gap,
                'gap_pct'     => $gapPct,
                'actual'      => (float)$r->actual,
                'target'      => (float)$r->target,
            ];
        })->toArray();
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
        // Best performing region (highest actual)
        $best = collect($this->topRegions)->sortByDesc('actual')->first();

        // Worst region (lowest achievement %)
        $worst = collect($this->underperformingRegions)->sortBy('achievement_pct')->first();

        // Highest growth month
        $highestGrowthMonth = collect($this->monthlySummary)->sortByDesc('growth_pct')->first();

        // Biggest negative gap
        $biggestNegGap = collect($this->gapAnalysis)->filter(fn($r) => $r['gap_value'] < 0)
                                                     ->sortBy('gap_value')
                                                     ->first();

        $this->insights = [
            'best_region' => $best ? [
                'title'    => 'Best Performing Region',
                'value'    => $best['cabang'],
                'sub'      => 'Sales: ' . number_format($best['actual'], 0, ',', '.'),
                'type'     => 'success',
            ] : null,
            'worst_region' => $worst ? [
                'title'    => 'Worst Performing Region',
                'value'    => $worst['cabang'],
                'sub'      => 'Achievement: ' . number_format($worst['achievement_pct'], 2) . '%',
                'type'     => 'error',
            ] : null,
            'highest_growth_month' => $highestGrowthMonth ? [
                'title'    => 'Highest Growth Month',
                'value'    => $highestGrowthMonth['month'],
                'sub'      => 'Growth: +' . number_format($highestGrowthMonth['growth_pct'], 2) . '%',
                'type'     => 'info',
            ] : null,
            'biggest_neg_gap' => $biggestNegGap ? [
                'title'    => 'Biggest Negative Gap',
                'value'    => $biggestNegGap['cabang'],
                'sub'      => 'Gap: ' . number_format($biggestNegGap['gap_value'], 0, ',', '.'),
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
        $this->applyBaseFilters($detailQuery);

        // Apply search to detail table
        if (!empty($this->searchDetail)) {
            $s = $this->searchDetail;
            $detailQuery->where(function ($q) use ($s) {
                $q->where('region', 'ilike', "%{$s}%")
                  ->orWhere('cabang', 'ilike', "%{$s}%");
            });
        }

        // LY actuals keyed by (month_num, region, cabang)
        $lyQuery = DB::table('v_sellinvstarget');
        $this->applyLastYearFilters($lyQuery);
        $lyMap = $lyQuery->selectRaw("
                EXTRACT(MONTH FROM bulan) AS month_num,
                region,
                cabang,
                SUM(actual) AS ly_actual
            ")
            ->groupBy('month_num', 'region', 'cabang')
            ->get()
            ->keyBy(fn($r) => "{$r->month_num}_{$r->region}_{$r->cabang}");

        $details = $detailQuery
            ->selectRaw("
                TO_CHAR(bulan, 'Mon YYYY') AS bulan_label,
                EXTRACT(MONTH FROM bulan) AS month_num,
                region,
                cabang,
                SUM(target) AS target,
                SUM(actual) AS actual
            ")
            ->groupBy('bulan_label', 'month_num', 'region', 'cabang', 'bulan')
            ->orderBy('month_num')
            ->orderBy('region')
            ->orderBy('cabang')
            ->paginate(20);

        // Attach computed columns
        $details->getCollection()->transform(function ($row) use ($lyMap) {
            $key             = "{$row->month_num}_{$row->region}_{$row->cabang}";
            $lyActual        = (float)($lyMap[$key]->ly_actual ?? 0);
            $actual          = (float)$row->actual;
            $target          = (float)$row->target;
            $row->ly_actual  = $lyActual;
            $row->ach_pct    = $target > 0 ? round(($actual / $target) * 100, 2) : 0;
            $row->growth_pct = $lyActual > 0 ? round((($actual - $lyActual) / $lyActual) * 100, 2) : 0;
            $row->gap_value  = $actual - $target;
            return $row;
        });

        // --- Filter array tables by search strings ---
        $s = strtolower($this->searchUnderperform);
        $underperforming = $s
            ? array_values(array_filter($this->underperformingRegions, fn($r) => str_contains(strtolower($r['cabang']), $s)))
            : $this->underperformingRegions;

        $s = strtolower($this->searchTop);
        $topRegions = $s
            ? array_values(array_filter($this->topRegions, fn($r) => str_contains(strtolower($r['cabang']), $s)))
            : $this->topRegions;

        $s = strtolower($this->searchMonthly);
        $monthlySummary = $s
            ? array_values(array_filter($this->monthlySummary, fn($r) => str_contains(strtolower($r['month']), $s)))
            : $this->monthlySummary;

        $s = strtolower($this->searchGap);
        $gapAnalysis = $s
            ? array_values(array_filter($this->gapAnalysis, fn($r) =>
                str_contains(strtolower($r['cabang']), $s) || str_contains(strtolower($r['month']), $s)
              ))
            : $this->gapAnalysis;

        $s = strtolower($this->searchYoy);
        $yoyComparison = $s
            ? array_values(array_filter($this->yoyComparison, fn($r) => str_contains(strtolower($r['month']), $s)))
            : $this->yoyComparison;

        return view('livewire.dashboard.analytics-dashboard', [
            'details'             => $details,
            'underperformingData' => $underperforming,
            'topRegionsData'      => $topRegions,
            'monthlySummaryData'  => $monthlySummary,
            'gapAnalysisData'     => $gapAnalysis,
            'yoyComparisonData'   => $yoyComparison,
        ])->layout('layouts.app', ['title' => 'Analytics Dashboard']);
    }
}
