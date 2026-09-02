<?php

namespace App\Livewire\Jobs;

use Livewire\Component;
use App\Jobs\SoFullJoinJob;
use App\Jobs\ZvSoPerToko2026Job;
use App\Jobs\ExtractSelloutPerCabangSqlServerJob;
use App\Jobs\UpdateAoPercabangJob;
use App\Jobs\FinalizeBatchJob;
use App\Models\ImportBatch;

class SoFullJoin extends Component
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
        // Guard: Cegah double dispatch jika proses sebelumnya masih berjalan
        if ($this->batchId) {
            $existing = ImportBatch::find($this->batchId);
            if ($existing && $existing->status === 'processing') {
                return; // Batalkan, proses sedang berjalan
            }
        }

        $batch = ImportBatch::create([
            'file_name' => 'Proses ETL SO Full Join (PostgreSQL Native)',
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        SoFullJoinJob::withChain([
            new ZvSoPerToko2026Job($batch->id),
            new ExtractSelloutPerCabangSqlServerJob($batch->id),
            new UpdateAoPercabangJob($batch->id),
            new FinalizeBatchJob($batch->id, "SELURUH PROSES ESTAFET BERHASIL DISELESAIKAN!")
        ])->dispatch($batch->id, $this->monthFilter, $this->yearFilter, true);
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
        if ($this->batchStatus === 'failed') return 100; // error status reaches 100% of its execution
        
        $progress = 10; // Base progress when started
        $logs = collect($this->logLines);

        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Sukses mengeksekusi Job SO Full Join'))) {
            $progress = 30;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 4 Selesai'))) {
            $progress = 60;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 5 Selesai'))) {
            $progress = 70;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 6 Selesai'))) {
            $progress = 65;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 7 Selesai'))) {
            $progress = 70;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 8 Selesai'))) {
            $progress = 75;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 9 Selesai'))) {
            $progress = 80;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 10 Selesai'))) {
            $progress = 65;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 11 Selesai'))) {
            $progress = 70;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Tahap 12 Selesai'))) {
            $progress = 75;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Sukses menarik data ZV SO Per Toko'))) {
            $progress = 85;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Sukses menarik data Sell Out Per Cabang'))) {
            $progress = 92;
        }
        if ($logs->contains(fn($l) => str_contains($l['message'] ?? '', 'Sukses memigrasi data AO Per Cabang'))) {
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
        return view('livewire.jobs.so-full-join')->layout('layouts.app');
    }
}
