<?php

namespace App\Imports;

use App\Models\MasterSupervisor;
use App\Models\MasterArea;
use App\Models\MasterRegion;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class MasterSupervisorsImport implements OnEachRow, WithStartRow
{
    private $areaCodes;
    private $regionCodes;
    public $importedCount = 0;
    public $skippedCount = 0;
    public $logs = [];
    private $currentRow = 1;

    public function __construct()
    {
        // Cache region and area codes for fast validation
        $this->regionCodes = MasterRegion::pluck('region_code')->flip();
        $this->areaCodes = MasterArea::pluck('area_code')->flip();
    }

    public function startRow(): int
    {
        return 2; // Lewati baris header
    }

    public function onRow(Row $row)
    {
        $this->currentRow++;
        $data = $row->toArray();

        $supervisorCode = trim($data[0] ?? '');
        $supervisorName = trim($data[1] ?? '');
        $description    = trim($data[2] ?? '');
        $areaCode       = trim($data[3] ?? '');
        $regionCode     = trim($data[4] ?? '');

        // Validasi data wajib ada
        if (empty($supervisorCode) || empty($supervisorName) || empty($areaCode) || empty($regionCode)) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kode Supervisor, Nama Supervisor, Kode Area, atau Kode Region ada yang kosong.";
            return;
        }

        // Validasi regionCode valid di Master Region
        if (!isset($this->regionCodes[$regionCode])) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kode Region '{$regionCode}' tidak ditemukan di Master Region.";
            return;
        }

        // Validasi areaCode valid di Master Area
        if (!isset($this->areaCodes[$areaCode])) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kode Area '{$areaCode}' tidak ditemukan di Master Area.";
            return;
        }

        try {
            MasterSupervisor::updateOrCreate(
                [
                    'supervisor_code' => $supervisorCode,
                ],
                [
                    'supervisor_name' => $supervisorName,
                    'description'     => $description,
                    'area_code'       => $areaCode,
                ]
            );
            $this->importedCount++;
            $this->logs[] = "Baris {$this->currentRow}: SUKSES - Supervisor '{$supervisorCode}' tersimpan.";
        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kesalahan sistem: " . $e->getMessage();
        }
    }
}
