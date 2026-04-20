<?php

namespace App\Exports;

use App\Models\ProductMapping;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductMappingsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = ProductMapping::query()
            ->join('master_distributors', 'product_mappings.distributor_code', '=', 'master_distributors.distributor_code')
            // [PERUBAHAN] Tambahkan LEFT JOIN ke product_masters
            ->leftJoin('product_masters', 'product_mappings.product_code_prc', '=', 'product_masters.product_id');

        // Terapkan filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('master_distributors.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('master_distributors.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('product_mappings.distributor_code', $this->filters['distributorFilter']);
        }
        if (!empty($this->filters['search'])) {
            // Gunakan ILIKE untuk PostgreSQL
            $query->where(function ($q) {
                $q->where('product_mappings.product_code_dist', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('product_mappings.product_name_dist', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('product_mappings.product_code_prc', 'ILIKE', '%' . $this->filters['search'] . '%')
                    // [PERUBAHAN] Tambahkan pencarian berdasarkan nama produk principal
                    ->orWhere('product_masters.product_name', 'ILIKE', '%' . $this->filters['search'] . '%');
            });
        }

        // [PERUBAHAN] Perbarui SELECT untuk mengambil kolom yang diperlukan
        return $query->select(
            'product_mappings.distributor_code',
            'master_distributors.distributor_name',
            'product_mappings.product_code_dist',
            'product_mappings.product_name_dist',
            'product_mappings.product_code_prc',
            'product_masters.product_name as product_name_prc'
        )
            ->latest('product_mappings.created_at');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // [PERUBAHAN] Hapus ID dan Tanggal Dibuat, tambahkan Nama Produk Prc
        return [
            'Kode Distributor',
            'Nama Distributor',
            'Kode Produk Distributor',
            'Nama Produk Distributor',
            'Kode Produk Principal',
            'Nama Produk Principal',
        ];
    }

    private function escapeExcelFormula($value)
    {
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            return "'" . $value;
        }
        return $value;
    }

    /**
     * @param ProductMapping $mapping
     * @return array
     */
    public function map($mapping): array
    {
        return [
            $this->escapeExcelFormula($mapping->distributor_code),
            $this->escapeExcelFormula($mapping->distributor_name),
            $this->escapeExcelFormula($mapping->product_code_dist),
            $this->escapeExcelFormula($mapping->product_name_dist),
            $this->escapeExcelFormula($mapping->product_code_prc),
            $this->escapeExcelFormula($mapping->product_name_prc),
        ];
    }
}
