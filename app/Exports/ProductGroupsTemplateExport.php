<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductGroupsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['GRP001', 'Group Contoh A'],
            ['GRP002', 'Group Contoh B'],
        ];
    }

    public function headings(): array
    {
        return [
            'Group ID',
            'Group Name (Brand Unit)',
        ];
    }
}
