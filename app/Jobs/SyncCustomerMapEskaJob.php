<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ImportBatch;

class SyncCustomerMapEskaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    protected $batchId;

    public function __construct($batchId = null)
    {
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
                $batch->update(['status' => 'processing']);
                $batch->addLog('info', "Memulai penarikan data Customer Map Eska (Mode Aman: Download First)...");
            }
        }

        try {
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ=='; 

            if ($batch) {
                $batch->addLog('info', "Mengunduh data Customer Map Eska dari API...");
            }
            Log::info("SyncCustomerMapEskaJob: Mengunduh data dari API...");

            // 1. Download data terlebih dahulu (dengan retry 2x jika ada gangguan jaringan sementara)
            try {
                $response = Http::withToken($token)
                    ->timeout(300)
                    ->retry(2, 2000)
                    ->get("https://jobs.asiatop.co.id:9080/trx/export?block=CUSTMAP");
            } catch (\Throwable $httpError) {
                $errorMsg = "Gagal menghubungi API Customer Map Eska: " . $httpError->getMessage() . ". Data lama TIDAK DIHAPUS (Tetap Aman).";
                if ($batch) {
                    $batch->addLog('error', $errorMsg);
                    $batch->update(['status' => 'failed']);
                }
                Log::error("SyncCustomerMapEskaJob: " . $errorMsg);
                return;
            }

            // 2. Validasi response API
            if (!$response->successful()) {
                $errorMsg = "API mengembalikan status HTTP {$response->status()}. Data lama TIDAK DIHAPUS (Tetap Aman).";
                if ($batch) {
                    $batch->addLog('error', $errorMsg);
                    $batch->update(['status' => 'failed']);
                }
                Log::error("SyncCustomerMapEskaJob: " . $errorMsg);
                return;
            }

            $responseData = $response->json();
            $data = $responseData['data'] ?? [];

            if (!is_array($data) || count($data) === 0) {
                $warningMsg = "Data Customer Map dari API kosong. Data lama TIDAK DIHAPUS (Tetap Aman).";
                if ($batch) {
                    $batch->addLog('warning', $warningMsg);
                    $batch->update(['status' => 'completed']);
                }
                Log::warning("SyncCustomerMapEskaJob: " . $warningMsg);
                return;
            }

            // 3. Siapkan payload data baru
            $insertData = [];
            $now = Carbon::now();

            foreach ($data as $row) {
                $insertData[] = [
                    'distid'       => $row['DISTID'] ?? null,
                    'branch_dist'  => $row['BRANCH_DIST'] ?? null,
                    'custno_dist'  => $row['CUSTNO_DIST'] ?? null,
                    'branch'       => $row['BRANCH'] ?? null,
                    'custno'       => $row['CUSTNO'] ?? null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            // 4. EKSEKUSI PENGGANTIAN DATA DALAM DATABASE TRANSACTION ATOMIK
            // Hapus data lama HANYA jika data baru sudah sukses di-download dan siap di-insert
            DB::transaction(function () use ($insertData, $batch) {
                if ($batch) {
                    $batch->addLog('warning', "Download sukses (" . count($insertData) . " data)! Mengosongkan data lama di customer_map_eska...");
                }
                
                DB::table('customer_map_eska')->truncate();

                $chunks = array_chunk($insertData, 500);
                foreach ($chunks as $chunk) {
                    DB::table('customer_map_eska')->insert($chunk);
                }
            });

            $totalInserted = count($insertData);
            if ($batch) {
                $batch->addLog('success', "Berhasil menyimpan {$totalInserted} data Customer Map Eska.");
                $batch->addLog('success', "Proses Selesai. Total {$totalInserted} data berhasil diperbarui.");
                $batch->update(['status' => 'completed']);
            }
            Log::info("SyncCustomerMapEskaJob: Berhasil menyimpan {$totalInserted} data Customer Map Eska.");

        } catch (\Throwable $e) {
            if ($batch) {
                $batch->addLog('error', "Terjadi kesalahan sistem: " . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            Log::error("SyncCustomerMapEskaJob Error: " . $e->getMessage());
            throw $e;
        }
    }
}
