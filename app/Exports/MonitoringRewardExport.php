<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class MonitoringRewardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $data;
    protected $year;

    public function __construct(array $data, $year)
    {
        $this->data = $data;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            ['MONITORING REWARD DISTRIBUTOR - TAHUN ' . $this->year],
            [
                'Cabang',
                'Distributor',
                'Region',
                'Area',
                // P1
                'Target P1',
                'Sell In P1',
                'Sell Out P1',
                'Stock P1 (%)',
                'AR > 7 Hari P1',
                'Status P1',
                'Reward P1',
                // P2
                'Target P2',
                'Sell In P2',
                'Sell Out P2',
                'Stock P2 (%)',
                'AR > 7 Hari P2',
                'Status P2',
                'Reward P2',
                // Total
                'Total Reward',
            ]
        ];
    }

    public function map($row): array
    {
        $p1 = $row['p1'];
        $p2 = $row['p2'];

        $statusP1 = ($p1['is_target_achieved'] && $p1['is_stock_achieved'] && $p1['is_ar_achieved']) ? 'LULUS' : 'GAGAL';
        $statusP2 = ($p2['is_target_achieved'] && $p2['is_stock_achieved'] && $p2['is_ar_achieved']) ? 'LULUS' : 'GAGAL';

        return [
            $row['cabang'],
            $row['distributor'] ?? '-',
            $row['region'] ?? '-',
            $row['area'] ?? '-',
            // P1
            $p1['target'],
            $p1['sell_in'],
            $p1['sell_out'],
            round((float) $p1['stock_avg'], 2),
            $p1['ar_late_count'],
            $statusP1,
            $p1['final_reward'],
            // P2
            $p2['target'],
            $p2['sell_in'],
            $p2['sell_out'],
            round((float) $p2['stock_avg'], 2),
            $p2['ar_late_count'],
            $statusP2,
            $p2['final_reward'],
            // Total
            $row['total_reward'],
        ];
    }

    public function columnFormats(): array
    {
        $accounting = '#,##0'; // Accounting format integer
        $percentage = '0.00"%"'; // Percentage style

        return [
            'E' => $accounting,
            'F' => $accounting,
            'G' => $accounting,
            'H' => $percentage,
            'K' => $accounting,
            'L' => $accounting,
            'M' => $accounting,
            'N' => $accounting,
            'O' => $percentage,
            'R' => $accounting,
            'S' => $accounting,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge Title
        $sheet->mergeCells('A1:S1');

        return [
            // Title Style
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => Color::COLOR_WHITE]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F2937'] // Dark Gray
                ]
            ],
            // Headers Style
            2 => [
                'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Colors for Header
                $blueFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'] // Blue-600 for P1
                ];
                $greenFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF059669'] // Green-600 for P2
                ];
                $orangeFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD97706'] // Amber-600 for Total
                ];
                $grayFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4B5563'] // Gray-600 for Info
                ];

                $sheet->getStyle('A2:D2')->applyFromArray(['fill' => $grayFill]);
                $sheet->getStyle('E2:K2')->applyFromArray(['fill' => $blueFill]);
                $sheet->getStyle('L2:R2')->applyFromArray(['fill' => $greenFill]);
                $sheet->getStyle('S2')->applyFromArray(['fill' => $orangeFill]);

                // Border for all data
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCCCCCC'],
                        ],
                    ],
                ];
                $sheet->getStyle('A2:S' . $highestRow)->applyFromArray($styleArray);

                // Add Filter
                $sheet->setAutoFilter('A2:S2');

                // Freeze Header (Freeze first 4 columns and first 2 rows)
                $sheet->freezePane('E3');

                // Conditional Formatting for Status (LULUS = Green, GAGAL = Red)
                $lulusCondition = new Conditional();
                $lulusCondition->setConditionType(Conditional::CONDITION_CELLIS)
                               ->setOperatorType(Conditional::OPERATOR_EQUAL)
                               ->addCondition('"LULUS"')
                               ->getStyle()->getFont()->getColor()->setARGB('FF059669'); // Green Text
                $lulusCondition->getStyle()->getFont()->setBold(true);

                $gagalCondition = new Conditional();
                $gagalCondition->setConditionType(Conditional::CONDITION_CELLIS)
                               ->setOperatorType(Conditional::OPERATOR_EQUAL)
                               ->addCondition('"GAGAL"')
                               ->getStyle()->getFont()->getColor()->setARGB('FFDC2626'); // Red Text
                $gagalCondition->getStyle()->getFont()->setBold(true);

                $conditionalStyles = [$lulusCondition, $gagalCondition];
                
                $sheet->getStyle('J3:J' . $highestRow)->setConditionalStyles($conditionalStyles); // P1 Status
                $sheet->getStyle('Q3:Q' . $highestRow)->setConditionalStyles($conditionalStyles); // P2 Status
            }
        ];
    }
}
