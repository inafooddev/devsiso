<?php

namespace App\Livewire\CallPlan\JksTeamElite\MasterCustomer;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use App\Exports\JksMasterCustomerExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ListTokoParetoTeamElite;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected string $menuRoute = 'call-plan.jks-team-elite.master-customer';
    protected $paginationTheme = 'tailwind';

    // Pencarian & Filter
    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterSupervisor = '';
    public $filterDistributor = '';
    public $filterPareto = ''; // Tambahan filter Pareto/Non Pareto
    public $filterPilar = ''; // Tambahan filter Pilar

    // Sorting
    public $sortColumn = 'md.region_name';
    public $sortDirection = 'asc';

    // State Modal Filter
    public $isFilterModalOpen = false;

    // State Modal Tambah Customer
    public $isCreateModalOpen = false;

    // Pencarian Distributor di Form Tambah Toko
    public $searchDistributor = '';

    // Properti Form Create
    public $distributor_code, $customer_code_prc, $customer_name, $uniq_kd, $customer_address;
    public $kecamatan, $desa, $latitude, $longitude, $pilar, $target, $keterangan;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterRegion' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterSupervisor' => ['except' => ''],
        'filterDistributor' => ['except' => ''],
        'filterPareto' => ['except' => ''],
        'filterPilar' => ['except' => ''],
        'sortColumn' => ['except' => 'md.region_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region, area, dan supervisor user.
     */
    private function applyHierarchyAccess($query, $distributorCodeColumn = 'l.distributor_code')
    {
        $user = auth()->user();

        // Admin atau tidak ada batasan → tampil semua
        if ($user->hasRole('admin')) {
            return $query;
        }

        // Level Supervisor
        if (!empty($user->supervisor_code)) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md_auth')
                    ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                    ->where('md_auth.supervisor_code', $user->supervisor_code);
            });
        }

        // Level Area
        if (!empty($user->area_code) && count((array) $user->area_code) > 0) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md_auth')
                    ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                    ->whereIn('md_auth.area_code', (array) $user->area_code);
            });
        }

        // Level Region
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

    public function mount()
    {
        $query = DB::table('master_distributors')->select('region_code')->whereNotNull('region_code')->distinct();
        $query = $this->applyHierarchyAccess($query, 'distributor_code');
        
        $regions = $query->get();

        if (!auth()->user()->hasRole('admin') && $regions->count() === 1) {
            $this->filterRegion = $regions->first()->region_code;
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterRegion() { $this->reset(['filterArea', 'filterSupervisor', 'filterDistributor']); $this->resetPage(); }
    public function updatingFilterArea() { $this->reset(['filterSupervisor', 'filterDistributor']); $this->resetPage(); }
    public function updatingFilterSupervisor() { $this->reset('filterDistributor'); $this->resetPage(); }
    public function updatingFilterDistributor() { $this->resetPage(); }
    public function updatingFilterPareto() { $this->resetPage(); }
    public function updatingFilterPilar() { $this->resetPage(); }

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

    protected function getBaseQuery($excludePilarAndPareto = false)
    {
        $query = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code')
            ->leftJoin(DB::raw('(
                select distributor_code, custno
                from (
                    select 
                        distributor_code,
                        custno,
                        row_number() over (
                            partition by distributor_code, custno
                            order by custno
                        ) as rn
                    from jks_team_elite
                ) x
                where rn = 1
            ) as j'), function($join) {
                $join->on('l.distributor_code', '=', 'j.distributor_code')
                     ->on('l.customer_code_prc', '=', 'j.custno');
            })
            ->select(
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                't.team_elite_code as supervisor_code',
                'f.SLSNAME as supervisor_name',
                'l.distributor_code',
                'md.distributor_name',
                'l.customer_code_prc as customer_code',
                'l.uniq_kd',
                'l.customer_name',
                'l.customer_address',
                'l.kecamatan',
                'l.desa',
                'l.latitude',
                'l.longitude',
                'l.pilar',
                'l.target',
                'l.keterangan',
                DB::raw("case when j.custno is null then 'N' else 'Y' end as on_plan"),
                DB::raw("case when l.pilar in ('1. RWO','2. PNR','3. NGVO') then 'PARETO' else 'NON PARETO' end as pareto")
            );

        // Hierarchy Access
        $query = $this->applyHierarchyAccess($query, 'l.distributor_code');

        // Search Filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('l.customer_code_prc', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_name', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_address', 'ilike', "%{$this->search}%")
                  ->orWhere('l.kecamatan', 'ilike', "%{$this->search}%")
                  ->orWhere('l.desa', 'ilike', "%{$this->search}%")
                  ->orWhere('l.pilar', 'ilike', "%{$this->search}%")
                  ->orWhere('f.SLSNAME', 'ilike', "%{$this->search}%")
                  ->orWhere('md.distributor_name', 'ilike', "%{$this->search}%");
            });
        }

        // Dropdown Filters
        if ($this->filterRegion) $query->where('md.region_code', $this->filterRegion);
        if ($this->filterArea) $query->where('md.area_code', $this->filterArea);
        if ($this->filterSupervisor) $query->where('t.team_elite_code', $this->filterSupervisor);
        if ($this->filterDistributor) $query->where('l.distributor_code', $this->filterDistributor);
        
        if (!$excludePilarAndPareto) {
            if ($this->filterPilar) $query->where('l.pilar', $this->filterPilar);
            
            if ($this->filterPareto) {
                if ($this->filterPareto === 'PARETO') {
                    $query->whereIn('l.pilar', ['1. RWO', '2. PNR', '3. NGVO']);
                } else {
                    $query->whereNotIn('l.pilar', ['1. RWO', '2. PNR', '3. NGVO'])
                          ->orWhereNull('l.pilar');
                }
            }
        }

        // Sorting
        if ($this->sortColumn) {
            // Handle virtual columns / raw queries sorting appropriately if needed
            if ($this->sortColumn === 'on_plan') {
                $query->orderBy(DB::raw("case when j.custno is null then 'N' else 'Y' end"), $this->sortDirection);
            } elseif ($this->sortColumn === 'pareto') {
                $query->orderBy(DB::raw("case when l.pilar in ('1. RWO','2. PNR','3. NGVO') then 'PARETO' else 'NON PARETO' end"), $this->sortDirection);
            } else {
                $query->orderBy($this->sortColumn, $this->sortDirection);
            }
        } else {
            $query->orderBy('md.region_name')
                  ->orderBy('md.area_name')
                  ->orderBy('md.distributor_name')
                  ->orderBy('f.SLSNAME')
                  ->orderBy('l.customer_name');
        }

        return $query;
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

        $data = $this->getBaseQuery()->paginate(15);

        // --- KPI Cards Calculation ---
        $kpiQuery = $this->getBaseQuery(true);
        $kpiQuery->orders = null; // Reset ordering for subquery execution
        
        $kpi = DB::table(DB::raw("({$kpiQuery->toSql()}) as sub"))
            ->mergeBindings($kpiQuery)
            ->selectRaw("
                COUNT(customer_code) as total_customer,
                SUM(CASE WHEN pareto = 'PARETO' THEN 1 ELSE 0 END) as total_pareto,
                SUM(CASE WHEN pilar = '1. RWO' THEN 1 ELSE 0 END) as total_rwo,
                SUM(CASE WHEN pilar = '2. PNR' THEN 1 ELSE 0 END) as total_pnr,
                SUM(CASE WHEN pilar = '3. NGVO' THEN 1 ELSE 0 END) as total_ngvo,
                SUM(CASE WHEN pilar = '4. GRO' THEN 1 ELSE 0 END) as total_gro
            ")->first();

        // --- Query Distributor Aktif untuk Form Tambah Toko ---
        $distributorsQuery = DB::table('master_distributors')
            ->select('distributor_code', 'distributor_name')
            ->where('is_active', true);
        $distributorsQuery = $this->applyHierarchyAccess($distributorsQuery, 'distributor_code');
        
        if (!empty($this->searchDistributor)) {
            $distributorsQuery->where(function($q) {
                $q->where('distributor_code', 'ilike', "%{$this->searchDistributor}%")
                  ->orWhere('distributor_name', 'ilike', "%{$this->searchDistributor}%");
            });
        }
        
        $createDistributors = $distributorsQuery->orderBy('distributor_name')->get();

        return view('livewire.call-plan.jks-team-elite.master-customer.index', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
            'distributors' => $distributors,
            'createDistributors' => $createDistributors,
            'kpi' => $kpi,
        ])->layout('layouts.app');
    }

    // --- FILTER MODAL ACTIONS ---
    public function openFilterModal() { $this->isFilterModalOpen = true; }
    public function closeFilterModal() { $this->isFilterModalOpen = false; }
    public function applyFilter() { $this->isFilterModalOpen = false; $this->resetPage(); }
    public function resetFilter() { 
        $this->reset(['filterRegion', 'filterArea', 'filterSupervisor', 'filterDistributor', 'filterPareto', 'filterPilar']); 
        $this->isFilterModalOpen = false; 
        
        // Kembalikan auto-select region setelah reset jika user non-admin hanya 1 region
        $this->mount();
        $this->resetPage(); 
    }

    // --- FITUR TAMBAH CUSTOMER BARU ---
    /**
     * Helper Keamanan: Memastikan kode distributor yang dimanipulasi valid dengan hak akses.
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = DB::table('master_distributors')->where('distributor_code', $distributorCode);
        $query = $this->applyHierarchyAccess($query, 'distributor_code');
        return $query->exists();
    }

    public function openCreateModal()
    {
        $this->reset(['distributor_code', 'customer_code_prc', 'customer_name', 'uniq_kd', 'customer_address', 'kecamatan', 'desa', 'latitude', 'longitude', 'pilar', 'target', 'keterangan', 'searchDistributor']);
        $this->isCreateModalOpen = true;
    }

    public function store()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'distributor_code' => 'required|string|max:15',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'uniq_kd' => 'nullable|string|max:255',
            'pilar' => 'nullable|string|in:1. RWO,2. PNR,3. NGVO,4. GRO',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'nullable|numeric',
            'keterangan' => 'nullable|string',
        ]);

        // Security Check: Pastikan user berhak menambah data di distributor ini
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Akses ditolak: Anda tidak memiliki otoritas di distributor ini.');
            return;
        }

        ListTokoParetoTeamElite::updateOrCreate(
            [
                'distributor_code' => $this->distributor_code,
                'customer_code_prc' => $this->customer_code_prc,
            ],
            [
                'customer_name' => $this->customer_name,
                'uniq_kd' => $this->uniq_kd,
                'customer_address' => $this->customer_address,
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'pilar' => $this->pilar,
                'target' => $this->target ?? 0,
                'keterangan' => $this->keterangan,
            ]
        );

        $this->isCreateModalOpen = false;
        \App\Helpers\ActivityLogger::log('Create Toko JKS Team Elite', "Menambahkan Toko Baru: {$this->customer_code_prc} - {$this->customer_name}");
        session()->flash('message', 'Customer berhasil ditambahkan.');
        $this->resetPage();
    }

    // --- EXPORT TO EXCEL ---
    public function export()
    {
        $this->authorizeAction('can_export');
        
        \App\Helpers\ActivityLogger::log('Export Master Customer JKS', "Mengekspor data Master Customer JKS Team Elite.");
        return Excel::download(new JksMasterCustomerExport($this->getBaseQuery()), 'Master_Customer_JKS_Team_Elite.xlsx');
    }
}
