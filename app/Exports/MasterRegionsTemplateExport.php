<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterRegionsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Kode Region',
            'Nama Region',
        ];
    }

    public function array(): array
    {
        return [
            [
                'INA',
                'INDONESIA',
            ],
            [
                'EXPT',
                'EXPORT',
            ],
        ];
    }
}
