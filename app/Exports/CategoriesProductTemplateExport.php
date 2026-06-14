<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CategoriesProductTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['CAT001', 'Kategori Contoh A'],
            ['CAT002', 'Kategori Contoh B'],
        ];
    }

    public function headings(): array
    {
        return [
            'Category ID',
            'Category Name',
        ];
    }
}
