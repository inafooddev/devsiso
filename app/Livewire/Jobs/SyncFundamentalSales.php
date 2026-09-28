<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Models\ImportBatch;
use App\Jobs\SyncFundamentalSalesMasterJob;
use Carbon\Carbon;

class SyncFundamentalSales extends Component
{
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    public $selectedMonth;

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }
    
    public function startProcess()
    {
        if (empty($this->selectedMonth)) {
            $this->addError('selectedMonth', 'Bulan wajib dipilih.');
            return;
        }

        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && in_array($existing->status, ['pending', 'processing'])) {
                return;
            }
        }

        $formattedMonth = Carbon::parse($this->selectedMonth)->format('Y-m-01');

        $batch = ImportBatch::create([
            'file_name' => 'Sync Fundamental Sales (' . $formattedMonth . ')',
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        SyncFundamentalSalesMasterJob::dispatch($batch->id, $formattedMonth);
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
        
        $total = 12; // Out of 12 planned stages
        if ($this->batchStatus === 'completed') return 100;
        
        return min(95, round(($completed / $total) * 100));
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
        return view('livewire.jobs.sync-fundamental-sales')->layout('layouts.app');
    }
}
