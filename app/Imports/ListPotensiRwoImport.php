<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class ListPotensiRwoImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $errorList = [];
    public $updatedList = [];

    public function collection(Collection $rows)
    {
        // Pre-fetch valid customer codes from reward_outlet for faster validation
        $validCustomers = DB::table('reward_outlet')->pluck('customer_code')->toArray();
        // Use associative array for O(1) lookup
        $validCustomersDict = array_flip($validCustomers);

        // Pre-fetch valid distributor codes from master_distributors
        $validDistributors = DB::table('master_distributors')->pluck('distributor_code')->toArray();
        $validDistributorsDict = array_flip($validDistributors);

        foreach ($rows as $row) {
            // Mapping from Excel Header. Expected headers:
            // kuartal, distributor_code, customer_code, customer_name, alamat, total_target
            
            $kuartal = $row['kuartal'] ?? null;
            $distributorCode = $row['distributor_code'] ?? null;
            $customerCode = $row['customer_code'] ?? null;
            $customerName = $row['customer_name'] ?? null;
            $alamat = $row['alamat'] ?? null;
            $totalTarget = $row['total_target'] ?? 0;

            if (!$kuartal || !$customerCode || !$distributorCode) {
                continue; // Skip invalid rows
            }

            $rowErrors = [];

            // Validasi: Cek apakah distributor_code ada di master_distributors
            if (!isset($validDistributorsDict[$distributorCode])) {
                $rowErrors[] = "kode Distributor [{$distributorCode}] salah";
            }

            // Validasi: Cek apakah customer_code ada di reward_outlet
            if (!isset($validCustomersDict[$customerCode])) {
                $rowErrors[] = "Toko [{$customerCode}] tidak ada di master customer rwo";
            }

            if (!empty($rowErrors)) {
                $this->errorList[] = "Toko: {$customerName} - " . implode(" | ", $rowErrors);
                continue;
            }

            // Cek apakah data sudah ada di list_potensi_rwo
            $existing = DB::table('list_potensi_rwo')
                ->where('kuartal', $kuartal)
                ->where('customer_code', $customerCode)
                ->first();

            if ($existing) {
                // Update
                DB::table('list_potensi_rwo')
                    ->where('id', $existing->id)
                    ->update([
                        'distributor_code' => $distributorCode,
                        'customer_name' => $customerName,
                        'alamat' => $alamat,
                        'total_target' => $totalTarget,
                        'updated_at' => now(),
                    ]);
                
                $this->updatedList[] = "Toko: {$customerName} ({$customerCode}) - Diperbarui";
            } else {
                // Insert
                DB::table('list_potensi_rwo')->insert([
                    'kuartal' => $kuartal,
                    'distributor_code' => $distributorCode,
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'alamat' => $alamat,
                    'total_target' => $totalTarget,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->successCount++;
            }
        }
    }
}
