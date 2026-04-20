<?php

namespace App\Livewire\MasterRegions;

use Livewire\Component;
use App\Models\MasterRegion;

class Create extends Component
{
    public $region_code;
    public $region_name;

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'region_code' => 'required|string|max:15|unique:master_regions,region_code',
            'region_name' => 'required|string|max:50',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'region_code.required' => 'Kode Region wajib diisi.',
            'region_code.unique'   => 'Kode Region ini sudah digunakan.',
            'region_name.required' => 'Nama Region wajib diisi.',
        ];
    }

    /**
     * Memvalidasi properti secara real-time.
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Menyimpan data region baru.
     */
    public function save()
    {
        $this->validate();

        MasterRegion::create([
            'region_code' => $this->region_code,
            'region_name' => $this->region_name,
        ]);

        session()->flash('message', 'Region baru berhasil ditambahkan.');

        return redirect()->route('master-regions.index');
    }

    /**
     * Merender tampilan form tambah region.
     */
    public function render()
    {
        return view('livewire.master-regions.create')->layout('layouts.app');
    }
}
