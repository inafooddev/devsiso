<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TargetSeValueTemplateExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            ['2026-08', 'DIST01', 'SLS01', '15000000'],
            ['2026-08', 'DIST01', 'SLS02', '20000000'],
        ];
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Distributor Code',
            'Salesman Code',
            'Target',
        ];
    }

    public function title(): string
    {
        return 'Format Target SE Value';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
