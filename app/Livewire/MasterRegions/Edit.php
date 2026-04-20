<?php

namespace App\Livewire\MasterRegions;

use Livewire\Component;
use App\Models\MasterRegion;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class Edit extends Component
{
    public $regionId;
    public $region_code;
    public $region_name;

    /**
     * Menginisialisasi komponen dengan data region yang akan diedit dari ID terenkripsi.
     */
    public function mount($hashedRegionId)
    {
        try {
            // Dekripsi ID dari URL
            $regionCode = base64_decode($hashedRegionId);
            $region = MasterRegion::findOrFail($regionCode);

            $this->regionId = $region->region_code;
            $this->region_code = $region->region_code;
            $this->region_name = $region->region_name;
        } catch (\Exception $e) {
            // Jika ID tidak valid atau tidak ditemukan, kembalikan ke halaman index
            session()->flash('error', 'Region tidak valid atau tidak ditemukan.');
            return redirect()->route('master-regions.index');
        }
    }

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'region_code' => [
                'required',
                'string',
                'max:15',
                Rule::unique('master_regions')->ignore($this->regionId, 'region_code'),
            ],
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
     * Memperbarui data region.
     */
    public function update()
    {
        $this->validate();

        $region = MasterRegion::find($this->regionId);
        $region->update([
            'region_code' => $this->region_code,
            'region_name' => $this->region_name,
        ]);

        session()->flash('message', 'Region berhasil diperbarui.');

        return redirect()->route('master-regions.index');
    }

    /**
     * Merender tampilan form edit region.
     */
    public function render()
    {
        return view('livewire.master-regions.edit')->layout('layouts.app');
    }
}

