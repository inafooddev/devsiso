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

class SyncFruteJob implements ShouldQueue
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
                $this->logMessage($batch, 'info', "Memulai proses Sync Frute...");
            }
        } else {
            $this->logMessage(null, 'info', "Memulai proses Sync Frute...");
        }

        try {
            $blocks = ['JKSINAJWA1', 'JKSINAJWA2', 'JKSINAPUL1', 'JKSINASUM1', 'JKSINASUM2', 'JKSHOINA'];
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ==';
            $client = new Client(['verify' => false]);
            $now = now()->format('Y-m-d H:i:s');

            // STEP 1: Truncate Table
            $this->logMessage($batch, 'warning', "Tahap 1: Menghapus (Truncate) semua data lama di tabel frute...");
            DB::statement('TRUNCATE TABLE frute');
            $this->logMessage($batch, 'success', "Tahap 1 Selesai. Tabel frute berhasil dikosongkan.");

            // STEP 2: Loop API Blocks & Insert
            $this->logMessage($batch, 'info', "Tahap 2: Mulai mengunduh dan menyisipkan data dari API per block...");
            
            $totalInserted = 0;

            foreach ($blocks as $block) {
                $this->logMessage($batch, 'info', "-> Mengunduh blok: $block...");
                $url = "https://jobs.asiatop.co.id:9080/trx/export?block={$block}";
                
                $response = $client->get($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ]
                ]);

                $json = json_decode($response->getBody(), true);
                $dataset = $json['data'] ?? [];
                
                if (empty($dataset)) {
                    $this->logMessage($batch, 'warning', "   Blok $block kosong, dilewati.");
                    continue;
                }

                $this->logMessage($batch, 'info', "   Blok $block berhasil diunduh. Memproses ".count($dataset)." baris...");

                // Mapping
                $mappedData = [];
                foreach ($dataset as $row) {
                    $mappedData[] = [
                        'region' => $row['REGION'] === '' ? null : $row['REGION'],
                        'kodecabang' => $row['KODECABANG'] === '' ? null : $row['KODECABANG'],
                        'cabang' => $row['ENTITY'] === '' ? null : $row['ENTITY'],
                        'slsno' => $row['KODESALES'] === '' ? null : $row['KODESALES'],
                        'norute' => $row['NORUTE'] === '' ? '0' : $row['NORUTE'],
                        'custno' => $row['KODECUST'] === '' ? null : $row['KODECUST'],
                        'h1' => $row['DAY1'] === '' ? null : $row['DAY1'],
                        'h2' => $row['DAY2'] === '' ? null : $row['DAY2'],
                        'h3' => $row['DAY3'] === '' ? null : $row['DAY3'],
                        'h4' => $row['DAY4'] === '' ? null : $row['DAY4'],
                        'h5' => $row['DAY5'] === '' ? null : $row['DAY5'],
                        'h6' => $row['DAY6'] === '' ? null : $row['DAY6'],
                        'h7' => $row['DAY7'] === '' ? null : $row['DAY7'],
                        'm1' => $row['WEEK1'] === '' ? null : $row['WEEK1'],
                        'm2' => $row['WEEK2'] === '' ? null : $row['WEEK2'],
                        'm3' => $row['WEEK3'] === '' ? null : $row['WEEK3'],
                        'm4' => $row['WEEK4'] === '' ? null : $row['WEEK4'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Chunk Insert
                $chunks = array_chunk($mappedData, 500);
                foreach ($chunks as $chunk) {
                    DB::table('frute')->insert($chunk);
                    $totalInserted += count($chunk);
                }
                
                $this->logMessage($batch, 'success', "   Data $block selesai dimasukkan. Total tersimpan saat ini: $totalInserted");
            }

            $this->logMessage($batch, 'success', "Tahap 2 Selesai. Seluruh blok telah disinkronisasi. Total baris masuk: $totalInserted");
            $this->logMessage($batch, 'success', "Proses Selesai. Sync Frute sukses dijalankan.");
            
            if ($batch) $batch->update(['status' => 'completed']);
            Log::info("SyncFruteJob selesai. Inserted $totalInserted rows.");

        } catch (\Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan: " . $e->getMessage());
            if ($batch) $batch->update(['status' => 'failed']);
            Log::error("SyncFruteJob Error: " . $e->getMessage());
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
