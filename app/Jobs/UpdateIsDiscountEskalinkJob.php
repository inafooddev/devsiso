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

class UpdateIsDiscountEskalinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200; // Allow 2 hours
    protected $batchId;

    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $this->logMessage($batch, 'info', "Memulai proses Update is_discount Eskalink...");
            }
        } else {
            $this->logMessage(null, 'info', "Memulai proses Update is_discount Eskalink...");
        }

        try {
            $this->logMessage($batch, 'info', "Tahap 1: Mengeksekusi Query UPDATE (Menandai Y/N)...");
            
            // Logika: Jika line_discount_4 ATAU line_discount_8 lebih dari 0, maka 'Y', sisanya 'N'
            // Hanya perbarui data yang is_discount nya masih NULL
            
            $updated = DB::update("
                UPDATE selling_out_eskalink 
                SET is_discount = CASE 
                    WHEN line_discount_4 > 0 OR line_discount_8 > 0 THEN 'Y' 
                    ELSE 'N' 
                END
                WHERE is_discount IS NULL
            ");
            
            $this->logMessage($batch, 'success', "Tahap 1 Selesai. Sebanyak $updated baris data baru (NULL) berhasil ditandai.");
            
            $this->logMessage($batch, 'success', "Proses Selesai. Update is_discount Eskalink sukses dijalankan.");
            
            if ($batch) $batch->update(['status' => 'completed']);
            Log::info("UpdateIsDiscountEskalinkJob selesai. Terupdate $updated baris.");

        } catch (\Exception $e) {
            $this->logMessage($batch, 'error', "Terjadi kesalahan: " . $e->getMessage());
            if ($batch) $batch->update(['status' => 'failed']);
            Log::error("UpdateIsDiscountEskalinkJob Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function logMessage($batch, $type, $message)
    {
        if ($batch) {
            $batch->addLog($type, $message);
        }
        
        // Cetak juga ke terminal/Cronicle jika dijalankan dari console
        if (app()->runningInConsole()) {
            $timestamp = now()->format('Y-m-d H:i:s');
            $typeUpper = strtoupper($type);
            echo "[$timestamp] [$typeUpper] $message\n";
        }
    }
}
