<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesmansTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Kode Salesman (Wajib)',
            'Kode Distributor (Wajib)',
            'Nama Salesman (Wajib)',
            'Status Aktif (1/0)',
            'Tipe (Principal/Distributor)',
            'Join Date (YYYY-MM-DD)',
            'Nama Bank',
            'A.N. Rekening',
            'No. Rekening'
        ];
    }

    public function array(): array
    {
        return [];
    }
}
