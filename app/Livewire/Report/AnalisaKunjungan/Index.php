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
    public $accessLevel = '';

    // Summary Filters
    public $summaryStartDate;
    public $summaryEndDate;
    public $summaryRegion = '';
    public $summaryLevels = [];

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

    // Secondary Detail Filters & Search
    public $search = '';
    public $filterVisit = 'all';     // 'all', 'Y', 'N'
    public $filterEc = 'all';        // 'all', 'Y', 'N'
    public $filterPjp = 'all';       // 'all', 'Y', 'N'
    public $filterDistance = 'all';  // 'all', 'within' (<=50m), 'out' (>50m)
    public $filterReason = 'all';    // 'all', 'with', 'without'
    public $filterDepo = 'all';      // 'all', 'at_depo' (<=100m), 'not_depo' (>100m)

    #[Locked]
    public $appliedSummaryStartDate;
    public $appliedSummaryEndDate;
    public $appliedSummaryRegion;
    public $appliedSummaryLevels = [];

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
        $this->accessLevel = auth()->user()->getAccessLevel();
        $this->setDatePreset('this_month');
        
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->summaryStartDate = $this->startDate;
        $this->summaryEndDate = $this->endDate;

        $this->loadRegions();

        $this->summaryRegion = $this->selectedRegion;
    }

    /**
     * Set quick date preset (Hari Ini, Kemarin, 7 Hari Terakhir, Bulan Ini, 30 Hari, Bulan Lalu).
     */
    public function setDatePreset(string $preset): void
    {
        $today = \Carbon\Carbon::today();

        switch ($preset) {
            case 'today':
                $this->startDate = $today->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay()->format('Y-m-d');
                $this->startDate = $yesterday;
                $this->endDate = $yesterday;
                break;
            case 'last_7_days':
                $this->startDate = $today->copy()->subDays(6)->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = $today->copy()->subDays(29)->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $today->copy()->subMonthNoOverflow();
                $this->startDate = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    /**
     * Quick navigate to previous month.
     */
    public function previousMonth(): void
    {
        $currentStart = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : \Carbon\Carbon::today();
        $prevMonth = $currentStart->copy()->subMonthNoOverflow();
        $this->startDate = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
        $this->endDate = $prevMonth->copy()->endOfMonth()->format('Y-m-d');
    }

    /**
     * Quick navigate to next month (capped at today if future).
     */
    public function nextMonth(): void
    {
        $currentStart = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : \Carbon\Carbon::today();
        $nextMonth = $currentStart->copy()->addMonthNoOverflow();
        $today = \Carbon\Carbon::today();

        $this->startDate = $nextMonth->copy()->startOfMonth()->format('Y-m-d');
        $end = $nextMonth->copy()->endOfMonth();
        $this->endDate = $end->gt($today) ? $today->format('Y-m-d') : $end->format('Y-m-d');

        if (\Carbon\Carbon::parse($this->startDate)->gt($today)) {
            $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
            $this->endDate = $today->format('Y-m-d');
        }
    }

    public function updatedStartDate($value): void
    {
        if (empty($value)) return;
        try {
            $start = \Carbon\Carbon::parse($value);
            $today = \Carbon\Carbon::today();
            if ($start->gt($today)) {
                $this->startDate = $today->format('Y-m-d');
                $start = $today;
            }
            if (!empty($this->endDate)) {
                $end = \Carbon\Carbon::parse($this->endDate);
                if ($start->gt($end)) {
                    $this->endDate = $start->format('Y-m-d');
                } elseif ($start->diffInDays($end) > 30) {
                    $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                }
            } else {
                $this->endDate = $start->copy()->addDays(30)->min($today)->format('Y-m-d');
            }
        } catch (\Exception $e) {}
    }

    public function updatedEndDate($value): void
    {
        if (empty($value)) return;
        try {
            $end = \Carbon\Carbon::parse($value);
            $today = \Carbon\Carbon::today();
            if ($end->gt($today)) {
                $this->endDate = $today->format('Y-m-d');
                $end = $today;
            }
            if (!empty($this->startDate)) {
                $start = \Carbon\Carbon::parse($this->startDate);
                if ($end->lt($start)) {
                    $this->startDate = $end->format('Y-m-d');
                } elseif ($start->diffInDays($end) > 30) {
                    $this->startDate = $end->copy()->subDays(30)->format('Y-m-d');
                }
            }
        } catch (\Exception $e) {}
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterVisit(): void
    {
        $this->resetPage();
    }

    public function updatedFilterEc(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPjp(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDistance(): void
    {
        $this->resetPage();
    }

    public function updatedFilterReason(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDepo(): void
    {
        $this->resetPage();
    }

    public function resetDetailFilters(): void
    {
        $this->search = '';
        $this->filterVisit = 'all';
        $this->filterEc = 'all';
        $this->filterPjp = 'all';
        $this->filterDistance = 'all';
        $this->filterReason = 'all';
        $this->filterDepo = 'all';
        $this->resetPage();
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
                
                if ($start->diffInDays($end) > 30) {
                    $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                    $this->dispatch('notify', msg: 'Rentang waktu disesuaikan otomatis maksimal 31 hari.', type: 'info');
                }
            }

            if (empty($this->selectedSupervisor)) {
                $this->dispatch('notify', msg: 'Harap pilih Supervisor terlebih dahulu.', type: 'error');
                return;
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
            $this->appliedSummaryLevels = $this->summaryLevels;
        }
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedSupervisor = '';
        $this->setDatePreset('this_month');
        
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedSupervisor = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';
        
        $this->summaryStartDate = '';
        $this->summaryEndDate = '';
        $this->summaryRegion = '';
        $this->summaryLevels = [];

        $this->appliedSummaryStartDate = '';
        $this->appliedSummaryEndDate = '';
        $this->appliedSummaryRegion = '';
        $this->appliedSummaryLevels = [];

        $this->search = '';
        $this->filterVisit = 'all';
        $this->filterEc = 'all';
        $this->filterPjp = 'all';
        $this->filterDistance = 'all';
        $this->filterReason = 'all';
        $this->filterDepo = 'all';

        $this->areas = [];
        $this->supervisors = [];
        $this->loadRegions();
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

        $query = $this->getDetailQuery();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AnalisaKunjunganExport($query, $this->filterDepo), 'analisa_kunjungan_' . date('Ymd_His') . '.xlsx');
    }

    public function exportSummary()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedSummaryRegion) && empty($this->appliedSummaryStartDate) && empty($this->appliedSummaryEndDate)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu sebelum melakukan export summary.', type: 'error');
            return;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AnalisaKunjunganSummaryExport($this->summaryDataForExport()), 'summary_analisa_kunjungan_' . date('Ymd_His') . '.xlsx');
    }

    public function summaryDataForExport()
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

        $custnameFilter = "UPPER(rvah.\"CUSTNAME\") NOT LIKE '%BRIFING%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%BRIEFING%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%EVALUASI%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%MEETING%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%PERJALANAN%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%BLITZ%'";

        $query = $this->getBaseQuery()
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
            ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
            ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
            ->select(
                DB::raw('rvah."TANGGAL"::date as tanggal'),
                'mr.region_name',
                'ma.area_name',
                't.level',
                'rvah.MUNAME as supervisor_name',
                't.team_elite_code as supervisor_code',
                DB::raw("COUNT(DISTINCT CASE WHEN $custnameFilter THEN rvah.\"CUSTNO\" ELSE NULL END) as pc"),
                DB::raw("COUNT(DISTINCT CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' AND $custnameFilter THEN rvah.\"CUSTNO\" ELSE NULL END) as ac"),
                DB::raw("COUNT(DISTINCT CASE WHEN rvah.\"FLAG_BUY\" = 'Y' AND $custnameFilter THEN rvah.\"CUSTNO\" ELSE NULL END) as ec"),
                DB::raw('SUM(COALESCE(l.target, 0)) as target'),
                DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as order_val'),
                DB::raw("COUNT(DISTINCT CASE WHEN l.pilar = '1. RWO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN rvah.\"CUSTNO\" ELSE NULL END) as rwo"),
                DB::raw("COUNT(DISTINCT CASE WHEN l.pilar = '2. PNR' AND rvah.\"FLAG_VISIT\" = 'Y' THEN rvah.\"CUSTNO\" ELSE NULL END) as pnr"),
                DB::raw("COUNT(DISTINCT CASE WHEN l.pilar = '3. NGVO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN rvah.\"CUSTNO\" ELSE NULL END) as ngvo"),
                DB::raw("COUNT(DISTINCT 
                    CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' 
                          AND rvah.\"V_LA\" IS NOT NULL AND rvah.\"V_LA\" != 0
                          AND rvah.\"M_LA\" IS NOT NULL AND rvah.\"M_LA\" != 0
                          AND $distanceSql > 50
                         THEN rvah.\"CUSTNO\" ELSE NULL 
                    END
                ) as out_of_area")
            )
            ->groupBy(
                DB::raw('rvah."TANGGAL"::date'),
                'mr.region_name',
                'ma.area_name',
                't.level',
                'rvah.MUNAME',
                't.team_elite_code'
            );
            
        $rows = $query->get();
        
        $grouped = $rows->map(function($row) {
            $levelMap = ['region' => 'RSM', 'area' => 'ASM', 'supervisor' => 'SPV'];
            $levelName = $levelMap[$row->level] ?? strtoupper($row->level ?? '');

            $pc = (int) $row->pc;
            $ac = (int) $row->ac;
            $ec = (int) $row->ec;
            $rwo = (int) $row->rwo;
            $pnr = (int) $row->pnr;
            $ngvo = (int) $row->ngvo;
            $pareto = $rwo + $pnr + $ngvo;
            $outOfArea = (int) $row->out_of_area;

            return [
                'tanggal' => $row->tanggal,
                'region_name' => $row->region_name,
                'area_name' => $row->area_name,
                'level' => $levelName,
                'supervisor_code' => $row->supervisor_code,
                'supervisor_name' => $row->supervisor_name,
                'pc' => $pc,
                'ac' => $ac,
                'pc_ac_pct' => $pc > 0 ? round(($ac / $pc) * 100, 1) : 0,
                'ec' => $ec,
                'ec_pct' => $pc > 0 ? round(($ec / $pc) * 100, 1) : 0,
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

        // Sort by region -> area -> level -> supervisor -> tanggal
        usort($grouped, function($a, $b) {
            $cmp = strcmp($a['region_name'] ?? '', $b['region_name'] ?? '');
            if ($cmp === 0) {
                $cmp = strcmp($a['area_name'] ?? '', $b['area_name'] ?? '');
                if ($cmp === 0) {
                    $levelOrder = ['RSM' => 1, 'ASM' => 2, 'SPV' => 3];
                    $rankA = $levelOrder[$a['level'] ?? ''] ?? 99;
                    $rankB = $levelOrder[$b['level'] ?? ''] ?? 99;
                    $cmp = $rankA <=> $rankB;
                    
                    if ($cmp === 0) {
                        $cmp = strcmp($a['supervisor_name'] ?? '', $b['supervisor_name'] ?? '');
                        if ($cmp === 0) {
                            $cmp = strcmp($a['tanggal'] ?? '', $b['tanggal'] ?? '');
                        }
                    }
                }
            }
            return $cmp;
        });

        return $grouped;
    }

    public static function isBriefingOrEvaluasi(?string $custno, ?string $custname = null): bool
    {
        if (!empty($custno)) {
            $upperNo = strtoupper(trim($custno));
            if (str_starts_with($upperNo, 'BRIEF') 
                || str_starts_with($upperNo, 'BRIF') 
                || str_starts_with($upperNo, 'EVAL') 
                || str_starts_with($upperNo, 'EVA')
                || str_starts_with($upperNo, 'BRF')
                || str_starts_with($upperNo, 'BRV')) {
                return true;
            }
        }
        if (!empty($custname)) {
            $upperName = strtoupper(trim($custname));
            if (str_contains($upperName, 'BRIEFING') 
                || str_contains($upperName, 'BRIFING') 
                || str_contains($upperName, 'BRIVING') 
                || str_contains($upperName, 'EVALUASI')
                || str_starts_with($upperName, 'BRIEF ')
                || str_starts_with($upperName, 'EVAL ')) {
                return true;
            }
        }
        return false;
    }

    public static function cleanCoordinate($rawLat, $rawLon): ?array
    {
        if ($rawLat === null || $rawLon === null) return null;

        $strLat = trim(str_replace(',', '.', (string)$rawLat));
        $strLon = trim(str_replace(',', '.', (string)$rawLon));

        if ($strLat === '' || $strLon === '') return null;
        if (!is_numeric($strLat) || !is_numeric($strLon)) return null;

        $lat = (float)$strLat;
        $lon = (float)$strLon;

        if (is_nan($lat) || is_nan($lon) || is_infinite($lat) || is_infinite($lon)) {
            return null;
        }

        if (abs($lat) < 0.000001 && abs($lon) < 0.000001) {
            return null;
        }

        if (abs($lat) > 90 && abs($lon) <= 90) {
            $tmp = $lat;
            $lat = $lon;
            $lon = $tmp;
        }

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return null;
        }

        return [
            'lat' => round($lat, 8),
            'lon' => round($lon, 8),
        ];
    }

    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $c1 = self::cleanCoordinate($lat1, $lon1);
        $c2 = self::cleanCoordinate($lat2, $lon2);
        if (!$c1 || !$c2) return 0;
        
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad($c1['lat']);
        $lonFrom = deg2rad($c1['lon']);
        $latTo = deg2rad($c2['lat']);
        $lonTo = deg2rad($c2['lon']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $val = pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2);
        $val = max(0.0, min(1.0, $val));
        $angle = 2 * asin(sqrt($val));
        
        $distance = round($angle * $earthRadius);
        return (is_nan($distance) || is_infinite($distance)) ? 0 : (int)$distance;
    }

    public static function parseSecondsFromTimeString($timeStr)
    {
        if (empty($timeStr)) return 0;
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})/', $timeStr, $m)) {
            return ($m[1] * 3600) + ($m[2] * 60) + (int)$m[3];
        }
        return 0;
    }

    public static function formatDurationSeconds($sec)
    {
        if (!$sec || $sec <= 0) return '0m';
        $h = floor($sec / 3600);
        $m = round(($sec % 3600) / 60);
        if ($h > 0) {
            return "{$h}j {$m}m";
        }
        return "{$m}m";
    }

    public static function formatTimeStringHuman($timeStr)
    {
        if (empty($timeStr) || $timeStr === '-' || $timeStr === '00:00:00') return '-';
        $sec = self::parseSecondsFromTimeString($timeStr);
        if ($sec <= 0) return '-';
        $h = floor($sec / 3600);
        $m = floor(($sec % 3600) / 60);
        $s = $sec % 60;
        
        if ($h > 0) {
            return "{$h}j {$m}m";
        }
        if ($m > 0) {
            return $s > 0 ? "{$m}m {$s}d" : "{$m}m";
        }
        return "{$s}d";
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        return self::calculateDistance($lat1, $lon1, $lat2, $lon2);
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

            if (!empty($this->appliedSummaryLevels)) {
                $query->whereIn('t.level', $this->appliedSummaryLevels);
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

    protected function getDetailQuery()
    {
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
                'rak.remark as action_remark',
                't.siso_code as siso_code',
                't.area_code as area_code',
                't.region_code as region_code',
                'l.distributor_code as toko_distributor_code'
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

        if (!empty($this->search)) {
            $term = trim($this->search);
            $query->where(function($q) use ($term) {
                $q->where('rvah.CUSTNO', 'ilike', "%{$term}%")
                  ->orWhere('rvah.CUSTNAME', 'ilike', "%{$term}%")
                  ->orWhere('rvah.CUSTADD1', 'ilike', "%{$term}%")
                  ->orWhere('l.pilar', 'ilike', "%{$term}%")
                  ->orWhere('rvah.MUNAME', 'ilike', "%{$term}%");
            });
        }

        if ($this->filterVisit === 'Y') {
            $query->where('rvah.FLAG_VISIT', 'Y');
        } elseif ($this->filterVisit === 'N') {
            $query->where(function($q) {
                $q->where('rvah.FLAG_VISIT', '!=', 'Y')
                  ->orWhereNull('rvah.FLAG_VISIT');
            });
        }

        if ($this->filterEc === 'Y') {
            $query->where('rvah.FLAG_EC', 'Y');
        } elseif ($this->filterEc === 'N') {
            $query->where(function($q) {
                $q->where('rvah.FLAG_EC', '!=', 'Y')
                  ->orWhereNull('rvah.FLAG_EC');
            });
        }

        if ($this->filterPjp === 'Y' || $this->filterPjp === 'R') {
            $query->whereIn('rvah.FLAG_PJP', ['R', 'Y']);
        } elseif ($this->filterPjp === 'N') {
            $query->where(function($q) {
                $q->whereNotIn('rvah.FLAG_PJP', ['R', 'Y'])
                  ->orWhereNull('rvah.FLAG_PJP');
            });
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

        if ($this->filterDistance === 'within') {
            $query->whereNotNull('rvah.V_LA')
                  ->where('rvah.V_LA', '!=', 0)
                  ->whereNotNull('rvah.M_LA')
                  ->where('rvah.M_LA', '!=', 0)
                  ->whereNotNull('rvah.V_LG')
                  ->whereNotNull('rvah.M_LG')
                  ->whereRaw("{$distanceSql} <= 50");
        } elseif ($this->filterDistance === 'out') {
            $query->whereNotNull('rvah.V_LA')
                  ->where('rvah.V_LA', '!=', 0)
                  ->whereNotNull('rvah.M_LA')
                  ->where('rvah.M_LA', '!=', 0)
                  ->whereNotNull('rvah.V_LG')
                  ->whereNotNull('rvah.M_LG')
                  ->whereRaw("{$distanceSql} > 50");
        }

        if ($this->filterReason === 'with') {
            $query->where(function($q) {
                $q->whereNotNull('rvar.REASON_TYPE')
                  ->orWhereNotNull('rvar.REASON_DESC');
            });
        } elseif ($this->filterReason === 'without') {
            $query->whereNull('rvar.REASON_TYPE')
                  ->whereNull('rvar.REASON_DESC');
        }

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return $query;
    }

    #[Computed]
    public function dailyData()
    {
        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            return [
                'summaries' => collect(),
                'grandTotal' => null,
            ];
        }

        $rawRows = $this->getDetailQuery()->get();

        // Ambil daftar titik koordinat seluruh depo / distributor aktif
        $allDepos = DB::table('master_distributors as md')
            ->whereNotNull('md.latitude')
            ->whereNotNull('md.longitude')
            ->where('md.latitude', '!=', 0)
            ->where('md.longitude', '!=', 0)
            ->select('md.distributor_code', 'md.distributor_name', 'md.supervisor_code', 'md.area_code', 'md.region_code', 'md.latitude', 'md.longitude')
            ->get();

        // Evaluasi jarak ke Depo untuk setiap baris kunjungan
        foreach ($rawRows as $item) {
            $isBriefingEva = self::isBriefingOrEvaluasi($item->custno, $item->custname);
            $cleanVisit = self::cleanCoordinate($item->visit_lat, $item->visit_lon);
            $item->is_briefing_eva = $isBriefingEva;
            $item->is_at_depo = false;
            $item->dist_to_depo = null;
            $item->depo_name = null;
            $item->depo_lat = null;
            $item->depo_lon = null;

            if ($cleanVisit && $allDepos->isNotEmpty()) {
                // Prioritaskan depo milik SPV ini
                $spvDepos = $allDepos->where('supervisor_code', $item->siso_code);
                if ($spvDepos->isEmpty() && !empty($item->area_code)) {
                    $spvDepos = $allDepos->where('area_code', $item->area_code);
                }
                if ($spvDepos->isEmpty()) {
                    $spvDepos = $allDepos;
                }

                $closest = null;
                $minDist = null;
                foreach ($spvDepos as $depo) {
                    $d = self::calculateDistance($cleanVisit['lat'], $cleanVisit['lon'], $depo->latitude, $depo->longitude);
                    if ($d !== null && ($minDist === null || $d < $minDist)) {
                        $minDist = (int)$d;
                        $closest = $depo;
                    }
                }

                // Cek jika ada depo lain yang berada dalam radius <= 100m
                if ($minDist === null || $minDist > 100) {
                    foreach ($allDepos as $depo) {
                        $d = self::calculateDistance($cleanVisit['lat'], $cleanVisit['lon'], $depo->latitude, $depo->longitude);
                        if ($d !== null && $d <= 100) {
                            $minDist = (int)$d;
                            $closest = $depo;
                            break;
                        }
                    }
                }

                if ($closest !== null && $minDist !== null) {
                    $item->dist_to_depo = $minDist;
                    $item->depo_name = trim(preg_replace('/[\r\n\t]+/', ' ', (string)$closest->distributor_name));
                    $item->depo_lat = $closest->latitude;
                    $item->depo_lon = $closest->longitude;
                    // Abaikan briefing pagi & evaluasi sore karena memang dilakukan di distributor/depo
                    $item->is_at_depo = (!$isBriefingEva && $item->flag_visit === 'Y' && $minDist <= 100);
                }
            }
        }

        // Terapkan filter lokasi depo jika dipilih
        if ($this->filterDepo === 'at_depo') {
            $rawRows = $rawRows->filter(fn($i) => $i->is_at_depo);
        } elseif ($this->filterDepo === 'not_depo') {
            $rawRows = $rawRows->filter(fn($i) => !$i->is_at_depo && !$i->is_briefing_eva);
        }

        $summaries = $rawRows->groupBy('tanggal')->map(function ($items, $tanggal) {
            $totalCall = $items->count();
            $pc = $items->filter(fn($i) => in_array($i->flag_pjp, ['R', 'Y']))->count();
            $xc = $totalCall - $pc;
            $visited = $items->where('flag_visit', 'Y');
            $totalVisit = $visited->count();
            $visitPercent = $totalCall > 0 ? round(($totalVisit / $totalCall) * 100, 1) : 0;
            
            $ec = $items->where('flag_ec', 'Y');
            $totalEc = $ec->count();
            $ecPercent = $totalVisit > 0 ? round(($totalEc / $totalVisit) * 100, 1) : 0;
            $totalTarget = (float) $items->sum('target');
            $totalValOrder = (float) $items->sum('val_order');
            
            // Sort items by time_in (kunjungan dengan jam masuk diutamakan)
            $sortedItems = $items->sortBy(fn($i) => !empty($i->time_in) ? $i->time_in : '99:99:99')->values();
            $firstIn = $sortedItems->first(fn($i) => !empty($i->time_in))?->time_in;
            $lastOut = $sortedItems->filter(fn($i) => !empty($i->time_out))->sortByDesc('time_out')->first()?->time_out;
            
            // Work duration
            $workDurationStr = '-';
            $workDurationSec = 0;
            if ($firstIn && $lastOut) {
                $secIn = self::parseSecondsFromTimeString($firstIn);
                $secOut = self::parseSecondsFromTimeString($lastOut);
                if ($secOut >= $secIn) {
                    $workDurationSec = $secOut - $secIn;
                    $workDurationStr = self::formatDurationSeconds($workDurationSec);
                }
            }

            // Distances & item enhancements
            $outOfRadiusCount = 0;
            $rowNum = 1;
            foreach ($sortedItems as $item) {
                $item->row_num = $rowNum++;
                $dist = self::calculateDistance($item->master_lat, $item->master_lon, $item->visit_lat, $item->visit_lon);
                $item->distance = (int)$dist;

                $isPjpVisited = (in_array($item->flag_pjp, ['R', 'Y']) && $item->flag_visit === 'Y');
                $item->is_out_of_radius = ($isPjpVisited && $dist > 50);
                if ($item->is_out_of_radius) {
                    $outOfRadiusCount++;
                }

                $item->time_consume_human = self::formatTimeStringHuman($item->time_consume);
                $item->time_travel_human = self::formatTimeStringHuman($item->time_travel);
                $item->time_pause_human = self::formatTimeStringHuman($item->time_pause);
                $item->time_in_fmt = !empty($item->time_in) ? substr($item->time_in, 0, 5) : '-';
                $item->time_out_fmt = !empty($item->time_out) ? substr($item->time_out, 0, 5) : '-';
            }

            // Map points for daily route
            $mapPoints = [];
            $routeSeq = 1;
            foreach ($sortedItems as $item) {
                if ($item->flag_visit === 'Y') {
                    $cleanCoord = self::cleanCoordinate($item->visit_lat, $item->visit_lon);
                    if ($cleanCoord) {
                        $cleanCustName = trim(preg_replace('/[\r\n\t]+/', ' ', (string)$item->custname));
                        $mapPoints[] = [
                            'seq' => $routeSeq++,
                            'lat' => $cleanCoord['lat'],
                            'lon' => $cleanCoord['lon'],
                            'custno' => (string)($item->custno ?? ''),
                            'name' => $cleanCustName !== '' ? $cleanCustName : 'Toko #' . $item->row_num,
                            'spv' => (string)($item->supervisor_name ?? ''),
                            'date' => $tanggal,
                            'time_in' => $item->time_in_fmt,
                            'time_out' => $item->time_out_fmt,
                            'time_consume' => $item->time_consume_human,
                            'order_val' => (float) ($item->val_order ?? 0),
                            'flag_ec' => $item->flag_ec,
                            'is_at_depo' => $item->is_at_depo,
                            'is_briefing_eva' => $item->is_briefing_eva,
                            'dist_to_depo' => $item->dist_to_depo,
                            'depo_name' => $item->depo_name,
                        ];
                    }
                }
            }

            // Time sums (in seconds)
            $consumeSec = 0;
            $travelSec = 0;
            foreach ($sortedItems as $item) {
                $consumeSec += self::parseSecondsFromTimeString($item->time_consume);
                $travelSec += self::parseSecondsFromTimeString($item->time_travel);
            }

            $totalActiveSec = $consumeSec + $travelSec;
            $consumePercent = $totalActiveSec > 0 ? (int) round(($consumeSec / $totalActiveSec) * 100) : 50;
            $travelPercent = 100 - $consumePercent;

            $carbonDate = \Carbon\Carbon::parse($tanggal);
            $isWeekend = $carbonDate->isSaturday() || $carbonDate->isSunday();
            $isSaturday = $carbonDate->isSaturday();

            return (object) [
                'tanggal' => $tanggal,
                'carbon_date' => $carbonDate,
                'day_name' => $carbonDate->locale('id')->translatedFormat('l'),
                'formatted_date' => $carbonDate->locale('id')->translatedFormat('d M Y'),
                'is_weekend' => $isWeekend,
                'is_saturday' => $isSaturday,
                'total_call' => $totalCall,
                'pc' => $pc,
                'xc' => $xc,
                'total_visit' => $totalVisit,
                'visit_percent' => $visitPercent,
                'total_ec' => $totalEc,
                'ec_percent' => $ecPercent,
                'total_target' => $totalTarget,
                'total_val_order' => $totalValOrder,
                'first_time_in' => $firstIn ? substr($firstIn, 0, 5) : '-',
                'last_time_out' => $lastOut ? substr($lastOut, 0, 5) : '-',
                'work_duration_sec' => $workDurationSec,
                'work_duration_str' => $workDurationStr,
                'consume_sec' => $consumeSec,
                'consume_str' => self::formatDurationSeconds($consumeSec),
                'travel_sec' => $travelSec,
                'travel_str' => self::formatDurationSeconds($travelSec),
                'consume_percent' => $consumePercent,
                'travel_percent' => $travelPercent,
                'out_of_radius_count' => $outOfRadiusCount,
                'at_depo_count' => $items->where('is_at_depo', true)->count(),
                'map_points' => $mapPoints,
                'items' => $sortedItems,
            ];
        });

        // Grand Total across all dates
        $grandTotal = null;
        if ($summaries->isNotEmpty()) {
            $totCalls = $summaries->sum('total_call');
            $totPc = $summaries->sum('pc');
            $totXc = $summaries->sum('xc');
            $totVisits = $summaries->sum('total_visit');
            $totEc = $summaries->sum('total_ec');
            $totTarget = $summaries->sum('total_target');
            $totVal = $summaries->sum('total_val_order');
            $totConsumeSec = $summaries->sum('consume_sec');
            $totTravelSec = $summaries->sum('travel_sec');
            $totOutOfRadius = $summaries->sum('out_of_radius_count');
            $totAtDepo = $summaries->sum('at_depo_count');
            $totDays = $summaries->count();

            $totWorkSec = $summaries->sum('work_duration_sec');
            $avgWorkSec = $totDays > 0 ? (int) round($totWorkSec / $totDays) : 0;
            $avgWorkStr = self::formatDurationSeconds($avgWorkSec);

            $totActiveSec = $totConsumeSec + $totTravelSec;
            $totConsumePercent = $totActiveSec > 0 ? (int) round(($totConsumeSec / $totActiveSec) * 100) : 50;
            $totTravelPercent = 100 - $totConsumePercent;

            $grandTotal = (object) [
                'total_days' => $totDays,
                'total_call' => $totCalls,
                'pc' => $totPc,
                'xc' => $totXc,
                'total_visit' => $totVisits,
                'visit_percent' => $totCalls > 0 ? round(($totVisits / $totCalls) * 100, 1) : 0,
                'total_ec' => $totEc,
                'ec_percent' => $totVisits > 0 ? round(($totEc / $totVisits) * 100, 1) : 0,
                'total_target' => $totTarget,
                'total_val_order' => $totVal,
                'avg_work_str' => $avgWorkStr,
                'consume_str' => self::formatDurationSeconds($totConsumeSec),
                'travel_str' => self::formatDurationSeconds($totTravelSec),
                'consume_percent' => $totConsumePercent,
                'travel_percent' => $totTravelPercent,
                'out_of_radius_count' => $totOutOfRadius,
                'at_depo_count' => $totAtDepo,
            ];
        }

        return [
            'summaries' => $summaries,
            'grandTotal' => $grandTotal,
        ];
    }

    #[Computed]
    public function dataKunjungan()
    {
        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 300);
        }

        return $this->getDetailQuery()->paginate(300);
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

        $custnameFilter = "UPPER(rvah.\"CUSTNAME\") NOT LIKE '%BRIFING%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%BRIEFING%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%EVALUASI%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%MEETING%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%PERJALANAN%' AND UPPER(rvah.\"CUSTNAME\") NOT LIKE '%BLITZ%'";

        $query = $this->getBaseQuery()
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
            ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
            ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
            ->select(
                'mr.region_name',
                'ma.area_name',
                't.level',
                'rvah.MUNAME as supervisor_name',
                't.team_elite_code as supervisor_code',
                DB::raw("COUNT(DISTINCT CASE WHEN $custnameFilter THEN rvah.\"CUSTNO\" ELSE NULL END) as pc"),
                DB::raw("COUNT(DISTINCT CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' AND $custnameFilter THEN rvah.\"CUSTNO\" ELSE NULL END) as ac"),
                DB::raw("COUNT(DISTINCT CASE WHEN rvah.\"FLAG_BUY\" = 'Y' AND $custnameFilter THEN rvah.\"CUSTNO\" ELSE NULL END) as ec"),
                DB::raw('SUM(COALESCE(l.target, 0)) as target'),
                DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as order_val'),
                DB::raw("COUNT(DISTINCT CASE WHEN l.pilar = '1. RWO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN rvah.\"CUSTNO\" ELSE NULL END) as rwo"),
                DB::raw("COUNT(DISTINCT CASE WHEN l.pilar = '2. PNR' AND rvah.\"FLAG_VISIT\" = 'Y' THEN rvah.\"CUSTNO\" ELSE NULL END) as pnr"),
                DB::raw("COUNT(DISTINCT CASE WHEN l.pilar = '3. NGVO' AND rvah.\"FLAG_VISIT\" = 'Y' THEN rvah.\"CUSTNO\" ELSE NULL END) as ngvo"),
                DB::raw("COUNT(DISTINCT 
                    CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' 
                          AND rvah.\"V_LA\" IS NOT NULL AND rvah.\"V_LA\" != 0
                          AND rvah.\"M_LA\" IS NOT NULL AND rvah.\"M_LA\" != 0
                          AND $distanceSql > 50
                         THEN rvah.\"CUSTNO\" ELSE NULL 
                    END
                ) as out_of_area")
            )
            ->groupBy(
                'mr.region_name',
                'ma.area_name',
                't.level',
                'rvah.MUNAME',
                't.team_elite_code'
            );
            
        $rows = $query->get();
        
        $grouped = $rows->map(function($row) {
            $levelMap = ['region' => 'RSM', 'area' => 'ASM', 'supervisor' => 'SPV'];
            $levelName = $levelMap[$row->level] ?? strtoupper($row->level ?? '');

            $pc = (int) $row->pc;
            $ac = (int) $row->ac;
            $ec = (int) $row->ec;
            $rwo = (int) $row->rwo;
            $pnr = (int) $row->pnr;
            $ngvo = (int) $row->ngvo;
            $pareto = $rwo + $pnr + $ngvo;
            $outOfArea = (int) $row->out_of_area;

            return [
                'region_name' => $row->region_name,
                'area_name' => $row->area_name,
                'level' => $levelName,
                'supervisor_code' => $row->supervisor_code,
                'supervisor_name' => $row->supervisor_name,
                'pc' => $pc,
                'ac' => $ac,
                'pc_ac_pct' => $pc > 0 ? round(($ac / $pc) * 100, 1) : 0,
                'ec' => $ec,
                'ec_pct' => $pc > 0 ? round(($ec / $pc) * 100, 1) : 0,
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

        // Sort by region -> area -> level -> supervisor
        usort($grouped, function($a, $b) {
            $cmp = strcmp($a['region_name'] ?? '', $b['region_name'] ?? '');
            if ($cmp === 0) {
                $cmp = strcmp($a['area_name'] ?? '', $b['area_name'] ?? '');
                if ($cmp === 0) {
                    $levelOrder = ['RSM' => 1, 'ASM' => 2, 'SPV' => 3];
                    $rankA = $levelOrder[$a['level'] ?? ''] ?? 99;
                    $rankB = $levelOrder[$b['level'] ?? ''] ?? 99;
                    $cmp = $rankA <=> $rankB;
                    
                    if ($cmp === 0) {
                        $cmp = strcmp($a['supervisor_name'] ?? '', $b['supervisor_name'] ?? '');
                    }
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

    /**
     * Trigger modal and send map points data on demand via Livewire event.
     */
    public function openAllMapsModal(): void
    {
        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu untuk melihat peta sebaran.', type: 'warning');
            return;
        }

        // Ambil data points harian yang telah diperkaya info depo, atau fallback ke mapPointsData
        $points = collect($this->dailyData()['summaries'] ?? [])->pluck('map_points')->flatten(1)->toArray();
        if (empty($points)) {
            $points = $this->mapPointsData;
        }

        if (empty($points)) {
            $this->dispatch('notify', msg: 'Tidak ada titik koordinat kunjungan yang ditemukan pada filter ini.', type: 'info');
            return;
        }

        $this->dispatch('open-all-maps-modal', $points);
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan.index', [
            'dailyData' => $this->dailyData(),
            'dataKunjungan' => $this->dataKunjungan(),
        ])->layout('layouts.app');
    }
}
