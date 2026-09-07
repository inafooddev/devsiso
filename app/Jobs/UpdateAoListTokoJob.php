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

class UpdateAoListTokoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
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
                $batch->addLog('info', "Memulai proses Update AO List Toko...");
            }
        }

        try {
            // STEP 1: TRUNCATE TABLE
            if ($batch) $batch->addLog('warning', "Mengeksekusi Tahap 1: TRUNCATE TABLE ao_list_toko...");
            DB::statement("TRUNCATE TABLE ao_list_toko");
            if ($batch) $batch->addLog('success', "Tahap 1 Selesai. Tabel berhasil dikosongkan.");

            // STEP 2: INSERT DATA
            if ($batch) $batch->addLog('info', "Mengeksekusi Tahap 2: INSERT DATA ke ao_list_toko dari t_sellingout...");
            
            $query = "
                INSERT INTO ao_list_toko (
                    bulan,
                    kd_dist,
                    uniq_kd,
                    custno,
                    custname,
                    alamat,
                    neto
                )
                SELECT 
                    make_date(
                        CAST(ts.\"THN\" AS INT),
                        CAST(ts.\"BLN\" AS INT),
                        1
                    ) AS bulan,
                    ts.\"KDDIST\" AS kd_dist,
                    substring(ts.\"KDDIST\", 3, 3)
                        || '-' || TRIM(UPPER(ts.\"CUSTNO\")) AS uniq_kd,
                    ts.\"CUSTNO\" AS custno,
                    MAX(ts.\"CUSTNAME\") AS custname,
                    MAX(ts.\"ALAMAT\") AS alamat,
                    SUM(ts.\"NETT\") AS neto
                FROM t_sellingout ts
                WHERE CAST(ts.\"THN\" AS INT) >= 2025
                  AND ts.\"REG_FEST\" = 'REG'
                GROUP BY 
                    ts.\"THN\",
                    ts.\"BLN\",
                    ts.\"KDDIST\",
                    ts.\"CUSTNO\",
                    substring(ts.\"KDDIST\", 3, 3)
                        || '-' || TRIM(UPPER(ts.\"CUSTNO\"))
            ";

            DB::statement($query);
            if ($batch) $batch->addLog('success', "Tahap 2 Selesai. Data berhasil diisi.");

            if ($batch) {
                $batch->addLog('success', "Proses Selesai. Update AO List Toko berhasil dijalankan.");
                $batch->update(['status' => 'completed']);
            }
            Log::info("UpdateAoListTokoJob selesai.");

        } catch (\Exception $e) {
            if ($batch) {
                $batch->addLog('error', "Terjadi kesalahan: " . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            Log::error("UpdateAoListTokoJob Error: " . $e->getMessage());
            throw $e;
        }
    }
}
