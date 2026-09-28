<?php

namespace App\Exports\Report\FundamentalSales;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class DashboardSheetExport implements FromArray, WithTitle, WithEvents, WithStrictNullComparison
{
    protected $supervisor;
    protected $year;
    protected $dashboardData;
    protected $kpis;

    public function __construct($supervisor, $year, $dashboardData, $kpis)
    {
        $this->supervisor = $supervisor;
        $this->year = $year;
        $this->dashboardData = $dashboardData;
        $this->kpis = $kpis;
    }

    public function array(): array
    {
        $data = [];

        // Row 1
        $data[] = ["LAPORAN PENCAPAIAN KPI SUPERVISOR (TAHUN {$this->year})"];
        // Row 2
        $cabang = $this->supervisor->branches_string ?: '-';
        $data[] = ["Nama Supervisor: " . strtoupper($this->supervisor->name) . " ({$this->supervisor->code}) | Region/Area: {$this->supervisor->region_name} / {$this->supervisor->area_name} | Cabang: {$cabang}"];
        // Row 3 (prevent row shift by placing empty string)
        $data[] = [''];
        
        // Row 4: Months
        $row4 = ['No', 'Indikator (KPI & PI)', 'Bobot'];
        for ($m = 1; $m <= 12; $m++) {
            $row4[] = date('M', mktime(0, 0, 0, $m, 10));
            $row4[] = '';
            $row4[] = '';
            $row4[] = '';
        }
        $data[] = $row4;

        // Row 5: Sub-headers
        $row5 = ['', '', ''];
        for ($m = 1; $m <= 12; $m++) {
            $row5[] = 'Target';
            $row5[] = 'Actual';
            $row5[] = '% Ach';
            $row5[] = 'Score';
        }
        $data[] = $row5;

        // Row 6+: KPI Data
        $totalScores = array_fill(1, 12, 0); // To accumulate total score per month
        $no = 1;

        foreach ($this->kpis as $key => $kpi) {
            $row = [
                $no++,
                $kpi['label'],
                $kpi['bobot'] / 100 // raw float for percentage
            ];

            for ($m = 1; $m <= 12; $m++) {
                $cell = $this->dashboardData['months'][$m][$key];
                $row[] = (float)$cell['target'];
                $row[] = (float)$cell['actual'];
                $row[] = (float)$cell['ach']; // Raw float (e.g., 0.14 for 14%)
                
                $scoreRaw = (float)$cell['score'] / 100; // raw percent for score
                $row[] = $scoreRaw;
                
                $totalScores[$m] += $scoreRaw;
            }
            $data[] = $row;
        }

        // Add TOTAL SCORE row
        $totalRow = ['', 'TOTAL SCORE', ''];
        for ($m = 1; $m <= 12; $m++) {
            $totalRow[] = '';
            $totalRow[] = '';
            $totalRow[] = '';
            $totalRow[] = $totalScores[$m];
        }
        $data[] = $totalRow;

        return $data;
    }

    public function title(): string
    {
        return 'Dashboard Matriks';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Merge title rows (Now up to AX because we added 1 column)
                $sheet->mergeCells('A1:AX1');
                $sheet->mergeCells('A2:AX2');
                
                // Title styles
                $sheet->getStyle('A1:AX2')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getFont()->setSize(14);
                $sheet->getStyle('A1:AX2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Merge headers (No, Indikator, Bobot)
                $sheet->mergeCells('A4:A5');
                $sheet->mergeCells('B4:B5');
                $sheet->mergeCells('C4:C5');
                
                // Merge months
                $colIndex = 4; // D (since A=No, B=Indikator, C=Bobot)
                for ($m = 1; $m <= 12; $m++) {
                    $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3);
                    $sheet->mergeCells("{$startCol}4:{$endCol}4");
                    $colIndex += 4;
                }

                // Header styles (Main Header)
                $sheet->getStyle('A4:AX4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A4:AX4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1F4E78'); // Dark Blue
                $sheet->getStyle('A4:AX4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4:AX4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Sub Header styles
                $sheet->getStyle('D5:AX5')->getFont()->setBold(true)->getColor()->setARGB('FF000000');
                $sheet->getStyle('D5:AX5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD9E1F2'); // Light Blue
                $sheet->getStyle('D5:AX5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Align left for KPI Label
                $sheet->getStyle('B6:B12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Align center for No and Bobot
                $sheet->getStyle('A6:A12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C6:C12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Formatting numbers and conditional highlights
                $sheet->getStyle('C6:C12')->getNumberFormat()->setFormatCode('0%');

                $colIndex = 4; // Start from D
                for ($m = 1; $m <= 12; $m++) {
                    $cTarget = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $cActual = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $cAch = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2);
                    $cScore = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3);

                    // Set formats (Including Total Score row which is row 13)
                    // Format code uses literal "0" for the zero value
                    $sheet->getStyle("{$cTarget}6:{$cTarget}13")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"0"');
                    $sheet->getStyle("{$cActual}6:{$cActual}13")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"0"');
                    $sheet->getStyle("{$cAch}6:{$cAch}13")->getNumberFormat()->setFormatCode('0%;-0%;"0%"');
                    $sheet->getStyle("{$cScore}6:{$cScore}13")->getNumberFormat()->setFormatCode('0%;-0%;"0%"'); // Score format as percent

                    // Center align data
                    $sheet->getStyle("{$cTarget}6:{$cScore}13")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Custom Conditional Background for % Ach
                    for ($r = 6; $r <= 12; $r++) {
                        $achVal = $sheet->getCell("{$cAch}{$r}")->getValue();
                        
                        if (is_numeric($achVal)) {
                            if ($achVal >= 1) { // 100%
                                $sheet->getStyle("{$cAch}{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FFC6EFCE'); // Light Green
                                $sheet->getStyle("{$cAch}{$r}")->getFont()->getColor()->setARGB('FF006100'); // Dark Green
                            } elseif ($achVal > 0) {
                                $sheet->getStyle("{$cAch}{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FFFFC7CE'); // Light Red
                                $sheet->getStyle("{$cAch}{$r}")->getFont()->getColor()->setARGB('FF9C0006'); // Dark Red
                            }
                        }
                    }
                    
                    $colIndex += 4;
                }

                // Styling for TOTAL SCORE row (Row 13)
                $sheet->getStyle('A13:AX13')->getFont()->setBold(true);
                $sheet->getStyle('A13:AX13')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFF00'); // Yellow background
                $sheet->mergeCells('B13:C13'); // Merge 'TOTAL SCORE' text over Indikator and Bobot

                // Borders for table data (A4 to AX13)
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A4:AX13')->applyFromArray($styleArray);

                // Column width adjustments
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(10);

                // Auto size for all data columns (D to AX)
                for ($col = 'D'; $col !== 'AY'; $col++) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
