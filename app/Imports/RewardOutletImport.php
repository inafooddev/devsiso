<?php

namespace App\Imports;

use App\Models\RewardOutlet;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class RewardOutletImport implements OnEachRow, WithStartRow
{
    public $importedCount = 0;
    public $skippedCount = 0;

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Skip header row
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $regionCode = $data[0] ?? null;
        $regionName = $data[1] ?? null;
        $areaCode = $data[2] ?? null;
        $areaName = $data[3] ?? null;
        $branchName = $data[4] ?? null;
        $eskalinkCode = $data[5] ?? null;
        $customerCode = $data[6] ?? null;
        $customerName = $data[7] ?? null;
        $alamat = $data[8] ?? null;
        $noHp = $data[9] ?? null;
        $latitude = $data[10] ?? null;
        $longitude = $data[11] ?? null;
        $namaPemilikToko = $data[12] ?? null;
        $namaKtp = $data[13] ?? null;
        $nikKtp = $data[14] ?? null;
        // By default, assume export format (with drawings, total 24 columns)
        $namaBank = $data[16] ?? null;
        $noRekening = $data[17] ?? null;
        $namaPemilikNorek = $data[18] ?? null;
        $keterangan = $data[22] ?? null;
        $isValidStr = $data[23] ?? null;

        // If sheet is simple (20 columns or less, without photo drawings), shift fields
        if (count($data) <= 20) {
            $namaBank = $data[15] ?? null;
            $noRekening = $data[16] ?? null;
            $namaPemilikNorek = $data[17] ?? null;
            $keterangan = $data[18] ?? null;
            $isValidStr = $data[19] ?? null;
        }

        // Clean single quotes that were prepended for Excel formatting
        $noHp = $noHp !== null ? ltrim($noHp, "'") : null;
        $latitude = $latitude !== null ? ltrim($latitude, "'") : null;
        $longitude = $longitude !== null ? ltrim($longitude, "'") : null;
        $nikKtp = $nikKtp !== null ? ltrim($nikKtp, "'") : null;
        $noRekening = $noRekening !== null ? ltrim($noRekening, "'") : null;

        // Basic validation
        if (empty($customerCode) || empty($customerName)) {
            $this->skippedCount++;
            return;
        }

        $existing = RewardOutlet::where('customer_code', $customerCode)->first();

        $updatePayload = [
            'region_code' => $regionCode,
            'region_name' => $regionName,
            'area_code' => $areaCode,
            'area_name' => $areaName,
            'branch_name' => $branchName,
            'eskalink_code' => $eskalinkCode,
            'customer_name' => $customerName,
            'alamat' => $alamat,
            'no_hp' => $noHp,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'nama_pemilik_toko' => $namaPemilikToko,
            'nama_ktp' => $namaKtp,
            'nik_ktp' => $nikKtp,
            'nama_bank' => $namaBank,
            'no_rekening' => $noRekening,
            'nama_pemilik_norek' => $namaPemilikNorek,
        ];

        // Parse Keterangan if column exists (Column W / index 22 in export format, index 18 in simple format)
        $keteranganIndex = count($data) <= 20 ? 18 : 22;
        if (array_key_exists($keteranganIndex, $data)) {
            if ($existing && !empty(trim($existing->keterangan))) {
                // Jangan timpa keterangan yang sudah ada di database
                $updatePayload['keterangan'] = $existing->keterangan;
            } else {
                $updatePayload['keterangan'] = $keterangan;
            }
        }

        // Parse Validasi if column exists (Column X / index 23 in export format, index 19 in simple format)
        $isValidIndex = count($data) <= 20 ? 19 : 23;
        if (array_key_exists($isValidIndex, $data)) {
            if ($existing && $existing->is_valid) {
                // Jangan timpa validasi jika sudah valid (true)
                $updatePayload['is_valid'] = true;
            } else {
                $isValidStrParsed = $isValidStr !== null ? strtolower(trim($isValidStr)) : '';
                $updatePayload['is_valid'] = in_array($isValidStrParsed, ['valid (toko ada)', '1', 'true', 'valid']);
            }
        }

        if ($existing) {
            $existing->update($updatePayload);
        } else {
            $updatePayload['customer_code'] = $customerCode;
            RewardOutlet::create($updatePayload);
        }

        $this->importedCount++;
    }
}
