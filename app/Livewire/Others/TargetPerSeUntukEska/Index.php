<?php

namespace App\Livewire\Others\TargetPerSeUntukEska;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\TargetPerSeUntukEska;
use App\Imports\TargetPerSeUntukEskaImport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Table Filters & Search
    public $search = '';
    public $tahunFilter = '';
    public $bulanFilter = '';
    public $regionFilter = '';
    public $branchFilter = '';
    public $perPage = 100;

    // Import Modal State
    public $isImportModalOpen = false;
    public $importFile;
    public $truncatePeriod = true;

    // Real-time Import Progress State
    public $isImporting = false;
    public $importId = null;
    public $importProgressCurrent = 0;
    public $importProgressPercent = 0;

    // Import Result Summary Modal State
    public $isImportResultModalOpen = false;
    public $resultImportedCount = 0;
    public $resultSkippedCount = 0;
    public $resultTruncatedCount = 0;
    public $resultTotalValue = 0;
    public $resultExecutionTime = 0;
    public $resultErrorLogs = [];

    // Export Modal State
    public $isExportModalOpen = false;
    public $exportTahun = '';
    public $exportBulan = '';
    public $exportRegion = '';
    public $exportBranches = [];
    public $exportSellingPoints = [];

    protected $queryString = [
        'search'       => ['except' => ''],
        'tahunFilter'  => ['except' => ''],
        'bulanFilter'  => ['except' => ''],
        'regionFilter' => ['except' => ''],
        'branchFilter' => ['except' => ''],
    ];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedTahunFilter() { $this->resetPage(); }
    public function updatedBulanFilter() { $this->resetPage(); }
    public function updatedRegionFilter() { $this->resetPage(); }
    public function updatedBranchFilter() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    // --- IMPORT METHODS ---
    public function openImportModal()
    {
        $this->reset(['importFile', 'isImporting', 'importId', 'importProgressCurrent', 'importProgressPercent']);
        $this->truncatePeriod = true;
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        if ($this->isImporting) return; // Mencegah penutupan saat import sedang berjalan
        $this->isImportModalOpen = false;
        $this->reset(['importFile', 'isImporting', 'importId', 'importProgressCurrent', 'importProgressPercent']);
    }

    public function closeImportResultModal()
    {
        $this->isImportResultModalOpen = false;
    }

    public function checkImportProgress()
    {
        if ($this->importId && $this->isImporting) {
            $progressData = Cache::get("import_progress_{$this->importId}");
            if ($progressData) {
                $this->importProgressCurrent = $progressData['current'] ?? 0;
            }
        }
    }

    public function processImport()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480',
        ], [
            'importFile.required' => 'Silakan pilih file CSV/Excel terlebih dahulu.',
            'importFile.mimes'    => 'Format file harus berupa .csv, .xlsx, atau .xls.',
            'importFile.max'      => 'Ukuran file maksimal adalah 20MB.',
        ]);

        $this->importId = uniqid('imp_');
        $this->isImporting = true;
        $this->importProgressCurrent = 0;
        $this->importProgressPercent = 10;

        Cache::put("import_progress_{$this->importId}", [
            'current' => 0,
            'status'  => 'processing',
        ], 300);

        $startTime = microtime(true);

        try {
            $import = new TargetPerSeUntukEskaImport($this->truncatePeriod, $this->importId);
            Excel::import($import, $this->importFile);

            $this->resultExecutionTime = round(microtime(true) - $startTime, 2);
            $this->resultImportedCount = $import->importedCount;
            $this->resultSkippedCount = $import->skippedCount;
            $this->resultTruncatedCount = $import->truncatedCount;
            $this->resultTotalValue = $import->totalValue;
            $this->resultErrorLogs = $import->errorLogs;

            Cache::forget("import_progress_{$this->importId}");

            $this->isImporting = false;
            $this->isImportModalOpen = false;
            $this->reset(['importFile']);

            // Tampilkan Modal Ringkasan Hasil Import
            $this->isImportResultModalOpen = true;
        } catch (\Exception $e) {
            $this->isImporting = false;
            Cache::forget("import_progress_{$this->importId}");
            session()->flash('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\TargetPerSeUntukEskaTemplateExport(), 'Template_Import_Target_Per_SE_Eska.xlsx');
    }

    // --- EXPORT MODAL METHODS ---
    public function openExportModal()
    {
        $latestTahun = TargetPerSeUntukEska::max('tahun');
        $this->exportTahun = $this->tahunFilter ?: ($latestTahun ?: date('Y'));
        $this->exportBulan = $this->bulanFilter ?: date('m');
        $this->exportRegion = $this->regionFilter ?: '';
        $this->exportBranches = [];
        $this->exportSellingPoints = [];
        $this->isExportModalOpen = true;
    }

    public function closeExportModal()
    {
        $this->isExportModalOpen = false;
    }

    public function updatedExportRegion()
    {
        $this->exportBranches = [];
        $this->exportSellingPoints = [];
    }

    public function updatedExportBranches()
    {
        $this->exportSellingPoints = [];
    }

    public function toggleAllExportBranches()
    {
        $availableBranches = $this->getExportBranchOptionsProperty()->pluck('branch')->toArray();
        if (count($this->exportBranches) === count($availableBranches)) {
            $this->exportBranches = [];
        } else {
            $this->exportBranches = $availableBranches;
        }
        $this->updatedExportBranches();
    }

    public function toggleAllExportSellingPoints()
    {
        $availableSPs = $this->getExportSellingPointOptionsProperty()->pluck('sellingpoint')->toArray();
        if (count($this->exportSellingPoints) === count($availableSPs)) {
            $this->exportSellingPoints = [];
        } else {
            $this->exportSellingPoints = $availableSPs;
        }
    }

    public function getExportBranchOptionsProperty()
    {
        $query = TargetPerSeUntukEska::select('branch')->whereNotNull('branch')->where('branch', '<>', '')->distinct();
        if ($this->exportRegion) {
            $query->where('region', $this->exportRegion);
        }
        return $query->orderBy('branch')->get();
    }

    public function getExportSellingPointOptionsProperty()
    {
        $query = TargetPerSeUntukEska::select('sellingpoint')->whereNotNull('sellingpoint')->where('sellingpoint', '<>', '')->distinct();
        if ($this->exportRegion) {
            $query->where('region', $this->exportRegion);
        }
        if (!empty($this->exportBranches)) {
            $query->whereIn('branch', $this->exportBranches);
        }
        return $query->orderBy('sellingpoint')->get();
    }

    public function processExport()
    {
        $fileName = 'Target_Per_SE_ESKA_' . date('Ymd_His') . '.xlsx';
        $export = new \App\Exports\TargetPerSeUntukEskaExport(
            $this->exportTahun,
            $this->exportBulan,
            $this->exportRegion,
            $this->exportBranches,
            $this->exportSellingPoints
        );

        $this->closeExportModal();
        return Excel::download($export, $fileName);
    }

    public function render()
    {
        $query = TargetPerSeUntukEska::query();

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('salesman', 'like', "%{$search}%")
                  ->orWhere('outlet', 'like', "%{$search}%")
                  ->orWhere('sellingpoint', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%");
            });
        }

        if (!empty($this->tahunFilter)) {
            $query->where('tahun', $this->tahunFilter);
        }

        if (!empty($this->bulanFilter)) {
            $query->where('bulan', $this->bulanFilter);
        }

        if (!empty($this->regionFilter)) {
            $query->where('region', $this->regionFilter);
        }

        if (!empty($this->branchFilter)) {
            $query->where('branch', $this->branchFilter);
        }

        // Ringkasan KPI
        $totalRecords = (clone $query)->count();
        $totalTargetValue = (clone $query)->sum('value');
        $totalSalesmanCount = (clone $query)->whereNotNull('salesman')->distinct('salesman')->count('salesman');
        $totalOutletCount = (clone $query)->whereNotNull('outlet')->distinct('outlet')->count('outlet');

        $data = $query->orderBy('tahun', 'desc')
                      ->orderBy('bulan', 'desc')
                      ->orderBy('region')
                      ->orderBy('branch')
                      ->paginate($this->perPage);

        // List opsi filter tabel & modal
        $tahunList = TargetPerSeUntukEska::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $bulanList = TargetPerSeUntukEska::select('bulan')->distinct()->orderBy('bulan', 'asc')->pluck('bulan');
        $regionList = TargetPerSeUntukEska::select('region')->whereNotNull('region')->where('region', '<>', '')->distinct()->orderBy('region')->pluck('region');
        $branchList = TargetPerSeUntukEska::select('branch')->whereNotNull('branch')->where('branch', '<>', '')->distinct()->orderBy('branch')->pluck('branch');

        return view('livewire.others.target-per-se-untuk-eska.index', [
            'data'               => $data,
            'totalRecords'       => $totalRecords,
            'totalTargetValue'   => $totalTargetValue,
            'totalSalesmanCount' => $totalSalesmanCount,
            'totalOutletCount'   => $totalOutletCount,
            'tahunList'          => $tahunList,
            'bulanList'          => $bulanList,
            'regionList'         => $regionList,
            'branchList'         => $branchList,
            'exportBranchOptions' => $this->exportBranchOptions,
            'exportSellingPointOptions' => $this->exportSellingPointOptions,
        ])->layout('layouts.app');
    }
}
