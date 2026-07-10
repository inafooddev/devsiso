<?php

namespace App\Livewire\CallPlan\JksTeamElite\SummaryVisitTeamElite;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;

class Index extends Component
{
    use EnforcesMenuPermissions;

    #[Title('Summary Visit Team Elite')]
    protected string $menuRoute = 'call-plan.jks-team-elite.summary-visit-team-elite'; 

    public $selectedMonth;

    public $regions = [];
    public $levels = [];
    public $teams = [];
    public $selectedRegion = '';
    public $selectedLevel = '';
    public $selectedTeam = '';
    public $selectedKeterangan = '';
    
    public $isFiltered = false;
    public $currentTab = 'summary';

    public $showRemarkModal = false;
    public $editingRemark = [
        'tanggal' => '',
        'team_code' => '',
        'custno' => '',
        'remark' => ''
    ];

    public function openRemarkModal($tanggal, $team_code, $custno, $currentRemark)
    {
        $this->editingRemark = [
            'tanggal' => $tanggal,
            'team_code' => $team_code,
            'custno' => $custno,
            'remark' => $currentRemark
        ];
        $this->showRemarkModal = true;
    }

    public function closeRemarkModal()
    {
        $this->showRemarkModal = false;
        $this->editingRemark = ['tanggal' => '', 'team_code' => '', 'custno' => '', 'remark' => ''];
    }

    public function saveRemark()
    {
        $exists = DB::table('zv_summary_visit_remarks')->where([
            'tanggal' => $this->editingRemark['tanggal'],
            'team_code' => $this->editingRemark['team_code'],
            'custno' => $this->editingRemark['custno'],
        ])->exists();

        $userName = auth()->user()->name ?? 'System';

        if ($exists) {
            DB::table('zv_summary_visit_remarks')->where([
                'tanggal' => $this->editingRemark['tanggal'],
                'team_code' => $this->editingRemark['team_code'],
                'custno' => $this->editingRemark['custno'],
            ])->update([
                'remark' => $this->editingRemark['remark'],
                'updated_by' => $userName,
                'updated_at' => Carbon::now(),
            ]);
        } else {
            DB::table('zv_summary_visit_remarks')->insert([
                'tanggal' => $this->editingRemark['tanggal'],
                'team_code' => $this->editingRemark['team_code'],
                'custno' => $this->editingRemark['custno'],
                'remark' => $this->editingRemark['remark'],
                'created_by' => $userName,
                'updated_by' => $userName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->closeRemarkModal();
        session()->flash('success', 'Remark berhasil disimpan.');
    }

    public function setTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function mount()
    {
        $this->selectedMonth = date('Y-m');
        $user = auth()->user();

        $regionQuery = DB::table('zv_summary_visit_team_elite')
            ->select('region_code', 'region_name')
            ->whereNotNull('region_code');
            
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $regionQuery->where('team_code', $user->supervisor_code);
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $regionQuery->whereIn('area_code', $user->area_code);
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionQuery->whereIn('region_code', $user->region_code);
            } else {
                $regionQuery->whereRaw('1 = 0');
            }
        }

        $this->regions = $regionQuery->distinct()
            ->orderBy('region_name')
            ->get()
            ->toArray();

        $query = DB::table('zv_summary_visit_team_elite')
            ->whereNotNull('level');
            
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $query->where('team_code', $user->supervisor_code);
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $query->whereIn('area_code', $user->area_code);
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $query->whereIn('region_code', $user->region_code);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $this->levels = $query->distinct()
            ->orderBy('level')
            ->pluck('level')
            ->toArray();

        if (count($this->regions) === 1) {
            $this->selectedRegion = $this->regions[0]->region_code;
            // Trigger updatedSelectedRegion manually since Livewire lifecycle doesn't trigger it on mount property change
            $this->updatedSelectedRegion($this->selectedRegion);
        }

        if (count($this->levels) === 1) {
            $this->selectedLevel = $this->levels[0];
            // Trigger updatedSelectedLevel manually
            $this->updatedSelectedLevel($this->selectedLevel);
        }

        // If we auto-selected level, check if we can auto-select team
        if ($this->selectedLevel && count($this->teams) === 1) {
            $this->selectedTeam = $this->teams[0]->team_code;
        }
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedLevel = '';
        $this->selectedTeam = '';
        $this->teams = [];
    }

    public function updatedSelectedLevel($value)
    {
        $this->selectedTeam = '';
        $this->teams = [];
        
        if ($value) {
            $query = DB::table('zv_summary_visit_team_elite')
                ->select('team_code', 'team_name')
                ->where('level', $value)
                ->whereNotNull('team_code');
                
            if ($this->selectedRegion) {
                $query->where('region_code', $this->selectedRegion);
            }
                
            $user = auth()->user();
            if ($user && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    $query->where('team_code', $user->supervisor_code);
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $query->whereIn('area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $query->whereIn('region_code', $user->region_code);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $this->teams = $query->distinct()
                ->orderBy('team_name')
                ->get()
                ->toArray();
        }
    }

    public function applyFilter()
    {
        $this->isFiltered = true;
    }

    #[Computed]
    public function dataKunjungan()
    {
        if (!$this->isFiltered || !$this->selectedMonth || empty($this->selectedTeam)) {
            return [];
        }

        $startDate = $this->selectedMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        $endDatePlus1 = Carbon::parse($endDate)->addDay()->format('Y-m-d');

        $visitConditions = "WHERE s.tanggal >= ? AND s.tanggal < ?";
        $dynamicBindings = [];

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $visitConditions .= " AND s.team_code = ?";
                $dynamicBindings[] = $user->supervisor_code;
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $areaCodes = $user->area_code;
                $placeholders = implode(',', array_fill(0, count($areaCodes), '?'));
                $visitConditions .= " AND s.area_code IN ($placeholders)";
                foreach ($areaCodes as $code) {
                    $dynamicBindings[] = $code;
                }
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionCodes = $user->region_code;
                $placeholders = implode(',', array_fill(0, count($regionCodes), '?'));
                $visitConditions .= " AND s.region_code IN ($placeholders)";
                foreach ($regionCodes as $code) {
                    $dynamicBindings[] = $code;
                }
            } else {
                $visitConditions .= " AND 1 = 0";
            }
        }

        if ($this->selectedRegion) {
            $visitConditions .= " AND s.region_code = ?";
            $dynamicBindings[] = $this->selectedRegion;
        }

        if ($this->selectedLevel) {
            $visitConditions .= " AND s.\"level\" = ?";
            $dynamicBindings[] = $this->selectedLevel;
        }

        if ($this->selectedTeam) {
            $visitConditions .= " AND s.team_code = ?";
            $dynamicBindings[] = $this->selectedTeam;
        }

        if ($this->selectedKeterangan) {
            $visitConditions .= " AND UPPER(s.keterangan) LIKE ?";
            $dynamicBindings[] = '%' . strtoupper($this->selectedKeterangan) . '%';
        }

        $visitBindings = array_merge([$startDate, $endDatePlus1], $dynamicBindings);

        $query = "
            WITH visit AS (
                SELECT
                    DATE_TRUNC('month', s.tanggal)::date AS bulan,
                    s.*
                FROM zv_summary_visit_team_elite s
                $visitConditions
            ),
            invoice AS (
                SELECT
                    v.bulan,
                    v.uniq_kd,
                    SUM(v.neto) AS invoice
                FROM zv_so_per_toko_2026 v
                WHERE v.bulan >= ?
                  AND v.bulan < ?
                GROUP BY
                    v.bulan,
                    v.uniq_kd
            ),
            ket_visit as(
                SELECT
                    DATE_TRUNC('month', s.tanggal)::date AS bulan,
                    s.*
                FROM zv_summary_visit_team_elite s
                $visitConditions
                  AND s.status_visit = 'Y'
            ),
            list_pareto as(
                select * from list_toko_pareto_team_elite ltpte 
            )
            SELECT
                v.region_code,
                v.region_name,
                v.area_code,
                v.area_name,
                v.\"level\",
                v.team_code,
                v.team_name,
                v.custno,
                v.uniq_id,
                v.custname,
                v.address,
                v.keterangan,
                case when k.status_visit is null then 'N' else k.status_visit end as status_visit,
                l.pilar,
                l.target,
                SUM(v.order_val) AS order_val,
                MAX(COALESCE(i.invoice,0)) AS invoice,
                MAX(rmk.remark) as remark,
                v.bulan
            FROM visit v
            LEFT JOIN invoice i
                ON v.bulan = i.bulan
               AND v.uniq_id = i.uniq_kd
            left join list_pareto l
                on v.custno = l.customer_code_prc 
            left join ket_visit k
                on v.custno = k.custno 
            left join zv_summary_visit_remarks rmk
                on v.bulan = rmk.tanggal
               and v.team_code = rmk.team_code
               and v.custno = rmk.custno
            GROUP BY
                v.bulan,
                v.region_code,
                v.region_name,
                v.area_code,
                v.area_name,
                v.\"level\",
                v.team_code,
                v.team_name,
                v.custno,
                v.uniq_id,
                v.custname,
                v.address,
                v.keterangan,
                k.status_visit,
                l.pilar,
                l.target
            ORDER BY v.region_name, v.area_name, v.\"level\", v.team_name, v.custno
        ";

        $bindings = array_merge($visitBindings, [$startDate, $endDatePlus1], $visitBindings);

        return DB::select($query, $bindings);
    }

    #[Computed]
    public function dataSummary()
    {
        if (!$this->isFiltered || !$this->selectedMonth) {
            return [];
        }

        $startDate = $this->selectedMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        $endDatePlus1 = Carbon::parse($endDate)->addDay()->format('Y-m-d');

        $visitConditions = "WHERE s.tanggal >= ? AND s.tanggal < ?";
        $dynamicBindings = [];

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $visitConditions .= " AND s.team_code = ?";
                $dynamicBindings[] = $user->supervisor_code;
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $areaCodes = $user->area_code;
                $placeholders = implode(',', array_fill(0, count($areaCodes), '?'));
                $visitConditions .= " AND s.area_code IN ($placeholders)";
                foreach ($areaCodes as $code) {
                    $dynamicBindings[] = $code;
                }
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionCodes = $user->region_code;
                $placeholders = implode(',', array_fill(0, count($regionCodes), '?'));
                $visitConditions .= " AND s.region_code IN ($placeholders)";
                foreach ($regionCodes as $code) {
                    $dynamicBindings[] = $code;
                }
            } else {
                $visitConditions .= " AND 1 = 0";
            }
        }

        if ($this->selectedRegion) {
            $visitConditions .= " AND s.region_code = ?";
            $dynamicBindings[] = $this->selectedRegion;
        }

        if ($this->selectedLevel) {
            $visitConditions .= " AND s.\"level\" = ?";
            $dynamicBindings[] = $this->selectedLevel;
        }

        $visitBindings = array_merge([$startDate, $endDatePlus1], $dynamicBindings);

        $query = "
            WITH visit AS (
                SELECT
                    DATE_TRUNC('month', s.tanggal)::date AS bulan,
                    s.*
                FROM zv_summary_visit_team_elite s
                $visitConditions
            ),
            invoice AS (
                SELECT
                    v.bulan,
                    v.uniq_kd,
                    SUM(v.neto) AS invoice
                FROM zv_so_per_toko_2026 v
                WHERE v.bulan >= ?
                  AND v.bulan < ?
                GROUP BY
                    v.bulan,
                    v.uniq_kd
            ),
            ket_visit as(
                SELECT
                    DATE_TRUNC('month', s.tanggal)::date AS bulan,
                    s.*
                FROM zv_summary_visit_team_elite s
                $visitConditions
                  AND s.status_visit = 'Y'
            ),
            list_pareto as(
                select * from list_toko_pareto_team_elite ltpte 
            ),
            detail AS (
                SELECT
                    v.region_code,
                    v.region_name,
                    v.area_code,
                    v.area_name,
                    v.\"level\",
                    v.team_code,
                    v.team_name,
                    v.custno,
                    v.uniq_id,
                    v.custname,
                    v.address,
                    v.keterangan,
                    case when k.status_visit is null then 'N' else k.status_visit end as status_visit,
                    l.pilar,
                    l.target,
                    SUM(v.order_val) AS order_val,
                    MAX(COALESCE(i.invoice,0)) AS invoice
                FROM visit v
                LEFT JOIN invoice i
                    ON v.bulan = i.bulan
                   AND v.uniq_id = i.uniq_kd
                left join list_pareto l
                    on v.custno = l.customer_code_prc 
                left join ket_visit k
                    on v.custno = k.custno 
                GROUP BY
                    v.region_code,
                    v.region_name,
                    v.area_code,
                    v.area_name,
                    v.\"level\",
                    v.team_code,
                    v.team_name,
                    v.custno,
                    v.uniq_id,
                    v.custname,
                    v.address,
                    v.keterangan,
                    k.status_visit,
                    l.pilar,
                    l.target
            )
            SELECT
                region_code, region_name, area_code, area_name, level, team_code, team_name,
                COUNT(custno) as total_toko,
                SUM(CASE WHEN status_visit = 'Y' THEN 1 ELSE 0 END) as total_visit,
                SUM(target) as total_target,
                SUM(order_val) as total_order,
                SUM(invoice) as total_invoice,
                SUM(CASE WHEN UPPER(pilar) LIKE '%1. RWO%' THEN 1 ELSE 0 END) as rwo_toko,
                SUM(CASE WHEN UPPER(pilar) LIKE '%1. RWO%' AND status_visit = 'Y' THEN 1 ELSE 0 END) as rwo_visit,
                SUM(CASE WHEN UPPER(pilar) LIKE '%2. PNR%' THEN 1 ELSE 0 END) as pnr_toko,
                SUM(CASE WHEN UPPER(pilar) LIKE '%2. PNR%' AND status_visit = 'Y' THEN 1 ELSE 0 END) as pnr_visit,
                SUM(CASE WHEN UPPER(pilar) LIKE '%3. NGVO%' THEN 1 ELSE 0 END) as ngvo_toko,
                SUM(CASE WHEN UPPER(pilar) LIKE '%3. NGVO%' AND status_visit = 'Y' THEN 1 ELSE 0 END) as ngvo_visit,
                SUM(CASE WHEN UPPER(keterangan) LIKE '%NOO%' THEN 1 ELSE 0 END) as noo_toko,
                SUM(CASE WHEN UPPER(keterangan) LIKE '%NOO%' AND status_visit = 'Y' THEN 1 ELSE 0 END) as noo_visit
            FROM detail
            GROUP BY region_code, region_name, area_code, area_name, level, team_code, team_name
            ORDER BY region_name, area_name, level, team_name
        ";

        $bindings = array_merge($visitBindings, [$startDate, $endDatePlus1], $visitBindings);

        return DB::select($query, $bindings);
    }

    #[Computed]
    public function kpiData()
    {
        if ($this->currentTab === 'detail') {
            $data = collect($this->dataKunjungan);
        } else {
            $data = collect($this->dataSummary);
        }
        
        if ($this->currentTab === 'detail') {
            $total_toko = $data->count();
            $total_visit = $data->filter(fn($item) => $item->status_visit === 'Y')->count();
            $total_order = $data->sum('order_val');
            $total_target = $data->sum('target');
            $total_invoice = $data->sum('invoice');
            
            $total_rwo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '1. RWO'))->count();
            $total_pnr = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '2. PNR'))->count();
            $total_ngvo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '3. NGVO'))->count();
            
            $total_order_rwo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '1. RWO'))->sum('order_val');
            $total_order_pnr = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '2. PNR'))->sum('order_val');
            $total_order_ngvo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '3. NGVO'))->sum('order_val');
            
            $toko_order_rwo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '1. RWO') && (float)($item->order_val ?? 0) > 0)->count();
            $toko_order_pnr = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '2. PNR') && (float)($item->order_val ?? 0) > 0)->count();
            $toko_order_ngvo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '3. NGVO') && (float)($item->order_val ?? 0) > 0)->count();
            
            $total_noo = $data->filter(fn($item) => str_contains(strtoupper($item->keterangan ?? ''), 'NOO'))->count();
            $total_toko_order = $data->filter(fn($item) => (float)($item->order_val ?? 0) > 0)->count();
        } else {
            $total_toko = $data->sum('total_toko');
            $total_visit = $data->sum('total_visit');
            $total_order = $data->sum('total_order');
            $total_target = $data->sum('total_target');
            $total_invoice = $data->sum('total_invoice');

            $total_rwo = $data->sum('rwo_toko');
            $total_pnr = $data->sum('pnr_toko');
            $total_ngvo = $data->sum('ngvo_toko');
            
            $total_order_rwo = 0; // we don't aggregate order val by pilar in summary yet
            $total_order_pnr = 0;
            $total_order_ngvo = 0;
            
            $toko_order_rwo = $data->sum('rwo_visit'); // proxy to visit instead of order for summary KPI
            $toko_order_pnr = $data->sum('pnr_visit');
            $toko_order_ngvo = $data->sum('ngvo_visit');
            
            $total_noo = $data->sum('noo_toko');
            $total_toko_order = 0; // maybe proxy to total_visit
        }

        return [
            'total_toko' => $total_toko,
            'total_visit' => $total_visit,
            'total_order' => $total_order,
            'total_toko_order' => $total_toko_order,
            'total_target' => $total_target,
            'total_invoice' => $total_invoice,
            'total_rwo' => $total_rwo,
            'total_pnr' => $total_pnr,
            'total_ngvo' => $total_ngvo,
            'total_order_rwo' => $total_order_rwo,
            'total_order_pnr' => $total_order_pnr,
            'total_order_ngvo' => $total_order_ngvo,
            'toko_order_rwo' => $toko_order_rwo,
            'toko_order_pnr' => $toko_order_pnr,
            'toko_order_ngvo' => $toko_order_ngvo,
            'total_noo' => $total_noo,
        ];
    }

    public function render()
    {
        return view('livewire.call-plan.jks-team-elite.summary-visit-team-elite.index', [
            'dataKunjungan' => $this->dataKunjungan,
            'dataSummary' => $this->dataSummary,
            'kpiData' => $this->kpiData
        ])->layout('layouts.app');
    }
}
