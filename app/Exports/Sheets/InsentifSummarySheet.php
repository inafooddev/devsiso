<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Services\InsentifCalculatorService;
use Carbon\Carbon;

class InsentifSummarySheet implements FromArray, WithEvents, WithTitle, WithCustomStartCell
{
    protected $rows = [];
    protected $grandTotal = 0;
    protected $monthName;
    protected $regionFilter;
    protected $totalRows = 0;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->regionFilter = $region;
        $this->monthName = Carbon::parse($bulan . '-01')->translatedFormat("F'y");

        $service = new InsentifCalculatorService();
        $summaryData = collect();

        // Level order: KACAB=1, SPV=2, SE=3
        $area = !empty($areas) ? $areas : null;

        // KACAB
        $kacabDataRaw = $service->calculateKacab($bulan, $region, $area);
        foreach ($kacabDataRaw as $kacab) {
            if ($kacab['trf'] > 0) {
                $summaryData->push([
                    'area_name'   => $kacab['area_name'],
                    'cabang'      => $kacab['cabang'],
                    'level'       => 'KACAB',
                    'level_order' => 1,
                    'kode'        => '-',
                    'nama'        => $kacab['nama_kacab'],
                    'thp'         => $kacab['trf'],
                ]);
                $this->grandTotal += $kacab['trf'];
            }
        }

        // SPV
        $spvDataRaw = $service->calculateSpv($bulan, $region, $area);
        foreach ($spvDataRaw['spvData'] as $spv) {
            if ($spv['transfer_70'] > 0) {
                $areaName = '';
                $cabangs = [];
                foreach ($spv['cabangs'] as $c => $cData) {
                    $cabangs[] = $c;
                    if (empty($areaName) && !empty($cData['area_name'])) {
                        $areaName = $cData['area_name'];
                    }
                }
                $summaryData->push([
                    'area_name'   => $areaName,
                    'cabang'      => implode(', ', $cabangs),
                    'cabang_sort' => $cabangs[0] ?? '',
                    'level'       => 'SPV',
                    'level_order' => 2,
                    'kode'        => $spv['supervisor_code'],
                    'nama'        => $spv['supervisor_name'],
                    'thp'         => $spv['transfer_70'],
                ]);
                $this->grandTotal += $spv['transfer_70'];
            }
        }

        // SE
        $seDataRaw = $service->calculateSe($bulan, $region, $area);
        foreach ($seDataRaw['salesmenData'] as $se) {
            if ($se['thp'] > 0) {
                $summaryData->push([
                    'area_name'   => $se['area_name'],
                    'cabang'      => $se['cabang'],
                    'cabang_sort' => $se['cabang'],
                    'level'       => 'SE',
                    'level_order' => 3,
                    'kode'        => $se['salesman_code'],
                    'nama'        => $se['salesman_name'],
                    'thp'         => $se['thp'],
                ]);
                $this->grandTotal += $se['thp'];
            }
        }

        // Sort: area → cabang → level_order
        $sorted = $summaryData->sortBy([
            ['area_name', 'asc'],
            ['cabang', 'asc'],
            ['level_order', 'asc'],
        ])->values();

        $this->rows = $sorted->map(fn($r) => [
            $r['area_name'],
            $r['level'],
            $r['cabang'],
            $r['kode'],
            $r['nama'],
            $r['thp'],
        ])->toArray();

        $this->totalRows = count($this->rows);
    }

    public function title(): string
    {
        return 'SUMMARY';
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function array(): array
    {
        $data = [];

        // Header row
        $data[] = ['Area', 'Jabatan', 'Cabang', 'Kode', 'Nama', 'THP (Rp)'];

        // Data rows
        foreach ($this->rows as $row) {
            $data[] = $row;
        }

        // Grand total row
        $data[] = ['', '', '', '', 'GRAND TOTAL', $this->grandTotal];

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataStartRow = 4; // A3 = header, A4 = first data row
                $lastDataRow  = $dataStartRow + $this->totalRows; // grand total row
                $grandTotalRow = $lastDataRow;
                $lastCol = 'F';

                // === TITLE ===
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'REKAPAN SUMMARY THP INSENTIF - ' . strtoupper($this->regionFilter) . ' - ' . strtoupper($this->monthName));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // === HEADER ROW (A3) ===
                $headerRange = "A3:{$lastCol}3";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(20);

                // === DATA ROWS — zebra striping + badge color for Jabatan ===
                $jabatanColors = [
                    'KACAB' => ['bg' => 'FEF3C7', 'font' => '92400E'],
                    'SPV'   => ['bg' => 'EDE9FE', 'font' => '5B21B6'],
                    'SE'    => ['bg' => 'DBEAFE', 'font' => '1E40AF'],
                ];

                for ($row = $dataStartRow; $row < $grandTotalRow; $row++) {
                    $jabatan = $sheet->getCell("B{$row}")->getValue();
                    $isEven  = ($row % 2 === 0);
                    $bgColor = $isEven ? 'F9FAFB' : 'FFFFFF';

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);

                    // Color badge on Jabatan column (C)
                    if (isset($jabatanColors[$jabatan])) {
                        $sheet->getStyle("B{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $jabatanColors[$jabatan]['font']]],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $jabatanColors[$jabatan]['bg']]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }

                // === GRAND TOTAL ROW ===
                $sheet->getStyle("A{$grandTotalRow}:{$lastCol}{$grandTotalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '15803D']]],
                ]);
                $sheet->getRowDimension($grandTotalRow)->setRowHeight(22);

                // === THP COLUMN — number format ===
                $thpRange = "F{$dataStartRow}:F{$grandTotalRow}";
                $sheet->getStyle($thpRange)->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle($thpRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // === COLUMN WIDTHS ===
                $sheet->getColumnDimension('A')->setWidth(22); // Area
                $sheet->getColumnDimension('B')->setWidth(12); // Jabatan
                $sheet->getColumnDimension('C')->setWidth(30); // Cabang
                $sheet->getColumnDimension('D')->setWidth(16); // Kode
                $sheet->getColumnDimension('E')->setWidth(32); // Nama
                $sheet->getColumnDimension('F')->setWidth(22); // THP

                // === FREEZE PANES ===
                $sheet->freezePane('A4');

                // === OUTER BORDER for whole table ===
                $tableRange = "A3:{$lastCol}{$grandTotalRow}";
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '374151']]],
                ]);
            },
        ];
    }
}
