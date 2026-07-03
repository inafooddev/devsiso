<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;

class PlanKunjungan extends Component
{
    use WithPagination;
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo'; 

    public $dateStart = '';
    public $dateEnd = '';
    public $selectedRegions = [];
    public $selectedAreas = [];
    public $selectedTeams = [];

    public function mount()
    {
        $this->dateStart = date('Y-m-d');
        $this->dateEnd = date('Y-m-d');
    }

    // Reset pagination when filters change
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['dateStart', 'dateEnd'])) {
            if (!empty($this->dateStart) && !empty($this->dateEnd)) {
                $start = \Carbon\Carbon::parse($this->dateStart);
                $end = \Carbon\Carbon::parse($this->dateEnd);
                
                // Max range 7 days (1 week)
                if ($start->diffInDays($end, false) > 7) {
                    if ($propertyName === 'dateStart') {
                        $this->dateEnd = $start->copy()->addDays(7)->format('Y-m-d');
                    } else {
                        $this->dateStart = $end->copy()->subDays(7)->format('Y-m-d');
                    }
                } elseif ($start->diffInDays($end, false) < 0) {
                    $this->dateEnd = $this->dateStart;
                }
            }
        }

        if (in_array($propertyName, ['dateStart', 'dateEnd', 'selectedRegions', 'selectedAreas', 'selectedTeams'])) {
            $this->resetPage();
        }
    }

    public function updatedSelectedRegions()
    {
        $regions = $this->selectedRegions;
        
        // Ensure regions is an array
        if (!is_array($regions)) {
            $regions = $regions ? [$regions] : [];
        }

        if (!empty($regions)) {
            $validAreas = DB::table('jks_team_elite')
                ->whereIn('nama_region', $regions)
                ->distinct()
                ->pluck('nama_area')
                ->toArray();
            $this->selectedAreas = array_values(array_intersect($this->selectedAreas, $validAreas));

            $validTeams = DB::table('jks_team_elite')
                ->whereIn('nama_region', $regions)
                ->distinct()
                ->pluck('nama_team')
                ->toArray();
            $this->selectedTeams = array_values(array_intersect($this->selectedTeams, $validTeams));
        } else {
            $this->selectedAreas = [];
            $this->selectedTeams = [];
        }
    }

    public function updatedSelectedAreas()
    {
        $areas = $this->selectedAreas;

        // Ensure areas is an array
        if (!is_array($areas)) {
            $areas = $areas ? [$areas] : [];
        }

        if (!empty($areas)) {
            $validTeams = DB::table('jks_team_elite')
                ->whereIn('nama_area', $areas)
                ->distinct()
                ->pluck('nama_team')
                ->toArray();
            $this->selectedTeams = array_values(array_intersect($this->selectedTeams, $validTeams));
        } else {
            $this->selectedTeams = [];
        }
    }

    public function resetFilters()
    {
        $this->dateStart = date('Y-m-d');
        $this->dateEnd = date('Y-m-d');
        $this->selectedRegions = [];
        $this->selectedAreas = [];
        $this->selectedTeams = [];
        $this->resetPage();
    }

    private function applyAccessScope($query, $alias = '')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        $prefix = $alias ? $alias . '.' : '';

        if (!empty($user->supervisor_code)) {
            $query->where($prefix . 'kode_team', $user->supervisor_code);
        } elseif (!empty($user->area_code)) {
            $query->whereIn($prefix . 'kode_area', (array) $user->area_code);
        } elseif (!empty($user->region_code)) {
            $query->whereIn($prefix . 'kode_region', (array) $user->region_code);
        }

        return $query;
    }

    public function getRegionOptions()
    {
        $query = DB::table('jks_team_elite')
            ->select('nama_region')
            ->whereNotNull('nama_region')
            ->where('nama_region', '!=', '');
            
        $this->applyAccessScope($query);

        return $query->distinct()->orderBy('nama_region')->pluck('nama_region');
    }

    public function getAreaOptions()
    {
        $query = DB::table('jks_team_elite')
            ->select('nama_area')
            ->whereNotNull('nama_area')
            ->where('nama_area', '!=', '');
            
        $this->applyAccessScope($query);

        if (!empty($this->selectedRegions)) {
            $query->whereIn('nama_region', $this->selectedRegions);
        }

        return $query->distinct()->orderBy('nama_area')->pluck('nama_area');
    }

    public function getTeamOptions()
    {
        $query = DB::table('jks_team_elite')
            ->select('nama_team')
            ->whereNotNull('nama_team')
            ->where('nama_team', '!=', '');
            
        $this->applyAccessScope($query);

        if (!empty($this->selectedRegions)) {
            $query->whereIn('nama_region', $this->selectedRegions);
        }
        if (!empty($this->selectedAreas)) {
            $query->whereIn('nama_area', $this->selectedAreas);
        }

        return $query->distinct()->orderBy('nama_team')->pluck('nama_team');
    }

    public function getDataProperty()
    {
        $query = DB::table('jks_team_elite as j')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('l.distributor_code', '=', 'j.distributor_code')
                     ->on('l.customer_code_prc', '=', 'j.custno');
            })
            ->leftJoin('reward_outlet as r', 'r.eskalink_code', '=', 'j.custno')
            ->where('l.pilar', '1. RWO');

        $this->applyAccessScope($query, 'j');

        if (!empty($this->dateStart) && !empty($this->dateEnd)) {
            $query->whereBetween('j.tanggal', [$this->dateStart, $this->dateEnd]);
        } elseif (!empty($this->dateStart)) {
            $query->where('j.tanggal', '>=', $this->dateStart);
        } elseif (!empty($this->dateEnd)) {
            $query->where('j.tanggal', '<=', $this->dateEnd);
        }

        if (!empty($this->selectedRegions)) {
            $query->whereIn('j.nama_region', $this->selectedRegions);
        }
        if (!empty($this->selectedAreas)) {
            $query->whereIn('j.nama_area', $this->selectedAreas);
        }
        if (!empty($this->selectedTeams)) {
            $query->whereIn('j.nama_team', $this->selectedTeams);
        }

        $query->select(
            'j.tanggal',
            'j.kode_region',
            'j.nama_region',
            'j.kode_area',
            'j.nama_area',
            'j.kode_team',
            'j.nama_team',
            'j.distributor_code',
            'j.distributor_name',
            'j.custno',
            'j.custname',
            'j.addres',
            'r.no_hp',
            'r.nama_pemilik_toko',
            'r.nik_ktp',
            'r.nama_ktp',
            'r.foto_ktp',
            'r.no_rekening',
            'r.nama_pemilik_norek',
            'r.latitude',
            'r.longitude',
            'r.foto_toko2 as tampak_depan',
            'r.foto_toko3 as tampak_dalam'
        );
        
        $query->orderBy('j.tanggal', 'desc');

        return $query->paginate(100);
    }

    public function export()
    {
        \App\Helpers\ActivityLogger::log('Export Plan Kunjungan', "Mengekspor data Plan Kunjungan RWO.");
        $filename = 'plan_kunjungan_export_' . now()->format('Ymd_His') . '.xlsx';
        
        $filters = [
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'selectedRegions' => $this->selectedRegions,
            'selectedAreas' => $this->selectedAreas,
            'selectedTeams' => $this->selectedTeams,
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PlanKunjunganExport($filters), $filename);
    }

    public function render()
    {
        return view('livewire.rwo.plan-kunjungan', [
            'regionOptions' => $this->getRegionOptions(),
            'areaOptions' => $this->getAreaOptions(),
            'teamOptions' => $this->getTeamOptions(),
            'records' => $this->data,
        ])->layout('layouts.app');
    }
}
