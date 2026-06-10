<?php

namespace App\Livewire\CallPlan\JksTeamElite\MonitoringJksSisoVsEska;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    public $filterMonth;
    public $search = '';
    public $filterRegion = '';

    // Modal State
    public $selectedTeamCode;
    public $selectedTeamName;
    public $selectedDate;
    public $selectedType;
    public $storeDetails = [];
    public $storeDetailsSiso = [];
    public $storeDetailsEska = [];
    public $sisoCustnos = [];
    public $eskaCustnos = [];
    public $isDetailModalOpen = false;

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
        $query = DB::table('team_elite_code_mappings as t')
            ->leftJoin('master_regions as mr', 'mr.region_code', '=', 't.region_code')
            ->leftJoin('master_areas as ma', 'ma.area_code', '=', 't.area_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code')
            ->select([
                't.region_code',
                'mr.region_name',
                't.area_code',
                'ma.area_name',
                't.team_elite_code as supervisor_code',
                'f.SLSNAME as supervisor_name',
                't.level'
            ]);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('t.region_code', (array) $user->region_code);
        }

        if ($this->filterRegion) {
            $query->where('t.region_code', $this->filterRegion);
        }
            
        if ($this->search) {
            $query->where(function($q) {
                $q->where('t.team_elite_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('f.SLSNAME', 'ilike', '%' . $this->search . '%')
                  ->orWhere('mr.region_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ma.area_name', 'ilike', '%' . $this->search . '%');
            });
        }

        $query->orderBy('mr.region_name', 'asc')
            ->orderBy('ma.area_name', 'asc')
            ->orderBy('t.team_elite_code', 'asc');

        $teams = $query->get();

        $regionsQuery = DB::table('master_regions')
            ->select('region_code', 'region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionsQuery->whereIn('region_code', (array) $user->region_code);
        }
        $regions = $regionsQuery->orderBy('region_name')->get();

        $monthDates = [];
        $dataMatrix = []; // $dataMatrix[$supervisor_code][$date] = ['siso' => x, 'eska' => y, 'selisih' => z]

        if ($this->filterMonth) {
            $year = substr($this->filterMonth, 0, 4);
            $month = substr($this->filterMonth, 5, 2);

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

            // 1. Header query
            $datesRaw = DB::table('master_calender as mc')
                ->select('mc.date')
                ->whereBetween('mc.date', [$startDate, $endDate])
                ->orderBy('mc.date', 'asc')
                ->get();

            foreach ($datesRaw as $dt) {
                $parsedDate = Carbon::parse($dt->date);
                $monthDates[$dt->date] = [
                    'label' => $parsedDate->format('d'),
                    'is_sunday' => $parsedDate->isSunday(),
                ];
            }

            // Initialization of dataMatrix
            foreach ($teams as $team) {
                foreach ($monthDates as $date => $dayData) {
                    $dataMatrix[$team->supervisor_code][$date] = [
                        'siso' => 0,
                        'eska' => 0,
                        'selisih' => 0
                    ];
                }
            }

            $supervisorCodes = $teams->pluck('supervisor_code')->toArray();

            if (!empty($supervisorCodes)) {
                // 3. SISO query
                $sisoRaw = DB::table('jks_team_elite as jte')
                    ->select('jte.kode_team as supervisor_code', 'jte.tanggal', DB::raw('COUNT(DISTINCT jte.custno) as total_siso'))
                    ->whereBetween('jte.tanggal', [$startDate, $endDate])
                    ->whereIn('jte.kode_team', $supervisorCodes)
                    ->groupBy('jte.kode_team', 'jte.tanggal')
                    ->get();

                foreach ($sisoRaw as $siso) {
                    if (isset($dataMatrix[$siso->supervisor_code][$siso->tanggal])) {
                        $dataMatrix[$siso->supervisor_code][$siso->tanggal]['siso'] = $siso->total_siso;
                    }
                }

                // 4. ESKA query
                $eskaRaw = DB::table('rpt_visit_an_h as rvah')
                    ->select(
                        DB::raw('rvah."TANGGAL"::date as tanggal'), 
                        'rvah.MUID as supervisor_code', 
                        DB::raw('COUNT(DISTINCT rvah."CUSTNO") as total_eska')
                    )
                    ->where('rvah.RID', 'HOINA')
                    ->where('rvah.FLAG_PJP', 'R')
                    ->where('rvah.CUSTNO', 'NOT LIKE', 'D202%')
                    ->whereRaw('rvah."TANGGAL"::date BETWEEN ? AND ?', [$startDate, $endDate])
                    ->whereIn('rvah.MUID', $supervisorCodes)
                    ->groupBy(DB::raw('rvah."TANGGAL"::date'), 'rvah.MUID')
                    ->get();

                foreach ($eskaRaw as $eska) {
                    if (isset($dataMatrix[$eska->supervisor_code][$eska->tanggal])) {
                        $dataMatrix[$eska->supervisor_code][$eska->tanggal]['eska'] = $eska->total_eska;
                    }
                }

                // Calculate Selisih
                foreach ($teams as $team) {
                    foreach ($monthDates as $date => $dayData) {
                        $sisoCount = $dataMatrix[$team->supervisor_code][$date]['siso'];
                        $eskaCount = $dataMatrix[$team->supervisor_code][$date]['eska'];
                        $dataMatrix[$team->supervisor_code][$date]['selisih'] = $sisoCount - $eskaCount;
                    }
                }
            }
        }

        return view('livewire.call-plan.jks-team-elite.monitoring-jks-siso-vs-eska.index', [
            'teams' => $teams,
            'regions' => $regions,
            'dataMatrix' => $dataMatrix,
            'monthDates' => $monthDates
        ])->layout('layouts.app');
    }

    public function showStoreDetails($kodeTeam, $date, $type)
    {
        $this->selectedTeamCode = $kodeTeam;
        $this->selectedDate = $date;
        $this->selectedType = $type;

        $team = DB::table('fsalesman')->where('SLSNO', $kodeTeam)->first();
        $this->selectedTeamName = $team ? $team->SLSNAME : $kodeTeam;

        // Fetch SISO
        $this->storeDetailsSiso = DB::table('jks_team_elite as j')
            ->select(
                'j.custno', 
                'j.custname', 
                'j.distributor_name', 
                'j.addres'
            )
            ->where('j.kode_team', $kodeTeam)
            ->where('j.tanggal', $date)
            ->orderBy('j.custname', 'asc')
            ->distinct()
            ->get()
            ->toArray();

        // Fetch ESKA
        $this->storeDetailsEska = DB::table('rpt_visit_an_h as r')
            ->select(
                'r.CUSTNO as custno',
                'r.CUSTNAME as custname',
                DB::raw("'-' as distributor_name"),
                DB::raw("'-' as addres")
            )
            ->where('r.RID', 'HOINA')
            ->where('r.FLAG_PJP', 'R')
            ->where('r.CUSTNO', 'not like', 'D202%')
            ->where('r.MUID', $kodeTeam)
            ->whereRaw('r."TANGGAL"::date = ?', [$date])
            ->distinct()
            ->get()
            ->toArray();

        $this->sisoCustnos = collect($this->storeDetailsSiso)->pluck('custno')->toArray();
        $this->eskaCustnos = collect($this->storeDetailsEska)->pluck('custno')->toArray();

        if ($type === 'SISO') {
            $this->storeDetails = $this->storeDetailsSiso;
        } elseif ($type === 'ESKA') {
            $this->storeDetails = $this->storeDetailsEska;
        }

        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->reset(['selectedTeamCode', 'selectedTeamName', 'selectedDate', 'selectedType', 'storeDetails', 'storeDetailsSiso', 'storeDetailsEska', 'sisoCustnos', 'eskaCustnos']);
    }
}
