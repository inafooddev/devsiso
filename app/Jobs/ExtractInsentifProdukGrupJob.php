<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ExtractInsentifProdukGrupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', '[6/X] Memulai penarikan data Master Produk Grup...');
                $batch->addLog('warning', '[6/X] Menghapus data lama Master Produk Grup (Truncate)...');
            }
        }

        // 1. Truncate tabel karena ini master data tanpa batas bulan
        DB::table('insentif_produk_grups')->truncate();

        if (isset($batch)) {
            $batch->addLog('info', '[6/X] Mengeksekusi kueri ekstraksi Master Produk...');
        }

        // 2. Eksekusi Raw Query
        // Menggunakan GROUP BY prd_code untuk menghindari pelanggaran UNIQUE
        $query = "
            INSERT INTO insentif_produk_grups (
                product_group_3, 
                prd_code, 
                prd_name, 
                created_at, 
                updated_at
            )
            SELECT 
                MAX(s.product_group_3) as product_group_3,
                s.prd_code,
                MAX(s.prd_name) as prd_name,
                NOW() as created_at,
                NOW() as updated_at
            FROM so_eska_n_noneska s 
            WHERE s.prd_code IS NOT NULL
            GROUP BY s.prd_code
        ";

        DB::statement($query);

        if (isset($batch)) {
            $batch->updateStatus('completed', null);
            $batch->addLog('success', '[6/X] Sukses menarik data Master Produk Grup!');
        }
    }
}
