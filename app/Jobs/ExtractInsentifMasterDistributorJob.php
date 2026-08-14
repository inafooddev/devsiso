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

class ExtractInsentifMasterDistributorJob implements ShouldQueue
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
                $batch->addLog('info', '[1/X] Memulai penarikan data Master Distributor...');
                $batch->addLog('warning', '[1/X] Membersihkan data lama Master Distributor untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini agar idempotent
        DB::table('insentif_master_distributors')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[1/X] Mengeksekusi kueri agregasi Master Distributor...');
        }

        // 2. Eksekusi Raw Query Insert Into ... Select
        // Menggunakan now() untuk created_at dan updated_at
        $query = "
            INSERT INTO insentif_master_distributors (
                bulan, 
                region_code, 
                region_name, 
                area_code, 
                area_name, 
                distributor_code, 
                distributor_name, 
                cabang, 
                created_at, 
                updated_at
            )
            SELECT 
                ? as bulan,
                MAX(senn.region_code) as region_code,
                MAX(senn.region_name) as region_name,
                MAX(senn.entity_code) as area_code,
                MAX(senn.entity_name) as area_name,
                senn.branch_code as distributor_code,
                MAX(senn.branch_name) as distributor_name,
                MAX(md.branch_name) as cabang,
                NOW() as created_at,
                NOW() as updated_at
            FROM so_eska_n_noneska senn 
            LEFT JOIN master_distributors md 
                ON md.distributor_code = senn.branch_code 
            WHERE senn.invoice_date BETWEEN ? AND ?
              AND senn.branch_code IS NOT NULL
            GROUP BY senn.branch_code
        ";

        DB::statement($query, [$this->bulan, $startDate, $endDate]);

        if (isset($batch)) {
            $batch->addLog('success', '[1/X] Sukses menarik data Master Distributor!');
        }
    }
}
