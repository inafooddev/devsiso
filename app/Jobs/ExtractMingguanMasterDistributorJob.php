<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ExtractMingguanMasterDistributorJob implements ShouldQueue
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
        $carbonBulan = \Carbon\Carbon::createFromFormat('Y-m', $this->bulan);
        $startDate = $carbonBulan->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $carbonBulan->copy()->endOfMonth()->format('Y-m-d');

        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', "[1/X] Memulai penarikan data Master Distributor Mingguan (Bulan $this->bulan)...");
                $batch->addLog('warning', "[1/X] Membersihkan data lama Master Distributor untuk bulan $this->bulan");
            }
        }

        // 1. Bersihkan data mingguan ini agar idempotent
        DB::table('insentif_mingguan_master_distributors')
            ->where('bulan', $this->bulan)
            ->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[1/X] Mengeksekusi kueri agregasi Master Distributor (Sumber: t_sellingout)...');
        }

        // 2. Eksekusi Raw Query Insert Into ... Select
        // Menggunakan now() untuk created_at dan updated_at
        $query = "
            INSERT INTO insentif_mingguan_master_distributors (
                bulan, 
                region_code, 
                region_name, 
                area_code, 
                area_name, 
                distributor_code, 
                distributor_name, 
                cabang, 
                supervisor_code,
                supervisor_name,
                created_at, 
                updated_at
            )
            SELECT 
                ? as bulan,
                MAX(md.region_code) as region_code,
                MAX(md.region_name) as region_name,
                MAX(md.area_code) as area_code,
                MAX(md.area_name) as area_name,
                ts.\"KDDIST\" as distributor_code,
                MAX(ts.\"DIST\") as distributor_name,
                MAX(md.branch_name) as cabang,
                MAX(t.team_elite_code) as supervisor_code,
                MAX(ms.description) as supervisor_name,
                NOW() as created_at,
                NOW() as updated_at
            FROM t_sellingout ts
            LEFT JOIN master_distributors md 
                ON md.distributor_code = ts.\"KDDIST\"
            LEFT JOIN team_elite_code_mappings t
                ON t.siso_code = md.supervisor_code 
            LEFT JOIN master_supervisors ms 
                ON md.supervisor_code = ms.supervisor_code 
            WHERE ts.\"INVOICE_DATE\" BETWEEN ? AND ?
              AND ts.\"KDDIST\" IS NOT NULL
            GROUP BY ts.\"KDDIST\"
        ";

        DB::statement($query, [$this->bulan, $startDate, $endDate]);

        if (isset($batch)) {
            $batch->addLog('success', '[1/X] Sukses menarik data Master Distributor Mingguan!');
        }
    }
}
