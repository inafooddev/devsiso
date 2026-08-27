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
    public $importMonth;
    public $generateMonth;
    public $lamaMonth;
    public $batchId;
    public $batchStatus;
    public $logLines = [];
    public $totalRows = 0;
    public $processedRows = 0;

    public function import()
    {
        $this->validate([
            'importMonth' => 'required|date_format:Y-m',
            'excel_file' => 'required|mimes:xls,xlsx|max:20480', // Maks 20MB
        ], [
            'importMonth.required' => 'Pilih periode bulan dan tahun terlebih dahulu.',
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
        
        \App\Helpers\ActivityLogger::log('Import Selling-In Raw', "Memulai antrean import file: {$originalFilename} untuk periode {$this->importMonth}");
        
        // Dispatch job ke background
        ProcessSellingInRawImport::dispatch($filePath, $this->batchId, $this->importMonth);
        
        $this->reset('excel_file');
    }

    public $generateBatchId;
    public $generateStatus;
    public $generateProgress = 0;
    public $generateTotal = 0;

    // Job Lama State
    public $lamaBatchId;
    public $lamaStatus;
    public $lamaProgress = 0;
    public $lamaTotal = 0;

    // Tabs UX
    public $currentTab = 'import';

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

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        
        // Refresh validasi otomatis jika masuk ke tab generate (untuk antisipasi ada mapping baru dari tab 3)
        if ($tab === 'generate') {
            $this->checkValidation();
        }
    }

    public function updatedGenerateMonth()
    {
        $this->checkValidation();
    }

    public function checkValidation()
    {
        if (empty($this->generateMonth)) {
            $this->unmappedDistributors = [];
            $this->unregisteredProducts = [];
            $this->isGenerateLocked = false;
            return;
        }

        $parsedDate = \Carbon\Carbon::createFromFormat('Y-m', $this->generateMonth);
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
            if (!empty($logs) && $this->currentTab === 'generate') {
                $this->logLines = $logs; // Override log terminal display
            }
        }

        // 3. Sync Job Lama Log
        if ($this->lamaBatchId) {
            $this->lamaStatus = \Illuminate\Support\Facades\Cache::get("lama_job_status_{$this->lamaBatchId}", 'pending');
            $this->lamaTotal = \Illuminate\Support\Facades\Cache::get("lama_job_total_{$this->lamaBatchId}", 0);
            $this->lamaProgress = \Illuminate\Support\Facades\Cache::get("lama_job_progress_{$this->lamaBatchId}", 0);
            
            $logs = \Illuminate\Support\Facades\Cache::get("lama_job_logs_{$this->lamaBatchId}", []);
            if (!empty($logs) && $this->currentTab === 'lama') {
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
            'generateMonth' => 'required|date_format:Y-m',
        ], [
            'generateMonth.required' => 'Pilih periode bulan dan tahun terlebih dahulu.',
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

        \App\Jobs\ProcessSellingInGenerateClean::dispatch($this->generateBatchId, $this->generateMonth);
    }

    public function runJobLama()
    {
        $this->validate([
            'lamaMonth' => 'required|date_format:Y-m',
        ], [
            'lamaMonth.required' => 'Pilih periode bulan dan tahun terlebih dahulu.',
        ]);

        $this->reset(['batchId', 'generateBatchId', 'logLines']);
        
        $this->lamaBatchId = uniqid('lama_');
        $this->lamaStatus = 'pending';
        $this->lamaProgress = 0;
        $this->lamaTotal = 0;
        $this->logLines = [['type' => 'info', 'message' => 'Memasukkan perintah Job Sell In Lama ke antrean...']];

        // Set awal cache
        \Illuminate\Support\Facades\Cache::put("lama_job_status_{$this->lamaBatchId}", 'pending', 3600);
        \Illuminate\Support\Facades\Cache::put("lama_job_logs_{$this->lamaBatchId}", $this->logLines, 3600);

        \App\Jobs\ProcessSellingInLamaJob::dispatch($this->lamaBatchId, $this->lamaMonth);
    }
    
    public function render()
    {
        return view('livewire.selling-in.import-raw')->layout('layouts.app');
    }
}
