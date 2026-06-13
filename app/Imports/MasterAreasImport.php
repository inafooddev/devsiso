<?php

namespace App\Imports;

use App\Models\MasterArea;
use App\Models\MasterRegion;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class MasterAreasImport implements OnEachRow, WithStartRow
{
    private $regionCodes;
    public $importedCount = 0;
    public $skippedCount = 0;
    public $logs = []; // Array untuk menyimpan log tiap baris
    private $currentRow = 1; // Mulai dari 1 (header), baris data mulai dari 2

    public function __construct()
    {
        // Cache region codes for fast validation
        $this->regionCodes = MasterRegion::pluck('region_code')->flip();
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Lewati baris header
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $this->currentRow++;
        $data = $row->toArray();

        $areaCode   = $data[0] ?? null;
        $areaName   = $data[1] ?? null;
        $regionCode = $data[2] ?? null;

        // Validasi data wajib ada
        if (empty($areaCode) || empty($areaName) || empty($regionCode)) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kolom Kode Area, Nama Area, atau Kode Region ada yang kosong.";
            return;
        }

        // Validasi regionCode valid di Master Region
        if (!isset($this->regionCodes[$regionCode])) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kode Region '{$regionCode}' tidak ditemukan di Master Region.";
            return;
        }

        try {
            MasterArea::updateOrCreate(
                [
                    'area_code' => $areaCode,
                ],
                [
                    'area_name'   => $areaName,
                    'region_code' => $regionCode,
                ]
            );
            $this->importedCount++;
            $this->logs[] = "Baris {$this->currentRow}: SUKSES - Area '{$areaCode}' tersimpan.";
        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kesalahan sistem: " . $e->getMessage();
        }
    }
}
