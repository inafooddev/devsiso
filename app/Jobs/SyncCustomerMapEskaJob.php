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

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $batch->addLog('info', "Memulai penarikan data Customer Map Eska...");
            }
        }

        try {
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ=='; 

            if ($batch) {
                $batch->addLog('warning', "Melakukan TRUNCATE (kosongkan) tabel customer_map_eska...");
            }
            DB::table('customer_map_eska')->truncate();

            if ($batch) {
                $batch->addLog('info', "Memulai tarik data dari API...");
            }
            Log::info("SyncCustomerMapEskaJob mulai tarik data");

            $response = Http::withToken($token)->timeout(300)->get("https://jobs.asiatop.co.id:9080/trx/export?block=CUSTMAP");

            if ($response->successful()) {
                $responseData = $response->json();
                $data = $responseData['data'] ?? [];
                
                if (is_array($data) && count($data) > 0) {
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

                    $chunks = array_chunk($insertData, 500);
                    foreach ($chunks as $chunk) {
                        DB::table('customer_map_eska')->insert($chunk);
                    }

                    if ($batch) {
                        $batch->addLog('success', "Berhasil insert " . count($insertData) . " data.");
                        $batch->addLog('success', "Proses Selesai. Total " . count($insertData) . " data berhasil disimpan.");
                        $batch->update(['status' => 'completed']);
                    }
                    Log::info("SyncCustomerMapEskaJob berhasil insert " . count($insertData) . " data.");
                } else {
                    if ($batch) {
                        $batch->addLog('warning', "Data dari API kosong.");
                        $batch->update(['status' => 'completed']);
                    }
                    Log::warning("SyncCustomerMapEskaJob: Data dari API kosong.");
                }
            } else {
                $errorMsg = "Gagal mengambil data. HTTP Status: " . $response->status();
                if ($batch) {
                    $batch->addLog('error', $errorMsg);
                    $batch->update(['status' => 'failed']);
                }
                Log::error("SyncCustomerMapEskaJob: " . $errorMsg);
            }

        } catch (\Exception $e) {
            if ($batch) {
                $batch->addLog('error', "Terjadi kesalahan: " . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            Log::error("SyncCustomerMapEskaJob Error: " . $e->getMessage());
            throw $e;
        }
    }
}
