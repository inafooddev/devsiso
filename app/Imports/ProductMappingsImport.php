<?php

namespace App\Imports;

use App\Models\ProductMapping;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use App\Models\MasterDistributor; // Untuk validasi
use App\Models\ProductMaster; // Untuk validasi

class ProductMappingsImport implements OnEachRow, WithStartRow
{
    private $distributorCodes;
    private $productCodes;
    public $importedCount = 0;
    public $skippedCount = 0;

    public function __construct()
    {
        // Cache data master untuk validasi yang lebih cepat
        $this->distributorCodes = MasterDistributor::pluck('distributor_code')->flip();
        $this->productCodes = ProductMaster::pluck('product_id')->flip();
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Mulai impor dari baris kedua (baris pertama adalah header)
    }

    /**
    * @param Row $row
    */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Ambil data berdasarkan indeks yang Anda tentukan
        $distributorCode = $data[0] ?? null;
        $productCodeDist = $data[2] ?? null;
        $productNameDist = $data[3] ?? null;
        $productCodePrc = $data[4] ?? null;

        // Validasi dasar
        if (
            empty($distributorCode) || 
            empty($productCodeDist) || // product_code_dist adalah bagian dari kunci, tidak boleh kosong
            !isset($this->distributorCodes[$distributorCode]) || // Cek apakah distributor valid
            ($productCodePrc && !isset($this->productCodes[$productCodePrc])) // Cek produk prc jika diisi
        ) {
            $this->skippedCount++;
            return; // Lewati baris jika data tidak lengkap atau tidak valid
        }

        // [PERUBAHAN] Gunakan updateOrCreate untuk menghindari duplikat
        // Mencari berdasarkan distributor_code dan product_code_dist
        ProductMapping::updateOrCreate(
            [
                'distributor_code' => $distributorCode,
                'product_code_dist' => $productCodeDist,
            ],
            [
                // Data yang akan di-update atau di-insert
                'product_name_dist' => $productNameDist,
                'product_code_prc' => $productCodePrc,
            ]
        );

        $this->importedCount++;
    }
}

