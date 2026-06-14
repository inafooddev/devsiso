<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductMastersTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['PRD001', 'Produk Contoh A', 'LIN001', 'BRD001', 'GRP001', '', '1', 'PCS', 'BOX', '', '', '10', '', '', '10000', '11000', '12000', '13000', '14000', 'CAT01,CAT02'],
        ];
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Line ID',
            'Brand ID',
            'Group ID',
            'Sub-Brand ID',
            'Is Active (1/0)',
            'Base Unit',
            'UOM 1',
            'UOM 2',
            'UOM 3',
            'Conv 1',
            'Conv 2',
            'Conv 3',
            'Price Zone 1',
            'Price Zone 2',
            'Price Zone 3',
            'Price Zone 4',
            'Price Zone 5',
            'Category IDs (Comma Separated)',
        ];
    }
}
