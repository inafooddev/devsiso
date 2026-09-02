<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class UpdateSellinPerCabangJob implements ShouldQueue
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
                $batch->addLog('info', 'Memulai update data Sell-In Per Cabang...');
                $batch->addLog('warning', 'Membersihkan data lama dari tabel v_sellinvstarget');
            }
        }

        try {

        if (isset($batch)) {
            $batch->addLog('info', 'Menarik data dari vw_sellinvstarget...');
        }

        $query = <<<'SQL'
TRUNCATE TABLE v_sellinvstarget;
INSERT INTO v_sellinvstarget SELECT * FROM vw_sellinvstarget;
SQL;

        DB::unprepared($query);

        if ($batch) {
            $batch->addLog('success', 'Sukses update data Sell-In Per Cabang!');
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
