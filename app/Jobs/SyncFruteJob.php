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

    /**
     * Execute the job safely with Download First -> Atomic Per-Region Replacement.
     */
    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $this->logMessage($batch, 'info', "Memulai proses Sync Frute (Mode Aman: Download First)...");
            }
        } else {
            $this->logMessage(null, 'info', "Memulai proses Sync Frute (Mode Aman: Download First)...");
        }

        try {
            $blocks = ['JKSINAJWA1', 'JKSINAJWA2', 'JKSINAPUL1', 'JKSINASUM1', 'JKSINASUM2', 'JKSHOINA'];
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ==';
            $client = new Client([
                'verify' => false,
                'timeout' => 300,
            ]);
            $now = now()->format('Y-m-d H:i:s');

            $totalInserted = 0;
            $failedBlocks = [];
            $successBlocks = [];

            $this->logMessage($batch, 'info', "Mulai mengunduh dan menyinkronkan data per block (Tanpa Global Truncate)...");

            foreach ($blocks as $block) {
                $this->logMessage($batch, 'info', "-> Mengunduh blok API: {$block}...");
                $url = "https://jobs.asiatop.co.id:9080/trx/export?block={$block}";
                
                // 1. Download data terlebih dahulu
                try {
                    $response = $client->get($url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token
                        ]
                    ]);
                } catch (\Throwable $httpError) {
                    $errorMsg = "Gagal mengunduh blok {$block}: " . $httpError->getMessage() . ". Data lama blok ini TIDAK DIHAPUS (Tetap Aman).";
                    $this->logMessage($batch, 'error', $errorMsg);
                    Log::error("SyncFruteJob: " . $errorMsg);
                    $failedBlocks[] = $block;
                    continue;
                }

                if ($response->getStatusCode() !== 200) {
                    $errorMsg = "API mengembalikan HTTP {$response->getStatusCode()} untuk blok {$block}. Data lama TIDAK DIHAPUS.";
                    $this->logMessage($batch, 'error', $errorMsg);
                    Log::error("SyncFruteJob: " . $errorMsg);
                    $failedBlocks[] = $block;
                    continue;
                }

                $json = json_decode($response->getBody(), true);
                $dataset = $json['data'] ?? [];
                
                if (empty($dataset)) {
                    $this->logMessage($batch, 'warning', "   Blok {$block} kosong dari API. Data lama TIDAK DIHAPUS, proses dilewati.");
                    continue;
                }

                $totalBlockRows = count($dataset);
                $this->logMessage($batch, 'info', "   Blok {$block} sukses diunduh ({$totalBlockRows} baris). Memetakan data...");

                // 2. Mapping
                $mappedData = [];
                $detectedRegion = null;

                foreach ($dataset as $row) {
                    $reg = $row['REGION'] === '' ? null : $row['REGION'];
                    if (!$detectedRegion && $reg) {
                        $detectedRegion = $reg;
                    }

                    $mappedData[] = [
                        'region' => $reg,
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

                if (!$detectedRegion) {
                    $detectedRegion = str_replace('JKS', '', $block);
                }

                // 3. ATOMIC REPLACEMENT PER REGION DALAM DATABASE TRANSACTION
                DB::transaction(function () use ($detectedRegion, $mappedData, $batch, $block) {
                    if ($detectedRegion) {
                        $this->logMessage($batch, 'warning', "   Menghapus data lama region {$detectedRegion} di tabel frute...");
                        DB::table('frute')->where('region', $detectedRegion)->delete();
                    }

                    $chunks = array_chunk($mappedData, 500);
                    foreach ($chunks as $chunk) {
                        DB::table('frute')->insert($chunk);
                    }
                });

                $totalInserted += count($mappedData);
                $successBlocks[] = $block;
                $this->logMessage($batch, 'success', "   Blok {$block} ({$detectedRegion}) selesai diperbarui: " . count($mappedData) . " baris. Total: {$totalInserted}");
            }

            // 4. Ringkasan Akhir
            if (!empty($failedBlocks)) {
                $statusSummary = "Perhatian: Sebagian blok gagal diunduh: " . implode(', ', $failedBlocks) . ". Data lama blok tersebut tetap aman.";
                $this->logMessage($batch, 'warning', $statusSummary);
            }

            if (empty($successBlocks) && !empty($failedBlocks)) {
                $this->logMessage($batch, 'error', "Gagal: Tidak ada blok yang berhasil disinkronisasi.");
                if ($batch) $batch->update(['status' => 'failed']);
            } else {
                $this->logMessage($batch, 'success', "Proses Selesai. Total baris masuk/tersimpan: {$totalInserted}");
                if ($batch) $batch->update(['status' => 'completed']);
            }

            Log::info("SyncFruteJob selesai. Total inserted: {$totalInserted} rows.");

        } catch (\Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan sistem: " . $e->getMessage());
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
