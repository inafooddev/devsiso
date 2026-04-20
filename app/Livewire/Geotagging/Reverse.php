<?php

namespace App\Livewire\Geotagging;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\GeotagJob;
use Illuminate\Support\Str;

class Reverse extends Component
{
    use WithFileUploads;

    public $file;
    public $jobs = [];

    public function mount()
    {
        $this->loadJobs();
    }

    public function loadJobs()
    {
        // 1. Ambil tugas terakhir untuk cek status sinkronisasi file fisik
        $jobs_check = GeotagJob::latest()->take(5)->get();
        
        foreach ($jobs_check as $job) {
            if ($job->status === 'pending' || $job->status === 'processing') {
                
                // Mengambil nama file tanpa ekstensi (karena output dari Python SELALU .xlsx)
                $nameOnly = pathinfo($job->system_filename, PATHINFO_FILENAME);
                
                $outputPath = 'geotag_jobs/output/result_' . $nameOnly . '.xlsx';
                $processingPath = 'geotag_jobs/input/' . $job->system_filename . '.processing';
                
                // Cek apakah file hasil (output) sudah dibuat oleh Python
                if (Storage::disk('local')->exists($outputPath)) {
                    $job->update(['status' => 'completed']);
                } 
                // Jika belum selesai, cek apakah sedang diproses (.processing)
                elseif (Storage::disk('local')->exists($processingPath)) {
                    // Update status database jadi processing jika tadinya pending
                    if ($job->status === 'pending') {
                        $job->update(['status' => 'processing']);
                    }
                }
            }
        }
        
        // 2. Reload ulang agar mendapatkan status terbaru yang fix untuk ditampilkan
        $this->jobs = GeotagJob::latest()->take(5)->get();

        // 3. BACA PROGRESS DINAMIS (Setelah data dari DB di-load agar tidak tertimpa)
        foreach ($this->jobs as $job) {
            if ($job->status === 'processing') {
                $progressPath = 'geotag_jobs/input/' . $job->system_filename . '.progress';
                
                if (Storage::disk('local')->exists($progressPath)) {
                    $progressJson = Storage::disk('local')->get($progressPath);
                    // Simpan sementara di property model untuk dikirim ke UI Blade
                    $job->progress_data = json_decode($progressJson, true); 
                }
            }
        }
    }

    public function processFile()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:51200' // Maks 50MB
        ]);

        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->getClientOriginalExtension();
        
        // Beri nama unik agar tidak bentrok
        $systemFilename = time() . '_' . Str::random(5) . '.' . $extension;

        // Simpan file ke folder private (sesuai settingan server Anda)
        $this->file->storeAs('geotag_jobs/input', $systemFilename, 'local');

        // Catat di database
        GeotagJob::create([
            'original_filename' => $originalName,
            'system_filename' => $systemFilename,
            'status' => 'pending'
        ]);

        $this->reset('file');
        session()->flash('message', 'File berhasil diunggah dan masuk antrean proses background.');
        $this->loadJobs();
    }

    public function downloadResult($systemFilename)
    {
        // Python kita di-setting untuk selalu menghasilkan output .xlsx
        $nameOnly = pathinfo($systemFilename, PATHINFO_FILENAME);
        $filePath = 'geotag_jobs/output/result_' . $nameOnly . '.xlsx';
        
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath);
        }
        
        session()->flash('error', 'File hasil belum tersedia atau terjadi kesalahan.');
    }


        /**
     * Mengunduh file template Excel dari folder public/templates
     */
    public function downloadTemplate()
    {
        // Mengarahkan ke file di dalam public/templates/Format GeoReverse.xlsx
        $filePath = public_path('templates/Format GeoReverse.xlsx');
        
        if (file_exists($filePath)) {
            return response()->download($filePath);
        }
        
        session()->flash('error', 'File template Format GeoReverse.xlsx tidak ditemukan di folder public/templates.');
    }

    public function render()
    {
        return view('livewire.geotagging.reverse')->layout('layouts.app');
    }
}