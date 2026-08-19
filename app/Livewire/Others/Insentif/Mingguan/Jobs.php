<?php

namespace App\Livewire\Others\Insentif\Mingguan;

use Livewire\Component;
use App\Jobs\ExtractInsentifMasterDistributorJob;
use App\Jobs\ExtractInsentifMasterSalesmanJob;
use App\Jobs\ExtractInsentifValuePerSalesmanJob;
use App\Jobs\ExtractInsentifQtyPerSeJob;
use App\Jobs\ExtractInsentifSeIptJob;
use App\Jobs\ExtractInsentifProdukGrupJob;
use App\Jobs\ExtractInsentifSeRoJob;
use App\Jobs\ExtractInsentifSeVisitJob;
use App\Jobs\ExtractInsentifSpvRwoJob;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class Jobs extends Component
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

    // State
    public function updatedMonthFilter()
    {
        $this->reset(['batchId', 'logLines', 'batchStatus']);
    }

    public function updatedYearFilter()
    {
        $this->reset(['batchId', 'logLines', 'batchStatus']);
    }

    public function startProcess()
    {

        $bulanFormat = sprintf('%04d-%02d', $this->yearFilter, $this->monthFilter);

        // Membuat log batch baru
        $batch = ImportBatch::create([
            'file_name' => 'Proses Insentif ETL - ' . Carbon::create($this->yearFilter, $this->monthFilter)->format('F Y'),
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ekstraksi ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        // Dispatch Job berantai (Chain)
        Bus::chain([
            new \App\Jobs\ExtractMingguanMasterDistributorJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractMingguanMasterSpvJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractMingguanMasterSalesmanJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractMingguanValuePerSalesmanJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractMingguanQtyPerSeJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractMingguanSeIptJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractMingguanProdukGrupJob($batch->id),
            new \App\Jobs\ExtractInsentifSeRoJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractInsentifSeVisitJob($bulanFormat, $batch->id),
            new \App\Jobs\ExtractInsentifSpvRwoJob($bulanFormat, $batch->id),
        ])->dispatch();
        
        // Karena chain dijalankan, kita perlu me-refresh instance batch
        // jika kebetulan environment menggunakan QUEUE_CONNECTION=sync
        $batch->refresh();
        $batch->addLog('success', 'Rantai Job (Chain) telah berhasil dikirim ke background!');
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
        
        $total = 10;
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
        return view('livewire.others.insentif.mingguan.jobs')->layout('layouts.app');
    }
}
