<?php

namespace App\Imports;

use App\Models\TargetSeValue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class TargetSeValueImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    protected $errors = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        $bulanRaw = $row['bulan'] ?? null;
        $distributorCode = $row['distributor_code'] ?? null;
        $salesmanCode = $row['salesman_code'] ?? null;
        $target = $row['target'] ?? 0;

        // Validasi format
        if (empty($bulanRaw) || empty($distributorCode) || empty($salesmanCode)) {
            $this->errors[] = "Baris dilewati karena ada kolom wajib (Bulan, Distributor Code, Salesman Code) yang kosong.";
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

        // Upsert data
        TargetSeValue::updateOrCreate(
            [
                'bulan' => $bulan,
                'distributor_code' => $distributorCode,
                'salesman_code' => $salesmanCode,
            ],
            [
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
