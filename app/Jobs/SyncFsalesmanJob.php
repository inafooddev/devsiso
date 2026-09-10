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

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $this->logMessage($batch, 'info', "Memulai proses Sync Fsalesman...");
            }
        } else {
            $this->logMessage(null, 'info', "Memulai proses Sync Fsalesman...");
        }

        try {
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ==';
            $client = new Client(['verify' => false]);
            $now = now()->format('Y-m-d H:i:s');

            // STEP 1: Truncate Table
            $this->logMessage($batch, 'warning', "Tahap 1: Menghapus (Truncate) semua data lama di tabel fsalesman...");
            DB::statement('TRUNCATE TABLE fsalesman');
            $this->logMessage($batch, 'success', "Tahap 1 Selesai. Tabel fsalesman berhasil dikosongkan.");

            // STEP 2: Fetch API & Insert
            $this->logMessage($batch, 'info', "Tahap 2: Mulai mengunduh dan menyisipkan data dari API SLSINA...");
            
            $url = "https://jobs.asiatop.co.id:9080/trx/export?block=SLSINA";
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            $json = json_decode($response->getBody(), true);
            $dataset = $json['data'] ?? [];
            
            if (empty($dataset)) {
                $this->logMessage($batch, 'warning', "Data dari API SLSINA kosong. Proses dihentikan.");
                if ($batch) $batch->update(['status' => 'completed']);
                return;
            }

            $totalData = count($dataset);
            $this->logMessage($batch, 'info', "API SLSINA berhasil diunduh. Memproses {$totalData} baris...");

            // Mapping
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

            // Chunk Insert
            $chunks = array_chunk($mappedData, 500);
            $totalInserted = 0;
            foreach ($chunks as $chunk) {
                DB::table('fsalesman')->insert($chunk);
                $totalInserted += count($chunk);
            }
            
            $this->logMessage($batch, 'success', "Tahap 2 Selesai. Total baris masuk: $totalInserted");
            $this->logMessage($batch, 'success', "Proses Selesai. Sync Fsalesman sukses dijalankan.");
            
            if ($batch) $batch->update(['status' => 'completed']);
            Log::info("SyncFsalesmanJob selesai. Inserted $totalInserted rows.");

        } catch (\Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan: " . $e->getMessage());
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
