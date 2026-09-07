<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Models\ImportBatch;
use App\Jobs\UpdateAoListTokoJob;

class UpdateAoListToko extends Component
{
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    
    public function startProcess()
    {
        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && in_array($existing->status, ['pending', 'processing'])) {
                return;
            }
        }

        $batch = ImportBatch::create([
            'file_name' => 'Update AO List Toko',
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        UpdateAoListTokoJob::dispatch($batch->id);
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
            return ($log['type'] ?? '') === 'success' && str_contains($log['message'], 'Tahap');
        })->count();
        
        // 2 Tahap
        $total = 2;
        if ($this->batchStatus === 'completed') return 100;
        
        return min(95, round(($completed / $total) * 100)); // Cap at 95 until completed
    }

    public function getCurrentTaskProperty()
    {
        if ($this->batchStatus === 'completed') return 'Proses Selesai';
        if ($this->batchStatus === 'failed') return 'Proses Gagal / Berhenti';
        if (empty($this->logLines)) return 'Menunggu Instruksi...';
        
        $lastLog = collect($this->logLines)->last();
        return $lastLog['message'] ?? 'Memproses...';
    }

    public function render()
    {
        return view('livewire.jobs.update-ao-list-toko')->layout('layouts.app');
    }
}
