<?php

namespace App\Livewire\Others\MappingSupervisorCode;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Exports\MappingSupervisorCodeExport;
use Maatwebsite\Excel\Facades\Excel;

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

    // Form Properties
    public $isCreateModalOpen = false;
    public $isEditMode = false;
    public $mappingId = null;
    public $formRegionCode = '';
    public $formAreaCode = '';
    public $formTeamEliteCode = '';
    public $formSisoCode = '';
    public $formLevel = '';

    public $searchTeamElite = '';
    public $selectedTeamEliteName = '';

    public $formRegions = [];
    public $formAreas = [];
    public $formTeamElites = [];
    public $formSupervisors = [];

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

    protected function rules()
    {
        return [
            'formRegionCode' => 'required|string',
            'formAreaCode' => 'required|string',
            'formTeamEliteCode' => 'required|string',
            'formSisoCode' => 'required|string',
            'formLevel' => 'required|string|in:region,area,supervisor',
        ];
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['formRegionCode', 'formAreaCode', 'formTeamEliteCode', 'formSisoCode', 'formLevel', 'searchTeamElite', 'selectedTeamEliteName']);
        
        $this->isEditMode = false;
        $this->mappingId = null;

        $this->formRegions = MasterRegion::orderBy('region_name')->get();

        $this->loadTeamElites();
        
        $this->formAreas = collect();
        $this->formSupervisors = collect();
        
        $this->isCreateModalOpen = true;
    }

    public function updatedSearchTeamElite()
    {
        $this->loadTeamElites($this->isEditMode ? $this->formTeamEliteCode : null);
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $this->isEditMode = true;
        $this->mappingId = $id;
        
        $mapping = \App\Models\TeamEliteCodeMapping::find($id);
        if (!$mapping) return;
        
        $this->formRegionCode = $mapping->region_code;
        $this->formAreaCode = $mapping->area_code;
        $this->formLevel = $mapping->level;
        
        $this->formRegions = MasterRegion::orderBy('region_name')->get();
        $this->formAreas = MasterArea::where('region_code', $this->formRegionCode)->orderBy('area_name')->get();
        
        $this->loadTeamElites($mapping->team_elite_code);
        
        $this->formTeamEliteCode = $mapping->team_elite_code;
        $te = DB::table('fsalesman')->where('SLSNO', $mapping->team_elite_code)->first();
        $this->selectedTeamEliteName = $te ? $te->SLSNAME : '';
        
        $this->loadSupervisors($this->formAreaCode, $mapping->siso_code);
        $this->formSisoCode = $mapping->siso_code;
        
        $this->isCreateModalOpen = true;
    }

    public function loadTeamElites($ignoreCode = null)
    {
        // Ambil list kode eska yang sudah termapping
        $mappedTeamElites = \App\Models\TeamEliteCodeMapping::pluck('team_elite_code')->filter()->toArray();
        if ($ignoreCode) {
            $mappedTeamElites = array_diff($mappedTeamElites, [$ignoreCode]);
        }

        $query = DB::table('fsalesman as f')
            ->where('TEAM', 'SPI')
            ->select(DB::raw('f."SLSNO" as team_elite_code'), DB::raw('f."SLSNAME" as team_elite_name'));

        if (!empty($mappedTeamElites)) {
            $query->whereNotIn(DB::raw('f."SLSNO"'), $mappedTeamElites);
        }

        if (!empty($this->searchTeamElite)) {
            $query->where(function($q) {
                $q->where(DB::raw('f."SLSNO"'), 'ilike', '%' . $this->searchTeamElite . '%')
                  ->orWhere(DB::raw('f."SLSNAME"'), 'ilike', '%' . $this->searchTeamElite . '%');
            });
        }

        $this->formTeamElites = $query->orderBy(DB::raw('f."SLSNAME"'))->take(50)->get();
    }

    public function selectTeamElite($code, $name)
    {
        $this->formTeamEliteCode = $code;
        $this->selectedTeamEliteName = $name;
        $this->searchTeamElite = ''; // reset search
        $this->loadTeamElites(); // reload list
    }

    public function clearTeamElite()
    {
        $this->formTeamEliteCode = '';
        $this->selectedTeamEliteName = '';
        $this->searchTeamElite = '';
        $this->loadTeamElites();
    }

    public function updatedFormRegionCode($value)
    {
        $this->formAreaCode = '';
        $this->formSisoCode = '';
        $this->formSupervisors = collect();
        
        if ($value) {
            $this->formAreas = MasterArea::where('region_code', $value)->orderBy('area_name')->get();
        } else {
            $this->formAreas = collect();
        }
    }

    public function loadSupervisors($areaCode, $ignoreCode = null)
    {
        $this->formSupervisors = collect();
        if ($areaCode) {
            $mappedSiso = \App\Models\TeamEliteCodeMapping::pluck('siso_code')->filter()->toArray();
            if ($ignoreCode) {
                $mappedSiso = array_diff($mappedSiso, [$ignoreCode]);
            }
            $query = DB::table('master_supervisors')->where('area_code', $areaCode);
            
            if (!empty($mappedSiso)) {
                $query->whereNotIn('supervisor_code', $mappedSiso);
            }
            
            $this->formSupervisors = $query->orderBy('description')->get();
        }
    }

    public function updatedFormAreaCode($value)
    {
        $this->formSisoCode = '';
        $this->loadSupervisors($value);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'region_code' => $this->formRegionCode,
            'area_code' => $this->formAreaCode,
            'team_elite_code' => $this->formTeamEliteCode,
            'siso_code' => $this->formSisoCode,
            'level' => $this->formLevel,
        ];

        if ($this->isEditMode && $this->mappingId) {
            \App\Models\TeamEliteCodeMapping::find($this->mappingId)?->update($data);
            session()->flash('message', 'Mapping berhasil diubah.');
        } else {
            \App\Models\TeamEliteCodeMapping::create($data);
            session()->flash('message', 'Mapping berhasil ditambahkan.');
        }

        $this->isCreateModalOpen = false;
        
        if ($this->regionFilter || $this->areaFilter || $this->levelFilter || $this->search) {
            $this->resetPage();
        }
    }

    public function deleteMapping($id)
    {
        \App\Models\TeamEliteCodeMapping::find($id)?->delete();
        session()->flash('message', 'Mapping berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(
            new MappingSupervisorCodeExport($this->regionFilter, $this->areaFilter, $this->levelFilter, $this->search),
            'Mapping_Supervisor_Code.xlsx'
        );
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
                'tecm.level',
                'tecm.id'
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

        $query->orderBy('mr.region_name', 'asc')
              ->orderBy('ma.area_name', 'asc')
              ->orderBy('tecm.level', 'asc');

        $data = $query->paginate(100);

        return view('livewire.others.mapping-supervisor-code.index', [
            'data' => $data,
        ])->layout('layouts.app');
    }
}
