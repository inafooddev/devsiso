<?php

namespace App\Exports;

use Carbon\Carbon;
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

class SalesComparisonExport implements FromArray, WithEvents, WithTitle, WithStrictNullComparison
{
    protected $monthFilter;
    protected $regionFilter;
    protected $implementasiFilter;
    protected $statusFilter;
    protected $search;
    protected $sortField;
    protected $sortDirection;

    protected $records;
    protected $totalRows = 0;
    protected $kpi = [
        'total_branch' => 0,
        'sudah_tarik'  => 0,
        'belum_tarik'  => 0,
        'total_ok'     => 0,
        'total_not_ok' => 0,
    ];
    protected $totals = [
        'qty_pcs'  => 0,
        'ld4'      => 0,
        'bb'       => 0,
        'net_eska' => 0,
        'net_siso' => 0,
        'selisih'  => 0,
    ];

    public function __construct(
        $monthFilter,
        $regionFilter = '',
        $implementasiFilter = 'ALL',
        $statusFilter = 'ALL',
        $search = '',
        $sortField = 'region_name',
        $sortDirection = 'asc'
    ) {
        $this->monthFilter = $monthFilter ?: Carbon::now()->subMonth()->format('Y-m');
        $this->regionFilter = $regionFilter;
        $this->implementasiFilter = $implementasiFilter;
        $this->statusFilter = $statusFilter;
        $this->search = $search;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;

        $this->prepareData();
    }

    protected function prepareData(): void
    {
        $startDate = Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($this->monthFilter)->endOfMonth()->format('Y-m-d');
        $user = auth()->user();

        $soeSub = DB::table('selling_out_eskalink')
            ->select(
                'branch_code',
                DB::raw('COUNT(*) AS row_count'),
                DB::raw('SUM(qty3_pcs) AS qty_pcs'),
                DB::raw('SUM(gross_amount) AS gross'),
                DB::raw('SUM(line_discount_4) AS ld4'),
                DB::raw('SUM(line_discount_8) AS bb'),
                DB::raw('SUM(dpp) AS dpp'),
                DB::raw('SUM(tax) AS tax'),
                DB::raw('SUM(nett_amount) AS net_eska')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('branch_code');

        $sidSub = DB::table('sales_invoice_distributor')
            ->select(
                'distributor_code',
                DB::raw('SUM(net_amount) AS net_siso')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('distributor_code');

        $baseQuery = DB::table('distributor_implementasi_eskalink as die')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->leftJoinSub($soeSub, 'soe', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoinSub($sidSub, 'sid', 'sid.distributor_code', '=', 'die.distributor_code')
            ->where('die.implementasi', 'Y')
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA');

        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $baseQuery->whereIn('md.region_code', (array) $user->region_code);
        }

        if (!empty($this->regionFilter)) {
            $baseQuery->where('md.region_code', $this->regionFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($s) {
                $q->where('md.distributor_name', 'ilike', $s)
                  ->orWhere('die.eskalink_code', 'ilike', $s)
                  ->orWhere('md.distributor_code', 'ilike', $s)
                  ->orWhere('md.area_name', 'ilike', $s)
                  ->orWhere('md.region_name', 'ilike', $s);
            });
        }

        // Hitung Ringkasan KPI
        $summaryObj = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) AS total_branch,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) = 0 THEN 1 ELSE 0 END) AS belum_tarik,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) <> 0 THEN 1 ELSE 0 END) AS sudah_tarik,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) <> 0 AND ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) < 1000 THEN 1 ELSE 0 END) AS total_ok,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) <> 0 AND ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) >= 1000 THEN 1 ELSE 0 END) AS total_not_ok
            ')
            ->first();

        $this->kpi = [
            'total_branch' => (int) ($summaryObj->total_branch ?? 0),
            'sudah_tarik'  => (int) ($summaryObj->sudah_tarik ?? 0),
            'belum_tarik'  => (int) ($summaryObj->belum_tarik ?? 0),
            'total_ok'     => (int) ($summaryObj->total_ok ?? 0),
            'total_not_ok' => (int) ($summaryObj->total_not_ok ?? 0),
        ];

        // Terapkan filter penarikan & status selisih untuk daftar baris
        $tableQuery = (clone $baseQuery)->select(
            'md.region_code',
            'md.region_name',
            'md.area_code AS entity_code',
            'md.area_name AS entity_name',
            'die.eskalink_code AS branch_code',
            'md.distributor_name AS branch_name',
            DB::raw('COALESCE(soe.row_count, 0) AS row_count'),
            DB::raw('COALESCE(soe.qty_pcs, 0) AS qty_pcs'),
            DB::raw('COALESCE(soe.gross, 0) AS gross'),
            DB::raw('COALESCE(soe.ld4, 0) AS ld4'),
            DB::raw('COALESCE(soe.bb, 0) AS bb'),
            DB::raw('FLOOR(COALESCE(soe.dpp, 0)) AS dpp'),
            DB::raw('FLOOR(COALESCE(soe.tax, 0)) AS tax'),
            DB::raw('FLOOR(COALESCE(soe.net_eska, 0)) AS net_eska'),
            DB::raw('FLOOR(COALESCE(sid.net_siso, 0)) AS net_siso'),
            DB::raw('FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0)) AS selisih'),
            DB::raw('ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) AS abs_selisih')
        );

        if ($this->implementasiFilter === 'Y') {
            $tableQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) <> 0');
        } elseif ($this->implementasiFilter === 'N') {
            $tableQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) = 0');
        }

        if ($this->statusFilter === 'OK') {
            $tableQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) <> 0')
                       ->whereRaw('ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) < 1000');
        } elseif ($this->statusFilter === 'NOT_OK') {
            $tableQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) <> 0')
                       ->whereRaw('ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) >= 1000');
        }

        $allowedSorts = [
            'region_name' => 'md.region_name',
            'entity_name' => 'md.area_name',
            'branch_code' => 'die.eskalink_code',
            'branch_name' => 'md.distributor_name',
            'qty_pcs'     => 'qty_pcs',
            'ld4'         => 'ld4',
            'bb'          => 'bb',
            'net_eska'    => 'net_eska',
            'net_siso'    => 'net_siso',
            'selisih'     => 'abs_selisih',
        ];

        $orderCol = $allowedSorts[$this->sortField] ?? 'md.region_name';
        $dir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $this->records = $tableQuery
            ->orderBy($orderCol, $dir)
            ->orderBy('md.region_name')
            ->orderBy('md.area_name')
            ->orderBy('md.distributor_name')
            ->get();

        $this->totalRows = $this->records->count();

        foreach ($this->records as $r) {
            $this->totals['qty_pcs']  += (float) ($r->qty_pcs ?? 0);
            $this->totals['ld4']      += (float) ($r->ld4 ?? 0);
            $this->totals['bb']       += (float) ($r->bb ?? 0);
            $this->totals['net_eska'] += (float) ($r->net_eska ?? 0);
            $this->totals['net_siso'] += (float) ($r->net_siso ?? 0);
            $this->totals['selisih']  += (float) ($r->selisih ?? 0);
        }
    }

    public function collection()
    {
        return $this->records;
    }

    public function title(): string
    {
        $period = Carbon::parse($this->monthFilter . '-01')->format('M Y');
        return 'Sales Comparison ' . $period;
    }

    public function array(): array
    {
        $periodLabel = Carbon::parse($this->monthFilter . '-01')->translatedFormat('F Y');
        $regionLabel = !empty($this->regionFilter) ? $this->regionFilter : 'Semua Region';

        $tarikMap = [
            'ALL' => 'Semua Cabang',
            'Y'   => 'Sudah Tarik Data',
            'N'   => 'Belum Tarik Data',
        ];
        $statusMap = [
            'ALL'    => 'Semua Status',
            'OK'     => 'Hanya OK (< Rp 1.000)',
            'NOT_OK' => 'Hanya NOT OK (>= Rp 1.000)',
        ];

        $tarikLabel  = $tarikMap[$this->implementasiFilter] ?? 'Semua Cabang';
        $statusLabel = $statusMap[$this->statusFilter] ?? 'Semua Status';
        $searchLabel = !empty($this->search) ? "  |  Pencarian: \"{$this->search}\"" : '';
        $printedAt   = Carbon::now()->format('d/m/Y H:i');

        $rows = [];

        // Row 1: Judul Utama Laporan
        $rows[] = ['LAPORAN REKONSILIASI SALES COMPARISON (SELLING OUT ESKALINK VS SID)', '', '', '', '', '', '', '', '', '', '', ''];

        // Row 2: Subjudul Metadata Filter
        $rows[] = ["Periode: {$periodLabel}   |   Region: {$regionLabel}   |   Penarikan: {$tarikLabel}   |   Filter Status: {$statusLabel}{$searchLabel}   |   Dicetak: {$printedAt}", '', '', '', '', '', '', '', '', '', '', ''];

        // Row 3: Spacer
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', ''];

        // Row 4: Ringkasan KPI (Header)
        $rows[] = [
            'TOTAL CABANG IMPLEMENTASI', '',
            'SUDAH TARIK SO ESKA', '',
            'BELUM TARIK SO ESKA', '',
            'STATUS OK (< Rp 1.000)', '',
            'STATUS NOT OK (>= Rp 1.000)', '',
            'DATA DITAMPILKAN', ''
        ];

        // Row 5: Ringkasan KPI (Nilai)
        $rows[] = [
            $this->kpi['total_branch'] . ' Cabang', '',
            $this->kpi['sudah_tarik'] . ' Cabang', '',
            $this->kpi['belum_tarik'] . ' Cabang', '',
            $this->kpi['total_ok'] . ' Cabang', '',
            $this->kpi['total_not_ok'] . ' Cabang', '',
            $this->totalRows . ' Cabang', ''
        ];

        // Row 6: Spacer
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', ''];

        // Row 7: Header Tabel Data
        $rows[] = [
            'NO',
            'REGION',
            'AREA',
            'BRANCH CODE',
            'BRANCH NAME (DISTRIBUTOR)',
            'QTY3 (PCS)',
            'LD 4 (RP)',
            'LD 8 (RP)',
            'NETTO ESKA (RP)',
            'NETTO SID (RP)',
            'SELISIH NETTO (RP)',
            'STATUS',
        ];

        // Row 8+: Data Baris
        $no = 1;
        foreach ($this->records as $row) {
            if (($row->net_eska ?? 0) == 0) {
                $status = 'BELUM TARIK';
            } elseif (abs($row->selisih ?? 0) >= 1000) {
                $status = 'NOT OK';
            } else {
                $status = 'OK';
            }

            $rows[] = [
                $no++,
                $row->region_name ?? '-',
                $row->entity_name ?? '-',
                $row->branch_code ?? '-',
                $row->branch_name ?? '-',
                (float) ($row->qty_pcs ?? 0),
                (float) ($row->ld4 ?? 0),
                (float) ($row->bb ?? 0),
                (float) ($row->net_eska ?? 0),
                (float) ($row->net_siso ?? 0),
                (float) ($row->selisih ?? 0),
                $status,
            ];
        }

        // Baris GRAND TOTAL
        $rows[] = [
            'GRAND TOTAL (' . $this->totalRows . ' CABANG)',
            '',
            '',
            '',
            '',
            (float) $this->totals['qty_pcs'],
            (float) $this->totals['ld4'],
            (float) $this->totals['bb'],
            (float) $this->totals['net_eska'],
            (float) $this->totals['net_siso'],
            (float) $this->totals['selisih'],
            '',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $headerRow = 7;
                $firstDataRow = 8;
                $lastDataRow = $firstDataRow + max($this->totalRows - 1, 0);
                $grandTotalRow = $firstDataRow + $this->totalRows;
                $lastCol = 'L';

                // Page Setup (Landscape, Fit to 1 page wide untuk print langsung)
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // === 1. TITLE & SUBTITLE BANNER (Rows 1-2) ===
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'indent'     => 1,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'indent'     => 1,
                    ],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(8);

                // === 2. KPI SUMMARY CARDS (Rows 4-5) ===
                $kpiBlocks = [
                    ['cols' => ['A', 'B'], 'headerBg' => 'F1F5F9', 'headerFg' => '475569', 'valBg' => 'FFFFFF', 'valFg' => '0F172A'],
                    ['cols' => ['C', 'D'], 'headerBg' => 'DCFCE7', 'headerFg' => '166534', 'valBg' => 'F0FDF4', 'valFg' => '15803D'],
                    ['cols' => ['E', 'F'], 'headerBg' => 'FEF3C7', 'headerFg' => '92400E', 'valBg' => 'FFFBEB', 'valFg' => 'B45309'],
                    ['cols' => ['G', 'H'], 'headerBg' => 'DBEAFE', 'headerFg' => '1E40AF', 'valBg' => 'EFF6FF', 'valFg' => '1D4ED8'],
                    ['cols' => ['I', 'J'], 'headerBg' => 'FEE2E2', 'headerFg' => '991B1B', 'valBg' => 'FEF2F2', 'valFg' => 'DC2626'],
                    ['cols' => ['K', 'L'], 'headerBg' => 'E0E7FF', 'headerFg' => '3730A3', 'valBg' => 'EEF2FF', 'valFg' => '4338CA'],
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
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $block['valFg']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $block['valBg']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    ]);
                }
                $sheet->getRowDimension(4)->setRowHeight(16);
                $sheet->getRowDimension(5)->setRowHeight(22);
                $sheet->getRowDimension(6)->setRowHeight(8);

                // === 3. TABLE HEADER (Row 7) ===
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']],
                    ],
                ]);

                // Aksen warna kolom spesifik di Header
                $sheet->getStyle("I{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A'); // Netto ESKA (Biru)
                $sheet->getStyle("J{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('065F46'); // Netto SID (Hijau)
                $sheet->getStyle("K{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('312E81'); // Selisih (Indigo)
                $sheet->getRowDimension($headerRow)->setRowHeight(26);

                // === 4. DATA ROWS STYLING ===
                $numberFormat = '#,##0;(#,##0);"-"';

                if ($this->totalRows > 0) {
                    // Base border & vertical alignment untuk seluruh baris data
                    $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'font' => ['size' => 9.5, 'color' => ['rgb' => '1E293B']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                        ],
                    ]);

                    // Alignment per kolom
                    $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$firstDataRow}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$firstDataRow}:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("L{$firstDataRow}:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Format angka ribuan
                    $sheet->getStyle("F{$firstDataRow}:K{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);

                    // Zebra striping & Conditional Status Highlight
                    for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(19);
                        $isEven = ($r % 2 === 0);
                        $rowBg = $isEven ? 'FFFFFF' : 'F8FAFC';

                        $sheet->getStyle("A{$r}:K{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($rowBg);

                        // Aksen lembut pada kolom Netto ESKA & Netto SID
                        $sheet->getStyle("I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($isEven ? 'F8FAFF' : 'F0F5FF');
                        $sheet->getStyle("J{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($isEven ? 'F7FEFA' : 'ECFDF5');

                        // Bold pada kolom Branch Code, Netto, dan Selisih
                        $sheet->getStyle("D{$r}")->getFont()->setBold(true);
                        $sheet->getStyle("I{$r}:K{$r}")->getFont()->setBold(true);

                        $statusVal = (string) $sheet->getCell("L{$r}")->getValue();

                        if ($statusVal === 'OK') {
                            $sheet->getStyle("L{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '166534']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                            ]);
                            $sheet->getStyle("K{$r}")->getFont()->getColor()->setRGB('475569');
                        } elseif ($statusVal === 'NOT OK') {
                            $sheet->getStyle("L{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '991B1B']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                            ]);
                            $sheet->getStyle("K{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                            ]);
                        } else {
                            // BELUM TARIK
                            $sheet->getStyle("L{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                            ]);
                            $sheet->getStyle("I{$r}")->getFont()->getColor()->setRGB('94A3B8');
                            $sheet->getStyle("K{$r}")->getFont()->getColor()->setRGB('94A3B8');
                        }
                    }
                }

                // === 5. GRAND TOTAL ROW ===
                $sheet->mergeCells("A{$grandTotalRow}:E{$grandTotalRow}");
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
                $sheet->getStyle("A{$grandTotalRow}:E{$grandTotalRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setIndent(1);
                $sheet->getStyle("F{$grandTotalRow}:K{$grandTotalRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$grandTotalRow}:K{$grandTotalRow}")
                    ->getNumberFormat()->setFormatCode($numberFormat);

                // Highlight nilai total Netto ESKA, SID, dan Selisih di baris Grand Total
                $sheet->getStyle("I{$grandTotalRow}")->getFont()->getColor()->setRGB('93C5FD');
                $sheet->getStyle("J{$grandTotalRow}")->getFont()->getColor()->setRGB('6EE7B7');
                $sheet->getStyle("K{$grandTotalRow}")->getFont()->getColor()->setRGB(
                    abs($this->totals['selisih']) >= 1000 ? 'FCA5A5' : 'E2E8F0'
                );
                $sheet->getRowDimension($grandTotalRow)->setRowHeight(24);

                // === 6. COLUMN WIDTHS, AUTO FILTER & FREEZE PANE ===
                $colWidths = [
                    'A' => 6,
                    'B' => 18,
                    'C' => 18,
                    'D' => 15,
                    'E' => 36,
                    'F' => 16,
                    'G' => 18,
                    'H' => 18,
                    'I' => 20,
                    'J' => 20,
                    'K' => 20,
                    'L' => 16,
                ];
                foreach ($colWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // AutoFilter pada baris header tabel
                if ($this->totalRows > 0) {
                    $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$lastDataRow}");
                }

                // Freeze pane di bawah header tabel agar judul, KPI, dan header tetap terlihat saat scroll
                $sheet->freezePane("A{$firstDataRow}");

                // Outline border tegas untuk keseluruhan tabel
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$grandTotalRow}")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E293B']],
                    ],
                ]);
            },
        ];
    }
}
