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
            // Tahap 6: UPDATE PRODUCT GROUP 3 & VTKP
            $batch->addLog('warning', "Tahap 6: Menjalankan Update Product Group 3 & VTKP...");
            
            $queries = [
                // GOODBIS 36
                "UPDATE so_eska_n_noneska SET product_group_3 = 'GOODBIS CREAM 36' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('36-BC-033','36-BC-035','36-BC-032','36-BC-021','36-BC-020','36-BC-023','36-BC-034','36-BC-036','36-BC-024','36-BC-022','36-BC-010','36-BC-008','36-BC-012')",
                // HM 36
                "UPDATE so_eska_n_noneska SET product_group_3 = 'HITAM MANIS CREAM 36' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('36-BC-031','36-BC-027','36-BC-030','36-BC-028','36-BC-017','36-BC-026','36-BC-025','36-BC-015','36-BC-016','36-BC-029','36-BC-018','36-BC-002','36-BC-006','36-BC-014','36-BC-013','36-BC-019','36-BC-001','36-BC-007','36-BC-004')",
                // OKB KLP & COK 28
                "UPDATE so_eska_n_noneska SET product_group_3 = 'OKEBIS BISKUIT 28' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('28-BC-001','28-BC-004','28-BC-002','28-BC-014','28-BC-015')",
                // OKB JAHE 28
                "UPDATE so_eska_n_noneska SET product_group_3 = 'OKEBIS JAHE 28' WHERE invoice_date BETWEEN ? AND ? AND prd_code = '28-BC-003'",
                // OKB CREAM 28
                "UPDATE so_eska_n_noneska SET product_group_3 = 'OKEBIS CREAM 28' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('28-BC-009','28-BC-010')",
                // FORTIUS WAFER 10
                "UPDATE so_eska_n_noneska SET product_group_3 = 'FORTIUS WAFER 10' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('10-WB-004','10-WB-005','10-WB-006','10-WB-003','10-WB-001','10-WB-002')",
                // FORTIUS WAFER 30
                "UPDATE so_eska_n_noneska SET product_group_3 = 'FORTIUS WAFER 30' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('30-WB-016','30-WB-019','30-WB-015','30-WB-017','30-WB-018','30-WB-010','30-WB-011','30-WB-014','30-WB-012','30-WB-013','30-WB-001','30-WB-002','30-WB-005','30-WB-004')",
                // OCC 20
                "UPDATE so_eska_n_noneska SET product_group_3 = 'OKEBIS COOKIES 20' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('20-BC-011','20-BC-013','20-BC-009','20-BC-008','20-BC-010','20-BC-004','20-BC-007','20-BC-003','20-BC-002')",
                // OKEBIS CREAM 72
                "UPDATE so_eska_n_noneska SET product_group_3 = 'OKEBIS CREAM 72' WHERE invoice_date BETWEEN ? AND ? AND prd_code IN ('72-BC-002','72-BC-001')",
                // OKEBIS COOKIES 72
                "UPDATE so_eska_n_noneska SET product_group_3 = 'OKEBIS COOKIES 72' WHERE invoice_date BETWEEN ? AND ? AND prd_code = '72-BC-003'",
                // VTKP FLAG
                "UPDATE so_eska_n_noneska SET vtkp = 'VTKP' WHERE invoice_date BETWEEN ? AND ? AND product_group_3 IN ('OKEBIS COOKIES 20', 'OKEBIS CREAM 72', 'OKEBIS CREAM 28', 'OKEBIS BISKUIT 28', 'FORTIUS WAFER 30', 'HITAM MANIS CREAM 36', 'FORTIUS WAFER 10', 'GOODBIS CREAM 36')"
            ];
            
            foreach ($queries as $q) {
                DB::connection('pgsql')->statement($q, [$startDate, $endDate]);
            }
            $batch->addLog('success', "Tahap 6 Selesai: Update Product Group 3 & VTKP berhasil.");

            // Tahap 7: UPDATE BASE QTY & KALKULASI QTY1_CAR
            $batch->addLog('warning', "Tahap 7: Menjalankan Update base_qty & Kalkulasi Ulang qty1_car...");
            $tahap7Queries = [
                // Update base_qty = 20
                "UPDATE so_eska_n_noneska SET base_qty = 20 WHERE prd_code = '20-BC-024'",
                // Update base_qty = 28
                "UPDATE so_eska_n_noneska SET base_qty = 28 WHERE prd_code = '28-KK-CHO'",
                // Update base_qty = 1
                "UPDATE so_eska_n_noneska SET base_qty = 1 WHERE prd_code IN ('3-TL-CCN','3-TL-DRN','3-TL-DRS')",
                // Update base_qty = 6
                "UPDATE so_eska_n_noneska SET base_qty = 6 WHERE prd_code = '6-FS-028'",
                // Kalkulasi ulang qty1_car
                "UPDATE so_eska_n_noneska SET qty1_car = qty3_pcs / base_qty WHERE prd_code IN ('20-BC-024','28-KK-CHO','3-TL-CCN','3-TL-DRN','3-TL-DRS','6-FS-028') AND base_qty > 0"
            ];

            foreach ($tahap7Queries as $t7q) {
                DB::connection('pgsql')->statement($t7q);
            }
            $batch->addLog('success', "Tahap 7 Selesai: Update base_qty dan Kalkulasi berhasil.");

            // CALCULATE TOTAL NETTO
            $totalNetto = DB::connection('pgsql')->selectOne("
                SELECT SUM(nett_amount) as total 
                FROM so_eska_n_noneska 
                WHERE invoice_date BETWEEN ? AND ?
            ", [$startDate, $endDate])->total ?? 0;

            $batch->addLog('info', "TOTAL NETTO Hasil Join: Rp " . number_format((float)$totalNetto, 0, ',', '.'));
            $batch->addLog('success', "SELURUH PROSES BERHASIL DISELESAIKAN!");
            $batch->update(['status' => 'completed']);
        } catch (Throwable $e) {
            $batch->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $batch->update(['status' => 'failed']);
            throw $e;
        }
    }
}
