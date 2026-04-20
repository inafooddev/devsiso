<?php

namespace App\Livewire\PlanCallTeamElite;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\PlanCallTeamEliteImport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class Import extends Component
{
    use WithFileUploads;

    public $excel_file;

    /**
     * Fungsi untuk mengeksekusi proses impor.
     */
    public function importData()
    {
        $this->validate([
            'excel_file' => 'required|mimes:xls,xlsx,csv|max:10240', // Maks 10MB
        ]);

        try {
            // Menjalankan proses impor menggunakan file yang diunggah sementara
            Excel::import(new PlanCallTeamEliteImport, $this->excel_file->getRealPath());
            
            session()->flash('message', 'Data Plan Call Team Elite berhasil diimpor!');
            $this->reset('excel_file');
            
        } catch (Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.plan-call-team-elite.import')->layout('layouts.app');
    }
}