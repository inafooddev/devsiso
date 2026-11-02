<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportBatch;
use App\Models\ConfigSalesInvoiceDistributor;
use App\Imports\SalesInvoiceImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\SalesInvoicePeriodGuard;
use App\Services\SalesInvoiceSpreadsheetScanner;

class ProcessSalesInvoiceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 20 menit untuk file berukuran sangat besar

    protected string $filePath;
    protected int $batchId;
    protected string $distributorCode;
    protected ?int $targetMonth;
    protected ?int $targetYear;

    public function __construct(string $filePath, int $batchId, string $distributorCode, ?int $targetMonth = null, ?int $targetYear = null) 
    {
        $this->filePath = $filePath;
        $this->batchId = $batchId;
        $this->distributorCode = strtoupper(trim($distributorCode));
        $this->targetMonth = $targetMonth;
        $this->targetYear = $targetYear;
    }

    public function handle()
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        $batch->updateStatus('processing');

        try {
            $fullPath = Storage::path($this->filePath);

            $batch->addLog('info', 'Mulai memvalidasi berkas...');

            // Mencari konfigurasi berdasarkan distributor_code
            $configModel = ConfigSalesInvoiceDistributor::where('distributor_code', $this->distributorCode)->first();
            if (!$configModel) {
                throw new \Exception("Konfigurasi tidak ditemukan untuk kode distributor '{$this->distributorCode}'.");
            }
            
            $batch->addLog('info', "Konfigurasi untuk '{$this->distributorCode}' berhasil dimuat.");
            $config = is_array($configModel->config) ? $configModel->config : json_decode($configModel->config, true);

            // [SMART SPREADSHEET SCANNER] Buka file dan tentukan sheet terbaik & letak baris header
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = SalesInvoiceSpreadsheetScanner::findBestWorksheet($spreadsheet, $config);
            $sheetTitle = $sheet->getTitle();
            
            $headerRowIndex = SalesInvoiceSpreadsheetScanner::findHeaderRowIndex($sheet, $config);
            if ($headerRowIndex > 1) {
                $batch->addLog('info', "Mendeteksi judul/kop file: baris header data transaksi berada di baris ke-{$headerRowIndex} (Sheet: '{$sheetTitle}').");
            }

            $highestRow = (int)$sheet->getHighestDataRow();
            $totalRows = max(0, $highestRow - $headerRowIndex); // Kurangi baris header dan kop atas

            if ($totalRows <= 0) {
                throw new \Exception('File Excel tidak berisi baris data.');
            }

            $batch->update(['total_rows' => $totalRows]);
            $batch->addLog('info', "Berkas valid, ditemukan {$totalRows} baris data untuk diproses.");

            if ($this->targetMonth && $this->targetYear) {
                // [SMART PERIOD GUARD] Validasi kesesuaian periode sebelum transaksi database
                $periodCheck = SalesInvoicePeriodGuard::checkConsistency(
                    $sheet,
                    $config,
                    $this->targetMonth,
                    $this->targetYear
                );

                if (!$periodCheck['is_valid']) {
                    throw new \Exception($periodCheck['error_message']);
                }

                $batch->addLog('info', "Kesesuaian periode terverifikasi: {$periodCheck['target_period_name']} ({$periodCheck['match_percentage']}% data transaksi cocok).");
            }

            // DB Transaction dengan Bulk Insert
            DB::beginTransaction();
            try {
                $importer = new SalesInvoiceImport(
                    $config, 
                    $this->distributorCode, 
                    $batch, 
                    $this->targetMonth, 
                    $this->targetYear,
                    $headerRowIndex
                );
                
                Excel::import($importer, $fullPath);
                $importer->flushBuffer(); // Simpan sisa baris terakhir di buffer

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $finalCount = $batch->fresh()->processed_rows;

            // Jika ada baris yang dilewati (footer/summary/kosong), informasikan dengan warna kuning (warning)
            if ($importer->skippedCount > 0) {
                // Simpan data baris yang dilewati ke penyimpanan lokal untuk diunduh user dalam format Excel (.xlsx)
                if (!empty($importer->skippedRowsData)) {
                    $dir = 'skipped-imports';
                    if (!Storage::disk('local')->exists($dir)) {
                        Storage::disk('local')->makeDirectory($dir);
                    }
                    Storage::disk('local')->put("{$dir}/batch_{$this->batchId}.json", json_encode($importer->skippedRowsData, JSON_PRETTY_PRINT));
                }

                $batch->addLog('warning', "CATATAN: Ditemukan {$importer->skippedCount} baris yang dilewati (skip):");
                foreach ($importer->skippedRowsSummary as $skipDetail) {
                    $batch->addLog('warning', "  ↳ {$skipDetail}");
                }
                if ($importer->skippedCount > count($importer->skippedRowsSummary)) {
                    $rem = $importer->skippedCount - count($importer->skippedRowsSummary);
                    $batch->addLog('warning', "  ↳ ... dan {$rem} baris lainnya.");
                }
                $batch->addLog('warning', "PROSES SELESAI DENGAN CATATAN: Berhasil mengimpor {$finalCount} dari {$totalRows} baris ({$importer->skippedCount} baris dilewati).");
            } else {
                $batch->addLog('success', "PROSES SELESAI: Berhasil mengimpor seluruh {$finalCount} dari {$totalRows} baris data.");
            }

            $batch->updateStatus('completed');

        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $batch->addLog('error', "PROSES GAGAL: Terjadi Kesalahan Validasi Header.");
            foreach($errors as $error) {
                $batch->addLog('error', "- " . $error);
            }
            $batch->updateStatus('failed');

        } catch (Throwable $e) {
            $batch->addLog('error', "PROSES GAGAL: " . $e->getMessage());
            $batch->updateStatus('failed');
        } finally {
            Storage::delete($this->filePath);
        }
    }
}
