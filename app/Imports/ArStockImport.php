<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArStockImport implements ToCollection, WithHeadingRow
{
    protected $bulan;

    public function __construct($bulan)
    {
        // Pastikan input bulan misal '2026-09' diubah menjadi awal bulan '2026-09-01'
        $this->bulan = $bulan . '-01';
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $distributorCode = $row['distributor_code'] ?? null;
            
            // Skip baris jika tidak ada kode distributor
            if (empty($distributorCode)) {
                continue;
            }

            // Pencarian key yang aman karena slug dari 'AR (Late > 7)' dan 'Stock (%)' bisa bervariasi
            $rawAr = null;
            $rawStock = null;

            foreach ($row as $key => $val) {
                if (str_starts_with((string)$key, 'ar')) {
                    $rawAr = $val;
                }
                if (str_starts_with((string)$key, 'stock')) {
                    $rawStock = $val;
                }
            }

            // SMART PARSING AR
            if (is_string($rawAr)) {
                // Ubah koma menjadi titik
                $rawAr = str_replace(',', '.', $rawAr);
                // Hapus semua karakter kecuali angka dan titik
                $rawAr = preg_replace('/[^0-9.-]/', '', $rawAr);
            }
            $ar = empty($rawAr) ? 0 : (int) round((float) $rawAr);

            // SMART PARSING STOCK
            if (is_string($rawStock)) {
                // Ubah koma menjadi titik (untuk format Indonesia seperti 99,9%)
                $rawStock = str_replace(',', '.', $rawStock);
                // Hapus semua karakter kecuali angka dan titik (misal % akan terhapus)
                $rawStock = preg_replace('/[^0-9.-]/', '', $rawStock);
            }
            $stock = empty($rawStock) ? 0 : (float) $rawStock;
            
            // Excel mengirim angka persentase sebagai desimal (0.665). Kita kalikan 100 agar jadi 66.5
            $stock = $stock * 100;

            // Upsert (Update or Insert)
            DB::table('reward_dist_ar_stock')->updateOrInsert(
                [
                    'distributor_code' => $distributorCode,
                    'bulan' => $this->bulan,
                ],
                [
                    'ar' => $ar,
                    'stock' => $stock,
                    // Karena DB query builder, created_at hanya dimasukkan saat insert
                    // updateOrInsert tidak otomatis menangani timestamps, jadi tidak masalah kita paksa update di sini atau biarkan null (jika db mengizinkan)
                    // Lebih aman tidak memasukkan timestamps karena skemanya mungkin tidak mengharuskan.
                ]
            );
        }
    }
}
