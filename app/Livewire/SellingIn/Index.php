<?php

namespace App\Livewire\SellingIn;

use Livewire\Component;
use App\Models\SellingIn;
use App\Models\ImportBatch;
use App\Imports\SellingInImport;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Properti Filter
    public $search = '';
    public $filterYear = '';

    // Properti UI & Import
    public $excel_file;
    public $isImportModalOpen = false;
    public $batchId;
    public $batchStatus;
    public $logLines = [];
    public $totalRows = 0;
    public $processedRows = 0;

    protected $queryString = ['search', 'filterYear'];

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $query = SellingIn::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama_distributor', 'like', '%' . $this->search . '%')
                  ->orWhere('kd_distributor', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_produk', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterYear) {
            $query->where('tahun', $this->filterYear);
        }

        return view('livewire.selling-in.index', [
            'data' => $query->latest()->paginate(10),
            'years' => SellingIn::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun')
        ])->layout('layouts.app');
    }

    public function import()
    {
        $this->validate(['excel_file' => 'required|mimes:xls,xlsx|max:20480']);

        $batch = ImportBatch::create([
            'file_name' => $this->excel_file->getClientOriginalName(),
            'status' => 'processing',
            'log_lines' => [['type' => 'info', 'message' => 'Memulai proses import Selling In...']]
        ]);

        $this->batchId = $batch->id;
        $path = $this->excel_file->store('temp');

        try {
            // Kita hitung estimasi baris untuk progress bar
            $rows = Excel::toArray(new \stdClass(), Storage::path($path));
            $this->totalRows = count($rows[0] ?? []) - 1;
            $batch->update(['total_rows' => $this->totalRows]);

            Excel::import(new SellingInImport($batch), $path);
            
            $batch->updateStatus('completed', 'Import selesai dengan sukses.');
            session()->flash('message', 'Data Selling In berhasil diimport.');
            $this->reset('excel_file');
        } catch (\Exception $e) {
            $batch->updateStatus('failed', 'Kesalahan fatal: ' . $e->getMessage());
            $this->addError('excel_file', 'Terjadi kesalahan saat mengolah file.');
        } finally {
            Storage::delete($path);
            $this->syncLog();
        }
    }

    public function syncLog()
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $this->logLines = $batch->log_lines ?? [];
                $this->batchStatus = $batch->status;
                $this->processedRows = $batch->processed_rows;
                $this->totalRows = $batch->total_rows;
            }
        }
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->reset(['batchId', 'logLines', 'batchStatus', 'processedRows', 'totalRows']);
    }
}