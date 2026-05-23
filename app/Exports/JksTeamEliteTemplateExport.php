<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JksTeamEliteTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Return empty array for rows
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Team',
            'Nama Team',
            'Distributor Code',
            'Distributor Name',
            'CustNo',
            'CustName',
            'Address'
        ];
    }
}
