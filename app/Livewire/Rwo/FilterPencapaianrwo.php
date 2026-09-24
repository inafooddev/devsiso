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

    protected function getBaseQuery()
    {
        $query = DB::table('master_distributors');
        $user = auth()->user();
        
        if (!$user) {
            $query->whereRaw('1 = 0');
            return $query;
        }
        
        if ($user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $query->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'master_distributors.supervisor_code')
                  ->where(function($q) use ($user) {
                      $q->where('te.team_elite_code', $user->supervisor_code)
                        ->orWhere('master_distributors.supervisor_code', $user->supervisor_code);
                  });
        } elseif (!empty($user->area_code)) {
            $query->where('master_distributors.area_code', $user->area_code);
        } elseif (!empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('master_distributors.region_code', $regions);
            }
        }
        
        return $query;
    }

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

        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $this->regions = $this->getBaseQuery()
            ->select('master_distributors.region_code as region_code', 'master_distributors.region_name as region_name')
            ->whereNotNull('master_distributors.region_code')
            ->distinct()
            ->orderBy('master_distributors.region_name')
            ->get();

        // Restore cascading dependent options if there are pre-selected values
        if ($this->region) {
            $this->areas = $this->getBaseQuery()
                ->where('master_distributors.region_code', $this->region)
                ->select('master_distributors.area_code as area_code', 'master_distributors.area_name as area_name')
                ->whereNotNull('master_distributors.area_code')
                ->distinct()
                ->orderBy('master_distributors.area_name')
                ->get();
        }
        if ($this->area) {
            $this->supervisors = $this->getBaseQuery()
                ->where('master_distributors.area_code', $this->area)
                ->select('master_distributors.supervisor_code as supervisor_code', 'master_distributors.supervisor_name as supervisor_name')
                ->whereNotNull('master_distributors.supervisor_code')
                ->where('master_distributors.supervisor_code', '!=', '')
                ->distinct()
                ->orderBy('master_distributors.supervisor_name')
                ->get();
        }
        if ($this->supervisor) {
            $this->distributors = $this->getBaseQuery()
                ->where('master_distributors.supervisor_code', $this->supervisor)
                ->when($this->area, fn($q) => $q->where('master_distributors.area_code', $this->area))
                ->select('master_distributors.distributor_code as distributor_code', 'master_distributors.distributor_name as distributor_name')
                ->distinct()
                ->orderBy('master_distributors.distributor_name')
                ->get();
        }
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->areas = empty($value) ? [] : $this->getBaseQuery()
            ->where('master_distributors.region_code', $value)
            ->select('master_distributors.area_code as area_code', 'master_distributors.area_name as area_name')
            ->whereNotNull('master_distributors.area_code')
            ->distinct()
            ->orderBy('master_distributors.area_name')
            ->get();
            
        $this->supervisors = [];
        $this->distributors = [];
    }

    public function updatedArea($value)
    {
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->supervisors = empty($value) ? [] : $this->getBaseQuery()
            ->where('master_distributors.area_code', $value)
            ->select('master_distributors.supervisor_code as supervisor_code', 'master_distributors.supervisor_name as supervisor_name')
            ->whereNotNull('master_distributors.supervisor_code')
            ->where('master_distributors.supervisor_code', '!=', '')
            ->distinct()
            ->orderBy('master_distributors.supervisor_name')
            ->get();
            
        $this->distributors = [];
    }

    public function updatedSupervisor($value)
    {
        $this->distributor = '';
        
        $this->distributors = empty($value) ? [] : $this->getBaseQuery()
            ->where('master_distributors.supervisor_code', $value)
            ->when($this->area, function ($q) {
                return $q->where('master_distributors.area_code', $this->area);
            })
            ->select('master_distributors.distributor_code as distributor_code', 'master_distributors.distributor_name as distributor_name')
            ->distinct()
            ->orderBy('master_distributors.distributor_name')
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
