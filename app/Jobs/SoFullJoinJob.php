<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class SoFullJoinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $month;
    public $year;
    public $isChained;
    public $timeout = 3600;

    public function __construct($batchId = null, $month = null, $year = null, $isChained = false)
    {
        $this->batchId = $batchId;
        $this->month = $month;
        $this->year = $year;
        $this->isChained = $isChained;
    }

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if ($batch) {
            $batch->addLog('info', "Memulai eksekusi SO Full Join untuk periode: {$this->month}-{$this->year}");
        }

        // 1. Setup Variabel Dinamis
        $nmrBln = (int) $this->month; 
        $tahun = (int) $this->year; 
        $strMonth = str_pad($this->month, 2, '0', STR_PAD_LEFT); 
        
        $contoh_0326 = $strMonth . substr((string)$tahun, -2); // e.g. 0826
        $tglMulai = "{$tahun}-{$strMonth}-01"; // e.g. 2026-08-01

        if ($batch) {
            $batch->addLog('info', "Parameter SQL ter-generate: TGL_MULAI='{$tglMulai}', NMR_BLN='{$nmrBln}', TAHUN='{$tahun}', contoh='{$contoh_0326}'");
            $batch->addLog('warning', "Melakukan TRUNCATE pada tabel t_tempsell (PostgreSQL)...");
        }

        // 2. Truncate tabel tujuan
        $countBefore = DB::connection('pgsql')->table('t_tempsell')->count();
        DB::connection('pgsql')->table('t_tempsell')->truncate();

        if ($batch) {
            $batch->addLog('info', "Tabel t_tempsell berhasil dikosongkan ({$countBefore} baris terhapus). Menjalankan query INSERT INTO SELECT secara native (Zero-Memory)...");
        }

        // 3. Raw Query
        $query = <<<SQL
INSERT INTO t_tempsell (
    "COM_ID", "BLN", "THN", "RSM", "REGION", "AREA", "KD_CABANG", "CABANG",
    "KD_DISTRIBUTOR", "DISTRIBUTOR", "KD_SPV", "SPV_NAME", "KD SALES", "SALES_NAME",
    "UNIQKD_TOKO", "KD_TOKO", "NAMA_TOKO", "ALAMAT", "BASE_TIPE", "TIPE_BLN",
    "NOO", "NMR_NOTA", "TGL_NOTA", "PRODUK", "MAPING_PRODUK", "REG_FEST",
    "KATEGORI", "TOP_ITEM", "BRAND", "SUB_BRAND", "QTY_(KTN)", "VALUE_(NETTO)"
)
SELECT
    SUBSTRING(a."KDDIST" FROM 3 FOR 3) || '{$contoh_0326}' AS "COM_ID",
    '{$tglMulai}'::date AS "BLN",
    a."THN",
    CASE 
        WHEN a."REGION" = 'INA JAWA 1' THEN 'DJOKO SISWANTORO'
        WHEN a."REGION" = 'INA JAWA 2' THEN 'AGUS SUPRIHANTO'
        WHEN a."REGION" = 'INA PULAU 1' THEN 'VACANT PULAU'
        WHEN a."REGION" = 'INA SUMATERA 1' THEN 'VACANT SUMATERA 1'
        WHEN a."REGION" = 'INA SUMATERA 2' THEN 'AGUS SUSANTO'
        WHEN a."REGION" = 'INA REMOTE' THEN 'REMOTE'
        ELSE ''
    END AS "RSM",
    CASE 
        WHEN a."REGION" = 'INA JAWA 1' THEN 'JAWA 1'
        WHEN a."REGION" = 'INA JAWA 2' THEN 'JAWA 2'
        WHEN a."REGION" = 'INA PULAU 1' THEN 'PULAU'
        WHEN a."REGION" = 'INA SUMATERA 1' THEN 'SUMATERA 1'
        WHEN a."REGION" = 'INA SUMATERA 2' THEN 'SUMATERA 2'
        WHEN a."REGION" = 'INA REMOTE' THEN 'REMOTE'
        ELSE ''
    END AS "REGION",
    CASE 
        WHEN a."AREA" ='INA INDO TIMUR' THEN 'INDO TIMUR'
		WHEN a."AREA" ='INA JAWA BARAT' THEN 'JABAR'
		WHEN a."AREA" ='INA LAMPUNG' THEN 'LAMPUNG'
		WHEN a."AREA" ='INA KALIMANTAN' THEN 'KALIMANTAN'
		WHEN a."AREA" ='INA BENGKULU' THEN 'BENGKULU'
		WHEN a."AREA" ='INA JABODETABEK' THEN 'JABODETABEK'
		WHEN a."AREA" ='INA JAWA TIMUR 2' THEN 'JATIM 2'
		WHEN a."AREA" ='INA SUMBAR' THEN 'SUMBAR'
		WHEN a."AREA" ='INA JAWA TENGAH 2' THEN 'JATENG 2'
		WHEN a."AREA" ='INA SULAWESI' THEN 'SULAWESI'
		WHEN a."AREA" ='INA JAWA TIMUR 1' THEN 'JATIM 1'
		WHEN a."AREA" ='INA SUMUT' THEN 'SUMUT'
		WHEN a."AREA" ='INA SUMSEL' THEN 'SUMSEL'
		WHEN a."AREA" ='INA JAWA TENGAH 1' THEN 'JATENG 1'
		WHEN a."AREA" ='INA RIAU' THEN 'RIAU'
		WHEN a."AREA" ='INA KEPRI' THEN 'KEPRI'
		WHEN a."AREA" ='INA NAD' THEN 'NAD'
		WHEN a."AREA" ='INA SULAWESI 2' THEN 'SULAWESI 2'
		WHEN a."AREA" ='INA JAMBI' THEN 'JAMBI'
		
        ELSE a."AREA"
    END AS "AREA",
    SUBSTRING(a."KDDIST" FROM 3 FOR 3) AS "KD_CABANG",
    a."CABANG",
    a."KDDIST" AS "KD_DISTRIBUTOR",
    a."DIST" AS "DISTRIBUTOR",
    a."KDSPV" AS "KD_SPV",
    spv.description,
    a."SLSNO_PRC" AS "KD SALES",
    a."SLSNAME" AS "SALES_NAME",
    a."KDUNIQ" AS "UNIQKD_TOKO",
    a."CUSTNO" AS "KD_TOKO",
    a."CUSTNAME" AS "NAMA_TOKO",
    a."ALAMAT",
    a."TIPE" AS "BASE_TIPE",
    a."TIPE_BLN",
    a."AO_NOO" AS "NOO",
    a."INVOICE_NO" AS "NMR_NOTA",
    a."INVOICE_DATE" AS "TGL_NOTA",
    a."NAMAITEMDST" AS "PRODUK",
    a."NAMAITEMPRC" AS "MAPING_PRODUK",
    d.produk_line as reg_fest,
    d.kategory AS "KATEGORI",
    d.topitem,
    d.brand,
    d.subbrand,
    a."TTL_QTY_KTN" AS "QTY_(KTN)",
    a."NETT" AS "VALUE_(NETTO)"
FROM "t_sellingout" a
LEFT JOIN "master_supervisors" spv ON spv.supervisor_code = a."KDSPV"
LEFT JOIN (
    SELECT *
    FROM (
        SELECT *,
            ROW_NUMBER() OVER (
                PARTITION BY divisi 
                ORDER BY divisi
            ) AS rn
        FROM "master_produk_lama"
    ) ranked_produk
    WHERE rn = 1
) d ON d.divisi = a."NAMAITEMPRC"
WHERE a."BLN"='{$nmrBln}' AND a."THN"='{$tahun}'
SQL;

        try {
            $insertedRows = DB::connection('pgsql')->affectingStatement($query);
            if ($batch) {
                $batch->addLog('success', "Sukses mengeksekusi Job SO Full Join di dalam database secara langsung! Sebanyak {$insertedRows} baris data berhasil masuk.");
                
                // 4. Tahap Pindah ke SQL Server
                $batch->addLog('info', "Memulai Tahap 4: Memindahkan {$insertedRows} baris data dari PostgreSQL (t_tempsell) ke SQL Server (temp_selling_out)...");
                $batch->addLog('warning', "Melakukan TRUNCATE pada tabel temp_selling_out (SQL Server)...");
            }

            // Truncate SQL Server target table
            DB::connection('sqlsrv')->table('temp_selling_out')->truncate();

            // 4. Tahap 4: Pindahkan data dari Postgres (t_tempsell) ke SQL Server (temp_selling_out)
            $totalMigrated = 0;
            $dataToInsert = [];
            
            // MENGGUNAKAN CURSOR: Karena t_tempsell tidak punya Primary Key (ID),
            // menggunakan chunk() dengan orderBy akan melompat-lompat dan menduplikasi/melewatkan data.
            // cursor() mengeksekusi 1 query dan membaca baris satu per satu tanpa membebani memori.
            foreach (DB::connection('pgsql')->table('t_tempsell')->cursor() as $record) {
                // MAPPING EKSPLISIT: Untuk menjamin urutan array selalu sama persis dan tidak bergeser
                // saat Laravel melakukan compile SQL untuk batch insert 50 baris sekaligus.
                $dataToInsert[] = [
                    'COM_ID' => $record->COM_ID,
                    'BLN' => $record->BLN,
                    'THN' => $record->THN,
                    'RSM' => $record->RSM,
                    'REGION' => $record->REGION,
                    'AREA' => $record->AREA,
                    'KD_CABANG' => $record->KD_CABANG,
                    'CABANG' => $record->CABANG,
                    'KD_DISTRIBUTOR' => $record->KD_DISTRIBUTOR,
                    'DISTRIBUTOR' => $record->DISTRIBUTOR,
                    'KD_SPV' => $record->KD_SPV,
                    'SPV_NAME' => $record->SPV_NAME,
                    'KD SALES' => $record->{'KD SALES'},
                    'SALES_NAME' => $record->SALES_NAME,
                    'UNIQKD_TOKO' => $record->UNIQKD_TOKO,
                    'KD_TOKO' => $record->KD_TOKO,
                    'NAMA_TOKO' => $record->NAMA_TOKO,
                    'ALAMAT' => $record->ALAMAT,
                    'BASE_TIPE' => $record->BASE_TIPE,
                    'TIPE_BLN' => $record->TIPE_BLN,
                    'NOO' => $record->NOO,
                    'NMR_NOTA' => $record->NMR_NOTA,
                    'TGL_NOTA' => $record->TGL_NOTA,
                    'PRODUK' => $record->PRODUK,
                    'MAPING_PRODUK' => $record->MAPING_PRODUK,
                    'REG_FEST' => $record->REG_FEST,
                    'KATEGORI' => $record->KATEGORI,
                    'TOP_ITEM' => $record->TOP_ITEM,
                    'BRAND' => $record->BRAND,
                    'SUB_BRAND' => $record->SUB_BRAND,
                    'QTY_(KTN)' => $record->{'QTY_(KTN)'},
                    'VALUE_(NETTO)' => $record->{'VALUE_(NETTO)'},
                ];
                
                // Karena batas parameter SQL Server (2100), kita insert per 50 baris
                if (count($dataToInsert) === 50) {
                    DB::connection('sqlsrv')->table('temp_selling_out')->insert($dataToInsert);
                    $totalMigrated += 50;
                    $dataToInsert = [];
                    
                    // Log tiap kelipatan 5.000 agar UI terminal tidak lag
                    if ($totalMigrated % 5000 === 0 && $batch) {
                        $batch->addLog('info', "Progress SQL Server: {$totalMigrated} baris telah disalin...");
                    }
                }
            }
            
            // Insert sisa baris terakhir
            if (count($dataToInsert) > 0) {
                DB::connection('sqlsrv')->table('temp_selling_out')->insert($dataToInsert);
                $totalMigrated += count($dataToInsert);
            }

            if ($batch) {
                $batch->addLog('success', "Tahap 4 Selesai! Sebanyak {$totalMigrated} baris data berhasil disalin ke SQL Server.");
                
                // 5. Tahap Final: Pindah dari temp_selling_out ke selling_out (SQL Server Native)
                $batch->addLog('info', "Memulai Tahap 5: Memasukkan data ke tabel utama selling_out (SQL Server)...");
                $batch->addLog('warning', "Menghapus (DELETE) data lama di selling_out untuk periode {$tglMulai}...");
            }

            // Hapus data bulan yang sama
            $deletedRows = DB::connection('sqlsrv')->affectingStatement("DELETE FROM selling_out WHERE BLN = ?", [$tglMulai]);

            if ($batch) {
                $batch->addLog('info', "Sebanyak {$deletedRows} baris data lama berhasil dihapus. Memulai proses INSERT INTO SELECT...");
            }

            // Insert native di SQL Server
            $sqlServerQuery = <<<SQL
INSERT INTO selling_out (
    [COM_ID], [BLN], [THN], [RSM], [REGION], [AREA], [KD_CABANG], [CABANG],
    [KD_DISTRIBUTOR], [DISTRIBUTOR], [KD_SPV], [SPV_NAME], [KD SALES], [SALES_NAME],
    [UNIQKD_TOKO], [KD_TOKO], [NAMA_TOKO], [ALAMAT], [BASE_TIPE], [TIPE_BLN],
    [NOO], [NMR_NOTA], [TGL_NOTA], [PRODUK], [MAPING_PRODUK], [REG_FEST],
    [KATEGORI], [TOP_ITEM], [BRAND], [SUB_BRAND], [QTY_(KTN)], [VALUE_(NETTO)]
)
SELECT
    [COM_ID], [BLN], [THN], [RSM], [REGION], [AREA], [KD_CABANG], [CABANG],
    [KD_DISTRIBUTOR], [DISTRIBUTOR], [KD_SPV], [SPV_NAME], [KD SALES], [SALES_NAME],
    [UNIQKD_TOKO], [KD_TOKO], [NAMA_TOKO], [ALAMAT], [BASE_TIPE], [TIPE_BLN],
    [NOO], [NMR_NOTA], [TGL_NOTA], [PRODUK], [MAPING_PRODUK], [REG_FEST],
    [KATEGORI], [TOP_ITEM], [BRAND], [SUB_BRAND], [QTY_(KTN)], [VALUE_(NETTO)]
FROM temp_selling_out
SQL;

            $finalInserted = DB::connection('sqlsrv')->affectingStatement($sqlServerQuery);

            if ($batch) {
                $batch->addLog('success', "Tahap 5 Selesai! Sebanyak {$finalInserted} baris data berhasil masuk ke tabel utama selling_out.");
                
                // 6. Tahap 6: Update Mapping Distributor
                $batch->addLog('info', "Memulai Tahap 6: Melakukan penyesuaian/Update Mapping Distributor di tabel selling_out...");
            }

            $updateMappingQuery = <<<SQL
UPDATE so
SET so.kd_distributor = m.rev_kd_distributor
FROM selling_out AS so
INNER JOIN master_mapping_dist AS m 
    ON CONCAT(so.kd_distributor, so.distributor) = CONCAT(m.kd_distributor, m.distributor)
WHERE so.bln = ?
SQL;
            $updatedRows = DB::connection('sqlsrv')->affectingStatement($updateMappingQuery, [$tglMulai]);

            if ($batch) {
                $batch->addLog('success', "Tahap 6 Selesai! Sebanyak {$updatedRows} baris kd_distributor berhasil di-update berdasarkan master_mapping_dist.");
                
                // 7. Tahap 7: Update FLAG_BONUS
                $batch->addLog('info', "Memulai Tahap 7: Menentukan FLAG_BONUS pada tabel selling_out...");
            }

            $updateFlagBonusQuery = <<<SQL
UPDATE selling_out
SET FLAG_BONUS = 
    CASE 
        WHEN [value_(netto)] = 0 THEN 'Y'
        WHEN [value_(netto)] IS NOT NULL THEN 'N'
        ELSE FLAG_BONUS
    END
WHERE BLN = ?
SQL;
            $updatedFlags = DB::connection('sqlsrv')->affectingStatement($updateFlagBonusQuery, [$tglMulai]);

            if ($batch) {
                $batch->addLog('success', "Tahap 7 Selesai! Sebanyak {$updatedFlags} baris FLAG_BONUS berhasil di-update.");
                
                // 8. Tahap 8: Update KD_SPV
                $batch->addLog('info', "Memulai Tahap 8: Melakukan penyesuaian KD_SPV berdasarkan mapping cabang...");
            }

            $updateSpvQuery = <<<SQL
UPDATE so
SET so.KD_SPV = ms.supervisor_code_old
FROM selling_out as so
JOIN mapping_supervisorcode as ms ON so.cabang = ms.branch_name
WHERE so.bln = ?
SQL;
            $updatedSpv = DB::connection('sqlsrv')->affectingStatement($updateSpvQuery, [$tglMulai]);

            if ($batch) {
                $batch->addLog('success', "Tahap 8 Selesai! Sebanyak {$updatedSpv} baris KD_SPV berhasil di-update berdasarkan mapping_supervisorcode.");
                
                // 9. Tahap 9: Update nama distributor
                $batch->addLog('info', "Memulai Tahap 9: Melakukan penyesuaian nama distributor...");
            }

            $updateDistQuery = <<<SQL
UPDATE a
SET a.distributor = b.distributor
FROM selling_out AS a
JOIN nama_distributor AS b 
    ON a.kd_distributor = b.kd_distributor
WHERE a.bln = ?
SQL;
            $updatedDist = DB::connection('sqlsrv')->affectingStatement($updateDistQuery, [$tglMulai]);

            if ($batch) {
                $batch->addLog('success', "Tahap 9 Selesai! Sebanyak {$updatedDist} baris distributor berhasil di-update berdasarkan nama_distributor.");
                
                // 10. Tahap 10: Update ASM
                $batch->addLog('info', "Memulai Tahap 10: Melakukan penyesuaian nama ASM...");
            }

            $updateAsmQuery = <<<SQL
UPDATE so
SET so.ASM = a.asm
FROM selling_out so
JOIN asm a
    ON so.AREA = a.area
WHERE so.bln = ?
SQL;
            $updatedAsm = DB::connection('sqlsrv')->affectingStatement($updateAsmQuery, [$tglMulai]);

            if ($batch) {
                $batch->addLog('success', "Tahap 10 Selesai! Sebanyak {$updatedAsm} baris ASM berhasil di-update berdasarkan tabel asm.");
                
                // 11. Tahap 11: Update sales_mapping
                $batch->addLog('info', "Memulai Tahap 11: Melakukan penyesuaian sales_name pada tabel sales_mapping...");
            }

            $updateSalesMappingQuery = <<<SQL
UPDATE sm
SET sm.sales_name = s.salesman_name
FROM sales_mapping sm
INNER JOIN salesmans s
    ON sm.kd_distributor = s.distributor_code
    AND sm.kd_sales = s.salesman_code
SQL;
            $updatedSales = DB::connection('sqlsrv')->affectingStatement($updateSalesMappingQuery);

            if ($batch) {
                $batch->addLog('success', "Tahap 11 Selesai! Sebanyak {$updatedSales} baris sales_name berhasil di-update di tabel sales_mapping.");
                
                // 12. Tahap 12: Update mapping_se pada selling_out
                $batch->addLog('info', "Memulai Tahap 12 (Final): Melakukan penyesuaian mapping_se di tabel selling_out...");
            }

            $updateMappingSeQuery = <<<SQL
UPDATE so
SET so.mapping_se = sm.sales_name
FROM selling_out so
JOIN sales_mapping sm
    ON so.kd_distributor = sm.kd_distributor
    AND so.[KD SALES] = sm.kd_sales
WHERE sm.active = 'Y' AND sm.tipe = 'SE' AND so.bln = ?
SQL;
            $updatedMappingSe = DB::connection('sqlsrv')->affectingStatement($updateMappingSeQuery, [$tglMulai]);

            if ($batch) {
                $batch->addLog('success', "Tahap 12 Selesai! Sebanyak {$updatedMappingSe} baris mapping_se berhasil di-update.");
                
                if (!$this->isChained) {
                    $batch->updateStatus('completed', "SELURUH PROSES SO FULL JOIN TELAH BERHASIL DISELESAIKAN!");
                } else {
                    $batch->addLog('info', "Melanjutkan estafet ke proses berikutnya (SO Per Toko)...");
                }
            }

        } catch (\Exception $e) {
            if ($batch) {
                $batch->addLog('error', 'Gagal: ' . $e->getMessage());
            }
            throw $e;
        }
    }
}
