<?php

namespace App\Livewire\CallPlan\JksTeamElite\MonitoringJKSTeamElite;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $filterMonth;
    public $search = '';
    public $filterRegion = '';

    // Detail Modal State
    public $selectedTeamCode;
    public $selectedTeamName;
    public $selectedDate;
    public $storeDetails = [];
    public $isDetailModalOpen = false;

    public function mount()
    {
        $this->filterMonth = date('Y-m');

        $regionQuery = DB::table('master_regions')
            ->select('region_code', 'region_name');
            
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $regionQuery->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.region_code', 'master_regions.region_code')
                        ->whereRaw('TRIM(tecm.team_elite_code) = TRIM(?)', [$user->supervisor_code]);
                });
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $regionQuery->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.region_code', 'master_regions.region_code')
                        ->whereIn('tecm.area_code', $user->area_code);
                });
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionQuery->whereIn('region_code', $user->region_code);
            } else {
                $regionQuery->whereRaw('1 = 0');
            }
        }
        
        $regions = $regionQuery->get();
        if ($user && !$user->hasRole('admin') && $regions->count() === 1) {
            $this->filterRegion = $regions->first()->region_code;
        }
    }

    public function render()
    {
        $query = DB::table('team_elite_code_mappings as tecm')
            ->leftJoin('fsalesman as f', 'tecm.team_elite_code', '=', 'f.SLSNO')
            ->leftJoin('master_regions as mr', 'tecm.region_code', '=', 'mr.region_code')
            ->leftJoin('master_areas as ma', 'tecm.area_code', '=', 'ma.area_code')
            ->select([
                'tecm.team_elite_code as kode_team',
                'f.SLSNAME as nama_team',
                'tecm.region_code',
                'mr.region_name',
                'tecm.area_code',
                'ma.area_name'
            ]);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
                    ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                    ->pluck('siso_code')
                    ->toArray();

                if (!empty($sisoCodes)) {
                    $query->whereIn('tecm.siso_code', $sisoCodes);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $query->whereIn('tecm.area_code', $user->area_code);
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $query->whereIn('tecm.region_code', $user->region_code);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->filterRegion) {
            $query->where('tecm.region_code', $this->filterRegion);
        }
            
        if ($this->search) {
            $query->where(function($q) {
                $q->where('tecm.team_elite_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('f.SLSNAME', 'ilike', '%' . $this->search . '%')
                  ->orWhere('mr.region_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ma.area_name', 'ilike', '%' . $this->search . '%');
            });
        }

        $query->orderBy('mr.region_name', 'asc')
            ->orderBy('ma.area_name', 'asc')
            ->orderBy('tecm.team_elite_code', 'asc');

        $teams = $query->get();

        $regionsQuery = DB::table('master_regions')
            ->select('region_code', 'region_name');
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $regionsQuery->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.region_code', 'master_regions.region_code')
                        ->whereRaw('TRIM(tecm.team_elite_code) = TRIM(?)', [$user->supervisor_code]);
                });
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $regionsQuery->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.region_code', 'master_regions.region_code')
                        ->whereIn('tecm.area_code', $user->area_code);
                });
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionsQuery->whereIn('region_code', $user->region_code);
            } else {
                $regionsQuery->whereRaw('1 = 0');
            }
        }
        $regions = $regionsQuery->orderBy('region_name')->get();

        $jksData = [];
        $monthDates = [];
        $weekSpans = [];
        if ($this->filterMonth) {
            $year = substr($this->filterMonth, 0, 4);
            $month = substr($this->filterMonth, 5, 2);

            $jksRaw = DB::table('jks_team_elite')
                ->select(
                    'kode_team', 
                    'tanggal', 
                    DB::raw("COUNT(CASE WHEN custno NOT ILIKE '%BRI%' AND custno NOT ILIKE '%EVA%' THEN 1 END) as total_toko"),
                    DB::raw("COUNT(CASE WHEN custno ILIKE '%BRI%' OR custno ILIKE '%EVA%' THEN 1 END) as total_toko_bri_eva")
                )
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereIn('kode_team', $teams->pluck('kode_team'))
                ->groupBy('kode_team', 'tanggal')
                ->get();

            foreach ($jksRaw as $data) {
                $jksData[$data->kode_team][$data->tanggal] = [
                    'count' => $data->total_toko,
                    'has_bri_eva' => $data->total_toko_bri_eva > 0
                ];
            }

            $datesRaw = DB::table('master_calender')
                ->select('date', 'week_month')
                ->where('month', $month)
                ->where('year', $year)
                ->orderBy('date', 'asc')
                ->get();
            
            $currentWeek = null;
            $currentIndex = -1;
            $previousDate = null;

            foreach ($datesRaw as $dt) {
                $parsedDate = \Carbon\Carbon::parse($dt->date);
                $monthDates[$dt->date] = [
                    'label' => $parsedDate->format('d'),
                    'is_sunday' => $parsedDate->isSunday(),
                    'is_saturday' => $parsedDate->isSaturday(),
                    'is_weekday' => $parsedDate->isWeekday(),
                    'week_month' => $dt->week_month,
                    'is_end_of_week' => false,
                ];
                
                if ($currentWeek !== $dt->week_month) {
                    if ($previousDate) {
                        $monthDates[$previousDate]['is_end_of_week'] = true;
                    }
                    $currentWeek = $dt->week_month;
                    $currentIndex++;
                    $weekSpans[$currentIndex] = [
                        'week_month' => $dt->week_month,
                        'colspan' => 0
                    ];
                }
                
                $weekSpans[$currentIndex]['colspan']++;
                $previousDate = $dt->date;
            }
            if ($previousDate) {
                $monthDates[$previousDate]['is_end_of_week'] = true;
            }
        }

        return view('livewire.call-plan.jks-team-elite.monitoring-jks-team-elite.index', [
            'teams' => $teams,
            'regions' => $regions,
            'jksData' => $jksData,
            'monthDates' => $monthDates,
            'weekSpans' => $weekSpans
        ])->layout('layouts.app');
    }

    public function showStoreDetails($kodeTeam, $date)
    {
        $this->selectedTeamCode = $kodeTeam;
        $this->selectedDate = $date;

        $team = DB::table('fsalesman')->where('SLSNO', $kodeTeam)->first();
        $this->selectedTeamName = $team ? $team->SLSNAME : $kodeTeam;

        $query = DB::table('jks_team_elite as j')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('j.distributor_code', '=', 'l.distributor_code')
                     ->on('j.custno', '=', 'l.customer_code_prc');
            })
            ->select(
                'j.custno', 
                'j.custname', 
                'j.distributor_name', 
                'j.addres', 
                'j.nama_area', 
                'j.nama_region',
                'l.pilar',
                'l.target'
            )
            ->where('j.kode_team', $kodeTeam)
            ->where('j.tanggal', $date);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $query->where('j.kode_team', $user->supervisor_code);
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $query->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.team_elite_code', 'j.kode_team')
                        ->whereIn('tecm.area_code', $user->area_code);
                });
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $query->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.team_elite_code', 'j.kode_team')
                        ->whereIn('tecm.region_code', $user->region_code);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $this->storeDetails = $query->orderBy('j.custname', 'asc')->get()->toArray();

        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->reset(['selectedTeamCode', 'selectedTeamName', 'selectedDate', 'storeDetails']);
    }
}
