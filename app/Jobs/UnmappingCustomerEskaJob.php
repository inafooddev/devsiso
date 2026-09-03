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

class UnmappingCustomerEskaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    protected $batchId;
    protected $startDate;
    protected $endDate;

    public function __construct($batchId, $startDate, $endDate)
    {
        $this->batchId = $batchId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $batch->addLog('info', "Memulai proses Unmapping Customer Eska...");
                $batch->addLog('info', "Rentang Tanggal: {$this->startDate} s/d {$this->endDate}");
            }
        }

        try {
            // STEP 1: INSERT INTO customer_map_eska
            if ($batch) $batch->addLog('info', "Mengeksekusi Tahap 1: Insert ke customer_map_eska...");
            
            $query1 = "
                INSERT INTO customer_map_eska (
                    bln,
                    distid,
                    branch_dist,
                    custno_dist,
                    branch,
                    custno
                )
                WITH unmapped AS (
                    SELECT DISTINCT
                        die.eskalink_code_dist,
                        die.eskalink_code,
                        rp.kd_toko,
                        rp.bln,
                        SUBSTRING(rp.kd_distributor FROM 3 FOR 3) AS cabang_code,
                        rp.kd_distributor NOT IN (
                            'DITSK001','DIBJR001','DICBN001','DISMD002','DIGRT001','DIKNG001','DIYYK002'
                        ) AS is_listed
                    FROM summary_selling_toko rp
                    LEFT JOIN distributor_implementasi_eskalink die
                        ON rp.kd_distributor = die.distributor_code
                    LEFT JOIN customer_map_eska cme
                        ON UPPER(REGEXP_REPLACE(TRIM(rp.kd_toko), '\s+', ' ', 'g')) = UPPER(REGEXP_REPLACE(TRIM(cme.custno_dist), '\s+', ' ', 'g'))
                        AND die.eskalink_code_dist = cme.distid
                        AND die.eskalink_code_dist = cme.branch_dist
                    WHERE 
                        cme.custno_dist IS NULL
                        AND die.eskalink_code NOT IN ('')
                        AND rp.bln BETWEEN ? AND ?
                ),
                lastnum AS (
                    SELECT 
                        kodecabang,
                        COALESCE(MAX((regexp_replace(custno, '^[A-Za-z]{5}', ''))::int), 0) AS last_no
                    FROM customer_prc_eska
                    WHERE custno ~ '^[A-Za-z]{5}[0-9]{3,5}$'
                    GROUP BY kodecabang
                ),
                next_codes AS (
                    SELECT
                        u.eskalink_code,
                        u.eskalink_code_dist,
                        u.bln,
                        u.kd_toko,
                        u.cabang_code,
                        u.is_listed,
                        CASE 
                            WHEN u.is_listed THEN 
                                COALESCE(ln.last_no, 0)
                                + ROW_NUMBER() OVER (PARTITION BY u.eskalink_code ORDER BY u.kd_toko)
                            ELSE NULL
                        END AS new_number
                    FROM unmapped u
                    LEFT JOIN lastnum ln
                        ON ln.kodecabang = u.eskalink_code
                )
                SELECT
                    bln,
                    CASE WHEN COALESCE(eskalink_code_dist, '') = '' THEN eskalink_code ELSE eskalink_code_dist END AS distid,
                    CASE WHEN COALESCE(eskalink_code_dist, '') = '' THEN eskalink_code ELSE eskalink_code_dist END AS branch_dist,
                    kd_toko AS custno_dist,
                    COALESCE(eskalink_code, eskalink_code_dist) AS branch,
                    CASE 
                        WHEN is_listed THEN
                            'CI' || cabang_code || LPAD(new_number::text, 4, '0')
                        ELSE
                            LEFT('CI' || cabang_code || REGEXP_REPLACE(TRIM(kd_toko), '\s+', ' ', 'g'), 25)
                    END AS custno
                FROM next_codes
                ORDER BY bln, eskalink_code, kd_toko
            ";

            DB::statement($query1, [$this->startDate, $this->endDate]);
            if ($batch) $batch->addLog('success', "Tahap 1 Selesai.");

            // STEP 2: INSERT INTO customer_dist_eska
            if ($batch) $batch->addLog('info', "Mengeksekusi Tahap 2: Insert ke customer_dist_eska...");
            
            $query2 = "
                INSERT INTO customer_dist_eska (
                    bln,
                    distid,
                    branch,
                    custno,
                    custname
                )
                SELECT 
                    cme.bln,
                    cme.distid,
                    cme.branch_dist,
                    cme.custno_dist,
                    UPPER(rp.nama_toko) AS custname
                FROM customer_map_eska cme
                LEFT JOIN distributor_implementasi_eskalink die 
                    ON cme.branch = die.eskalink_code 
                LEFT JOIN ro_penjualan rp 
                    ON die.distributor_code = rp.kd_distributor 
                    AND UPPER(REGEXP_REPLACE(TRIM(cme.custno_dist), '\s+', ' ', 'g')) = UPPER(REGEXP_REPLACE(TRIM(rp.kd_toko), '\s+', ' ', 'g'))
                WHERE 
                    cme.bln BETWEEN ? AND ?
                    AND NOT EXISTS (
                        SELECT 1
                        FROM customer_dist_eska d
                        WHERE d.distid = cme.distid
                        AND d.branch = cme.branch_dist
                        AND d.custno = cme.custno_dist
                    )
            ";

            DB::statement($query2, [$this->startDate, $this->endDate]);
            if ($batch) $batch->addLog('success', "Tahap 2 Selesai.");

            // STEP 3: INSERT INTO customer_prc_eska
            if ($batch) $batch->addLog('info', "Mengeksekusi Tahap 3: Insert ke customer_prc_eska...");
            
            $query3 = "
                INSERT INTO customer_prc_eska (
                    bln,
                    custno,
                    custname,
                    custadd1,
                    ccity,
                    cterm,
                    typeout,
                    grupout,
                    gharga,
                    flagpay,
                    flagout,
                    kodecabang,
                    la,
                    lg
                )
                SELECT 
                    cme.bln,
                    cme.custno,
                    UPPER(rp.nama_toko) AS custname,
                    UPPER(rp.alamat) AS custadd1,
                    rp.cabang AS ccity,
                    '014' AS cterm,
                    'GT04' AS typeout,
                    'GT' AS grupout,
                    pz.price_zone AS gharga,
                    'K' AS flagpay,
                    'C' AS flagout,
                    cme.branch AS kodecabang,
                    '0' AS la,
                    '0' AS lg
                FROM customer_map_eska cme
                LEFT JOIN distributor_implementasi_eskalink die 
                    ON cme.branch = die.eskalink_code 
                LEFT JOIN ro_penjualan rp 
                    ON die.distributor_code = rp.kd_distributor 
                    AND UPPER(REGEXP_REPLACE(TRIM(cme.custno_dist), '\s+', ' ', 'g')) = UPPER(REGEXP_REPLACE(TRIM(rp.kd_toko), '\s+', ' ', 'g'))
                LEFT JOIN master_distributors md 
                    ON die.distributor_code = md.distributor_code 
                LEFT JOIN price_zone pz 
                    ON md.area_code = pz.area_code 
                WHERE 
                    cme.bln BETWEEN ? AND ?
                    AND NOT EXISTS (
                        SELECT 1
                        FROM customer_prc_eska d
                        WHERE d.kodecabang = cme.branch
                        AND d.custno = cme.custno
                    )
            ";

            DB::statement($query3, [$this->startDate, $this->endDate]);
            if ($batch) $batch->addLog('success', "Tahap 3 Selesai.");

            if ($batch) {
                $batch->addLog('success', "Proses Selesai. Unmapping berhasil dijalankan.");
                $batch->update(['status' => 'completed']);
            }
            Log::info("UnmappingCustomerEskaJob selesai.");

        } catch (\Exception $e) {
            if ($batch) {
                $batch->addLog('error', "Terjadi kesalahan: " . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            Log::error("UnmappingCustomerEskaJob Error: " . $e->getMessage());
            throw $e;
        }
    }
}
