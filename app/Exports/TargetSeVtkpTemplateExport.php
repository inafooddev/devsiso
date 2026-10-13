<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TargetSeVtkpTemplateExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            // Sample row to guide user
            [
                '2026-08',
                'KDA',
                'S001',
                'PRD01',
                '10000000',
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Distributor Code',
            'Salesman Code',
            'Produk Grup',
            'Target',
        ];
    }

    public function title(): string
    {
        return 'Format Target SE VTKP';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
