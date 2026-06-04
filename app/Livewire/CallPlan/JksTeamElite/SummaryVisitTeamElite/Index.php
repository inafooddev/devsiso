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

    public $levels = [];
    public $teams = [];
    public $selectedLevel = '';
    public $selectedTeam = '';
    public $selectedKeterangan = '';
    
    public $isFiltered = false;

    public function mount()
    {
        $this->selectedMonth = date('Y-m');

        $this->levels = DB::table('zv_summary_visit_team_elite')
            ->whereNotNull('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level')
            ->toArray();
    }

    public function updatedSelectedLevel($value)
    {
        $this->selectedTeam = '';
        $this->teams = [];
        
        if ($value) {
            $this->teams = DB::table('zv_summary_visit_team_elite')
                ->select('team_code', 'team_name')
                ->where('level', $value)
                ->whereNotNull('team_code')
                ->distinct()
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
            ORDER BY v.custno
        ";

        $bindings = array_merge($visitBindings, [$startDate, $endDatePlus1], $visitBindings);

        return DB::select($query, $bindings);
    }

    #[Computed]
    public function kpiData()
    {
        $data = collect($this->dataKunjungan);
        
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
            'kpiData' => $this->kpiData
        ])->layout('layouts.app');
    }
}
