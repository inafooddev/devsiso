<?php

namespace App\Livewire\CallPlan\JksTeamElite\MonitoringJKSTeamElite;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $filterMonth;
    public $search = '';
    public $filterRegion = '';

    public function mount()
    {
        $this->filterMonth = date('Y-m');

        $regionQuery = DB::table('master_regions')
            ->select('region_code', 'region_name');
            
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
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
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('tecm.region_code', (array) $user->region_code);
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
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionsQuery->whereIn('region_code', (array) $user->region_code);
        }
        $regions = $regionsQuery->orderBy('region_name')->get();

        $jksData = [];
        $monthDates = [];
        $weekSpans = [];
        if ($this->filterMonth) {
            $year = substr($this->filterMonth, 0, 4);
            $month = substr($this->filterMonth, 5, 2);

            $jksRaw = DB::table('jks_team_elite')
                ->select('kode_team', 'tanggal', DB::raw('count(*) as total_toko'))
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereIn('kode_team', $teams->pluck('kode_team'))
                ->groupBy('kode_team', 'tanggal')
                ->get();

            foreach ($jksRaw as $data) {
                $jksData[$data->kode_team][$data->tanggal] = $data->total_toko;
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
}
