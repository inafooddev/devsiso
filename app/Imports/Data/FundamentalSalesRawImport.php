<?php

namespace App\Imports\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class FundamentalSalesRawImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check if required fields are present
            if (!isset($row['bulan']) || !isset($row['cabang'])) {
                continue;
            }

            // Ensure bulan is a valid date format. It might come as a timestamp or string from Excel.
            $bulan = null;
            if (is_numeric($row['bulan'])) {
                $bulan = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['bulan'])->format('Y-m-d');
            } else {
                $bulan = date('Y-m-d', strtotime($row['bulan']));
            }

            $cabang = trim($row['cabang']);
            $year = date('Y', strtotime($bulan));
            $monthNum = date('m', strtotime($bulan));

            // Default to 0 if empty
            $data = [
                'target' => (float)($row['target_so'] ?? 0),
                'selling_out' => (float)($row['selling_out'] ?? 0),
                'jumlah_se' => (int)($row['jumlah_se'] ?? 0),
                'pc' => (int)($row['pc'] ?? 0),
                'ac' => (int)($row['ac'] ?? 0),
                'ec' => (int)($row['ec'] ?? 0),
                'ro' => (int)($row['ro'] ?? 0),
                'ao' => (int)($row['ao'] ?? 0),
                'sku' => (int)($row['total_sku'] ?? 0),
                'nota' => (int)($row['total_nota'] ?? 0),
                'potensi_rwo' => (int)($row['potensi_rwo'] ?? 0),
                'capai_rwo' => (int)($row['capai_rwo'] ?? 0),
                'updated_at' => now()
            ];

            // Upsert based on bulan and cabang
            $exists = DB::table('ipm_fundamental_sales')
                ->whereYear('bulan', $year)
                ->whereMonth('bulan', $monthNum)
                ->where('cabang', $cabang)
                ->first();

            if ($exists) {
                DB::table('ipm_fundamental_sales')
                    ->where('id', $exists->id)
                    ->update($data);
            } else {
                $data['bulan'] = $year . '-' . $monthNum . '-01';
                $data['cabang'] = $cabang;
                $data['created_at'] = now();
                DB::table('ipm_fundamental_sales')->insert($data);
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
