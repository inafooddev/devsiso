<?php

namespace App\Livewire\MasterDistributors;

use Livewire\Component;
use App\Models\MasterDistributor;
use App\Models\MasterBranch;

class Edit extends Component
{
    public $distributor_code, $distributor_name, $join_date, $resign_date, $latitude, $longitude;
    public $branch_code = '', $region_name, $area_name, $supervisor_name;
    public $is_active;

    // Properti baru untuk pencarian cabang
    public $branchSearch = '';
    public $selectedBranchName = '';

    public function mount($distributor_code)
    {
        $distributor = MasterDistributor::findOrFail($distributor_code);
        
        $this->distributor_code = $distributor->distributor_code;
        $this->distributor_name = $distributor->distributor_name;
        $this->join_date = $distributor->join_date ? $distributor->join_date->format('Y-m-d') : null;
        $this->resign_date = $distributor->resign_date ? $distributor->resign_date->format('Y-m-d') : null;
        $this->latitude = $distributor->latitude;
        $this->longitude = $distributor->longitude;
        $this->is_active = $distributor->is_active;

        $this->branch_code = $distributor->branch_code;
        $this->selectedBranchName = $distributor->branch_name; // Simpan nama cabang yang ada
        $this->region_name = $distributor->region_name;
        $this->area_name = $distributor->area_name;
        $this->supervisor_name = $distributor->supervisor_name;
    }

    /**
     * Hook yang dijalankan setiap kali properti branch_code diperbarui.
     */
    public function updatedBranchCode($value)
    {
        if ($value) {
            $branch = MasterBranch::with(['supervisor.area.region'])->find($value);
            if ($branch) {
                $this->region_name = $branch->supervisor->area->region->region_name ?? 'N/A';
                $this->area_name = $branch->supervisor->area->area_name ?? 'N/A';
                $this->supervisor_name = $branch->supervisor->supervisor_name ?? 'N/A';
            }
        } else {
            $this->reset(['region_name', 'area_name', 'supervisor_name']);
        }
    }

    /**
     * Memilih cabang dari hasil pencarian.
     */
    public function selectBranch($branchCode, $branchName)
    {
        $this->branch_code = $branchCode;
        $this->selectedBranchName = $branchName;
        $this->branchSearch = ''; // Kosongkan pencarian setelah memilih
        $this->updatedBranchCode($branchCode);
    }

    /**
     * Memperbarui data distributor.
     */
    public function update()
    {
        $this->validate([
            'distributor_name' => 'required|string|max:100',
            'branch_code' => 'required|exists:master_branches,branch_code',
            'join_date' => 'nullable|date',
            'resign_date' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $distributor = MasterDistributor::find($this->distributor_code);
        $branch = MasterBranch::with(['supervisor.area.region'])->find($this->branch_code);

        $distributor->update([
            'distributor_name' => $this->distributor_name,
            'join_date' => $this->join_date,
            'resign_date' => $this->resign_date,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => $this->is_active,
            'branch_code' => $this->branch_code,
            'branch_name' => $branch->branch_name ?? 'N/A',
            'supervisor_code' => $branch->supervisor->supervisor_code ?? 'N/A',
            'supervisor_name' => $branch->supervisor->supervisor_name ?? 'N/A',
            'area_code' => $branch->supervisor->area->area_code ?? 'N/A',
            'area_name' => $branch->supervisor->area->area_name ?? 'N/A',
            'region_code' => $branch->supervisor->area->region->region_code ?? 'N/A',
            'region_name' => $branch->supervisor->area->region->region_name ?? 'N/A',
        ]);

        session()->flash('message', 'Data distributor berhasil diperbarui.');
        return redirect()->route('master-distributors.index');
    }

    public function render()
    {
        $branches = collect();
        // Hanya cari jika ada input dan belum ada cabang yang dipilih
        if (strlen($this->branchSearch) >= 2 && empty($this->branch_code)) {
            $branches = MasterBranch::where('branch_name', 'ilike', '%' . $this->branchSearch . '%')
                ->orWhere('branch_code', 'ilike', '%' . $this->branchSearch . '%')
                ->take(10)
                ->get();
        }

        return view('livewire.master-distributors.edit', [
            'branches' => $branches
        ])->layout('layouts.app');
    }
}

