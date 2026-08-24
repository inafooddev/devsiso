<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterProdukLamaTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            // Return an empty row or an example row, usually empty is fine for templates
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Produk',
            'Nama Produk',
            'Status Produk (1=Aktif, 0=Nonaktif)',
            'UOM 1',
            'UOM 2',
            'UOM 3',
            'CRT To PCS',
            'CRT To PACK',
            'PACK To PCS',
            'Price HRT',
            'Produk Line',
            'Brand',
            'Divisi',
            'Kategori',
            'Sub Brand',
            'Top Item',
            'Promo Group',
        ];
    }
}
