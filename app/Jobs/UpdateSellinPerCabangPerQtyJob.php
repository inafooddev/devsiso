<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class UpdateSellinPerCabangPerQtyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $timeout = 3600;

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
                $batch->addLog('info', 'Memulai pembaruan data Sell-In Per Cabang Per Qty...');
                $batch->addLog('warning', 'Membersihkan data lama di tabel sellin_per_cabang_per_qty (TRUNCATE)');
            }
        }

        try {
            if ($batch) {
                $batch->addLog('info', 'Mengeksekusi query agregasi dari tabel selling_in...');
            }

            $startTime = microtime(true);

            $query = <<<'SQL'
TRUNCATE TABLE sellin_per_cabang_per_qty;

INSERT INTO sellin_per_cabang_per_qty (
    bulan,
    region,
    area,
    cabang,
    kd_distributor,
    nama_produk_mapping,
    qty_ctn,
    value_net,
    created_at,
    updated_at
)
SELECT 
    si.bulan,
    si.region,
    si.area,
    si.cabang,
    si.kd_distributor,
    si.nama_produk_mapping,
    SUM(si.ktn_net) AS qty_ctn,
    SUM(si.value_net) AS value_net,
    NOW(),
    NOW()
FROM selling_in si 
GROUP BY 
    si.bulan,
    si.region,
    si.area,
    si.cabang,
    si.kd_distributor,
    si.nama_produk_mapping;
SQL;

            DB::unprepared($query);

            $duration = round(microtime(true) - $startTime, 2);
            $totalCount = DB::table('sellin_per_cabang_per_qty')->count();

            if ($batch) {
                $batch->addLog('success', "Sukses memperbarui data! Total " . number_format($totalCount, 0, ',', '.') . " baris tersimpan dalam {$duration} detik.");
                $batch->update(['status' => 'completed']);
            }

        } catch (\Throwable $e) {
            if ($batch) {
                $batch->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            throw $e;
        }
    }
}
