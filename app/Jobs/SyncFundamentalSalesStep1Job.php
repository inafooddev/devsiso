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
use Exception;

class SyncFundamentalSalesStep1Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
    protected $batchId;
    protected $bulan;

    public function __construct($batchId, $bulan)
    {
        $this->batchId = $batchId;
        $this->bulan = $bulan;
    }

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
        }

        try {
            $this->logMessage($batch, 'info', "Tahap 1: Membersihkan data lama untuk bulan {$this->bulan}...");
            $deleted = DB::delete("DELETE FROM ipm_fundamental_sales WHERE bulan = ?", [$this->bulan]);
            $this->logMessage($batch, 'info', "Terhapus $deleted baris data lama.");

            $this->logMessage($batch, 'info', "Tahap 1: Menginisialisasi cabang dari tabel master_branches...");
            
            DB::insert("
                INSERT INTO ipm_fundamental_sales (bulan, cabang, created_at, updated_at)
                SELECT ?, branch_name, NOW(), NOW() FROM master_branches
            ", [$this->bulan]);

            // Hitung data yang disisipkan
            $count = DB::table('ipm_fundamental_sales')->where('bulan', $this->bulan)->count();
            
            $this->logMessage($batch, 'success', "Tahap 1 Selesai: Berhasil menyisipkan $count data cabang.");

        } catch (Exception $e) {
            $this->logMessage($batch, 'error', "Error Tahap 1: " . $e->getMessage());
            throw $e; // Lempar error agar Master Job berhenti
        }
    }

    private function logMessage($batch, $type, $message)
    {
        if ($batch) {
            $batch->addLog($type, $message);
        }
        if (app()->runningInConsole()) {
            $timestamp = now()->format('Y-m-d H:i:s');
            $typeUpper = strtoupper($type);
            echo "[$timestamp] [$typeUpper] $message\n";
        }
    }
}
