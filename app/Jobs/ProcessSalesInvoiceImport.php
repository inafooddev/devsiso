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
use Throwable;
use Illuminate\Validation\ValidationException;

class ProcessSalesInvoiceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $batchId;
    protected $distributorCode; 

    public function __construct(string $filePath, int $batchId, string $distributorCode) 
    {
        $this->filePath = $filePath;
        $this->batchId = $batchId;
        $this->distributorCode = $distributorCode;
    }

    public function handle()
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        $batch->updateStatus('processing');

        try {
            $fullPath = Storage::path($this->filePath);

            $batch->addLog('info', 'Mulai membaca file...');
            $allRows = Excel::toArray(new \stdClass(), $fullPath);
            $totalRows = isset($allRows[0]) ? count($allRows[0]) - 1 : 0;
            
            if ($totalRows <= 0) throw new \Exception('File Excel tidak berisi baris data.');
            
            $batch->update(['total_rows' => $totalRows]);
            $batch->addLog('info', "File valid, ditemukan {$totalRows} baris data untuk diproses.");

            // Mencari konfigurasi berdasarkan distributor_code
            $configModel = ConfigSalesInvoiceDistributor::where('distributor_code', $this->distributorCode)->first();
            if (!$configModel) throw new \Exception("Konfigurasi tidak ditemukan untuk kode distributor '{$this->distributorCode}'.");
            
            $batch->addLog('info', "Konfigurasi untuk '{$this->distributorCode}' berhasil dimuat.");
            $config = json_decode($configModel->config, true);

            // Teruskan objek $batch dan distributorCode ke importer
            $importer = new SalesInvoiceImport($config, $this->distributorCode, $batch);
            Excel::import($importer, $fullPath);

            $finalCount = $batch->fresh()->processed_rows;
            $batch->addLog('success', "PROSES SELESAI: Berhasil memproses {$finalCount} dari {$totalRows} baris data.");
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