<?php

namespace App\Livewire\Others\Qceskalink;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\QcEskalinkAnomalyExport;

class AnomalyCheck extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $monthFilter;
    public $regionFilter = '';
    public $branchFilter = '';
    /**
     * Pilihan anomalyType:
     * - ALL                : Semua Anomali QTY vs Nett
     * - QTY_NEG_NET_POS    : qty3_pcs < 0 AND nett_amount > 0
     * - QTY_POS_NET_NEG    : qty3_pcs > 0 AND nett_amount < 0
     * - QTY_ZERO_NET_VAL   : qty3_pcs = 0 AND (nett_amount <> 0 OR qty1_car <> 0 OR qty2_pck <> 0)
     * - NON_BONUS_NET_ZERO : qty3_pcs <> 0 AND nett_amount = 0 AND flag_bonus != 'Y'
     * - BONUS_HAS_NET      : flag_bonus = 'Y' AND nett_amount <> 0
     * - RET_POSITIVE       : invoice_type = 'RET' AND (qty3_pcs > 0 OR nett_amount > 0)
     */
    public $anomalyType = 'ALL';
    public $search = '';
    public $viewMode = 'summary'; // Default: 'summary' (Rekap Distributor), klik Detail -> 'detail'
    public $perPage = 50;

    public function mount()
    {
        $this->monthFilter = date('Y-m', strtotime('-1 month'));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRegionFilter()
    {
        $this->branchFilter = '';
        $this->resetPage();
    }

    public function updatingBranchFilter()
    {
        $this->resetPage();
    }

    public function updatingAnomalyType()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
    {
        $this->resetPage();
    }

    public function updatingViewMode()
    {
        $this->resetPage();
    }

    public function filterByAnomalyType($type)
    {
        $this->anomalyType = $type;
        $this->resetPage();
    }

    public function drillDownBranch($branchCode, $anomalyType = null)
    {
        $this->branchFilter = $branchCode;
        if ($anomalyType) {
            $this->anomalyType = $anomalyType;
        }
        $this->viewMode = 'detail';
        $this->resetPage();
    }

    public function backToSummary()
    {
        $this->branchFilter = '';
        $this->viewMode = 'summary';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->regionFilter = '';
        $this->branchFilter = '';
        $this->anomalyType = 'ALL';
        $this->search = '';
        $this->viewMode = 'summary';
        $this->resetPage();
    }

    public function export()
    {
        $fileName = 'Anomali_SO_Eskalink_' . $this->monthFilter . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(
            new QcEskalinkAnomalyExport(
                $this->monthFilter,
                $this->regionFilter,
                $this->branchFilter,
                $this->anomalyType,
                $this->search
            ),
            $fileName
        );
    }

    /**
     * Terapkan kondisi anomali qty3_pcs vs nett_amount pada query builder.
     */
    private function applyAnomalyCondition($query, $type = 'ALL')
    {
        if ($type === 'QTY_NEG_NET_POS') {
            $query->where('soe.qty3_pcs', '<', 0)->where('soe.nett_amount', '>', 0);
        } elseif ($type === 'QTY_POS_NET_NEG') {
            $query->where('soe.qty3_pcs', '>', 0)->where('soe.nett_amount', '<', 0);
        } elseif ($type === 'QTY_ZERO_NET_VAL') {
            $query->where('soe.qty3_pcs', '=', 0)
                  ->where(function ($q) {
                      $q->where('soe.nett_amount', '<>', 0)
                        ->orWhere('soe.qty1_car', '<>', 0)
                        ->orWhere('soe.qty2_pck', '<>', 0);
                  });
        } elseif ($type === 'NON_BONUS_NET_ZERO') {
            $query->where('soe.qty3_pcs', '<>', 0)
                  ->where('soe.nett_amount', '=', 0)
                  ->whereRaw("COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS')");
        } elseif ($type === 'BONUS_HAS_NET') {
            $query->whereRaw("UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS')")
                  ->where('soe.nett_amount', '<>', 0);
        } elseif ($type === 'RET_POSITIVE') {
            $query->whereRaw("UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R')")
                  ->where(function ($q) {
                      $q->where('soe.qty3_pcs', '>', 0)
                        ->orWhere('soe.nett_amount', '>', 0);
                  });
        } else {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('soe.qty3_pcs', '<', 0)->where('soe.nett_amount', '>', 0);
                })
                ->orWhere(function ($sub) {
                    $sub->where('soe.qty3_pcs', '>', 0)->where('soe.nett_amount', '<', 0);
                })
                ->orWhere(function ($sub) {
                    $sub->where('soe.qty3_pcs', '=', 0)
                        ->where(function ($inner) {
                            $inner->where('soe.nett_amount', '<>', 0)
                                  ->orWhere('soe.qty1_car', '<>', 0)
                                  ->orWhere('soe.qty2_pck', '<>', 0);
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->where('soe.qty3_pcs', '<>', 0)
                        ->where('soe.nett_amount', '=', 0)
                        ->whereRaw("COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS')");
                })
                ->orWhere(function ($sub) {
                    $sub->whereRaw("UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS')")
                        ->where('soe.nett_amount', '<>', 0);
                })
                ->orWhere(function ($sub) {
                    $sub->whereRaw("UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R')")
                        ->where(function ($inner) {
                            $inner->where('soe.qty3_pcs', '>', 0)
                                  ->orWhere('soe.nett_amount', '>', 0);
                        });
                });
            });
        }

        return $query;
    }

    public function render()
    {
        $month = $this->monthFilter ?: date('Y-m', strtotime('-1 month'));
        $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth()->format('Y-m-d');
        $user = Auth::user();

        // 1. Daftar Opsi Region
        $regionsQuery = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('is_active', true)
            ->where('region_code', '<>', 'HOINA');

        if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
            $regionsQuery->whereIn('region_code', (array) $user->region_code);
        }

        $regions = $regionsQuery->groupBy('region_code', 'region_name')
            ->orderBy('region_name')
            ->get();

        // 2. Daftar Opsi Distributor / Branch
        $branchesQuery = DB::table('distributor_implementasi_eskalink as die')
            ->join('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->where('die.implementasi', 'Y')
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA')
            ->whereNotNull('die.eskalink_code')
            ->select('die.eskalink_code as branch_code', 'md.distributor_name');

        if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
            $branchesQuery->whereIn('md.region_code', (array) $user->region_code);
        }

        if (!empty($this->regionFilter)) {
            $branchesQuery->where('md.region_code', $this->regionFilter);
        }

        $branches = $branchesQuery->orderBy('md.distributor_name')->get();

        // 3. Base Query untuk KPI Summary (terfilter bulan, region, branch, search)
        $kpiQuery = DB::table('selling_out_eskalink as soe')
            ->leftJoin('distributor_implementasi_eskalink as die', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->whereBetween('soe.invoice_date', [$startOfMonth, $endOfMonth]);

        if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
            $kpiQuery->whereIn(DB::raw('COALESCE(md.region_code, soe.region_code)'), (array) $user->region_code);
        }

        if (!empty($this->regionFilter)) {
            $kpiQuery->where(DB::raw('COALESCE(md.region_code, soe.region_code)'), $this->regionFilter);
        }

        if (!empty($this->branchFilter)) {
            $kpiQuery->where('soe.branch_code', $this->branchFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $kpiQuery->where(function ($q) use ($s) {
                $q->where('soe.invoice_no', 'ilike', $s)
                  ->orWhere('soe.ord_no', 'ilike', $s)
                  ->orWhere('soe.prd_code', 'ilike', $s)
                  ->orWhere('soe.prd_name', 'ilike', $s)
                  ->orWhere('soe.cust_name', 'ilike', $s)
                  ->orWhere('soe.cust_code_dist', 'ilike', $s)
                  ->orWhere('soe.branch_code', 'ilike', $s)
                  ->orWhere('soe.branch_name', 'ilike', $s);
            });
        }

        $this->applyAnomalyCondition($kpiQuery, 'ALL');

        $kpi = (clone $kpiQuery)->selectRaw("
            COUNT(*) as total_rows,
            COUNT(DISTINCT soe.branch_code) as affected_branches,
            SUM(CASE WHEN soe.qty3_pcs < 0 AND soe.nett_amount > 0 THEN 1 ELSE 0 END) as cnt_qty_neg_net_pos,
            COALESCE(SUM(CASE WHEN soe.qty3_pcs < 0 AND soe.nett_amount > 0 THEN soe.nett_amount ELSE 0 END), 0) as sum_qty_neg_net_pos,
            SUM(CASE WHEN soe.qty3_pcs > 0 AND soe.nett_amount < 0 THEN 1 ELSE 0 END) as cnt_qty_pos_net_neg,
            COALESCE(SUM(CASE WHEN soe.qty3_pcs > 0 AND soe.nett_amount < 0 THEN soe.nett_amount ELSE 0 END), 0) as sum_qty_pos_net_neg,
            SUM(CASE WHEN soe.qty3_pcs = 0 AND (soe.nett_amount <> 0 OR soe.qty1_car <> 0 OR soe.qty2_pck <> 0) THEN 1 ELSE 0 END) as cnt_qty_zero_net_val,
            COALESCE(SUM(CASE WHEN soe.qty3_pcs = 0 AND (soe.nett_amount <> 0 OR soe.qty1_car <> 0 OR soe.qty2_pck <> 0) THEN soe.nett_amount ELSE 0 END), 0) as sum_qty_zero_net_val,
            SUM(CASE WHEN soe.qty3_pcs <> 0 AND soe.nett_amount = 0 AND COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS') THEN 1 ELSE 0 END) as cnt_non_bonus_net_zero,
            COALESCE(SUM(CASE WHEN soe.qty3_pcs <> 0 AND soe.nett_amount = 0 AND COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS') THEN soe.qty3_pcs ELSE 0 END), 0) as sum_qty_non_bonus_zero,
            SUM(CASE WHEN UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS') AND soe.nett_amount <> 0 THEN 1 ELSE 0 END) as cnt_bonus_has_net,
            COALESCE(SUM(CASE WHEN UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS') AND soe.nett_amount <> 0 THEN soe.nett_amount ELSE 0 END), 0) as sum_bonus_has_net,
            SUM(CASE WHEN UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R') AND (soe.qty3_pcs > 0 OR soe.nett_amount > 0) THEN 1 ELSE 0 END) as cnt_ret_positive,
            COALESCE(SUM(CASE WHEN UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R') AND (soe.qty3_pcs > 0 OR soe.nett_amount > 0) THEN soe.nett_amount ELSE 0 END), 0) as sum_ret_positive
        ")->first();

        // 4. Query Tabel sesuai View Mode ('detail' atau 'summary')
        $baseTableQuery = DB::table('selling_out_eskalink as soe')
            ->leftJoin('distributor_implementasi_eskalink as die', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->whereBetween('soe.invoice_date', [$startOfMonth, $endOfMonth]);

        if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
            $baseTableQuery->whereIn(DB::raw('COALESCE(md.region_code, soe.region_code)'), (array) $user->region_code);
        }

        if (!empty($this->regionFilter)) {
            $baseTableQuery->where(DB::raw('COALESCE(md.region_code, soe.region_code)'), $this->regionFilter);
        }

        if (!empty($this->branchFilter)) {
            $baseTableQuery->where('soe.branch_code', $this->branchFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $baseTableQuery->where(function ($q) use ($s) {
                $q->where('soe.invoice_no', 'ilike', $s)
                  ->orWhere('soe.ord_no', 'ilike', $s)
                  ->orWhere('soe.prd_code', 'ilike', $s)
                  ->orWhere('soe.prd_name', 'ilike', $s)
                  ->orWhere('soe.cust_name', 'ilike', $s)
                  ->orWhere('soe.cust_code_dist', 'ilike', $s)
                  ->orWhere('soe.branch_code', 'ilike', $s)
                  ->orWhere('soe.branch_name', 'ilike', $s);
            });
        }

        $this->applyAnomalyCondition($baseTableQuery, $this->anomalyType);

        if ($this->viewMode === 'summary') {
            $rows = $baseTableQuery
                ->select(
                    DB::raw('COALESCE(md.region_name, soe.region_name) as region_name'),
                    DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name) as area_name'),
                    'soe.branch_code',
                    DB::raw('COALESCE(md.distributor_name, soe.branch_name) as branch_name'),
                    DB::raw('COUNT(*) as total_anomaly'),
                    DB::raw('SUM(CASE WHEN soe.qty3_pcs < 0 AND soe.nett_amount > 0 THEN 1 ELSE 0 END) as cnt_qty_neg_net_pos'),
                    DB::raw('SUM(CASE WHEN soe.qty3_pcs > 0 AND soe.nett_amount < 0 THEN 1 ELSE 0 END) as cnt_qty_pos_net_neg'),
                    DB::raw('SUM(CASE WHEN soe.qty3_pcs = 0 AND (soe.nett_amount <> 0 OR soe.qty1_car <> 0 OR soe.qty2_pck <> 0) THEN 1 ELSE 0 END) as cnt_qty_zero_net_val'),
                    DB::raw("SUM(CASE WHEN soe.qty3_pcs <> 0 AND soe.nett_amount = 0 AND COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS') THEN 1 ELSE 0 END) as cnt_non_bonus_net_zero"),
                    DB::raw("SUM(CASE WHEN UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS') AND soe.nett_amount <> 0 THEN 1 ELSE 0 END) as cnt_bonus_has_net"),
                    DB::raw("SUM(CASE WHEN UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R') AND (soe.qty3_pcs > 0 OR soe.nett_amount > 0) THEN 1 ELSE 0 END) as cnt_ret_positive"),
                    DB::raw('SUM(soe.qty3_pcs) as total_qty_pcs'),
                    DB::raw('SUM(soe.nett_amount) as total_nett_amount')
                )
                ->groupBy(
                    DB::raw('COALESCE(md.region_name, soe.region_name)'),
                    DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name)'),
                    'soe.branch_code',
                    DB::raw('COALESCE(md.distributor_name, soe.branch_name)')
                )
                ->orderBy(DB::raw('COALESCE(md.region_name, soe.region_name)'))
                ->orderBy(DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name)'))
                ->orderBy(DB::raw('COALESCE(md.distributor_name, soe.branch_name)'))
                ->paginate($this->perPage);
        } else {
            $rows = $baseTableQuery
                ->select(
                    'soe.id',
                    DB::raw('COALESCE(md.region_name, soe.region_name) as region_name'),
                    DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name) as area_name'),
                    'soe.branch_code',
                    DB::raw('COALESCE(md.distributor_name, soe.branch_name) as branch_name'),
                    'soe.invoice_date',
                    'soe.invoice_no',
                    'soe.invoice_type',
                    'soe.ord_no',
                    'soe.cust_code_dist',
                    'soe.cust_name',
                    'soe.prd_code',
                    'soe.prd_name',
                    'soe.flag_bonus',
                    'soe.qty1_car',
                    'soe.qty2_pck',
                    'soe.qty3_pcs',
                    'soe.gross_amount',
                    'soe.line_discount_4',
                    'soe.line_discount_8',
                    'soe.total_line_discount',
                    'soe.dpp',
                    'soe.tax',
                    'soe.nett_amount',
                    DB::raw("
                        CASE
                            WHEN soe.qty3_pcs < 0 AND soe.nett_amount > 0 THEN 'QTY_NEG_NET_POS'
                            WHEN soe.qty3_pcs > 0 AND soe.nett_amount < 0 THEN 'QTY_POS_NET_NEG'
                            WHEN soe.qty3_pcs = 0 AND (soe.nett_amount <> 0 OR soe.qty1_car <> 0 OR soe.qty2_pck <> 0) THEN 'QTY_ZERO_NET_VAL'
                            WHEN UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS') AND soe.nett_amount <> 0 THEN 'BONUS_HAS_NET'
                            WHEN soe.qty3_pcs <> 0 AND soe.nett_amount = 0 AND COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS') THEN 'NON_BONUS_NET_ZERO'
                            WHEN UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R') AND (soe.qty3_pcs > 0 OR soe.nett_amount > 0) THEN 'RET_POSITIVE'
                            ELSE 'OTHER'
                        END as anomaly_code
                    ")
                )
                ->orderBy(DB::raw('COALESCE(md.region_name, soe.region_name)'))
                ->orderBy(DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name)'))
                ->orderBy(DB::raw('COALESCE(md.distributor_name, soe.branch_name)'))
                ->orderBy('soe.invoice_date')
                ->orderBy('soe.invoice_no')
                ->paginate($this->perPage);
        }

        return view('livewire.others.qceskalink.anomaly-check', [
            'regions'  => $regions,
            'branches' => $branches,
            'kpi'      => $kpi,
            'rows'     => $rows,
        ])->layout('layouts.app');
    }
}
