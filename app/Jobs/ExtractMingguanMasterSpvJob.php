<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ExtractMingguanMasterSpvJob implements ShouldQueue
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
        if ($this->batchId) {
            $batch = \App\Models\ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', '[1.5/X] Memulai agregasi Master SPV Mingguan (MPP)...');
                $batch->addLog('warning', '[1.5/X] Membersihkan data lama Master SPV Mingguan untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        \Illuminate\Support\Facades\DB::table('insentif_mingguan_master_spvs')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[1.5/X] Mengeksekusi kueri agregasi Master SPV Mingguan...');
        }

        $bulanFormat = $this->bulan . '-01'; // Untuk target_per_depo (YYYY-MM-01)

        // 2. Eksekusi Kueri Gabungan
        $query = "
            INSERT INTO insentif_mingguan_master_spvs (
                bulan,
                region_name,
                area_name,
                cabang,
                supervisor_code,
                supervisor_name,
                supervisor_code_hak_akses_login,
                created_at,
                updated_at
            )
            WITH CabangGabungan AS (
                SELECT cabang 
                FROM target_per_depo 
                WHERE bulan = ?
                
                UNION    
                
                SELECT cabang 
                FROM insentif_mingguan_master_distributors 
                WHERE bulan = ?
            )
            SELECT 
                ? as bulan,
                COALESCE(mr.region_name, md.region_name) AS region_name,
                COALESCE(ma.area_name, md.area_name) AS area_name,
                cg.cabang AS cabang,
                mb.supervisor_code,
                COALESCE(ms.description, 'Vacant') AS supervisor_name,
                te.team_elite_code AS supervisor_code_hak_akses_login,
                NOW() as created_at,
                NOW() as updated_at
            FROM CabangGabungan cg
            LEFT JOIN master_branches mb ON cg.cabang = mb.branch_name
            LEFT JOIN master_supervisors ms ON mb.supervisor_code = ms.supervisor_code
            LEFT JOIN master_areas ma ON ms.area_code = ma.area_code
            LEFT JOIN master_regions mr ON ma.region_code = mr.region_code
            LEFT JOIN team_elite_code_mappings te ON te.siso_code = ms.supervisor_code 
            LEFT JOIN (
                SELECT branch_name, MAX(area_name) AS area_name, MAX(region_name) AS region_name 
                FROM master_distributors 
                GROUP BY branch_name
            ) md ON cg.cabang = md.branch_name
        ";

        \Illuminate\Support\Facades\DB::statement($query, [$bulanFormat, $this->bulan, $this->bulan]);

        if (isset($batch)) {
            $batch->addLog('success', '[1.5/X] Sukses membuat Master SPV Mingguan!');
        }
    }
}
