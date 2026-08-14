<?php

namespace App\Livewire\Others\Insentif\Target;

use App\Exports\TargetKacabExport;
use App\Exports\TargetKacabTemplateExport;
use App\Imports\TargetKacabImport;
use App\Models\TargetKacab as TargetKacabModel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TargetKacab extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $monthFilter = '';

    // Modal Import
    public $isImportModalOpen = false;
    public $importExcel;

    // Modal Edit
    public $isEditModalOpen = false;
    public $editId;
    public $editBulan = '';
    public $editCabang = '';
    public $editTarget = 0;

    public function mount()
    {
        $this->monthFilter = date('Y-m');
    }

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
        return Excel::download(new TargetKacabTemplateExport(), "Template_Target_Kacab_{$timestamp}.xlsx");
    }

    public function processImport()
    {
        $this->validate([
            'importExcel' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new TargetKacabImport();
            Excel::import($import, $this->importExcel);
            
            $errors = $import->getErrors();
            $success = $import->getSuccessCount();

            if (count($errors) > 0) {
                // Generate error text
                $errorText = "Laporan Error Import Target Kacab\n";
                $errorText .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
                $errorText .= str_repeat("-", 50) . "\n\n";
                foreach ($errors as $idx => $err) {
                    $errorText .= ($idx + 1) . ". " . $err . "\n";
                }

                $base64 = base64_encode($errorText);
                
                $this->dispatch('download-error-file', [
                    'name' => 'Error_Import_Target_Kacab_' . date('Ymd_His') . '.txt',
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
        return Excel::download(new TargetKacabExport($this->monthFilter), "Target_Kacab_{$monthStr}_{$timestamp}.xlsx");
    }

    // -- EDIT --
    public function openEditModal($id)
    {
        $record = TargetKacabModel::find($id);
        if ($record) {
            $this->editId = $record->id;
            $this->editBulan = $record->bulan;
            $this->editCabang = $record->cabang;
            $this->editTarget = $record->target;
            
            $this->isEditModalOpen = true;
        }
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editId', 'editBulan', 'editCabang', 'editTarget']);
    }

    public function saveEdit()
    {
        $this->validate([
            'editTarget' => 'required|numeric|min:0',
        ]);

        if ($this->editId) {
            $record = TargetKacabModel::find($this->editId);
            if ($record) {
                $record->update([
                    'target' => $this->editTarget,
                ]);
                
                session()->flash('message', 'Data berhasil diperbarui.');
                $this->closeEditModal();
            }
        }
    }

    // -- DELETE --
    public function deleteData($id)
    {
        TargetKacabModel::destroy($id);
        session()->flash('message', 'Data berhasil dihapus.');
    }

    public function render()
    {
        $query = TargetKacabModel::query();

        if (!empty($this->search)) {
            $query->where('cabang', 'ilike', '%' . $this->search . '%');
        }

        if (!empty($this->monthFilter)) {
            $query->where('bulan', $this->monthFilter);
        }

        $data = $query->orderBy('bulan', 'desc')
                      ->orderBy('cabang')
                      ->paginate(100);

        return view('livewire.others.insentif.target.target-kacab', [
            'data' => $data,
        ]);
    }
}
