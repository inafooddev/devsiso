<?php

namespace App\Livewire\CallPlan\JksTeamElite\MasterCustomer\MonitoringCustomerPareto;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected string $menuRoute = 'call-plan.jks-team-elite.master-customer';
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterSupervisor = '';
    public $filterDistributor = '';
    public $filterStatus = '';
    public $filterPilar = '';
    
    public $isDetailModalOpen = false;
    public $detailVisits = [];
    public $detailCustomerName = '';
    public $detailLevel = '';

    public $isAddPlanModalOpen = false;
    public $addPlanTanggal = '';
    public $addPlanKodeTeam = '';
    public $availableTeamCodes = [];
    public $selectedCustomerForPlan = [];
    
    public $startDate;
    public $endDate;

    public $sortColumn = 'md.region_name';
    public $sortDirection = 'asc';
    
    public $isFilterModalOpen = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterRegion' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterSupervisor' => ['except' => ''],
        'filterDistributor' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterPilar' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'sortColumn' => ['except' => 'md.region_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $this->initDefaults();
    }

    private function initDefaults()
    {
        if (empty($this->startDate)) {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            // Auto-set Region
            $regionQuery = DB::table('master_distributors')->select('region_code')->whereNotNull('region_code')->distinct();
            $regionQuery = $this->applyHierarchyAccess($regionQuery, 'distributor_code');
            $regions = $regionQuery->get();
            if ($regions->count() === 1 && empty($this->filterRegion)) {
                $this->filterRegion = $regions->first()->region_code;
            }
            
            if ($this->filterRegion) {
                // Auto-set Area
                $areaQuery = DB::table('master_distributors')->select('area_code')->whereNotNull('area_code')->where('region_code', $this->filterRegion)->distinct();
                $areaQuery = $this->applyHierarchyAccess($areaQuery, 'distributor_code');
                $areas = $areaQuery->get();
                if ($areas->count() === 1 && empty($this->filterArea)) {
                    $this->filterArea = $areas->first()->area_code;
                }
                
                if ($this->filterArea) {
                    // Auto-set Supervisor
                    $spvQuery = DB::table('master_distributors as md')
                        ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                        ->select('t.team_elite_code as supervisor_code')
                        ->where('md.area_code', $this->filterArea)
                        ->distinct();
                    $spvQuery = $this->applyHierarchyAccess($spvQuery, 'md.distributor_code');
                    $spvs = $spvQuery->get();
                    if ($spvs->count() === 1 && empty($this->filterSupervisor)) {
                        $this->filterSupervisor = $spvs->first()->supervisor_code;
                    }
                }
            }
        }
    }

    private function applyHierarchyAccess($query, $distributorCodeColumn = 'l.distributor_code')
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $sisoCodes = DB::table('team_elite_code_mappings')
                ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                ->pluck('siso_code')
                ->toArray();

            return $query->whereExists(function ($sub) use ($sisoCodes, $distributorCodeColumn) {
                if (!empty($sisoCodes)) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md_auth')
                        ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                        ->whereIn('md_auth.supervisor_code', $sisoCodes);
                } else {
                    $sub->selectRaw('1')->whereRaw('1 = 0');
                }
            });
        }

        if (!empty($user->area_code) && count((array) $user->area_code) > 0) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md_auth')
                    ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                    ->whereIn('md_auth.area_code', (array) $user->area_code);
            });
        }

        if (!empty($user->region_code) && count((array) $user->region_code) > 0) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md_auth')
                    ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                    ->whereIn('md_auth.region_code', (array) $user->region_code);
            });
        }

        return $query;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterRegion() { $this->reset(['filterArea', 'filterSupervisor', 'filterDistributor']); $this->resetPage(); }
    public function updatingFilterArea() { $this->reset(['filterSupervisor', 'filterDistributor']); $this->resetPage(); }
    public function updatingFilterSupervisor() { $this->reset('filterDistributor'); $this->resetPage(); }
    public function updatingFilterDistributor() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        $startDate = $this->startDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->endDate ?: Carbon::now()->endOfMonth()->format('Y-m-d');

        $query = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code')
            ->leftJoin(DB::raw("(
                SELECT
                    j.custno,
                    COUNT(CASE WHEN UPPER(tm.\"level\") = 'REGION' THEN 1 END) AS rsm,
                    COUNT(CASE WHEN UPPER(tm.\"level\") = 'AREA' THEN 1 END) AS asm,
                    COUNT(CASE WHEN UPPER(tm.\"level\") = 'SUPERVISOR' THEN 1 END) AS spv
                FROM jks_team_elite j
                INNER JOIN team_elite_code_mappings tm ON tm.team_elite_code = j.kode_team
                WHERE j.tanggal BETWEEN ? AND ?
                GROUP BY j.custno
            ) as v"), function($join) {
                $join->on('v.custno', '=', 'l.customer_code_prc');
            })
            ->addBinding([$startDate, $endDate], 'join')
            ->select(
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'md.supervisor_code as siso_code',
                't.team_elite_code as supervisor_code',
                'f.SLSNAME as supervisor_name',
                'l.distributor_code',
                'md.distributor_name',
                'l.customer_code_prc',
                'l.uniq_kd',
                'l.customer_name',
                'l.customer_address',
                'l.pilar',
                'l.target',
                DB::raw('COALESCE(v.rsm, 0) AS rsm'),
                DB::raw('COALESCE(v.asm, 0) AS asm'),
                DB::raw('COALESCE(v.spv, 0) AS spv')
            )
            ->whereRaw("TRIM(COALESCE(l.pilar, '')) <> '4. GRO'")
            ->whereNotNull('l.pilar')
            ->whereRaw("TRIM(l.pilar) <> ''");

        // Hierarchy Access
        $query = $this->applyHierarchyAccess($query, 'l.distributor_code');

        // Search Filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('l.customer_code_prc', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_name', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_address', 'ilike', "%{$this->search}%")
                  ->orWhere('l.pilar', 'ilike', "%{$this->search}%")
                  ->orWhere('f.SLSNAME', 'ilike', "%{$this->search}%")
                  ->orWhere('md.distributor_name', 'ilike', "%{$this->search}%")
                  ->orWhere('l.uniq_kd', 'ilike', "%{$this->search}%");
            });
        }

        // Dropdown Filters
        if ($this->filterRegion) $query->where('md.region_code', $this->filterRegion);
        if ($this->filterArea) $query->where('md.area_code', $this->filterArea);
        if ($this->filterSupervisor) {
            $sisoCodes = DB::table('team_elite_code_mappings')
                ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$this->filterSupervisor])
                ->pluck('siso_code')
                ->toArray();
            
            if (!empty($sisoCodes)) {
                $query->whereIn('md.supervisor_code', $sisoCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        if ($this->filterDistributor) $query->where('l.distributor_code', $this->filterDistributor);

        if ($this->filterPilar) {
            $query->where('l.pilar', $this->filterPilar);
        }

        if ($this->filterStatus) {
            if ($this->filterStatus === 'Masuk Plan') {
                $query->where(function($q) {
                    $q->where(DB::raw('COALESCE(v.rsm, 0)'), '>', 0)
                      ->orWhere(DB::raw('COALESCE(v.asm, 0)'), '>', 0)
                      ->orWhere(DB::raw('COALESCE(v.spv, 0)'), '>', 0);
                });
            } elseif ($this->filterStatus === 'Belum Diplan') {
                $query->where(DB::raw('COALESCE(v.rsm, 0)'), '=', 0)
                      ->where(DB::raw('COALESCE(v.asm, 0)'), '=', 0)
                      ->where(DB::raw('COALESCE(v.spv, 0)'), '=', 0);
            }
        }

        // Sorting
        if ($this->sortColumn) {
            if ($this->sortColumn === 'rsm') {
                $query->orderBy(DB::raw('COALESCE(v.rsm, 0)'), $this->sortDirection);
            } elseif ($this->sortColumn === 'asm') {
                $query->orderBy(DB::raw('COALESCE(v.asm, 0)'), $this->sortDirection);
            } elseif ($this->sortColumn === 'spv') {
                $query->orderBy(DB::raw('COALESCE(v.spv, 0)'), $this->sortDirection);
            } else {
                $query->orderBy($this->sortColumn, $this->sortDirection);
            }
        } else {
            $query->orderBy('md.region_name')
                  ->orderBy('md.area_name')
                  ->orderBy('f.SLSNAME')
                  ->orderBy('l.customer_name')
                  ->orderBy('l.pilar');
        }

        return $query;
    }

    public function getKpiStatsProperty()
    {
        $baseQuery = clone $this->getBaseQuery();
        // Remove orders for efficiency in aggregation
        $baseQuery->orders = null;

        $stats = DB::query()->fromSub($baseQuery, 'sub')
            ->select(
                DB::raw('COUNT(*) as total_toko'),
                DB::raw('SUM(CASE WHEN sub.rsm > 0 OR sub.asm > 0 OR sub.spv > 0 THEN 1 ELSE 0 END) as total_plan'),
                DB::raw('SUM(CASE WHEN sub.pilar = \'1. RWO\' THEN 1 ELSE 0 END) as total_rwo'),
                DB::raw('SUM(CASE WHEN sub.pilar = \'1. RWO\' AND (sub.rsm > 0 OR sub.asm > 0 OR sub.spv > 0) THEN 1 ELSE 0 END) as plan_rwo'),
                DB::raw('SUM(CASE WHEN sub.pilar = \'2. PNR\' THEN 1 ELSE 0 END) as total_pnr'),
                DB::raw('SUM(CASE WHEN sub.pilar = \'2. PNR\' AND (sub.rsm > 0 OR sub.asm > 0 OR sub.spv > 0) THEN 1 ELSE 0 END) as plan_pnr'),
                DB::raw('SUM(CASE WHEN sub.pilar = \'3. NGVO\' THEN 1 ELSE 0 END) as total_ngvo'),
                DB::raw('SUM(CASE WHEN sub.pilar = \'3. NGVO\' AND (sub.rsm > 0 OR sub.asm > 0 OR sub.spv > 0) THEN 1 ELSE 0 END) as plan_ngvo')
            )->first();

        return $stats;
    }

    public function render()
    {
        // Region options
        $regionQuery = DB::table('master_distributors')->select('region_code', 'region_name')->whereNotNull('region_code')->distinct();
        $regionQuery = $this->applyHierarchyAccess($regionQuery, 'distributor_code');
        $regions = $regionQuery->orderBy('region_name')->get();
        
        // Area options
        $areas = [];
        if ($this->filterRegion) {
            $areaQuery = DB::table('master_distributors')->select('area_code', 'area_name')->where('region_code', $this->filterRegion)->whereNotNull('area_code')->distinct();
            $areaQuery = $this->applyHierarchyAccess($areaQuery, 'distributor_code');
            $areas = $areaQuery->orderBy('area_name')->get();
        }

        // Supervisor options
        $supervisors = [];
        if ($this->filterArea) {
            $spvQuery = DB::table('master_distributors as md')
                ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                ->join('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code')
                ->select('t.team_elite_code as supervisor_code', 'f.SLSNAME as supervisor_name')
                ->where('md.area_code', $this->filterArea)
                ->distinct();
            $spvQuery = $this->applyHierarchyAccess($spvQuery, 'md.distributor_code');
            $supervisors = $spvQuery->orderBy('supervisor_name')->get();
        }

        // Distributor options
        $distributors = [];
        if ($this->filterSupervisor) {
            $distQuery = DB::table('master_distributors as md')
                ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                ->select('md.distributor_code', 'md.distributor_name')
                ->where('t.team_elite_code', $this->filterSupervisor)
                ->distinct();
            $distQuery = $this->applyHierarchyAccess($distQuery, 'md.distributor_code');
            $distributors = $distQuery->orderBy('distributor_name')->get();
        } elseif ($this->filterArea) {
            $distQuery = DB::table('master_distributors as md')
                ->select('md.distributor_code', 'md.distributor_name')
                ->where('md.area_code', $this->filterArea)
                ->distinct();
            $distQuery = $this->applyHierarchyAccess($distQuery, 'md.distributor_code');
            $distributors = $distQuery->orderBy('distributor_name')->get();
        } elseif ($this->filterRegion) {
            $distQuery = DB::table('master_distributors as md')
                ->select('md.distributor_code', 'md.distributor_name')
                ->where('md.region_code', $this->filterRegion)
                ->distinct();
            $distQuery = $this->applyHierarchyAccess($distQuery, 'md.distributor_code');
            $distributors = $distQuery->orderBy('distributor_name')->get();
        }

        $data = $this->getBaseQuery()->paginate(100);

        return view('livewire.call-plan.jks-team-elite.master-customer.monitoring-customer-pareto.index', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
            'distributors' => $distributors,
        ])->layout('layouts.app');
    }

    public function openFilterModal() { $this->isFilterModalOpen = true; }
    public function closeFilterModal() { $this->isFilterModalOpen = false; }
    public function applyFilter() { $this->isFilterModalOpen = false; $this->resetPage(); }
    public function resetFilter() { 
        $this->reset(['filterRegion', 'filterArea', 'filterSupervisor', 'filterDistributor', 'filterStatus', 'filterPilar', 'startDate', 'endDate']); 
        $this->isFilterModalOpen = false; 
        
        $this->initDefaults();
        $this->resetPage(); 
    }

    public function showDetailVisits($customerCode, $customerName, $level)
    {
        $startDate = $this->startDate ?: \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->endDate ?: \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->detailCustomerName = $customerName;
        $this->detailLevel = $level;

        $this->detailVisits = DB::table('jks_team_elite as j')
            ->join('team_elite_code_mappings as tm', 'tm.team_elite_code', '=', 'j.kode_team')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'j.kode_team')
            ->select('j.tanggal', 'j.kode_team', 'f.SLSNAME as nama_team', 'tm.level')
            ->where('j.custno', $customerCode)
            ->whereBetween('j.tanggal', [$startDate, $endDate])
            ->whereRaw('UPPER(tm."level") = ?', [$level])
            ->orderBy('j.tanggal', 'asc')
            ->get()
            ->toArray();

        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->detailVisits = [];
    }

    public function openAddPlanModal($customerCode, $distributorCode)
    {
        $customer = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->where('l.customer_code_prc', $customerCode)
            ->where('l.distributor_code', $distributorCode)
            ->select('l.customer_code_prc as customer_code', 'l.customer_name', 'l.customer_address', 'md.distributor_code', 'md.distributor_name', 'md.region_code', 'md.region_name', 'md.area_code', 'md.area_name', 'md.supervisor_code as siso_code')
            ->first();

        if (!$customer) {
            $this->dispatch('swal:error', [
                'title' => 'Gagal',
                'text' => 'Data toko tidak ditemukan.'
            ]);
            return;
        }

        $this->selectedCustomerForPlan = (array) $customer;

        $regionCode = $customer->region_code;
        $areaCode = $customer->area_code;
        $sisoCode = $customer->siso_code;

        // Dapatkan tim yang tersedia (Region, Area, dan Supervisor)
        $query = DB::table('team_elite_code_mappings as t')
            ->join('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code')
            ->where(function($query) use ($regionCode, $areaCode, $sisoCode) {
                $query->where(function($q) use ($regionCode) {
                    $q->where('t.level', 'region')->where('t.region_code', $regionCode);
                })
                ->orWhere(function($q) use ($areaCode) {
                    $q->where('t.level', 'area')->where('t.area_code', $areaCode);
                })
                ->orWhere(function($q) use ($sisoCode) {
                    $q->where('t.level', 'supervisor')->where('t.siso_code', $sisoCode);
                });
            });

        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $query->whereRaw("TRIM(t.team_elite_code) = TRIM(?)", [$user->supervisor_code]);
            } elseif (!empty($user->area_code) && count((array) $user->area_code) > 0) {
                $query->whereIn('t.area_code', (array) $user->area_code)
                      ->whereIn('t.level', ['area', 'supervisor']);
            } elseif (!empty($user->region_code) && count((array) $user->region_code) > 0) {
                $query->whereIn('t.region_code', (array) $user->region_code);
            }
        }

        $this->availableTeamCodes = $query->select('t.team_elite_code', 'f.SLSNAME as team_name', 't.level')
            ->distinct()
            ->orderBy('t.level')
            ->get()
            ->toArray();

        $this->addPlanTanggal = \Carbon\Carbon::now()->format('Y-m-d');
        $this->addPlanKodeTeam = '';
        $this->isAddPlanModalOpen = true;
    }

    public function closeAddPlanModal()
    {
        $this->isAddPlanModalOpen = false;
        $this->selectedCustomerForPlan = [];
        $this->availableTeamCodes = [];
        $this->resetValidation();
    }

    public function submitPlan()
    {
        $this->validate([
            'addPlanTanggal' => 'required|date',
            'addPlanKodeTeam' => 'required',
        ]);

        $teamName = '';
        foreach($this->availableTeamCodes as $team) {
            if ((string)$team->team_elite_code === (string)$this->addPlanKodeTeam) {
                $teamName = $team->team_name;
                break;
            }
        }

        // Cek duplikat
        $exists = DB::table('jks_team_elite')
            ->where('custno', $this->selectedCustomerForPlan['customer_code'])
            ->where('tanggal', $this->addPlanTanggal)
            ->where('kode_team', $this->addPlanKodeTeam)
            ->exists();

        if ($exists) {
            $this->dispatch('swal:error', [
                'title' => 'Gagal',
                'text' => 'Plan untuk toko dan tim ini di tanggal tersebut sudah ada.'
            ]);
            return;
        }

        DB::table('jks_team_elite')->insert([
            'tanggal' => $this->addPlanTanggal,
            'kode_team' => $this->addPlanKodeTeam,
            'nama_team' => $teamName,
            'kode_region' => $this->selectedCustomerForPlan['region_code'],
            'nama_region' => $this->selectedCustomerForPlan['region_name'],
            'kode_area' => $this->selectedCustomerForPlan['area_code'],
            'nama_area' => $this->selectedCustomerForPlan['area_name'],
            'distributor_code' => $this->selectedCustomerForPlan['distributor_code'],
            'distributor_name' => $this->selectedCustomerForPlan['distributor_name'],
            'custno' => $this->selectedCustomerForPlan['customer_code'],
            'custname' => $this->selectedCustomerForPlan['customer_name'],
            'addres' => $this->selectedCustomerForPlan['customer_address'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->isAddPlanModalOpen = false;
        
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Plan kunjungan berhasil ditambahkan.'
        ]);
        
        unset($this->kpiStats); // Force KPI reload
        $this->resetPage(); // Refresh data table
    }

    public function export()
    {
        $fileName = 'monitoring-customer-pareto-'.date('YmdHis').'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonitoringCustomerParetoExport($this->getBaseQuery()), 
            $fileName
        );
    }
}
