<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TargetSpvValueTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Sample row
            [
                '2026-08',
                'REGION_1',
                'AREA_1',
                'CBG001',
                'FEST_A',
                '15000000',
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Region',
            'Area',
            'Cabang',
            'Reg Fest',
            'Target',
        ];
    }
}
