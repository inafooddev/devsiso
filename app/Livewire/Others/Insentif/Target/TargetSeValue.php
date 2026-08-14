<?php

namespace App\Livewire\Others\Insentif\Target;

use App\Exports\TargetSeValueExport;
use App\Exports\TargetSeValueTemplateExport;
use App\Imports\TargetSeValueImport;
use App\Models\TargetSeValue as TargetSeValueModel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TargetSeValue extends Component
{
    use WithPagination, WithFileUploads;

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

                session()->flash('warning', "Import selesai dengan $success baris sukses, namun terdapat " . count($errors) . " error. (File error otomatis didownload)");
            } else {
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
        $timestamp = date('Ymd_His');
        $monthStr = $this->monthFilter ?: 'ALL';
        return Excel::download(new TargetSeValueExport($this->monthFilter), "Target_SE_Value_{$monthStr}_{$timestamp}.xlsx");
    }

    // -- EDIT --
    public function openEditModal($id)
    {
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
        $this->validate([
            'editTarget' => 'required|numeric|min:0',
        ]);

        if ($this->editId) {
            $data = TargetSeValueModel::findOrFail($this->editId);
            $data->update([
                'target' => $this->editTarget,
            ]);

            session()->flash('message', 'Data berhasil diperbarui.');
            $this->closeEditModal();
        }
    }

    // -- DELETE --
    public function deleteData($id)
    {
        $data = TargetSeValueModel::findOrFail($id);
        $data->delete();
        session()->flash('message', 'Data berhasil dihapus.');
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
