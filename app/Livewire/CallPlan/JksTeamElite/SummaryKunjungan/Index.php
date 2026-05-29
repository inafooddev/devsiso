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

    #[Title('Summary Kunjungan JKS Team Elite')]
    protected string $menuRoute = 'call-plan.jks-team-elite.summary-kunjungan'; 

    public $regions = [];
    public $areas = [];
    public $teams = [];

    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedTeam = '';
    
    public $showFilterModal = false;

    public function mount()
    {
        $this->loadRegions();
    }

    public function loadRegions()
    {
        $this->regions = DB::table('jks_team_elite')
            ->select('kode_region', 'nama_region')
            ->whereNotNull('kode_region')
            ->distinct()
            ->orderBy('nama_region')
            ->get()
            ->toArray();
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

    #[Computed]
    public function dataKunjungan()
    {
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

        if ($this->selectedRegion) {
            $query->where('jks.kode_region', $this->selectedRegion);
        }

        if ($this->selectedArea) {
            $query->where('jks.kode_area', $this->selectedArea);
        }

        if ($this->selectedTeam) {
            $query->where('jks.kode_team', $this->selectedTeam);
        }

        // Limit the results to avoid massive memory usage on initial load without pagination
        // Can be changed to paginate() later if needed
        return $query->limit(1000)->get();
    }

    #[Computed]
    public function kpiData()
    {
        $data = $this->dataKunjungan;

        $total_jks = $data->count();
        $total_visit = $data->filter(fn($item) => $item->flag_visit === 'Y')->count();
        $total_order = $data->sum('order_val');
        $total_target = $data->sum('target');
        
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

    public function render()
    {
        return view('livewire.call-plan.jks-team-elite.summary-kunjungan.index', [
            'dataKunjungan' => $this->dataKunjungan,
            'kpiData' => $this->kpiData
        ])->layout('layouts.app');
    }
}
