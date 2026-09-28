<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Exception;

class SyncFundamentalSalesStep11Job implements ShouldQueue
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
            $bulanKe = ($carbonDate->month - 1) % 3 + 1; // 1, 2, or 3
            
            $startOfQuarter = $carbonDate->copy()->firstOfQuarter()->format('Y-m-d');
            $tabelPenjualan = "zv_so_per_toko_{$tahun}";

            if (!Schema::hasTable($tabelPenjualan)) {
                $this->logMessage($batch, 'warning', "Tahap 11: Tabel $tabelPenjualan tidak ditemukan. Skip perhitungan Capai RWO.");
                return;
            }

            $this->logMessage($batch, 'info', "Tahap 11: Menarik data Capai RWO (Q$kuartal-$tahun, Bulan Ke-$bulanKe). Target Proporsi: (Target/3)*$bulanKe...");
            
            $updated = DB::update("
                UPDATE ipm_fundamental_sales AS ifs
                SET capai_rwo = capai_agg.capai,
                    updated_at = NOW()
                FROM (
                    SELECT 
                        md.branch_name, 
                        COUNT(DISTINCT lpr.customer_code) as capai
                    FROM list_potensi_rwo lpr
                    LEFT JOIN master_distributors md 
                        ON lpr.distributor_code = md.distributor_code
                    INNER JOIN (
                        SELECT uniq_kd, SUM(neto) as accumulated_sales
                        FROM {$tabelPenjualan}
                        WHERE bulan BETWEEN ? AND ?
                        GROUP BY uniq_kd
                    ) AS sales 
                        ON lpr.customer_code = sales.uniq_kd
                    WHERE lpr.tahun = ? 
                      AND lpr.kuartal = ?
                      AND sales.accumulated_sales >= (lpr.total_target / 3) * ?
                    GROUP BY 
                        md.branch_name
                ) AS capai_agg
                WHERE ifs.cabang = capai_agg.branch_name
                  AND ifs.bulan = ?
            ", [$startOfQuarter, $this->bulan, $tahun, $kuartal, $bulanKe, $this->bulan]);

            $this->logMessage($batch, 'success', "Tahap 11 Selesai: Berhasil memperbarui $updated cabang dengan data Capai RWO.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 11: " . $e->getMessage());
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
