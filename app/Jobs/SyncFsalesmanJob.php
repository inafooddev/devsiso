<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\ImportBatch;

class SyncFsalesmanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
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
                $this->logMessage($batch, 'info', "Memulai proses Sync Fsalesman (Mode Aman: Download First)...");
            }
        } else {
            $this->logMessage(null, 'info', "Memulai proses Sync Fsalesman (Mode Aman: Download First)...");
        }

        try {
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ==';
            $client = new Client([
                'verify' => false,
                'timeout' => 300,
            ]);
            $now = now()->format('Y-m-d H:i:s');

            // 1. Download data terlebih dahulu
            $this->logMessage($batch, 'info', "Tahap 1: Mengunduh data dari API SLSINA...");
            $url = "https://jobs.asiatop.co.id:9080/trx/export?block=SLSINA";

            try {
                $response = $client->get($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ]
                ]);
            } catch (\Throwable $httpError) {
                $errorMsg = "Gagal mengunduh data API SLSINA: " . $httpError->getMessage() . ". Data lama fsalesman TIDAK DIHAPUS (Tetap Aman).";
                $this->logMessage($batch, 'error', $errorMsg);
                Log::error("SyncFsalesmanJob: " . $errorMsg);
                if ($batch) $batch->update(['status' => 'failed']);
                return;
            }

            if ($response->getStatusCode() !== 200) {
                $errorMsg = "API mengembalikan HTTP {$response->getStatusCode()}. Data lama fsalesman TIDAK DIHAPUS (Tetap Aman).";
                $this->logMessage($batch, 'error', $errorMsg);
                Log::error("SyncFsalesmanJob: " . $errorMsg);
                if ($batch) $batch->update(['status' => 'failed']);
                return;
            }

            $json = json_decode($response->getBody(), true);
            $dataset = $json['data'] ?? [];

            if (empty($dataset)) {
                $this->logMessage($batch, 'warning', "Data dari API SLSINA kosong. Data lama fsalesman TIDAK DIHAPUS.");
                if ($batch) $batch->update(['status' => 'completed']);
                return;
            }

            $totalData = count($dataset);
            $this->logMessage($batch, 'info', "API SLSINA sukses diunduh ({$totalData} baris). Memetakan data...");

            // 2. Mapping
            $mappedData = [];
            foreach ($dataset as $row) {
                $mappedData[] = [
                    'KODEREGION' => $row['KODEREGION'] === '' ? null : $row['KODEREGION'],
                    'KODECABANG' => $row['KODECABANG'] === '' ? null : $row['KODECABANG'],
                    'KD' => $row['KD'] === '' ? null : $row['KD'],
                    'SLSNO' => $row['SLSNO'] === '' ? null : $row['SLSNO'],
                    'SLSNAME' => $row['SLSNAME'] === '' ? null : $row['SLSNAME'],
                    'TEAM' => $row['TEAM'] === '' ? null : $row['TEAM'],
                    'FLAG_ACTIVE' => $row['FLAG_ACTIVE'] === '' ? null : $row['FLAG_ACTIVE'],
                    'FLAG_OFFICE' => $row['FLAG_OFFICE'] === '' ? null : $row['FLAG_OFFICE'],
                ];
            }

            // 3. ATOMIC REPLACEMENT DALAM DATABASE TRANSACTION
            // Hanya dikosongkan jika data baru sudah di tangan dan siap disisipkan
            $totalInserted = 0;
            DB::transaction(function () use ($mappedData, $batch, &$totalInserted) {
                $this->logMessage($batch, 'warning', "Download sukses! Mengosongkan data lama di tabel fsalesman...");
                DB::statement('TRUNCATE TABLE fsalesman');

                $chunks = array_chunk($mappedData, 500);
                foreach ($chunks as $chunk) {
                    DB::table('fsalesman')->insert($chunk);
                    $totalInserted += count($chunk);
                }

                // Update FLAG_OFFICE di dalam transaksi yang sama
                DB::statement("UPDATE fsalesman SET \"FLAG_OFFICE\" = 'N' WHERE \"FLAG_OFFICE\" IS NULL");
            });

            $this->logMessage($batch, 'success', "Tahap Penggantian Data Selesai. Total baris tersimpan: {$totalInserted}");
            $this->logMessage($batch, 'success', "Proses Selesai. Sync Fsalesman sukses dijalankan.");

            if ($batch) $batch->update(['status' => 'completed']);
            Log::info("SyncFsalesmanJob selesai. Inserted {$totalInserted} rows.");

        } catch (\Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan sistem: " . $e->getMessage());
            if ($batch) $batch->update(['status' => 'failed']);
            Log::error("SyncFsalesmanJob Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function logMessage($batch, $type, $message)
    {
        if ($batch) {
            $batch->addLog($type, $message);
        }

        if (app()->runningInConsole()) {
            $timestamp = now()->format('Y-m-d H:i:s');
            $typeUpper = strtoupper($type);
            echo "[$timestamp] [$typeUpper] $message\n";
        }
    }
}
