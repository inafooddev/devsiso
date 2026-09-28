<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;
use Exception;

class SyncFundamentalSalesStep2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
    protected $batchId;
    protected $bulan;

    public function __construct($batchId, $bulan)
    {
        $this->batchId = $batchId;
        $this->bulan = $bulan;
    }

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
        }

        try {
            $this->logMessage($batch, 'info', "Tahap 2: Menarik data Target (target_per_depo) untuk bulan {$this->bulan}...");
            
            $updated = DB::update("
                UPDATE ipm_fundamental_sales AS ifs
                SET target = tpd.total_target,
                    updated_at = NOW()
                FROM (
                    SELECT bulan, cabang, SUM(target) as total_target
                    FROM target_per_depo
                    WHERE bulan = ?
                    GROUP BY bulan, cabang
                ) AS tpd
                WHERE ifs.bulan = tpd.bulan 
                  AND ifs.cabang = tpd.cabang 
                  AND ifs.bulan = ?
            ", [$this->bulan, $this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 2 Selesai: Berhasil memperbarui $updated cabang dengan data Target.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 2: " . $e->getMessage());
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
