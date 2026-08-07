<?php

namespace App\Livewire\Others\Qceskalink;

use App\Models\ExtractorConfig;
use App\Models\ExtractorTempResult;
use Illuminate\Support\Str;
use Livewire\Component;

class ExtractorProcess extends Component
{
    public $configs = [];
    public $distMapping = [];

    public function mount()
    {
        $this->configs = ExtractorConfig::all()->toArray();
        
        // Mengambil mapping kode distributor berdasarkan nama branch / keyword
        $this->distMapping = \DB::table('master_distributors as ms')
            ->leftJoin('distributor_implementasi_eskalink as die', 'ms.distributor_code', '=', 'die.distributor_code')
            ->where('ms.is_active', true)
            ->pluck('die.eskalink_code', 'ms.branch_name')
            ->toArray();
    }

    public function saveResults($batchId, $mode, $results, $tanggal = null)
    {
        if (empty($results)) {
            return;
        }

        $tanggal = $tanggal ?: date('Y-m-01');

        $insertsTemp = [];
        $insertsPermanent = [];
        $distCodes = [];
        $now = now();
        
        foreach ($results as $res) {
            $distCode = $res['kode_dist'] ?? null;
            if ($distCode && !in_array($distCode, $distCodes)) {
                $distCodes[] = $distCode;
            }

            // Temp Insert (History)
            $insertsTemp[] = [
                'batch_id' => $batchId,
                'nama_file' => $res['nama_file'] ?? '',
                'kode_dist' => $distCode,
                'group_name' => $res['group_name'] ?? '',
                'nominal_surat' => $res['nominal_surat'] ?? 0,
                'mode' => $mode,
                'extracted_data' => json_encode($res['extracted_data'] ?? []),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            // Permanent Insert Mapping
            $extracted = $res['extracted_data'] ?? [];
            
            // Mapping label dari frontend ke kolom database (Case-Insensitive)
            $qty = 0; $disc4 = 0; $disc8 = 0; $neto = 0;
            foreach ($extracted as $key => $val) {
                $k = strtoupper(trim($key));
                $v = is_numeric($val) ? (float)$val : 0;
                
                if (str_contains($k, 'QTY') || $k === 'KUANTITAS') {
                    $qty = $v;
                } elseif (str_contains($k, 'DISC 4') || str_contains($k, 'DISCOUNT 4')) {
                    $disc4 = $v;
                } elseif (str_contains($k, 'DISC 8') || str_contains($k, 'DISCOUNT 8')) {
                    $disc8 = $v;
                } elseif (str_contains($k, 'NETT') || str_contains($k, 'NETO')) {
                    $neto = $v;
                }
            }

            if ($distCode) { // Hanya simpan yang punya distributor code valid
                $insertsPermanent[] = [
                    'tanggal' => $tanggal,
                    'distributor_code' => $distCode,
                    'qty' => $qty,
                    'discount_4' => $disc4,
                    'discount_8' => $disc8,
                    'neto' => $neto,
                    'nominal_surat' => $res['nominal_surat'] ?? 0,
                    'file_surat' => $res['nama_file'] ?? '',
                    'timestamp' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Chunking the inserts Temp
        foreach (array_chunk($insertsTemp, 500) as $chunk) {
            ExtractorTempResult::insert($chunk);
        }

        // Logic "Upsert": Hapus data lama di bulan dan dist code yang sama, lalu Insert baru
        if (!empty($distCodes)) {
            \DB::table('nominal_qc_dist')
                ->where('tanggal', $tanggal)
                ->whereIn('distributor_code', $distCodes)
                ->delete();
                
            foreach (array_chunk($insertsPermanent, 500) as $chunk) {
                \DB::table('nominal_qc_dist')->insert($chunk);
            }
        }

        // Beritahu frontend
        $this->dispatch('results-saved', batchId: $batchId, mode: $mode);
    }

    public function render()
    {
        return view('livewire.others.qceskalink.extractor-process')
            ->layout('layouts.app');
    }
}
