<?php

namespace App\Livewire\Others\MappingSupervisorCode;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\MasterRegion;
use App\Models\MasterArea;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $regionFilter = '';
    public $areaFilter = '';
    public $levelFilter = '';

    public $regions = [];
    public $areas = [];
    public $levels = ['region', 'area', 'supervisor'];

    protected $queryString = ['search', 'regionFilter', 'areaFilter', 'levelFilter'];

    public function mount()
    {
        $this->regions = MasterRegion::orderBy('region_name')->get();
        if ($this->regionFilter) {
            $this->areas = MasterArea::where('region_code', $this->regionFilter)->orderBy('area_name')->get();
        }
    }

    public function updatedRegionFilter($value)
    {
        $this->areaFilter = '';
        if ($value) {
            $this->areas = MasterArea::where('region_code', $value)->orderBy('area_name')->get();
        } else {
            $this->areas = collect();
        }
        $this->resetPage();
    }

    public function updatedAreaFilter()
    {
        $this->resetPage();
    }

    public function updatedLevelFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DB::table('team_elite_code_mappings as tecm')
            ->leftJoin('fsalesman as f', DB::raw('f."SLSNO"'), '=', 'tecm.team_elite_code')
            ->leftJoin('master_supervisors as ms', 'tecm.siso_code', '=', 'ms.supervisor_code')
            ->leftJoin('master_regions as mr', 'tecm.region_code', '=', 'mr.region_code')
            ->leftJoin('master_areas as ma', 'tecm.area_code', '=', 'ma.area_code')
            ->select(
                'mr.region_name',
                'ma.area_name',
                'tecm.team_elite_code as kode_eska',
                DB::raw('f."SLSNAME" as nama_eska'),
                'tecm.siso_code as kode_siso',
                'ms.description as nama_siso',
                'tecm.level'
            );

        if ($this->regionFilter) {
            $query->where('tecm.region_code', $this->regionFilter);
        }
        
        if ($this->areaFilter) {
            $query->where('tecm.area_code', $this->areaFilter);
        }

        if ($this->levelFilter) {
            $query->where('tecm.level', $this->levelFilter);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('mr.region_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ma.area_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('tecm.team_elite_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere(DB::raw('f."SLSNAME"'), 'ilike', '%' . $this->search . '%')
                  ->orWhere('tecm.siso_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ms.description', 'ilike', '%' . $this->search . '%');
            });
        }

        $data = $query->paginate(10);

        return view('livewire.others.mapping-supervisor-code.index', [
            'data' => $data,
        ])->layout('layouts.app');
    }
}
