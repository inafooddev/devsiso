<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\InsentifMasterDistributor;
use App\Models\TargetKacab;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsentifKacabSheet implements FromArray, WithHeadings, WithEvents, WithTitle, WithCustomStartCell
{
    protected $kacabData = [];
    protected $totals = [];
    protected $monthName;
    protected $bulanFilter;
    protected $regionFilter;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->bulanFilter = $bulan;
        $this->regionFilter = $region;
        $this->monthName = Carbon::parse($bulan . '-01')->translatedFormat("F'y");
        $yearFilter = Carbon::parse($bulan . '-01')->format('Y');

        $query = InsentifMasterDistributor::where('bulan', $bulan);
        if ($region) $query->where('region_name', $region);
        if (!empty($areas)) $query->whereIn('area_name', $areas);
        
        $masterData = $query->orderBy('area_name')->orderBy('cabang')->get();

        $targets = TargetKacab::where('tahun', $yearFilter)->get()->keyBy(function($item) {
            return strtoupper(trim($item->cabang));
        });

        $actuals = DB::table('insentif_value_per_salesmans')
            ->select('distributor_code', DB::raw('SUM(actual) as total_actual'))
            ->where('bulan', $bulan)
            ->groupBy('distributor_code')
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->distributor_code));
            });

        $totalTarget = 0; $totalInsentif = 0; $totalSellOut = 0;
        $totalNilaiInsentif = 0; $totalPph = 0; $totalTrf = 0;

        // Build map of cabang -> distCode from masterData
        $cabangToDistCode = [];
        foreach ($masterData as $md) {
            $c = strtoupper(trim($md->cabang));
            $dc = strtoupper(trim($md->distributor_code));
            $cabangToDistCode[$c] = $dc;
        }

        // Apply Mappings
        $mappings = \App\Models\InsentifKacabMapping::all();
        $childToParentMap = $mappings->pluck('parent_cabang', 'child_cabang')->toArray();
        $parentToChildrenMap = [];
        foreach ($mappings as $m) {
            $parentToChildrenMap[$m->parent_cabang][] = $m->child_cabang;
        }

        foreach ($childToParentMap as $child => $parent) {
            $childDistCode = $cabangToDistCode[$child] ?? null;
            $parentDistCode = $cabangToDistCode[$parent] ?? null;

            if ($childDistCode && $actuals->has($childDistCode)) {
                $childActual = $actuals->get($childDistCode)->total_actual;
                if ($parentDistCode) {
                    if ($actuals->has($parentDistCode)) {
                        $actuals->get($parentDistCode)->total_actual += $childActual;
                    } else {
                        $actuals->put($parentDistCode, (object)['distributor_code' => $parentDistCode, 'total_actual' => $childActual]);
                    }
                }
            }
        }

        foreach ($masterData as $md) {
            $cabang = strtoupper(trim($md->cabang));
            $distCode = strtoupper(trim($md->distributor_code));
            
            // Skip rendering child cabangs
            if (array_key_exists($cabang, $childToParentMap)) {
                continue;
            }

            $targetData = $targets->get($cabang);
            $target = $targetData ? (float) $targetData->target : 0;
            $insentif = $targetData ? (float) $targetData->insentif : 0;
            $namaKacab = $targetData ? $targetData->nama_kacab : '-';

            $actualData = $actuals->get($distCode);
            $sellOut = $actualData ? (float) $actualData->total_actual : 0;

            // Rename cabang if it has children mapped to it
            $displayCabang = $cabang;
            if (isset($parentToChildrenMap[$cabang])) {
                $displayCabang .= ', ' . implode(', ', $parentToChildrenMap[$cabang]);
            }

            $percentage = $target > 0 ? ($sellOut / $target) * 100 : 0;
            $nilaiInsentif = $percentage >= 100 ? $insentif : 0;
            $pph = $nilaiInsentif * 0.05;
            $trf = $nilaiInsentif - $pph;

            $this->kacabData[] = [
                'area_name' => $md->area_name,
                'distributor_name' => $md->distributor_name,
                'cabang' => $displayCabang,
                'nama_kacab' => $namaKacab,
                'target' => $target,
                'insentif' => $insentif,
                'sell_out' => $sellOut,
                'percentage' => $percentage,
                'nilai_insentif' => $nilaiInsentif,
                'pph' => $pph,
                'trf' => $trf,
            ];

            $totalTarget += $target; $totalInsentif += $insentif; $totalSellOut += $sellOut;
            $totalNilaiInsentif += $nilaiInsentif; $totalPph += $pph; $totalTrf += $trf;
        }

        $this->totals = [
            'target' => $totalTarget, 'insentif' => $totalInsentif, 'sell_out' => $totalSellOut,
            'nilai_insentif' => $totalNilaiInsentif, 'pph' => $totalPph, 'trf' => $totalTrf,
        ];
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function title(): string
    {
        return 'Kacab';
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->kacabData as $row) {
            $rows[] = [
                $no++,
                $row['area_name'],
                $row['distributor_name'],
                $row['cabang'],
                $row['nama_kacab'],
                $row['target'],
                $row['insentif'],
                $row['sell_out'],
                $row['percentage'] / 100, // For Excel percentage format
                $row['nilai_insentif'],
                $row['pph'],
                $row['trf'],
            ];
        }

        // Add empty row
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', ''];

        // Add totals
        $totalPercentage = $this->totals['target'] > 0 ? ($this->totals['sell_out'] / $this->totals['target']) * 100 : 0;
        
        $rows[] = [
            '', '', '', '', 'GRAND TOTAL',
            $this->totals['target'],
            $this->totals['insentif'],
            $this->totals['sell_out'],
            $totalPercentage / 100, // For Excel percentage format
            $this->totals['nilai_insentif'],
            $this->totals['pph'],
            $this->totals['trf'],
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            [
                'No', 'Area', 'Distributor', 'Cabang', 'Nama Kacab', 
                'Target', 'Insentif', 'PENCAPAIAN BULAN ' . mb_strtoupper($this->monthName),
                '', '', '', ''
            ],
            [
                '', '', '', '', '', 
                '', '', 'Sell Out', '%', 'Nilai Insentif', 'PPH 5%', 'TOTAL TRF'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $ws->setShowGridlines(false);
                $totalRows = count($this->kacabData);
                $lastRow = $totalRows + 6; // 2 titles (1,2) + 2 headers (3,4) + data + empty + grand total

                $colors = [
                    'identity'   => '1A237E', // Navy
                    'header_id'  => '1A237E', // Navy
                    'header_ach' => '1B4F72', // Blue dark
                    'font_white' => 'FFFFFF',
                    'zebra'      => 'F0F8FF', // AliceBlue
                    'gt_row'     => 'FCF3CF', // Light yellow
                ];

                // ── 1. Dynamic Titles ──────────────────────────────────────────
                $regionText = $this->regionFilter ?: 'SEMUA REGION';
                $periodText = mb_strtoupper(Carbon::parse($this->bulanFilter . '-01')->translatedFormat('F Y'));
                
                $ws->setCellValue('A1', "PROGRAM INSENTIF SALES INAFOOD REGION {$regionText}");
                $ws->setCellValue('A2', "PERIODE {$periodText}");

                $ws->getStyle('A1:A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $colors['identity']]],
                ]);

                // ── 2. Merge Headers ──────────────────────────────────────────
                // Identity + Target + Insentif
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
                    $ws->mergeCells($col . '3:' . $col . '4');
                }
                
                // Pencapaian
                $ws->mergeCells('H3:L3');

                // ── 3. Header Styling ─────────────────────────────────────────
                // Identity
                $ws->getStyle('A3:G4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_id']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ]);

                // Pencapaian
                $ws->getStyle('H3:L4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_ach']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ]);

                // ── 4. Row Heights ────────────────────────────────────────────
                $ws->getRowDimension(3)->setRowHeight(25);
                $ws->getRowDimension(4)->setRowHeight(25);
                for ($r = 5; $r <= $lastRow; $r++) {
                    $ws->getRowDimension($r)->setRowHeight(18);
                }

                // ── 5. Borders ───────────────────────────────────────────────
                $ws->getStyle('A3:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B0BEC5'],
                        ],
                    ],
                ]);

                // Thick right border for group boundaries
                foreach (['G', 'L'] as $col) {
                    $ws->getStyle($col . '3:' . $col . $lastRow)->applyFromArray([
                        'borders' => [
                            'right' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                'color'       => ['rgb' => '546E7A'],
                            ],
                        ],
                    ]);
                }

                // ── 6. Zebra striping (row 5+) ──────────────────────────────
                for ($r = 5; $r <= $lastRow - 2; $r++) {
                    if ($r % 2 === 0) {
                        $ws->getStyle('A' . $r . ':L' . $r)->applyFromArray([
                            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['zebra']]],
                        ]);
                    }
                }

                // ── 7. Grand Total row ────────────────────────────────────
                $ws->getStyle('A' . $lastRow . ':L' . $lastRow)->applyFromArray([
                    'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['gt_row']]],
                    'font'    => ['bold' => true, 'size' => 11],
                    'borders' => [
                        'top'        => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '78281F']],
                        'bottom'     => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '78281F']],
                    ],
                ]);

                // ── 8. Number formats ───────────────────────────────────────
                $rupiahFormat = '#,##0';
                $pctFormat    = '0.0%';

                // Target, Insentif, Sell Out
                $ws->getStyle('F5:H' . $lastRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                
                // %
                $ws->getStyle('I5:I' . $lastRow)->getNumberFormat()->setFormatCode($pctFormat);
                
                // Nilai Insentif, PPH, TRF
                $ws->getStyle('J5:L' . $lastRow)->getNumberFormat()->setFormatCode($rupiahFormat);

                // ── 9. Freeze panes ──────────────────────────────────────────
                $ws->freezePane('F5');

                // ── 10. Alignment ────────────────────────────────────────────
                $ws->getStyle('A5:L' . $lastRow)->applyFromArray([
                    'alignment' => ['vertical' => 'center'],
                ]);
                $ws->getStyle('F5:L' . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                ]);
                $ws->getStyle('A5:E' . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'left'],
                ]);
                
                // ── 11. Column widths ──────────────────────────────────────────
                $ws->getColumnDimension('A')->setWidth(5);   // No
                $ws->getColumnDimension('B')->setWidth(13);  // Area
                $ws->getColumnDimension('C')->setWidth(25);  // Distributor
                $ws->getColumnDimension('D')->setWidth(15);  // Cabang
                $ws->getColumnDimension('E')->setWidth(20);  // Nama Kacab
                $ws->getStyle('E5:E' . $lastRow)->getAlignment()->setWrapText(true);

                foreach (['F', 'G', 'H', 'I', 'J', 'K', 'L'] as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
