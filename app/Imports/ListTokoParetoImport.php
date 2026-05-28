<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use App\Models\ListTokoParetoTeamElite;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ListTokoParetoImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public $duplicates = [];
    public $processedKeys = []; // Melacak key yang sudah diproses di batch ini agar tidak ada duplikat dalam file itu sendiri

    public function collection(Collection $rows)
    {
        $custCodes = [];
        $distCodes = [];
        
        // Kumpulkan semua kode untuk di-query sekaligus (efisiensi)
        foreach ($rows as $row) {
            if (!empty($row['customer_code_prc'])) $custCodes[] = trim($row['customer_code_prc']);
            if (!empty($row['distributor_code'])) $distCodes[] = trim($row['distributor_code']);
        }

        if (empty($custCodes) || empty($distCodes)) {
            return;
        }

        // Cek data yang sudah ada di database
        $existing = ListTokoParetoTeamElite::whereIn('customer_code_prc', $custCodes)
            ->whereIn('distributor_code', $distCodes)
            ->get(['customer_code_prc', 'distributor_code'])
            ->map(function($item) {
                return $item->customer_code_prc . '|||' . $item->distributor_code;
            })->toArray();

        $inserts = [];
        $now = now();

        foreach ($rows as $row) {
            $custCode = isset($row['customer_code_prc']) ? trim($row['customer_code_prc']) : '';
            $distCode = isset($row['distributor_code']) ? trim($row['distributor_code']) : '';
            $name     = isset($row['customer_name']) ? trim($row['customer_name']) : '';

            if (empty($custCode) || empty($distCode)) {
                continue; // Skip data kosong
            }

            $key = $custCode . '|||' . $distCode;

            // Jika sudah ada di DB atau sudah diproses di baris Excel sebelumnya, catat sebagai duplikat
            if (in_array($key, $existing) || in_array($key, $this->processedKeys)) {
                $this->duplicates[] = "Distributor: {$distCode} | Kode Customer: {$custCode} | Nama: {$name}";
                continue;
            }

            // Data Cleaning & Casting untuk Lat/Lng
            $lat = null;
            if (isset($row['latitude']) && trim($row['latitude']) !== '') {
                $latStr = str_replace(',', '.', trim($row['latitude']));
                if (is_numeric($latStr)) {
                    $latFloat = (float) $latStr;
                    if ($latFloat >= -90 && $latFloat <= 90) $lat = $latFloat;
                }
            }

            $lng = null;
            if (isset($row['longitude']) && trim($row['longitude']) !== '') {
                $lngStr = str_replace(',', '.', trim($row['longitude']));
                if (is_numeric($lngStr)) {
                    $lngFloat = (float) $lngStr;
                    if ($lngFloat >= -180 && $lngFloat <= 180) $lng = $lngFloat;
                }
            }

            $inserts[] = [
                'customer_code_prc' => $custCode,
                'distributor_code'  => $distCode,
                'customer_name'     => $name,
                'uniq_kd'           => isset($row['uniq_kd']) ? trim($row['uniq_kd']) : null,
                'customer_address'  => isset($row['customer_address']) ? trim($row['customer_address']) : null,
                'kecamatan'         => isset($row['kecamatan']) ? trim($row['kecamatan']) : null,
                'desa'              => isset($row['desa']) ? trim($row['desa']) : null,
                'latitude'          => $lat,
                'longitude'         => $lng,
                'pilar'             => isset($row['pilar']) ? trim($row['pilar']) : null,
                'target'            => isset($row['target']) ? (float) str_replace(',', '.', trim($row['target'])) : 0,
                'keterangan'        => isset($row['keterangan']) ? trim($row['keterangan']) : null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            // Tambahkan ke processed keys agar tidak ganda saat looping
            $this->processedKeys[] = $key;
        }

        // Bulk insert hanya untuk data yang bersih dan belum ada di DB
        if (count($inserts) > 0) {
            ListTokoParetoTeamElite::insert($inserts);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}