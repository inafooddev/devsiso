<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon; // [DITAMBAHKAN] Import Carbon

class UnmappedProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // Logika query ini identik dengan di komponen Livewire
        $productMappingsSub = DB::table('product_mappings')
            ->select('distributor_code', 'product_code_dist', DB::raw('MIN(product_code_prc) as product_code_prc'))
            ->groupBy('distributor_code', 'product_code_dist');

        $query = DB::table('sales_invoice_distributor as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code')
            ->leftJoinSub($productMappingsSub, 'c', function ($join) {
                $join->on('a.distributor_code', '=', 'c.distributor_code')
                    ->on('a.product_code', '=', 'c.product_code_dist');
            })
            ->leftJoin('product_masters as d', 'c.product_code_prc', '=', 'd.product_id')
            ->select(
                'b.distributor_name',
                'a.distributor_code',
                'a.product_code',
                'a.product_name'
            )
            ->whereNull('d.product_id')
            ->groupBy(
                'a.distributor_code',
                'b.distributor_name',
                'a.product_code',
                'a.product_name'
            );

        if (!empty($this->filters['regionFilter'])) {
            $query->where('b.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('b.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('a.distributor_code', $this->filters['distributorFilter']);
        }

        // [PERUBAHAN] Menggunakan range tanggal agar bisa diindeks
        if (!empty($this->filters['monthFilter']) && !empty($this->filters['yearFilter'])) {
            $startDate = Carbon::create($this->filters['yearFilter'], $this->filters['monthFilter'], 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            $query->whereBetween('a.invoice_date', [$startDate, $endDate]);
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('a.product_code', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('a.product_name', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('b.distributor_name', 'ILIKE', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->orderBy('b.distributor_name')->orderBy('a.product_name');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Distributor',
            'Nama Distributor',
            'Kode Produk Dist',
            'Nama Produk Dist',
            'Kode Produk Prc',
            'Nama Produk Prc',

        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->distributor_code,
            $row->distributor_name,
            $row->product_code,
            $row->product_name,
        ];
    }
}
