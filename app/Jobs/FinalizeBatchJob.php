<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportBatch;

class FinalizeBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $message;

    public function __construct($batchId, $message = "Semua proses background berhasil diselesaikan!")
    {
        $this->batchId = $batchId;
        $this->message = $message;
    }

    public function handle(): void
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->updateStatus('completed', $this->message);
            }
        }
    }
}
