<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductCategoriesMapTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['PRD001', 'CAT001'],
            ['PRD001', 'CAT002'],
        ];
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Category ID',
        ];
    }
}
