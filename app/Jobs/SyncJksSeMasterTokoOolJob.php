<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ImportBatch;

class SyncJksSeMasterTokoOolJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
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
                $this->logMessage($batch, 'info', "Memulai proses Sync JKS SE Master Toko OOL...");
            }
        } else {
            $this->logMessage(null, 'info', "Memulai proses Sync JKS SE Master Toko OOL...");
        }

        try {
            // STEP 1: Truncate Table
            $this->logMessage($batch, 'warning', "Tahap 1: Menghapus (Truncate) semua data lama di tabel jks_se_master_toko_ool...");
            DB::statement('TRUNCATE TABLE jks_se_master_toko_ool RESTART IDENTITY');
            $this->logMessage($batch, 'success', "Tahap 1 Selesai. Tabel berhasil dikosongkan.");

            // STEP 2: Insert using Query
            $this->logMessage($batch, 'info', "Tahap 2: Mulai menyisipkan data menggunakan Query...");
            
            $query = <<<SQL
INSERT INTO jks_se_master_toko_ool (
    region_code, region_name, area_code, area_name, supervisor_code, supervisor_name,
    distributor_code, distributor_name, customer_code, customer_eska, customer_name,
    alamat, avg_value_net, created_at, updated_at
)
WITH monthly AS (
    SELECT
        zs.bulan,
        md.region_code,
        md.region_name,
        md.area_code,
        md.area_name,
        te.team_elite_code AS supervisor_code,
        ms.description AS supervisor_name,
        zs.kd_dist AS distributor_code,
        zs.nm_dist AS distributor_name,
        zs.uniq_kd AS customer_code,
        cme.custno AS customer_eska,
        zs.custname AS customer_name,
        zs.alamat,
        SUM(zs.neto) AS value_net
    FROM zv_so_per_toko_2026 zs
    LEFT JOIN distributor_implementasi_eskalink die
        ON die.distributor_code = zs.kd_dist
    LEFT JOIN customer_map_eska cme
        ON die.eskalink_code = cme.branch
        AND zs.custno = cme.custno_dist
    LEFT JOIN master_distributors md
        ON md.distributor_code = zs.kd_dist
    LEFT JOIN team_elite_code_mappings te
        ON te.siso_code = md.supervisor_code
    LEFT JOIN master_supervisors ms
        ON md.supervisor_code = ms.supervisor_code
    WHERE md.is_active IS TRUE
    GROUP BY
        zs.bulan,
        md.region_code,
        md.region_name,
        md.area_code,
        md.area_name,
        te.team_elite_code,
        ms.description,
        zs.kd_dist,
        zs.nm_dist,
        zs.uniq_kd,
        cme.custno,
        zs.custname,
        zs.alamat
),
latest_customer AS (
    SELECT
        *,
        ROW_NUMBER() OVER (
            PARTITION BY customer_code
            ORDER BY bulan DESC
        ) AS rn
    FROM monthly
),
customer_average AS (
    SELECT
        customer_code,
        AVG(value_net) AS avg_value_net
    FROM monthly
    GROUP BY customer_code
)
SELECT
    lc.region_code,
    lc.region_name,
    lc.area_code,
    lc.area_name,
    lc.supervisor_code,
    lc.supervisor_name,
    lc.distributor_code,
    lc.distributor_name,
    lc.customer_code,
    lc.customer_eska,
    lc.customer_name,
    lc.alamat,
    ca.avg_value_net,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM latest_customer lc
LEFT JOIN customer_average ca
    ON ca.customer_code = lc.customer_code
WHERE lc.rn = 1
SQL;

            DB::statement($query);
            
            $this->logMessage($batch, 'success', "Tahap 2 Selesai. Eksekusi query berhasil.");
            $this->logMessage($batch, 'success', "Proses Selesai. Sync JKS SE Master Toko OOL sukses dijalankan.");
            
            if ($batch) {
                $batch->update(['status' => 'completed']);
            }
            Log::info("SyncJksSeMasterTokoOolJob selesai dijalankan.");

        } catch (\Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan: " . $e->getMessage());
            if ($batch) {
                $batch->update(['status' => 'failed']);
            }
            Log::error("SyncJksSeMasterTokoOolJob Error: " . $e->getMessage());
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
