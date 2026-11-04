<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class QcEskalinkReferenceNumberExport implements FromArray, WithEvents, WithTitle, WithStrictNullComparison
{
    protected $monthFilter;
    protected $regionFilter;
    protected $branchFilter;
    protected $statusFilter;
    protected $search;
    protected $viewMode;

    protected $records;
    protected $totalRows = 0;
    protected $kpi = [
        'total_invoices'        => 0,
        'invoices_with_ord'     => 0,
        'invoices_without_ord'  => 0,
        'disc_without_ord_inv'  => 0,
        'disc4_val_without_ord' => 0,
        'disc8_val_without_ord' => 0,
        'total_disc_val'        => 0,
    ];

    public function __construct(
        $monthFilter,
        $regionFilter = '',
        $branchFilter = '',
        $statusFilter = 'ALL',
        $search = '',
        $viewMode = 'summary'
    ) {
        $this->monthFilter  = $monthFilter ?: Carbon::now()->subMonth()->format('Y-m');
        $this->regionFilter = $regionFilter;
        $this->branchFilter = $branchFilter;
        $this->statusFilter = $statusFilter;
        $this->search       = $search;
        $this->viewMode     = $viewMode;

        $this->prepareData();
    }

    protected function prepareData(): void
    {
        $startDate = Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        $endDate   = Carbon::parse($this->monthFilter)->endOfMonth()->format('Y-m-d');
        $user      = Auth::user();

        // Base Query
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

        // 1. Hitung KPI Keseluruhan
        $kpiObj = (clone $baseQuery)->selectRaw("
            COUNT(DISTINCT soe.invoice_no) as total_invoices,
            COUNT(DISTINCT CASE WHEN soe.ord_no IS NOT NULL AND TRIM(soe.ord_no) NOT IN ('', '0', '-') THEN soe.invoice_no END) as invoices_with_ord,
            COUNT(DISTINCT CASE WHEN soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-') THEN soe.invoice_no END) as invoices_without_ord,
            COUNT(DISTINCT CASE WHEN (COALESCE(soe.line_discount_4, 0) != 0 OR COALESCE(soe.line_discount_8, 0) != 0) AND (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN soe.invoice_no END) as disc_without_ord_inv,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_4, 0) ELSE 0 END), 0) as disc4_val_without_ord,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_8, 0) ELSE 0 END), 0) as disc8_qty_without_ord,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_8, 0) ELSE 0 END), 0) as disc8_val_without_ord,
            COALESCE(SUM(CASE WHEN (soe.ord_no IS NULL OR TRIM(soe.ord_no) IN ('', '0', '-')) THEN COALESCE(soe.line_discount_4, 0) ELSE 0 END), 0) as total_disc_val
        ")->first();

        $this->kpi = [
            'total_invoices'        => (int) ($kpiObj->total_invoices ?? 0),
            'invoices_with_ord'     => (int) ($kpiObj->invoices_with_ord ?? 0),
            'invoices_without_ord'  => (int) ($kpiObj->invoices_without_ord ?? 0),
            'disc_without_ord_inv'  => (int) ($kpiObj->disc_without_ord_inv ?? 0),
            'disc4_val_without_ord' => (float) ($kpiObj->disc4_val_without_ord ?? 0),
            'disc8_qty_without_ord' => (float) ($kpiObj->disc8_qty_without_ord ?? $kpiObj->disc8_val_without_ord ?? 0),
            'disc8_val_without_ord' => (float) ($kpiObj->disc8_val_without_ord ?? 0),
            'total_disc_val'        => (float) ($kpiObj->total_disc_val ?? 0),
        ];

        // 2. Fetch Records sesuai Mode View
        if ($this->viewMode === 'detail') {
            $tableQuery = (clone $baseQuery)->select(
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

            $this->records = $tableQuery
                ->orderBy(DB::raw('COALESCE(md.region_name, soe.region_name)'))
                ->orderBy(DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name)'))
                ->orderBy(DB::raw('COALESCE(md.distributor_name, soe.branch_name)'))
                ->orderBy('soe.invoice_date')
                ->orderBy('soe.invoice_no')
                ->get();
        } else {
            // Mode Summary (Rekap Cabang Berbasis Master Implementasi Eskalink)
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

            $this->records = $summaryBase->select(
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
            ->orderBy('md.region_name')
            ->orderBy('md.area_name')
            ->orderBy('md.distributor_name')
            ->get();
        }

        $this->totalRows = $this->records->count();
    }

    public function collection()
    {
        return $this->records;
    }

    public function title(): string
    {
        $prefix = $this->viewMode === 'detail' ? 'Detail Referensi' : 'Rekap Referensi';
        return $prefix . ' ' . Carbon::parse($this->monthFilter . '-01')->format('M Y');
    }

    public function array(): array
    {
        $periodLabel = Carbon::parse($this->monthFilter . '-01')->translatedFormat('F Y');
        $regionLabel = !empty($this->regionFilter) ? $this->regionFilter : 'Semua Region';
        $branchLabel = !empty($this->branchFilter) ? $this->branchFilter : 'Semua Cabang';
        $statusLabels = [
            'ALL'              => 'Semua Cabang (76 Master)',
            'PULLED'           => 'Hanya Cabang Sudah Tarik SO',
            'NOT_PULLED'       => 'Hanya Cabang Belum Tarik SO',
            'DISC_WITHOUT_ORD' => 'Hanya Kritis Klaim (Ada Diskon Tanpa No Order)',
            'WITHOUT_ORD'      => 'Semua Nota Tanpa No Order',
            'WITH_ORD'         => 'Hanya Nota Lengkap No Order',
        ];
        $statusLabel = $statusLabels[$this->statusFilter] ?? 'Semua Status';
        $printedAt   = Carbon::now()->format('d/m/Y H:i');

        $rows = [];

        // Row 1: Title
        $titleText = $this->viewMode === 'detail'
            ? 'LAPORAN DETAIL NOMOR REFERENSI (INVOICE VS ORDER NUMBER & KLAIM DISKON)'
            : 'LAPORAN REKAPITULASI NOMOR REFERENSI ORDER (KELENGKAPAN NO. ORDER & KLAIM DISKON)';
        $rows[] = [$titleText];

        // Row 2: Subtitle Metadata
        $rows[] = ["Periode: {$periodLabel}   |   Region: {$regionLabel}   |   Cabang: {$branchLabel}   |   Filter: {$statusLabel}   |   Dicetak: {$printedAt}"];

        // Row 3: Blank Spacer
        $rows[] = [''];

        // Row 4-5: KPI Summary
        $rows[] = [
            'TOTAL NOTA BULAN INI', '',
            'NOTA ADA NO ORDER', '',
            'NOTA TANPA NO ORDER', '',
            'KRITIS KLAIM (ADA DISKON TANPA ORDER)', '',
            'POTENSI DISC 4 TANPA ORDER (RP)', '',
            'POTENSI DISC 8 TANPA ORDER (PCS)', ''
        ];

        $pctWithOrd = $this->kpi['total_invoices'] > 0
            ? round(($this->kpi['invoices_with_ord'] / $this->kpi['total_invoices']) * 100, 1)
            : 0;

        $rows[] = [
            number_format($this->kpi['total_invoices']) . ' Nota', '',
            number_format($this->kpi['invoices_with_ord']) . " Nota ({$pctWithOrd}%)", '',
            number_format($this->kpi['invoices_without_ord']) . ' Nota', '',
            number_format($this->kpi['disc_without_ord_inv']) . ' Nota', '',
            'Rp ' . number_format($this->kpi['disc4_val_without_ord']), '',
            number_format($this->kpi['disc8_val_without_ord']) . ' Pcs', ''
        ];

        // Row 6: Blank Spacer
        $rows[] = [''];

        // Table Header & Rows
        if ($this->viewMode === 'detail') {
            $rows[] = [
                'NO',
                'REGION',
                'AREA',
                'BRANCH CODE',
                'DISTRIBUTOR NAME',
                'INVOICE DATE',
                'INVOICE NO',
                'INVOICE TYPE',
                'ORDER DATE',
                'ORDER NO',
                'CUST CODE',
                'CUSTOMER NAME',
                'PRODUCT CODE',
                'PRODUCT NAME',
                'QTY (PCS)',
                'LINE DISC 4 (RP)',
                'LINE DISC 8 (PCS)',
                'NETT AMOUNT (RP)',
                'STATUS KLAIM',
            ];

            $no = 1;
            $sumQty = 0; $sumDisc4 = 0; $sumDisc8 = 0; $sumNett = 0;

            foreach ($this->records as $r) {
                $hasDisc = ((float)($r->line_discount_4 ?? 0) != 0 || (float)($r->line_discount_8 ?? 0) != 0);
                $isOrdEmpty = (empty($r->ord_no) || in_array(trim((string)$r->ord_no), ['', '0', '-']));

                if ($hasDisc && $isOrdEmpty) {
                    $claimStatus = 'KRITIS (Diskon Tanpa Order)';
                } elseif ($isOrdEmpty) {
                    $claimStatus = 'Tanpa Order (Non-Diskon)';
                } else {
                    $claimStatus = 'LENGKAP';
                }

                $sumQty   += (float) ($r->qty3_pcs ?? 0);
                $sumDisc4 += (float) ($r->line_discount_4 ?? 0);
                $sumDisc8 += (float) ($r->line_discount_8 ?? 0);
                $sumNett  += (float) ($r->nett_amount ?? 0);

                $rows[] = [
                    $no++,
                    $r->region_name ?? '-',
                    $r->area_name ?? '-',
                    $r->branch_code ?? '-',
                    $r->branch_name ?? '-',
                    $r->invoice_date ?? '-',
                    $r->invoice_no ?? '-',
                    $r->invoice_type ?? '-',
                    $r->ord_date ?? '-',
                    $isOrdEmpty ? '-' : $r->ord_no,
                    $r->cust_code_dist ?? '-',
                    $r->cust_name ?? '-',
                    $r->prd_code ?? '-',
                    $r->prd_name ?? '-',
                    (float) ($r->qty3_pcs ?? 0),
                    (float) ($r->line_discount_4 ?? 0),
                    (float) ($r->line_discount_8 ?? 0),
                    (float) ($r->nett_amount ?? 0),
                    $claimStatus,
                ];
            }

            // Grand Total Detail
            $rows[] = [
                'GRAND TOTAL (' . $this->totalRows . ' BARIS)',
                '', '', '', '', '', '', '', '', '', '', '', '', '',
                $sumQty,
                $sumDisc4,
                $sumDisc8,
                $sumNett,
                '',
            ];
        } else {
            // Summary Table
            $rows[] = [
                'NO',
                'REGION',
                'AREA',
                'BRANCH CODE',
                'DISTRIBUTOR NAME',
                'TOTAL NOTA',
                'NOTA BER-ORDER',
                'NOTA TANPA ORDER',
                '% KEPATUHAN',
                'NOTA DISKON TANPA ORDER',
                'NILAI DISC 4 TANPA ORDER (RP)',
                'QTY DISC 8 TANPA ORDER (PCS)',
                'STATUS KELENGKAPAN',
            ];

            $no = 1;
            $sumTotInv = 0; $sumWithOrd = 0; $sumWithoutOrd = 0;
            $sumDiscWithoutOrdInv = 0; $sumDisc4 = 0; $sumDisc8 = 0;

            foreach ($this->records as $r) {
                $totInv     = (int) ($r->total_invoices ?? 0);
                $withOrd    = (int) ($r->invoices_with_ord ?? 0);
                $withoutOrd = (int) ($r->invoices_without_ord ?? 0);
                $discInv    = (int) ($r->disc_without_ord_inv ?? 0);
                $disc4      = (float) ($r->disc4_val_without_ord ?? 0);
                $disc8      = (float) ($r->disc8_qty_without_ord ?? $r->disc8_val_without_ord ?? 0);

                $pct = $totInv > 0 ? round(($withOrd / $totInv) * 100, 1) : 0;

                if ($totInv === 0) {
                    $status = 'BELUM TARIK DATA SO';
                } elseif ($discInv > 0) {
                    $status = 'KRITIS (Ada Diskon)';
                } elseif ($withoutOrd > 0) {
                    $status = 'KURANG LENGKAP';
                } else {
                    $status = 'LENGKAP 100%';
                }

                $sumTotInv += $totInv;
                $sumWithOrd += $withOrd;
                $sumWithoutOrd += $withoutOrd;
                $sumDiscWithoutOrdInv += $discInv;
                $sumDisc4 += $disc4;
                $sumDisc8 += $disc8;

                $rows[] = [
                    $no++,
                    $r->region_name ?? '-',
                    $r->area_name ?? '-',
                    $r->branch_code ?? '-',
                    $r->branch_name ?? '-',
                    $totInv,
                    $withOrd,
                    $withoutOrd,
                    $pct / 100, // raw float for percentage formatting
                    $discInv,
                    $disc4,
                    $disc8,
                    $status,
                ];
            }

            $overallPct = $sumTotInv > 0 ? ($sumWithOrd / $sumTotInv) : 0;

            // Grand Total Summary
            $rows[] = [
                'GRAND TOTAL (' . $this->totalRows . ' DISTRIBUTOR)',
                '', '', '', '',
                $sumTotInv,
                $sumWithOrd,
                $sumWithoutOrd,
                $overallPct,
                $sumDiscWithoutOrdInv,
                $sumDisc4,
                $sumDisc8,
                '',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $isDetail = ($this->viewMode === 'detail');
                $lastCol  = $isDetail ? 'S' : 'M';

                $headerRow     = 7;
                $firstDataRow  = 8;
                $lastDataRow   = $firstDataRow + max($this->totalRows - 1, 0);
                $grandTotalRow = $firstDataRow + $this->totalRows;

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // 1. BANNER TITLE & SUBTITLE
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(8);

                // 2. KPI SUMMARY CARDS (Rows 4-5)
                $kpiBlocks = [
                    ['cols' => ['A', 'B'], 'headerBg' => 'F1F5F9', 'headerFg' => '475569', 'valBg' => 'FFFFFF', 'valFg' => '0F172A'],
                    ['cols' => ['C', 'D'], 'headerBg' => 'DCFCE7', 'headerFg' => '166534', 'valBg' => 'F0FDF4', 'valFg' => '15803D'],
                    ['cols' => ['E', 'F'], 'headerBg' => 'FEF3C7', 'headerFg' => '92400E', 'valBg' => 'FFFBEB', 'valFg' => 'B45309'],
                    ['cols' => ['G', 'H'], 'headerBg' => 'FEE2E2', 'headerFg' => '991B1B', 'valBg' => 'FEF2F2', 'valFg' => 'DC2626'],
                    ['cols' => ['I', 'J'], 'headerBg' => 'FFE4E6', 'headerFg' => '881337', 'valBg' => 'FFF1F2', 'valFg' => '9F1239'],
                    ['cols' => ['K', $lastCol], 'headerBg' => '450A0A', 'headerFg' => 'FFFFFF', 'valBg' => '7F1D1D', 'valFg' => 'FEF2F2'],
                ];

                foreach ($kpiBlocks as $block) {
                    [$c1, $c2] = $block['cols'];
                    $sheet->mergeCells("{$c1}4:{$c2}4");
                    $sheet->mergeCells("{$c1}5:{$c2}5");

                    $sheet->getStyle("{$c1}4:{$c2}4")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => $block['headerFg']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $block['headerBg']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    ]);

                    $sheet->getStyle("{$c1}5:{$c2}5")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10.5, 'color' => ['rgb' => $block['valFg']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $block['valBg']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    ]);
                }
                $sheet->getRowDimension(4)->setRowHeight(16);
                $sheet->getRowDimension(5)->setRowHeight(22);
                $sheet->getRowDimension(6)->setRowHeight(8);

                // 3. TABLE HEADER (Row 7)
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(26);

                $numberFormat = '#,##0;(#,##0);"-"';

                // 4. DATA ROWS FORMATTING
                if ($this->totalRows > 0) {
                    $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'font' => ['size' => 9.5, 'color' => ['rgb' => '1E293B']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    ]);

                    $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    if ($isDetail) {
                        $sheet->getStyle("D{$firstDataRow}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("F{$firstDataRow}:J{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("O{$firstDataRow}:R{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("O{$firstDataRow}:R{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle("S{$firstDataRow}:S{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                            $sheet->getRowDimension($r)->setRowHeight(19);
                            $isEven = ($r % 2 === 0);
                            $sheet->getStyle("A{$r}:R{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($isEven ? 'FFFFFF' : 'F8FAFC');

                            $status = (string) $sheet->getCell("S{$r}")->getValue();
                            if (str_contains($status, 'KRITIS')) {
                                $sheet->getStyle("S{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '991B1B']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                                ]);
                                $sheet->getStyle("J{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                                ]);
                            } elseif (str_contains($status, 'Tanpa Order')) {
                                $sheet->getStyle("S{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '92400E']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                                ]);
                            } else {
                                $sheet->getStyle("S{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '166534']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                                ]);
                            }
                        }
                    } else {
                        // Summary Formats
                        $sheet->getStyle("D{$firstDataRow}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("F{$firstDataRow}:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("F{$firstDataRow}:H{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle("I{$firstDataRow}:I{$lastDataRow}")->getNumberFormat()->setFormatCode('0.0%');
                        $sheet->getStyle("I{$firstDataRow}:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("J{$firstDataRow}:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("J{$firstDataRow}:L{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
                        $sheet->getStyle("M{$firstDataRow}:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                            $sheet->getRowDimension($r)->setRowHeight(19);
                            $isEven = ($r % 2 === 0);
                            $sheet->getStyle("A{$r}:L{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($isEven ? 'FFFFFF' : 'F8FAFC');

                            // Highlight Disc Risk columns
                            $sheet->getStyle("K{$r}:L{$r}")->getFont()->setBold(true);

                            $status = (string) $sheet->getCell("M{$r}")->getValue();
                            if (str_contains($status, 'BELUM TARIK')) {
                                $sheet->getStyle("M{$r}")->applyFromArray([
                                    'font' => ['bold' => false, 'size' => 9, 'color' => ['rgb' => '64748B']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                                ]);
                            } elseif (str_contains($status, 'KRITIS')) {
                                $sheet->getStyle("M{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '991B1B']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                                ]);
                                $sheet->getStyle("J{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                                ]);
                            } elseif (str_contains($status, 'KURANG')) {
                                $sheet->getStyle("M{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '92400E']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                                ]);
                            } else {
                                $sheet->getStyle("M{$r}")->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '166534']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                                ]);
                            }
                        }
                    }

                    $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$lastDataRow}");
                }

                // 5. GRAND TOTAL ROW
                $mergeEndCol = $isDetail ? 'N' : 'E';
                $sheet->mergeCells("A{$grandTotalRow}:{$mergeEndCol}{$grandTotalRow}");
                $sheet->getStyle("A{$grandTotalRow}:{$lastCol}{$grandTotalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '334155']],
                        'top'        => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F172A']],
                        'bottom'     => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '0F172A']],
                    ],
                ]);
                $sheet->getStyle("A{$grandTotalRow}:{$mergeEndCol}{$grandTotalRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setIndent(1);

                if ($isDetail) {
                    $sheet->getStyle("O{$grandTotalRow}:R{$grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("O{$grandTotalRow}:R{$grandTotalRow}")->getNumberFormat()->setFormatCode($numberFormat);
                } else {
                    $sheet->getStyle("F{$grandTotalRow}:H{$grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("F{$grandTotalRow}:H{$grandTotalRow}")->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle("I{$grandTotalRow}")->getNumberFormat()->setFormatCode('0.0%');
                    $sheet->getStyle("I{$grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("J{$grandTotalRow}:L{$grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("J{$grandTotalRow}:L{$grandTotalRow}")->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle("K{$grandTotalRow}")->getFont()->getColor()->setRGB('FCA5A5');
                    $sheet->getStyle("L{$grandTotalRow}")->getFont()->getColor()->setRGB('FCD34D');
                }
                $sheet->getRowDimension($grandTotalRow)->setRowHeight(24);

                // 6. COLUMN WIDTHS & FREEZE PANES
                if ($isDetail) {
                    $widths = [
                        'A' => 6, 'B' => 16, 'C' => 16, 'D' => 14, 'E' => 28,
                        'F' => 13, 'G' => 18, 'H' => 12, 'I' => 13, 'J' => 20,
                        'K' => 14, 'L' => 26, 'M' => 14, 'N' => 26,
                        'O' => 14, 'P' => 18, 'Q' => 18, 'R' => 20, 'S' => 24
                    ];
                } else {
                    $widths = [
                        'A' => 6, 'B' => 18, 'C' => 18, 'D' => 15, 'E' => 32,
                        'F' => 14, 'G' => 16, 'H' => 18, 'I' => 15, 'J' => 22,
                        'K' => 22, 'L' => 22, 'M' => 24
                    ];
                }

                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                $sheet->freezePane("A{$firstDataRow}");

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$grandTotalRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E293B']]],
                ]);
            },
        ];
    }
}
