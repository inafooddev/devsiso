<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ImportBatch;
use Exception;

class SyncFundamentalSalesMasterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200; // Allow 2 hours
    protected $batchId;
    protected $bulan;

    public function __construct($batchId = null, $bulan = null)
    {
        $this->batchId = $batchId;
        $this->bulan = $bulan ?? now()->format('Y-m');
    }

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $this->logMessage($batch, 'info', "Memulai Master Job Fundamental Sales untuk periode: " . $this->bulan);
            }
        } else {
            $this->logMessage(null, 'info', "Memulai Master Job Fundamental Sales untuk periode: " . $this->bulan);
        }

        try {
            // STEP 1
            SyncFundamentalSalesStep1Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 2
            SyncFundamentalSalesStep2Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 3
            SyncFundamentalSalesStep3Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 4
            SyncFundamentalSalesStep4Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 5
            SyncFundamentalSalesStep5Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 6
            SyncFundamentalSalesStep6Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 7
            SyncFundamentalSalesStep7Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 8
            SyncFundamentalSalesStep8Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 9
            SyncFundamentalSalesStep9Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 10
            SyncFundamentalSalesStep10Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 11
            SyncFundamentalSalesStep11Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 12
            SyncFundamentalSalesStep12Job::dispatchSync($this->batchId, $this->bulan);

            // STEP 13
            SyncFundamentalSalesStep13Job::dispatchSync($this->batchId, $this->bulan);
            
            $this->logMessage($batch, 'success', "🎉 Seluruh proses Fundamental Sales selesai dengan sukses.");
            if ($batch) $batch->update(['status' => 'completed']);
            Log::info("SyncFundamentalSalesMasterJob selesai untuk periode {$this->bulan}.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan di salah satu tahap: " . $e->getMessage());
            $this->logMessage($batch, 'error', "Proses dihentikan demi menjaga integritas data.");
            if ($batch) $batch->update(['status' => 'failed']);
            Log::error("SyncFundamentalSalesMasterJob Error: " . $e->getMessage());
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
