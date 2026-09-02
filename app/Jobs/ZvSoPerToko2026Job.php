<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ZvSoPerToko2026Job implements ShouldQueue
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
                $batch->addLog('info', 'Memulai penarikan data ZV SO Per Toko 2026...');
                $batch->addLog('warning', 'Membersihkan data lama ZV SO Per Toko 2026');
            }
        }

        try {

        if (isset($batch)) {
            $batch->addLog('info', 'Mengeksekusi kueri ZV SO Per Toko 2026...');
        }

        $query = <<<'SQL'
TRUNCATE TABLE zv_so_per_toko_2026;

INSERT INTO zv_so_per_toko_2026 (
    bulan,
    region,
    area,
    cabang,
    kd_dist,
    nm_dist,
    uniq_kd,
    custno,
    custname,
    alamat,
    neto
)
SELECT 
    make_date(2026, CAST(ts."BLN" AS int), 1) AS bulan,
    ts."REGION" as region,
    ts."AREA" as area,
    ts."CABANG" as cabang,
    ts."KDDIST" as kd_dist,
    ts."DIST" as nm_dist,
    substring(ts."KDDIST",3,3)||'-'||TRIM(UPPER(ts."CUSTNO")) as uniq_kd,
    ts."CUSTNO" as custno,
    MAX(ts."CUSTNAME") as custname,
    MAX(ts."ALAMAT") as alamat,
    SUM(ts."NETT") as neto
FROM t_sellingout ts
WHERE ts."THN" ='2026'
  AND ts."REG_FEST" ='REG'
GROUP BY 
    ts."BLN",
    ts."REGION",
    ts."AREA",
    ts."CABANG",
    ts."KDDIST",
    ts."DIST",
    ts."CUSTNO",
    substring(ts."KDDIST",3,3)||'-'||TRIM(UPPER(ts."CUSTNO"));
SQL;

        DB::unprepared($query);

        if ($batch) {
            $batch->addLog('success', 'Sukses menarik data ZV SO Per Toko 2026!');
            $batch->update(['status' => 'completed']);
        }

        } catch (\Throwable $e) {
            if (isset($batch)) {
                $batch->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            throw $e;
        }
    }
}
