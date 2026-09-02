<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Jobs\ZvSoPerToko2026Job;
use App\Models\ImportBatch;

class ZvSoPerToko2026 extends Component
{
    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    
    public function startProcess()
    {
        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && in_array($existing->status, ['pending', 'processing'])) {
                return; // Prevent double dispatch
            }
        }

        // Membuat log batch baru
        $batch = ImportBatch::create([
            'file_name' => 'Proses ETL ZV SO Per Toko 2026',
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        // Dispatch Job
        ZvSoPerToko2026Job::dispatch($batch->id);
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
        
        $total = 1; // Only 1 main job
        return min(100, round(($completed / $total) * 100));
    }

    public function getCurrentTaskProperty()
    {
        if ($this->progress == 100) return 'Proses Selesai';
        if ($this->batchStatus === 'failed') return 'Proses Gagal / Berhenti';
        if (empty($this->logLines)) return 'Menunggu Instruksi...';
        
        // Find the last info/warning message to show as current task
        $lastLog = collect($this->logLines)->last();
        return $lastLog['message'] ?? 'Memproses...';
    }

    public function render()
    {
        return view('livewire.jobs.zv-so-per-toko-2026')->layout('layouts.app');
    }
}
