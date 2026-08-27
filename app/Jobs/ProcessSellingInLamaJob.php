<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProcessSellingInLamaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; 

    protected $trackingId;
    protected $lamaMonth; // Format: Y-m

    public function __construct($trackingId, $lamaMonth)
    {
        $this->trackingId = $trackingId;
        $this->lamaMonth = $lamaMonth;
    }

    public function handle(): void
    {
        $cacheProgressKey = "lama_job_progress_{$this->trackingId}";
        $cacheLogsKey = "lama_job_logs_{$this->trackingId}";
        $cacheStatusKey = "lama_job_status_{$this->trackingId}";

        Cache::put($cacheStatusKey, 'processing', 3600);
        $this->logInfo($cacheLogsKey, "Memulai proses Job Sell In Lama untuk periode {$this->lamaMonth}.");

        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $this->lamaMonth);
            $year = $parsedDate->year;
            $month = $parsedDate->month;

            // 1. Hitung total data di selling_ins (tabel clean baru)
            $totalData = DB::table('selling_ins')
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->count();

            if ($totalData === 0) {
                throw new \Exception("Tidak ada data bersih (selling_ins) untuk periode {$this->lamaMonth}. Jalankan Validasi & Generate terlebih dahulu.");
            }

            Cache::put("lama_job_total_{$this->trackingId}", $totalData, 3600);
            $this->logInfo($cacheLogsKey, "Ditemukan {$totalData} baris data di tabel selling_ins. Memulai pembersihan tabel tujuan...");

            // 2. Hapus data lama di selling_in untuk periode yang sama (Idempotent)
            // Kolom di selling_in adalah 'bulan' dengan tipe date
            DB::table('selling_in')
                ->whereYear('bulan', $year)
                ->whereMonth('bulan', $month)
                ->delete();

            $this->logInfo($cacheLogsKey, "Menjalankan Query Transformasi ke tabel selling_in lama...");
            Cache::put($cacheProgressKey, floor($totalData / 2), 3600); // 50% fake progress for long query

            // 3. INSERT ... SELECT murni di level Database
            $query = "
                INSERT INTO selling_in (
                    bulan, tahun, rsm, asm, region, area, kd_spv, nama_spv, cabang, 
                    kd_distributor, nama_distributor, nama_distributor_fix, nama_produk, 
                    nama_produk_mapping, jenis, reg_fes, reg_fes_npd, kategori, top_item, 
                    brand, sub_brand, ktn_jual, pcs_jual, value_jual, ktn_retur, pcs_retur, 
                    value_retur, ktn_net, pcs_net, value_net, created_at, updated_at
                )
                SELECT 
                    DATE_TRUNC('month', si.invoice_date)::date as bulan,
                    EXTRACT(YEAR FROM si.invoice_date) AS tahun,
                    si.region_name as rsm,
                    CASE si.area_name
                        WHEN 'INA BENGKULU' THEN 'BENGKULU'
                        WHEN 'INA INDO TIMUR' THEN 'INDO TIMUR'
                        WHEN 'INA JABODETABEK' THEN 'JABODETABEK'
                        WHEN 'INA JAMBI' THEN 'JAMBI'
                        WHEN 'INA JAWA BARAT' THEN 'JABAR'
                        WHEN 'INA JAWA TENGAH 1' THEN 'JATENG 1'
                        WHEN 'INA JAWA TENGAH 2' THEN 'JATENG 2'
                        WHEN 'INA JAWA TIMUR 1' THEN 'JATIM 1'
                        WHEN 'INA JAWA TIMUR 2' THEN 'JATIM 2'
                        WHEN 'INA KALIMANTAN' THEN 'KALIMANTAN'
                        WHEN 'INA KEPRI' THEN 'KEPRI'
                        WHEN 'INA LAMPUNG' THEN 'LAMPUNG'
                        WHEN 'INA NAD' THEN 'NAD'
                        WHEN 'INA RIAU' THEN 'RIAU'
                        WHEN 'INA SULAWESI' THEN 'SULAWESI'
                        WHEN 'INA SULAWESI 2' THEN 'SULAWESI 2'
                        WHEN 'INA SUMBAR' THEN 'SUMBAR'
                        WHEN 'INA SUMSEL' THEN 'SUMSEL'
                        WHEN 'INA SUMUT' THEN 'SUMUT'
                        ELSE NULL
                    END AS asm,
                    CASE
                        WHEN si.region_name = 'INA JAWA 1' THEN 'JAWA 1'
                        WHEN si.region_name = 'INA JAWA 2' THEN 'JAWA 2'
                        WHEN si.region_name = 'INA PULAU 1' THEN 'PULAU'
                        WHEN si.region_name = 'INA REMOTE' THEN 'REMOTE'
                        WHEN si.region_name = 'INA SUMATERA 1' THEN 'SUMATERA 1'
                        WHEN si.region_name = 'INA SUMATERA 2' THEN 'SUMATERA 2'
                        ELSE NULL
                    END AS region,
                    CASE si.area_name
                        WHEN 'INA BENGKULU' THEN 'BENGKULU'
                        WHEN 'INA INDO TIMUR' THEN 'INDO TIMUR'
                        WHEN 'INA JABODETABEK' THEN 'JABODETABEK'
                        WHEN 'INA JAMBI' THEN 'JAMBI'
                        WHEN 'INA JAWA BARAT' THEN 'JABAR'
                        WHEN 'INA JAWA TENGAH 1' THEN 'JATENG 1'
                        WHEN 'INA JAWA TENGAH 2' THEN 'JATENG 2'
                        WHEN 'INA JAWA TIMUR 1' THEN 'JATIM 1'
                        WHEN 'INA JAWA TIMUR 2' THEN 'JATIM 2'
                        WHEN 'INA KALIMANTAN' THEN 'KALIMANTAN'
                        WHEN 'INA KEPRI' THEN 'KEPRI'
                        WHEN 'INA LAMPUNG' THEN 'LAMPUNG'
                        WHEN 'INA NAD' THEN 'NAD'
                        WHEN 'INA RIAU' THEN 'RIAU'
                        WHEN 'INA SULAWESI' THEN 'SULAWESI'
                        WHEN 'INA SULAWESI 2' THEN 'SULAWESI 2'
                        WHEN 'INA SUMBAR' THEN 'SUMBAR'
                        WHEN 'INA SUMSEL' THEN 'SUMSEL'
                        WHEN 'INA SUMUT' THEN 'SUMUT'
                        ELSE NULL
                    END AS area,
                    si.supervisor_code as kd_spv,
                    si.supervisor_name as nama_spv,
                    si.branch_name as cabang,
                    si.distributor_code as kd_distributor,
                    si.distributor_name as nama_distributor,
                    si.distributor_name as nama_distributor_fix,
                    si.nama_barang as nama_produk,
                    si.produk_grup as nama_produk_mapping,
                    '' as jenis,
                    si.reg_fes as reg_fes,
                    '' as reg_fes_npd,
                    si.kategory as kategori,
                    si.topitem as top_item,
                    '' as brand,
                    si.subbrand as sub_brand,
                    0 as ktn_jual,
                    0 as pcs_jual,
                    0 as value_jual,
                    0 as ktn_retur,
                    0 as pcs_retur,
                    0 as value_retur,
                    si.qty as ktn_net,
                    0 as pcs_net,
                    si.total_idr as value_net,
                    NOW(),
                    NOW()
                FROM 
                    selling_ins si
                WHERE
                    EXTRACT(YEAR FROM si.invoice_date) = ?
                    AND EXTRACT(MONTH FROM si.invoice_date) = ?
            ";

            DB::statement($query, [$year, $month]);
            $this->logInfo($cacheLogsKey, "Tahap 1 selesai: {$totalData} baris data berhasil disalin ke tabel selling_in lama.");
            Cache::put($cacheProgressKey, floor($totalData * 0.8), 3600); // 80% progress

            // 4. Update dan Rapihkan Data SPV (Retroaktif)
            $this->logInfo($cacheLogsKey, "Menjalankan Tahap 2: Merapihkan nama SPV ke seluruh data historis...");
            $queryUpdateSpv = "
                UPDATE selling_in si 
                SET nama_spv = UPPER(ms.description)
                FROM master_supervisors ms  
                WHERE si.kd_spv = ms.supervisor_code 
            ";
            DB::statement($queryUpdateSpv);

            // 5. Update dan Rapihkan Data ASM (Retroaktif)
            $this->logInfo($cacheLogsKey, "Menjalankan Tahap 3: Merapihkan nama ASM ke seluruh data historis...");
            $queryUpdateAsm = "
                UPDATE selling_in
                SET asm = a.asm
                FROM asm a
                WHERE selling_in.area = a.area
            ";
            DB::statement($queryUpdateAsm);

            // 6. Update dan Rapihkan Data Brand (Retroaktif)
            $this->logInfo($cacheLogsKey, "Menjalankan Tahap 4: Merapihkan nama Brand ke seluruh data historis...");
            $queryUpdateBrand = "
                UPDATE selling_in si 
                SET brand = mpl.brand
                FROM master_produk_lama mpl 
                WHERE si.nama_produk_mapping = mpl.divisi
            ";
            DB::statement($queryUpdateBrand);

            // 7. Update dan Rapihkan Data REG/FES/NPD (Retroaktif)
            $this->logInfo($cacheLogsKey, "Menjalankan Tahap 5: Merapihkan status REG/FES/NPD...");
            $queryUpdateNpd = "
                UPDATE selling_in
                SET reg_fes_npd = CASE 
                    WHEN kategori = 'NPD' THEN 'NPD'
                    ELSE reg_fes
                END
            ";
            DB::statement($queryUpdateNpd);

            // Selesai
            Cache::put($cacheProgressKey, $totalData, 3600);
            Cache::put($cacheStatusKey, 'completed', 3600);
            $this->logInfo($cacheLogsKey, "Job Sell In Lama selesai total! Data siap digunakan.");
            
            \App\Helpers\ActivityLogger::log('Job Sell In Lama', "Berhasil menjalankan Job Sell In Lama dan merapihkan data untuk periode {$this->lamaMonth}.");

        } catch (\Exception $e) {
            Cache::put($cacheStatusKey, 'failed', 3600);
            $this->logInfo($cacheLogsKey, "ERROR: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error("ProcessSellingInLamaJob Error: " . $e->getMessage());
        }
    }

    private function logInfo($key, $message)
    {
        $logs = Cache::get($key, []);
        $logs[] = ['type' => 'info', 'message' => $message];
        Cache::put($key, $logs, 3600);
    }
}
