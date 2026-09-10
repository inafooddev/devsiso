<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\ImportBatch;

class SyncRptVisitAnHJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
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
                $batch->addLog('info', "Memulai proses Sync RPT Visit An H...");
            }
        }

        try {
            // STEP 1: Fetch API
            if ($batch) $batch->addLog('info', "Tahap 1: Mengunduh data dari API RPT_ANH...");
            
            $url = 'https://jobs.asiatop.co.id:9080/trx/export?block=RPT_ANH';
            $token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ==';

            $client = new Client(['verify' => false]);
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            $json = json_decode($response->getBody(), true);
            $dataset = $json['data'] ?? [];
            
            if (empty($dataset)) {
                if ($batch) {
                    $batch->addLog('warning', "API tidak mengembalikan data apa pun.");
                    $batch->addLog('success', "Proses Selesai tanpa perubahan data.");
                    $batch->update(['status' => 'completed']);
                }
                return;
            }
            
            $totalData = count($dataset);
            if ($batch) $batch->addLog('success', "Tahap 1 Selesai. Berhasil mengunduh $totalData baris data.");

            // STEP 2: Delete Current Month Data
            if ($batch) $batch->addLog('warning', "Tahap 2: Menghapus data bulan berjalan di database...");
            
            // Ambil sample TANGGAL dari row pertama untuk menentukan bulan berjalan
            $sampleDate = $dataset[0]['TANGGAL'] ?? now()->toDateString();
            $carbonDate = Carbon::parse($sampleDate);
            $startOfMonth = $carbonDate->copy()->startOfMonth()->toDateString() . ' 00:00:00';
            $endOfMonth = $carbonDate->copy()->endOfMonth()->toDateString() . ' 23:59:59';
            
            DB::table('rpt_visit_an_h')
                ->whereBetween('TANGGAL', [$startOfMonth, $endOfMonth])
                ->delete();
                
            if ($batch) $batch->addLog('success', "Tahap 2 Selesai. Data periode " . $carbonDate->format('M Y') . " berhasil dihapus dari tabel.");

            // STEP 3: Bulk Insert
            if ($batch) $batch->addLog('info', "Tahap 3: Memasukkan (Insert) data baru ke database...");
            
            $chunks = array_chunk($dataset, 500); // 500 rows per insert
            $inserted = 0;
            
            foreach ($chunks as $chunk) {
                $insertData = [];
                foreach ($chunk as $row) {
                    $formattedRow = [];
                    foreach ($row as $key => $value) {
                        // Mengubah string kosong menjadi null untuk menghindari error cast tipe data di Postgres (misal integer/date)
                        $formattedRow[$key] = ($value === '') ? null : $value;
                    }
                    $insertData[] = $formattedRow;
                }
                
                DB::table('rpt_visit_an_h')->insert($insertData);
                $inserted += count($chunk);
                if ($batch) $batch->addLog('info', "Progress Insert: $inserted / $totalData baris...");
            }

            if ($batch) {
                $batch->addLog('success', "Tahap 3 Selesai. $inserted baris data berhasil ditambahkan.");
                $batch->addLog('success', "Proses Selesai. Sync RPT Visit An H sukses dijalankan.");
                $batch->update(['status' => 'completed']);
            }
            Log::info("SyncRptVisitAnHJob selesai.");

        } catch (\Exception $e) {
            if ($batch) {
                $batch->addLog('error', "Terjadi kesalahan: " . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            Log::error("SyncRptVisitAnHJob Error: " . $e->getMessage());
            throw $e;
        }
    }
}
