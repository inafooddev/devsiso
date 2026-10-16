<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeTrendExport;

class Trend extends Component
{
    use EnforcesMenuPermissions;

    #[Title('Trend Bulanan Kunjungan SE')]
    protected string $menuRoute = 'report.analisa-kunjungan-se.index';

    // Permissions & Access Levels
    public $canExport = false;
    public $accessLevel = 'nasional';
    public $allowedRegionCodes = [];
    public $allowedAreaCodes = [];
    public $allowedDistributorCodes = [];

    // Filter Options
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $yearOptions = [];

    // Filter Selections
    #[Url(as: 'year')]
    public $selectedYear;

    #[Url(as: 'call_type')]
    public $callType = 'ALL'; // 'ALL', 'PJP', 'XC'

    #[Url(as: 'region')]
    public $selectedRegion = '';

    #[Url(as: 'area')]
    public $selectedArea = '';

    #[Url(as: 'distributor')]
    public $selectedDistributor = '';

    // Applied Filters
    public $appliedYear;
    public $appliedCallType = 'ALL';
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedDistributor = '';

    public function mount()
    {
        $this->authorizeAction('can_view');
        $this->canExport = auth()->check() && auth()->user()->hasMenuAccess($this->menuRoute, 'can_export');
        $this->resolveUserAccess();

        $currentYear = (int) date('Y');
        $this->selectedYear = $this->selectedYear ? (int)$this->selectedYear : $currentYear;
        $this->appliedYear = $this->selectedYear;

        // Build last 5 years options
        for ($y = $currentYear; $y >= $currentYear - 4; $y--) {
            $this->yearOptions[] = $y;
        }

        $this->loadRegions();

        if (!empty($this->selectedRegion)) {
            $this->updatedSelectedRegion($this->selectedRegion);
            if (!empty($this->selectedArea)) {
                $this->updatedSelectedArea($this->selectedArea);
            }
        }

        $this->applyFilter();
    }

    protected function resolveUserAccess(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->accessLevel = $user->getAccessLevel();

        if ($this->accessLevel === 'region') {
            $this->allowedRegionCodes = (array) ($user->region_code ?? []);
        } elseif ($this->accessLevel === 'area') {
            $this->allowedAreaCodes = (array) ($user->area_code ?? []);
            $this->allowedRegionCodes = DB::table('master_areas')
                ->whereIn('area_code', $this->allowedAreaCodes)
                ->pluck('region_code')
                ->unique()
                ->filter()
                ->values()
                ->toArray();
        } elseif ($this->accessLevel === 'supervisor') {
            $hierarchy = DB::table('vw_distributor_hierarchy')
                ->where('supervisor_code', $user->supervisor_code)
                ->get();

            $this->allowedRegionCodes = $hierarchy->pluck('region_code')->unique()->filter()->values()->toArray();
            $this->allowedAreaCodes = $hierarchy->pluck('area_code')->unique()->filter()->values()->toArray();
            $this->allowedDistributorCodes = $hierarchy->pluck('distributor_code')->unique()->filter()->values()->toArray();
        }
    }

    protected function loadRegions()
    {
        $query = DB::table('master_regions')
            ->select('region_code', 'region_name')
            ->where('region_code', '!=', 'HOINA')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name');

        if ($this->accessLevel !== 'nasional' && !empty($this->allowedRegionCodes)) {
            $query->whereIn('region_code', $this->allowedRegionCodes);
        }

        $this->regions = $query->get()->toArray();

        // If user is locked to only 1 region, auto select
        if (count($this->regions) === 1 && empty($this->selectedRegion)) {
            $this->selectedRegion = $this->regions[0]->region_code;
            $this->updatedSelectedRegion($this->selectedRegion);
        }
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->areas = [];
        $this->distributors = [];

        if ($value) {
            if ($this->accessLevel !== 'nasional' && !empty($this->allowedRegionCodes) && !in_array($value, $this->allowedRegionCodes)) {
                $this->selectedRegion = '';
                return;
            }

            $query = DB::table('master_areas')
                ->where('region_code', $value)
                ->whereNotNull('area_code')
                ->select('area_code', 'area_name')
                ->distinct()
                ->orderBy('area_name');

            if (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes)) {
                $query->whereIn('area_code', $this->allowedAreaCodes);
            }

            $this->areas = $query->get()->toArray();

            if (count($this->areas) === 1 && empty($this->selectedArea)) {
                $this->selectedArea = $this->areas[0]->area_code;
                $this->updatedSelectedArea($this->selectedArea);
            }
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->selectedDistributor = '';
        $this->distributors = [];

        if ($this->selectedRegion) {
            if ($value && in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes) && !in_array($value, $this->allowedAreaCodes)) {
                $this->selectedArea = '';
                return;
            }

            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('distributor_code', '!=', 'HOINA')
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct()
                ->orderBy('distributor_name');

            if (!empty($value)) {
                $query->where('area_code', $value);
            } elseif (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes)) {
                $query->whereIn('area_code', $this->allowedAreaCodes);
            }

            if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
                $query->whereIn('distributor_code', $this->allowedDistributorCodes);
            }

            $this->distributors = $query->get()->toArray();

            if (count($this->distributors) === 1 && empty($this->selectedDistributor)) {
                $this->selectedDistributor = $this->distributors[0]->distributor_code;
            }
        }
    }

    public function setCallType(string $type)
    {
        if (in_array($type, ['ALL', 'PJP', 'XC'])) {
            $this->callType = $type;
            $this->appliedCallType = $type;
            $trend = $this->monthlyTrendData(true);
            $this->dispatch('chart-data-updated', trend: $trend);
        }
    }

    public function applyFilter()
    {
        $this->appliedYear = (int)$this->selectedYear;
        $this->appliedCallType = $this->callType;
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor;
        $trend = $this->monthlyTrendData(true);
        $this->dispatch('chart-data-updated', trend: $trend);
    }

    public function resetFilter()
    {
        $currentYear = (int) date('Y');
        $this->selectedYear = $currentYear;
        $this->callType = 'ALL';
        $this->selectedRegion = count($this->regions) === 1 ? $this->regions[0]->region_code : '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';

        if ($this->selectedRegion) {
            $this->updatedSelectedRegion($this->selectedRegion);
        }

        $this->applyFilter();
    }

    public function resetFilters()
    {
        $this->resetFilter();
    }

    protected function getCacheKey(): string
    {
        $userId = auth()->id() ?? 'guest';
        return "trend_data_{$userId}_{$this->appliedYear}_{$this->appliedCallType}_{$this->appliedRegion}_{$this->appliedArea}_{$this->appliedDistributor}";
    }

    /**
     * Compute or retrieve 12-month aggregated trend data (Jan - Dec)
     */
    public function monthlyTrendData(bool $forceRefresh = false)
    {
        $cacheKey = $this->getCacheKey();

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 600, function () {
            return $this->computeMonthlyTrendData();
        });
    }

    protected function computeMonthlyTrendData()
    {
        $year = (int)$this->appliedYear;
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        $query = DB::table('rpt_visit_an_h as rvah')
            ->where('rvah.RID', '!=', 'HOINA')
            ->whereBetween('rvah.TANGGAL', [$startDate, $endDate]);

        // Territory filtering
        if (!empty($this->appliedRegion)) {
            $query->where('rvah.RID', $this->appliedRegion);
        } elseif ($this->accessLevel === 'region' && !empty($this->allowedRegionCodes)) {
            $query->whereIn('rvah.RID', $this->allowedRegionCodes);
        }

        if (!empty($this->appliedArea)) {
            $query->where('rvah.EID', $this->appliedArea);
        } elseif (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes)) {
            $query->whereIn('rvah.EID', $this->allowedAreaCodes);
        }

        if (!empty($this->appliedDistributor)) {
            $query->where('rvah.BID', $this->appliedDistributor);
        } elseif ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
            $query->whereIn('rvah.BID', $this->allowedDistributorCodes);
        }

        // Subquery: Distinct per hari per toko (1 Toko per Hari = Maksimal 1 Call target & realisasi)
        $subQuery = $query->select(
            DB::raw('EXTRACT(MONTH FROM rvah."TANGGAL"::date)::int as bulan'),
            DB::raw('rvah."TANGGAL"::date as tanggal'),
            'rvah.CUSTNO as custno',
            'rvah.MUID as muid',
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" = 'R' THEN 1 ELSE 0 END) as is_pjp"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" != 'R' THEN 1 ELSE 0 END) as is_xc"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as is_visit"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as is_ec"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" = 'R' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as is_pjp_visit"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" = 'R' AND rvah.\"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as is_pjp_ec"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" != 'R' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as is_xc_visit"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" != 'R' AND rvah.\"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as is_xc_ec"),
            DB::raw('SUM(CASE WHEN rvah."ORDER_VAL" > 0 AND rvah."ORDER_VAL" < 5000000000 THEN rvah."ORDER_VAL" ELSE 0 END) as cust_order_val')
        )
        ->groupBy(
            DB::raw('EXTRACT(MONTH FROM rvah."TANGGAL"::date)'),
            DB::raw('rvah."TANGGAL"::date'),
            'rvah.CUSTNO',
            'rvah.MUID'
        );

        $dbRows = DB::table(DB::raw("({$subQuery->toSql()}) as d"))
            ->mergeBindings($subQuery)
            ->select(
                'bulan',
                DB::raw('COUNT(DISTINCT muid) as active_se'),
                DB::raw('COUNT(DISTINCT tanggal) as active_days'),
                DB::raw('COUNT(*) as total_pc'),
                DB::raw('SUM(is_visit) as total_ac'),
                DB::raw('SUM(is_ec) as total_ec'),
                DB::raw('SUM(is_pjp) as pjp_pc'),
                DB::raw('SUM(is_pjp_visit) as pjp_ac'),
                DB::raw('SUM(is_pjp_ec) as pjp_ec'),
                DB::raw('SUM(is_xc) as xc_pc'),
                DB::raw('SUM(is_xc_visit) as xc_ac'),
                DB::raw('SUM(is_xc_ec) as xc_ec'),
                DB::raw('SUM(cust_order_val) as total_order_val')
            )
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $shortNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $result = collect();
        $callType = $this->appliedCallType;
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');

        for ($m = 1; $m <= 12; $m++) {
            $row = $dbRows->get($m);
            $isFuture = ($year === $currentYear && $m > $currentMonth);

            $pc = 0;
            $ac = 0;
            $ec = 0;
            $val = 0;
            $days = 0;
            $se = 0;

            if ($row) {
                $days = (int)$row->active_days;
                $se = (int)$row->active_se;
                $val = (float)$row->total_order_val;

                if ($callType === 'PJP') {
                    $pc = (int)$row->pjp_pc;
                    $ac = (int)$row->pjp_ac;
                    $ec = (int)$row->pjp_ec;
                } elseif ($callType === 'XC') {
                    $pc = (int)$row->xc_pc;
                    $ac = (int)$row->xc_ac;
                    $ec = (int)$row->xc_ec;
                } else {
                    $pc = (int)$row->total_pc;
                    $ac = (int)$row->total_ac;
                    $ec = (int)$row->total_ec;
                }
            }

            if ($isFuture && $pc === 0) {
                $acPercent = null;
                $ecPercent = null;
                $ecPcPercent = null;
            } else {
                $acPercent = $pc > 0 ? round(($ac / $pc) * 100, 1) : 0;
                $ecPercent = $ac > 0 ? round(($ec / $ac) * 100, 1) : 0;
                $ecPcPercent = $pc > 0 ? round(($ec / $pc) * 100, 1) : 0;
            }

            $dropCall = max(0, $pc - $ac);
            $unproductiveCall = max(0, $ac - $ec);

            $result->push((object)[
                'month_num' => $m,
                'month_name' => $monthNames[$m],
                'short_name' => $shortNames[$m],
                'is_future' => $isFuture,
                'active_days' => $days,
                'active_se' => $se,
                'pc' => $pc,
                'ac' => $ac,
                'ec' => $ec,
                'ac_percent' => $acPercent,
                'ec_percent' => $ecPercent,
                'ec_pc_percent' => $ecPcPercent,
                'drop_call' => $dropCall,
                'unproductive_call' => $unproductiveCall,
                'order_val' => $val,
            ]);
        }

        return $result;
    }

    /**
     * Compute Grand Total for the year
     */
    #[Computed]
    public function yearTotals()
    {
        $data = $this->monthlyTrendData();

        $totalPc = $data->sum('pc');
        $totalAc = $data->sum('ac');
        $totalEc = $data->sum('ec');
        $totalVal = $data->sum('order_val');
        $totalDrop = $data->sum('drop_call');
        $totalUnproductive = $data->sum('unproductive_call');

        $acPercent = $totalPc > 0 ? round(($totalAc / $totalPc) * 100, 1) : 0;
        $ecPercent = $totalAc > 0 ? round(($totalEc / $totalAc) * 100, 1) : 0;
        $ecPcPercent = $totalPc > 0 ? round(($totalEc / $totalPc) * 100, 1) : 0;

        return (object)[
            'total_pc' => $totalPc,
            'total_ac' => $totalAc,
            'total_ec' => $totalEc,
            'ac_percent' => $acPercent,
            'ec_percent' => $ecPercent,
            'ec_pc_percent' => $ecPcPercent,
            'total_val' => $totalVal,
            'total_drop' => $totalDrop,
            'total_unproductive' => $totalUnproductive,
            'aov' => $totalEc > 0 ? round($totalVal / $totalEc) : 0,
        ];
    }

    public function exportExcel()
    {
        $this->authorizeAction('can_export');
        $rows = $this->monthlyTrendData();

        if ($rows->isEmpty()) {
            $this->dispatch('notify', msg: 'Tidak ada data untuk diexport.', type: 'error');
            return;
        }

        $filename = 'Trend_Bulanan_PC_AC_EC_' . $this->appliedYear . '_' . $this->appliedCallType . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AnalisaKunjunganSeTrendExport($rows, $this->yearTotals(), $this->appliedYear, $this->appliedCallType), $filename);
    }

    public function render()
    {
        $trend = $this->monthlyTrendData();
        $totals = $this->yearTotals();

        return view('livewire.report.analisa-kunjungan-se.trend', [
            'trendData' => $trend,
            'totals' => $totals,
        ])->layout('layouts.app');
    }
}
