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
        // Skip index 15 (drawing placeholder Foto KTP)
        $namaBank = $data[16] ?? null;
        $noRekening = $data[17] ?? null;
        $namaPemilikNorek = $data[18] ?? null;

        // If sheet is simple (18 columns or less, without photo drawings), shift fields
        if (count($data) <= 18) {
            $namaBank = $data[15] ?? null;
            $noRekening = $data[16] ?? null;
            $namaPemilikNorek = $data[17] ?? null;
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

        RewardOutlet::updateOrCreate(
            [
                'customer_code' => $customerCode,
            ],
            [
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
            ]
        );

        $this->importedCount++;
    }
}
