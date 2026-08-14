<?php

namespace App\Livewire\Others\Insentif\Target;

use App\Exports\TargetSpvVtkpExport;
use App\Exports\TargetSpvVtkpTemplateExport;
use App\Imports\TargetSpvVtkpImport;
use App\Models\TargetSpvVtkp as TargetSpvVtkpModel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TargetSpvVtkp extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $yearFilter = '';
    public $quarterFilter = 'Q1';

    // Modal Import
    public $isImportModalOpen = false;
    public $importExcel;

    // Modal Edit
    public $isEditModalOpen = false;
    public $editCabang = '';
    public $editTargets = [];
    public $productGroups = []; // Dynamic columns

    public function mount()
    {
        $this->yearFilter = date('Y');
        $month = date('n');
        if ($month <= 3) $this->quarterFilter = 'Q1';
        elseif ($month <= 6) $this->quarterFilter = 'Q2';
        elseif ($month <= 9) $this->quarterFilter = 'Q3';
        else $this->quarterFilter = 'Q4';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingYearFilter()
    {
        $this->resetPage();
    }

    public function updatingQuarterFilter()
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
        return Excel::download(new TargetSpvVtkpTemplateExport(), "Template_Target_SPV_VTKP_{$timestamp}.xlsx");
    }

    public function processImport()
    {
        $this->validate([
            'importExcel' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new TargetSpvVtkpImport();
            Excel::import($import, $this->importExcel);
            
            $errors = $import->getErrors();
            $success = $import->getSuccessCount();

            if (count($errors) > 0) {
                // Generate error text
                $errorText = "Laporan Error Import Target SPV VTKP\n";
                $errorText .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
                $errorText .= str_repeat("-", 50) . "\n\n";
                foreach ($errors as $idx => $err) {
                    $errorText .= ($idx + 1) . ". " . $err . "\n";
                }

                $base64 = base64_encode($errorText);
                
                $this->dispatch('download-error-file', [
                    'name' => 'Error_Import_Target_SPV_VTKP_' . date('Ymd_His') . '.txt',
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
        return Excel::download(new TargetSpvVtkpExport($this->yearFilter, $this->quarterFilter), "Target_SPV_VTKP_{$yearStr}_{$this->quarterFilter}_{$timestamp}.xlsx");
    }

    private function getMonthsInQuarter()
    {
        $year = $this->yearFilter ?: date('Y');
        $q = $this->quarterFilter;
        if ($q == 'Q1') return [sprintf('%s-01', $year), sprintf('%s-02', $year), sprintf('%s-03', $year)];
        if ($q == 'Q2') return [sprintf('%s-04', $year), sprintf('%s-05', $year), sprintf('%s-06', $year)];
        if ($q == 'Q3') return [sprintf('%s-07', $year), sprintf('%s-08', $year), sprintf('%s-09', $year)];
        return [sprintf('%s-10', $year), sprintf('%s-11', $year), sprintf('%s-12', $year)];
    }

    // -- EDIT --
    public function openEditModal($cabang)
    {
        $this->editCabang = $cabang;
        $this->editTargets = [];

        $months = $this->getMonthsInQuarter();
        $records = TargetSpvVtkpModel::whereIn('bulan', $months)
                                ->where('cabang', $cabang)
                                ->get();
        
        foreach ($this->productGroups as $idx => $pg) {
            $record = $records->where('produk_grup', $pg)->first();
            $this->editTargets[$idx] = $record ? $record->target : 0;
        }
        
        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editCabang', 'editTargets']);
    }

    public function saveEdit()
    {
        $rules = [];
        foreach ($this->productGroups as $idx => $pg) {
            $rules["editTargets.{$idx}"] = 'required|numeric|min:0';
        }
        
        if (!empty($rules)) {
            $this->validate($rules);
        }

        if ($this->editCabang) {
            $months = $this->getMonthsInQuarter();

            foreach ($this->productGroups as $idx => $pg) {
                $targetValue = $this->editTargets[$idx] ?? 0;
                
                foreach ($months as $bulan) {
                    TargetSpvVtkpModel::updateOrCreate(
                        [
                            'bulan' => $bulan,
                            'cabang' => $this->editCabang,
                            'produk_grup' => $pg
                        ],
                        [
                            'target' => $targetValue,
                        ]
                    );
                }
            }

            session()->flash('message', 'Data berhasil diperbarui.');
            $this->closeEditModal();
        }
    }

    // -- DELETE --
    public function deleteData($cabang)
    {
        $months = $this->getMonthsInQuarter();
        TargetSpvVtkpModel::whereIn('bulan', $months)
                     ->where('cabang', $cabang)
                     ->delete();
                     
        session()->flash('message', 'Data berhasil dihapus.');
    }

    public function render()
    {
        $months = $this->getMonthsInQuarter();

        // Cari semua Produk Grup yang ada di kuartal ini
        $this->productGroups = TargetSpvVtkpModel::whereIn('bulan', $months)
            ->select('produk_grup')
            ->distinct()
            ->orderBy('produk_grup')
            ->pluck('produk_grup')
            ->toArray();

        $query = TargetSpvVtkpModel::query()
            ->select('cabang')
            ->whereIn('bulan', $months);

        foreach ($this->productGroups as $idx => $pg) {
            $query->selectRaw("MAX(CASE WHEN produk_grup = ? THEN target ELSE 0 END) as target_{$idx}", [$pg]);
        }

        $query->groupBy('cabang');

        if (!empty($this->search)) {
            $query->where('cabang', 'ilike', '%' . $this->search . '%');
        }

        $data = $query->orderBy('cabang')
                      ->paginate(100);

        return view('livewire.others.insentif.target.target-spv-vtkp', [
            'data' => $data,
        ]);
    }
}
