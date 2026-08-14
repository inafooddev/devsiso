<?php

namespace App\Imports;

use App\Models\TargetPerDepo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class TargetSpvValueImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    protected $errors = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        $bulanRaw = $row['bulan'] ?? null;
        $region = $row['region'] ?? null;
        $area = $row['area'] ?? null;
        $cabang = $row['cabang'] ?? null;
        $regFest = $row['reg_fest'] ?? null;
        $target = $row['target'] ?? 0;

        // Validasi format: Bulan dan Cabang adalah kunci utama
        if (empty($bulanRaw) || empty($cabang)) {
            $this->errors[] = "Baris dilewati karena ada kolom wajib (Bulan, Cabang) yang kosong.";
            return null;
        }

        // Parsing bulan agar lebih fleksibel
        try {
            if (is_numeric($bulanRaw)) {
                $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($bulanRaw);
                $bulan = \Carbon\Carbon::instance($parsedDate)->format('Y-m');
            } else {
                $bulan = \Carbon\Carbon::parse($bulanRaw)->format('Y-m');
            }
        } catch (\Exception $e) {
            $this->errors[] = "Baris dilewati karena format Bulan ($bulanRaw) tidak dapat dipahami sistem.";
            return null;
        }

        // Upsert data berdasarkan kombinasi bulan dan cabang
        TargetPerDepo::updateOrCreate(
            [
                'bulan' => $bulan,
                'cabang' => $cabang,
            ],
            [
                'region' => $region,
                'area' => $area,
                'reg_fest' => $regFest,
                'target' => $target,
            ]
        );

        $this->successCount++;
        return null;
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
