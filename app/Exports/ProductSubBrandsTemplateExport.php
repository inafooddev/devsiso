<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductSubBrandsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['SBRD001', 'Sub-Brand Contoh A'],
            ['SBRD002', 'Sub-Brand Contoh B'],
        ];
    }

    public function headings(): array
    {
        return [
            'Sub-Brand ID',
            'Sub-Brand Name',
        ];
    }
}
