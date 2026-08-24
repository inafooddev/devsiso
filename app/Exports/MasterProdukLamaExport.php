<?php

namespace App\Exports;

use App\Models\MasterProdukLama;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterProdukLamaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
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

    public function map($row): array
    {
        return [
            $row->pcode_prc,
            $row->nama_produk,
            $row->status_product,
            $row->uom1,
            $row->uom2,
            $row->uom3,
            $row->crttopcs,
            $row->crttopack,
            $row->packtopcs,
            $row->pricehrt,
            $row->produk_line,
            $row->brand,
            $row->divisi,
            $row->kategory,
            $row->subbrand,
            $row->topitem,
            $row->promo_group,
        ];
    }
}
