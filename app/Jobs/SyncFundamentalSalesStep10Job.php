<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Exception;

class SyncFundamentalSalesStep10Job implements ShouldQueue
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
            $carbonDate = Carbon::parse($this->bulan);
            $tahun = $carbonDate->year;
            $kuartal = $carbonDate->quarter;

            $this->logMessage($batch, 'info', "Tahap 10: Menarik data Potensi RWO (Kuartal $kuartal Tahun $tahun) dari list_potensi_rwo untuk bulan {$this->bulan}...");
            
            $updated = DB::update("
                UPDATE ipm_fundamental_sales ifs
                SET potensi_rwo = lpr_agg.potensi,
                    updated_at = NOW()
                FROM (
                    SELECT 
                        md.branch_name,
                        count(lpr.customer_code) as potensi
                    FROM list_potensi_rwo lpr
                    LEFT JOIN master_distributors md 
                        ON lpr.distributor_code = md.distributor_code 
                    WHERE lpr.tahun = ? 
                      AND lpr.kuartal = ?
                    GROUP BY
                        md.branch_name
                ) AS lpr_agg
                WHERE ifs.cabang = lpr_agg.branch_name
                  AND ifs.bulan = ?
            ", [$tahun, $kuartal, $this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 10 Selesai: Berhasil memperbarui $updated cabang dengan data Potensi RWO.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 10: " . $e->getMessage());
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
