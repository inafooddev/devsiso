<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ExtractInsentifSeRoJob implements ShouldQueue
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
                $batch->addLog('info', '[X/X] Memulai perhitungan Frekuensi Rute SE...');
                $batch->addLog('warning', '[X/X] Membersihkan data lama Frekuensi Rute SE untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        DB::table('insentif_se_ro')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[X/X] Mengeksekusi kueri agregasi Rute F2/F4...');
        }

        // 2. Eksekusi Raw Query
        $query = "
            INSERT INTO insentif_se_ro (
                bulan, 
                kodecabang, 
                slsno, 
                frekuensi, 
                total_customer, 
                created_at, 
                updated_at
            )
            WITH data_aktif AS (
                SELECT * 
                FROM frute
                WHERE (h1='Y' OR h2='Y' OR h3='Y' OR h4='Y' OR h5='Y' OR h6='Y' OR h7='Y')
                  AND (m1='Y' OR m2='Y' OR m3='Y' OR m4='Y')
            ),
            customer_freq AS (
                SELECT 
                    kodecabang, 
                    slsno, 
                    custno,
                    CASE 
                        WHEN m1='Y' AND m2='Y' AND m3='Y' AND m4='Y' THEN 'F4'
                        WHEN (m1='Y' AND m3='Y') OR (m2='Y' AND m4='Y') THEN 'F2'
                        ELSE 'OTHER' 
                    END as freq
                FROM data_aktif
            ),
            se_total_cust AS (
                SELECT 
                    kodecabang,
                    slsno,
                    COUNT(DISTINCT custno) as total_customer
                FROM data_aktif
                GROUP BY kodecabang, slsno
            ),
            freq_counts AS (
                SELECT 
                    kodecabang,
                    slsno,
                    freq,
                    COUNT(custno) as total_cust
                FROM customer_freq
                WHERE freq IN ('F2', 'F4')
                GROUP BY kodecabang, slsno, freq
            ),
            ranked_freq AS (
                SELECT 
                    kodecabang,
                    slsno,
                    freq,
                    ROW_NUMBER() OVER (PARTITION BY kodecabang, slsno ORDER BY total_cust DESC) as rn
                FROM freq_counts
            )
            SELECT 
                ?,
                r.kodecabang, 
                r.slsno, 
                r.freq as frekuensi,
                t.total_customer,
                NOW(),
                NOW()
            FROM ranked_freq r
            JOIN se_total_cust t ON r.kodecabang = t.kodecabang AND r.slsno = t.slsno
            WHERE r.rn = 1
        ";

        DB::statement($query, [$this->bulan]);

        if (isset($batch)) {
            $batch->addLog('success', '[X/X] Sukses menghitung Frekuensi Rute SE!');
        }
    }
}
