<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class ProcessSellOutJobV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchId;
    protected $filters;
    protected $totalRows;

    public function __construct(int $batchId, array $filters, int $totalRowsToProcess)
    {
        $this->batchId = $batchId;
        $this->filters = $filters;
        $this->totalRows = $totalRowsToProcess;
    }

    public function handle()
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        try {
            $batch->updateStatus('processing');

            $startDate = Carbon::create($this->filters['yearFilter'], $this->filters['monthFilter'], 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            // ========================
            // 1️⃣ MAP REGION & AREA
            // ========================
            $regionMap = [
                'INAJWA1' => 'INA JAWA 1',
                'INAJWA2' => 'INA JAWA 2',
                'INAPUL1' => 'INA PULAU 1',
                'INAREM1' => 'INA REMOTE',
                'INASUM1' => 'INA SUMATERA 1',
                'INASUM2' => 'INA SUMATERA 2',
            ];

            $areaMap = [
                'INA01'  => 'INA JABODETABEK',
                'INA011' => 'INA SUMBAR',
                'INA015' => 'INA SUMSEL',
                'INA02'  => 'INA JAWA TIMUR 1',
                'INA03'  => 'INA JAWA TIMUR 2',
                'INA04'  => 'INA JAWA BARAT',
                'INA05'  => 'INA JAWA TENGAH 1',
                'INA06'  => 'INA JAWA TENGAH 2',
                'INA07'  => 'INA KALIMANTAN',
                'INA08'  => 'INA SULAWESI',
                'INA09'  => 'INA NAD',
                'INA10'  => 'INA RIAU',
                'INA11'  => 'INA SUMUT',
                'INA12'  => 'INA BENGKULU',
                'INA13'  => 'INA JAMBI',
                'INA14'  => 'INA LAMPUNG',
                'INA16'  => 'INA KEPRI',
                'INA17'  => 'INA INDO TIMUR',
                'INA18'  => 'INA SULAWESI 2',
            ];

            // Simpan filter asli (untuk insert)
            $regionFilterOriginal = $this->filters['regionFilter'] ?? null;
            $areaFilterOriginal = $this->filters['areaFilter'] ?? null;
            $distributorFilter = $this->filters['distributorFilter'] ?? null;

            // Buat versi mapping untuk delete
            $regionForDelete = $regionFilterOriginal;
            $areaForDelete = $areaFilterOriginal;

            if (!empty($regionForDelete)) {
                $key = strtoupper(trim($regionForDelete));
                if (isset($regionMap[$key])) {
                    $regionForDelete = $regionMap[$key];
                    $batch->addLog('info', "Mapping region (DELETE): {$key} → {$regionForDelete}");
                }
            }

            if (!empty($areaForDelete)) {
                $key = strtoupper(trim($areaForDelete));
                if (isset($areaMap[$key])) {
                    $areaForDelete = $areaMap[$key];
                    $batch->addLog('info', "Mapping area (DELETE): {$key} → {$areaForDelete}");
                }
            }

            // ========================
            // 2️⃣ HAPUS DATA LAMA
            // ========================
            $batch->addLog('info', "Menghapus data lama untuk periode " . $startDate->format('F Y') . "...");

            $deleteQuery = DB::table('t_sellingout')
                ->whereBetween('INVOICE_DATE', [$startDate, $endDate]);

            if (!empty($regionForDelete)) {
                $deleteQuery->where('REGION', $regionForDelete);
                $batch->addLog('info', "Filter delete: REGION = {$regionForDelete}");
            }
            if (!empty($areaForDelete)) {
                $deleteQuery->where('AREA', $areaForDelete);
                $batch->addLog('info', "Filter delete: AREA = {$areaForDelete}");
            }
            if (!empty($distributorFilter)) {
                $deleteQuery->where('KDDIST', $distributorFilter);
                $batch->addLog('info', "Filter delete: Distributor = {$distributorFilter}");
            }

            $deletedRows = $deleteQuery->delete();
            $batch->addLog('success', "Berhasil menghapus {$deletedRows} data lama.");

            // ========================
            // 3️⃣ SIAPKAN FILTER INSERT (kode asli)
            // ========================
            $bindings = [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
            $filterClauses = "WHERE a.invoice_date >= :startDate AND a.invoice_date <= :endDate";

            if (!empty($regionFilterOriginal)) {
                $filterClauses .= " AND ma.region_code = :regionFilter";
                $bindings['regionFilter'] = $regionFilterOriginal;
                $batch->addLog('info', "Filter insert: region_code = {$regionFilterOriginal}");
            }
            if (!empty($areaFilterOriginal)) {
                $filterClauses .= " AND ma.area_code = :areaFilter";
                $bindings['areaFilter'] = $areaFilterOriginal;
                $batch->addLog('info', "Filter insert: area_code = {$areaFilterOriginal}");
            }
            if (!empty($distributorFilter)) {
                $filterClauses .= " AND ma.distributor_code = :distributorFilter";
                $bindings['distributorFilter'] = $distributorFilter;
                $batch->addLog('info', "Filter insert: distributor_code = {$distributorFilter}");
            }

            // ========================
            // 4️⃣ EKSEKUSI QUERY INSERT
            // ========================
            $batch->addLog('info', "Memulai penyisipan {$this->totalRows} baris data baru...");
            
            // [PERHATIAN] Pastikan nama kolom di query SELECT cocok dengan di INSERT
           $sql = "
                INSERT INTO t_sellingout (
                    \"BLN\", \"WEEK\", \"THN\", \"REGION\", \"AREA\", \"KDSPV\", \"SPV\", \"CABANG\", \"KDDIST\", \"DIST\", 
                    \"INVOICE_NO\", \"INVOICE_DATE\", \"SLSNO_PRC\", \"SLSNO\", \"SLSNAME\", \"KDUNIQ\", 
                    \"CUSTNOPRC\", \"CUSTNO\", \"CUSTNAME\", \"ALAMAT\", \"TIPE\", \"TIPE_BLN\", \"AO_NOO\",
                    \"KDITEMDST\", \"NAMAITEMDST\", \"KDITEMPRC\", \"NAMAITEMPRC\", \"REG_FEST\", \"KATEGORI\", 
                    \"TOPITEM\", \"SUBBRAND\", \"QTYBASE\", \"KTN\", \"PAK\", \"PCS\", \"QTY\", \"SATUAN\", \"TTL_QTY_PCS\", 
                    \"TTL_QTY_KTN\", \"QTY_BNS_PCS\", \"FLAG_BNS\", \"PRICE\", \"GROSS\", 
                    \"DISC_1\", \"DISC_2\", \"DISC_3\", \"DISC_4\", 
                    \"DISC_5\", \"DISC_6\", \"DISC_7\", \"DISC_8\", 
                    \"TOTAL_DISC\", \"DPP\", \"TAX\", \"NETT\", created_at, updated_at
                )
                WITH 
                tipeagg AS (
                    SELECT 
                        distributor_code, customer_code,
                        CASE 
                            WHEN SUM(net_amount) > 10000000 THEN 'SO'
                            WHEN SUM(net_amount) > 5000000 THEN 'G'
                            WHEN SUM(net_amount) > 3000000 THEN 'SG'
                            ELSE 'R'
                        END AS tipe_agg
                    FROM sales_invoice_distributor
                    WHERE invoice_date BETWEEN :startDate AND :endDate
                    GROUP BY distributor_code, customer_code
                ),
                produkmap AS (
                    SELECT DISTINCT ON (distributor_code, product_code_dist)
                        distributor_code, product_code_dist, product_code_prc
                    FROM product_mappings
                    ORDER BY 
                        distributor_code, product_code_dist,
                        product_code_prc DESC 
                ),
                base AS (
                    SELECT 
                        a.*,
                        ma.region_name, ma.area_name, ma.supervisor_code AS kdspv, ma.supervisor_name AS spv, ma.branch_name, ma.distributor_name AS dist,
                        ms.salesman_code_prc,
                        mc.\"CUSTNO\" AS custnoprc,
                        ro.\"TIPE\" AS tipe_ro,
                        mp.product_code_prc, mpk.divisi AS namaitemprc, mpk.produk_line AS reg_fest, 
                        mpk.kategory, mpk.topitem, mpk.subbrand, mpk.crttopcs, mpk.packtopcs,
                        ta.tipe_agg
                    FROM sales_invoice_distributor a
                    LEFT JOIN master_distributors ma ON a.distributor_code = ma.distributor_code  
                    LEFT JOIN (
                        SELECT DISTINCT ON (distributor_code, salesman_code_dist) *
                        FROM salesman_mappings
                        ORDER BY distributor_code, salesman_code_dist, salesman_code_prc DESC
                    ) ms ON ms.distributor_code = ma.distributor_code AND ms.salesman_code_dist = a.salesman_code
                    LEFT JOIN (
                        SELECT DISTINCT ON (\"KDCABANG\", \"CUSTNO_DIST\") *
                        FROM mapping_cust
                        ORDER BY \"KDCABANG\", \"CUSTNO_DIST\", \"CUSTNO\" DESC 
                    ) mc ON mc.\"KDCABANG\" = ma.distributor_code AND mc.\"CUSTNO_DIST\" = a.customer_code
                    LEFT JOIN (
                        SELECT DISTINCT ON (\"KDCABANG\", \"CUSTNONEW\") *
                        FROM _lama
                        ORDER BY \"KDCABANG\", \"CUSTNONEW\", \"TIPE\" DESC 
                    ) ro ON ro.\"KDCABANG\" = ma.distributor_code AND ro.\"CUSTNONEW\" = a.customer_code
                    LEFT JOIN produkmap mp ON ma.distributor_code = mp.distributor_code AND a.product_code = mp.product_code_dist
                    LEFT JOIN master_produk_lama mpk ON mp.product_code_prc = mpk.pcode_prc
                    LEFT JOIN tipeagg ta ON ta.distributor_code = a.distributor_code AND ta.customer_code = a.customer_code
                    $filterClauses
                )
                SELECT
                    EXTRACT(MONTH FROM b.invoice_date)::int AS bln,
                    (EXTRACT(WEEK FROM b.invoice_date)::int - EXTRACT(WEEK FROM date_trunc('month', b.invoice_date))::int + 1) AS week_in_month,
                    EXTRACT(YEAR FROM b.invoice_date)::int AS thn,
                    b.region_name, b.area_name, b.kdspv, b.spv, b.branch_name, b.distributor_code AS kddist, b.dist,
                    b.invoice_no, b.invoice_date,
                    b.salesman_code_prc, b.salesman_code, b.salesman_name,
                    concat(substring(b.distributor_code, 3, 3), '-', b.customer_code) AS kduniq,
                    b.custnoprc, b.customer_code,
                    regexp_replace(b.customer_name, '[\r\n\t]', '', 'g') AS custname,
                    regexp_replace(
                        COALESCE(NULLIF(trim(b.address), ''), b.branch_name),
                        '[\r\n\t]', '', 'g'
                    ) AS alamat,
                    COALESCE(b.tipe_ro, b.tipe_agg, 'R') AS tipe,
                    b.tipe_agg AS tipe_bln,
                    CASE WHEN b.tipe_ro IS NOT NULL THEN 'AO' ELSE 'NOO' END AS ao_noo,
                    b.product_code AS kditemdst, b.product_name AS namaitemdst,
                    b.product_code_prc, b.namaitemprc, b.reg_fest, b.kategory, b.topitem, b.subbrand,
                    b.crttopcs AS qtybase, b.carton_qty, b.pack_qty, b.pcs_qty, b.quantity, b.unit,
                    (
                        COALESCE(b.carton_qty, 0) * COALESCE(b.crttopcs, 0) +
                        COALESCE(b.pack_qty, 0) * COALESCE(b.packtopcs, 0) +
                        COALESCE(b.pcs_qty, 0) +
                        CASE
                            WHEN upper(b.unit) IN ('CARTON','CRT','CTN','DUS','KARTON','KRT','KRT01','KRT10','KRT12','KRT18','KRT2','KRT24','KRT3','KRT4','KRT6','KRT8','KRTN','KTN') THEN COALESCE(b.quantity, 0) * COALESCE(b.crttopcs, 0)
                            WHEN upper(b.unit) IN ('BALL','BOX','PACK','PAK','PCK','PRES','RTG','RCG','BAL','DOS','PK','RENCENG','PRESS') THEN COALESCE(b.quantity, 0) * COALESCE(b.packtopcs, 0)
                            WHEN upper(b.unit) IN ('BKS','BUAH','PCS','PLS','TIN','TOP','PC','JAR','BALL') THEN COALESCE(b.quantity, 0)
                            ELSE 0
                        END
                    ) AS ttl_qty_pcs,
                    ROUND((
                        (
                            COALESCE(b.carton_qty, 0) * COALESCE(b.crttopcs, 0) +
                            COALESCE(b.pack_qty, 0) * COALESCE(b.packtopcs, 0) +
                            COALESCE(b.pcs_qty, 0) +
                            CASE
                                WHEN upper(b.unit) IN ('CARTON','CRT','CTN','DUS','KARTON','KRT','KRT01','KRT10','KRT12','KRT18','KRT2','KRT24','KRT3','KRT4','KRT6','KRT8','KRTN','KTN') THEN COALESCE(b.quantity, 0) * COALESCE(b.crttopcs, 0)
                                WHEN upper(b.unit) IN ('BALL','BOX','PACK','PAK','PCK','PRES','RTG','RCG','BAL','DOS','PK','RENCENG','PRESS') THEN COALESCE(b.quantity, 0) * COALESCE(b.packtopcs, 0)
                                WHEN upper(b.unit) IN ('BKS','BUAH','PCS','PLS','TIN','TOP','PC','JAR','BALL') THEN COALESCE(b.quantity, 0)
                            ELSE 0
                        END
                        ) / NULLIF(COALESCE(b.crttopcs, 1), 0) -- Hindari divide by zero
                    )::numeric, 4) AS ttl_qty_ktn,
                    CASE 
                        WHEN b.is_bonus = true AND COALESCE(b.bonus, 0) = 0 THEN
                            (
                                COALESCE(b.carton_qty, 0) * COALESCE(b.crttopcs, 0) +
                                COALESCE(b.pack_qty, 0) * COALESCE(b.packtopcs, 0) +
                                COALESCE(b.pcs_qty, 0)
                            )
                        ELSE b.bonus
                    END AS qty_bns_pcs,
                    CASE WHEN b.is_bonus = true THEN 'Y' ELSE 'N' END AS flag_bns, 
                    b.unit_price, b.gross_amount,
                    COALESCE(b.discount1, 0) AS disc_dist1,
                    COALESCE(b.discount2, 0) AS disc_dist2,
                    COALESCE(b.discount3, 0) AS disc_dist3,
                    COALESCE(b.discount4, 0) AS disc_dist4,
                    COALESCE(b.discount5, 0) AS disc_prc1,
                    COALESCE(b.discount6, 0) AS disc_prc2,
                    COALESCE(b.discount7, 0) AS disc_prc3,
                    0 AS disc_prc4,
                    COALESCE(b.total_discount, 0) AS total_disc,
                    CASE 
                        WHEN b.dpp = 0 THEN b.net_amount - 
                            (CASE WHEN b.tax = 0 THEN b.net_amount * 0.11 ELSE b.tax END)
                        ELSE b.dpp
                    END AS dpp,
                    CASE WHEN b.tax = 0 THEN b.net_amount * 0.11 ELSE b.tax END AS tax,
                    b.net_amount,
                    NOW(), NOW() -- created_at, updated_at
                FROM base b;
            ";

            DB::statement($sql, $bindings);

            // 4. Selesai
            $batch->addLog('success', "PROSES SELESAI: {$this->totalRows} baris data berhasil diproses.");
            $batch->updateStatus('completed');
            
        } catch (Throwable $e) {
            $batch->addLog('error', 'Proses Insert Gagal: ' . $e->getMessage());
            $batch->updateStatus('failed');
        }
    }
}

