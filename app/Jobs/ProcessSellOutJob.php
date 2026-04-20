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

class ProcessSellOutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchId;
    protected $filters;
    protected $totalRows; // [DITAMBAHKAN]

    /**
     * [PERUBAHAN] Menerima $totalRowsToProcess dari job validasi
     */
    public function __construct(int $batchId, array $filters, int $totalRowsToProcess)
    {
        $this->batchId = $batchId;
        $this->filters = $filters;
        $this->totalRows = $totalRowsToProcess; // [DITAMBAHKAN]
    }

    public function handle()
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        try {
            $batch->updateStatus('processing');
            
            $startDate = Carbon::create($this->filters['yearFilter'], $this->filters['monthFilter'], 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            // 1. Hapus data lama [PERUBAHAN LOGIKA DELETE]
            $batch->addLog('info', "Menghapus data lama untuk periode " . $startDate->format('F Y') . "...");
            
            // Mulai query delete dengan filter tanggal
            $deleteQuery = DB::table('detail_sell_out')
                            ->whereBetween('invoice_date', [$startDate, $endDate]);

            // [PERUBAHAN] Tambahkan filter lain jika ada
            if (!empty($this->filters['regionFilter'])) {
                $deleteQuery->where('region_code', $this->filters['regionFilter']);
                $batch->addLog('info', "Filter delete: Region " . $this->filters['regionFilter']);
            }
            if (!empty($this->filters['areaFilter'])) {
                $deleteQuery->where('entity_code', $this->filters['areaFilter']);
                $batch->addLog('info', "Filter delete: Area " . $this->filters['areaFilter']);
            }
            if (!empty($this->filters['distributorFilter'])) {
                // Asumsi 'entity_code' di 'detail_sell_out' adalah 'distributor_code'
                $deleteQuery->where('branch_code', $this->filters['distributorFilter']);
                $batch->addLog('info', "Filter delete: Distributor " . $this->filters['distributorFilter']);
            }

            $deletedRows = $deleteQuery->delete();
            $batch->addLog('success', "Berhasil menghapus {$deletedRows} data lama.");
            
            // 2. Siapkan binding filter
            $bindings = [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
            $filterClauses = "AND a.invoice_date >= :startDate AND a.invoice_date <= :endDate";

            if (!empty($this->filters['regionFilter'])) {
                $filterClauses .= " AND b.region_code = :regionFilter";
                $bindings['regionFilter'] = $this->filters['regionFilter'];
            }
            if (!empty($this->filters['areaFilter'])) {
                $filterClauses .= " AND b.area_code = :areaFilter";
                $bindings['areaFilter'] = $this->filters['areaFilter'];
            }
            if (!empty($this->filters['distributorFilter'])) {
                $filterClauses .= " AND b.distributor_code = :distributorFilter";
                $bindings['distributorFilter'] = $this->filters['distributorFilter'];
            }
            
            // 3. Bangun dan jalankan query INSERT ... SELECT
            $batch->addLog('info', "Memulai penyisipan {$this->totalRows} baris data baru...");
            
            // [PERHATIAN] Pastikan nama kolom di query SELECT cocok dengan di INSERT
            $sql = "
                INSERT INTO detail_sell_out (
                    region_code, region_name, entity_code, entity_name, branch_code, branch_name,
                    area_code, area_name, sales_code, sales_name,
                    cust_code_prc, cust_code_dist, cust_name, cust_address, cust_city,
                    sub_channel, type_outlet,
                    ord_no, ord_date, invoice_no, invoice_type, invoice_date,
                    prd_brand, product_group_1, product_group_2, product_group_3,
                    prd_code, prd_name,
                    qty1_car, qty2_pck, qty3_pcs, qty4_pcs, qty5_pcs,
                    flag_bonus, gross_amount,
                    line_discount_1, line_discount_2, line_discount_3, line_discount_4,
                    line_discount_5, line_discount_6, line_discount_7, line_discount_8,
                    total_line_discount, dpp, tax, nett_amount,
                    category_item, vtkp, npd, created_at, updated_at
                )
                WITH BaseData AS (
                    SELECT
                        b.region_code, b.region_name, b.area_code, b.area_name, 
                        b.distributor_code, 
                        b.distributor_name, b.supervisor_code, b.supervisor_name, b.branch_name,
                        a.salesman_name, a.customer_code,cm.customer_code_prc, a.customer_name, a.address, 
                        a.order_no, a.order_date, a.invoice_no, a.invoice_type, a.invoice_date, 
                        a.net_amount, a.gross_amount, a.discount1, a.discount2, a.discount3, 
                        a.discount4, a.discount5, a.discount6, a.discount7, a.discount8, 
                        a.total_discount, a.dpp, a.tax, a.unit, a.quantity, a.carton_qty, 
                        a.pack_qty, a.pcs_qty, a.salesman_code, a.product_code,
                        sm.salesman_code_prc,
                        d.brand_name, d.line_id, d.line_name, d.sub_brand_name, 
                        d.product_id, d.product_name, d.conv_unit1, d.conv_unit3, d.brand_unit_name,
                        cat.VTKP, cat.NPD,
                        (
                            COALESCE(a.carton_qty, 0) * COALESCE(d.conv_unit1, 0) +
                            COALESCE(a.pack_qty, 0) * COALESCE(d.conv_unit3, 0) +
                            COALESCE(a.pcs_qty, 0) +
                            CASE
                                WHEN UPPER(a.unit) IN ('CARTON','CRT','CTN','DUS','KARTON','KRT','KRT01','KRT10','KRT12','KRT18','KRT2','KRT24','KRT3','KRT4','KRT6','KRT8','KRTN','KTN')
                                    THEN COALESCE(a.quantity, 0) * COALESCE(d.conv_unit1, 0)
                                WHEN UPPER(a.unit) IN ('BALL','BOX','PACK','PAK','PCK','PRES','RTG','RCG','BAL','DOS','PK','RENCENG','PRESS')
                                    THEN COALESCE(a.quantity, 0) * COALESCE(d.conv_unit3, 0)
                                WHEN UPPER(a.unit) IN ('BKS','BUAH','PCS','PLS','TIN','TOP','PC','JAR','BALL')
                                    THEN COALESCE(a.quantity, 0)
                                ELSE 0
                            END
                        ) AS total_qty_in_pcs
                    FROM sales_invoice_distributor AS a
                    LEFT JOIN master_distributors AS b 
                        ON b.distributor_code = a.distributor_code
                    LEFT JOIN salesman_mappings AS sm 
                        ON a.salesman_code = sm.salesman_code_dist 
                        AND b.distributor_code = sm.distributor_code
                    LEFT JOIN (
					    SELECT DISTINCT ON (die.distributor_code, cme.custno_dist)
						    die.distributor_code as distributor_code,
						    cme.custno_dist as customer_code_dist,
						    cme.custno as customer_code_prc
						FROM customer_map_eska cme
						LEFT JOIN distributor_implementasi_eskalink die 
						    ON die.eskalink_code = cme.branch
						ORDER BY 
						    die.distributor_code,
						    cme.custno_dist,
						    cme.custno  
					) cm 
					    ON a.customer_code = cm.customer_code_dist 
					    AND b.distributor_code = cm.distributor_code
                    LEFT JOIN (
                        SELECT 
                            distributor_code, 
                            product_code_dist, 
                            MIN(product_code_prc) AS product_code_prc
                        FROM product_mappings 
                        GROUP BY distributor_code, product_code_dist
                    ) c 
                        ON a.distributor_code = c.distributor_code 
                        AND a.product_code = c.product_code_dist 
                    LEFT JOIN product_masters d 
                        ON c.product_code_prc = d.product_id 
                    LEFT JOIN (
                        SELECT 
                            pc.product_id,
                            MAX(CASE WHEN pc.category_id = 'VTKP' THEN 'VTKP' ELSE 'NON VTKP' END) AS VTKP,
                            MAX(CASE WHEN pc.category_id = 'NPD'  THEN 'NPD' ELSE 'NON NPD' END) AS NPD
                        FROM product_categories pc
                        GROUP BY pc.product_id
                    ) cat 
                        ON cat.product_id = d.product_id
                    WHERE 1=1 $filterClauses
                )
                SELECT 
                    COALESCE(region_code, '-') AS region_code,
                    COALESCE(region_name, '-') AS region_name,
                    COALESCE(area_code, '-') AS entity_code,
                    COALESCE(area_name, '-') AS entity_name,
                    COALESCE(distributor_code, '-') AS branch_code,
                    COALESCE(distributor_name, '-') AS branch_name,
                    COALESCE(supervisor_code, '-') AS area_code,
                    COALESCE(supervisor_name, '-') AS area_name,
                    COALESCE(salesman_code_prc, '-') AS sales_code,
                    COALESCE(salesman_name, '-') AS sales_name,
                    COALESCE(customer_code_prc, '-') AS cust_code_prc,
                    COALESCE(customer_code, '-') AS cust_code_dist,
                    COALESCE(customer_name, '-') AS cust_name,
                    COALESCE(address, '-') AS cust_address,
                    COALESCE(branch_name, '-') AS cust_city,
                    COALESCE('-', '-') AS sub_channel,
                    COALESCE('-', '-') AS type_outlet,
                    COALESCE(order_no, '-') AS ord_no,
                    COALESCE(order_date, CURRENT_DATE) AS ord_date,
                    COALESCE(invoice_no, '-') AS invoice_no,
                    COALESCE(invoice_type, '-') AS invoice_type,
                    COALESCE(invoice_date, CURRENT_DATE) AS invoice_date,
                    COALESCE(brand_name, '-') AS prd_brand,
                    COALESCE(line_id, '-') AS product_group_1,
                    COALESCE(line_name, '-') AS product_group_2,
                    COALESCE(sub_brand_name, '-') AS product_group_3,
                    COALESCE(product_id, '-') AS prd_code,
                    COALESCE(product_name, '-') AS prd_name,
                    COALESCE(
                        CASE 
                            WHEN net_amount = 0 THEN 0
                            ELSE ROUND((total_qty_in_pcs) / NULLIF(COALESCE(conv_unit1, 0), 0), 4)
                        END, 0
                    ) AS qty1_car,
                    0 AS qty2_pck,
                    COALESCE(
                        CASE 
                            WHEN net_amount = 0 THEN 0
                            ELSE total_qty_in_pcs
                        END, 0
                    ) AS qty3_pcs,
                    0 AS qty4_pcs,
                    0 AS qty5_pcs,
                    -- [PERBAIKAN] Mengganti true/false menjadi 'Y'/'N'
                    CASE when coalesce(net_amount,0)=0 THEN 'Y' ELSE 'N' END AS flag_bonus,
                    COALESCE(gross_amount, 0) AS gross_amount,
                    COALESCE(discount1, 0) AS line_discount_1,
                    COALESCE(discount2, 0) AS line_discount_2,
                    COALESCE(discount3, 0) AS line_discount_3,
                    COALESCE(discount4, 0) AS line_discount_4,
                    COALESCE(discount5, 0) AS line_discount_5,
                    COALESCE(discount6, 0) AS line_discount_6,
                    COALESCE(discount7, 0) AS line_discount_7,
                    CASE 
                        WHEN COALESCE(net_amount, 0) = 0 AND total_qty_in_pcs != 0
                            THEN total_qty_in_pcs 
                        ELSE COALESCE(discount8, 0) 
                    END AS line_discount_8,
                    COALESCE(total_discount, 0) AS total_line_discount,
                    COALESCE(dpp, 0) AS dpp,
                    COALESCE(tax, 0) AS tax,
                    COALESCE(net_amount, 0) AS nett_amount,
                    COALESCE(brand_unit_name, '-') AS category_item,
                    COALESCE(VTKP, 'NON VTKP') AS vtkp,
                    COALESCE(NPD, 'NON NPD')  AS npd,
                    NOW(), NOW() -- created_at, updated_at
                FROM BaseData;
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

