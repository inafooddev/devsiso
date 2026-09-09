<?php

namespace App\Imports;

use App\Models\JksImportTemp;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class JksSalesmanImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    protected $batchId;
    protected $rowNumber = 1; // Start from row 2 (after header)

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        // Only process rows that have actual data (checking customer_code as proxy)
        $custCode = trim($row['customer_code'] ?? '');
        
        // Skip empty rows or the instruction row from the template
        if (empty($custCode) || $custCode === '(Wajib) Kode Toko') {
            return null;
        }

        return new JksImportTemp([
            'batch_id' => $this->batchId,
            'row_number' => $this->rowNumber,
            'bulan' => $this->parseDate($row['bulan'] ?? null),
            'distributor_code' => $row['distributor_code'] ?? null,
            'salesman_code' => $row['salesman_code'] ?? null,
            'customer_code' => $row['customer_code'] ?? null,
            
            // Map days
            'h1' => $this->formatYT($row['senin'] ?? null),
            'h2' => $this->formatYT($row['selasa'] ?? null),
            'h3' => $this->formatYT($row['rabu'] ?? null),
            'h4' => $this->formatYT($row['kamis'] ?? null),
            'h5' => $this->formatYT($row['jumat'] ?? null),
            'h6' => $this->formatYT($row['sabtu'] ?? null),
            'h7' => $this->formatYT($row['minggu'] ?? null),
            
            // Map weeks
            'w1' => $this->formatYT($row['minggu_1'] ?? null),
            'w2' => $this->formatYT($row['minggu_2'] ?? null),
            'w3' => $this->formatYT($row['minggu_3'] ?? null),
            'w4' => $this->formatYT($row['minggu_4'] ?? null),
            
            'status' => 'pending',
        ]);
    }

    private function formatYT($value)
    {
        if (empty($value)) return 'T';
        $val = strtoupper(trim($value));
        return $val === 'Y' ? 'Y' : 'T';
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Jika Excel mengirimkan serial date (contoh: 46266)
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-01');
            }

            // Jika format string (2026-01-01, 01/01/2026, dll)
            // Carbon bisa menebak dengan sangat pintar
            return \Carbon\Carbon::parse($value)->format('Y-m-01');
        } catch (\Exception $e) {
            // Jika gagal diparse, kembalikan value aslinya agar tertangkap oleh validator strict di ImportModal
            return $value;
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
