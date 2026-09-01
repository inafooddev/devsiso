<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Throwable;

class JoinSoEskaNonEksaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $batchId;
    public $month;
    public $year;

    public function __construct($batchId, $month, $year)
    {
        $this->batchId = $batchId;
        $this->month = $month;
        $this->year = $year;
    }

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        try {
            $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth()->toDateString();

            $batch->addLog('info', "Memulai Job SO Eska & Non Eska untuk periode: $startDate s/d $endDate");

            // Tahap 1: DELETE DATA
            $batch->addLog('warning', "Tahap 1: Menghapus data lama di so_eska_n_noneska...");
            $deleteQuery = "DELETE FROM so_eska_n_noneska WHERE invoice_date BETWEEN ? AND ?";
            DB::connection('pgsql')->statement($deleteQuery, [$startDate, $endDate]);
            $batch->addLog('success', "Tahap 1 Selesai: Data lama terhapus.");

            // Tahap 2: UPDATE TRIM PRD CODE
            $batch->addLog('warning', "Tahap 2: Membersihkan prd_code (TRIM) di selling_out_eskalink...");
            $updateQuery = "UPDATE selling_out_eskalink SET prd_code = trim(prd_code) WHERE invoice_date BETWEEN ? AND ?";
            DB::connection('pgsql')->statement($updateQuery, [$startDate, $endDate]);
            $batch->addLog('success', "Tahap 2 Selesai: prd_code berhasil di-trim.");

            // Tahap 3: INSERT ESKA
            $batch->addLog('warning', "Tahap 3: Memasukkan data ESKA...");
            $insertEskaQuery = "
                INSERT INTO so_eska_n_noneska
                (region_code, region_name, entity_code, entity_name, branch_code, branch_name, area_code, area_name, sales_code, sales_name,
                cust_code_prc, cust_code_dist, cust_name, cust_address, cust_city, sub_channel, type_outlet, ord_no, ord_date, invoice_no, invoice_type,
                invoice_date, prd_brand, product_group_1, product_group_2, product_group_3, prd_code, prd_name, qty1_car, qty2_pck, qty3_pcs, qty4_pcs, qty5_pcs,
                flag_bonus, gross_amount, line_discount_1, line_discount_2, line_discount_3, line_discount_4, line_discount_5, line_discount_6, line_discount_7,
                line_discount_8, total_line_discount, dpp, tax, nett_amount, category_item, vtkp, npd, base_qty, ket)
                SELECT 
                    soe.region_code, soe.region_name, soe.entity_code, soe.entity_name, soe.branch_code, soe.branch_name, soe.area_code, soe.area_name, soe.sales_code, soe.sales_name,
                    soe.cust_code_prc, soe.cust_code_dist, soe.cust_name, soe.cust_address, soe.cust_city, soe.sub_channel, soe.type_outlet, soe.ord_no, soe.ord_date, soe.invoice_no, soe.invoice_type,
                    soe.invoice_date, soe.prd_brand, soe.product_group_1, soe.product_group_2, soe.product_group_3, soe.prd_code, soe.prd_name,
                    (soe.qty3_pcs / NULLIF(pm.base_unit, '')::numeric) AS qty1_ctn,
                    soe.qty2_pck, soe.qty3_pcs, soe.qty4_pcs, soe.qty5_pcs, soe.flag_bonus, soe.gross_amount, soe.line_discount_1, soe.line_discount_2, soe.line_discount_3, soe.line_discount_4, soe.line_discount_5, soe.line_discount_6, soe.line_discount_7,
                    soe.line_discount_8, soe.total_line_discount, soe.dpp, soe.tax, soe.nett_amount, soe.category_item, soe.vtkp, soe.npd,
                    NULLIF(pm.base_unit, '')::numeric AS base_qty, 'eska'
                FROM selling_out_eskalink soe
                LEFT JOIN product_masters pm ON soe.prd_code = pm.product_id
                WHERE soe.invoice_date BETWEEN ? AND ?
            ";
            DB::connection('pgsql')->statement($insertEskaQuery, [$startDate, $endDate]);
            $batch->addLog('success', "Tahap 3 Selesai: Data ESKA berhasil dimasukkan.");

            // Tahap 4: INSERT NON ESKA (WITH CTE FILTER)
            $batch->addLog('warning', "Tahap 4: Memasukkan data NON ESKA...");
            $insertNonEskaQuery = "
                WITH data_eskalink AS (
                    SELECT DISTINCT
                        soe_eska.branch_code,
                        die.distributor_code
                    FROM selling_out_eskalink soe_eska
                    LEFT JOIN distributor_implementasi_eskalink die
                        ON soe_eska.branch_code = die.eskalink_code
                    WHERE soe_eska.invoice_date BETWEEN ? AND ?
                )
                INSERT INTO so_eska_n_noneska 
                (region_code, region_name, entity_code, entity_name, branch_code, branch_name, area_code, area_name, sales_code, sales_name,
                cust_code_prc, cust_code_dist, cust_name, cust_address, cust_city, sub_channel, type_outlet, ord_no, ord_date, invoice_no, invoice_type,
                invoice_date, prd_brand, product_group_1, product_group_2, product_group_3, prd_code, prd_name, qty1_car, qty2_pck, qty3_pcs, qty4_pcs, qty5_pcs,
                flag_bonus, gross_amount, line_discount_1, line_discount_2, line_discount_3, line_discount_4, line_discount_5, line_discount_6, line_discount_7,
                line_discount_8, total_line_discount, dpp, tax, nett_amount, category_item, vtkp, npd, base_qty, ket)
                SELECT 
                    soe.region_code, soe.region_name, soe.entity_code, soe.entity_name, soe.branch_code, soe.branch_name, soe.area_code, soe.area_name, soe.sales_code, soe.sales_name,
                    soe.cust_code_prc, soe.cust_code_dist, soe.cust_name, soe.cust_address, soe.cust_city, soe.sub_channel, soe.type_outlet, soe.ord_no, soe.ord_date, soe.invoice_no, soe.invoice_type,
                    soe.invoice_date, soe.prd_brand,
                    CASE WHEN soe.product_group_1 = 'IFEST' THEN 'INA FESTIVE' ELSE 'INA REGULAR' END,
                    CASE WHEN soe.product_group_1 = 'IFEST' THEN 'IFES' ELSE 'IREG' END,
                    soe.product_group_3, soe.prd_code, soe.prd_name,
                    (soe.qty3_pcs / NULLIF(pm.base_unit, '')::numeric) AS qty1_ctn,
                    soe.qty2_pck, soe.qty3_pcs, soe.qty4_pcs, soe.qty5_pcs, soe.flag_bonus, soe.gross_amount, soe.line_discount_1, soe.line_discount_2, soe.line_discount_3, soe.line_discount_4, soe.line_discount_5, soe.line_discount_6, soe.line_discount_7,
                    soe.line_discount_8, soe.total_line_discount, soe.dpp, soe.tax, soe.nett_amount, soe.category_item, soe.vtkp, soe.npd,
                    NULLIF(pm.base_unit, '')::numeric AS base_qty, 'non_eska'
                FROM detail_sell_out soe
                LEFT JOIN product_masters pm ON soe.prd_code = pm.product_id
                LEFT JOIN data_eskalink de ON soe.branch_code = de.distributor_code
                WHERE soe.invoice_date BETWEEN ? AND ?
                  AND de.distributor_code IS NULL
            ";
            DB::connection('pgsql')->statement($insertNonEskaQuery, [$startDate, $endDate, $startDate, $endDate]);
            $batch->addLog('success', "Tahap 4 Selesai: Data NON ESKA berhasil dimasukkan.");

            // Tahap 5: UPDATE PRODUK HARDCODE
            $batch->addLog('warning', "Tahap 5: Menjalankan Update Produk IFES/INA FESTIVE (Hardcode)...");
            $updateProduct1 = "
                UPDATE so_eska_n_noneska 
                SET product_group_2 = 'IFES'
                WHERE invoice_date BETWEEN '2025-11-01' AND '2026-03-31'
                  AND prd_code = '1-FS-005'
            ";
            $updateProduct2 = "
                UPDATE so_eska_n_noneska 
                SET product_group_1 = 'INA FESTIVE'
                WHERE invoice_date BETWEEN '2025-11-01' AND '2026-03-31'
                  AND prd_code = '1-FS-005'
            ";
            DB::connection('pgsql')->statement($updateProduct1);
            DB::connection('pgsql')->statement($updateProduct2);
            $batch->addLog('success', "Tahap 5 Selesai: Update Produk berhasil.");

            $batch->addLog('success', "SELURUH PROSES BERHASIL DISELESAIKAN!");
            $batch->update(['status' => 'completed']);
        } catch (Throwable $e) {
            $batch->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $batch->update(['status' => 'failed']);
            throw $e;
        }
    }
}
