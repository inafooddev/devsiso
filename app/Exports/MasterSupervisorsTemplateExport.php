<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterSupervisorsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Kode Supervisor',
            'Nama Supervisor',
            'Keterangan',
            'Kode Area',
            'Kode Region'
        ];
    }

    public function array(): array
    {
        return [
            [
                'SPV-01',
                'Budi Santoso',
                'Supervisor Lapangan',
                'AREA-01',
                'REG-01'
            ]
        ];
    }
}
