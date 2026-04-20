<?php

namespace App\Livewire\MasterBranches;

use Livewire\Component;
use App\Models\MasterBranch;
use App\Models\MasterSupervisor;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use Livewire\Attributes\Computed;

class Create extends Component
{
    public $branch_code;
    public $branch_name;
    public $supervisor_code = '';

    // Properti untuk dropdown berjenjang
    public $selectedRegion = '';
    public $selectedArea = '';

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'branch_code'     => 'required|string|max:15|unique:master_branches,branch_code',
            'branch_name'     => 'required|string|max:50',
            'selectedRegion'  => 'required',
            'selectedArea'    => 'required',
            'supervisor_code' => 'required|exists:master_supervisors,supervisor_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'supervisor_code.required' => 'Silakan pilih salah satu supervisor.',
            'selectedRegion.required'  => 'Silakan pilih region terlebih dahulu.',
            'selectedArea.required'    => 'Silakan pilih area terlebih dahulu.',
        ];
    }
    
    /**
     * Validasi real-time.
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Dipanggil saat properti selectedRegion diubah.
     */
    public function updatedSelectedRegion($region_code)
    {
        // Reset pilihan area dan supervisor
        $this->selectedArea = '';
        $this->supervisor_code = '';
    }

    /**
     * Dipanggil saat properti selectedArea diubah.
     */
    public function updatedSelectedArea($area_code)
    {
        // Reset pilihan supervisor
        $this->supervisor_code = '';
    }

    /**
     * Mengambil daftar regions dari database.
     */
    #[Computed]
    public function regions()
    {
        return MasterRegion::orderBy('region_name')->get();
    }

    /**
     * Mengambil daftar areas berdasarkan region yang dipilih.
     */
    #[Computed]
    public function areas()
    {
        if (!$this->selectedRegion) {
            return collect();
        }
        return MasterArea::where('region_code', $this->selectedRegion)->orderBy('area_name')->get();
    }

    /**
     * Mengambil daftar supervisors berdasarkan area yang dipilih.
     */
    #[Computed]
    public function supervisors()
    {
        if (!$this->selectedArea) {
            return collect();
        }
        return MasterSupervisor::where('area_code', $this->selectedArea)->orderBy('supervisor_name')->get();
    }

    /**
     * Menyimpan data cabang baru.
     */
    public function save()
    {
        $validatedData = $this->validate();

        MasterBranch::create([
            'branch_code' => $this->branch_code,
            'branch_name' => $this->branch_name,
            'supervisor_code' => $this->supervisor_code,
        ]);

        session()->flash('message', 'Cabang baru berhasil ditambahkan.');

        return redirect()->route('master-branches.index');
    }

    public function render()
    {
        return view('livewire.master-branches.create')->layout('layouts.app');
    }
}

