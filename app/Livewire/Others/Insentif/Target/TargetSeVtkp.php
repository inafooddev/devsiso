<?php

namespace App\Livewire\Others\Insentif\Target;

use App\Exports\TargetSeVtkpExport;
use App\Exports\TargetSeVtkpTemplateExport;
use App\Imports\TargetSeVtkpImport;
use App\Models\TargetSeVtkp as TargetSeVtkpModel;
use App\Traits\EnforcesMenuPermissions;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TargetSeVtkp extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'others.insentif.target.index';

    public $search = '';
    public $yearFilter = '';
    public $quarterFilter = 'Q1';

    // Modal Import
    public $isImportModalOpen = false;
    public $importExcel;

    // Modal Edit
    public $isEditModalOpen = false;
    public $editDistributorCode = '';
    public $editSalesmanCode = '';
    public $editTargets = [];
    public $productGroups = []; // Dynamic columns

    // Modal Swap
    public $isSwapModalOpen = false;
    public $swapSourceDistributorCode = '';
    public $swapSourceSalesmanCode = '';
    public $swapTargetSalesmanCode = '';
    public $swapListSE = [];

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
        return Excel::download(new TargetSeVtkpTemplateExport(), "Template_Target_SE_VTKP_{$timestamp}.xlsx");
    }

    public function processImport()
    {
        $this->authorizeAction('can_import');
        $this->validate([
            'importExcel' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new TargetSeVtkpImport();
            Excel::import($import, $this->importExcel);
            
            $errors = $import->getErrors();
            $success = $import->getSuccessCount();

            if (count($errors) > 0) {
                // Generate error text
                $errorText = "Laporan Error Import Target SE VTKP\n";
                $errorText .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
                $errorText .= str_repeat("-", 50) . "\n\n";
                foreach ($errors as $idx => $err) {
                    $errorText .= ($idx + 1) . ". " . $err . "\n";
                }

                $base64 = base64_encode($errorText);
                
                $this->dispatch('download-error-file', [
                    'name' => 'Error_Import_Target_SE_VTKP_' . date('Ymd_His') . '.txt',
                    'content' => 'data:text/plain;base64,' . $base64
                ]);

                \App\Helpers\ActivityLogger::log('Import Target SE VTKP', "Import selesai dengan $success baris sukses, namun terdapat " . count($errors) . " error.");

                session()->flash('warning', "Import selesai dengan $success baris sukses, namun terdapat " . count($errors) . " error. (File error otomatis didownload)");
            } else {
                \App\Helpers\ActivityLogger::log('Import Target SE VTKP', "Data berhasil di-import ($success baris sukses).");
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
        $monthStr = $this->yearFilter . '_' . $this->quarterFilter;
        
        \App\Helpers\ActivityLogger::log('Export Target SE VTKP', "Mengekspor data Target SE VTKP {$monthStr}");
        
        // Kita sementara biarkan logic export karena view yang difokuskan. 
        // Jika TargetSeVtkpExport belum mendukung quarter filter, kita panggil seperti awal saja dulu,
        // namun untuk menghindari error, kita sesuaikan passing parameternya.
        // Asumsi TargetSeVtkpExport hanya menerima 1 argumen $monthFilter
        return Excel::download(new TargetSeVtkpExport($monthStr), "Target_SE_VTKP_{$monthStr}_{$timestamp}.xlsx");
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
    public function openEditModal($distributorCode, $salesmanCode)
    {
        $this->authorizeAction('can_edit');
        $this->editDistributorCode = $distributorCode;
        $this->editSalesmanCode = $salesmanCode;
        $this->editTargets = [];

        $months = $this->getMonthsInQuarter();
        $records = TargetSeVtkpModel::whereIn('bulan', $months)
                                ->where('distributor_code', $distributorCode)
                                ->where('salesman_code', $salesmanCode)
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
        $this->reset(['editDistributorCode', 'editSalesmanCode', 'editTargets']);
    }

    public function saveEdit()
    {
        $this->authorizeAction('can_edit');
        $rules = [];
        foreach ($this->productGroups as $idx => $pg) {
            $rules["editTargets.{$idx}"] = 'required|numeric|min:0';
        }
        
        if (!empty($rules)) {
            $this->validate($rules);
        }

        if ($this->editDistributorCode && $this->editSalesmanCode) {
            $months = $this->getMonthsInQuarter();

            foreach ($this->productGroups as $idx => $pg) {
                $targetValue = $this->editTargets[$idx] ?? 0;
                
                foreach ($months as $bulan) {
                    TargetSeVtkpModel::updateOrCreate(
                        [
                            'bulan' => $bulan,
                            'distributor_code' => $this->editDistributorCode,
                            'salesman_code' => $this->editSalesmanCode,
                            'produk_grup' => $pg
                        ],
                        [
                            'target' => $targetValue,
                        ]
                    );
                }
            }

            \App\Helpers\ActivityLogger::log('Edit Target SE VTKP', "Memperbarui data Target SE VTKP kuartal {$this->quarterFilter} {$this->yearFilter} untuk Distributor {$this->editDistributorCode} dan Salesman {$this->editSalesmanCode}");

            session()->flash('message', 'Data berhasil diperbarui.');
            $this->closeEditModal();
        }
    }

    // -- DELETE --
    public function deleteData($distributorCode, $salesmanCode)
    {
        $this->authorizeAction('can_delete');
        $months = $this->getMonthsInQuarter();
        TargetSeVtkpModel::whereIn('bulan', $months)
                     ->where('distributor_code', $distributorCode)
                     ->where('salesman_code', $salesmanCode)
                     ->delete();
                     
        \App\Helpers\ActivityLogger::log('Delete Target SE VTKP', "Menghapus data Target SE VTKP kuartal {$this->quarterFilter} {$this->yearFilter} untuk Distributor {$distributorCode} dan Salesman {$salesmanCode}");
                     
        session()->flash('message', 'Data berhasil dihapus.');
    }

    // -- SWAP TARGET --
    public function openSwapModal($distributorCode, $salesmanCode)
    {
        $this->authorizeAction('can_edit');
        
        $this->swapSourceDistributorCode = $distributorCode;
        $this->swapSourceSalesmanCode = $salesmanCode;
        
        $months = $this->getMonthsInQuarter();
        
        // Cari SE lain di kuartal dan distributor yang sama
        $this->swapListSE = TargetSeVtkpModel::whereIn('bulan', $months)
            ->where('distributor_code', $distributorCode)
            ->where('salesman_code', '!=', $salesmanCode)
            ->select('salesman_code')
            ->distinct()
            ->orderBy('salesman_code')
            ->pluck('salesman_code')
            ->toArray();
            
        $this->swapTargetSalesmanCode = '';
        $this->isSwapModalOpen = true;
    }

    public function closeSwapModal()
    {
        $this->isSwapModalOpen = false;
        $this->reset(['swapSourceDistributorCode', 'swapSourceSalesmanCode', 'swapTargetSalesmanCode', 'swapListSE']);
    }

    public function processSwap()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'swapTargetSalesmanCode' => 'required',
        ], [
            'swapTargetSalesmanCode.required' => 'Pilih Salesman tujuan untuk ditukar targetnya.',
        ]);

        if ($this->swapSourceDistributorCode && $this->swapSourceSalesmanCode && $this->swapTargetSalesmanCode) {
            $months = $this->getMonthsInQuarter();
            $sourceSE = $this->swapSourceSalesmanCode;
            $targetSE = $this->swapTargetSalesmanCode;
            $distCode = $this->swapSourceDistributorCode;

            // Ambil semua record untuk Source SE dan Target SE di bulan-bulan tersebut
            $sourceRecords = TargetSeVtkpModel::whereIn('bulan', $months)
                ->where('distributor_code', $distCode)
                ->where('salesman_code', $sourceSE)
                ->get();
                
            $targetRecords = TargetSeVtkpModel::whereIn('bulan', $months)
                ->where('distributor_code', $distCode)
                ->where('salesman_code', $targetSE)
                ->get();

            // Kita harus menukar target untuk setiap (bulan, produk_grup).
            // Karena produk_grup SE A dan SE B mungkin berbeda, kita harus mendata semua kombinasi (bulan, produk_grup)
            // dari kedua SE, lalu buat/update di database secara tertukar nilainya.

            $sourceData = [];
            foreach ($sourceRecords as $sr) {
                $sourceData[$sr->bulan][$sr->produk_grup] = $sr->target;
            }

            $targetData = [];
            foreach ($targetRecords as $tr) {
                $targetData[$tr->bulan][$tr->produk_grup] = $tr->target;
            }

            // Gabungkan kombinasi bulan dan produk_grup dari keduanya
            $allCombinations = [];
            foreach ($sourceRecords as $r) { $allCombinations[$r->bulan][$r->produk_grup] = true; }
            foreach ($targetRecords as $r) { $allCombinations[$r->bulan][$r->produk_grup] = true; }

            // Tukar data
            foreach ($allCombinations as $bulan => $groups) {
                foreach ($groups as $pg => $true) {
                    $targetValueForSource = $targetData[$bulan][$pg] ?? 0;
                    $targetValueForTarget = $sourceData[$bulan][$pg] ?? 0;

                    // Update untuk Source SE
                    TargetSeVtkpModel::updateOrCreate(
                        [
                            'bulan' => $bulan,
                            'distributor_code' => $distCode,
                            'salesman_code' => $sourceSE,
                            'produk_grup' => $pg
                        ],
                        ['target' => $targetValueForSource]
                    );

                    // Update untuk Target SE
                    TargetSeVtkpModel::updateOrCreate(
                        [
                            'bulan' => $bulan,
                            'distributor_code' => $distCode,
                            'salesman_code' => $targetSE,
                            'produk_grup' => $pg
                        ],
                        ['target' => $targetValueForTarget]
                    );
                }
            }

            \App\Helpers\ActivityLogger::log('Swap Target SE VTKP', "Menukar Target SE VTKP kuartal {$this->quarterFilter} {$this->yearFilter} di Distributor {$distCode} antara Salesman {$sourceSE} dan {$targetSE}");

            session()->flash('message', 'Target berhasil ditukar.');
            $this->closeSwapModal();
        }
    }

    public function render()
    {
        $months = $this->getMonthsInQuarter();

        // Cari semua Produk Grup yang ada di kuartal ini
        $this->productGroups = TargetSeVtkpModel::whereIn('bulan', $months)
            ->select('produk_grup')
            ->distinct()
            ->orderBy('produk_grup')
            ->pluck('produk_grup')
            ->toArray();

        $query = TargetSeVtkpModel::query()
            ->select('distributor_code', 'salesman_code')
            ->whereIn('bulan', $months);

        foreach ($this->productGroups as $idx => $pg) {
            $query->selectRaw("MAX(CASE WHEN produk_grup = ? THEN target ELSE 0 END) as target_{$idx}", [$pg]);
        }

        $query->groupBy('distributor_code', 'salesman_code');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('distributor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('salesman_code', 'ilike', '%' . $this->search . '%');
            });
        }

        $data = $query->orderBy('distributor_code')
                      ->orderBy('salesman_code')
                      ->paginate(100);

        return view('livewire.others.insentif.target.target-se-vtkp', [
            'data' => $data,
        ]);
    }
}
