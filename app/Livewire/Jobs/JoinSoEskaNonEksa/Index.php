<?php

namespace App\Livewire\Jobs\JoinSoEskaNonEksa;

use Livewire\Component;
use App\Jobs\JoinSoEskaNonEksaJob;
use App\Models\ImportBatch;

class Index extends Component
{
    // Filter
    public $monthFilter;
    public $yearFilter;

    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    
    public function mount()
    {
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
    }

    public function startProcess()
    {
        // Guard: Cegah double dispatch
        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && in_array($existing->status, ['pending', 'processing'])) {
                return;
            }
        }

        $batch = ImportBatch::create([
            'file_name' => 'Proses ETL Join SO Eska Non Eksa',
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        JoinSoEskaNonEksaJob::dispatch($batch->id, $this->monthFilter, $this->yearFilter);
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
        if ($this->batchStatus === 'completed') return 100;
        if ($this->batchStatus === 'failed') return 100;
        
        $progress = 10;
        $logs = collect($this->logLines);

        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 1 Selesai'))) {
            $progress = 30;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 2 Selesai'))) {
            $progress = 50;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 3 Selesai'))) {
            $progress = 70;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 4 Selesai'))) {
            $progress = 85;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 5 Selesai'))) {
            $progress = 89;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 6 Selesai'))) {
            $progress = 95;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 7 Selesai'))) {
            $progress = 98;
        }
        
        return $progress;
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
        return view('livewire.jobs.join-so-eska-non-eksa.index')->layout('layouts.app');
    }
}
