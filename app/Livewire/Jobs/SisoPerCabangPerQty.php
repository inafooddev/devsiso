<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Jobs\UpdateSisoPerCabangPerQtyJob;
use App\Models\ImportBatch;
use Carbon\Carbon;

class SisoPerCabangPerQty extends Component
{
    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;

    // Filter Periode
    public $processMode = 'month'; // 'month' atau 'all'
    public $selectedMonth;

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }
    
    public function startProcess()
    {
        if ($this->processMode === 'month' && empty($this->selectedMonth)) {
            $this->addError('selectedMonth', 'Silakan tentukan periode bulan yang ingin diproses.');
            return;
        }

        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && in_array($existing->status, ['pending', 'processing'])) {
                return; // Prevent double dispatch
            }
        }

        $monthParam = null;
        $label = 'Semua Periode';
        if ($this->processMode === 'month') {
            $monthParam = Carbon::parse($this->selectedMonth)->format('Y-m-01');
            $label = Carbon::parse($this->selectedMonth)->translatedFormat('F Y');
        }

        // Membuat log batch baru
        $batch = ImportBatch::create([
            'file_name' => "Proses ETL SISO Per Cabang Per Qty ({$label})",
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => "Proses antrian dimulai untuk {$label}..."]]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        // Dispatch Job gabungan dengan parameter bulan
        UpdateSisoPerCabangPerQtyJob::dispatch($batch->id, $monthParam);
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
            $msg = $log['message'] ?? '';
            return ($log['type'] ?? '') === 'success' && (str_contains($msg, 'Sukses Sell-In') || str_contains($msg, 'Sukses Sell-Out'));
        })->count();
        
        $total = 2; // 2 Sub-proses: Sell-In & Sell-Out
        return min(100, round(($completed / $total) * 100));
    }

    public function getCurrentTaskProperty()
    {
        if ($this->progress == 100) return 'Seluruh Proses Selesai';
        if ($this->batchStatus === 'failed') return 'Proses Gagal / Berhenti';
        if (empty($this->logLines)) return 'Menunggu Instruksi...';
        
        $lastLog = collect($this->logLines)->last();
        return $lastLog['message'] ?? 'Memproses...';
    }

    public function render()
    {
        return view('livewire.jobs.siso-per-cabang-per-qty')->layout('layouts.app');
    }
}
