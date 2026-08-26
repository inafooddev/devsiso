<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SellingInRawImport;

class ProcessSellingInRawImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 jam timeout, untuk antisipasi file besar

    protected $filePath;
    protected $batchId;
    protected $selectedMonth;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $batchId, $selectedMonth)
    {
        $this->filePath = $filePath;
        $this->batchId = $batchId;
        $this->selectedMonth = $selectedMonth;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        
        if (!$batch) {
            return; // Batch mungkin dihapus
        }

        $cacheProgressKey = "import_batch_{$this->batchId}_progress";
        $cacheLogsKey = "import_batch_{$this->batchId}_logs";

        try {
            $batch->update([
                'status' => 'processing',
                'log_lines' => array_merge($batch->log_lines ?? [], [
                    ['type' => 'info', 'message' => 'Memulai pembacaan file Excel...']
                ])
            ]);

            // Inisialisasi Cache
            \Illuminate\Support\Facades\Cache::put($cacheProgressKey, 0, 3600);
            \Illuminate\Support\Facades\Cache::put($cacheLogsKey, $batch->log_lines, 3600);

            $fullPath = Storage::path($this->filePath);

            // Hitung estimasi baris
            $rows = Excel::toArray(new \stdClass(), $fullPath);
            $totalRows = count($rows[0] ?? []) - 1; // -1 asumsi 1 baris header

            $batch->update([
                'total_rows' => $totalRows > 0 ? $totalRows : 0
            ]);

            // Tambahkan log ke cache
            $logs = \Illuminate\Support\Facades\Cache::get($cacheLogsKey, []);
            $logs[] = ['type' => 'info', 'message' => "Ditemukan sekitar {$totalRows} baris. Memulai transaksi database..."];
            \Illuminate\Support\Facades\Cache::put($cacheLogsKey, $logs, 3600);

            // Buka DB Transaction
            DB::beginTransaction();

            // 1. Hapus data lama untuk periode yang dipilih
            list($year, $month) = explode('-', $this->selectedMonth);
            $deletedRows = DB::table('selling_in_raws')
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->delete();

            if ($deletedRows > 0) {
                $logs = \Illuminate\Support\Facades\Cache::get($cacheLogsKey, []);
                $logs[] = ['type' => 'warning', 'message' => "Menghapus {$deletedRows} baris data lama untuk periode {$this->selectedMonth} (Auto-Replace)..."];
                \Illuminate\Support\Facades\Cache::put($cacheLogsKey, $logs, 3600);
            }

            // 2. Lakukan import data baru
            $importer = new SellingInRawImport($batch, $this->selectedMonth);
            Excel::import($importer, $fullPath);

            // Validasi: Pastikan baris header benar-benar ada dan format Excel tepat
            if (!$importer->isHeaderFound()) {
                throw new \Exception("Validasi Gagal: Header kolom yang diwajibkan (seperti 'TANGGAL FAKTUR' dan 'KODE DISTRIBUTOR') tidak ditemukan di dalam file Excel. Pastikan format file sesuai.");
            }

            // Jika sampai sini, berarti tidak ada exception. Commit!
            DB::commit();

            // Ambil state terakhir dari cache
            $finalProcessedCount = $importer->getProcessedCount();
            $finalLogs = \Illuminate\Support\Facades\Cache::get($cacheLogsKey, []);
            $finalLogs[] = ['type' => 'success', 'message' => 'Proses import selesai dan di-commit ke database dengan sukses.'];

            $batch->update([
                'status' => 'completed',
                'processed_rows' => $finalProcessedCount,
                'log_lines' => $finalLogs
            ]);

        } catch (\Exception $e) {
            // Ada error, Rollback!
            DB::rollBack();

            $finalLogs = \Illuminate\Support\Facades\Cache::get($cacheLogsKey, []);
            $finalLogs[] = ['type' => 'error', 'message' => 'Terjadi kesalahan kritis. Transaksi di-rollback. Seluruh data import dibatalkan.'];
            $finalLogs[] = ['type' => 'error', 'message' => 'Pesan Error: ' . $e->getMessage()];

            $batch->update([
                'status' => 'failed',
                'log_lines' => $finalLogs
            ]);
        } finally {
            // Bersihkan Cache agar tidak jadi sampah memori
            \Illuminate\Support\Facades\Cache::forget($cacheProgressKey);
            \Illuminate\Support\Facades\Cache::forget($cacheLogsKey);

            // Hapus file temporary agar storage tidak penuh
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }
}
