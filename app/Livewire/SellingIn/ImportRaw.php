<?php

namespace App\Livewire\SellingIn;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ImportBatch;
use App\Jobs\ProcessSellingInRawImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\EnforcesMenuPermissions;

class ImportRaw extends Component
{
    use WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'selling-in.index'; // Sesuaikan jika ada menu spesifik

    public $excel_file;
    public $selectedMonth;
    public $batchId;
    public $batchStatus;
    public $logLines = [];
    public $totalRows = 0;
    public $processedRows = 0;

    public function import()
    {
        $this->validate([
            'selectedMonth' => 'required|date_format:Y-m',
            'excel_file' => 'required|mimes:xls,xlsx|max:20480', // Maks 20MB
        ], [
            'selectedMonth.required' => 'Pilih periode bulan dan tahun terlebih dahulu.',
            'excel_file.required' => 'Pilih file Excel terlebih dahulu.',
            'excel_file.mimes' => 'Format file harus berupa .xls atau .xlsx.',
            'excel_file.max' => 'Ukuran file maksimal adalah 20MB.'
        ]);

        $this->reset(['batchId', 'batchStatus', 'logLines', 'totalRows', 'processedRows']);
        
        $originalFilename = $this->excel_file->getClientOriginalName();
        $filePath = $this->excel_file->storeAs(
            'livewire-tmp',
            'selling_in_raw_' . Str::random(20) . '.' . $this->excel_file->getClientOriginalExtension()
        );

        // Buat batch record untuk tracking progress
        $batch = ImportBatch::create([
            'file_name' => $originalFilename,
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'File berhasil diunggah. Menambahkan ke antrean pemrosesan...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog(); // Tampilkan log awal
        
        \App\Helpers\ActivityLogger::log('Import Selling-In Raw', "Memulai antrean import file: {$originalFilename} untuk periode {$this->selectedMonth}");
        
        // Dispatch job ke background
        ProcessSellingInRawImport::dispatch($filePath, $this->batchId, $this->selectedMonth);
        
        $this->reset('excel_file');
    }

    public $generateBatchId;
    public $generateStatus;
    public $generateProgress = 0;
    public $generateTotal = 0;

    // Stepper UX
    public $currentStep = 1;

    // Dapodik Validation Properties
    public $unmappedDistributors = [];
    public $unregisteredProducts = [];
    public $masterDistributorsList = [];
    public $isGenerateLocked = false;
    
    // Quick Mapping Bindings
    public $quickMapSelections = [];

    public function mount()
    {
        $this->masterDistributorsList = \App\Models\MasterDistributor::select('distributor_code', 'distributor_name')
                                        ->orderBy('distributor_name')->get();
    }

    public function setStep($step)
    {
        if ($step == 2 && $this->batchStatus !== 'completed') {
            return; // Cegah lompat ke step 2 jika import belum selesai
        }
        if ($step == 3 && $this->isGenerateLocked) {
            return; // Cegah lompat ke step 3 jika validasi belum beres
        }
        $this->currentStep = $step;
    }

    public function updatedSelectedMonth()
    {
        $this->checkValidation();
    }

    public function checkValidation()
    {
        if (empty($this->selectedMonth)) {
            $this->unmappedDistributors = [];
            $this->unregisteredProducts = [];
            $this->isGenerateLocked = false;
            return;
        }

        $parsedDate = \Carbon\Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $year = $parsedDate->year;
        $month = $parsedDate->month;

        // 1. Cek Unmapped Distributors
        $this->unmappedDistributors = \Illuminate\Support\Facades\DB::select("
            SELECT DISTINCT raw.divisi, raw.wilayah, raw.kode_distributor, raw.distributor
            FROM selling_in_raws raw
            LEFT JOIN selling_in_distributor_mappings map
                ON raw.divisi = map.divisi
                AND raw.wilayah = map.wilayah
                AND raw.kode_distributor = map.kode_distributor
                AND raw.distributor = map.distributor
            WHERE EXTRACT(YEAR FROM raw.invoice_date) = ?
            AND EXTRACT(MONTH FROM raw.invoice_date) = ?
            AND map.id IS NULL
        ", [$year, $month]);

        // 2. Cek Unregistered Products
        $this->unregisteredProducts = \Illuminate\Support\Facades\DB::select("
            SELECT DISTINCT raw.kode_barang, raw.nama_barang
            FROM selling_in_raws raw
            LEFT JOIN master_produk_lama mpl
                ON raw.kode_barang = mpl.pcode_prc
            WHERE EXTRACT(YEAR FROM raw.invoice_date) = ?
            AND EXTRACT(MONTH FROM raw.invoice_date) = ?
            AND mpl.pcode_prc IS NULL
        ", [$year, $month]);

        // Hard Lock Condition
        $this->isGenerateLocked = count($this->unmappedDistributors) > 0 || count($this->unregisteredProducts) > 0;
    }

    public function saveQuickMapping($index, $divisi, $wilayah, $kodeDistributor, $distributor)
    {
        $masterCode = $this->quickMapSelections[$index] ?? null;

        if (empty($masterCode)) {
            $this->addError('quickmap.'.$index, 'Pilih Master Distributor terlebih dahulu.');
            return;
        }

        // Cek validitas master code
        if (!\App\Models\MasterDistributor::where('distributor_code', $masterCode)->exists()) {
            $this->addError('quickmap.'.$index, 'Kode Master Distributor tidak valid.');
            return;
        }

        \App\Models\SellingInDistributorMapping::create([
            'divisi' => $divisi,
            'wilayah' => $wilayah,
            'kode_distributor' => $kodeDistributor,
            'distributor' => $distributor,
            'distributor_code' => $masterCode,
        ]);

        unset($this->quickMapSelections[$index]);
        $this->checkValidation(); // Re-validate
    }

    /**
     * Sinkronisasi log dan progres dari database/cache secara real-time via polling.
     */
    public function syncLog()
    {
        // 1. Sync Import Raw Log
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $this->batchStatus = $batch->status;
                $this->totalRows = $batch->total_rows;

                if ($this->batchStatus === 'processing') {
                    $this->processedRows = \Illuminate\Support\Facades\Cache::get("import_batch_{$this->batchId}_progress", 0);
                    $this->logLines = \Illuminate\Support\Facades\Cache::get("import_batch_{$this->batchId}_logs", []);
                } else {
                    $this->processedRows = $batch->processed_rows;
                    $this->logLines = $batch->log_lines ?? [];
                    // Trigger validation check after import completes
                    if ($this->batchStatus === 'completed' && $this->processedRows > 0) {
                        $this->checkValidation();
                    }
                }
            }
        }

        // 2. Sync Generate Clean Log
        if ($this->generateBatchId) {
            $this->generateStatus = \Illuminate\Support\Facades\Cache::get("generate_clean_status_{$this->generateBatchId}", 'pending');
            $this->generateTotal = \Illuminate\Support\Facades\Cache::get("generate_clean_total_{$this->generateBatchId}", 0);
            $this->generateProgress = \Illuminate\Support\Facades\Cache::get("generate_clean_progress_{$this->generateBatchId}", 0);
            
            $logs = \Illuminate\Support\Facades\Cache::get("generate_clean_logs_{$this->generateBatchId}", []);
            if (!empty($logs)) {
                $this->logLines = $logs; // Override log terminal display
            }
        }
    }

    public function generateClean()
    {
        $this->checkValidation();
        if ($this->isGenerateLocked) {
            return;
        }

        $this->validate([
            'selectedMonth' => 'required|date_format:Y-m',
        ], [
            'selectedMonth.required' => 'Pilih periode bulan dan tahun terlebih dahulu.',
        ]);

        $this->reset(['batchId', 'batchStatus', 'logLines', 'totalRows', 'processedRows']);
        
        $this->generateBatchId = uniqid('gen_');
        $this->generateStatus = 'pending';
        $this->generateProgress = 0;
        $this->generateTotal = 0;
        $this->logLines = [['type' => 'info', 'message' => 'Memasukkan perintah Generate Clean ke antrean...']];

        // Set awal cache
        \Illuminate\Support\Facades\Cache::put("generate_clean_status_{$this->generateBatchId}", 'pending', 3600);
        \Illuminate\Support\Facades\Cache::put("generate_clean_logs_{$this->generateBatchId}", $this->logLines, 3600);

        \App\Jobs\ProcessSellingInGenerateClean::dispatch($this->generateBatchId, $this->selectedMonth);
    }
    
    public function render()
    {
        return view('livewire.selling-in.import-raw')->layout('layouts.app');
    }
}
