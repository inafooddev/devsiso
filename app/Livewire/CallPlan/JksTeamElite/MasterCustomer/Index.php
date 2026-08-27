<?php

namespace App\Livewire\CallPlan\JksTeamElite\MasterCustomer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use App\Exports\JksMasterCustomerExport;
use App\Exports\JksMasterCustomerTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ListTokoParetoTeamElite;
use App\Imports\JksMasterCustomerImport;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

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

    // State Export Customer
    public $isExportModalOpen = false;
    public $exportTotalRows = 0;

    // State Import Customer
    public $isImportModalOpen = false;
    public $importFile;
    public $importErrorLogUrl = null;
    public $importLogCount = 0;
    public $importMethod = 'upsert';
    
    // Live Progress Queue Properties
    public $importJobId = null;
    public $isImporting = false;
    public $importCompleted = false;
    public $liveSuccessCount = 0;
    public $liveSkipCount = 0;
    public $liveErrorCount = 0;
    
    public $importErrorLogs = [];
    public $importSkipLogs = [];

    // State Sync Wilayah
    public $isSyncModalOpen = false;
    public $syncJobId = null;
    public $isSyncing = false;
    public $syncCompleted = false;
    public $syncProcessed = 0;
    public $syncTotal = 0;
    public $syncUpdatedCount = 0;
    public $syncMessage = '';

    // Pencarian Distributor di Form Tambah Toko
    public $searchDistributor = '';

    // Properti Form Create/Edit
    public $distributor_code, $customer_code_prc, $customer_name, $uniq_kd, $customer_address;
    public $kabupaten, $kecamatan, $desa, $latitude, $longitude, $pilar, $target, $keterangan;
    public $channel_outlet, $classification_outlet, $segment_outlet;
    public $pilar_q1, $pilar_q2, $pilar_q3, $pilar_q4;
    
    // State Edit & Delete
    public $isEditModalOpen = false;
    public $isDeleteModalOpen = false;
    public $original_distributor_code;
    public $original_uniq_kd;
    public $delete_distributor_code;
    public $delete_uniq_kd;
    public $isDetailModalOpen = false;
    public $detailData = null;

    // State Map Modal


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
            $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
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
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            // Auto-set Region
            $regionQuery = DB::table('master_distributors')->select('region_code')->whereNotNull('region_code')->distinct();
            $regionQuery = $this->applyHierarchyAccess($regionQuery, 'distributor_code');
            $regions = $regionQuery->get();
            if ($regions->count() === 1) {
                $this->filterRegion = $regions->first()->region_code;
                
                // Auto-set Area
                $areaQuery = DB::table('master_distributors')->select('area_code')->whereNotNull('area_code')->where('region_code', $this->filterRegion)->distinct();
                $areaQuery = $this->applyHierarchyAccess($areaQuery, 'distributor_code');
                $areas = $areaQuery->get();
                if ($areas->count() === 1) {
                    $this->filterArea = $areas->first()->area_code;
                    
                    // Auto-set Supervisor
                    $spvQuery = DB::table('master_distributors as md')
                        ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                        ->select('t.team_elite_code as supervisor_code')
                        ->where('md.area_code', $this->filterArea)
                        ->distinct();
                    $spvQuery = $this->applyHierarchyAccess($spvQuery, 'md.distributor_code');
                    $spvs = $spvQuery->get();
                    if ($spvs->count() === 1) {
                        $this->filterSupervisor = $spvs->first()->supervisor_code;
                    }
                }
            }
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

    protected function getBaseQuery($excludePilarAndPareto = false, $excludeSearch = false)
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
                'l.kabupaten',
                'l.kecamatan',
                'l.desa',
                'l.latitude',
                'l.longitude',
                'l.pilar',
                'l.pilar_q1',
                'l.pilar_q2',
                'l.pilar_q3',
                'l.pilar_q4',
                'l.target',
                'l.keterangan',
                'l.channel_outlet',
                'l.classification_outlet',
                'l.segment_outlet',
                DB::raw("case when j.custno is null then 'N' else 'Y' end as on_plan"),
                DB::raw("case when l.pilar in ('1. RWO','2. PNR','3. NGVO') then 'PARETO' else 'NON PARETO' end as pareto")
            );

        // Hierarchy Access
        $query = $this->applyHierarchyAccess($query, 'l.distributor_code');

        // Search Filter
        if ($this->search && !$excludeSearch) {
            $query->where(function($q) {
                $q->where('l.customer_code_prc', 'ilike', "%{$this->search}%")
                  ->orWhere('l.uniq_kd', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_name', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_address', 'ilike', "%{$this->search}%")
                  ->orWhere('l.kabupaten', 'ilike', "%{$this->search}%")
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
        if ($this->filterSupervisor) {
            $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
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

        $data = $this->getBaseQuery()->paginate(100);

        // --- KPI Cards Calculation ---
        $kpiQuery = $this->getBaseQuery(true, true);
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
        $this->reset(['distributor_code', 'customer_code_prc', 'customer_name', 'uniq_kd', 'customer_address', 'kabupaten', 'kecamatan', 'desa', 'latitude', 'longitude', 'pilar', 'target', 'keterangan', 'searchDistributor', 'pilar_q1', 'pilar_q2', 'pilar_q3', 'pilar_q4', 'channel_outlet', 'classification_outlet', 'segment_outlet']);
        $this->isCreateModalOpen = true;
    }

    public function store()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'distributor_code' => 'required|string|max:15',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'required|string|max:255',
            'uniq_kd' => 'required|string|max:255',
            'pilar' => 'required|string|in:1. RWO,2. PNR,3. NGVO,4. GRO',
            'kabupaten' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'channel_outlet' => 'nullable|string|max:255',
            'classification_outlet' => 'nullable|string|max:255',
            'segment_outlet' => 'nullable|string|max:255',
        ]);

        // Security Check: Pastikan user berhak menambah data di distributor ini
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Akses ditolak: Anda tidak memiliki otoritas di distributor ini.');
            return;
        }

        // Validasi Duplikasi uniq_kd per distributor
        if (!empty($this->uniq_kd)) {
            $exists = DB::table('list_toko_pareto_team_elite')
                ->where('distributor_code', $this->distributor_code)
                ->where('uniq_kd', $this->uniq_kd)
                ->exists();
                
            if ($exists) {
                $this->addError('uniq_kd', "Customer dengan Uniq KD '{$this->uniq_kd}' sudah ada.");
                return;
            }
        }

        ListTokoParetoTeamElite::updateOrCreate(
            [
                'distributor_code' => $this->distributor_code,
                'uniq_kd' => $this->uniq_kd,
            ],
            [
                'customer_code_prc' => $this->customer_code_prc,
                'customer_name' => $this->customer_name,
                'customer_address' => $this->customer_address,
                'kabupaten' => $this->kabupaten,
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'pilar' => $this->pilar,
                'target' => $this->target ?? 0,
                'keterangan' => $this->keterangan,
                'pilar_q1' => $this->pilar_q1,
                'pilar_q2' => $this->pilar_q2,
                'pilar_q3' => $this->pilar_q3,
                'pilar_q4' => $this->pilar_q4,
                'channel_outlet' => $this->channel_outlet,
                'classification_outlet' => $this->classification_outlet,
                'segment_outlet' => $this->segment_outlet,
            ]
        );

        $this->isCreateModalOpen = false;
        \App\Helpers\ActivityLogger::log('Create Toko JKS Team Elite', "Menambahkan Toko Baru: {$this->customer_code_prc} - {$this->customer_name}");
        session()->flash('message', 'Customer berhasil ditambahkan.');
        $this->resetPage();
    }

    // --- EXPORT TO EXCEL ---
    public function openExportModal()
    {
        $this->authorizeAction('can_export');
        $this->exportTotalRows = $this->getBaseQuery()->count();
        $this->isExportModalOpen = true;
    }

    public function closeExportModal()
    {
        $this->isExportModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_export');
        
        \App\Helpers\ActivityLogger::log('Export Master Customer JKS', "Mengekspor data Master Customer JKS Team Elite sejumlah {$this->exportTotalRows} baris.");
        $filename = 'Master_Customer_JKS_Team_Elite_' . date('Ymd_His') . '.xlsx';
        
        // Tutup modal export
        $this->closeExportModal();

        return Excel::download(new JksMasterCustomerExport($this->getBaseQuery()), $filename);
    }

    public function openDetailModal($distributorCode, $uniqKd)
    {
        $this->detailData = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code')
            ->select(
                'md.region_name',
                'md.area_name',
                'f.SLSNAME as supervisor_name',
                'md.distributor_name',
                'l.*'
            )
            ->where('l.distributor_code', $distributorCode)
            ->where('l.uniq_kd', $uniqKd)
            ->first();

        if ($this->detailData) {
            $this->isDetailModalOpen = true;
        }
    }

    public function openEditModal($distributorCode, $uniqKd)
    {
        $this->authorizeAction('can_edit');
        $this->resetValidation();
        
        $record = ListTokoParetoTeamElite::where('distributor_code', $distributorCode)
            ->where('uniq_kd', $uniqKd)
            ->first();

        if ($record) {
            $this->original_distributor_code = $distributorCode;
            $this->original_uniq_kd = $uniqKd;
            
            $this->distributor_code = $record->distributor_code;
            $this->customer_code_prc = $record->customer_code_prc;
            $this->customer_name = $record->customer_name;
            $this->uniq_kd = $record->uniq_kd;
            $this->customer_address = $record->customer_address;
            $this->kabupaten = $record->kabupaten;
            $this->kecamatan = $record->kecamatan;
            $this->desa = $record->desa;
            $this->latitude = $record->latitude;
            $this->longitude = $record->longitude;
            $this->pilar = $record->pilar;
            $this->target = $record->target;
            $this->keterangan = $record->keterangan;
            $this->pilar_q1 = $record->pilar_q1;
            $this->pilar_q2 = $record->pilar_q2;
            $this->pilar_q3 = $record->pilar_q3;
            $this->pilar_q4 = $record->pilar_q4;
            $this->channel_outlet = $record->channel_outlet;
            $this->classification_outlet = $record->classification_outlet;
            $this->segment_outlet = $record->segment_outlet;
            
            $this->searchDistributor = $record->distributor_code;
            $this->isEditModalOpen = true;
        }
    }

    public function update()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'distributor_code' => 'required|string|max:15|exists:master_distributors,distributor_code',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'required|string|max:255',
            'uniq_kd' => 'required|string|max:255',
            'pilar' => 'required|string|in:1. RWO,2. PNR,3. NGVO,4. GRO',
            'kabupaten' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'channel_outlet' => 'nullable|string|max:255',
            'classification_outlet' => 'nullable|string|max:255',
            'segment_outlet' => 'nullable|string|max:255',
        ], [
            'distributor_code.required' => 'Distributor Code wajib diisi',
            'distributor_code.exists' => 'Distributor Code tidak valid',
            'customer_code_prc.required' => 'Customer Code wajib diisi',
            'customer_name.required' => 'Nama Customer wajib diisi',
            'uniq_kd.required' => 'Uniq KD wajib diisi',
            'pilar.required' => 'Pilar wajib diisi',
            'target.required' => 'Target wajib diisi',
        ]);

        // Cek duplikasi uniq_kd jika diubah
        if ($this->distributor_code !== $this->original_distributor_code || $this->uniq_kd !== $this->original_uniq_kd) {
            $exists = ListTokoParetoTeamElite::where('distributor_code', $this->distributor_code)
                ->where('uniq_kd', $this->uniq_kd)
                ->exists();
            
            if ($exists) {
                $this->addError('uniq_kd', "Uniq KD '{$this->uniq_kd}' sudah digunakan di distributor tersebut.");
                return;
            }
        }

        $record = ListTokoParetoTeamElite::where('distributor_code', $this->original_distributor_code)
            ->where('uniq_kd', $this->original_uniq_kd)
            ->first();

        if ($record) {
            $record->update([
                'distributor_code' => $this->distributor_code,
                'customer_code_prc' => $this->customer_code_prc,
                'customer_name' => $this->customer_name,
                'uniq_kd' => $this->uniq_kd,
                'customer_address' => $this->customer_address,
                'kabupaten' => $this->kabupaten,
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'pilar' => $this->pilar,
                'target' => $this->target ?? 0,
                'keterangan' => $this->keterangan,
                'pilar_q1' => $this->pilar_q1,
                'pilar_q2' => $this->pilar_q2,
                'pilar_q3' => $this->pilar_q3,
                'pilar_q4' => $this->pilar_q4,
                'channel_outlet' => $this->channel_outlet,
                'classification_outlet' => $this->classification_outlet,
                'segment_outlet' => $this->segment_outlet,
            ]);

            \App\Helpers\ActivityLogger::log('Edit Master Customer JKS', "Mengedit Customer: {$this->customer_name} ({$this->uniq_kd})");
            session()->flash('message', 'Data customer berhasil diperbarui.');
            $this->isEditModalOpen = false;
        }
    }

    public function confirmDelete($distributorCode, $uniqKd)
    {
        $this->authorizeAction('can_edit');
        $this->delete_distributor_code = $distributorCode;
        $this->delete_uniq_kd = $uniqKd;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_edit');
        
        $record = ListTokoParetoTeamElite::where('distributor_code', $this->delete_distributor_code)
            ->where('uniq_kd', $this->delete_uniq_kd)
            ->first();

        if ($record) {
            $name = $record->customer_name;
            $record->delete();
            \App\Helpers\ActivityLogger::log('Delete Master Customer JKS', "Menghapus Customer: {$name} ({$this->delete_uniq_kd})");
            session()->flash('message', 'Data customer berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
        $this->resetPage();
    }

    // --- IMPORT DARI EXCEL ---
    public function downloadTemplate()
    {
        $filename = 'Template_Import_Master_Customer_JKS_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new \App\Exports\JksMasterCustomerTemplateExport(), $filename);
    }

    public function openImportModal()
    {
        $this->authorizeAction('can_import');
        $this->resetErrorBag();
        $this->importFile = null;
        $this->isImporting = false;
        $this->importCompleted = false;
        $this->importMethod = 'upsert';
        $this->importErrorLogUrl = null;
        $this->importErrorLogs = [];
        $this->importSkipLogs = [];
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->reset(['importFile', 'importErrorLogUrl', 'importLogCount', 'importMethod', 'importJobId', 'isImporting', 'importCompleted', 'liveSuccessCount', 'liveSkipCount', 'liveErrorCount']);
        $this->isImportModalOpen = false;
    }

    public function import()
    {
        \Illuminate\Support\Facades\Log::info("Import method triggered");
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'importFile' => 'required|file|max:5120', // Maksimal 5MB, dihapus mimes karena Livewire upload sering gagal membaca mime xlsx
            'importMethod' => 'required|in:upsert,insert_only',
        ]);

        \Illuminate\Support\Facades\Log::info("File validated successfully");

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $allowedDistributors = [];

        if (!$isAdmin) {
            $q = DB::table('master_distributors')->select('distributor_code');
            $q = $this->applyHierarchyAccess($q, 'distributor_code');
            $allowedDistributors = $q->pluck('distributor_code')->toArray();
        }

        try {
            $this->importJobId = (string) \Illuminate\Support\Str::uuid();
            $filename = 'import_jks_' . $this->importJobId . '.' . $this->importFile->getClientOriginalExtension();
            // Simpan file sementara agar bisa dibaca worker
            $this->importFile->storeAs('temp_imports', $filename, 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path('temp_imports/' . $filename);

            \Illuminate\Support\Facades\Cache::put("import_progress_{$this->importJobId}", [
                'status' => 'processing',
                'success' => 0,
                'insert' => 0,
                'update' => 0,
                'skip' => 0,
                'error' => 0,
                'logs' => [],
                'skipLogs' => []
            ], 3600);

            $this->isImporting = true;
            $this->importCompleted = false;

            $queueConnection = env('IMPORT_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync'));
            \Illuminate\Support\Facades\Log::info("Dispatching JksMasterCustomerImportJob with ID {$this->importJobId} to connection {$queueConnection} on queue 'imports'");
            
            $job = new \App\Jobs\JksMasterCustomerImportJob($fullPath, $isAdmin, $allowedDistributors, $this->importMethod, $this->importJobId);
            dispatch($job)->onConnection($queueConnection)->onQueue('imports');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to dispatch import: " . $e->getMessage());
            $this->addError('importFile', 'Gagal memproses file: ' . $e->getMessage());
            $this->isImporting = false;
        }

        $this->resetPage();
    }

    public function checkImportProgress()
    {
        if (!$this->isImporting || !$this->importJobId) return;

        $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importJobId}");

        if ($progress) {
            $this->liveSuccessCount = $progress['success'] ?? 0;
            $this->liveSkipCount = $progress['skip'] ?? 0;
            $this->liveErrorCount = $progress['error'] ?? 0;

            if (isset($progress['status']) && in_array($progress['status'], ['completed', 'failed'])) {
                $this->isImporting = false;
                $this->importCompleted = true;
                
                $this->importLogCount = count($progress['logs'] ?? []);
                $this->importErrorLogs = $progress['logs'] ?? [];
                $this->importSkipLogs = $progress['skipLogs'] ?? [];
                
                // Generate Log File
                $logContent = "Log Hasil Import Master Customer JKS Team Elite\n";
                $logContent .= "Tanggal: " . now()->format('Y-m-d H:i:s') . "\n";
                $logContent .= "Metode Import: " . ($this->importMethod == 'upsert' ? 'Update & Insert' : 'Insert Only') . "\n";
                $logContent .= "Status: " . strtoupper($progress['status']) . "\n";
                $logContent .= "Total Sukses: {$this->liveSuccessCount} (Insert: {$progress['insert']}, Update: {$progress['update']})\n";
                $logContent .= "Total Dilewati: {$this->liveSkipCount}\n";
                $logContent .= "Total Gagal: {$this->importLogCount}\n";
                $logContent .= "---------------------------------------------------\n\n";

                if ($this->liveSkipCount > 0 && !empty($progress['skipLogs'])) {
                    $logContent .= "Rincian Dilewati (Data Sudah Ada):\n";
                    foreach ($progress['skipLogs'] as $log) {
                        $logContent .= $log . "\n";
                    }
                    $logContent .= "\n---------------------------------------------------\n\n";
                }

                if ($this->importLogCount > 0 && !empty($progress['logs'])) {
                    $logContent .= "Rincian Gagal:\n";
                    foreach ($progress['logs'] as $error) {
                        $logContent .= $error . "\n";
                    }
                    $logContent .= "\n---------------------------------------------------\n\n";
                }

                $filename = 'Error_Log_Import_Master_Customer_JKS_' . date('Ymd_His') . '.txt';
                \Illuminate\Support\Facades\Storage::disk('public')->put('exports/' . $filename, $logContent);
                $this->importErrorLogUrl = \Illuminate\Support\Facades\Storage::url('exports/' . $filename);
                
                \Illuminate\Support\Facades\Cache::forget("import_progress_{$this->importJobId}");
                session()->flash('message', 'Import selesai diproses.');
            }
        }
    }
    // --- SYNC WILAYAH DENGAN POSTGIS ---
    public function openSyncModal()
    {
        $this->authorizeAction('can_edit');
        $this->isSyncModalOpen = true;
        $this->isSyncing = false;
        $this->syncCompleted = false;
        $this->syncProcessed = 0;
        $this->syncTotal = 0;
        $this->syncJobId = null;
        $this->syncMessage = '';
    }

    public function closeSyncModal()
    {
        $this->isSyncModalOpen = false;
    }

    public function startSync()
    {
        $this->authorizeAction('can_edit');
        
        $this->syncJobId = (string) \Illuminate\Support\Str::uuid();
        $this->isSyncing = true;
        $this->syncCompleted = false;
        
        try {
            $queueConnection = env('IMPORT_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync'));
            \Illuminate\Support\Facades\Log::info("Dispatching JksSyncWilayahJob with ID {$this->syncJobId}");
            
            $job = new \App\Jobs\JksSyncWilayahJob($this->syncJobId);
            dispatch($job)->onConnection($queueConnection)->onQueue('imports');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to dispatch sync job: " . $e->getMessage());
            $this->isSyncing = false;
            $this->syncMessage = 'Gagal memulai sinkronisasi: ' . $e->getMessage();
        }
    }

    public function checkSyncProgress()
    {
        if (!$this->isSyncing || !$this->syncJobId) return;

        $progress = \Illuminate\Support\Facades\Cache::get("sync_wilayah_progress_{$this->syncJobId}");

        if ($progress) {
            $this->syncMessage = $progress['message'] ?? 'Memproses...';
            $this->syncProcessed = $progress['processed'] ?? 0;
            $this->syncTotal = $progress['total'] ?? 0;
            $this->syncUpdatedCount = $progress['updated'] ?? 0;

            if (isset($progress['status']) && in_array($progress['status'], ['completed', 'error'])) {
                $this->isSyncing = false;
                $this->syncCompleted = true;
                \Illuminate\Support\Facades\Cache::forget("sync_wilayah_progress_{$this->syncJobId}");
                $this->resetPage(); // Refresh table
            }
        }
    }
}

