<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProdukEskaExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    use Exportable;

    protected $region;
    protected $area;
    protected $distributor;
    protected $products;

    public function __construct($region, $area, $distributor, $products = [])
    {
        $this->region = $region;
        $this->area = $area;
        $this->distributor = $distributor;
        $this->products = $products;
    }

    public function title(): string
    {
        return 'DATA 01';
    }

    public function query()
    {
        $query = DB::table('product_mappings as pm')
            ->select(
                'die.eskalink_code_dist',
                'pm.product_code_dist',
                'pm.product_name_dist',
                'pmm.uom1',
                'pmm.uom2',
                'pmm.uom3',
                'pmm.conv_unit3',
                'pmm.conv_unit2',
                'pmm.price_zone1',
                'pmm.conv_unit1'
            )
            ->leftJoin('distributor_implementasi_eskalink as die', 'pm.distributor_code', '=', 'die.distributor_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('product_masters as pmm', 'pm.product_code_prc', '=', 'pmm.product_id')
            ->where('md.region_code', $this->region)
            ->where('md.area_code', $this->area)
            ->where('md.distributor_code', $this->distributor);

        // Filter Produk (Multi-Select) khusus Export
        if (!empty($this->products)) {
            $query->whereIn('pm.product_code_dist', $this->products);
        }

        return $query->orderBy('pm.product_code_dist');
    }

    public function headings(): array
    {
        return [
            "KODE DISTRIBUTOR",
            "KODE PRODUCT DISTRIBUTOR",
            "NAMA PRODUCT DISTRIBUTOR",
            "UOM 1",
            "UOM 2",
            "UOM 3",
            "UOM 4",
            "UOM 5",
            "CONVUNIT 2",
            "CONVUNIT 3",
            "CONVUNIT 4",
            "CONVUNIT 5",
            "SELLPRICE 1",
            "SELLPRICE 2",
            "SELLPRICE 3",
            "SELLPRICE 4",
            "SELLPRICE 5"
        ];
    }

    public function map($row): array
    {
        // Kalkulasi Harga (Handling division by zero)
        $sellPrice1 = ($row->price_zone1 > 0) ? ($row->price_zone1 / 1.11) : 0;
        $sellPrice2 = ($sellPrice1 > 0 && $row->conv_unit2 > 0) ? ($sellPrice1 / $row->conv_unit2) : 0;
        $sellPrice3 = ($sellPrice1 > 0 && $row->conv_unit1 > 0) ? ($sellPrice1 / $row->conv_unit1) : 0;

        return [
            $row->eskalink_code_dist,
            (string) $row->product_code_dist, // Force string agar 0 di depan tidak hilang
            $row->product_name_dist,
            $row->uom1,
            $row->uom2,
            $row->uom3,
            '', // UOM 4
            '', // UOM 5
            $row->conv_unit3, // CONVUNIT 2
            $row->conv_unit2, // CONVUNIT 3
            1,  // CONVUNIT 4
            '', // CONVUNIT 5
            $sellPrice1,
            $sellPrice2,
            $sellPrice3,
            '', // SELLPRICE 4
            '', // SELLPRICE 5
        ];
    }
}
