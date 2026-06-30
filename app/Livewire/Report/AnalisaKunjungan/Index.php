<?php

namespace App\Livewire\Report\AnalisaKunjungan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithPagination;
use App\Models\RemarkAnalisaKunjungan;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use EnforcesMenuPermissions, WithPagination;

    #[Title('Analisa Kunjungan')]
    protected string $menuRoute = 'report.analisa-kunjungan.index';

    public $activeTab = 'detail';

    // Summary Filters
    public $summaryStartDate;
    public $summaryEndDate;
    public $summaryRegion = '';

    public $regions = [];
    public $areas = [];
    public $supervisors = [];

    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedSupervisor = '';
    
    public $startDate = '';
    public $endDate = '';

    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSupervisor = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';

    #[Locked]
    public $appliedSummaryStartDate;
    public $appliedSummaryEndDate;
    public $appliedSummaryRegion;

    #[Locked]
    public $modalRemarkVisitId = '';
    #[Locked]
    public $modalRemarkMuid = '';
    #[Locked]
    public $modalRemarkCustno = '';
    #[Locked]
    public $modalRemarkTanggal = '';

    public $modalRemarkText = '';

    public function mount()
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->summaryStartDate = $this->startDate;
        $this->summaryEndDate = $this->endDate;

        $this->loadRegions();

        $this->summaryRegion = $this->selectedRegion;
    }

    public function loadRegions()
    {
        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();

        $query = DB::table('team_elite_code_mappings as t')
            ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
            ->select('mr.region_code', 'mr.region_name')
            ->whereNotNull('mr.region_code')
            ->distinct()
            ->orderBy('mr.region_name');

        if ($accessLevel === 'region') {
            $query->whereIn('t.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $query->whereIn('t.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $query->where('t.team_elite_code', $user->supervisor_code);
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
        $this->selectedSupervisor = '';
        $this->areas = [];
        $this->supervisors = [];

        if ($value) {
            $user = auth()->user();
            $accessLevel = $user->getAccessLevel();

            $query = DB::table('team_elite_code_mappings as t')
                ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
                ->where('t.region_code', $value)
                ->whereNotNull('ma.area_code')
                ->select('ma.area_code', 'ma.area_name')
                ->distinct()
                ->orderBy('ma.area_name');

            if ($accessLevel === 'area') {
                $query->whereIn('t.area_code', (array) $user->area_code);
            } elseif ($accessLevel === 'supervisor') {
                $query->where('t.team_elite_code', $user->supervisor_code);
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
        $this->selectedSupervisor = '';
        $this->supervisors = [];

        if ($value) {
            $user = auth()->user();
            $accessLevel = $user->getAccessLevel();

            $query = DB::table('team_elite_code_mappings as t')
                ->leftJoin('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
                ->where('t.region_code', $this->selectedRegion)
                ->where('t.area_code', $value)
                ->whereNotNull('t.team_elite_code')
                ->select('t.team_elite_code as supervisor_code', 'f.SLSNAME as supervisor_name')
                ->distinct()
                ->orderBy('f.SLSNAME');

            if ($accessLevel === 'supervisor') {
                $query->where('t.team_elite_code', $user->supervisor_code);
            }

            $this->supervisors = $query->get()->toArray();

            if (count($this->supervisors) === 1 && empty($this->selectedSupervisor)) {
                $this->selectedSupervisor = $this->supervisors[0]->supervisor_code;
            }
        }
    }

    public function applyFilter()
    {
        if ($this->activeTab === 'detail') {
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

            $this->appliedRegion = $this->selectedRegion;
            $this->appliedArea = $this->selectedArea;
            $this->appliedSupervisor = $this->selectedSupervisor;
            $this->appliedStartDate = $this->startDate;
            $this->appliedEndDate = $this->endDate;
            $this->resetPage();
        } else {
            if ($this->summaryStartDate && $this->summaryEndDate) {
                $start = \Carbon\Carbon::parse($this->summaryStartDate);
                $end = \Carbon\Carbon::parse($this->summaryEndDate);
                
                if ($start->gt($end)) {
                    $this->dispatch('notify', msg: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', type: 'error');
                    return;
                }
                
                if ($start->diffInDays($end) > 31) {
                    $this->dispatch('notify', msg: 'Rentang waktu maksimal adalah 1 bulan (31 hari).', type: 'error');
                    return;
                }
            }

            $this->appliedSummaryStartDate = $this->summaryStartDate;
            $this->appliedSummaryEndDate = $this->summaryEndDate;
            $this->appliedSummaryRegion = $this->summaryRegion;
        }
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedSupervisor = '';
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedSupervisor = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';
        
        $this->summaryStartDate = '';
        $this->summaryEndDate = '';
        $this->summaryRegion = '';

        $this->appliedSummaryStartDate = '';
        $this->appliedSummaryEndDate = '';
        $this->appliedSummaryRegion = '';

        $this->areas = [];
        $this->supervisors = [];
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openRemarkModal($visitId, $muid, $custno, $tanggal, $text)
    {
        $this->modalRemarkVisitId = $visitId;
        $this->modalRemarkMuid = $muid;
        $this->modalRemarkCustno = $custno;
        $this->modalRemarkTanggal = $tanggal;
        $this->modalRemarkText = $text;
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-action-remark');
    }

    public function saveRemark()
    {
        $isEdit = RemarkAnalisaKunjungan::where('visit_id', $this->modalRemarkVisitId)->exists();
        $this->authorizeAction($isEdit ? 'can_edit' : 'can_add');

        $this->validate([
            'modalRemarkVisitId' => 'required|string|max:50',
            'modalRemarkMuid' => 'required|string|max:50',
            'modalRemarkCustno' => 'required|string|max:50',
            'modalRemarkTanggal' => 'required|date',
            'modalRemarkText' => 'required|string|max:1000',
        ]);

        RemarkAnalisaKunjungan::updateOrCreate(
            [
                'visit_id' => $this->modalRemarkVisitId,
                'muid' => $this->modalRemarkMuid,
                'custno' => $this->modalRemarkCustno,
                'tanggal' => $this->modalRemarkTanggal,
            ],
            [
                'remark' => $this->modalRemarkText,
                'created_by' => auth()->user()->username ?? 'system'
            ]
        );

        $this->dispatch('close-modal', 'modal-action-remark');
        $this->dispatch('notify', msg: 'Remark berhasil disimpan!', type: 'success');
    }

    public function deleteRemark($visitId, $muid, $custno, $tanggal)
    {
        $this->authorizeAction('can_delete');

        $visitId = strip_tags(trim($visitId));
        $tanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

        RemarkAnalisaKunjungan::where('visit_id', $visitId)
            ->where('muid', $muid)
            ->where('custno', $custno)
            ->where('tanggal', $tanggal)
            ->delete();

        $this->dispatch('notify', msg: 'Remark berhasil dihapus!', type: 'success');
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu sebelum melakukan export.', type: 'error');
            return;
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.ID as id',
                'rvah.MUID as supervisor_code',
                'rvah.MUNAME as supervisor_name',
                'rvah.CUSTNO as custno',
                'rvah.CUSTNAME as custname',
                'rvah.CUSTADD1 as address',
                'l.pilar as pilar',
                'l.target as target',
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
                'rvar.REASON_DESC as reason_desc',
                'rak.remark as action_remark'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->leftJoin('remark_analisa_kunjungan as rak', function($join) {
                $join->on('rvah.ID', '=', 'rak.visit_id')
                     ->on('rvah.MUID', '=', 'rak.muid')
                     ->on('rvah.CUSTNO', '=', 'rak.custno')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rak.tanggal');
            })
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO');

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AnalisaKunjunganExport($query), 'analisa_kunjungan_' . date('Ymd_His') . '.xlsx');
    }

    public function exportSummary()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedSummaryRegion) && empty($this->appliedSummaryStartDate) && empty($this->appliedSummaryEndDate)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu sebelum melakukan export summary.', type: 'error');
            return;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AnalisaKunjunganSummaryExport($this->summaryData), 'summary_analisa_kunjungan_' . date('Ymd_His') . '.xlsx');
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
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

    protected function getBaseQuery()
    {
        $query = DB::table('rpt_visit_an_h as rvah')
            ->join('team_elite_code_mappings as t', 't.team_elite_code', '=', 'rvah.MUID');

        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();
        if ($accessLevel === 'region') {
            $query->whereIn('t.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $query->whereIn('t.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $query->where('t.team_elite_code', $user->supervisor_code);
        }

        if ($this->activeTab === 'summary') {
            if ($this->appliedSummaryRegion) {
                $query->where('t.region_code', $this->appliedSummaryRegion);
            }

            if ($this->appliedSummaryStartDate && $this->appliedSummaryEndDate) {
                $query->whereBetween(DB::raw('rvah."TANGGAL"::date'), [$this->appliedSummaryStartDate, $this->appliedSummaryEndDate]);
            } elseif ($this->appliedSummaryStartDate) {
                $query->where(DB::raw('rvah."TANGGAL"::date'), '>=', $this->appliedSummaryStartDate);
            } elseif ($this->appliedSummaryEndDate) {
                $query->where(DB::raw('rvah."TANGGAL"::date'), '<=', $this->appliedSummaryEndDate);
            }
        } else {
            if ($this->appliedRegion) {
                $query->where('t.region_code', $this->appliedRegion);
            }

            if ($this->appliedArea) {
                $query->where('t.area_code', $this->appliedArea);
            }

            if ($this->appliedSupervisor) {
                $query->where('rvah.MUID', $this->appliedSupervisor);
            }

            if ($this->appliedStartDate && $this->appliedEndDate) {
                $query->whereBetween(DB::raw('rvah."TANGGAL"::date'), [$this->appliedStartDate, $this->appliedEndDate]);
            } elseif ($this->appliedStartDate) {
                $query->where(DB::raw('rvah."TANGGAL"::date'), '>=', $this->appliedStartDate);
            } elseif ($this->appliedEndDate) {
                $query->where(DB::raw('rvah."TANGGAL"::date'), '<=', $this->appliedEndDate);
            }
        }

        return $query;
    }

    #[Computed]
    public function dataKunjungan()
    {
        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 300);
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.ID as id',
                'rvah.MUID as supervisor_code',
                'rvah.MUNAME as supervisor_name',
                'rvah.CUSTNO as custno',
                'rvah.CUSTNAME as custname',
                'rvah.CUSTADD1 as address',
                'l.pilar as pilar',
                'l.target as target',
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
                'rvar.REASON_DESC as reason_desc',
                'rak.remark as action_remark'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->leftJoin('remark_analisa_kunjungan as rak', function($join) {
                $join->on('rvah.ID', '=', 'rak.visit_id')
                     ->on('rvah.MUID', '=', 'rak.muid')
                     ->on('rvah.CUSTNO', '=', 'rak.custno')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rak.tanggal');
            })
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO');

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return $query->paginate(300);
    }

    #[Computed]
    public function summaryData()
    {
        if (empty($this->appliedSummaryRegion) && empty($this->appliedSummaryStartDate) && empty($this->appliedSummaryEndDate)) {
            return [];
        }

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

        $query = $this->getBaseQuery()
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
            ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
            ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
            ->select(
                'mr.region_name',
                'ma.area_name',
                'rvah.MUNAME as supervisor_name',
                't.team_elite_code as supervisor_code',
                DB::raw('COUNT(*) as pc'),
                DB::raw("SUM(CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as ac"),
                DB::raw('SUM(COALESCE(l.target, 0)) as target'),
                DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as order_val'),
                DB::raw("SUM(CASE WHEN l.pilar = '1. RWO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as rwo"),
                DB::raw("SUM(CASE WHEN l.pilar = '2. PNR' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as pnr"),
                DB::raw("SUM(CASE WHEN l.pilar = '3. NGVO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as ngvo"),
                DB::raw("SUM(
                    CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' 
                          AND rvah.\"V_LA\" IS NOT NULL AND rvah.\"V_LA\" != 0
                          AND rvah.\"M_LA\" IS NOT NULL AND rvah.\"M_LA\" != 0
                          AND $distanceSql > 50
                         THEN 1 ELSE 0 
                    END
                ) as out_of_area")
            )
            ->groupBy(
                'mr.region_name',
                'ma.area_name',
                'rvah.MUNAME',
                't.team_elite_code'
            );
            
        $rows = $query->get();
        
        $grouped = $rows->map(function($row) {
            $pc = (int) $row->pc;
            $ac = (int) $row->ac;
            $rwo = (int) $row->rwo;
            $pnr = (int) $row->pnr;
            $ngvo = (int) $row->ngvo;
            $pareto = $rwo + $pnr + $ngvo;
            $outOfArea = (int) $row->out_of_area;

            return [
                'region_name' => $row->region_name,
                'area_name' => $row->area_name,
                'supervisor_code' => $row->supervisor_code,
                'supervisor_name' => $row->supervisor_name,
                'pc' => $pc,
                'ac' => $ac,
                'pc_ac_pct' => $pc > 0 ? round(($ac / $pc) * 100, 1) : 0,
                'target' => (float) $row->target,
                'order' => (float) $row->order_val,
                'target_order_pct' => $row->target > 0 ? round(($row->order_val / $row->target) * 100, 1) : 0,
                'rwo' => $rwo,
                'rwo_pct' => $pc > 0 ? round(($rwo / $pc) * 100, 1) : 0,
                'pnr' => $pnr,
                'pnr_pct' => $pc > 0 ? round(($pnr / $pc) * 100, 1) : 0,
                'ngvo' => $ngvo,
                'ngvo_pct' => $pc > 0 ? round(($ngvo / $pc) * 100, 1) : 0,
                'pareto' => $pareto,
                'pareto_pct' => $pc > 0 ? round(($pareto / $pc) * 100, 1) : 0,
                'out_of_area' => $outOfArea,
                'out_of_area_pct' => $ac > 0 ? round(($outOfArea / $ac) * 100, 1) : 0
            ];
        })->toArray();

        // Sort by region -> area -> supervisor
        usort($grouped, function($a, $b) {
            $cmp = strcmp($a['region_name'] ?? '', $b['region_name'] ?? '');
            if ($cmp === 0) {
                $cmp = strcmp($a['area_name'] ?? '', $b['area_name'] ?? '');
                if ($cmp === 0) {
                    $cmp = strcmp($a['supervisor_name'] ?? '', $b['supervisor_name'] ?? '');
                }
            }
            return $cmp;
        });

        return $grouped;
    }

    #[Computed]
    public function kpiStats()
    {
        if ($this->activeTab === 'detail' && empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            return [
                'total_visit_target' => 0, 'total_visit_actual' => 0,
                'total_order_target' => 0, 'total_order_actual' => 0,
                'rwo_target' => 0, 'rwo_actual' => 0,
                'pnr_target' => 0, 'pnr_actual' => 0,
                'ngvo_target' => 0, 'ngvo_actual' => 0,
            ];
        }

        $stats = $this->getBaseQuery()
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
            ->select(
                DB::raw('COUNT(*) as total_visit_target'),
                DB::raw("SUM(CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as total_visit_actual"),
                DB::raw('SUM(COALESCE(l.target, 0)) as total_order_target'),
                DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as total_order_actual'),
                DB::raw("SUM(CASE WHEN l.pilar = '1. RWO' THEN 1 ELSE 0 END) as rwo_target"),
                DB::raw("SUM(CASE WHEN l.pilar = '1. RWO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as rwo_actual"),
                DB::raw("SUM(CASE WHEN l.pilar = '2. PNR' THEN 1 ELSE 0 END) as pnr_target"),
                DB::raw("SUM(CASE WHEN l.pilar = '2. PNR' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as pnr_actual"),
                DB::raw("SUM(CASE WHEN l.pilar = '3. NGVO' THEN 1 ELSE 0 END) as ngvo_target"),
                DB::raw("SUM(CASE WHEN l.pilar = '3. NGVO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as ngvo_actual")
            )->first();

        return [
            'total_visit_target' => $stats->total_visit_target ?? 0,
            'total_visit_actual' => $stats->total_visit_actual ?? 0,
            'total_order_target' => $stats->total_order_target ?? 0,
            'total_order_actual' => $stats->total_order_actual ?? 0,
            'rwo_target' => $stats->rwo_target ?? 0,
            'rwo_actual' => $stats->rwo_actual ?? 0,
            'pnr_target' => $stats->pnr_target ?? 0,
            'pnr_actual' => $stats->pnr_actual ?? 0,
            'ngvo_target' => $stats->ngvo_target ?? 0,
            'ngvo_actual' => $stats->ngvo_actual ?? 0,
        ];
    }

    #[Computed]
    public function mapPointsData()
    {
        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            return [];
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvah.CUSTNAME as custname',
                'rvah.MUNAME as supervisor_name',
                DB::raw('rvah."TANGGAL"::date as tanggal')
            )
            ->where('rvah.FLAG_VISIT', 'Y')
            ->whereRaw("UPPER(rvah.\"CUSTNO\") NOT LIKE '%BRI%'")
            ->whereRaw("UPPER(rvah.\"CUSTNO\") NOT LIKE '%EVA%'")
            ->whereNotNull('rvah.V_LA')
            ->whereNotNull('rvah.V_LG');

        return $query->get()->map(function($row) {
            return [
                'lat' => $row->visit_lat,
                'lon' => $row->visit_lon,
                'name' => $row->custname,
                'spv' => $row->supervisor_name,
                'date' => $row->tanggal ?? '-'
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan.index', [
            'dataKunjungan' => $this->dataKunjungan
        ])->layout('layouts.app');
    }
}
