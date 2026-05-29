<?php

namespace App\Livewire\CallPlan\JksTeamElite\MonitoringJKSTeamElite;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $filterMonth;

    public function mount()
    {
        $this->filterMonth = date('Y-m');
    }

    public function render()
    {
        $teams = DB::table('fsalesman')
            ->select('SLSNO as kode_team', 'SLSNAME as nama_team')
            ->where('TEAM', 'SPI')
            ->where('KODEREGION', 'HOINA')
            ->get();

        $jksData = [];
        $monthDates = [];
        $weekSpans = [];
        if ($this->filterMonth) {
            $year = substr($this->filterMonth, 0, 4);
            $month = substr($this->filterMonth, 5, 2);

            $jksRaw = DB::table('jks_team_elite')
                ->select('kode_team', 'tanggal')
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereIn('kode_team', $teams->pluck('kode_team'))
                ->get();

            foreach ($jksRaw as $data) {
                $jksData[$data->kode_team][$data->tanggal] = true;
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
            'jksData' => $jksData,
            'monthDates' => $monthDates,
            'weekSpans' => $weekSpans
        ])->layout('layouts.app');
    }
}
