<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterBranchesTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Kode Cabang',
            'Nama Cabang',
            'Kode Supervisor',
        ];
    }

    public function array(): array
    {
        return [
            [
                'CAB-01',
                'Cabang Jakarta',
                'SPV-01',
            ]
        ];
    }
}
