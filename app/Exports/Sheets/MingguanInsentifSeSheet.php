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
use App\Livewire\Others\Insentif\Perhitungan\InsentifSe;
use Carbon\Carbon;

class MingguanInsentifSeSheet implements FromArray, WithHeadings, WithStyles, WithEvents, WithTitle, WithCustomStartCell
{
    protected $bulan;
    protected $region;
    protected $areas;
    
    protected $salesmenData = [];
    protected $headers = [];
    protected $monthName;
    protected $titleMonthYear;  // e.g. "JUNI 2026"
    protected $regionLabel;     // e.g. "JAWA BARAT"

    // Grand totals
    protected $grandTotals = [];
    protected $grandTotalValue = [];
    protected $grandTotalVtkp = 0;
    protected $grandTotalEc = [];
    protected $grandTotalIpt = [];
    protected $grandTotalSfa = [];
    protected $grandTotalKeseluruhan = 0;
    protected $grandTotalPph = 0;
    protected $grandTotalThp = 0;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->bulan   = $bulan;
        $this->region  = $region;
        $this->areas   = $areas;

        $carbon = Carbon::parse($bulan . '-01');
        $this->monthName      = $carbon->translatedFormat("F'y");
        $this->titleMonthYear = mb_strtoupper($carbon->translatedFormat('F Y'));
        $this->regionLabel    = mb_strtoupper($region);

        $this->fetchData();
    }
    
    protected function fetchData()
    {
        $component = app(InsentifSe::class);
        $component->filterBulan = $this->bulan;
        $component->filterRegion = $this->region;
        $component->filterArea = $this->areas;
        
        $view = $component->render();
        $data = $view->getData();
        
        $this->salesmenData = $data['salesmenData'] ?? [];
        $this->headers = $data['headers'] ?? [];
        
        $this->grandTotals = $data['grandTotals'] ?? [];
        $this->grandTotalValue = $data['grandTotalValue'] ?? [];
        $this->grandTotalVtkp = $data['grandTotalVtkp'] ?? 0;
        $this->grandTotalEc = $data['grandTotalEc'] ?? [];
        $this->grandTotalIpt = $data['grandTotalIpt'] ?? [];
        $this->grandTotalSfa = $data['grandTotalSfa'] ?? [];
        $this->grandTotalKeseluruhan = $data['grandTotalKeseluruhan'] ?? 0;
        $this->grandTotalPph = $data['grandTotalPph'] ?? 0;
        $this->grandTotalThp = $data['grandTotalThp'] ?? 0;
    }
    
    public function title(): string
    {
        return 'SE';
    }

    public function startCell(): string
    {
        // Rows 1 & 2 are reserved for the report titles;
        // headings (row 3 & 4) and data start from row 3.
        return 'A3';
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->salesmenData as $s) {
            $row = [];
            $row[] = $no++;              // A
            $row[] = $s['area'];         // B
            $row[] = $s['distributor']; // C
            $row[] = $s['cabang'];       // D
            $row[] = $s['kode_se'];      // E
            $row[] = $s['nama_se'];      // F
            
            // Value
            $row[] = $s['value_target'] ?? 0;
            $row[] = $s['value_real'] ?? 0;
            $row[] = ($s['value_ach'] ?? 0) / 100;   // numeric for % format
            $row[] = $s['value_insentif'] ?? 0;
            
            // VTKP
            foreach ($this->headers as $h) {
                $ach = $s['achievements'][$h->nama_header] ?? ['target'=>0, 'real'=>0, 'growth'=>0, 'insentif'=>0];
                $row[] = $ach['target'];
                $row[] = $ach['real'];
                $row[] = ($ach['growth'] ?? 0) / 100;  // numeric for % format
                $row[] = $ach['insentif'];
            }
            $row[] = $s['total_insentif_vtkp'] ?? 0;
            
            // EC
            $row[] = $s['ro'] ?? 0;
            $row[] = $s['ac'] ?? 0;
            $row[] = $s['ec'] ?? 0;
            $row[] = ($s['persen_ec'] ?? 0) / 100;    // numeric for % format
            $row[] = $s['ec_harian'] ?? 0;
            $row[] = $s['insentif_ec'] ?? 0;
            
            // IPT
            $row[] = $s['ipt_sku'] ?? 0;
            $row[] = $s['ipt_ec'] ?? 0;
            $row[] = $s['ipt'] ?? 0;                  // numeric for #,##0.0 format
            $row[] = $s['insentif_ipt'] ?? 0;
            
            // SFA
            $row[] = $s['sfa_pc'] ?? 0;
            $row[] = $s['sfa_ac'] ?? 0;
            $row[] = ($s['sfa_persen'] ?? 0) / 100;  // numeric for % format
            
            // Grand Total
            $row[] = $s['total_insentif'] ?? 0;
            $row[] = $s['pph_5'] ?? 0;
            $row[] = $s['thp'] ?? 0;
            
            $rows[] = $row;
        }

        // Add empty row  (6 identity + 4 value + n*4 VTKP + 1 total VTKP + 6 EC + 4 IPT + 3 SFA + 3 grand)
        $emptyRow = array_fill(0, 6 + 4 + (count($this->headers) * 4) + 1 + 6 + 4 + 3 + 3, '');
        $rows[] = $emptyRow;

        // Add Grand Total row
        if (!empty($this->salesmenData)) {
            $gtRow = array_fill(0, count($emptyRow), '');
            $gtRow[0] = ''; $gtRow[1] = ''; $gtRow[2] = 'GRAND TOTAL';

            // Value group starts at index 6 (col G)
            $gtRow[6] = $this->grandTotalValue['target'] ?? 0;
            $gtRow[7] = $this->grandTotalValue['real'] ?? 0;
            $gtRow[8] = ($this->grandTotalValue['ach'] ?? 0) / 100;
            $gtRow[9] = $this->grandTotalValue['insentif'] ?? 0;

            $colIdx = 10; // VTKP starts at index 10 (col K)
            foreach ($this->headers as $h) {
                $ach = $this->grandTotals[$h->nama_header] ?? ['target'=>0, 'real'=>0, 'growth'=>0, 'insentif'=>0];
                $gtRow[$colIdx++] = $ach['target'];
                $gtRow[$colIdx++] = $ach['real'];
                $gtRow[$colIdx++] = ($ach['growth'] ?? 0) / 100;
                $gtRow[$colIdx++] = $ach['insentif'];
            }
            
            $gtRow[$colIdx++] = $this->grandTotalVtkp;
            
            $gtRow[$colIdx++] = $this->grandTotalEc['ro'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalEc['ac'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalEc['ec'] ?? 0;
            $gtRow[$colIdx++] = ($this->grandTotalEc['persen_ec'] ?? 0) / 100;
            $gtRow[$colIdx++] = $this->grandTotalEc['ec_harian'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalEc['insentif'] ?? 0;
            
            $gtRow[$colIdx++] = $this->grandTotalIpt['sku'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalIpt['ec'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalIpt['ipt'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalIpt['insentif'] ?? 0;
            
            $gtRow[$colIdx++] = $this->grandTotalSfa['pc'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalSfa['ac'] ?? 0;
            $gtRow[$colIdx++] = ($this->grandTotalSfa['persen'] ?? 0) / 100;
            
            $gtRow[$colIdx++] = $this->grandTotalKeseluruhan;
            $gtRow[$colIdx++] = $this->grandTotalPph;
            $gtRow[$colIdx++] = $this->grandTotalThp;
            
            $rows[] = $gtRow;
        }

        return $rows;
    }

    public function headings(): array
    {
        $h1 = [
            'No', 'Area', 'Distributor Name', 'Cabang', 'Kode SE', 'Nama SE',
            'PENCAPAIAN BULAN ' . mb_strtoupper($this->monthName), '', '', ''
        ];

        foreach ($this->headers as $h) {
            $h1[] = $h->nama_header;
            $h1[] = ''; $h1[] = ''; $h1[] = '';
        }
        $h1[] = 'TOTAL INSENTIF VTKP';

        $h1[] = 'EC'; $h1[] = ''; $h1[] = ''; $h1[] = ''; $h1[] = ''; $h1[] = '';
        $h1[] = 'IPT'; $h1[] = ''; $h1[] = ''; $h1[] = '';
        $h1[] = 'SFA'; $h1[] = ''; $h1[] = '';

        $h1[] = 'Total Insentif Keseluruhan';
        $h1[] = 'PPH 5%';
        $h1[] = 'THP (TOTAL TRF)';

        $h2 = [
            '', '', '', '', '', '',           // 6 identity cols
            'Target (Rp)', 'Aktual (Rp)', 'Ach', 'Insentif'
        ];

        foreach ($this->headers as $h) {
            $h2[] = 'Target'; $h2[] = 'Real'; $h2[] = 'Growth %'; $h2[] = 'Insentif';
        }
        $h2[] = '';

        $h2[] = 'RO'; $h2[] = 'AC'; $h2[] = 'EC'; $h2[] = '% EC'; $h2[] = 'EC HARIAN'; $h2[] = 'Insentif';
        $h2[] = 'SKU'; $h2[] = 'EC'; $h2[] = 'IPT'; $h2[] = 'Insentif';
        $h2[] = 'PC'; $h2[] = 'AC'; $h2[] = '%';

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
            AfterSheet::class => function(AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $ws->setShowGridlines(false);

                // ── Color palette ────────────────────────────────────────────
                $colors = [
                    'header_dark'   => '1F3864',  // dark navy  – row 1 base (identity)
                    'header_id'     => '1F3864',  // identity cols
                    'header_value'  => '1F497D',  // value group
                    'header_vtkp'   => '215732',  // VTKP groups
                    'header_total_vtkp' => '375623', // total insentif VTKP
                    'header_ec'     => '7B3F00',  // EC group
                    'header_ipt'    => '4A235A',  // IPT group
                    'header_sfa'    => '7E5109',  // SFA group
                    'header_grand'  => '78281F',  // grand totals
                    'gt_row'        => 'FCF3CF',  // grand total row bg
                    'zebra'         => 'EBF5FB',  // alternate row
                    'white'         => 'FFFFFF',
                    'font_white'    => 'FFFFFF',
                ];

                $totalCols  = count($this->headings()[0]);
                // +2 title rows + 2 header rows (from startCell A3) + data rows
                $lastRow    = count($this->salesmenData) > 0 ? (count($this->array()) + 4) : 4;
                $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // ── 0. Write title rows 1 & 2 ───────────────────────────────
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

                // ── 1. Merge identity cols (A-F) rows 3-4 ───────────────────
                foreach (['A','B','C','D','E','F'] as $col) {
                    $ws->mergeCells($col . '3:' . $col . '4');
                }

                // ── 2. Merge & colour group headers ─────────────────────────
                // Value group G3:J3
                $ws->mergeCells('G3:J3');

                $colIdx = 11; // K = start of VTKP sub-headers

                // VTKP sub-groups
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

                // Total Insentif VTKP
                $totalVtkpCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $ws->mergeCells($totalVtkpCol . '3:' . $totalVtkpCol . '4');
                $ws->getStyle($totalVtkpCol . '3:' . $totalVtkpCol . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_total_vtkp']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                ]);
                $colIdx++;

                // EC group
                $ecStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $ecEnd   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 5);
                $ws->mergeCells($ecStart . '3:' . $ecEnd . '3');
                $ws->getStyle($ecStart . '3:' . $ecEnd . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_ec']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 6;

                // IPT group
                $iptStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $iptEnd   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                $ws->mergeCells($iptStart . '3:' . $iptEnd . '3');
                $ws->getStyle($iptStart . '3:' . $iptEnd . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_ipt']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 4;

                // SFA group
                $sfaStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sfaEnd   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 2);
                $ws->mergeCells($sfaStart . '3:' . $sfaEnd . '3');
                $ws->getStyle($sfaStart . '3:' . $sfaEnd . '4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_sfa']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']]],
                ]);
                $colIdx += 3;

                // Grand total cols (3 cols: Total Insentif, PPH, THP)
                for ($i = 0; $i < 3; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + $i);
                    $ws->mergeCells($col . '3:' . $col . '4');
                    $ws->getStyle($col . '3:' . $col . '4')->applyFromArray([
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_grand']]],
                        'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                    ]);
                }

                // ── 3. Colour identity & Value header (rows 3-4) ────────────
                $ws->getStyle('A3:F4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_id']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                ]);
                $ws->getStyle('G3:J4')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['header_value']]],
                    'font' => ['color' => ['rgb' => $colors['font_white']], 'bold' => true],
                ]);

                // ── 4. Header alignment, wrap, row height ────────────────────
                $ws->getStyle('A3:' . $highestCol . '4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                        'wrapText'   => true,
                    ],
                    'font' => ['bold' => true],
                ]);
                $ws->getRowDimension(3)->setRowHeight(28);
                $ws->getRowDimension(4)->setRowHeight(36);

                // ── 5. Borders (header + data, rows 3 onwards) ──────────────
                $ws->getStyle('A3:' . $highestCol . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B0BEC5'],
                        ],
                    ],
                ]);

                // Thick right border between groups
                $groupBorderCols = [];
                $gIdx = 10; // after Value (J)
                $groupBorderCols[] = $gIdx;
                foreach ($this->headers as $h) { $gIdx += 4; }
                $groupBorderCols[] = $gIdx;
                $gIdx++;
                $groupBorderCols[] = $gIdx + 5;
                $gIdx += 6;
                $groupBorderCols[] = $gIdx + 3;
                $gIdx += 4;
                $groupBorderCols[] = $gIdx + 2;

                foreach ($groupBorderCols as $gbCol) {
                    $gbColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($gbCol);
                    $ws->getStyle($gbColLetter . '3:' . $gbColLetter . $lastRow)->applyFromArray([
                        'borders' => [
                            'right' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                'color'       => ['rgb' => '546E7A'],
                            ],
                        ],
                    ]);
                }

                // ── 6. Zebra striping data rows (start row 5) ───────────────
                for ($r = 5; $r <= $lastRow - 2; $r++) {
                    if ($r % 2 === 0) {
                        $ws->getStyle('A' . $r . ':' . $highestCol . $r)->applyFromArray([
                            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['zebra']]],
                        ]);
                    }
                }

                // ── 7. Grand Total row styling ───────────────────────────────
                $ws->getStyle('A' . $lastRow . ':' . $highestCol . $lastRow)->applyFromArray([
                    'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $colors['gt_row']]],
                    'font'    => ['bold' => true, 'size' => 11],
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']],
                        'top'        => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '78281F']],
                        'bottom'     => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '78281F']],
                    ],
                ]);

                // ── 8. Number formats (data rows start at 5) ────────────────
                $dataStart   = 5;
                $lastDataRow = $lastRow;

                $rupiahFormat = '#,##0';
                $pctFormat    = '0.0%';
                $iptFormat    = '#,##0';

                $ws->getStyle('G' . $dataStart . ':G' . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat); // target
                $ws->getStyle('H' . $dataStart . ':H' . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat); // real
                $ws->getStyle('I' . $dataStart . ':I' . $lastDataRow)->getNumberFormat()->setFormatCode($pctFormat);    // ach%
                $ws->getStyle('J' . $dataStart . ':J' . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat); // insentif

                $vIdx = 11; // VTKP starts at col K
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

                $tvCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx);
                $ws->getStyle($tvCol . $dataStart . ':' . $tvCol . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $vIdx++;

                $ec4 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 3);
                $ec6 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 5);
                $ws->getStyle($ec4 . $dataStart . ':' . $ec4 . $lastDataRow)->getNumberFormat()->setFormatCode($pctFormat);
                $ws->getStyle($ec6 . $dataStart . ':' . $ec6 . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $vIdx += 6;

                $ipt1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 2);
                $ipt2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 3);
                $ws->getStyle($ipt1 . $dataStart . ':' . $ipt1 . $lastDataRow)->getNumberFormat()->setFormatCode($iptFormat);
                $ws->getStyle($ipt2 . $dataStart . ':' . $ipt2 . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                $vIdx += 4;

                $sfa3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + 2);
                $ws->getStyle($sfa3 . $dataStart . ':' . $sfa3 . $lastDataRow)->getNumberFormat()->setFormatCode($pctFormat);
                $vIdx += 3;

                for ($i = 0; $i < 3; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($vIdx + $i);
                    $ws->getStyle($col . $dataStart . ':' . $col . $lastDataRow)->getNumberFormat()->setFormatCode($rupiahFormat);
                }

                // ── 9. Freeze panes (below titles + header rows) ─────────────
                $ws->freezePane('G5');

                // ── 10. Data row height ──────────────────────────────────────
                for ($r = 5; $r <= $lastRow; $r++) {
                    $ws->getRowDimension($r)->setRowHeight(18);
                }

                // ── 11. Center-align data rows ───────────────────────────────
                $ws->getStyle('A5:' . $highestCol . $lastRow)->applyFromArray([
                    'alignment' => ['vertical' => 'center'],
                ]);
                $ws->getStyle('G5:' . $highestCol . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                ]);
                $ws->getStyle('A5:F' . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'left'],
                ]);

                // ── 12. Column widths ────────────────────────────────────────
                // Fixed width for identity text columns
                $ws->getColumnDimension('A')->setWidth(5);   // No
                $ws->getColumnDimension('B')->setWidth(13);  // Area
                $ws->getColumnDimension('C')->setWidth(22);  // Distributor Name
                $ws->getColumnDimension('D')->setWidth(13);  // Cabang
                $ws->getColumnDimension('E')->setWidth(12);  // Kode SE
                $ws->getColumnDimension('F')->setWidth(20);  // Nama SE

                // WrapText for Nama SE only
                $ws->getStyle('F5:F' . $lastRow)->getAlignment()->setWrapText(true);

                // Auto-size (fit content) for all numeric columns G onwards
                for ($ci = 7; $ci <= $totalCols; $ci++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
                    $ws->getColumnDimension($colLetter)->setAutoSize(true);
                }
            },
        ];
    }
}