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
    public function __construct($region, $batchId = null)
    {
        $this->region = $region;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', "Memulai penarikan data Customer Eska...");
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

            foreach ($regionsToProcess as $r) {
                if ($batch) {
                    $batch->addLog('info', "Memulai tarik data untuk region: {$r}");
                }
                Log::info("SyncCustomerEskaJob mulai tarik data untuk region: {$r}");

                $response = Http::withToken($token)->timeout(300)->get("https://jobs.asiatop.co.id:9080/trx/export?block={$r}");

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (is_array($data) && count($data) > 0) {
                        $insertData = [];
                        $now = Carbon::now();

                        foreach ($data as $row) {
                            $insertData[] = [
                                'region_code' => $row['REGION_CODE'] ?? null,
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

                        $chunks = array_chunk($insertData, 500);
                        foreach ($chunks as $chunk) {
                            DB::table('customer_prc_eska')->insert($chunk);
                        }

                        $totalDataInserted += count($insertData);
                        if ($batch) {
                            $batch->addLog('success', "Berhasil insert " . count($insertData) . " data untuk region: {$r}");
                        }
                        Log::info("SyncCustomerEskaJob berhasil insert " . count($insertData) . " data untuk region: {$r}");
                    } else {
                        if ($batch) {
                            $batch->addLog('warning', "Data dari API kosong untuk region: {$r}");
                        }
                        Log::warning("SyncCustomerEskaJob: Data dari API kosong untuk region: {$r}");
                    }
                } else {
                    if ($batch) {
                        $batch->addLog('error', "Gagal tarik data dari API untuk region: {$r}");
                    }
                    Log::error("SyncCustomerEskaJob: Gagal tarik data dari API untuk region: {$r}. Error: " . $response->body());
                }
            }

            if ($batch) {
                $batch->addLog('success', "Proses Selesai. Total $totalDataInserted data berhasil disimpan.");
                $batch->update(['status' => 'completed']);
            }

        } catch (\Throwable $e) {
            if (isset($batch)) {
                $batch->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            throw $e;
        }
    }
}
