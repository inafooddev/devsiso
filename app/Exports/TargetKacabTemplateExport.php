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
            ['Bulan', 'Cabang', 'Target'],
            ['2025-01', 'JAKARTA', 500000000],
            ['2025-01', 'BANDUNG', 450000000],
        ];
    }
}
