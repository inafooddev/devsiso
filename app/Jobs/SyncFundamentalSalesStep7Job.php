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

class SyncFundamentalSalesStep7Job implements ShouldQueue
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
            $this->logMessage($batch, 'info', "Tahap 7: Menarik data Active Outlet (AO) dari t_sellingout untuk bulan {$this->bulan}...");
            
            $updated = DB::update("
                UPDATE ipm_fundamental_sales AS ifs
                SET ao = ts.total_ao,
                    updated_at = NOW()
                FROM (
                    SELECT
                        DATE_TRUNC('month', \"INVOICE_DATE\"::date)::date AS bulan,
                        \"CABANG\" AS cabang,
                        COUNT(DISTINCT \"KDUNIQ\") AS total_ao
                    FROM t_sellingout
                    WHERE DATE_TRUNC('month', \"INVOICE_DATE\"::date)::date = ?
                    GROUP BY
                        DATE_TRUNC('month', \"INVOICE_DATE\"::date)::date,
                        \"CABANG\"
                ) AS ts
                WHERE ifs.bulan = ts.bulan 
                  AND ifs.cabang = ts.cabang 
                  AND ifs.bulan = ?
            ", [$this->bulan, $this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 7 Selesai: Berhasil memperbarui $updated cabang dengan data AO (Active Outlet).");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 7: " . $e->getMessage());
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
