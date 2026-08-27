<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\ListTokoParetoTeamElite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class JksMasterCustomerImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $allowedDistributors;
    protected $isAdmin;
    public $errorLogs = [];
    public $successCount = 0;
    public $insertCount = 0;
    public $updateCount = 0;
    public $insertLogs = [];
    public $updateLogs = [];

    protected $importMethod;
    public $skipCount = 0;
    public $skipLogs = [];
    protected $processedKeys = []; // Tahap 2: Mencegah duplikasi internal
    protected $jobId;

    public function __construct($isAdmin, $allowedDistributors = [], $importMethod = 'upsert', $jobId = null)
    {
        $this->isAdmin = $isAdmin;
        $this->allowedDistributors = $allowedDistributors;
        $this->importMethod = $importMethod;
        $this->jobId = $jobId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->errorLogs[] = "File Excel kosong atau tidak valid.";
            return;
        }

        // Header Validation
        $firstRow = $rows->first()->toArray();
        $expectedHeaders = [
            'distributor_code', 'customer_code', 'uniq_kd', 'customer_name',
            'customer_address', 'kecamatan', 'desa', 'latitude', 'longitude',
            'channel', 'classification', 'segment', 'pilar',
            'pilar_q1', 'pilar_q2', 'pilar_q3', 'pilar_q4', 'target', 'remarks_spm'
        ];

        $missingHeaders = array_diff($expectedHeaders, array_keys($firstRow));
        if (count($missingHeaders) > 0) {
            $this->errorLogs[] = "Gagal Import! Format Header tidak valid. Kolom berikut hilang atau salah ketik: " . implode(', ', $missingHeaders) . ". Harap gunakan template terbaru.";
            return; // Stop processing
        }

        $rowNumber = 1; // Start from 1 because of heading row

        foreach ($rows as $row) {
            $rowNumber++;

            // Skip empty rows
            if (!isset($row['distributor_code']) && !isset($row['customer_code'])) {
                continue;
            }

            // Tahap 1: Sanitization (Trim semua value string)
            $rowArray = $row->toArray();
            foreach ($rowArray as $key => $value) {
                if (is_string($value)) {
                    $rowArray[$key] = trim($value);
                } elseif (is_numeric($value)) {
                    $rowArray[$key] = trim((string) $value);
                }
            }

            // Update row attributes after trim
            foreach ($rowArray as $key => $value) {
                $row[$key] = $value;
            }

            // Tahap 2: Cek Duplikasi Internal File
            if (isset($row['distributor_code']) && isset($row['uniq_kd'])) {
                $uniqueKey = $row['distributor_code'] . '_' . $row['uniq_kd'];
                if (in_array($uniqueKey, $this->processedKeys)) {
                    $this->errorLogs[] = "Baris {$rowNumber} - Gagal: Duplikasi data internal di dalam file Excel (Distributor: {$row['distributor_code']}, Uniq KD: {$row['uniq_kd']})";
                    continue;
                }
                $this->processedKeys[] = $uniqueKey;
            }

            try {
                DB::transaction(function () use ($row, $rowNumber, $rowArray) {
                    // Validasi Input Format Dasar
                    $validator = Validator::make($rowArray, [
                        'distributor_code' => 'required|string|max:15|exists:master_distributors,distributor_code',
                        'customer_code' => 'required|string|max:50',
                        'customer_name' => 'required|string|max:255',
                        'uniq_kd' => 'required|string|max:255',
                        'channel' => 'nullable|string|in:GT,MT,LMT,OTH',
                        'classification' => 'nullable|string|in:PARETO,NON PARETO,DUMMY BRIEF,DUMMY EVALUASI',
                        'segment' => 'nullable|string|in:STAR OUTLET,GROSIR,SEMI-GROSIR,RETAIL,PENGRAJIN,TRADER,SEASONAL/HAJATAN',
                        'pilar' => 'required|string|in:1. RWO,2. PNR,3. NGVO,4. GRO',
                        'pilar_q1' => 'nullable|string',
                        'pilar_q2' => 'nullable|string',
                        'pilar_q3' => 'nullable|string',
                        'pilar_q4' => 'nullable|string',
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
                        if ($this->importMethod === 'insert_only') {
                            $this->skipCount++;
                            $this->skipLogs[] = "Baris {$rowNumber} - Dilewati: {$distributorCode} - {$uniqKd} (Data sudah ada)";
                        } else {
                            $existing->update([
                                'customer_name' => trim($row['customer_name']),
                                'customer_address' => $row['customer_address'] ?? null,
                                'kecamatan' => $row['kecamatan'] ?? null,
                                'desa' => $row['desa'] ?? null,
                                'latitude' => $row['latitude'] ?? null,
                                'longitude' => $row['longitude'] ?? null,
                                'channel_outlet' => $row['channel'] ?? null,
                                'classification_outlet' => $row['classification'] ?? null,
                                'segment_outlet' => $row['segment'] ?? null,
                                'pilar' => $row['pilar'] ?? null,
                                'pilar_q1' => $row['pilar_q1'] ?? null,
                                'pilar_q2' => $row['pilar_q2'] ?? null,
                                'pilar_q3' => $row['pilar_q3'] ?? null,
                                'pilar_q4' => $row['pilar_q4'] ?? null,
                                'target' => isset($row['target']) ? (float) $row['target'] : 0,
                                'keterangan' => $row['remarks_spm'] ?? null,
                            ]);
                            $this->updateCount++;
                            $this->updateLogs[] = "Baris {$rowNumber} - Update: {$distributorCode} - {$uniqKd} (" . trim($row['customer_name']) . ")";
                        }
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
                            'channel_outlet' => $row['channel'] ?? null,
                            'classification_outlet' => $row['classification'] ?? null,
                            'segment_outlet' => $row['segment'] ?? null,
                            'pilar' => $row['pilar'] ?? null,
                            'pilar_q1' => $row['pilar_q1'] ?? null,
                            'pilar_q2' => $row['pilar_q2'] ?? null,
                            'pilar_q3' => $row['pilar_q3'] ?? null,
                            'pilar_q4' => $row['pilar_q4'] ?? null,
                            'target' => isset($row['target']) ? (float) $row['target'] : 0,
                            'keterangan' => $row['remarks_spm'] ?? null,
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

        // Push progress to cache at the end of this chunk
        if ($this->jobId) {
            $cacheKey = "import_progress_{$this->jobId}";
            $progress = Cache::get($cacheKey, [
                'status' => 'processing',
                'success' => 0,
                'insert' => 0,
                'update' => 0,
                'skip' => 0,
                'error' => 0,
                'logs' => [],
                'skipLogs' => []
            ]);

            $progress['success'] = $this->successCount;
            $progress['insert'] = $this->insertCount;
            $progress['update'] = $this->updateCount;
            $progress['skip'] = $this->skipCount;
            $progress['error'] = count($this->errorLogs);
            
            $progress['logs'] = $this->errorLogs;
            $progress['skipLogs'] = $this->skipLogs;

            Cache::put($cacheKey, $progress, 3600);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
