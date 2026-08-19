<?php

namespace App\Livewire\Others\Insentif\Target;

use App\Exports\TargetSeValueExport;
use App\Exports\TargetSeValueTemplateExport;
use App\Imports\TargetSeValueImport;
use App\Models\TargetSeValue as TargetSeValueModel;
use App\Traits\EnforcesMenuPermissions;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TargetSeValue extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'others.insentif.target.index';

    public $search = '';
    public $monthFilter = '';

    // Modal Import
    public $isImportModalOpen = false;
    public $importExcel;

    // Modal Edit
    public $isEditModalOpen = false;
    public $editId = null;
    public $editBulan = '';
    public $editDistributorCode = '';
    public $editSalesmanCode = '';
    public $editTarget = 0;

    // Modal Swap
    public $isSwapModalOpen = false;
    public $swapSourceId = null;
    public $swapTargetId = '';
    public $swapListSE = [];
    public $swapSourceData = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
    {
        $this->resetPage();
    }

    // -- IMPORT --
    public function openImportModal()
    {
        $this->authorizeAction('can_import');
        $this->reset(['importExcel']);
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->reset(['importExcel']);
    }

    public function downloadFormat()
    {
        $timestamp = date('Ymd_His');
        return Excel::download(new TargetSeValueTemplateExport(), "Template_Target_SE_Value_{$timestamp}.xlsx");
    }

    public function processImport()
    {
        $this->authorizeAction('can_import');
        $this->validate([
            'importExcel' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new TargetSeValueImport();
            Excel::import($import, $this->importExcel);
            
            $errors = $import->getErrors();
            $success = $import->getSuccessCount();

            if (count($errors) > 0) {
                // Generate error text
                $errorText = "Laporan Error Import Target SE Value\n";
                $errorText .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
                $errorText .= str_repeat("-", 50) . "\n\n";
                foreach ($errors as $idx => $err) {
                    $errorText .= ($idx + 1) . ". " . $err . "\n";
                }

                $base64 = base64_encode($errorText);
                
                $this->dispatch('download-error-file', [
                    'name' => 'Error_Import_Target_SE_Value_' . date('Ymd_His') . '.txt',
                    'content' => 'data:text/plain;base64,' . $base64
                ]);

                \App\Helpers\ActivityLogger::log('Import Target SE Value', "Import selesai dengan $success baris sukses, namun terdapat " . count($errors) . " error.");

                session()->flash('warning', "Import selesai dengan $success baris sukses, namun terdapat " . count($errors) . " error. (File error otomatis didownload)");
            } else {
                \App\Helpers\ActivityLogger::log('Import Target SE Value', "Data berhasil di-import ($success baris sukses).");
                session()->flash('message', "Data berhasil di-import ($success baris).");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }

        $this->closeImportModal();
        $this->resetPage();
    }

    // -- EXPORT --
    public function export()
    {
        $this->authorizeAction('can_export');
        $timestamp = date('Ymd_His');
        $yearStr = $this->yearFilter ?: 'ALL';
        
        \App\Helpers\ActivityLogger::log('Export Target SE Value', "Mengekspor data Target SE Value tahun {$yearStr}");
        
        return Excel::download(new TargetSeValueExport($this->yearFilter), "Target_SE_Value_{$yearStr}_{$timestamp}.xlsx");
    }

    // -- EDIT --
    public function openEditModal($id)
    {
        $this->authorizeAction('can_edit');
        $data = TargetSeValueModel::findOrFail($id);
        
        $this->editId = $data->id;
        $this->editBulan = $data->bulan;
        $this->editDistributorCode = $data->distributor_code;
        $this->editSalesmanCode = $data->salesman_code;
        $this->editTarget = $data->target;
        
        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editId', 'editBulan', 'editDistributorCode', 'editSalesmanCode', 'editTarget']);
    }

    public function saveEdit()
    {
        $this->authorizeAction('can_edit');
        $this->validate([
            'editTarget' => 'required|numeric|min:0',
        ]);

        if ($this->editId) {
            $data = TargetSeValueModel::findOrFail($this->editId);
            $data->update([
                'target' => $this->editTarget,
            ]);

            \App\Helpers\ActivityLogger::log('Edit Target SE Value', "Memperbarui data Target SE Value bulan {$this->editBulan} untuk Salesman {$this->editSalesmanCode}");

            session()->flash('message', 'Data berhasil diperbarui.');
            $this->closeEditModal();
        }
    }

    // -- DELETE --
    public function deleteData($id)
    {
        $this->authorizeAction('can_delete');
        $data = TargetSeValueModel::findOrFail($id);
        
        \App\Helpers\ActivityLogger::log('Delete Target SE Value', "Menghapus data Target SE Value bulan {$data->bulan} untuk Salesman {$data->salesman_code}");
        
        $data->delete();
        session()->flash('message', 'Data berhasil dihapus.');
    }

    // -- SWAP TARGET --
    public function openSwapModal($id)
    {
        $this->authorizeAction('can_edit');
        
        $this->swapSourceData = TargetSeValueModel::findOrFail($id);
        $this->swapSourceId = $this->swapSourceData->id;
        
        // Cari SE lain di bulan yang sama dan distributor yang sama
        $this->swapListSE = TargetSeValueModel::where('bulan', $this->swapSourceData->bulan)
            ->where('distributor_code', $this->swapSourceData->distributor_code)
            ->where('id', '!=', $this->swapSourceId)
            ->orderBy('salesman_code')
            ->get();
            
        $this->swapTargetId = '';
        $this->isSwapModalOpen = true;
    }

    public function closeSwapModal()
    {
        $this->isSwapModalOpen = false;
        $this->reset(['swapSourceId', 'swapTargetId', 'swapListSE', 'swapSourceData']);
    }

    public function processSwap()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'swapTargetId' => 'required',
        ], [
            'swapTargetId.required' => 'Pilih Salesman tujuan untuk ditukar targetnya.',
        ]);

        if ($this->swapSourceId && $this->swapTargetId) {
            $source = TargetSeValueModel::findOrFail($this->swapSourceId);
            $target = TargetSeValueModel::findOrFail($this->swapTargetId);

            // Simpan nilai lama
            $sourceOldTarget = $source->target;
            $targetOldTarget = $target->target;

            // Tukar nilai target
            $source->update(['target' => $targetOldTarget]);
            $target->update(['target' => $sourceOldTarget]);

            \App\Helpers\ActivityLogger::log('Swap Target SE Value', "Menukar Target SE Value bulan {$source->bulan} di Distributor {$source->distributor_code} antara Salesman {$source->salesman_code} dan {$target->salesman_code}");

            session()->flash('message', 'Target berhasil ditukar.');
            $this->closeSwapModal();
        }
    }

    public function render()
    {
        $query = TargetSeValueModel::query();

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('distributor_code', 'like', '%' . $this->search . '%')
                  ->orWhere('salesman_code', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->monthFilter)) {
            $query->where('bulan', $this->monthFilter);
        }

        $data = $query->orderBy('bulan', 'desc')
                      ->orderBy('distributor_code')
                      ->orderBy('salesman_code')
                      ->paginate(100);

        return view('livewire.others.insentif.target.target-se-value', [
            'data' => $data,
        ]);
    }
}
