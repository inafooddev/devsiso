<?php

namespace App\Livewire\MasterData\Product\MasterProdukLama\Traits;

use App\Models\MasterProdukLama;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterProdukLamaExport;
use App\Exports\MasterProdukLamaTemplateExport;
use App\Imports\MasterProdukLamaImport;

trait WithExportImport
{
    public $isImportModalOpen = false;
    
    // Import properties
    public $importFile;
    public $importErrors = [];
    public $importSuccessCount = 0;

    public function export()
    {
        // Use the query builder from the main component (via computed property or direct query)
        // Since we are moving to #[Computed], we can reconstruct the query here or call a method.
        // It's safer to reconstruct it so we don't rely on the pagination wrapper.
        
        $query = MasterProdukLama::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama_produk', 'like', '%' . $this->search . '%')
                  ->orWhere('pcode_prc', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status_product', $this->statusFilter);
        }
        if ($this->kategoriFilter !== '') {
            $query->where('kategory', $this->kategoriFilter);
        }
        if ($this->topItemFilter !== '') {
            $query->where('topitem', $this->topItemFilter);
        }
        if ($this->subbrandFilter !== '') {
            $query->where('subbrand', $this->subbrandFilter);
        }
        if ($this->divisiFilter !== '') {
            $query->where('divisi', $this->divisiFilter);
        }

        return Excel::download(new MasterProdukLamaExport($query), 'Master_Produk_Lama_' . date('Ymd_His') . '.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new MasterProdukLamaTemplateExport, 'Template_Import_Master_Produk_Lama.xlsx');
    }

    public function openImportModal()
    {
        $this->reset(['importFile', 'importErrors', 'importSuccessCount']);
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->reset(['importFile', 'importErrors', 'importSuccessCount']);
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $this->importErrors = [];
        $this->importSuccessCount = 0;

        try {
            $importer = new MasterProdukLamaImport();
            Excel::import($importer, $this->importFile);
            
            $this->importErrors = $importer->errorLogs;
            $this->importSuccessCount = $importer->successCount;

            if (empty($this->importErrors) && $this->importSuccessCount > 0) {
                session()->flash('message', 'Berhasil mengimpor ' . $this->importSuccessCount . ' data produk lama.');
                $this->closeImportModal();
                if (method_exists($this, 'resetPage')) {
                    $this->resetPage();
                }
            } elseif ($this->importSuccessCount > 0) {
                session()->flash('warning', 'Berhasil mengimpor ' . $this->importSuccessCount . ' data produk lama, dengan beberapa kesalahan (lihat di bawah).');
            } else {
                session()->flash('error', 'Gagal mengimpor data. Pastikan format file sesuai dengan template.');
            }
        } catch (\Exception $e) {
            Log::error('Error importing master produk lama: ' . $e->getMessage());
            $this->importErrors = ['Terjadi kesalahan saat mengimpor file: ' . $e->getMessage()];
        }
    }
}
