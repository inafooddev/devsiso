<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductBrandsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['BRD001', 'Brand Contoh A'],
            ['BRD002', 'Brand Contoh B'],
        ];
    }

    public function headings(): array
    {
        return [
            'Brand ID',
            'Brand Name',
        ];
    }
}
