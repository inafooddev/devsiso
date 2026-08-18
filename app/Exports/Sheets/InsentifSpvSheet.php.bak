<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Livewire\Others\Insentif\Perhitungan\InsentifSpv;
use Carbon\Carbon;

class InsentifSpvSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithTitle
{
    protected $bulan;
    protected $region;
    protected $areas;
    
    protected $spvData = [];
    protected $grandTotal = [];
    protected $headers = [];
    protected $monthName;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->bulan = $bulan;
        $this->region = $region;
        $this->areas = $areas;
        $this->monthName = Carbon::parse($bulan . '-01')->translatedFormat("F'y");
        
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
                        $row[] = number_format($spv['pencapaian_persen'], 1, ',', '.') . '%';
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
                            $row[] = number_format($ach['growth'], 1, ',', '.') . '%';
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
                        $row[] = number_format($spv['rwo_achieve_pct'], 1, ',', '.') . '%';
                        $row[] = $spv['insentif_rwo'];
                    } else {
                        $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';
                    }

                    $row[] = $dist['ipt_sku'];
                    $row[] = $dist['ipt_ec'];

                    if ($idx === 0) {
                        $row[] = $spv['total_ipt_sku'];
                        $row[] = $spv['total_ipt_ec'];
                        $row[] = number_format($spv['ipt'], 1, ',', '.');
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
            $gtRow[6] = number_format($this->grandTotal['pencapaian_persen'], 1, ',', '.') . '%';
            $gtRow[7] = $this->grandTotal['ins_so'];
            
            $colIdx = 8;
            foreach ($this->headers as $h) {
                $tgt = $this->grandTotal['vtkp'][$h->nama_header]['target'] ?? 0;
                $real = $this->grandTotal['vtkp'][$h->nama_header]['real'] ?? 0;
                $growth = $this->grandTotal['vtkp'][$h->nama_header]['growth'] ?? 0;
                $ins = $this->grandTotal['vtkp'][$h->nama_header]['insentif'] ?? 0;
                
                $gtRow[$colIdx++] = $tgt;
                $gtRow[$colIdx++] = $real;
                $gtRow[$colIdx++] = number_format($growth, 1, ',', '.') . '%';
                $gtRow[$colIdx++] = $ins;
            }
            
            $gtRow[$colIdx++] = $this->grandTotal['total_insentif_vtkp'];
            
            $gtRow[$colIdx++] = $this->grandTotal['rwo_peserta'];
            $gtRow[$colIdx++] = $this->grandTotal['rwo_achieve'];
            $gtRow[$colIdx++] = $this->grandTotal['rwo_peserta'];
            $gtRow[$colIdx++] = $this->grandTotal['rwo_achieve'];
            $gtRow[$colIdx++] = number_format($this->grandTotal['rwo_achieve_pct'], 1, ',', '.') . '%';
            $gtRow[$colIdx++] = $this->grandTotal['insentif_rwo'];

            $gtRow[$colIdx++] = $this->grandTotal['ipt_sku'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt_ec'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt_sku'];
            $gtRow[$colIdx++] = $this->grandTotal['ipt_ec'];
            $gtRow[$colIdx++] = number_format($this->grandTotal['ipt'], 1, ',', '.');
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
        $cols = count($this->headings()[0]);
        $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols);
        $lastRow = count($this->spvData) > 0 ? (count($this->array()) + 2) : 2;
        
        $sheet->getStyle('A1:' . $highestColumn . ($lastRow - 2))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);
        
        $sheet->getStyle('A' . $lastRow . ':' . $highestColumn . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        return [
            1 => ['alignment' => ['horizontal' => 'center', 'vertical' => 'center'], 'font' => ['bold' => true]],
            2 => ['alignment' => ['horizontal' => 'center', 'vertical' => 'center'], 'font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Freeze Panes (Row 1-2, Col A-D)
                $sheet->freezePane('E3');
                
                // Merge common headers
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells('E1:H1'); // Pencapaian Bulan
                
                $colIdx = 9; // I
                foreach ($this->headers as $h) {
                    $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                    $sheet->mergeCells($startCol . '1:' . $endCol . '1');
                    
                    // Style VTKP Headers
                    $sheet->getStyle($startCol . '1:' . $endCol . '2')->applyFromArray([
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6E6FA']]
                    ]);
                    
                    $colIdx += 4;
                }
                
                // TOTAL INSENTIF VTKP
                $totalVtkpCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->mergeCells($totalVtkpCol . '1:' . $totalVtkpCol . '2');
                $sheet->getStyle($totalVtkpCol . '1:' . $totalVtkpCol . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD8BFD8']]
                ]);
                $colIdx++;
                
                // RWO
                $rwoStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $rwoEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->mergeCells($rwoStart . '1:' . $rwoEnd . '1');
                $sheet->getStyle($rwoStart . '1:' . $rwoEnd . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE4B5']]
                ]);
                $colIdx += 2;
                
                // Total RWO
                $totRwoStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $totRwoEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                $sheet->mergeCells($totRwoStart . '1:' . $totRwoEnd . '1');
                $sheet->getStyle($totRwoStart . '1:' . $totRwoEnd . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFDAB9']]
                ]);
                $colIdx += 4;

                // IPT
                $iptStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $iptEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->mergeCells($iptStart . '1:' . $iptEnd . '1');
                $sheet->getStyle($iptStart . '1:' . $iptEnd . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0FFFF']]
                ]);
                $colIdx += 2;

                // Total IPT
                $totIptStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $totIptEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                $sheet->mergeCells($totIptStart . '1:' . $totIptEnd . '1');
                $sheet->getStyle($totIptStart . '1:' . $totIptEnd . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFAFEEEE']]
                ]);
                $colIdx += 4;
                
                // Totals
                $totalColors = ['FF98FB98', 'FFFFE066', 'FFADD8E6']; // Emerald, Yellow, Blue
                for ($i=0; $i<3; $i++) {
                    $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->mergeCells($c . '1:' . $c . '2');
                    $sheet->getStyle($c . '1:' . $c . '2')->applyFromArray([
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => $totalColors[$i]]]
                    ]);
                    $colIdx++;
                }
                
                // Base Info Style
                $sheet->getStyle('A1:D2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']]
                ]);
                
                // SO Style
                $sheet->getStyle('E1:H2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6F2FF']]
                ]);

                // Format numbers & alignments
                $lastRow = count($this->spvData) > 0 ? (count($this->array()) + 2) : 2;
                $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx - 1);
                $sheet->getStyle('E3:' . $highestCol . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
                
                // Set Auto Width for all columns, except A to C where we might want some width
                foreach (range('A', $highestCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Apply Rowspans for Grouping
                $currentRow = 3;
                foreach ($this->spvData as $spv) {
                    $rowspan = $spv['rowspan'];
                    if ($rowspan > 1) {
                        $endRow = $currentRow + $rowspan - 1;
                        // Nama SPV is at D
                        $sheet->mergeCells("D{$currentRow}:D{$endRow}");
                        
                        // Percentage and Ins SO are at G and H
                        $sheet->mergeCells("G{$currentRow}:G{$endRow}");
                        $sheet->mergeCells("H{$currentRow}:H{$endRow}");
                        
                        $c = 9; // VTKP starts at column I (9)
                        $numVtkpProductCols = count($this->headers) * 4;
                        
                        // We merge VTKP products in a separate loop for cabangs
                        $c += $numVtkpProductCols;
                        
                        // Merge TOTAL INSENTIF VTKP per SPV
                        $totalVtkpCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c++);
                        $sheet->mergeCells("{$totalVtkpCol}{$currentRow}:{$totalVtkpCol}{$endRow}");
                        
                        $c += 2; // Skip RWO (Peserta) & RWO (Achieve)
                        for($i=0; $i<4; $i++) { // Total RWO
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c++);
                            $sheet->mergeCells("{$col}{$currentRow}:{$col}{$endRow}");
                        }
                        
                        $c += 2; // Skip IPT SKU & EC
                        for($i=0; $i<7; $i++) { // Total IPT & All Totals
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c++);
                            $sheet->mergeCells("{$col}{$currentRow}:{$col}{$endRow}");
                        }
                    }
                    
                    // Now loop cabangs for VTKP merging
                    $cabangRow = $currentRow;
                    foreach ($spv['cabangs'] as $cabData) {
                        $cRowspan = $cabData['rowspan'];
                        if ($cRowspan > 1) {
                            $cEndRow = $cabangRow + $cRowspan - 1;
                            
                            $c = 9; // VTKP starts at column I (9)
                            foreach ($this->headers as $h) {
                                $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                                $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c+1);
                                $c3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c+2);
                                $c4 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c+3);
                                $sheet->mergeCells("{$c1}{$cabangRow}:{$c1}{$cEndRow}");
                                $sheet->mergeCells("{$c2}{$cabangRow}:{$c2}{$cEndRow}");
                                $sheet->mergeCells("{$c3}{$cabangRow}:{$c3}{$cEndRow}");
                                $sheet->mergeCells("{$c4}{$cabangRow}:{$c4}{$cEndRow}");
                                $c += 4;
                            }
                        }
                        $cabangRow += $cRowspan;
                    }
                    
                    // Vertical alignment top for merged cells
                    $sheet->getStyle("A{$currentRow}:" . $highestCol . ($currentRow + $rowspan - 1))
                        ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        
                    $currentRow += $rowspan;
                }
            },
        ];
    }
}
