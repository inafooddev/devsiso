<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JksMasterCustomerTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
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
            'Kabupaten',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Channel',
            'Classification',
            'Segment',
            'Pilar',
            'Pilar Q1',
            'Pilar Q2',
            'Pilar Q3',
            'Pilar Q4',
            'Target',
            'Remarks SPM',
        ];
    }
}
