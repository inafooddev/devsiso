<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\MasterProdukLama;
use App\Models\ProductMaster;

class MasterProdukLamaImport implements ToCollection, WithHeadingRow
{
    public $errorLogs = [];
    public $successCount = 0;

    public function collection(Collection $rows)
    {
        $pcodePrs = $rows->pluck('kode_produk')->filter()->toArray();
        if (empty($pcodePrs)) {
            return;
        }

        $validProductMasters = ProductMaster::whereIn('product_id', $pcodePrs)->pluck('product_id')->toArray();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 for 0-index, +1 for heading row
            
            $pcodePrc = trim($row['kode_produk'] ?? '');
            
            if (empty($pcodePrc)) {
                continue; // Skip empty rows
            }

            if (!in_array($pcodePrc, $validProductMasters)) {
                $this->errorLogs[] = "Baris {$rowNumber}: Kode Produk '{$pcodePrc}' tidak ditemukan di Product Master utama.";
                continue;
            }

            // Convert empty strings to null for numeric fields to prevent pgsql errors
            $status = isset($row['status_produk_1aktif_0nonaktif']) ? trim($row['status_produk_1aktif_0nonaktif']) : '1';
            if ($status === '') $status = '1';

            $crtToPcs = (isset($row['crt_to_pcs']) && trim($row['crt_to_pcs']) !== '') ? floatval(trim($row['crt_to_pcs'])) : null;
            $crtToPack = (isset($row['crt_to_pack']) && trim($row['crt_to_pack']) !== '') ? floatval(trim($row['crt_to_pack'])) : null;
            $packToPcs = (isset($row['pack_to_pcs']) && trim($row['pack_to_pcs']) !== '') ? floatval(trim($row['pack_to_pcs'])) : null;
            $priceHrt = (isset($row['price_hrt']) && trim($row['price_hrt']) !== '') ? floatval(trim($row['price_hrt'])) : null;

            MasterProdukLama::updateOrCreate(
                ['pcode_prc' => $pcodePrc],
                [
                    'nama_produk' => trim($row['nama_produk'] ?? '') ?: null,
                    'status_product' => $status,
                    'uom1' => trim($row['uom_1'] ?? '') ?: null,
                    'uom2' => trim($row['uom_2'] ?? '') ?: null,
                    'uom3' => trim($row['uom_3'] ?? '') ?: null,
                    'crttopcs' => $crtToPcs,
                    'crttopack' => $crtToPack,
                    'packtopcs' => $packToPcs,
                    'pricehrt' => $priceHrt,
                    'produk_line' => trim($row['produk_line'] ?? '') ?: null,
                    'brand' => trim($row['brand'] ?? '') ?: null,
                    'divisi' => trim($row['divisi'] ?? '') ?: null,
                    'kategory' => trim($row['kategori'] ?? '') ?: null,
                    'subbrand' => trim($row['sub_brand'] ?? '') ?: null,
                    'topitem' => trim($row['top_item'] ?? '') ?: null,
                    'promo_group' => trim($row['promo_group'] ?? '') ?: null,
                ]
            );

            $this->successCount++;
        }
    }
}
