<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ImportBatch;
use App\Jobs\ProcessSalesInvoiceImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SalesInvoiceImport extends Component
{
    use WithFileUploads;

    public $excel_file;
    public $batchId;
    public $batchStatus;
    public $logLines = [];
    public $totalRows = 0;
    public $processedRows = 0;

    public function import()
    {
        $this->validate([
            'excel_file' => 'required|mimes:xls,xlsx|max:10240', // Maks 10MB
        ]);

        $this->reset(['batchId', 'batchStatus', 'logLines', 'totalRows', 'processedRows']);
        
        $originalFilename = $this->excel_file->getClientOriginalName();
        $filePath = $this->excel_file->storeAs(
            'livewire-tmp',
            Str::random(40) . '.' . $this->excel_file->getClientOriginalExtension()
        );

        $filenameParts = explode('_', $originalFilename);
        $distributorCodeCode = $filenameParts[0] ?? null;

        if (empty($distributorCodeCode)) {
            $this->addError('excel_file', 'Format nama file tidak valid. Harus diawali dengan kode cabang.');
            Storage::delete($filePath);
            return;
        }

        $batch = ImportBatch::create([
            'file_name' => $originalFilename,
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'Menambahkan proses ke dalam antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog(); // Lakukan sinkronisasi awal untuk menampilkan pesan "pending"
        
        ProcessSalesInvoiceImport::dispatch($filePath, $this->batchId, $distributorCodeCode);
        
        $this->reset('excel_file');
    }

    /**
     * Sinkronisasi log dan progres dari database.
     */
    public function syncLog()
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $this->logLines = $batch->log_lines ?? [];
                $this->batchStatus = $batch->status;
                $this->totalRows = $batch->total_rows;
                $this->processedRows = $batch->processed_rows;
            }
        }
    }
    
    public function render()
    {
        return view('livewire.sales-invoice.import')->layout('layouts.app');
    }
}

