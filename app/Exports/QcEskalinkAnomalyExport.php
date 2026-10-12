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

class QcEskalinkAnomalyExport implements FromArray, WithEvents, WithTitle, WithStrictNullComparison
{
    protected $monthFilter;
    protected $regionFilter;
    protected $branchFilter;
    protected $anomalyType;
    protected $search;

    protected $records;
    protected $totalRows = 0;

    public function __construct($monthFilter, $regionFilter = '', $branchFilter = '', $anomalyType = 'ALL', $search = '')
    {
        $this->monthFilter = $monthFilter ?: Carbon::now()->subMonth()->format('Y-m');
        $this->regionFilter = $regionFilter;
        $this->branchFilter = $branchFilter;
        $this->anomalyType = $anomalyType;
        $this->search = $search;

        $this->records = $this->fetchRecords();
        $this->totalRows = $this->records->count();
    }

    protected function fetchRecords()
    {
        $startOfMonth = Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::parse($this->monthFilter)->endOfMonth()->format('Y-m-d');
        $user = Auth::user();

        $query = DB::table('selling_out_eskalink as soe')
            ->leftJoin('distributor_implementasi_eskalink as die', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->whereBetween('soe.invoice_date', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw('COALESCE(md.region_name, soe.region_name) as region_name'),
                DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name) as area_name'),
                'soe.branch_code',
                DB::raw('COALESCE(md.distributor_name, soe.branch_name) as branch_name'),
                'soe.invoice_date',
                'soe.invoice_no',
                'soe.invoice_type',
                'soe.ord_no',
                'soe.sales_code',
                'soe.sales_name',
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
                        WHEN soe.qty3_pcs < 0 AND soe.nett_amount > 0 THEN 'QTY (-) tapi Nett (+)'
                        WHEN soe.qty3_pcs > 0 AND soe.nett_amount < 0 THEN 'QTY (+) tapi Nett (-)'
                        WHEN soe.qty3_pcs = 0 AND (soe.nett_amount <> 0 OR soe.qty1_car <> 0 OR soe.qty2_pck <> 0) THEN 'QTY Pcs (0) tapi Nett/Karton != 0'
                        WHEN UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS') AND soe.nett_amount <> 0 THEN 'Barang Bonus (Y) tapi Nett != 0'
                        WHEN soe.qty3_pcs <> 0 AND soe.nett_amount = 0 AND COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS') THEN 'Non-Bonus tapi Nett = 0'
                        WHEN UPPER(COALESCE(soe.invoice_type, '')) IN ('RET', 'RETUR', 'CN', 'R') AND (soe.qty3_pcs > 0 OR soe.nett_amount > 0) THEN 'Tipe Retur (RET) tapi Positif (+)'
                        ELSE 'Anomali Lainnya'
                    END as anomaly_label
                ")
            );

        if ($user && !empty($user->region_code) && !$user->hasRole('admin')) {
            $query->whereIn(DB::raw('COALESCE(md.region_code, soe.region_code)'), (array) $user->region_code);
        }

        if ($this->anomalyType === 'QTY_NEG_NET_POS') {
            $query->where('soe.qty3_pcs', '<', 0)->where('soe.nett_amount', '>', 0);
        } elseif ($this->anomalyType === 'QTY_POS_NET_NEG') {
            $query->where('soe.qty3_pcs', '>', 0)->where('soe.nett_amount', '<', 0);
        } elseif ($this->anomalyType === 'QTY_ZERO_NET_VAL') {
            $query->where('soe.qty3_pcs', '=', 0)
                  ->where(function ($q) {
                      $q->where('soe.nett_amount', '<>', 0)
                        ->orWhere('soe.qty1_car', '<>', 0)
                        ->orWhere('soe.qty2_pck', '<>', 0);
                  });
        } elseif ($this->anomalyType === 'NON_BONUS_NET_ZERO') {
            $query->where('soe.qty3_pcs', '<>', 0)
                  ->where('soe.nett_amount', '=', 0)
                  ->whereRaw("COALESCE(UPPER(soe.flag_bonus), 'N') NOT IN ('Y', 'YES', '1', 'B', 'BONUS')");
        } elseif ($this->anomalyType === 'BONUS_HAS_NET') {
            $query->whereRaw("UPPER(COALESCE(soe.flag_bonus, '')) IN ('Y', 'YES', '1', 'B', 'BONUS')")
                  ->where('soe.nett_amount', '<>', 0);
        } elseif ($this->anomalyType === 'RET_POSITIVE') {
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

        if (!empty($this->regionFilter)) {
            $query->where(DB::raw('COALESCE(md.region_code, soe.region_code)'), $this->regionFilter);
        }

        if (!empty($this->branchFilter)) {
            $query->where('soe.branch_code', $this->branchFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
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

        return $query
            ->orderBy(DB::raw('COALESCE(md.region_name, soe.region_name)'))
            ->orderBy(DB::raw('COALESCE(md.area_name, soe.entity_name, soe.area_name)'))
            ->orderBy(DB::raw('COALESCE(md.distributor_name, soe.branch_name)'))
            ->orderBy('soe.invoice_date')
            ->orderBy('soe.invoice_no')
            ->get();
    }

    public function collection()
    {
        return $this->records;
    }

    public function title(): string
    {
        return 'Anomali SO ' . Carbon::parse($this->monthFilter . '-01')->format('M Y');
    }

    public function array(): array
    {
        $periodLabel = Carbon::parse($this->monthFilter . '-01')->translatedFormat('F Y');
        $regionLabel = !empty($this->regionFilter) ? $this->regionFilter : 'Semua Region';
        $branchLabel = !empty($this->branchFilter) ? $this->branchFilter : 'Semua Cabang';
        $printedAt   = Carbon::now()->format('d/m/Y H:i');

        $rows = [];
        $rows[] = ['LAPORAN AUDIT ANOMALI DATA SELLING OUT ESKALINK (QTY3 PCS VS NETT AMOUNT)'];
        $rows[] = ["Periode: {$periodLabel}   |   Region: {$regionLabel}   |   Cabang: {$branchLabel}   |   Kategori: {$this->anomalyType}   |   Total Temuan: {$this->totalRows} Baris   |   Dicetak: {$printedAt}"];
        $rows[] = [''];

        $rows[] = [
            'NO',
            'REGION',
            'AREA',
            'BRANCH CODE',
            'DISTRIBUTOR NAME',
            'INVOICE DATE',
            'INVOICE NO',
            'INVOICE TYPE',
            'ORDER NO',
            'SALES CODE',
            'SALES NAME',
            'CUST CODE',
            'CUSTOMER NAME',
            'PRODUCT CODE',
            'PRODUCT NAME',
            'FLAG BONUS',
            'QTY1 (CAR)',
            'QTY2 (PCK)',
            'QTY3 (PCS)',
            'GROSS AMOUNT',
            'LINE DISC 4',
            'LINE DISC 8',
            'TOTAL DISC',
            'DPP',
            'TAX',
            'NETT AMOUNT',
            'JENIS ANOMALI',
        ];

        $no = 1;
        $totQty3 = 0;
        $totGross = 0;
        $totLd4 = 0;
        $totLd8 = 0;
        $totDisc = 0;
        $totDpp = 0;
        $totTax = 0;
        $totNett = 0;

        foreach ($this->records as $row) {
            $totQty3  += (float) ($row->qty3_pcs ?? 0);
            $totGross += (float) ($row->gross_amount ?? 0);
            $totLd4   += (float) ($row->line_discount_4 ?? 0);
            $totLd8   += (float) ($row->line_discount_8 ?? 0);
            $totDisc  += (float) ($row->total_line_discount ?? 0);
            $totDpp   += (float) ($row->dpp ?? 0);
            $totTax   += (float) ($row->tax ?? 0);
            $totNett  += (float) ($row->nett_amount ?? 0);

            $rows[] = [
                $no++,
                $row->region_name ?? '-',
                $row->area_name ?? '-',
                $row->branch_code ?? '-',
                $row->branch_name ?? '-',
                $row->invoice_date ?? '-',
                $row->invoice_no ?? '-',
                $row->invoice_type ?? '-',
                $row->ord_no ?? '-',
                $row->sales_code ?? '-',
                $row->sales_name ?? '-',
                $row->cust_code_dist ?? '-',
                $row->cust_name ?? '-',
                $row->prd_code ?? '-',
                $row->prd_name ?? '-',
                $row->flag_bonus ?? '-',
                (float) ($row->qty1_car ?? 0),
                (float) ($row->qty2_pck ?? 0),
                (float) ($row->qty3_pcs ?? 0),
                (float) ($row->gross_amount ?? 0),
                (float) ($row->line_discount_4 ?? 0),
                (float) ($row->line_discount_8 ?? 0),
                (float) ($row->total_line_discount ?? 0),
                (float) ($row->dpp ?? 0),
                (float) ($row->tax ?? 0),
                (float) ($row->nett_amount ?? 0),
                $row->anomaly_label,
            ];
        }

        $rows[] = [
            'GRAND TOTAL (' . $this->totalRows . ' BARIS)',
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '',
            $totQty3,
            $totGross,
            $totLd4,
            $totLd8,
            $totDisc,
            $totDpp,
            $totTax,
            $totNett,
            '',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headerRow = 4;
                $firstDataRow = 5;
                $lastDataRow = $firstDataRow + max($this->totalRows - 1, 0);
                $grandTotalRow = $firstDataRow + $this->totalRows;
                $lastCol = 'AA';

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '881337']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(8);

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(24);

                $numberFormat = '#,##0;(#,##0);"-"';
                if ($this->totalRows > 0) {
                    $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'font' => ['size' => 9],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    ]);
                    $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("Q{$firstDataRow}:Z{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("Q{$firstDataRow}:Z{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$lastDataRow}");
                }

                $sheet->mergeCells("A{$grandTotalRow}:R{$grandTotalRow}");
                $sheet->getStyle("A{$grandTotalRow}:{$lastCol}{$grandTotalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A{$grandTotalRow}:R{$grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setIndent(1);
                $sheet->getStyle("S{$grandTotalRow}:Z{$grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("S{$grandTotalRow}:Z{$grandTotalRow}")->getNumberFormat()->setFormatCode($numberFormat);
                $sheet->getRowDimension($grandTotalRow)->setRowHeight(22);

                foreach (['A' => 6, 'B' => 16, 'C' => 16, 'D' => 14, 'E' => 30, 'F' => 13, 'G' => 18, 'H' => 12, 'I' => 18, 'N' => 14, 'O' => 28, 'S' => 15, 'T' => 16, 'U' => 15, 'V' => 15, 'Z' => 18, 'AA' => 30] as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                $sheet->freezePane("A{$firstDataRow}");
            },
        ];
    }
}
