<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class UpdateSalesmansJob implements ShouldQueue
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
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', 'Memulai penarikan data dari PostgreSQL...');
                $batch->addLog('warning', 'Membersihkan data lama dari tabel salesmans (SQL Server)');
            }
        }

        // Truncate SQL Server table
        DB::connection('sqlsrv')->table('salesmans')->truncate();

        if (isset($batch)) {
            $batch->addLog('info', 'Mengeksekusi kueri pada PostgreSQL...');
        }

        $query = <<<'SQL'
select 
s.salesman_code ,
s.distributor_code ,
s.salesman_name ,
s.is_active ,
s.created_at ,
s.updated_at ,
id
from salesmans s
SQL;

        // Fetch data from PostgreSQL
        $results = DB::connection('pgsql')->select($query);

        if (isset($batch)) {
            $batch->addLog('info', 'Berhasil menarik ' . count($results) . ' baris data dari PostgreSQL. Memulai proses insert ke SQL Server...');
        }

        // Convert to array of arrays
        $dataToInsert = [];
        foreach ($results as $row) {
            $dataToInsert[] = (array) $row;
        }

        // Insert into SQL Server in chunks to avoid parameter limits (Max 2100 params)
        $chunks = array_chunk($dataToInsert, 200);
        foreach ($chunks as $chunk) {
            // Kita bungkus dalam try-catch untuk berjaga-jaga jika tabel di SQL Server 
            // menggunakan IDENTITY pada kolom 'id', sehingga kalau error IDENTITY_INSERT
            // kita bisa melakukan penanganan (menambahkan statement SET IDENTITY_INSERT ON).
            try {
                DB::connection('sqlsrv')->table('salesmans')->insert($chunk);
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'IDENTITY_INSERT')) {
                    DB::connection('sqlsrv')->statement('SET IDENTITY_INSERT salesmans ON');
                    DB::connection('sqlsrv')->table('salesmans')->insert($chunk);
                    DB::connection('sqlsrv')->statement('SET IDENTITY_INSERT salesmans OFF');
                } else {
                    throw $e;
                }
            }
        }

        if (isset($batch)) {
            $batch->addLog('success', 'Sukses memigrasi data Salesmans dari PostgreSQL ke SQL Server!');
        }
    }
}
