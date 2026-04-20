<?php

namespace App\Livewire\MasterAreas;

use Livewire\Component;
use App\Models\MasterArea;
use App\Models\MasterRegion;

class Create extends Component
{
    public $area_code;
    public $area_name;
    public $region_code = ''; // Inisialisasi agar placeholder terpilih
    public $regions = [];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'area_code'   => 'required|string|max:15|unique:master_areas,area_code',
            'area_name'   => 'required|string|max:50',
            'region_code' => 'required|exists:master_regions,region_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'region_code.required' => 'Silakan pilih salah satu region.',
        ];
    }

    /**
     * Inisialisasi komponen.
     */
    public function mount()
    {
        $this->regions = MasterRegion::orderBy('region_name')->get();
    }
    
    /**
     * Validasi real-time.
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Menyimpan data area baru.
     */
    public function save()
    {
        $validatedData = $this->validate();

        MasterArea::create($validatedData);

        session()->flash('message', 'Area baru berhasil ditambahkan.');

        return redirect()->route('master-areas.index');
    }

    public function render()
    {
        return view('livewire.master-areas.create')->layout('layouts.app');
    }
}
