<?php

namespace App\Livewire\Others\Qceskalink;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\QcEskalinkReferenceNumberExport;

class ReferenceNumber extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public $monthFilter;
    public $regionFilter = '';
    public $branchFilter = '';
    /**
     * Pilihan statusFilter:
     * - 'ALL'              : Semua Cabang Implementasi (76 Cabang)
     * - 'PULLED'           : Cabang Sudah Tarik Data SO
     * - 'NOT_PULLED'       : Cabang Belum Tarik Data SO
     * - 'DISC_WITHOUT_ORD' : Kritis Klaim (Ada Disc 4/8 tapi No Order Kosong)
     * - 'WITHOUT_ORD'      : Semua Nota Tanpa No Order
     * - 'WITH_ORD'         : Nota Lengkap No Order
     */
    public $statusFilter = 'ALL';
    public $search = '';

    // UI View Mode: 'summary' (Rekap Distributor) vs 'detail' (Detail Nota / Transaksi)
    public $viewMode = 'summary';
    public $perPage = 50;

    // Sorting
    public $sortField = 'region_name';
    public $sortDirection = 'asc';

    public function mount()
    {
        $this->monthFilter = Carbon::now()->subMonth()->format('Y-m');
        $this->regionFilter = '';
        $this->branchFilter = '';
        $this->statusFilter = 'ALL';
        $this->viewMode     = 'summary';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
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

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingViewMode()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $numericFields = [
                'total_invoices', 'invoices_with_ord', 'invoices_without_ord',
                'disc_without_ord_inv', 'disc4_val_without_ord', 'disc8_val_without_ord', 'disc8_qty_without_ord',
                'total_disc_val', 'qty3_pcs', 'line_discount_4', 'line_discount_8', 'nett_amount'
            ];
            $this->sortDirection = in_array($field, $numericFields) ? 'desc' : 'asc';
        }
        $this->resetPage();
    }

    public function quickFilter($status = 'ALL')
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function drillDownBranch($branchCode, $status = null)
    {
        $this->branchFilter = $branchCode;
        if ($status !== null) {
            $this->statusFilter = $status;
        }
        $this->viewMode = 'detail';
        $this->sortField = 'invoice_date';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function backToSummary()
    {
        $this->branchFilter = '';
        $this->viewMode = 'summary';
        $this->sortField = 'region_name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->regionFilter  = '';
        $this->branchFilter  = '';
        $this->statusFilter  = 'ALL';
        $this->search        = '';
        $this->viewMode      = 'summary';
        $this->sortField     = 'region_name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function export()
    {
        $modeName = $this->viewMode === 'detail' ? 'Detail' : 'Rekap';
        $fileName = "Referensi_Number_{$modeName}_{$this->monthFilter}_" . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new QcEskalinkReferenceNumberExport(
                $this->monthFilter,
                $this->regionFilter,
                $this->branchFilter,
                $this->statusFilter,
                $this->search,
                $this->viewMode
            ),
            $fileName
        );
    }

    public function render()
    {
        $startDate = Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        $endDate   = Carbon::parse($this->monthFilter)->endOfMonth()->format('Y-m-d');
        $user      = Auth::user();

        // 1. Opsi Region
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

        // 2. Opsi Branch / Distributor
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

        // 3. Base Query untuk seluruh transaksi pada rentang tanggal & proteksi akses
        $baseQuery = DB::table('selling_out_eskalink as soe')
            ->leftJoin('distributor_implementasi_eskalink as die', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->whereBetween('soe.invoice_date', [$startDate, $endDate]);

        if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
            $baseQuery->whereIn(DB::raw('COALESCE(md.region_code, soe.region_code)'), (array) $user->region_code);
        }

        if (!empty($this->regionFilter)) {
            $baseQuery->where(DB::raw('COALESCE(md.region_code, soe.region_code)'), $this->regionFilter);
        }

        if (!empty($this->branchFilter)) {
            $baseQuery->where('soe.branch_code', $this->branchFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($s) {
                $q->where('soe.invoice_no', 'ilike', $s)
                  ->orWhere('soe.ord_no', 'ilike', $s)
                  ->orWhere('soe.branch_code', 'ilike', $s)
                  ->orWhere('soe.branch_name', 'ilike', $s)
                  ->orWhere('soe.cust_name', 'ilike', $s)
                  ->orWhere('soe.prd_code', 'ilike', $s)
                  ->orWhere('soe.prd_name', 'ilike', $s);
            });
        }

        // 4. Hitung KPI Summary Keseluruhan
        // Catatan: line_discount_4 berupa Rupiah (Rp), sedangkan line_discount_8 berupa Qty / Pcs (Bonus Barang).
        $kpi = (clone $baseQuery)->selectRaw("
            COUNT(DISTINCT soe.invoice_no) as total_invoices,
            COUNT(DISTINCT CASE WHEN soe.ord_no IS NOT NULL AND TRIM(soe.ord_no) NOT IN ('', '0', '-') THEN soe.invoice_no END) as invoices_with_ord,
            COUNT(DISTINCT CASE WHEN soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-') THEN soe.invoice_no END) as invoices_without_ord,
            COUNT(DISTINCT CASE WHEN (COALESCE(soe.line_discount_4, 0) != 0 OR COALESCE(soe.line_discount_8, 0) != 0) AND (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN soe.invoice_no END) as disc_without_ord_inv,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_4, 0) ELSE 0 END), 0) as disc4_val_without_ord,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_8, 0) ELSE 0 END), 0) as disc8_qty_without_ord,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_8, 0) ELSE 0 END), 0) as disc8_val_without_ord,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_4, 0) ELSE 0 END), 0) as total_disc_val
        ")->first();

        // 5. Query Tabel Data (Summary atau Detail)
        if ($this->viewMode === 'summary') {
            // Subquery agregat per cabang dari selling_out_eskalink
            $soeSub = DB::table('selling_out_eskalink')
                ->select(
                    'branch_code',
                    DB::raw('COUNT(DISTINCT invoice_no) as total_invoices'),
                    DB::raw("COUNT(DISTINCT CASE WHEN ord_no IS NOT NULL AND TRIM(ord_no) NOT IN ('', '0', '-') THEN invoice_no END) as invoices_with_ord"),
                    DB::raw("COUNT(DISTINCT CASE WHEN ord_no IS NULL OR TRIM(ord_no) IN ('', '0', '-') THEN invoice_no END) as invoices_without_ord"),
                    DB::raw("COUNT(DISTINCT CASE WHEN (COALESCE(line_discount_4, 0) != 0 OR COALESCE(line_discount_8, 0) != 0) AND (ord_no IS NULL OR TRIM(ord_no) IN ('', '0', '-')) THEN invoice_no END) as disc_without_ord_inv"),
                    DB::raw("COALESCE(SUM(CASE WHEN (ord_no IS NULL OR TRIM(ord_no) IN ('', '0', '-')) THEN COALESCE(line_discount_4, 0) ELSE 0 END), 0) as disc4_val_without_ord"),
                    DB::raw("COALESCE(SUM(CASE WHEN (ord_no IS NULL OR TRIM(ord_no) IN ('', '0', '-')) THEN COALESCE(line_discount_8, 0) ELSE 0 END), 0) as disc8_qty_without_ord"),
                    DB::raw("COALESCE(SUM(CASE WHEN (ord_no IS NULL OR TRIM(ord_no) IN ('', '0', '-')) THEN COALESCE(line_discount_8, 0) ELSE 0 END), 0) as disc8_val_without_ord"),
                    DB::raw("COALESCE(SUM(CASE WHEN (ord_no IS NULL OR TRIM(ord_no) IN ('', '0', '-')) THEN COALESCE(line_discount_4, 0) ELSE 0 END), 0) as total_disc_val")
                )
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->groupBy('branch_code');

            // Base Query berbasis Master Implementasi Eskalink (76 Cabang)
            $summaryBase = DB::table('distributor_implementasi_eskalink as die')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->leftJoinSub($soeSub, 'soe', 'die.eskalink_code', '=', 'soe.branch_code')
                ->where('die.implementasi', 'Y')
                ->where('md.is_active', true)
                ->where('md.region_code', '<>', 'HOINA');

            if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
                $summaryBase->whereIn('md.region_code', (array) $user->region_code);
            }

            if (!empty($this->regionFilter)) {
                $summaryBase->where('md.region_code', $this->regionFilter);
            }

            if (!empty($this->branchFilter)) {
                $summaryBase->where('die.eskalink_code', $this->branchFilter);
            }

            if (!empty($this->search)) {
                $s = '%' . $this->search . '%';
                $summaryBase->where(function ($q) use ($s, $startDate, $endDate) {
                    $q->where('die.eskalink_code', 'ilike', $s)
                      ->orWhere('md.distributor_code', 'ilike', $s)
                      ->orWhere('md.distributor_name', 'ilike', $s)
                      ->orWhere('md.area_name', 'ilike', $s)
                      ->orWhere('md.region_name', 'ilike', $s)
                      ->orWhereExists(function ($sub) use ($s, $startDate, $endDate) {
                          $sub->select(DB::raw(1))
                              ->from('selling_out_eskalink as sub_soe')
                              ->whereColumn('sub_soe.branch_code', 'die.eskalink_code')
                              ->whereBetween('sub_soe.invoice_date', [$startDate, $endDate])
                              ->where(function ($iq) use ($s) {
                                  $iq->where('sub_soe.invoice_no', 'ilike', $s)
                                     ->orWhere('sub_soe.ord_no', 'ilike', $s)
                                     ->orWhere('sub_soe.cust_name', 'ilike', $s)
                                     ->orWhere('sub_soe.prd_code', 'ilike', $s)
                                     ->orWhere('sub_soe.prd_name', 'ilike', $s);
                              });
                      });
                });
            }

            if ($this->statusFilter === 'PULLED') {
                $summaryBase->where('soe.total_invoices', '>', 0);
            } elseif ($this->statusFilter === 'NOT_PULLED') {
                $summaryBase->where(function ($q) {
                    $q->whereNull('soe.total_invoices')
                      ->orWhere('soe.total_invoices', '=', 0);
                });
            } elseif ($this->statusFilter === 'DISC_WITHOUT_ORD') {
                $summaryBase->where('soe.disc_without_ord_inv', '>', 0);
            } elseif ($this->statusFilter === 'WITHOUT_ORD') {
                $summaryBase->where('soe.invoices_without_ord', '>', 0);
            } elseif ($this->statusFilter === 'WITH_ORD') {
                $summaryBase->where('soe.total_invoices', '>', 0)
                            ->where(function ($q) {
                                $q->whereNull('soe.invoices_without_ord')
                                  ->orWhere('soe.invoices_without_ord', '=', 0);
                            });
            }

            $allowedSorts = [
                'region_name'           => 'md.region_name',
                'area_name'             => 'md.area_name',
                'branch_code'           => 'die.eskalink_code',
                'branch_name'           => 'md.distributor_name',
                'total_invoices'        => DB::raw('COALESCE(soe.total_invoices, 0)'),
                'invoices_with_ord'     => DB::raw('COALESCE(soe.invoices_with_ord, 0)'),
                'invoices_without_ord'  => DB::raw('COALESCE(soe.invoices_without_ord, 0)'),
                'disc_without_ord_inv'  => DB::raw('COALESCE(soe.disc_without_ord_inv, 0)'),
                'disc4_val_without_ord' => DB::raw('COALESCE(soe.disc4_val_without_ord, 0)'),
                'disc8_val_without_ord' => DB::raw('COALESCE(soe.disc8_val_without_ord, 0)'),
                'disc8_qty_without_ord' => DB::raw('COALESCE(soe.disc8_qty_without_ord, 0)'),
                'total_disc_val'        => DB::raw('COALESCE(soe.total_disc_val, 0)'),
            ];

            $orderCol = $allowedSorts[$this->sortField] ?? 'md.region_name';
            $dir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

            $rows = (clone $summaryBase)->select(
                'md.region_name',
                'md.area_name',
                'die.eskalink_code as branch_code',
                'md.distributor_name as branch_name',
                DB::raw('COALESCE(soe.total_invoices, 0) as total_invoices'),
                DB::raw('COALESCE(soe.invoices_with_ord, 0) as invoices_with_ord'),
                DB::raw('COALESCE(soe.invoices_without_ord, 0) as invoices_without_ord'),
                DB::raw('COALESCE(soe.disc_without_ord_inv, 0) as disc_without_ord_inv'),
                DB::raw('COALESCE(soe.disc4_val_without_ord, 0) as disc4_val_without_ord'),
                DB::raw('COALESCE(soe.disc8_qty_without_ord, 0) as disc8_qty_without_ord'),
                DB::raw('COALESCE(soe.disc8_val_without_ord, 0) as disc8_val_without_ord'),
                DB::raw('COALESCE(soe.total_disc_val, 0) as total_disc_val')
            )
            ->orderBy($orderCol, $dir)
            ->orderBy('md.region_name')
            ->orderBy('md.area_name')
            ->orderBy('md.distributor_name')
            ->paginate($this->perPage);

            // Summary Totals untuk Baris Sticky Grand Total
            $totals = (clone $summaryBase)->selectRaw("
                COALESCE(SUM(soe.total_invoices), 0) as total_invoices,
                COALESCE(SUM(soe.invoices_with_ord), 0) as invoices_with_ord,
                COALESCE(SUM(soe.invoices_without_ord), 0) as invoices_without_ord,
                COALESCE(SUM(soe.disc_without_ord_inv), 0) as disc_without_ord_inv,
                COALESCE(SUM(soe.disc4_val_without_ord), 0) as disc4_val_without_ord,
                COALESCE(SUM(soe.disc8_qty_without_ord), 0) as disc8_qty_without_ord,
                COALESCE(SUM(soe.disc8_val_without_ord), 0) as disc8_val_without_ord,
                COALESCE(SUM(soe.total_disc_val), 0) as total_disc_val
            ")->first();

        } else {
            // Mode Detail Baris
            $tableQuery = (clone $baseQuery)->select(
                'soe.id',
                DB::raw('COALESCE(md.region_name, soe.region_name) as region_name'),
                DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name) as area_name'),
                'soe.branch_code',
                DB::raw('COALESCE(md.distributor_name, soe.branch_name) as branch_name'),
                'soe.invoice_date',
                'soe.invoice_no',
                'soe.invoice_type',
                'soe.ord_date',
                'soe.ord_no',
                'soe.cust_code_dist',
                'soe.cust_name',
                'soe.prd_code',
                'soe.prd_name',
                'soe.qty3_pcs',
                'soe.line_discount_4',
                'soe.line_discount_8',
                'soe.nett_amount'
            );

            if ($this->statusFilter === 'DISC_WITHOUT_ORD') {
                $tableQuery->where(function ($q) {
                    $q->where('soe.line_discount_4', '<>', 0)
                      ->orWhere('soe.line_discount_8', '<>', 0);
                })->where(function ($q) {
                    $q->whereNull('soe.ord_no')
                      ->orWhereRaw("TRIM(soe.ord_no) IN ('', '0', '-')");
                });
            } elseif ($this->statusFilter === 'WITHOUT_ORD') {
                $tableQuery->where(function ($q) {
                    $q->whereNull('soe.ord_no')
                      ->orWhereRaw("TRIM(soe.ord_no) IN ('', '0', '-')");
                });
            } elseif ($this->statusFilter === 'WITH_ORD') {
                $tableQuery->whereNotNull('soe.ord_no')
                           ->whereRaw("TRIM(soe.ord_no) NOT IN ('', '0', '-')");
            }

            $allowedSorts = [
                'region_name'     => DB::raw('COALESCE(md.region_name, soe.region_name)'),
                'area_name'       => DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name)'),
                'branch_code'     => 'soe.branch_code',
                'branch_name'     => DB::raw('COALESCE(md.distributor_name, soe.branch_name)'),
                'invoice_date'    => 'soe.invoice_date',
                'invoice_no'      => 'soe.invoice_no',
                'ord_no'          => 'soe.ord_no',
                'qty3_pcs'        => 'soe.qty3_pcs',
                'line_discount_4' => 'soe.line_discount_4',
                'line_discount_8' => 'soe.line_discount_8',
                'nett_amount'     => 'soe.nett_amount',
            ];

            $orderCol = $allowedSorts[$this->sortField] ?? 'soe.invoice_date';
            $dir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

            $rows = $tableQuery
                ->orderBy($orderCol, $dir)
                ->orderBy('soe.invoice_no')
                ->paginate($this->perPage);

            // Totals Detail
            $detailTotalsQuery = clone $baseQuery;
            if ($this->statusFilter === 'DISC_WITHOUT_ORD') {
                $detailTotalsQuery->where(function ($q) {
                    $q->where('soe.line_discount_4', '<>', 0)
                      ->orWhere('soe.line_discount_8', '<>', 0);
                })->where(function ($q) {
                    $q->whereNull('soe.ord_no')
                      ->orWhereRaw("TRIM(soe.ord_no) IN ('', '0', '-')");
                });
            } elseif ($this->statusFilter === 'WITHOUT_ORD') {
                $detailTotalsQuery->where(function ($q) {
                    $q->whereNull('soe.ord_no')
                      ->orWhereRaw("TRIM(soe.ord_no) IN ('', '0', '-')");
                });
            } elseif ($this->statusFilter === 'WITH_ORD') {
                $detailTotalsQuery->whereNotNull('soe.ord_no')
                           ->whereRaw("TRIM(soe.ord_no) NOT IN ('', '0', '-')");
            }

            $totals = $detailTotalsQuery->selectRaw("
                COUNT(*) as total_rows,
                COALESCE(SUM(soe.qty3_pcs), 0) as total_qty_pcs,
                COALESCE(SUM(soe.line_discount_4), 0) as total_line_discount_4,
                COALESCE(SUM(soe.line_discount_8), 0) as total_line_discount_8,
                COALESCE(SUM(soe.nett_amount), 0) as total_nett_amount
            ")->first();
        }

        return view('livewire.others.qceskalink.reference-number', [
            'regions'  => $regions,
            'branches' => $branches,
            'kpi'      => $kpi,
            'rows'     => $rows,
            'totals'   => $totals,
        ])->layout('layouts.app');
    }
}
