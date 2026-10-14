<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeExport;

class Index extends Component
{
    use EnforcesMenuPermissions, WithPagination;

    #[Title('Analisa Kunjungan SE')]
    protected string $menuRoute = 'report.analisa-kunjungan-se.index';

    public $activeTab = 'detail';

    // Filter Options
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $salesmen = [];

    // Filter Selections
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    public $selectedSalesman = '';
    public $startDate = '';
    public $endDate = '';

    // Applied Filters
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedDistributor = '';
    public $appliedSalesman = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';

    public function mount()
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->loadRegions();
    }

    public function loadRegions()
    {
        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();

        $query = DB::table('master_regions')
            ->select('region_code', 'region_name')
            ->where('region_code', '!=', 'HOINA')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name');

        if ($accessLevel === 'region' && !empty($user->region_code)) {
            $query->whereIn('region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area' && !empty($user->region_code)) {
            $query->whereIn('region_code', (array) $user->region_code);
        }

        $this->regions = $query->get()->toArray();

        if (count($this->regions) === 1 && empty($this->selectedRegion)) {
            $this->selectedRegion = $this->regions[0]->region_code;
            $this->updatedSelectedRegion($this->selectedRegion);
        }
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->areas = [];
        $this->distributors = [];
        $this->salesmen = [];

        if ($value) {
            $user = auth()->user();
            $accessLevel = $user->getAccessLevel();

            $query = DB::table('master_areas')
                ->where('region_code', $value)
                ->whereNotNull('area_code')
                ->select('area_code', 'area_name')
                ->distinct()
                ->orderBy('area_name');

            if ($accessLevel === 'area' && !empty($user->area_code)) {
                $query->whereIn('area_code', (array) $user->area_code);
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
        $this->selectedSalesman = '';
        $this->distributors = [];
        $this->salesmen = [];

        if ($value && $this->selectedRegion) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('area_code', $value)
                ->where('distributor_code', '!=', 'HOINA')
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct()
                ->orderBy('distributor_name');

            $this->distributors = $query->get()->toArray();

            if (count($this->distributors) === 1 && empty($this->selectedDistributor)) {
                $this->selectedDistributor = $this->distributors[0]->distributor_code;
                $this->updatedSelectedDistributor($this->selectedDistributor);
            }
        }
    }

    public function updatedSelectedDistributor($value)
    {
        $this->selectedSalesman = '';
        $this->salesmen = [];

        if ($value) {
            // Load salesman SE aktif untuk distributor tersebut
            $query = DB::table('rpt_visit_an_h')
                ->where('BID', $value)
                ->where('RID', '!=', 'HOINA')
                ->whereNotNull('MUID')
                ->select('MUID as salesman_code', 'MUNAME as salesman_name')
                ->distinct()
                ->orderBy('MUNAME');

            $this->salesmen = $query->get()->toArray();

            if (count($this->salesmen) === 1 && empty($this->selectedSalesman)) {
                $this->selectedSalesman = $this->salesmen[0]->salesman_code;
            }
        }
    }

    public function applyFilter()
    {
        if ($this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::parse($this->startDate);
            $end = \Carbon\Carbon::parse($this->endDate);
            
            if ($start->gt($end)) {
                $this->dispatch('notify', msg: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', type: 'error');
                return;
            }
            
            if ($start->diffInDays($end) > 31) {
                $this->dispatch('notify', msg: 'Rentang waktu maksimal adalah 1 bulan (31 hari).', type: 'error');
                return;
            }
        }

        if (empty($this->selectedRegion) || empty($this->selectedArea)) {
            $this->dispatch('notify', msg: 'Harap pilih minimal Region dan Area sebelum menerapkan filter.', type: 'error');
            return;
        }

        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor;
        $this->appliedSalesman = $this->selectedSalesman;
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedDistributor = '';
        $this->appliedSalesman = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';

        $this->areas = [];
        $this->distributors = [];
        $this->salesmen = [];

        $this->loadRegions();
        $this->resetPage();
    }

    public function setTab($tab)
    {
        if ($tab === 'summary') {
            $this->dispatch('notify', msg: 'Halaman Summary Kunjungan SE akan dibuat pada tahap berikutnya.', type: 'info');
            return;
        }
        $this->activeTab = $tab;
    }

    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
        
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return round($angle * $earthRadius); // in meters
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        return self::calculateDistance($lat1, $lon1, $lat2, $lon2);
    }

    public function getBaseQuery()
    {
        $query = DB::table('rpt_visit_an_h as rvah')
            ->where('rvah.RID', '!=', 'HOINA');

        if ($this->appliedRegion) {
            $query->where('rvah.RID', $this->appliedRegion);
        }

        if ($this->appliedArea) {
            $query->where('rvah.EID', $this->appliedArea);
        }

        if ($this->appliedDistributor) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }

        if ($this->appliedSalesman) {
            $query->where('rvah.MUID', $this->appliedSalesman);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $query->where('rvah.TANGGAL', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $query->where('rvah.TANGGAL', '<=', $this->appliedEndDate);
        }

        return $query;
    }

    #[Computed]
    public function dataKunjungan()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 100);
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.ID as id',
                'rvah.RID as region_code',
                'rvah.RNAME as region_name',
                'rvah.EID as area_code',
                'rvah.ENAME as area_name',
                'rvah.BID as distributor_code',
                'rvah.BNAME as distributor_name',
                'rvah.MUID as salesman_code',
                'rvah.MUNAME as salesman_name',
                'rvah.CUSTNO as custno',
                'rvah.CUSTNAME as custname',
                'rvah.CUSTADD1 as address',
                DB::raw('rvah."TANGGAL"::date as tanggal'),
                DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
                DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
                DB::raw('rvah."TIME_CONSUME"::time as time_consume'),
                DB::raw('rvah."TIME_TRAVEL"::time as time_travel'),
                DB::raw('rvah."TIME_PAUSE"::time as time_pause'),
                'rvah.ORDER_QTY as qty_order',
                'rvah.ORDER_VAL as val_order',
                'rvah.FLAG_PJP as flag_pjp',
                'rvah.FLAG_VISIT as flag_visit',
                'rvah.FLAG_EC as flag_ec',
                'rvah.FLAG_BUY as flag_buy',
                'rvah.FLAG_PAUSE as flag_pause',
                'rvah.M_LA as master_lat',
                'rvah.M_LG as master_lon',
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvar.REASON_TYPE as reason_type',
                'rvar.REASON_DESC as reason_desc'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            });

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return $query->paginate(100);
    }

    #[Computed]
    public function kpiStats()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            return [
                'total_call' => 0,
                'pc' => 0,
                'xc' => 0,
                'total_visit' => 0,
                'visit_ac' => 0,
                'visit_ac_percent' => 0,
                'visit_xc' => 0,
                'visit_xc_percent' => 0,
                'total_visit_percent' => 0,
                'total_ec' => 0,
                'ec_pc' => 0,
                'ec_pc_percent' => 0,
                'ec_xc' => 0,
                'ec_xc_percent' => 0,
                'total_ec_percent' => 0,
                'total_order_val' => 0,
                'out_of_area' => 0,
                'out_of_area_percent' => 0,
            ];
        }

        // 1, 2 & 3. KPI 1 (Total Plan Call), KPI 2 (Actual Visit) & KPI 3 (Effective Call) - Distinct per TANGGAL, MUID, CUSTNO
        $subDailyQuery = (clone $this->getBaseQuery())
            ->select(
                'rvah.TANGGAL',
                'rvah.MUID',
                'rvah.CUSTNO',
                DB::raw('MAX(rvah."FLAG_PJP") as flag_pjp'),
                DB::raw('MAX(CASE WHEN rvah."FLAG_VISIT" = \'Y\' THEN 1 ELSE 0 END) as is_visited'),
                DB::raw('MAX(CASE WHEN rvah."FLAG_EC" = \'Y\' THEN 1 ELSE 0 END) as is_ec')
            )
            ->groupBy('rvah.TANGGAL', 'rvah.MUID', 'rvah.CUSTNO');

        $dailyStats = DB::query()->fromSub($subDailyQuery, 'sub')
            ->select(
                DB::raw('COUNT(*) as total_call'),
                DB::raw("COALESCE(SUM(CASE WHEN flag_pjp = 'R' THEN 1 ELSE 0 END), 0) as pc"),
                DB::raw("COALESCE(SUM(CASE WHEN flag_pjp = 'N' THEN 1 ELSE 0 END), 0) as xc"),
                DB::raw("COALESCE(SUM(CASE WHEN is_visited = 1 THEN 1 ELSE 0 END), 0) as total_visit"),
                DB::raw("COALESCE(SUM(CASE WHEN is_visited = 1 AND flag_pjp = 'R' THEN 1 ELSE 0 END), 0) as visit_ac"),
                DB::raw("COALESCE(SUM(CASE WHEN is_visited = 1 AND flag_pjp = 'N' THEN 1 ELSE 0 END), 0) as visit_xc"),
                DB::raw("COALESCE(SUM(CASE WHEN is_ec = 1 THEN 1 ELSE 0 END), 0) as total_ec"),
                DB::raw("COALESCE(SUM(CASE WHEN is_ec = 1 AND flag_pjp = 'R' THEN 1 ELSE 0 END), 0) as ec_pc"),
                DB::raw("COALESCE(SUM(CASE WHEN is_ec = 1 AND flag_pjp = 'N' THEN 1 ELSE 0 END), 0) as ec_xc")
            )->first();

        $totalCall = (int) ($dailyStats->total_call ?? 0);
        $pc = (int) ($dailyStats->pc ?? 0);
        $xc = (int) ($dailyStats->xc ?? 0);
        $totalVisit = (int) ($dailyStats->total_visit ?? 0);
        $visitAc = (int) ($dailyStats->visit_ac ?? 0);
        $visitXc = (int) ($dailyStats->visit_xc ?? 0);

        // Card 2: AC % terhadap PC, XC % terhadap XC
        $visitAcPercent = $pc > 0 ? round(($visitAc / $pc) * 100, 1) : 0;
        $visitXcPercent = $xc > 0 ? round(($visitXc / $xc) * 100, 1) : 0;
        $totalVisitPercent = $totalCall > 0 ? round(($totalVisit / $totalCall) * 100, 1) : 0;

        // Card 3: EC vs Actual Visit (Konversi Kunjungan ke Order)
        $totalEc = (int) ($dailyStats->total_ec ?? 0);
        $ecPc = (int) ($dailyStats->ec_pc ?? 0);
        $ecXc = (int) ($dailyStats->ec_xc ?? 0);

        $totalEcPercent = $totalVisit > 0 ? round(($totalEc / $totalVisit) * 100, 1) : 0;
        $ecPcPercent = $visitAc > 0 ? round(($ecPc / $visitAc) * 100, 1) : 0;
        $ecXcPercent = $visitXc > 0 ? round(($ecXc / $visitXc) * 100, 1) : 0;

        $distanceSql = "
            (ACOS(
                LEAST(1.0, GREATEST(-1.0, 
                    SIN(RADIANS(rvah.\"V_LA\")) * 
                    SIN(RADIANS(rvah.\"M_LA\")) + 
                    COS(RADIANS(rvah.\"V_LA\")) * 
                    COS(RADIANS(rvah.\"M_LA\")) * 
                    COS(RADIANS(rvah.\"V_LG\" - rvah.\"M_LG\"))
                ))
            ) * 6371000)
        ";

        $stats = $this->getBaseQuery()
            ->select(
                DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as total_order_val'),
                DB::raw("COUNT(DISTINCT 
                    CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' 
                          AND rvah.\"V_LA\" IS NOT NULL AND rvah.\"V_LA\" != 0
                          AND rvah.\"M_LA\" IS NOT NULL AND rvah.\"M_LA\" != 0
                          AND $distanceSql > 50
                         THEN rvah.\"ID\" ELSE NULL 
                    END
                ) as out_of_area")
            )->first();

        $outOfArea = (int) ($stats->out_of_area ?? 0);

        return [
            'total_call' => $totalCall,
            'pc' => $pc,
            'xc' => $xc,
            'total_visit' => $totalVisit,
            'visit_ac' => $visitAc,
            'visit_ac_percent' => $visitAcPercent,
            'visit_xc' => $visitXc,
            'visit_xc_percent' => $visitXcPercent,
            'total_visit_percent' => $totalVisitPercent,
            'total_ec' => $totalEc,
            'ec_pc' => $ecPc,
            'ec_pc_percent' => $ecPcPercent,
            'ec_xc' => $ecXc,
            'ec_xc_percent' => $ecXcPercent,
            'total_ec_percent' => $totalEcPercent,
            'total_order_val' => (float) ($stats->total_order_val ?? 0),
            'out_of_area' => $outOfArea,
            'out_of_area_percent' => $totalVisit > 0 ? round(($outOfArea / $totalVisit) * 100) : 0,
        ];
    }

    #[Computed]
    public function mapPointsData()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            return [];
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvah.CUSTNAME as custname',
                'rvah.MUNAME as salesman_name',
                DB::raw('rvah."TANGGAL"::date as tanggal')
            )
            ->where('rvah.FLAG_VISIT', 'Y')
            ->whereNotNull('rvah.V_LA')
            ->whereNotNull('rvah.V_LG');

        return $query->get()->map(function($row) {
            return [
                'lat' => $row->visit_lat,
                'lon' => $row->visit_lon,
                'name' => $row->custname,
                'spv' => $row->salesman_name,
                'date' => $row->tanggal ?? '-'
            ];
        })->toArray();
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu sebelum melakukan export.', type: 'error');
            return;
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.ID as id',
                'rvah.RID as region_code',
                'rvah.RNAME as region_name',
                'rvah.EID as area_code',
                'rvah.ENAME as area_name',
                'rvah.BID as distributor_code',
                'rvah.BNAME as distributor_name',
                'rvah.MUID as salesman_code',
                'rvah.MUNAME as salesman_name',
                'rvah.CUSTNO as custno',
                'rvah.CUSTNAME as custname',
                'rvah.CUSTADD1 as address',
                DB::raw('rvah."TANGGAL"::date as tanggal'),
                DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
                DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
                DB::raw('rvah."TIME_CONSUME"::time as time_consume'),
                DB::raw('rvah."TIME_TRAVEL"::time as time_travel'),
                DB::raw('rvah."TIME_PAUSE"::time as time_pause'),
                'rvah.ORDER_QTY as qty_order',
                'rvah.ORDER_VAL as val_order',
                'rvah.FLAG_PJP as flag_pjp',
                'rvah.FLAG_VISIT as flag_visit',
                'rvah.FLAG_EC as flag_ec',
                'rvah.FLAG_BUY as flag_buy',
                'rvah.FLAG_PAUSE as flag_pause',
                'rvah.M_LA as master_lat',
                'rvah.M_LG as master_lon',
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvar.REASON_TYPE as reason_type',
                'rvar.REASON_DESC as reason_desc'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            });

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return Excel::download(new AnalisaKunjunganSeExport($query), 'analisa_kunjungan_se_' . date('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan-se.index', [
            'dataKunjungan' => $this->dataKunjungan(),
            'kpiStats' => $this->kpiStats(),
            'mapPointsData' => $this->mapPointsData(),
        ])->layout('layouts.app');
    }
}
