<?php

namespace App\Livewire\MasterAreas;

use Livewire\Component;
use App\Models\MasterArea;
use App\Models\MasterRegion;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $areaId;
    public $area_code;
    public $area_name;
    public $region_code;
    public $regions = [];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'area_code' => [
                'required',
                'string',
                'max:15',
                Rule::unique('master_areas')->ignore($this->areaId, 'area_code'),
            ],
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
    public function mount($encodedAreaCode)
    {
        $areaCode = base64_decode($encodedAreaCode);
        $area = MasterArea::findOrFail($areaCode);

        $this->areaId      = $area->area_code;
        $this->area_code   = $area->area_code;
        $this->area_name   = $area->area_name;
        $this->region_code = $area->region_code;
        
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
     * Memperbarui data area.
     */
    public function update()
    {
        $validatedData = $this->validate();

        $area = MasterArea::find($this->areaId);
        $area->update($validatedData);

        session()->flash('message', 'Data area berhasil diperbarui.');

        return redirect()->route('master-areas.index');
    }

    public function render()
    {
        return view('livewire.master-areas.edit')->layout('layouts.app');
    }
}
