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

class SyncFundamentalSalesStep4Job implements ShouldQueue
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
            $this->logMessage($batch, 'info', "Tahap 4: Menarik data Productive Call (PC) dari rpt_visit_an_h untuk bulan {$this->bulan}...");
            
            $updated = DB::update("
                UPDATE ipm_fundamental_sales AS ifs
                SET pc = v.total_pc,
                    updated_at = NOW()
                FROM (
                    SELECT 
                        bulan,
                        cabang,
                        SUM(jumlah_customer) AS total_pc
                    FROM (
                        SELECT
                            md.branch_name AS cabang,
                            DATE_TRUNC('month', rv.\"TANGGAL\"::date)::date AS bulan,
                            COUNT(DISTINCT rv.\"CUSTNO\") AS jumlah_customer
                        FROM rpt_visit_an_h rv
                        LEFT JOIN distributor_implementasi_eskalink die
                            ON die.eskalink_code = rv.\"BID\"
                        LEFT JOIN master_distributors md
                            ON md.distributor_code = die.distributor_code
                        WHERE rv.\"FLAG_PJP\" = 'R'
                          AND rv.\"RID\" <> 'HOINA'
                          AND DATE_TRUNC('month', rv.\"TANGGAL\"::date)::date = ?
                        GROUP BY
                            rv.\"BID\",
                            md.branch_name,
                            DATE_TRUNC('month', rv.\"TANGGAL\"::date)::date,
                            rv.\"MUID\",
                            rv.\"TANGGAL\"::date
                    ) x
                    GROUP BY bulan, cabang
                ) AS v
                WHERE ifs.bulan = v.bulan 
                  AND ifs.cabang = v.cabang 
                  AND ifs.bulan = ?
            ", [$this->bulan, $this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 4 Selesai: Berhasil memperbarui $updated cabang dengan data PC (Productive Call).");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 4: " . $e->getMessage());
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
