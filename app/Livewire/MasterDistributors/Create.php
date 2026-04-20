<?php

namespace App\Livewire\MasterDistributors;

use Livewire\Component;
use App\Models\MasterDistributor;
use App\Models\MasterBranch;

class Create extends Component
{
    public $distributor_code, $distributor_name, $join_date, $resign_date, $latitude, $longitude;
    public $branch_code = '', $region_name, $area_name, $supervisor_name;
    public $is_active = true;
    
    // Properti baru untuk pencarian cabang
    public $branchSearch = '';
    public $selectedBranchName = '';

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
     * Menyimpan data distributor baru.
     */
    public function save()
    {
        $this->validate([
            'distributor_code' => 'required|string|max:15|unique:master_distributors,distributor_code',
            'distributor_name' => 'required|string|max:100',
            'branch_code' => 'required|exists:master_branches,branch_code',
            'join_date' => 'nullable|date',
            'resign_date' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $branch = MasterBranch::with(['supervisor.area.region'])->find($this->branch_code);

        MasterDistributor::create([
            'distributor_code' => $this->distributor_code,
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

        session()->flash('message', 'Distributor baru berhasil ditambahkan.');
        return redirect()->route('master-distributors.index');
    }

    public function render()
    {
        $branches = collect();
        if (strlen($this->branchSearch) >= 2) {
            $branches = MasterBranch::where('branch_name', 'ilike', '%' . $this->branchSearch . '%')
                ->orWhere('branch_code', 'ilike', '%' . $this->branchSearch . '%')
                ->take(10)
                ->get();
        }

        return view('livewire.master-distributors.create', [
            'branches' => $branches
        ])->layout('layouts.app');
    }
}

