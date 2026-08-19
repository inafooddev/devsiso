<?php

namespace App\Livewire\Others\Qceskalink;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\MasterDistributor;
use App\Models\OcrDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OcrScanner extends Component
{
    use WithFileUploads;

    public $uploadedFiles = [];

    public function saveOcrResults($data, $tanggal, $saveNominal = true, $saveFile = true)
    {
        \Log::info('OcrScanner: saveOcrResults called.', [
            'count_data' => count($data),
            'count_uploadedFiles' => count($this->uploadedFiles),
        ]);
        
        DB::transaction(function () use ($data, $tanggal, $saveNominal, $saveFile) {
            foreach ($data as $index => $item) {
                $fileName = $item['file_name'];
                $path = null;
                
                // If there's an uploaded file for this index and user wants to save it
                if ($saveFile && isset($this->uploadedFiles[$index])) {
                    $file = $this->uploadedFiles[$index];
                    // Store the file in 'surat_qc' folder using the 'public' disk
                    // This ensures it goes to storage/app/public/surat_qc
                    $path = $file->storeAs('surat_qc', $file->getClientOriginalName(), 'public');
                    $fileName = $file->getClientOriginalName();
                }

                $nominal = $saveNominal ? $item['nominal_extracted'] : 0;

                OcrDocument::updateOrCreate(
                    [
                        'file_name' => $fileName,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'distributor_code' => $item['distributor_code'],
                        'raw_text' => $item['raw_text'],
                        'nominal_extracted' => $nominal,
                        'status' => 'verified',
                    ]
                );

                // Sinkronisasi ke tabel utama QC (NominalQcDist)
                if ($item['distributor_code']) {
                    $qcRecord = \App\Models\NominalQcDist::firstOrNew([
                        'tanggal' => $tanggal,
                        'distributor_code' => $item['distributor_code']
                    ]);
                    
                    if (!$qcRecord->exists) {
                        $qcRecord->qty = 0;
                        $qcRecord->discount_4 = 0;
                        $qcRecord->discount_8 = 0;
                        $qcRecord->neto = 0;
                    }
                    
                    if ($saveNominal) {
                        $qcRecord->nominal_surat = $nominal;
                    }
                    if ($saveFile && $path) {
                        $qcRecord->file_surat = $path;
                    }
                    
                    $qcRecord->save();
                }
            }
        });

        // Clear uploaded files after saving
        $this->uploadedFiles = [];

        return true;
    }

    public function render()
    {
        // Ambil mapping keyword (branch_name) -> eskalink_code untuk mendeteksi distributor_code otomatis
        $distMapping = DB::table('master_distributors as ms')
            ->leftJoin('distributor_implementasi_eskalink as die', 'ms.distributor_code', '=', 'die.distributor_code')
            ->where('ms.is_active', true)
            ->whereNotNull('die.eskalink_code')
            ->pluck('die.eskalink_code', 'ms.branch_name')
            ->toArray();

        return view('livewire.others.qceskalink.ocr-scanner', [
            'distMapping' => $distMapping,
        ])->layout('layouts.app');
    }
}
