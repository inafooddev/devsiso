<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ExtractInsentifSeVisitJob implements ShouldQueue
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
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', '[X/X] Memulai perhitungan Kunjungan SE (PC, AC, EC)...');
                $batch->addLog('warning', '[X/X] Membersihkan data lama Kunjungan SE untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        DB::table('insentif_se_visits')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[X/X] Mengeksekusi kueri agregasi Kunjungan...');
        }

        // 2. Eksekusi Raw Query
        $query = "
            INSERT INTO insentif_se_visits (
                bulan, 
                distributor_code, 
                salesman_code, 
                pc, 
                ac, 
                ec,
                created_at, 
                updated_at
            )
            SELECT 
                SUBSTRING(\"TANGGAL\", 1, 7) as bulan,
                \"BID\" as distributor_code,
                \"MUID\" as salesman_code,
                SUM(CASE WHEN \"FLAG_PJP\" = 'R' THEN 1 ELSE 0 END) as pc,
                SUM(CASE WHEN \"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as ac,
                SUM(CASE WHEN \"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as ec,
                NOW(), NOW()
            FROM rpt_visit_an_h
            WHERE SUBSTRING(\"TANGGAL\", 1, 7) = ? 
              AND \"BID\" IS NOT NULL
              AND \"MUID\" IS NOT NULL
              AND (\"FLAG_PAUSE\" IS NULL OR \"FLAG_PAUSE\" != 'Y')
            GROUP BY SUBSTRING(\"TANGGAL\", 1, 7), \"BID\", \"MUID\"
        ";

        DB::statement($query, [$this->bulan]);

        if (isset($batch)) {
            $batch->addLog('success', '[X/X] Sukses menghitung Kunjungan SE (PC, AC, EC)!');
        }
    }
}
