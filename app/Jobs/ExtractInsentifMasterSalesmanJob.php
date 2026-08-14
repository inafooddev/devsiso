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

class ExtractInsentifMasterSalesmanJob implements ShouldQueue
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
                $batch->addLog('info', '[2/X] Memulai penarikan data Master Salesman...');
                $batch->addLog('warning', '[2/X] Membersihkan data lama Master Salesman untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        DB::table('insentif_master_salesmans')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[2/X] Mengeksekusi kueri agregasi Master Salesman...');
        }

        // 2. Eksekusi Raw Query
        $query = "
            INSERT INTO insentif_master_salesmans (
                bulan, 
                distributor_code, 
                sales_code, 
                sales_name, 
                jenis_se,
                created_at, 
                updated_at
            )
            SELECT 
                ? as bulan,
                senn.branch_code as distributor_code,
                senn.sales_code,
                MAX(UPPER(senn.sales_name)) as sales_name,
                CASE 
                    WHEN senn.sales_code LIKE 'SOI%' OR senn.sales_code LIKE '%OFI' THEN 'office'
                    ELSE 'se'
                END as jenis_se,
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
            $batch->addLog('success', '[2/X] Sukses menarik data Master Salesman!');
        }
    }
}
