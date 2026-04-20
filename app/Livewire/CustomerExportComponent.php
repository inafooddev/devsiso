<?php

namespace App\Livewire;

use Livewire\Component;
use App\Exports\CustomerDataExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerExportComponent extends Component
{
    public $showModal = false;
    
    // Form Inputs
    public $selectedMonth; // Format: YYYY-MM
    public $selectedRegions = []; // Array of region codes

    // Opsi Region (Bisa diambil dari DB Master Region jika ada)
    public $availableRegions = [
        'INAJWA1', 'INAJWA2', 'INAPUL1', 'INASUM1', 'INASUM2'
    ];

    public function mount()
    {
        // Default ke bulan saat ini
        $this->selectedMonth = date('Y-m');
        // Default select all regions
        $this->selectedRegions = $this->availableRegions;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function export()
    {
        // Validasi sederhana
        $this->validate([
            'selectedMonth' => 'required',
            'selectedRegions' => 'required|array|min:1'
        ]);

        $filename = 'customer_data_' . $this->selectedMonth . '.xlsx';

        // Trigger download Excel
        return Excel::download(
            new CustomerDataExport($this->selectedMonth, $this->selectedRegions), 
            $filename
        );
    }

    public function render()
    {
        // PERBAIKAN DI SINI:
        // Menambahkan ->layout('layouts.app') untuk memaksa Livewire menggunakan
        // layout utama Laravel Anda, bukan mencari 'components.layouts.app'.
        return view('livewire.customer-export-component')->layout('layouts.app');
    }
}