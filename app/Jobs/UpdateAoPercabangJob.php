<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class UpdateAoPercabangJob implements ShouldQueue
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
                $batch->addLog('info', 'Memulai penarikan data dari SQL Server...');
                $batch->addLog('warning', 'Membersihkan data lama dari tabel ao_percabang_perbulan (PostgreSQL)');
            }
        }

        try {

        // Truncate PostgreSQL table
        DB::connection('pgsql')->table('ao_percabang_perbulan')->truncate();

        if (isset($batch)) {
            $batch->addLog('info', 'Mengeksekusi kueri aggregasi pada SQL Server...');
        }

        $query = <<<'SQL'
select 
so.BLN as bulan,
so.REGION as region,
so.AREA  as area,
so.CABANG as cabang,
COUNT(distinct(so.UNIQKD_TOKO)) as ao
from selling_out so 
group BY 
so.BLN,
so.REGION,
so.AREA,
so.CABANG
SQL;

        // Fetch data from SQL Server
        $results = DB::connection('sqlsrv')->select($query);

        if (isset($batch)) {
            $batch->addLog('info', 'Berhasil menarik ' . count($results) . ' baris data dari SQL Server. Memulai proses insert ke PostgreSQL...');
        }

        // Convert to array of arrays
        $dataToInsert = [];
        foreach ($results as $row) {
            $dataToInsert[] = (array) $row;
        }

        // Insert into PostgreSQL in chunks
        $chunks = array_chunk($dataToInsert, 300);
        foreach ($chunks as $chunk) {
            DB::connection('pgsql')->table('ao_percabang_perbulan')->insert($chunk);
        }

        if ($batch) {
            $batch->addLog('success', 'Sukses memigrasi data AO Per Cabang dari SQL Server ke PostgreSQL!');
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
