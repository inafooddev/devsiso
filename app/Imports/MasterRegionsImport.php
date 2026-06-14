<?php

namespace App\Imports;

use App\Models\MasterRegion;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class MasterRegionsImport implements OnEachRow, WithStartRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $logs = [];
    private $currentRow = 1;

    public function startRow(): int
    {
        return 2;
    }

    public function onRow(Row $row)
    {
        $this->currentRow++;
        $data = $row->toArray();

        $regionCode = $data[0] ?? null;
        $regionName = $data[1] ?? null;

        if (empty($regionCode) || empty($regionName)) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kolom Kode Region atau Nama Region ada yang kosong.";
            return;
        }

        try {
            MasterRegion::updateOrCreate(
                ['region_code' => $regionCode],
                [
                    'region_name' => $regionName,
                ]
            );
            
            $this->importedCount++;
            $this->logs[] = "Baris {$this->currentRow}: SUKSES - Region {$regionCode} ({$regionName}) berhasil diimpor.";
        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
