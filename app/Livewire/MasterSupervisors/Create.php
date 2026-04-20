<?php

namespace App\Livewire\MasterSupervisors;

use Livewire\Component;
use App\Models\MasterSupervisor;
use App\Models\MasterArea;

class Create extends Component
{
    public $supervisor_code;
    public $supervisor_name;
    public $description;
    public $area_code = ''; // Inisialisasi agar placeholder terpilih
    public $areas = [];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'supervisor_code' => 'required|string|max:15|unique:master_supervisors,supervisor_code',
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
    public function mount()
    {
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
     * Menyimpan data supervisor baru.
     */
    public function save()
    {
        $validatedData = $this->validate();

        MasterSupervisor::create($validatedData);

        session()->flash('message', 'Supervisor baru berhasil ditambahkan.');

        return redirect()->route('master-supervisors.index');
    }

    public function render()
    {
        return view('livewire.master-supervisors.create')->layout('layouts.app');
    }
}
