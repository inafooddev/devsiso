<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Livewire\Others\Insentif\Perhitungan\InsentifSpv;
use Carbon\Carbon;

class InsentifSpvSheet implements FromArray, WithHeadings, WithStyles, WithEvents, WithTitle, WithCustomStartCell
{
    protected $bulan;
    protected $region;
    protected $areas;
    
    protected $spvData    = [];
    protected $grandTotal = [];
    protected $headers    = [];
    protected $monthName;
    protected $titleMonthYear;
    protected $regionLabel;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->bulan  = $bulan;
        $this->region = $region;
        $this->areas  = $areas;

        $carbon = Carbon::parse($bulan . '-01');
        $this->monthName      = $carbon->translatedFormat("F'y");
        $this->titleMonthYear = mb_strtoupper($carbon->translatedFormat('F Y'));
        $this->regionLabel    = mb_strtoupper($region);

        $this->fetchData();
    }
    
    protected function fetchData()
    {
        $component = app(InsentifSpv::class);
        $component->filterBulan = $this->bulan;
        $component->filterRegion = $this->region;
        $component->filterArea = $this->areas;
        
        $view = $component->render();
        $data = $view->getData();
        
        $this->spvData = $data['spvData'] ?? [];
        $this->grandTotal = $data['grandTotal'] ?? [];
        $this->headers = $data['headers'] ?? [];
    }
    
    public function title(): string
    {
        return 'SPV';
    }

    public function startCell(): string
    {
        // Rows 1 & 2 reserved for report titles
        return 'A3';
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->spvData as $spv) {
            $idx = 0;
            foreach ($spv['cabangs'] as $cabang => $cabData) {
                foreach ($cabData['distributors'] as $cIdx => $dist) {
                    $row = [];
                    
                    $row[] = $dist['area_name'];
                    $row[] = $dist['distributor_name'];
                    $row[] = $dist['cabang'];

                    if ($idx === 0) {
                        $row[] = $spv['supervisor_name'];
                    } else {
                        $row[] = '';
                    }
                    
                    $row[] = $dist['target_so'];
                    $row[] = $dist['aktual_so'];
                    
                    if ($idx === 0) {
                        $row[] = $spv['pencapaian_persen'] / 100;
                        $row[] = $spv['ins_so'];
                    } else {
                        $row[] = '';
                        $row[] = '';
                    }
                        
                    if ($cIdx === 0) {
                        foreach ($this->headers as $h) {
                            $ach = $cabData['vtkp_achievements'][$h->nama_header] ?? ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
                            $row[] = $ach['target'];
                            $row[] = $ach['real'];
                            $row[] = ($ach['growth'] ?? 0) / 100;
                            $row[] = $ach['insentif'];
                        }
                        
                        $row[] = $spv['total_insentif_vtkp'];
                    } else {
                        foreach ($this->headers as $h) {
                            $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';
                        }
                        
                        $row[] = '';
                    }

                    $row[] = $dist['rwo_peserta'];
                    $row[] = $dist['rwo_achieve'];
                    
                    if ($idx === 0) {
                        $row[] = $spv['total_rwo_peserta'];
                        $row[] = $spv['total_rwo_achieve'];
                        $row[] = $spv['rwo_achieve_pct'] / 100;
                        $row[] = $spv['insentif_rwo'];
                    } else {
                        $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';
                    }

                    $row[] = $dist['ipt_sku'];
                    $row[] = $dist['ipt_ec'];

                    if ($idx === 0) {
                        $row[] = $spv['total_ipt_sku'];
                        $row[] = $spv['total_ipt_ec'];
                        $row[] = $spv['ipt'];
                        $row[] = $spv['insentif_ipt'];
                        
                        $row[] = $spv['total_all_insentif'];
                        $row[] = $spv['tabungan_30'];
                        $row[] = $spv['transfer_70'];
                    } else {
                        $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';
                    }
                    
                    $rows[] = $row;
                    $idx++;
                }
            }
        }

        // Add empty row
        $emptyRow = array_fill(0, 8 + (count($this->headers) * 4) + 1 + 17, '');
        $rows[] = $emptyRow;

        // Add Grand Total
        if (!empty($this->grandTotal)) {
            $gtRow = array_fill(0, 8 + (count($this->headers) * 4) + 1 + 17, '');
            $gtRow[0] = ''; $gtRow[1] = ''; $gtRow[2] = ''; $gtRow[3] = 'GRAND TOTAL';
            $gtRow[4] = $this->grandTotal['target_so'];
            $gtRow[5] = $this->grandTotal['aktual_so'];
            $gtRow[6] = ($this->grandTotal['pencapaian_persen'] ?? 0) / 100;
            $gtRow[7] = $this->grandTotal['ins_so'];
            
            $colIdx = 8;
            foreach ($this->headers as $h) {
                $tgt    = $this->grandTotal['vtkp'][$h->nama_header]['target']   ?? 0;
                $real   = $this->grandTotal['vtkp'][$h->nama_header]['real']     ?? 0;
                $growth = $this->grandTotal['vtkp'][$h->nama_header]['growth']   ?? 0;
                $ins    = $this->grandTotal['vtkp'][$h->nama_header]['insentif'] ?? 0;
                
                $gtRow[$colIdx++] = $tgt;
                $gtRow[$colIdx++] = $real;
                $gtRow[$colIdx++] = $growth / 100;
                $gtRow[$colIdx++] = $ins;
            }
            
            $gtRow[$colIdx++] = $this->grandTotal['total_insentif_vtkp'];
            
            $gtRow[$colIdx++] = $this->grandTotal['rwo_peserta'];
            $gtRow[$colIdx++] = $this->grandTotal['rwo_achieve'];
            $gtRow[$colIdx++] = $this->grandTotal['rwo_peserta'];
            $gtRow[$colIdx++] = $this->grandTotal['rwo_achieve'];
            $gtRow[$colIdx++] = ($this->grandTotal['rwo_achieve_pct'] ?? 0) / 100;
            $gtRow[$colIdx++] = $this->grandTotal['insentif_rwo'];

            $gtRow[$colIdx++] = $this->grandTotal['ipt_sku'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt_ec'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt_sku'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt_ec'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotal['insentif_ipt'];

            $gtRow[$colIdx++] = $this->grandTotal['total_all_insentif'];
            $gtRow[$colIdx++] = $this->grandTotal['tabungan_30'];
            $gtRow[$colIdx++] = $this->grandTotal['transfer_70'];
            
            $rows[] = $gtRow;
        }

        return $rows;
    }

    public function headings(): array
    {
        $h1 = [
            'Area', 'Distributor Name', 'Cabang', 'Nama SPV',
            'PENCAPAIAN BULAN ' . mb_strtoupper($this->monthName), '', '', ''
        ];
        
        foreach ($this->headers as $h) {
            $h1[] = $h->nama_header;
            $h1[] = ''; $h1[] = ''; $h1[] = '';
        }
        $h1[] = 'TOTAL INSENTIF VTKP';
        
        $h1[] = 'RWO'; $h1[] = '';
        $h1[] = 'Total RWO'; $h1[] = ''; $h1[] = ''; $h1[] = '';
        
        $h1[] = 'IPT'; $h1[] = '';
        $h1[] = 'Total IPT'; $h1[] = ''; $h1[] = ''; $h1[] = '';
        
        $h1[] = 'TOTAL INSENTIF ALL PROGRAM';
        $h1[] = '30% TABUNGAN';
        $h1[] = '70% TRANSFER';

        $h2 = [
            '', '', '', '',
            'Target (Rp)', 'Aktual SO (Rp)', '%', 'INS SO'
        ];
        
        foreach ($this->headers as $h) {
            $h2[] = 'Target'; $h2[] = 'Real'; $h2[] = 'Growth %'; $h2[] = 'Insentif';
        }
        $h2[] = '';
        
        $h2[] = 'RWO (Peserta)'; $h2[] = 'RWO (Achieve)';
        $h2[] = 'Peserta'; $h2[] = 'Achieve'; $h2[] = '%'; $h2[] = 'Insentif';
        
        $h2[] = 'SKU'; $h2[] = 'EC';
        $h2[] = 'Total SKU'; $h2[] = 'Total EC'; $h2[] = 'IPT'; $h2[] = 'Insentif';
        
        $h2[] = ''; $h2[] = ''; $h2[] = '';


        return [$h1, $h2];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $ws->setShowGridlines(false);

                // ── Color palette ────────────────────────────────────────────
                $colors = [
                    'header_id'         => '1F3864',
                    'header_so'         => '1F497D',
                    'header_vtkp'       => '215732',
                    'header_total_vtkp' => '375623',
                    'header_rwo'        => '7B3F00',
                    'header_total_rwo'  => '6E3600',
                    'header_ipt'        => '4A235A',
                    'header_total_ipt'  => '3D1A4F',
                    'header_grand'      => '78281F',
                    'gt_row'            => 'FCF3CF',
                    'zebra'             => 'EBF5FB',
                    'font_white'        => 'FFFFFF',
                ];

                $totalCols  = count($this->headings()[0]);
                $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
                $lastRow    = count($this->spvData) > 0 ? (count($this->array()) + 4) : 4;

                // ── 0. Title rows 1 & 2 ──────────────────────────────────
                $ws->setCellValue('A1', 'PROGRAM INSENTIF SALES INAFOOD REGION ' . $this->regionLabel);
                $ws->setCellValue('A2', 'PERIODE ' . $this->titleMonthYear);
                $ws->mergeCells('A1:' . $highestCol . '1');
                $ws->mergeCells('A2:' . $highestCol . '2');
                $ws->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A237E']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $ws->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '283593']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $ws->getRowDimension(1)->setRowHeight(32);
                $ws->getRowDimension(2)->setRowHeight(22);

                // ── 1. Merge identity cols A-D rows 3-4 ───────────────────
                foreach (['A', 'B', 'C', 'D'] as $col) {
                    $ws->mergeCells($col . '3:' . $col . '4');
                }

                // ── 2. Merge & colour group headers ─────────────────────────
                $ws->mergeCells('E3:H3');

                $colIdx = 9;
                foreach ($this->headers as $h) {
                    $s = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $e = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                    $ws->mergeCells($s . '3:' . $e . '3');
                    $ws->getStyle($s . '3:' . $e . '4')->applyFromArray([
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_vtkp']]],
                        'font' => ['color' => ['rgb' => $colors['font_white']]],
                    ]);
                    $colIdx += 4;
                }

                $tvCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $ws->mergeCells($tvCol . '3:' . $tvCol . '4');
                $ws->getStyle($tvCol . '3:' . $tvCol . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_total_vtkp']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                ]);
                $colIdx++;

                $rwoS = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $rwoE = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $ws->mergeCells($rwoS . '3:' . $rwoE . '3');
                $ws->getStyle($rwoS . '3:' . $rwoE . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_rwo']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 2;

                $trwoS = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $trwoE = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                $ws->mergeCells($trwoS . '3:' . $trwoE . '3');
                $ws->getStyle($trwoS . '3:' . $trwoE . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_total_rwo']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 4;

                $iptS = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $iptE = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $ws->mergeCells($iptS . '3:' . $iptE . '3');
                $ws->getStyle($iptS . '3:' . $iptE . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_ipt']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 2;

                $tiptS = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $tiptE = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                $ws->mergeCells($tiptS . '3:' . $tiptE . '3');
                $ws->getStyle($tiptS . '3:' . $tiptE . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_total_ipt']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 4;

                for ($i = 0; $i < 3; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + $i);
                    $ws->mergeCells($col . '3:' . $col . '4');
                    $ws->getStyle($col . '3:' . $col . '4')->applyFromArray([
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_grand']]],
                        'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                    ]);
                }

                // ── 3. Identity & SO header colours ──────────────────────
                $ws->getStyle('A3:D4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_id']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                ]);
                $ws->getStyle('E3:H4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_so']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                ]);

                // ── 4. Header alignment, wrapText, row height ────────────────
                $ws->getStyle('A3:' . $highestCol . '4')->applyFromArray([
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'font'      => ['bold' => true],
                ]);
                $ws->getRowDimension(3)->setRowHeight(28);
                $ws->getRowDimension(4)->setRowHeight(36);

                // ── 5. Borders ────────────────────────────────────────────
                $ws->getStyle('A3:' . $highestCol . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B0BEC5'],
                        ],
                    ],
                ]);

                $groupEndCols = [];
                $gi = 8;
                $groupEndCols[] = $gi;
                foreach ($this->headers as $h) { $gi += 4; }
                $groupEndCols[] = $gi;
                $gi++;
                $groupEndCols[] = $gi + 1;
                $gi += 2;
                $groupEndCols[] = $gi + 3;
                $gi += 4;
                $groupEndCols[] = $gi + 1;
                $gi += 2;
                $groupEndCols[] = $gi + 3;

                foreach ($groupEndCols as $gbCol) {
                    $gbLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($gbCol);
                    $ws->getStyle($gbLetter . '3:' . $gbLetter . $lastRow)->applyFromArray([
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
                    $distName = $ws->getCell('B' . $r)->getValue();
                    if ($distName === 'VACANT') {
                        $ws->getStyle('A' . $r . ':' . $highestCol . $r)->applyFromArray([
                            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFCCCC']],
                            'font' => ['color' => ['rgb' => 'FF0000'], 'bold' => true],
                        ]);
                    } elseif ($r % 2 === 0) {
                        $ws->getStyle('A' . $r . ':' . $highestCol . $r)->applyFromArray([
                            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['zebra']]],
                        ]);
                    }
                }

                // ── 7. Grand Total row ────────────────────────────────────
                $ws->getStyle('A' . $lastRow . ':' . $highestCol . $lastRow)->applyFromArray([
                    'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['gt_row']]],
                    'font'    => ['bold' => true, 'size' => 11],
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']],
                        'top'        => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '78281F']],
                        'bottom'     => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '78281F']],
                    ],
                ]);

                // ── 8. Number formats ───────────────────────────────────────
                $dataStart    = 5;
                $lastDataRow  = $lastRow;
                $rupiahFormat = '#,##0';
                $pctFormat    = '0.0%';
                $iptFormat    = '#,##0';

                $ws->getStyle('E' . $dataStart . ':E' . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $ws->getStyle('F' . $dataStart . ':F' . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $ws->getStyle('G' . $dataStart . ':G' . $lastDataRow)->getNumberFormat()->setFormatCode($pctFormat);
                $ws->getStyle('H' . $dataStart . ':H' . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);

                $vIdx = 9;
                foreach ($this->headers as $h) {
                    $cT = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx);
                    $cR = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 1);
                    $cG = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 2);
                    $cI = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 3);
                    $ws->getStyle($cT . $dataStart . ':' . $cT . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                    $ws->getStyle($cR . $dataStart . ':' . $cR . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                    $ws->getStyle($cG . $dataStart . ':' . $cG . $lastDataRow)->getNumberFormat()->setFormatCode($pctFormat);
                    $ws->getStyle($cI . $dataStart . ':' . $cI . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                    $vIdx += 4;
                }

                $tvCol2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx);
                $ws->getStyle($tvCol2 . $dataStart . ':' . $tvCol2 . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $vIdx += 3; // skip Total VTKP + 2 RWO dist cols

                $trwoPct = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 2);
                $trwoIns = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 3);
                $ws->getStyle($trwoPct . $dataStart . ':' . $trwoPct . $lastDataRow)->getNumberFormat()->setFormatCode($pctFormat);
                $ws->getStyle($trwoIns . $dataStart . ':' . $trwoIns . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $vIdx += 6; // Total RWO (4) + 2 IPT dist cols

                $tiptIpt = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 2);
                $tiptIns = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 3);
                $ws->getStyle($tiptIpt . $dataStart . ':' . $tiptIpt . $lastDataRow)->getNumberFormat()->setFormatCode($iptFormat);
                $ws->getStyle($tiptIns . $dataStart . ':' . $tiptIns . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $vIdx += 4;

                for ($i = 0; $i < 3; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + $i);
                    $ws->getStyle($col . $dataStart . ':' . $col . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                }

                // ── 9. Freeze panes ──────────────────────────────────────────
                $ws->freezePane('E5');

                // ── 10. Row heights ──────────────────────────────────────────
                for ($r = 5; $r <= $lastRow; $r++) {
                    $ws->getRowDimension($r)->setRowHeight(18);
                }

                // ── 11. Alignment ────────────────────────────────────────────
                $ws->getStyle('A5:' . $highestCol . $lastRow)->applyFromArray([
                    'alignment' => ['vertical' => 'center'],
                ]);
                $ws->getStyle('E5:' . $highestCol . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                ]);
                $ws->getStyle('A5:D' . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'left'],
                ]);

                // ── 12. Column widths ──────────────────────────────────────────
                $ws->getColumnDimension('A')->setWidth(13);  // Area
                $ws->getColumnDimension('B')->setWidth(22);  // Distributor Name
                $ws->getColumnDimension('C')->setWidth(13);  // Cabang
                $ws->getColumnDimension('D')->setWidth(20);  // Nama SPV
                $ws->getStyle('D5:D' . $lastRow)->getAlignment()->setWrapText(true);

                for ($ci = 5; $ci <= $totalCols; $ci++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
                    $ws->getColumnDimension($colLetter)->setAutoSize(true);
                }

                // ── 13. Row-span merges for SPV grouping ───────────────────
                $currentRow = 5;
                foreach ($this->spvData as $spv) {
                    $rowspan = $spv['rowspan'];
                    if ($rowspan > 1) {
                        $endRow = $currentRow + $rowspan - 1;

                        $ws->mergeCells("D{$currentRow}:D{$endRow}");
                        $ws->mergeCells("G{$currentRow}:G{$endRow}");
                        $ws->mergeCells("H{$currentRow}:H{$endRow}");

                        $c = 9 + (count($this->headers) * 4);
                        $tvColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c++);
                        $ws->mergeCells("{$tvColLetter}{$currentRow}:{$tvColLetter}{$endRow}");

                        $c += 2;
                        for ($i = 0; $i < 4; $i++) {
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c++);
                            $ws->mergeCells("{$col}{$currentRow}:{$col}{$endRow}");
                        }

                        $c += 2;
                        for ($i = 0; $i < 7; $i++) {
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c++);
                            $ws->mergeCells("{$col}{$currentRow}:{$col}{$endRow}");
                        }
                    }

                    $cabangRow = $currentRow;
                    foreach ($spv['cabangs'] as $cabData) {
                        $cRowspan = $cabData['rowspan'];
                        if ($cRowspan > 1) {
                            $cEndRow = $cabangRow + $cRowspan - 1;
                            $c = 9;
                            foreach ($this->headers as $h) {
                                for ($i = 0; $i < 4; $i++) {
                                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + $i);
                                    $ws->mergeCells("{$col}{$cabangRow}:{$col}{$cEndRow}");
                                }
                                $c += 4;
                            }
                        }
                        $cabangRow += $cRowspan;
                    }

                    $currentRow += $rowspan;
                }
            },
        ];
    }
}
