<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Jobs\SyncCustomerEskaJob;
use App\Models\ImportBatch;

class SyncCustomerEska extends Component
{
    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    
    public $selectedRegion = 'all';

    public function startProcess()
    {
        // Guard: cegah double dispatch
        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && in_array($existing->status, ['pending', 'processing'])) {
                return;
            }
        }

        $regionText = $this->selectedRegion === 'all' ? 'Semua Region' : $this->selectedRegion;

        $batch = ImportBatch::create([
            'file_name' => 'Proses Sinkronisasi Customer Eska (' . $regionText . ')',
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        SyncCustomerEskaJob::dispatch($this->selectedRegion, $batch->id);
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
            return ($log['type'] ?? '') === 'success' && str_contains($log['message'], 'Proses Selesai');
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
        return view('livewire.jobs.sync-customer-eska')->layout('layouts.app');
    }
}
