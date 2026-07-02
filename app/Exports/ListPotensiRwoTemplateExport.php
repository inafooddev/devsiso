<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ListPotensiRwoTemplateExport implements WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'kuartal',
            'distributor_code',
            'customer_code',
            'customer_name',
            'alamat',
            'total_target',
        ];
    }
}
