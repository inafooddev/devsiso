<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProdukEskaMapExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
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
                'pm.product_code_prc',
                'pmm.product_name'
            )
            ->leftJoin('distributor_implementasi_eskalink as die', 'pm.distributor_code', '=', 'die.distributor_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('product_masters as pmm', 'pm.product_code_prc', '=', 'pmm.product_id')
            ->where('md.region_code', $this->region)
            ->where('md.area_code', $this->area)
            ->where('md.distributor_code', $this->distributor);

        // Filter Produk Multi-Select (Jika ada yang dipilih)
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
            "KODE PRODUCT",
            "NAMA PRODUCT"
        ];
    }

    public function map($row): array
    {
        return [
            $row->eskalink_code_dist,
            (string) $row->product_code_dist, // Force string
            $row->product_name_dist,
            $row->product_code_prc,
            $row->product_name
        ];
    }
}
