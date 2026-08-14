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

class ExtractInsentifQtyPerSeJob implements ShouldQueue
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
                $batch->addLog('info', '[4/X] Memulai perhitungan Quantity (CTN) per Salesman...');
                $batch->addLog('warning', '[4/X] Membersihkan data lama Quantity per Salesman untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        DB::table('insentif_qty_per_ses')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[4/X] Mengeksekusi kueri agregasi (SUM) Quantity (CTN)...');
        }

        // 2. Eksekusi Raw Query
        $query = "
            INSERT INTO insentif_qty_per_ses (
                bulan, 
                distributor_code, 
                sales_code, 
                product_group_3,
                qty_ctn, 
                created_at, 
                updated_at
            )
            SELECT 
                ? as bulan,
                senn.branch_code as distributor_code,
                senn.sales_code,
                senn.product_group_3,
                SUM(senn.qty1_car) as qty_ctn,
                NOW() as created_at,
                NOW() as updated_at
            FROM so_eska_n_noneska senn 
            WHERE senn.invoice_date BETWEEN ? AND ?
              AND senn.branch_code IS NOT NULL
              AND senn.sales_code IS NOT NULL
            GROUP BY senn.branch_code, senn.sales_code, senn.product_group_3
        ";

        DB::statement($query, [$this->bulan, $startDate, $endDate]);

        if (isset($batch)) {
            $batch->addLog('success', '[4/X] Sukses menghitung Quantity (CTN) per Salesman!');
        }
    }
}
