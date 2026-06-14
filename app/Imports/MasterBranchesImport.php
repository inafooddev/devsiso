<?php

namespace App\Imports;

use App\Models\MasterBranch;
use App\Models\MasterSupervisor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class MasterBranchesImport implements OnEachRow, WithStartRow
{
    private $supervisorCodes;
    public $importedCount = 0;
    public $skippedCount = 0;
    public $logs = [];
    private $currentRow = 1;

    public function __construct()
    {
        // Cache supervisor codes for fast validation
        $this->supervisorCodes = MasterSupervisor::pluck('supervisor_code')->flip();
    }

    public function startRow(): int
    {
        return 2;
    }

    public function onRow(Row $row)
    {
        $this->currentRow++;
        $data = $row->toArray();

        $branchCode     = trim($data[0] ?? '');
        $branchName     = trim($data[1] ?? '');
        $supervisorCode = trim($data[2] ?? '');

        // Validasi data wajib ada
        if (empty($branchCode) || empty($branchName) || empty($supervisorCode)) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kode Cabang, Nama Cabang, atau Kode Supervisor ada yang kosong.";
            return;
        }

        // Validasi supervisorCode
        if (!isset($this->supervisorCodes[$supervisorCode])) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kode Supervisor '{$supervisorCode}' tidak ditemukan di Master Supervisor.";
            return;
        }

        try {
            MasterBranch::updateOrCreate(
                [
                    'branch_code' => $branchCode,
                ],
                [
                    'branch_name'     => $branchName,
                    'supervisor_code' => $supervisorCode,
                ]
            );
            $this->importedCount++;
            $this->logs[] = "Baris {$this->currentRow}: SUKSES - Cabang '{$branchCode}' tersimpan.";
        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kesalahan sistem: " . $e->getMessage();
        }
    }
}
