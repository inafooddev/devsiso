<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ImportBatch;

class ExtractInsentifSeIptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bulan; // Format: YYYY-MM
    public $batchId;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct($bulan, $batchId = null)
    {
        $this->bulan = $bulan;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $carbonBulan = Carbon::createFromFormat('Y-m', $this->bulan);
        $startDate = $carbonBulan->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $carbonBulan->copy()->endOfMonth()->format('Y-m-d');

        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', '[5/X] Memulai perhitungan IPT (SKU & EC) per Salesman...');
                $batch->addLog('warning', '[5/X] Membersihkan data lama IPT untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        DB::table('insentif_se_ipts')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[5/X] Mengeksekusi kueri agregasi SKU dan EC...');
        }

        // 2. Eksekusi Raw Query
        $query = "
            INSERT INTO insentif_se_ipts (
                bulan, 
                distributor_code, 
                sales_code, 
                sku,
                ec, 
                created_at, 
                updated_at
            )
            SELECT 
                ? as bulan,
                senn.branch_code as distributor_code,
                senn.sales_code,
                COUNT(senn.prd_name) as sku,
                COUNT(DISTINCT senn.invoice_no) as ec,
                NOW() as created_at,
                NOW() as updated_at
            FROM so_eska_n_noneska senn 
            WHERE senn.invoice_date BETWEEN ? AND ?
              AND senn.branch_code IS NOT NULL
              AND senn.sales_code IS NOT NULL
            GROUP BY senn.branch_code, senn.sales_code
        ";

        DB::statement($query, [$this->bulan, $startDate, $endDate]);

        if (isset($batch)) {
            $batch->addLog('success', '[5/X] Sukses menghitung IPT (SKU & EC) per Salesman!');
        }
    }
}
