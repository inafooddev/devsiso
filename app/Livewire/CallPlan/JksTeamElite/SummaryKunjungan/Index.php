<?php

namespace App\Livewire\CallPlan\JksTeamElite\SummaryKunjungan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use EnforcesMenuPermissions;

    #[Title('Plan Call (JKS) vs Actual Visit')]
    protected string $menuRoute = 'call-plan.jks-team-elite.summary-kunjungan'; 

    public $regions = [];
    public $areas = [];
    public $teams = [];

    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedTeam = '';
    
    public $startDate = '';
    public $endDate = '';

    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedTeam = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';

    public $currentTab = 'detail';

    public function setTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function mount()
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        $this->loadRegions();
    }

    public function loadRegions()
    {
        $user = auth()->user();
        $query = DB::table('jks_team_elite')
            ->select('kode_region', 'nama_region')
            ->whereNotNull('kode_region');
            
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('kode_region', (array) $user->region_code);
        }
            
        $this->regions = $query->distinct()
            ->orderBy('nama_region')
            ->get()
            ->toArray();
            
        if (count($this->regions) > 0 && empty($this->selectedRegion)) {
            $this->selectedRegion = $this->regions[0]->kode_region;
            $this->updatedSelectedRegion($this->selectedRegion);
        }
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->selectedTeam = '';
        $this->areas = [];
        $this->teams = [];

        if ($value) {
            $this->areas = DB::table('jks_team_elite')
                ->where('kode_region', $value)
                ->whereNotNull('kode_area')
                ->select('kode_area', 'nama_area')
                ->distinct()
                ->orderBy('nama_area')
                ->get()
                ->toArray();
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->selectedTeam = '';
        $this->teams = [];

        if ($value) {
            $this->teams = DB::table('jks_team_elite')
                ->where('kode_region', $this->selectedRegion)
                ->where('kode_area', $value)
                ->whereNotNull('kode_team')
                ->select('kode_team', 'nama_team')
                ->distinct()
                ->orderBy('nama_team')
                ->get()
                ->toArray();
        }
    }

    public function applyFilter()
    {
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedTeam = $this->selectedTeam;
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedTeam = '';
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedTeam = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';
        
        $this->areas = [];
        $this->teams = [];
    }

    #[Computed]
    public function dataKunjungan()
    {
        if (empty($this->appliedRegion)) {
            return collect();
        }

        $query = DB::table('jks_team_elite as jks')
            ->select(
                'jks.tanggal', 
                'jks.kode_team',
                'jks.nama_team',
                'jks.kode_region',
                'jks.nama_region',
                'jks.kode_area',
                'jks.nama_area',
                'jks.distributor_code',
                'jks.distributor_name',
                'jks.custno',
                'jks.custname',
                'jks.addres',
                'list.pilar',
                'list.target',
                'visit.flag_visit',
                'visit.order_val'
            )
            ->leftJoin('list_toko_pareto_team_elite as list', function($join) {
                $join->on('jks.distributor_code', '=', 'list.distributor_code')
                     ->on('jks.custno', '=', 'list.customer_code_prc');
            })
            ->leftJoin('v_hanya_list_toko_yg_di_kunjungi_hoina as visit', function($join) {
                $join->on('jks.kode_team', '=', 'visit.spv_code')
                     ->on('jks.custno', '=', 'visit.custno')
                     ->on('jks.tanggal', '=', 'visit.tanggal');
            });

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('jks.kode_region', (array) $user->region_code);
        }

        if ($this->appliedRegion) {
            $query->where('jks.kode_region', $this->appliedRegion);
        }

        if ($this->appliedArea) {
            $query->where('jks.kode_area', $this->appliedArea);
        }

        if ($this->appliedTeam) {
            $query->where('jks.kode_team', $this->appliedTeam);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('jks.tanggal', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $query->where('jks.tanggal', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $query->where('jks.tanggal', '<=', $this->appliedEndDate);
        }

        $query->orderBy('jks.tanggal', 'asc');

        // Limit the results to avoid massive memory usage on initial load without pagination
        // Can be changed to paginate() later if needed
        return $query->limit(1000)->get();
    }

    #[Computed]
    public function kpiData()
    {
        if (empty($this->appliedRegion)) {
            return [
                'total_jks' => 0,
                'total_visit' => 0,
                'total_order' => 0,
                'total_target' => 0,
                'total_rwo' => 0,
                'total_pnr' => 0,
                'total_ngvo' => 0,
                'visit_rwo' => 0,
                'visit_pnr' => 0,
                'visit_ngvo' => 0,
            ];
        }

        $data = $this->dataKunjungan;

        $total_jks = $data->count();
        $total_visit = $data->filter(fn($item) => $item->flag_visit === 'Y')->count();
        $total_order = $data->sum('order_val');
        
        $total_target = $data->unique(function ($item) {
            return $item->distributor_code . '-' . $item->custno;
        })->sum('target');
        
        $total_rwo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '1. RWO'))->count();
        $total_pnr = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '2. PNR'))->count();
        $total_ngvo = $data->filter(fn($item) => str_contains(strtoupper($item->pilar ?? ''), '3. NGVO'))->count();

        $visit_rwo = $data->filter(fn($item) => $item->flag_visit === 'Y' && str_contains(strtoupper($item->pilar ?? ''), '1. RWO'))->count();
        $visit_pnr = $data->filter(fn($item) => $item->flag_visit === 'Y' && str_contains(strtoupper($item->pilar ?? ''), '2. PNR'))->count();
        $visit_ngvo = $data->filter(fn($item) => $item->flag_visit === 'Y' && str_contains(strtoupper($item->pilar ?? ''), '3. NGVO'))->count();

        return [
            'total_jks' => $total_jks,
            'total_visit' => $total_visit,
            'total_order' => $total_order,
            'total_target' => $total_target,
            'total_rwo' => $total_rwo,
            'total_pnr' => $total_pnr,
            'total_ngvo' => $total_ngvo,
            'visit_rwo' => $visit_rwo,
            'visit_pnr' => $visit_pnr,
            'visit_ngvo' => $visit_ngvo,
        ];
    }

    #[Computed]
    public function dataSummary()
    {
        $uniqueTargets = DB::table('jks_team_elite as sub_jks')
            ->select('sub_jks.kode_team', 'sub_jks.distributor_code', 'sub_jks.custno', 'sub_list.target')
            ->leftJoin('list_toko_pareto_team_elite as sub_list', function($join) {
                $join->on('sub_jks.distributor_code', '=', 'sub_list.distributor_code')
                     ->on('sub_jks.custno', '=', 'sub_list.customer_code_prc');
            })
            ->whereNotNull('sub_jks.kode_team')
            ->distinct();

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $uniqueTargets->whereIn('sub_jks.kode_region', (array) $user->region_code);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $uniqueTargets->whereBetween('sub_jks.tanggal', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $uniqueTargets->where('sub_jks.tanggal', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $uniqueTargets->where('sub_jks.tanggal', '<=', $this->appliedEndDate);
        }

        $targetSums = DB::query()
            ->fromSub($uniqueTargets, 'unique_toko')
            ->select('kode_team', DB::raw('SUM(target) as total_target'))
            ->groupBy('kode_team');

        $query = DB::table('jks_team_elite as jks')
            ->select(
                'jks.kode_region',
                'jks.nama_region',
                'jks.kode_area',
                'jks.nama_area',
                'jks.kode_team',
                'jks.nama_team',
                DB::raw('COUNT(jks.custno) as total_plan'),
                DB::raw('COUNT(visit.custno) as total_visit'),
                DB::raw('COALESCE(target_sums.total_target, 0) as total_target'),
                DB::raw('SUM(visit.order_val) as total_order'),
                DB::raw("SUM(CASE WHEN UPPER(list.pilar) LIKE '%1. RWO%' THEN 1 ELSE 0 END) as rwo_plan"),
                DB::raw("SUM(CASE WHEN UPPER(list.pilar) LIKE '%1. RWO%' AND visit.custno IS NOT NULL THEN 1 ELSE 0 END) as rwo_actual"),
                DB::raw("SUM(CASE WHEN UPPER(list.pilar) LIKE '%2. PNR%' THEN 1 ELSE 0 END) as pnr_plan"),
                DB::raw("SUM(CASE WHEN UPPER(list.pilar) LIKE '%2. PNR%' AND visit.custno IS NOT NULL THEN 1 ELSE 0 END) as pnr_actual"),
                DB::raw("SUM(CASE WHEN UPPER(list.pilar) LIKE '%3. NGVO%' THEN 1 ELSE 0 END) as ngvo_plan"),
                DB::raw("SUM(CASE WHEN UPPER(list.pilar) LIKE '%3. NGVO%' AND visit.custno IS NOT NULL THEN 1 ELSE 0 END) as ngvo_actual")
            )
            ->leftJoin('v_hanya_list_toko_yg_di_kunjungi_hoina as visit', function($join) {
                $join->on('jks.kode_team', '=', 'visit.spv_code')
                     ->on('jks.custno', '=', 'visit.custno')
                     ->on('jks.tanggal', '=', 'visit.tanggal');
            })
            ->leftJoin('list_toko_pareto_team_elite as list', function($join) {
                $join->on('jks.distributor_code', '=', 'list.distributor_code')
                     ->on('jks.custno', '=', 'list.customer_code_prc');
            })
            ->leftJoinSub($targetSums, 'target_sums', function ($join) {
                $join->on('jks.kode_team', '=', 'target_sums.kode_team');
            })
            ->whereNotNull('jks.kode_team')
            ->groupBy(
                'jks.kode_region',
                'jks.nama_region',
                'jks.kode_area',
                'jks.nama_area',
                'jks.kode_team',
                'jks.nama_team',
                'target_sums.total_target'
            )
            ->orderBy('jks.nama_region')
            ->orderBy('jks.nama_area')
            ->orderBy('jks.nama_team');

        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('jks.kode_region', (array) $user->region_code);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('jks.tanggal', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $query->where('jks.tanggal', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $query->where('jks.tanggal', '<=', $this->appliedEndDate);
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.call-plan.jks-team-elite.summary-kunjungan.index', [
            'dataKunjungan' => $this->dataKunjungan,
            'kpiData' => $this->kpiData,
            'dataSummary' => $this->dataSummary
        ])->layout('layouts.app');
    }
}
