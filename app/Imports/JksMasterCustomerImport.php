<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\ListTokoParetoTeamElite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JksMasterCustomerImport implements ToCollection, WithHeadingRow
{
    protected $allowedDistributors;
    protected $isAdmin;
    public $errorLogs = [];
    public $successCount = 0;
    public $insertCount = 0;
    public $updateCount = 0;
    public $insertLogs = [];
    public $updateLogs = [];

    public function __construct($isAdmin, $allowedDistributors = [])
    {
        $this->isAdmin = $isAdmin;
        $this->allowedDistributors = $allowedDistributors;
    }

    public function collection(Collection $rows)
    {
        $rowNumber = 1; // Start from 1 because of heading row


        foreach ($rows as $row) {
            $rowNumber++;

            // Skip empty rows
            if (!isset($row['distributor_code']) && !isset($row['customer_code'])) {
                continue;
            }

            try {
                DB::transaction(function () use ($row, $rowNumber) {
                    // Validasi Input Format Dasar
                    $validator = Validator::make($row->toArray(), [
                        'distributor_code' => 'required|string|max:15|exists:master_distributors,distributor_code',
                        'customer_code' => 'required|string|max:50',
                        'customer_name' => 'required|string|max:255',
                        'uniq_kd' => 'required|string|max:255',
                        'pilar' => 'required|string|in:1. RWO,2. PNR,3. NGVO,4. GRO',
                        'latitude' => 'nullable|numeric|between:-90,90',
                        'longitude' => 'nullable|numeric|between:-180,180',
                        'target' => 'nullable|numeric',
                    ], [
                        'distributor_code.required' => 'Distributor Code wajib diisi',
                        'distributor_code.exists' => 'Distributor Code tidak ditemukan di tabel Master Distributors',
                        'customer_code.required' => 'Customer Code wajib diisi',
                        'customer_name.required' => 'Nama Customer wajib diisi',
                        'uniq_kd.required' => 'Uniq KD wajib diisi',
                        'pilar.required' => 'Pilar wajib diisi',
                        'pilar.in' => 'Format Pilar salah (harus: 1. RWO, 2. PNR, 3. NGVO, 4. GRO)',
                    ]);

                    if ($validator->fails()) {
                        $errorMsg = implode(', ', $validator->errors()->all());
                        throw new \Exception("Gagal validasi: {$errorMsg}");
                    }

                    $distributorCode = trim($row['distributor_code']);
                    $customerCode = trim($row['customer_code']);
                    $uniqKd = trim($row['uniq_kd']);

                    // Security Check: Hierarchy Access
                    if (!$this->isAdmin && !in_array($distributorCode, $this->allowedDistributors)) {
                        throw new \Exception("Akses ditolak: Anda tidak memiliki otoritas untuk Distributor Code '{$distributorCode}'.");
                    }

                    // Lolos validasi, proses Upsert
                    $existing = ListTokoParetoTeamElite::where('distributor_code', $distributorCode)
                        ->where('uniq_kd', $uniqKd)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'customer_name' => trim($row['customer_name']),
                            'customer_address' => $row['customer_address'] ?? null,
                            'kecamatan' => $row['kecamatan'] ?? null,
                            'desa' => $row['desa'] ?? null,
                            'latitude' => $row['latitude'] ?? null,
                            'longitude' => $row['longitude'] ?? null,
                            'pilar' => $row['pilar'] ?? null,
                            'target' => isset($row['target']) ? (float) $row['target'] : 0,
                            'keterangan' => $row['keterangan'] ?? null,
                        ]);
                        $this->updateCount++;
                        $this->updateLogs[] = "Baris {$rowNumber} - Update: {$distributorCode} - {$uniqKd} (" . trim($row['customer_name']) . ")";
                    } else {
                        ListTokoParetoTeamElite::create([
                            'distributor_code' => $distributorCode,
                            'uniq_kd' => $uniqKd,
                            'customer_code_prc' => $customerCode,
                            'customer_name' => trim($row['customer_name']),
                            'customer_address' => $row['customer_address'] ?? null,
                            'kecamatan' => $row['kecamatan'] ?? null,
                            'desa' => $row['desa'] ?? null,
                            'latitude' => $row['latitude'] ?? null,
                            'longitude' => $row['longitude'] ?? null,
                            'pilar' => $row['pilar'] ?? null,
                            'target' => isset($row['target']) ? (float) $row['target'] : 0,
                            'keterangan' => $row['keterangan'] ?? null,
                        ]);
                        $this->insertCount++;
                        $this->insertLogs[] = "Baris {$rowNumber} - Insert: {$distributorCode} - {$uniqKd} (" . trim($row['customer_name']) . ")";
                    }

                    $this->successCount++;
                });
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                // If it's a database exception, try to get a cleaner message if possible, or just log the original
                if ($e instanceof \Illuminate\Database\QueryException) {
                    $errorMsg = "Kesalahan Database: " . $e->errorInfo[2] ?? $e->getMessage();
                }
                $this->errorLogs[] = "Baris {$rowNumber} - {$errorMsg}";
            }
        }
    }
}
