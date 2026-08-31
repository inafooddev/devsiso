<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Jobs\UpdateSellinPerCabangJob;
use App\Models\ImportBatch;

class UpdateSellinPerCabang extends Component
{
    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    
    public function startProcess()
    {
        $batch = ImportBatch::create([
            'file_name' => 'Proses ETL Update Sell-In Per Cabang',
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'Proses update ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        UpdateSellinPerCabangJob::dispatch($batch->id);
        
        $batch->refresh();
        $batch->addLog('success', 'Job telah berhasil dikirim ke background!');
        $batch->update(['status' => 'processing']);
    }

    public function syncLog()
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $this->logLines = $batch->log_lines ?? [];
                $this->batchStatus = $batch->status;
            }
        }
    }

    public function getProgressProperty()
    {
        if (empty($this->logLines)) return 0;
        
        $completed = collect($this->logLines)->filter(function ($log) {
            return ($log['type'] ?? '') === 'success' && str_contains($log['message'], 'Sukses');
        })->count();
        
        $total = 1;
        return min(100, round(($completed / $total) * 100));
    }

    public function getCurrentTaskProperty()
    {
        if ($this->progress == 100) return 'Proses Selesai';
        if ($this->batchStatus === 'failed') return 'Proses Gagal / Berhenti';
        if (empty($this->logLines)) return 'Menunggu Instruksi...';
        
        $lastLog = collect($this->logLines)->last();
        return $lastLog['message'] ?? 'Memproses...';
    }

    public function render()
    {
        return view('livewire.jobs.update-sellin-per-cabang')->layout('layouts.app');
    }
}
