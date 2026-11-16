<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\ImportBatch;

class SyncCustomerEskaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $region;
    public $batchId;
    public $timeout = 3600; // 1 hour timeout for all regions

    /**
     * Create a new job instance.
     */
    public function __construct($region = 'all', $batchId = null)
    {
        $this->region = $region ?: 'all';
        $this->batchId = $batchId;
    }

    /**
     * Execute the job safely with Download First -> Atomic Transaction Replacement.
     */
    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', "Memulai penarikan data Customer Eska (Mode Aman: Download First)...");
            }
        }

        try {
            $regionsToProcess = [];
            if ($this->region === 'all') {
                $regionsToProcess = [
                    'CSTINAJWA1',
                    'CSTINAJWA2',
                    'CSTINAPUL1',
                    'CSTINASUM1',
                    'CSTINASUM2',
                ];
            } else {
                $regionsToProcess = [$this->region];
            }

            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ=='; 

            $totalDataInserted = 0;
            $failedRegions = [];
            $successRegions = [];

            foreach ($regionsToProcess as $r) {
                if ($batch) {
                    $batch->addLog('info', "Mengunduh data dari API untuk region: {$r}...");
                }
                Log::info("SyncCustomerEskaJob: Mengunduh data dari API untuk region: {$r}");

                // 1. Download data terlebih dahulu (dengan retry 2x jika ada gangguan jaringan sementara)
                try {
                    $response = Http::withToken($token)
                        ->timeout(300)
                        ->retry(2, 2000)
                        ->get("https://jobs.asiatop.co.id:9080/trx/export?block={$r}");
                } catch (\Throwable $httpError) {
                    $errorMsg = "Gagal menghubungi API untuk region {$r}: " . $httpError->getMessage();
                    if ($batch) {
                        $batch->addLog('error', "{$errorMsg}. Data lama region ini TIDAK DIHAPUS (Tetap Aman).");
                    }
                    Log::error("SyncCustomerEskaJob: " . $errorMsg);
                    $failedRegions[] = $r;
                    continue;
                }

                // 2. Validasi response API
                if (!$response->successful()) {
                    $errorMsg = "API mengembalikan status {$response->status()} untuk region {$r}. Data lama region ini TIDAK DIHAPUS (Tetap Aman).";
                    if ($batch) {
                        $batch->addLog('error', $errorMsg);
                    }
                    Log::error("SyncCustomerEskaJob: " . $errorMsg);
                    $failedRegions[] = $r;
                    continue;
                }

                $responseData = $response->json();
                $data = $responseData['data'] ?? [];

                if (!is_array($data) || count($data) === 0) {
                    $warningMsg = "Data dari API kosong untuk region {$r}. Data lama region ini TIDAK DIHAPUS (Tetap Aman).";
                    if ($batch) {
                        $batch->addLog('warning', $warningMsg);
                    }
                    Log::warning("SyncCustomerEskaJob: " . $warningMsg);
                    continue;
                }

                // 3. Tentukan region_code untuk penghapusan terarah
                $regionCodeFromApi = $data[0]['REGION_CODE'] ?? null;
                if (!$regionCodeFromApi) {
                    $regionCodeFromApi = str_starts_with($r, 'CST') ? substr($r, 3) : $r;
                }

                // 4. Siapkan payload data baru
                $insertData = [];
                $now = Carbon::now();

                foreach ($data as $row) {
                    $insertData[] = [
                        'region_code' => $row['REGION_CODE'] ?? $regionCodeFromApi,
                        'area_code'   => $row['ENTITY_CODE'] ?? null,
                        'kodecabang'  => $row['BRANCH_CODE'] ?? null,
                        'custno'      => $row['CUSTOMER_CODE'] ?? null,
                        'custname'    => $row['CUSTOMER_NAME'] ?? null,
                        'custadd1'    => $row['ADDRESS_1'] ?? null,
                        'ccity'       => $row['CCITY'] ?? null,
                        'typeout'     => $row['TYPE_OUTLET'] ?? null,
                        'grupout'     => $row['GROUP_OUTLET'] ?? null,
                        'gharga'      => $row['GROUP_HARGA'] ?? null,
                        'la'          => isset($row['LA']) && $row['LA'] !== '' ? (float) $row['LA'] : null,
                        'lg'          => isset($row['LG']) && $row['LG'] !== '' ? (float) $row['LG'] : null,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }

                // 5. EKSEKUSI PENGGANTIAN DATA DALAM DATABASE TRANSACTION ATOMIK
                // Hapus data lama HANYA jika data baru sudah sukses di-download dan siap di-insert
                DB::transaction(function () use ($regionCodeFromApi, $insertData, $batch, $r) {
                    if ($regionCodeFromApi) {
                        if ($batch) {
                            $batch->addLog('warning', "Download sukses! Menghapus data lama region {$regionCodeFromApi}...");
                        }
                        DB::table('customer_prc_eska')->where('region_code', $regionCodeFromApi)->delete();
                    }

                    $chunks = array_chunk($insertData, 500);
                    foreach ($chunks as $chunk) {
                        DB::table('customer_prc_eska')->insert($chunk);
                    }
                });

                $countInserted = count($insertData);
                $totalDataInserted += $countInserted;
                $successRegions[] = $r;

                if ($batch) {
                    $batch->addLog('success', "Sukses memperbarui region {$r} ({$regionCodeFromApi}): {$countInserted} data tersimpan.");
                }
                Log::info("SyncCustomerEskaJob: Sukses memperbarui region {$r} ({$regionCodeFromApi}): {$countInserted} data tersimpan.");
            }

            // 6. Ringkasan Status Akhir
            if (!empty($failedRegions)) {
                $statusSummary = "Perhatian: Terdapat region yang gagal diunduh: " . implode(', ', $failedRegions) . ". Data lama region tersebut tetap dipertahankan aman.";
                if ($batch) {
                    $batch->addLog('warning', $statusSummary);
                }
                Log::warning("SyncCustomerEskaJob: " . $statusSummary);
            }

            if ($batch) {
                if (empty($successRegions) && !empty($failedRegions)) {
                    $batch->addLog('error', "Gagal total: Tidak ada region yang berhasil diunduh.");
                    $batch->update(['status' => 'failed']);
                } else {
                    $batch->addLog('success', "Proses Selesai. Total {$totalDataInserted} data berhasil diperbarui.");
                    $batch->update(['status' => 'completed']);
                }
            }

        } catch (\Throwable $e) {
            Log::error("SyncCustomerEskaJob Terjadi Exception: " . $e->getMessage());
            if ($batch) {
                $batch->addLog('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            throw $e;
        }
    }
}
