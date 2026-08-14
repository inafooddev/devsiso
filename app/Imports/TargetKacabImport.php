<?php

namespace App\Imports;

use App\Models\TargetKacab;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TargetKacabImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $errors = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        // Skip jika baris kosong
        if (!array_filter($row)) {
            return null;
        }

        $rawTahun = $row['tahun'] ?? null;
        $cabang = $row['cabang'] ?? null;
        $namaKacab = $row['nama_kacab'] ?? null;
        $target = $row['target'] ?? 0;
        $insentif = $row['insentif'] ?? 0;

        if (empty($rawTahun) || empty($cabang)) {
            $this->errors[] = "Tahun dan Cabang harus diisi. Baris diabaikan: " . json_encode($row);
            return null;
        }

        // Parsing tahun fleksibel
        $parsedTahun = null;
        try {
            if (is_numeric($rawTahun)) {
                // If it's just a 4 digit year
                if (strlen(trim($rawTahun)) == 4) {
                    $parsedTahun = trim($rawTahun);
                } else {
                    $dateObj = Date::excelToDateTimeObject($rawTahun);
                    $parsedTahun = $dateObj->format('Y');
                }
            } else {
                $parsedTahun = Carbon::parse($rawTahun)->format('Y');
            }
        } catch (\Exception $e) {
            $this->errors[] = "Format tahun tidak valid ('$rawTahun') untuk Cabang '$cabang'. Baris diabaikan.";
            return null;
        }

        // Bersihkan data
        $targetVal = preg_replace('/[^0-9\.\-]/', '', (string)$target);
        $insentifVal = preg_replace('/[^0-9\.\-]/', '', (string)$insentif);

        // Upsert berdasar Tahun + Cabang
        TargetKacab::updateOrCreate(
            [
                'tahun' => $parsedTahun,
                'cabang' => strtoupper(trim($cabang)),
            ],
            [
                'nama_kacab' => $namaKacab,
                'target' => (float) $targetVal,
                'insentif' => (float) $insentifVal,
            ]
        );

        $this->successCount++;
        return null; // karena kita pakai updateOrCreate
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
