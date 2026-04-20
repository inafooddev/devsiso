<?php

namespace App\Livewire\MasterBranches;

use Livewire\Component;
use App\Models\MasterBranch;
use App\Models\MasterSupervisor;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

class Edit extends Component
{
    public $branchId;
    public $branch_code;
    public $branch_name;
    public $supervisor_code;

    // Properti untuk dropdown berjenjang
    public $selectedRegion;
    public $selectedArea;

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'branch_code' => [
                'required',
                'string',
                'max:15',
                Rule::unique('master_branches')->ignore($this->branchId, 'branch_code'),
            ],
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
     * Inisialisasi komponen.
     */
    public function mount($encodedBranchCode)
    {
        $branchCode = base64_decode($encodedBranchCode);
        $branch = MasterBranch::with(['supervisor.area'])->findOrFail($branchCode);

        $this->branchId        = $branch->branch_code;
        $this->branch_code     = $branch->branch_code;
        $this->branch_name     = $branch->branch_name;
        $this->supervisor_code = $branch->supervisor_code;

        // Inisialisasi dropdown berjenjang
        if ($branch->supervisor) {
            $this->selectedArea = $branch->supervisor->area_code;
            if ($branch->supervisor->area) {
                $this->selectedRegion = $branch->supervisor->area->region_code;
            }
        }
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
        $this->selectedArea = '';
        $this->supervisor_code = '';
    }

    /**
     * Dipanggil saat properti selectedArea diubah.
     */
    public function updatedSelectedArea($area_code)
    {
        $this->supervisor_code = '';
    }

    #[Computed]
    public function regions()
    {
        return MasterRegion::orderBy('region_name')->get();
    }

    #[Computed]
    public function areas()
    {
        if (!$this->selectedRegion) {
            return collect();
        }
        return MasterArea::where('region_code', $this->selectedRegion)->orderBy('area_name')->get();
    }

    #[Computed]
    public function supervisors()
    {
        if (!$this->selectedArea) {
            return collect();
        }
        return MasterSupervisor::where('area_code', $this->selectedArea)->orderBy('supervisor_name')->get();
    }

    /**
     * Memperbarui data cabang.
     */
    public function update()
    {
        $validatedData = $this->validate();

        $branch = MasterBranch::find($this->branchId);
        $branch->update([
            'branch_code' => $this->branch_code,
            'branch_name' => $this->branch_name,
            'supervisor_code' => $this->supervisor_code,
        ]);

        session()->flash('message', 'Data cabang berhasil diperbarui.');

        return redirect()->route('master-branches.index');
    }

    public function render()
    {
        return view('livewire.master-branches.edit')->layout('layouts.app');
    }
}

