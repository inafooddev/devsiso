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
use App\Models\SellingInClean;

class ProcessSellingInGenerateClean implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 jam jika data sangat besar

    protected $trackingId;
    protected $selectedMonth; // Format: Y-m

    public function __construct($trackingId, $selectedMonth)
    {
        $this->trackingId = $trackingId;
        $this->selectedMonth = $selectedMonth;
    }

    public function handle(): void
    {
        $cacheProgressKey = "generate_clean_progress_{$this->trackingId}";
        $cacheLogsKey = "generate_clean_logs_{$this->trackingId}";
        $cacheStatusKey = "generate_clean_status_{$this->trackingId}";

        Cache::put($cacheStatusKey, 'processing', 3600);
        $this->logInfo($cacheLogsKey, "Memulai proses Generate Data Clean untuk periode {$this->selectedMonth}.");

        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $this->selectedMonth);
            $year = $parsedDate->year;
            $month = $parsedDate->month;

            // 1. Hitung total raw data
            $totalRaws = DB::table('selling_in_raws')
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->count();

            if ($totalRaws === 0) {
                throw new \Exception("Tidak ada data raw untuk periode {$this->selectedMonth}. Import data raw terlebih dahulu.");
            }

            Cache::put("generate_clean_total_{$this->trackingId}", $totalRaws, 3600);
            $this->logInfo($cacheLogsKey, "Ditemukan {$totalRaws} baris data raw. Memulai pembersihan tabel tujuan...");

            // 2. Hapus data clean lama untuk periode yang sama (Auto-Replace / Idempotent)
            SellingInClean::whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->delete();

            $this->logInfo($cacheLogsKey, "Menjalankan Query Transformasi & Mapping...");
            Cache::put($cacheProgressKey, floor($totalRaws / 2), 3600); // 50% fake progress for long query

            // 3. INSERT ... SELECT murni di level Database (Sangat Cepat & Efisien)
            // Karena tabel sumber dan tujuan di database yang sama, ini 100x lebih cepat dari memprosesnya di PHP (chunk).
            $query = "
                INSERT INTO selling_ins (
                    region_code, region_name, area_code, area_name, supervisor_code, supervisor_name, 
                    distributor_code, distributor_name, branch_name, invoice_date, kode, invoice_no, 
                    jenis_penjualan, kode_barang, nama_barang, produk_grup, subbrand, reg_fes, kategory, 
                    topitem, qty, satuan, harga_satuan, subtotal, qty_bonus, nilai_bonus, diskon_1, 
                    diskon_2, diskon_3, dpp, ppn, total, total_idr, created_at, updated_at
                )
                SELECT 
                    md.region_code,
                    md.region_name,
                    md.area_code,
                    md.area_name,
                    md.supervisor_code,
                    ms.description as supervisor_name,
                    md.distributor_code,
                    md.distributor_name,
                    md.branch_name,
                    raw.invoice_date,
                    raw.kode,
                    raw.invoice_no,
                    raw.jenis_penjualan,
                    raw.kode_barang,
                    raw.nama_barang,
                    mpl.divisi as produk_grup,
                    mpl.subbrand,
                    mpl.produk_line as reg_fes,
                    mpl.kategory,
                    mpl.topitem,
                    raw.qty,
                    raw.satuan,
                    raw.harga_satuan,
                    raw.subtotal,
                    raw.qty_bonus,
                    raw.nilai_bonus,
                    raw.diskon_1,
                    raw.diskon_2,
                    raw.diskon_3,
                    raw.dpp,
                    raw.ppn,
                    raw.total,
                    raw.total_idr,
                    NOW(),
                    NOW()
                FROM 
                    selling_in_raws raw
                LEFT JOIN 
                    selling_in_distributor_mappings map
                    ON raw.divisi = map.divisi
                    AND raw.wilayah = map.wilayah
                    AND raw.kode_distributor = map.kode_distributor
                    AND raw.distributor = map.distributor
                LEFT JOIN master_distributors md 
                    ON map.distributor_code = md.distributor_code 
                LEFT JOIN master_supervisors ms 
                    ON md.supervisor_code = ms.supervisor_code 
                LEFT JOIN master_produk_lama mpl
                    ON raw.kode_barang = mpl.pcode_prc
                WHERE
                    EXTRACT(YEAR FROM raw.invoice_date) = ?
                    AND EXTRACT(MONTH FROM raw.invoice_date) = ?
            ";

            DB::statement($query, [$year, $month]);

            // Selesai
            Cache::put($cacheProgressKey, $totalRaws, 3600);
            Cache::put($cacheStatusKey, 'completed', 3600);
            $this->logInfo($cacheLogsKey, "Generate Data Clean berhasil! {$totalRaws} baris data siap digunakan di Dashboard.");
            
            \App\Helpers\ActivityLogger::log('Generate Selling-In Clean', "Berhasil me-generate data bersih untuk periode {$this->selectedMonth} ({$totalRaws} baris).");

        } catch (\Exception $e) {
            Cache::put($cacheStatusKey, 'failed', 3600);
            $this->logInfo($cacheLogsKey, "ERROR: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error("GenerateCleanJob Error: " . $e->getMessage());
        }
    }

    private function logInfo($key, $message)
    {
        $logs = Cache::get($key, []);
        $logs[] = ['type' => 'info', 'message' => $message];
        Cache::put($key, $logs, 3600);
    }
}
