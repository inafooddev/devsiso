<?php

namespace App\Livewire\Others\Insentif\Target;

use App\Exports\TargetSpvValueExport;
use App\Exports\TargetSpvValueTemplateExport;
use App\Imports\TargetSpvValueImport;
use App\Models\TargetPerDepo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TargetSpvValue extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $yearFilter = '';

    // Modal Import
    public $isImportModalOpen = false;
    public $importExcel;

    // Modal Edit
    public $isEditModalOpen = false;
    public $editBulan = '';
    public $editRegion = '';
    public $editArea = '';
    public $editCabang = '';
    public $editTargetReg = 0;
    public $editTargetFest = 0;

    public function mount()
    {
        $this->yearFilter = date('Y');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingYearFilter()
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
        return Excel::download(new TargetSpvValueTemplateExport(), "Template_Target_SPV_Value_{$timestamp}.xlsx");
    }

    public function processImport()
    {
        $this->validate([
            'importExcel' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new TargetSpvValueImport();
            Excel::import($import, $this->importExcel);
            
            $errors = $import->getErrors();
            $success = $import->getSuccessCount();

            if (count($errors) > 0) {
                // Generate error text
                $errorText = "Laporan Error Import Target SPV Value\n";
                $errorText .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
                $errorText .= str_repeat("-", 50) . "\n\n";
                foreach ($errors as $idx => $err) {
                    $errorText .= ($idx + 1) . ". " . $err . "\n";
                }

                $base64 = base64_encode($errorText);
                
                $this->dispatch('download-error-file', [
                    'name' => 'Error_Import_Target_SPV_Value_' . date('Ymd_His') . '.txt',
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
        $yearStr = $this->yearFilter ?: 'ALL';
        return Excel::download(new TargetSpvValueExport($this->yearFilter), "Target_SPV_Value_{$yearStr}_{$timestamp}.xlsx");
    }

    // -- EDIT --
    public function openEditModal($bulan, $cabang)
    {
        // Ambil data untuk cabang dan bulan tersebut
        $records = TargetPerDepo::where('bulan', $bulan)
                                ->where('cabang', $cabang)
                                ->get();
        
        if ($records->isEmpty()) {
            session()->flash('error', 'Data tidak ditemukan.');
            return;
        }

        $first = $records->first();
        $this->editBulan = $first->bulan;
        $this->editRegion = $first->region;
        $this->editArea = $first->area;
        $this->editCabang = $first->cabang;

        $this->editTargetReg = 0;
        $this->editTargetFest = 0;

        foreach ($records as $record) {
            if ($record->reg_fest === 'REG') {
                $this->editTargetReg = $record->target;
            } elseif ($record->reg_fest === 'FEST') {
                $this->editTargetFest = $record->target;
            }
        }
        
        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editBulan', 'editRegion', 'editArea', 'editCabang', 'editTargetReg', 'editTargetFest']);
    }

    public function saveEdit()
    {
        $this->validate([
            'editTargetReg' => 'required|numeric|min:0',
            'editTargetFest' => 'required|numeric|min:0',
        ]);

        if ($this->editBulan && $this->editCabang) {
            // Update or Create REG
            TargetPerDepo::updateOrCreate(
                [
                    'bulan' => $this->editBulan,
                    'cabang' => $this->editCabang,
                    'reg_fest' => 'REG'
                ],
                [
                    'region' => $this->editRegion,
                    'area' => $this->editArea,
                    'target' => $this->editTargetReg,
                ]
            );

            // Update or Create FEST
            TargetPerDepo::updateOrCreate(
                [
                    'bulan' => $this->editBulan,
                    'cabang' => $this->editCabang,
                    'reg_fest' => 'FEST'
                ],
                [
                    'region' => $this->editRegion,
                    'area' => $this->editArea,
                    'target' => $this->editTargetFest,
                ]
            );

            session()->flash('message', 'Data berhasil diperbarui.');
            $this->closeEditModal();
        }
    }

    // -- DELETE --
    public function deleteData($bulan, $cabang)
    {
        TargetPerDepo::where('bulan', $bulan)
                     ->where('cabang', $cabang)
                     ->delete();
                     
        session()->flash('message', 'Data berhasil dihapus.');
    }

    public function render()
    {
        $query = TargetPerDepo::query()
            ->select('bulan', 'region', 'area', 'cabang')
            ->selectRaw("SUM(CASE WHEN reg_fest = 'REG' THEN target ELSE 0 END) as target_reg")
            ->selectRaw("SUM(CASE WHEN reg_fest = 'FEST' THEN target ELSE 0 END) as target_fest")
            ->selectRaw("SUM(target) as total_target")
            ->groupBy('bulan', 'region', 'area', 'cabang');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('cabang', 'ilike', '%' . $this->search . '%')
                  ->orWhere('region', 'ilike', '%' . $this->search . '%')
                  ->orWhere('area', 'ilike', '%' . $this->search . '%');
            });
        }

        if (!empty($this->yearFilter)) {
            // Because 'bulan' format is YYYY-MM
            $query->where('bulan', 'like', $this->yearFilter . '-%');
        }

        $data = $query->orderBy('bulan', 'desc')
                      ->orderBy('region')
                      ->orderBy('area')
                      ->orderBy('cabang')
                      ->paginate(100);

        return view('livewire.others.insentif.target.target-spv-value', [
            'data' => $data,
        ]);
    }
}
