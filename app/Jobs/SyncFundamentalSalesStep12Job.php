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

class SyncFundamentalSalesStep12Job implements ShouldQueue
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
            $this->logMessage($batch, 'info', "Tahap 12: Menarik data Jumlah SE dari tabel salesmans untuk bulan {$this->bulan}...");
            
            $updated = DB::update("
                UPDATE ipm_fundamental_sales AS ifs
                SET jumlah_se = se_agg.jumlah_se,
                    updated_at = NOW()
                FROM (
                    SELECT
                        md.branch_name AS cabang,
                        COUNT(s.salesman_code) AS jumlah_se
                    FROM salesmans s
                    LEFT JOIN master_distributors md
                        ON s.distributor_code = md.distributor_code
                    WHERE md.is_active IS TRUE
                      AND s.salesman_code NOT ILIKE '%OFI%'
                      AND s.is_active IS TRUE
                    GROUP BY md.branch_name
                ) AS se_agg
                WHERE ifs.cabang = se_agg.cabang
                  AND ifs.bulan = ?
            ", [$this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 12 Selesai: Berhasil memperbarui $updated cabang dengan data Jumlah SE.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 12: " . $e->getMessage());
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
