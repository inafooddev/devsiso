<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganSeReasonExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $rows;
    protected $columns;
    protected $totals;

    public function __construct($rows, $columns, $totals)
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->totals = $totals;
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Distributor',
            'Area',
            'Kode SE',
            'Nama SE',
        ];

        foreach ($this->columns as $col) {
            $headings[] = $col->short_desc;
        }

        $headings[] = 'Total Kasus';

        return $headings;
    }

    public function collection()
    {
        $result = collect();
        $no = 1;

        foreach ($this->rows as $row) {
            $line = [
                $no++,
                $row->distributor_name,
                $row->area_name,
                $row->salesman_code,
                $row->salesman_name,
            ];

            foreach ($this->columns as $col) {
                $line[] = $row->reasons[$col->raw_desc] ?? 0;
            }

            $line[] = $row->total_reason;

            $result->push($line);
        }

        // Total footer row
        $totalLine = [
            'TOTAL',
            '',
            '',
            '',
            count($this->rows) . ' SE',
        ];

        foreach ($this->columns as $col) {
            $totalLine[] = $this->totals[$col->raw_desc] ?? 0;
        }

        $totalLine[] = $this->totals['__grand_total__'] ?? 0;

        $result->push($totalLine);

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->rows) + 2;
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
            $lastRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ],
            ]
        ];
    }
}
