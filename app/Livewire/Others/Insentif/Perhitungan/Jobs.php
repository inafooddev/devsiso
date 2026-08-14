<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

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

    // State
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;

    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    
    public function mount()
    {
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
    }

    public function applyFilters()
    {
        $this->hasAppliedFilters = true;
        $this->isFilterModalOpen = false;
        $this->reset(['batchId', 'logLines', 'batchStatus']);
    }

    public function resetFilters()
    {
        $this->reset(['monthFilter', 'yearFilter']);
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
        $this->hasAppliedFilters = false;
        $this->reset(['batchId', 'logLines', 'batchStatus']);
    }

    public function startProcess()
    {
        if (!$this->hasAppliedFilters) {
            session()->flash('error', 'Silakan terapkan filter terlebih dahulu.');
            return;
        }

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
            new ExtractInsentifMasterDistributorJob($bulanFormat, $batch->id),
            new ExtractInsentifMasterSalesmanJob($bulanFormat, $batch->id),
            new ExtractInsentifValuePerSalesmanJob($bulanFormat, $batch->id),
            new ExtractInsentifQtyPerSeJob($bulanFormat, $batch->id),
            new ExtractInsentifSeIptJob($bulanFormat, $batch->id),
            new ExtractInsentifProdukGrupJob($batch->id),
            new ExtractInsentifSeRoJob($bulanFormat, $batch->id),
            new ExtractInsentifSeVisitJob($bulanFormat, $batch->id),
            new ExtractInsentifSpvRwoJob($bulanFormat, $batch->id),
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

    public function render()
    {
        return view('livewire.others.insentif.perhitungan.jobs');
    }
}
