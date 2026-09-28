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

class SyncFundamentalSalesStep13Job implements ShouldQueue
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
            $this->logMessage($batch, 'info', "Tahap 13: Menarik data RO dari tabel jks_salesmans untuk bulan {$this->bulan}...");

            $updated = DB::update("
                UPDATE ipm_fundamental_sales AS ifs
                SET ro = js_agg.ro,
                    updated_at = NOW()
                FROM (
                    SELECT
                        js.bulan::date AS bulan,
                        md.branch_name AS cabang,
                        COUNT(DISTINCT js.customer_code) AS ro
                    FROM jks_salesmans js
                    LEFT JOIN master_distributors md
                        ON js.distributor_code = md.distributor_code
                    WHERE js.bulan::date = ?
                    GROUP BY
                        js.bulan::date,
                        md.branch_name
                ) AS js_agg
                WHERE ifs.cabang = js_agg.cabang
                  AND ifs.bulan = js_agg.bulan
            ", [$this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 13 Selesai: Berhasil memperbarui $updated cabang dengan data RO.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 13: " . $e->getMessage());
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
