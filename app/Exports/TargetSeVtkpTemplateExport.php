<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TargetSeVtkpTemplateExport implements FromArray, WithHeadings
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
}
