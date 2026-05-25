<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ImportBatch;
use App\Jobs\ProcessSalesInvoiceImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ConfigSalesInvoiceDistributor;
use App\Models\UnitMapping;
use App\Models\UnmappedUnit;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\EnforcesMenuPermissions;

class SalesInvoiceImport extends Component
{
    use WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'sales-invoice-report.index';

    public $excel_file;
    public $batchId;
    public $batchStatus;
    public $logLines = [];
    public $totalRows = 0;
    public $processedRows = 0;

    public function import()
    {
        $this->validate([
            'excel_file' => 'required|mimes:xls,xlsx|max:10240', // Maks 10MB
        ]);

        $this->reset(['batchId', 'batchStatus', 'logLines', 'totalRows', 'processedRows']);
        
        $originalFilename = $this->excel_file->getClientOriginalName();
        $filePath = $this->excel_file->storeAs(
            'livewire-tmp',
            Str::random(40) . '.' . $this->excel_file->getClientOriginalExtension()
        );

        $filenameParts = explode('_', $originalFilename);
        $distributorCodeCode = $filenameParts[0] ?? null;

        if (empty($distributorCodeCode)) {
            $this->addError('excel_file', 'Format nama file tidak valid. Harus diawali dengan kode cabang.');
            Storage::delete($filePath);
            return;
        }

        // [VALIDASI SMART] - Cek unit mapping sebelum kirim ke Job
        try {
            $fullPath = Storage::path($filePath);
            $allRows = Excel::toArray(new \stdClass(), $fullPath);
            
            if (!isset($allRows[0]) || count($allRows[0]) <= 1) {
                throw new \Exception('File Excel tidak berisi baris data.');
            }

            $configModel = ConfigSalesInvoiceDistributor::where('distributor_code', $distributorCodeCode)->first();
            if (!$configModel) {
                throw new \Exception("Konfigurasi tidak ditemukan untuk kode distributor '{$distributorCodeCode}'.");
            }
            
            $config = json_decode($configModel->config, true);
            $unitIndex = isset($config['unit']) && $config['unit']['index'] > 0 ? $config['unit']['index'] - 1 : null;
            
            if ($unitIndex !== null) {
                $rawUnits = [];
                // Skip header (index 0)
                for ($i = 1; $i < count($allRows[0]); $i++) {
                    if (isset($allRows[0][$i][$unitIndex])) {
                        $unitVal = trim((string)$allRows[0][$i][$unitIndex]);
                        if ($unitVal !== '') {
                            $rawUnits[] = strtoupper($unitVal);
                        }
                    }
                }
                
                $uniqueRawUnits = array_unique($rawUnits);
                if (!empty($uniqueRawUnits)) {
                    $mappedUnits = UnitMapping::where('distributor_code', $distributorCodeCode)
                        ->whereIn('raw_unit', $uniqueRawUnits)
                        ->pluck('raw_unit')
                        ->toArray();
                        
                    $unmappedUnits = array_diff($uniqueRawUnits, $mappedUnits);
                    
                    if (!empty($unmappedUnits)) {
                        // Simpan ke tabel unmapped_units agar admin bisa melihatnya di Management
                        foreach ($unmappedUnits as $unit) {
                            UnmappedUnit::firstOrCreate([
                                'distributor_code' => $distributorCodeCode,
                                'raw_unit' => $unit
                            ]);
                        }

                        $this->addError('excel_file', "Ada unit yang belum di mapping, silakan mapping dulu di menu Unit Mapping.");
                        Storage::delete($filePath);
                        return;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addError('excel_file', 'Gagal memvalidasi file: ' . $e->getMessage());
            Storage::delete($filePath);
            return;
        }

        $batch = ImportBatch::create([
            'file_name' => $originalFilename,
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'Menambahkan proses ke dalam antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog(); // Lakukan sinkronisasi awal untuk menampilkan pesan "pending"
        
        \App\Helpers\ActivityLogger::log('Import Sales Invoice', "Memulai proses import file: {$originalFilename}");
        
        ProcessSalesInvoiceImport::dispatch($filePath, $this->batchId, $distributorCodeCode);
        
        $this->reset('excel_file');
    }

    /**
     * Sinkronisasi log dan progres dari database.
     */
    public function syncLog()
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $this->logLines = $batch->log_lines ?? [];
                $this->batchStatus = $batch->status;
                $this->totalRows = $batch->total_rows;
                $this->processedRows = $batch->processed_rows;
            }
        }
    }
    
    public function render()
    {
        return view('livewire.sales-invoice.import')->layout('layouts.app');
    }
}

