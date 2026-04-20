<?php

namespace App\Livewire\MasterSupervisors;

use Livewire\Component;
use App\Models\MasterSupervisor;
use App\Models\MasterArea;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $supervisorId;
    public $supervisor_code;
    public $supervisor_name;
    public $description;
    public $area_code;
    public $areas = [];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'supervisor_code' => [
                'required',
                'string',
                'max:15',
                Rule::unique('master_supervisors')->ignore($this->supervisorId, 'supervisor_code'),
            ],
            'supervisor_name' => 'required|string|max:50',
            'description'     => 'nullable|string|max:100',
            'area_code'       => 'required|exists:master_areas,area_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'area_code.required' => 'Silakan pilih salah satu area.',
        ];
    }

    /**
     * Inisialisasi komponen.
     */
    public function mount($encodedSupervisorCode)
    {
        $supervisorCode = base64_decode($encodedSupervisorCode);
        $supervisor = MasterSupervisor::findOrFail($supervisorCode);

        $this->supervisorId    = $supervisor->supervisor_code;
        $this->supervisor_code = $supervisor->supervisor_code;
        $this->supervisor_name = $supervisor->supervisor_name;
        $this->description     = $supervisor->description;
        $this->area_code       = $supervisor->area_code;
        
        $this->areas = MasterArea::orderBy('area_name')->get();
    }
    
    /**
     * Validasi real-time.
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Memperbarui data supervisor.
     */
    public function update()
    {
        $validatedData = $this->validate();

        $supervisor = MasterSupervisor::find($this->supervisorId);
        $supervisor->update($validatedData);

        session()->flash('message', 'Data supervisor berhasil diperbarui.');

        return redirect()->route('master-supervisors.index');
    }

    public function render()
    {
        return view('livewire.master-supervisors.edit')->layout('layouts.app');
    }
}
