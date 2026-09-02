<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ExtractSelloutPerCabangSqlServerJob implements ShouldQueue
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
                $batch->addLog('warning', 'Membersihkan data lama dari tabel sellout_per_cabang (PostgreSQL)');
            }
        }

        try {

        // Truncate PostgreSQL table
        DB::connection('pgsql')->table('sellout_per_cabang')->truncate();

        if (isset($batch)) {
            $batch->addLog('info', 'Mengeksekusi kueri aggregasi pada SQL Server...');
        }

        $query = <<<'SQL'
select 
so.BLN as bulan,
so.REGION as region,
so.AREA  as area,
so.CABANG as cabang,
so.REG_FEST as reg_fest,
sum(so.[VALUE_(NETTO)]) as actual 
from selling_out so 
group BY 
so.BLN,
so.REGION,
so.AREA,
so.CABANG,
so.REG_FEST
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

        // Insert into PostgreSQL in chunks to avoid parameter limits
        $chunks = array_chunk($dataToInsert, 500);
        foreach ($chunks as $chunk) {
            DB::connection('pgsql')->table('sellout_per_cabang')->insert($chunk);
        }

        if (isset($batch)) {
            $batch->addLog('info', 'Menyeragamkan data cabang (METRO -> KOTA METRO) dan reg_fest (fest -> FEST)...');
        }

        DB::connection('pgsql')->statement("UPDATE sellout_per_cabang SET cabang = 'KOTA METRO' WHERE cabang = 'METRO'");
        DB::connection('pgsql')->statement("UPDATE sellout_per_cabang SET reg_fest = 'FEST' WHERE reg_fest = 'fest'");

        if ($batch) {
            $batch->addLog('success', 'Sukses memigrasi data Sellout Per Cabang dari SQL Server ke PostgreSQL!');
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
