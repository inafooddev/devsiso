<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FilterPencapaianrwo extends Component
{
    // Filter properties
    public $kuartal = '';
    public $region = '';
    public $area = '';
    public $supervisor = '';
    public $distributor = '';

    // Status Filters
    public $statusProgress = 'Semua';
    public $statusSkb = 'Semua';
    public $statusData = 'Semua';
    public $statusReward = 'Semua';

    // Dropdown options
    public $kuartals = [];
    public $regions = [];
    public $areas = [];
    public $supervisors = [];
    public $distributors = [];

    public function mount($appliedKuartal, $appliedRegion, $appliedArea, $appliedSupervisor, $appliedDistributor, $appliedStatusProgress, $appliedStatusSkb, $appliedStatusData, $appliedStatusReward)
    {
        // Load initial states from parent component
        $this->kuartal = $appliedKuartal;
        $this->region = $appliedRegion;
        $this->area = $appliedArea;
        $this->supervisor = $appliedSupervisor;
        $this->distributor = $appliedDistributor;
        
        $this->statusProgress = $appliedStatusProgress;
        $this->statusSkb = $appliedStatusSkb;
        $this->statusData = $appliedStatusData;
        $this->statusReward = $appliedStatusReward;

        $user = auth()->user();
        
        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $this->regions = $regionQuery->get();

        // Restore cascading dependent options if there are pre-selected values
        if ($this->region) {
            $this->areas = DB::table('master_areas')->where('region_code', $this->region)->orderBy('area_name')->get();
        }
        if ($this->area) {
            $this->supervisors = DB::table('master_distributors')
                ->where('area_code', $this->area)
                ->select('supervisor_code', 'supervisor_name')
                ->whereNotNull('supervisor_code')
                ->where('supervisor_code', '!=', '')
                ->distinct()
                ->orderBy('supervisor_name')
                ->get();
        }
        if ($this->supervisor) {
            $this->distributors = DB::table('master_distributors')
                ->where('supervisor_code', $this->supervisor)
                ->when($this->area, fn($q) => $q->where('area_code', $this->area))
                ->select('distributor_code', 'distributor_name')
                ->orderBy('distributor_name')
                ->get();
        }
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->areas = empty($value) ? [] : DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name')
            ->get();
            
        $this->supervisors = [];
        $this->distributors = [];
    }

    public function updatedArea($value)
    {
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->supervisors = empty($value) ? [] : DB::table('master_distributors')
            ->where('area_code', $value)
            ->select('supervisor_code', 'supervisor_name')
            ->whereNotNull('supervisor_code')
            ->where('supervisor_code', '!=', '')
            ->distinct()
            ->orderBy('supervisor_name')
            ->get();
            
        $this->distributors = [];
    }

    public function updatedSupervisor($value)
    {
        $this->distributor = '';
        
        $this->distributors = empty($value) ? [] : DB::table('master_distributors')
            ->where('supervisor_code', $value)
            ->when($this->area, function ($q) {
                return $q->where('area_code', $this->area);
            })
            ->select('distributor_code', 'distributor_name')
            ->orderBy('distributor_name')
            ->get();
    }

    public function applyFilter()
    {
        $this->dispatch('apply-rwo-filter', filters: [
            'kuartal' => $this->kuartal,
            'region' => $this->region,
            'area' => $this->area,
            'supervisor' => $this->supervisor,
            'distributor' => $this->distributor,
            'statusProgress' => $this->statusProgress,
            'statusSkb' => $this->statusSkb,
            'statusData' => $this->statusData,
            'statusReward' => $this->statusReward,
        ]);
        
        $this->dispatch('close-filter-modal');
    }

    public function resetFilter()
    {
        $currentMonth = (int) date('n');
        $currentQuarter = (string) ceil($currentMonth / 3);

        $this->kuartal = $currentQuarter;
        $this->region = '';
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->statusProgress = 'Semua';
        $this->statusSkb = 'Semua';
        $this->statusData = 'Semua';
        $this->statusReward = 'Semua';

        $this->areas = [];
        $this->supervisors = [];
        $this->distributors = [];

        $this->applyFilter();
    }

    public function render()
    {
        return view('livewire.rwo.filter-pencapaianrwo');
    }
}
