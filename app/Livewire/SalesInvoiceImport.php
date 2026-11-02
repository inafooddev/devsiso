<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ImportBatch;
use App\Jobs\ProcessSalesInvoiceImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ConfigSalesInvoiceDistributor;
use App\Models\MasterDistributor;
use App\Models\UnitMapping;
use App\Models\UnmappedUnit;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\SalesInvoicePeriodGuard;
use App\Services\SalesInvoiceSpreadsheetScanner;
use App\Helpers\UnitHelper;

class SalesInvoiceImport extends Component
{
    use WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'sales-invoice-report.index';

    public $excel_file;
    public $selectedDistributorCode = '';
    public $isDistributorAutoDetected = false;
    public $distributorSearch = '';
    public $targetMonth;
    public $targetYear;

    // Self-Healing System: File temporary storage & Modal States
    public $storedTempFilePath = null;
    public $storedOriginalFilename = null;

    // 1. Quick Header Mapping Modal
    public $showQuickHeaderModal = false;
    public $unresolvedHeaders = [];
    public $availableFileHeaders = [];
    public $saveHeaderToConfig = true;

    // 2. Quick Unit Mapping Modal
    public $showQuickUnitModal = false;
    public $pendingUnmappedUnits = [];
    public $availableStandardUnits = ['CTN', 'PCK', 'PCS'];
    public $saveUnitToDatabase = true;

    public $batchId;
    public $batchStatus;
    public $logLines = [];
    public $totalRows = 0;
    public $processedRows = 0;

    /**
     * Inisialisasi awal
     */
    public function mount()
    {
        $this->targetMonth = (int)date('n');
        $this->targetYear = (int)date('Y');

        // Jika ada proses batch yang sedang berjalan, langsung pantau
        $latestBatch = ImportBatch::latest()->first();
        if ($latestBatch && in_array($latestBatch->status, ['pending', 'processing'])) {
            $this->batchId = $latestBatch->id;
            $this->syncLog();
        }
    }

    /**
     * Hook ketika file dipilih oleh user
     */
    public function updatedExcelFile()
    {
        $this->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx|max:20480', // Maks 20MB
        ], [
            'excel_file.mimes' => 'Berkas harus berupa file Excel (.xlsx atau .xls).',
            'excel_file.max'   => 'Ukuran file maksimal 20 MB.',
        ]);

        $originalFilename = $this->excel_file->getClientOriginalName();

        // Otomatis deteksi kode cabang dari nama file jika ada (misal DIBDG001_sales.xlsx atau DIBDG001-sales.xlsx)
        if (preg_match('/^([A-Za-z0-9]+)[_\-\s]/', $originalFilename, $matches)) {
            $code = strtoupper($matches[1]);
            $hasConfig = ConfigSalesInvoiceDistributor::where('distributor_code', $code)->exists();
            if ($hasConfig) {
                $this->selectedDistributorCode = $code;
                $this->isDistributorAutoDetected = true;
                $this->distributorSearch = '';
            }
        }
    }

    /**
     * Memilih distributor secara manual dari dropdown / list
     */
    public function selectDistributor($code)
    {
        $this->selectedDistributorCode = $code;
        $this->isDistributorAutoDetected = false;
        $this->distributorSearch = '';
    }

    /**
     * Membatalkan pilihan distributor
     */
    public function clearDistributor()
    {
        $this->selectedDistributorCode = '';
        $this->isDistributorAutoDetected = false;
        $this->distributorSearch = '';
    }

    /**
     * Memulai proses import dengan Self-Healing Pre-Check
     */
    public function import()
    {
        $this->validate([
            'excel_file'              => 'required|file|mimes:xls,xlsx|max:20480',
            'selectedDistributorCode' => 'required|string|exists:config_sales_invoice_distributor,distributor_code',
            'targetMonth'             => 'required|integer|between:1,12',
            'targetYear'              => 'required|integer|between:2020,2035',
        ], [
            'selectedDistributorCode.required' => 'Silakan pilih distributor terlebih dahulu sebelum memulai proses impor.',
            'targetMonth.required'             => 'Pilih bulan target transaksi.',
            'targetYear.required'              => 'Pilih tahun target transaksi.',
        ]);

        $this->reset(['batchId', 'batchStatus', 'logLines', 'totalRows', 'processedRows', 'showQuickHeaderModal', 'showQuickUnitModal', 'unresolvedHeaders', 'pendingUnmappedUnits']);

        // Simpan file ke temporary storage
        $this->storedOriginalFilename = $this->excel_file->getClientOriginalName();
        $this->storedTempFilePath = $this->excel_file->storeAs(
            'livewire-tmp',
            Str::random(40) . '.' . $this->excel_file->getClientOriginalExtension()
        );

        $this->runPreImportChecks();
    }

    /**
     * Menjalankan pra-pemeriksaan dokumen (Sheet, Baris Header, Periode, Kolom Inti, dan Satuan)
     */
    protected function runPreImportChecks()
    {
        try {
            $fullPath = Storage::path($this->storedTempFilePath);
            $distributorCode = $this->selectedDistributorCode;

            $configModel = ConfigSalesInvoiceDistributor::where('distributor_code', $distributorCode)->first();
            if (!$configModel) {
                throw new \Exception("Konfigurasi tidak ditemukan untuk kode distributor '{$distributorCode}'.");
            }

            $config = is_array($configModel->config) ? $configModel->config : json_decode($configModel->config, true);

            $spreadsheet = IOFactory::load($fullPath);
            $sheet = SalesInvoiceSpreadsheetScanner::findBestWorksheet($spreadsheet, $config);
            $headerRowIndex = SalesInvoiceSpreadsheetScanner::findHeaderRowIndex($sheet, $config);

            // 1. [SMART PERIOD GUARD] Validasi kesesuaian periode file vs pilihan UI
            $periodCheck = SalesInvoicePeriodGuard::checkConsistency(
                $sheet,
                $config,
                (int)$this->targetMonth,
                (int)$this->targetYear
            );

            if (!$periodCheck['is_valid']) {
                $this->addError('excel_file', $periodCheck['error_message']);
                $this->cancelQuickModal();
                return;
            }

            // 2. [CORE HEADERS RESOLUTION] Periksa apakah kolom wajib inti tersedia
            $highestCol = $sheet->getHighestDataColumn();
            $headerCells = $sheet->rangeToArray("A{$headerRowIndex}:{$highestCol}{$headerRowIndex}")[0] ?? [];

            $this->availableFileHeaders = array_values(array_unique(array_filter(array_map('trim', $headerCells))));
            $fileHeaderMap = [];
            foreach ($headerCells as $idx0 => $val) {
                $norm = SalesInvoiceSpreadsheetScanner::normalize((string)$val);
                if ($norm !== '') {
                    $fileHeaderMap[$norm] = $idx0;
                }
            }

            $coreFields = [
                'invoice_no'    => 'Nomor Faktur',
                'invoice_date'  => 'Tanggal Faktur',
                'customer_code' => 'Kode Pelanggan',
                'product_code'  => 'Kode Produk',
                'net_amount'    => 'Nilai Netto / Total Bersih',
            ];

            $this->unresolvedHeaders = [];
            foreach ($coreFields as $colKey => $colLabel) {
                $expected = trim((string)($config[$colKey]['header_inv_dist'] ?? ''));
                $normExpected = SalesInvoiceSpreadsheetScanner::normalize($expected);
                $found = false;

                if ($normExpected !== '') {
                    if (isset($fileHeaderMap[$normExpected])) {
                        $found = true;
                    } else {
                        $cleanExpected = preg_replace('/[^a-z0-9]/', '', $normExpected);
                        foreach ($fileHeaderMap as $fNorm => $fIdx) {
                            if ($cleanExpected === preg_replace('/[^a-z0-9]/', '', $fNorm)) {
                                $found = true;
                                break;
                            }
                        }
                    }
                }

                if (!$found) {
                    // Cari tebakan terbaik dari header file untuk auto-suggest
                    $bestSuggestion = '';
                    $highestSimilarity = 0;
                    foreach ($this->availableFileHeaders as $opt) {
                        similar_text(strtolower($opt), strtolower($expected), $percent);
                        if ($percent > $highestSimilarity && $percent > 35) {
                            $highestSimilarity = $percent;
                            $bestSuggestion = $opt;
                        }
                    }

                    $this->unresolvedHeaders[$colKey] = [
                        'label'           => $colLabel,
                        'config_value'    => $expected,
                        'selected_header' => $bestSuggestion,
                    ];
                }
            }

            // Jika ada kolom wajib yang tidak ditemukan, munculkan Modal Pemetaan Cepat
            if (!empty($this->unresolvedHeaders)) {
                $this->showQuickHeaderModal = true;
                return;
            }

            // 3. [UNIT MAPPING CHECK] Periksa apakah ada satuan baru yang belum dipetakan
            $this->checkUnitMapping($sheet, $config, $headerCells, $distributorCode);

        } catch (\Exception $e) {
            $this->addError('excel_file', 'Gagal memvalidasi berkas: ' . $e->getMessage());
            $this->cancelQuickModal();
        }
    }

    /**
     * Pemeriksaan satuan pada file Excel
     */
    protected function checkUnitMapping($sheet, array $config, array $headerCells, string $distributorCode)
    {
        $unitHeader = trim((string)($config['unit']['header_inv_dist'] ?? ''));
        $unitIndex = isset($config['unit']) && $config['unit']['index'] > 0 ? (int)$config['unit']['index'] : null;

        if ($unitHeader !== '') {
            $normUnitHeader = SalesInvoiceSpreadsheetScanner::normalize($unitHeader);
            foreach ($headerCells as $hIdx => $hVal) {
                if (SalesInvoiceSpreadsheetScanner::normalize((string)$hVal) === $normUnitHeader) {
                    $unitIndex = $hIdx + 1;
                    break;
                }
            }
        }

        if ($unitIndex !== null) {
            $highestRow = min((int)$sheet->getHighestDataRow(), 1000);
            $rawUnits = [];
            for ($rowNum = 2; $rowNum <= $highestRow; $rowNum++) {
                $c = $sheet->getCell([$unitIndex, $rowNum]);
                $cellVal = $c->isFormula() 
                    ? (function() use ($c) {
                        try { return $c->getCalculatedValue(); }
                        catch (\Throwable $e) { return $c->getOldCalculatedValue(); }
                    })()
                    : $c->getValue();
                $unitStr = trim((string)$cellVal);
                if ($unitStr !== '') {
                    $rawUnits[] = strtoupper($unitStr);
                }
            }

            $uniqueRawUnits = array_unique($rawUnits);
            if (!empty($uniqueRawUnits)) {
                $mappedUnits = UnitMapping::where('distributor_code', $distributorCode)
                    ->whereIn('raw_unit', $uniqueRawUnits)
                    ->pluck('raw_unit')
                    ->toArray();

                $unmapped = array_diff($uniqueRawUnits, $mappedUnits);
                if (!empty($unmapped)) {
                    $this->pendingUnmappedUnits = [];
                    foreach ($unmapped as $u) {
                        $this->pendingUnmappedUnits[$u] = [
                            'mapped_unit' => 'CTN', // Default pilihan satuan standar
                        ];
                    }
                    $this->showQuickUnitModal = true;
                    return;
                }
            }
        }

        // Jika seluruh pengecekan lolos, kirim ke antrian import
        $this->dispatchImportJob();
    }

    /**
     * Menerapkan hasil pencocokan dari Modal Pemetaan Header Cepat
     */
    public function applyQuickHeaderMapping()
    {
        $distributorCode = $this->selectedDistributorCode;
        $configModel = ConfigSalesInvoiceDistributor::where('distributor_code', $distributorCode)->first();
        if (!$configModel) {
            $this->addError('excel_file', "Konfigurasi tidak ditemukan untuk distributor {$distributorCode}.");
            $this->cancelQuickModal();
            return;
        }

        $config = is_array($configModel->config) ? $configModel->config : json_decode($configModel->config, true);

        foreach ($this->unresolvedHeaders as $colKey => $data) {
            $selected = trim((string)($data['selected_header'] ?? ''));
            if ($selected !== '') {
                $config[$colKey]['header_inv_dist'] = $selected;
                $idx = array_search($selected, $this->availableFileHeaders);
                if ($idx !== false) {
                    $config[$colKey]['index'] = $idx + 1;
                }
            }
        }

        if ($this->saveHeaderToConfig) {
            $configModel->config = $config;
            $configModel->save();
        }

        $this->showQuickHeaderModal = false;

        // Lanjutkan pemeriksaan unit sebelum dispatch
        try {
            $fullPath = Storage::path($this->storedTempFilePath);
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = SalesInvoiceSpreadsheetScanner::findBestWorksheet($spreadsheet, $config);
            $headerRowIndex = SalesInvoiceSpreadsheetScanner::findHeaderRowIndex($sheet, $config);
            $highestCol = $sheet->getHighestDataColumn();
            $headerCells = $sheet->rangeToArray("A{$headerRowIndex}:{$highestCol}{$headerRowIndex}")[0] ?? [];

            $this->checkUnitMapping($sheet, $config, $headerCells, $distributorCode);
        } catch (\Exception $e) {
            $this->addError('excel_file', 'Gagal memproses file: ' . $e->getMessage());
            $this->cancelQuickModal();
        }
    }

    /**
     * Menerapkan hasil pemetaan dari Modal Satuan Cepat
     */
    public function applyQuickUnitMapping()
    {
        $distributorCode = $this->selectedDistributorCode;

        foreach ($this->pendingUnmappedUnits as $rawUnit => $data) {
            $mapped = strtoupper(trim((string)($data['mapped_unit'] ?? 'CTN')));
            if ($this->saveUnitToDatabase) {
                UnitMapping::updateOrCreate(
                    ['distributor_code' => $distributorCode, 'raw_unit' => strtoupper($rawUnit)],
                    ['mapped_unit' => $mapped]
                );
            }
            UnmappedUnit::where('distributor_code', $distributorCode)->where('raw_unit', strtoupper($rawUnit))->delete();
        }

        UnitHelper::clearCache($distributorCode);
        $this->showQuickUnitModal = false;

        $this->dispatchImportJob();
    }

    /**
     * Memulai Job Antrian Import di Latar Belakang
     */
    protected function dispatchImportJob()
    {
        $filePath = $this->storedTempFilePath;
        $originalFilename = $this->storedOriginalFilename;
        $distributorCode = $this->selectedDistributorCode;

        $monthName = Carbon::create($this->targetYear, $this->targetMonth, 1)->translatedFormat('F');
        $distributor = MasterDistributor::find($distributorCode);
        $distName = $distributor ? $distributor->distributor_name : $distributorCode;

        $batch = ImportBatch::create([
            'file_name' => "{$distributorCode} - {$originalFilename}",
            'status'    => 'pending',
            'log_lines' => [
                ['type' => 'info', 'message' => "Distributor: {$distName} ({$distributorCode})"],
                ['type' => 'info', 'message' => "Periode Target: {$monthName} {$this->targetYear}"],
                ['type' => 'info', 'message' => 'Menambahkan proses ke dalam antrian...'],
            ]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        \App\Helpers\ActivityLogger::log('Import Sales Invoice', "Memulai proses import file: {$originalFilename} untuk distributor {$distributorCode} periode {$monthName} {$this->targetYear}");

        ProcessSalesInvoiceImport::dispatch(
            $filePath,
            $this->batchId,
            $distributorCode,
            $this->targetMonth,
            $this->targetYear
        );

        $this->reset(['storedTempFilePath', 'storedOriginalFilename', 'showQuickHeaderModal', 'showQuickUnitModal', 'unresolvedHeaders', 'pendingUnmappedUnits']);
    }

    /**
     * Membatalkan proses modal dan membersihkan file sementara
     */
    public function cancelQuickModal()
    {
        if ($this->storedTempFilePath && Storage::exists($this->storedTempFilePath)) {
            Storage::delete($this->storedTempFilePath);
        }

        $this->reset([
            'storedTempFilePath',
            'storedOriginalFilename',
            'showQuickHeaderModal',
            'showQuickUnitModal',
            'unresolvedHeaders',
            'pendingUnmappedUnits',
            'excel_file'
        ]);
    }

    /**
     * Sinkronisasi log dan progres dari database
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

    /**
     * Menghapus file yang telah dipilih dan mereset deteksi otomatis distributor jika ada
     */
    public function clearSelectedFile()
    {
        $this->reset('excel_file');
        if ($this->isDistributorAutoDetected) {
            $this->selectedDistributorCode = '';
            $this->isDistributorAutoDetected = false;
        }
    }

    /**
     * Membuka log dari riwayat batch sebelumnya
     */
    public function viewBatchLog($batchId)
    {
        $this->batchId = $batchId;
        $this->syncLog();
    }

    /**
     * Mengunduh daftar baris yang dilewati (skipped) dalam format Excel (.xlsx)
     */
    public function downloadSkippedRows($batchId)
    {
        $filePath = "skipped-imports/batch_{$batchId}.json";
        if (!Storage::disk('local')->exists($filePath)) {
            session()->flash('error', 'Detail baris yang dilewati untuk proses ini tidak ditemukan.');
            return null;
        }

        $skippedData = json_decode(Storage::disk('local')->get($filePath), true) ?? [];
        if (empty($skippedData)) {
            session()->flash('error', 'Tidak ada data baris yang dilewati.');
            return null;
        }

        $batch = ImportBatch::find($batchId);
        $batchName = $batch ? Str::slug($batch->file_name) : "batch_{$batchId}";
        $filename = "Baris_Dilewati_{$batchName}_" . date('Ymd_His') . ".xlsx";

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Baris Dilewati');

        // Header kolom
        $headers = [
            'No',
            'Baris File Excel',
            'Alasan Dilewati (Skip Reason)',
            'No. Faktur',
            'Tanggal Faktur',
            'Nama Pelanggan / Toko',
            'Kode Produk',
            'Nama Produk',
            'Cuplikan Data Mentah (Sample)',
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Styling baris header
        $headerRange = 'A1:I1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F2937');
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $rowNum = 2;
        foreach ($skippedData as $idx => $item) {
            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", $item['row_number'] ?? '');
            $sheet->setCellValue("C{$rowNum}", $item['skip_reason'] ?? '');
            $sheet->setCellValueExplicit("D{$rowNum}", (string)($item['invoice_no'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E{$rowNum}", $item['invoice_date'] ?? '');
            $sheet->setCellValue("F{$rowNum}", $item['customer'] ?? '');
            $sheet->setCellValueExplicit("G{$rowNum}", (string)($item['product_code'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("H{$rowNum}", $item['product_name'] ?? '');
            $sheet->setCellValue("I{$rowNum}", $item['raw_sample'] ?? '');

            // Styling zebra stripe
            if ($rowNum % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9FAFB');
            }

            // Tanda peringatan merah lembut di kolom alasan skip
            $sheet->getStyle("C{$rowNum}")->getFont()->setBold(true)->getColor()->setARGB('FFB45309');

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        // Auto-fit kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Daftar batch ID yang memiliki file detail baris dilewati (skipped)
     */
    public function getBatchesWithSkippedRowsProperty(): array
    {
        if (!Storage::disk('local')->exists('skipped-imports')) {
            return [];
        }

        $files = Storage::disk('local')->files('skipped-imports');
        $batchIds = [];
        foreach ($files as $file) {
            if (preg_match('/batch_(\d+)\.json$/', $file, $matches)) {
                $batchIds[(int)$matches[1]] = true;
            }
        }
        return $batchIds;
    }

    /**
     * Daftar distributor yang memiliki konfigurasi (dengan batasan hak akses region)
     */
    public function getDistributorsProperty()
    {
        $user = auth()->user();
        $query = MasterDistributor::join('config_sales_invoice_distributor', 'master_distributors.distributor_code', '=', 'config_sales_invoice_distributor.distributor_code')
            ->select('master_distributors.distributor_code', 'master_distributors.distributor_name');

        if ($user && method_exists($user, 'hasRole') && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('master_distributors.region_code', $user->region_code);
        }

        if (strlen($this->distributorSearch) >= 2) {
            $query->where(function ($q) {
                $q->where('master_distributors.distributor_name', 'ilike', '%' . $this->distributorSearch . '%')
                  ->orWhere('master_distributors.distributor_code', 'ilike', '%' . $this->distributorSearch . '%');
            });
        }

        return $query->orderBy('master_distributors.distributor_name')->take(50)->get();
    }

    /**
     * Riwayat batch impor dalam 24 jam terakhir (1 hari)
     */
    public function getRecentBatchesProperty()
    {
        $batches = ImportBatch::where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->get();

        // Jika dalam 24 jam terakhir belum ada impor, tampilkan 5 riwayat terakhir agar tabel tidak kosong
        if ($batches->isEmpty()) {
            $batches = ImportBatch::latest()->take(5)->get();
        }

        return $batches;
    }

    public function render()
    {
        return view('livewire.sell-out.import', [
            'distributors'           => $this->distributors,
            'recentBatches'          => $this->recentBatches,
            'batchesWithSkippedRows' => $this->batchesWithSkippedRows,
        ])->layout('layouts.app');
    }
}
