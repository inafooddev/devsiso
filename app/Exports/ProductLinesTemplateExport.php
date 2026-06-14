<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductLinesTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['LIN001', 'Line Contoh A'],
            ['LIN002', 'Line Contoh B'],
        ];
    }

    public function headings(): array
    {
        return [
            'Line ID',
            'Line Name',
        ];
    }
}
