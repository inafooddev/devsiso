<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;

class TargetSpvVtkpTemplateExport implements FromArray
{
    use Exportable;

    public function array(): array
    {
        return [
            ['Bulan', 'Cabang', 'Produk Grup', 'Target'],
            ['2025-01', 'JAKARTA', 'VTKP A', 15000000],
            ['2025-01', 'BANDUNG', 'VTKP B', 10000000],
        ];
    }
}
