<?php

namespace App\Imports;

use App\Models\TargetSpvVtkp;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TargetSpvVtkpImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $errors = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        // Skip jika baris benar-benar kosong
        if (!array_filter($row)) {
            return null;
        }

        $rawBulan = $row['bulan'] ?? null;
        $cabang = $row['cabang'] ?? null;
        $produkGrup = $row['produk_grup'] ?? null;
        $target = $row['target'] ?? 0;

        if (empty($rawBulan) || empty($cabang) || empty($produkGrup)) {
            $this->errors[] = "Bulan, Cabang, dan Produk Grup harus diisi. Baris diabaikan: " . json_encode($row);
            return null;
        }

        // Parsing tanggal fleksibel
        $parsedBulan = null;
        try {
            if (is_numeric($rawBulan)) {
                $dateObj = Date::excelToDateTimeObject($rawBulan);
                $parsedBulan = $dateObj->format('Y-m');
            } else {
                $parsedBulan = Carbon::parse($rawBulan)->format('Y-m');
            }
        } catch (\Exception $e) {
            $this->errors[] = "Format bulan tidak valid ('$rawBulan') untuk Cabang '$cabang'. Baris diabaikan.";
            return null;
        }

        // Bersihkan data
        $targetVal = preg_replace('/[^0-9\.\-]/', '', $target);

        // Upsert berdasar Bulan + Cabang + Produk Grup
        TargetSpvVtkp::updateOrCreate(
            [
                'bulan' => $parsedBulan,
                'cabang' => strtoupper(trim($cabang)),
                'produk_grup' => strtoupper(trim($produkGrup)),
            ],
            [
                'target' => (float) $targetVal,
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
