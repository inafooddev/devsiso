<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

trait WithCallPlanFilters
{
    // Selected filters in the modal (live)
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedSupervisor = '';
    public $selectedDistributor = '';
    public $selectedBulan = '';

    // Applied filters for the table (deferred until 'Terapkan' is clicked)
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSupervisor = '';
    public $appliedDistributor = '';
    public $appliedBulan = '';

    public function mountWithCallPlanFilters()
    {
        $this->selectedBulan = date('Y-m');
        $this->appliedBulan = date('Y-m');
    }

    #[Computed]
    public function filterRegions()
    {
        return DB::table('master_regions')->select('region_code', 'region_name')->orderBy('region_name')->get();
    }

    #[Computed]
    public function filterAreas()
    {
        $areasQuery = DB::table('master_areas')->select('area_code', 'area_name');
        if ($this->selectedRegion) {
            $areasQuery->where('region_code', $this->selectedRegion);
        }
        return $areasQuery->orderBy('area_name')->get();
    }

    #[Computed]
    public function filterSupervisors()
    {
        $query = DB::table('master_supervisors')->select('supervisor_code', 'description');
        
        if ($this->selectedArea) {
            $query->where('area_code', $this->selectedArea);
        } elseif ($this->selectedRegion) {
            $areaCodes = DB::table('master_areas')
                ->where('region_code', $this->selectedRegion)
                ->pluck('area_code');
            $query->whereIn('area_code', $areaCodes);
        }
        
        return $query->orderBy('description')->get();
    }

    #[Computed]
    public function filterDistributors()
    {
        $distributorsQuery = DB::table('master_distributors')->select('distributor_code', 'distributor_name');
        if ($this->selectedRegion) $distributorsQuery->where('region_code', $this->selectedRegion);
        if ($this->selectedArea) $distributorsQuery->where('area_code', $this->selectedArea);
        if ($this->selectedSupervisor) $distributorsQuery->where('supervisor_code', $this->selectedSupervisor);
        
        return $distributorsQuery->orderBy('distributor_name')->get();
    }

    public function syncFiltersToSelected()
    {
        $this->selectedRegion = $this->appliedRegion;
        $this->selectedArea = $this->appliedArea;
        $this->selectedSupervisor = $this->appliedSupervisor;
        $this->selectedDistributor = $this->appliedDistributor;
        $this->selectedBulan = $this->appliedBulan;
    }

    public function updatedSelectedRegion()
    {
        $this->selectedArea = '';
        $this->selectedSupervisor = '';
        $this->selectedDistributor = '';
    }

    public function updatedSelectedArea()
    {
        $this->selectedSupervisor = '';
        $this->selectedDistributor = '';
    }

    public function updatedSelectedSupervisor()
    {
        $this->selectedDistributor = '';
    }

    public function applyFilters()
    {
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedSupervisor = $this->selectedSupervisor;
        $this->appliedDistributor = $this->selectedDistributor;
        $this->appliedBulan = $this->selectedBulan;
        
        // Asumsi komponen yang memakai trait ini menggunakan pagination
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset([
            'selectedRegion', 'selectedArea', 'selectedSupervisor', 'selectedDistributor',
            'appliedRegion', 'appliedArea', 'appliedSupervisor', 'appliedDistributor'
        ]);
        
        // Reset bulan ke default
        $this->selectedBulan = date('Y-m');
        $this->appliedBulan = date('Y-m');
        
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Get the currently applied filters as an array.
     */
    public function getAppliedFilters()
    {
        return [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'supervisor' => $this->appliedSupervisor,
            'distributor' => $this->appliedDistributor,
            'bulan' => $this->appliedBulan,
        ];
    }
}
