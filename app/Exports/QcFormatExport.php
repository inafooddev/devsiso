<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QcFormatExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['DIST001', 100, 50000.50, 15000, 2000000, 2000000],
            ['DIST002', 50, 0, 0, 1500000, 1500000],
        ];
    }

    public function headings(): array
    {
        return [
            'kode_dist',
            'qty',
            'disc_4',
            'disc_8',
            'nett',
            'nominal_surat'
        ];
    }
}
