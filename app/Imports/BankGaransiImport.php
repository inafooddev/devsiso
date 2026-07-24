<?php

namespace App\Imports;

use App\Models\BankGaransi;
use App\Models\MasterDistributor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Exception;

class BankGaransiImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $errorCount = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +1 for 0-index, +1 for heading

            // Skip empty rows
            if (!isset($row['kode_distributor']) || empty($row['kode_distributor'])) {
                continue;
            }

            $distributorCode = trim($row['kode_distributor']);
            
            // Check if distributor exists
            $distributor = MasterDistributor::where('distributor_code', $distributorCode)->first();
            if (!$distributor) {
                $this->errorCount++;
                $this->errors[] = "Baris {$rowNum}: Distributor kode '{$distributorCode}' tidak ditemukan.";
                continue;
            }

            try {
                // Parse dates
                $tgl_terbit = $this->parseDate($row['tanggal_terbit']);
                $tgl_jatuh_tempo = $this->parseDate($row['tanggal_jatuh_tempo']);
                
                if (!$tgl_terbit || !$tgl_jatuh_tempo) {
                    $this->errorCount++;
                    $this->errors[] = "Baris {$rowNum}: Format tanggal tidak valid (gunakan YYYY-MM-DD atau format Excel standard).";
                    continue;
                }

                $status_perpanjangan = isset($row['status_perpanjangan']) ? ucfirst(strtolower(trim($row['status_perpanjangan']))) : 'Tidak';
                if (!in_array($status_perpanjangan, ['Ya', 'Tidak'])) {
                    $status_perpanjangan = 'Tidak';
                }

                // Create or update based on distributor code and nomor jaminan
                BankGaransi::updateOrCreate(
                    [
                        'distributor_code' => $distributorCode,
                        'nomor_jaminan' => trim($row['nomor_jaminan'] ?? ''),
                    ],
                    [
                        'nama_bank'        => trim($row['nama_bank'] ?? 'Lainnya'),
                        'nomor_seri'       => isset($row['nomor_seri']) ? trim($row['nomor_seri']) : null,
                        'nilai_jaminan'    => floatval($row['nilai_jaminan'] ?? 0),
                        'tanggal_terbit'   => $tgl_terbit,
                        'tanggal_jatuh_tempo' => $tgl_jatuh_tempo,
                        'status_perpanjangan' => $status_perpanjangan,
                        'keterangan'       => isset($row['keterangan']) ? trim($row['keterangan']) : null,
                    ]
                );

                $this->successCount++;

            } catch (Exception $e) {
                $this->errorCount++;
                $this->errors[] = "Baris {$rowNum}: Gagal memproses data - " . $e->getMessage();
            }
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;
        
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
}
