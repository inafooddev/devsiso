<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;

class TargetKacabTemplateExport implements FromArray
{
    use Exportable;

    public function array(): array
    {
        return [
            ['Tahun', 'Cabang', 'Nama Kacab', 'Target', 'Insentif'],
            ['2025', 'JAKARTA', 'BUDI', 500000000, 1000000],
            ['2025', 'BANDUNG', 'ANDI', 450000000, 900000],
        ];
    }
}
