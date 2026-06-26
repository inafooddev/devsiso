<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JksMasterCustomerTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        // Mengembalikan array kosong agar hanya ter-generate header saja
        return [];
    }

    public function headings(): array
    {
        return [
            'Distributor Code',
            'Customer Code',
            'Uniq Kd',
            'Customer Name',
            'Customer Address',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Pilar',
            'Pilar Q1',
            'Pilar Q2',
            'Pilar Q3',
            'Pilar Q4',
            'Target',
            'Keterangan',
        ];
    }
}
