<?php

namespace App\Livewire\CallPlan\JksTeamElite\MonitoringOutletPareto;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use App\Exports\MonitoringOutletParetoExport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    #[Title('Monitoring Outlet Pareto')]

    public $startDate;
    public $endDate;
    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterSupervisor = '';
    public $filterPilar = '';
    public $filterStatusVisit = '';
    public $activeTab = 'summary'; // Tab state: 'summary' or 'detail'
    public $isFilterModalOpen = false;

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'search' => ['except' => ''],
        'filterRegion' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterSupervisor' => ['except' => ''],
        'filterPilar' => ['except' => ''],
        'filterStatusVisit' => ['except' => ''],
        'activeTab' => ['except' => 'summary'],
    ];

    public function mount()
    {
        // Default date range to active month
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');

        // Auto-select region if user only has 1 region
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regions = DB::table('master_regions')
                ->select('region_code')
                ->whereIn('region_code', (array) $user->region_code)
                ->get();
            if ($regions->count() === 1) {
                $this->filterRegion = $regions->first()->region_code;
            }
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingFilterPilar() { $this->resetPage(); }
    public function updatingFilterStatusVisit() { $this->resetPage(); }
    public function updatedActiveTab() { $this->resetPage(); }

    public function updatingFilterRegion()
    {
        $this->reset(['filterArea', 'filterSupervisor']);
        $this->resetPage();
    }

    public function updatingFilterArea()
    {
        $this->reset('filterSupervisor');
        $this->resetPage();
    }

    public function updatingFilterSupervisor()
    {
        $this->resetPage();
    }

    public function openFilterModal()
    {
        $this->isFilterModalOpen = true;
    }

    public function closeFilterModal()
    {
        $this->isFilterModalOpen = false;
    }

    public function applyFilter()
    {
        $this->isFilterModalOpen = false;
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['filterRegion', 'filterArea', 'filterSupervisor', 'filterPilar', 'filterStatusVisit']);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regions = DB::table('master_regions')
                ->select('region_code')
                ->whereIn('region_code', (array) $user->region_code)
                ->get();
            if ($regions->count() === 1) {
                $this->filterRegion = $regions->first()->region_code;
            }
        }

        $this->isFilterModalOpen = false;
        $this->resetPage();
    }

    private function buildQueryAndBindings()
    {
        $user = auth()->user();
        $bindings = [];
        $bindings[] = $this->startDate ?: date('Y-m-01');
        $bindings[] = $this->endDate ?: date('Y-m-t');

        $whereClauses = [];

        // Permissions check
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionCodes = (array) $user->region_code;
            $placeholders = implode(',', array_fill(0, count($regionCodes), '?'));
            $whereClauses[] = "md.region_code IN ($placeholders)";
            foreach ($regionCodes as $code) {
                $bindings[] = $code;
            }
        }

        // Region Filter
        if ($this->filterRegion) {
            $whereClauses[] = "md.region_code = ?";
            $bindings[] = $this->filterRegion;
        }

        // Area Filter
        if ($this->filterArea) {
            $whereClauses[] = "md.area_code = ?";
            $bindings[] = $this->filterArea;
        }

        // Supervisor Filter
        if ($this->filterSupervisor) {
            $whereClauses[] = "t.team_elite_code = ?";
            $bindings[] = $this->filterSupervisor;
        }

        // Search Filter
        if ($this->search) {
            $whereClauses[] = "(l.customer_code_prc ILIKE ? OR l.customer_name ILIKE ? OR md.distributor_name ILIKE ? OR f.\"SLSNAME\" ILIKE ?)";
            $searchTerm = '%' . $this->search . '%';
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }

        // Pilar Filter
        if ($this->filterPilar) {
            $whereClauses[] = "l.pilar = ?";
            $bindings[] = $this->filterPilar;
        }

        $whereSql = '';
        if (count($whereClauses) > 0) {
            $whereSql = ' AND ' . implode(' AND ', $whereClauses);
        }

        // Having clauses for status visit filter (applied on aggregated visit levels)
        $havingClauses = [];
        if ($this->filterStatusVisit) {
            $havingClauses[] = "(
                CASE
                    WHEN COUNT(CASE WHEN v.level = 'region' THEN 1 END) > 0
                      OR COUNT(CASE WHEN v.level = 'area' THEN 1 END) > 0
                      OR COUNT(CASE WHEN v.level = 'supervisor' THEN 1 END) > 0
                    THEN 'Y'
                    ELSE 'N'
                END
            ) = ?";
            $bindings[] = $this->filterStatusVisit;
        }

        $havingSql = '';
        if (count($havingClauses) > 0) {
            $havingSql = ' HAVING ' . implode(' AND ', $havingClauses);
        }

        $rawSql = "
            WITH visit_data AS (
                SELECT
                    z.custno,
                    LOWER(z.\"level\") AS level
                FROM zv_summary_visit_team_elite z
                WHERE z.status_visit = 'Y'
                  AND z.tanggal BETWEEN ? and ?
            )

            SELECT
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                t.team_elite_code AS supervisor_code,
                UPPER(f.\"SLSNAME\") AS supervisor_name,
                l.distributor_code,
                md.distributor_name,
                l.customer_code_prc AS customer_code,
                l.uniq_kd,
                l.customer_name,
                l.pilar,

                CASE
                    WHEN COUNT(CASE WHEN v.level = 'region' THEN 1 END) > 0
                    THEN 1
                    ELSE 0
                END AS visit_region,

                CASE
                    WHEN COUNT(CASE WHEN v.level = 'area' THEN 1 END) > 0
                    THEN 1
                    ELSE 0
                END AS visit_area,

                CASE
                    WHEN COUNT(CASE WHEN v.level = 'supervisor' THEN 1 END) > 0
                    THEN 1
                    ELSE 0
                END AS visit_supervisor,

                CASE
                    WHEN COUNT(CASE WHEN v.level = 'region' THEN 1 END) > 0
                      OR COUNT(CASE WHEN v.level = 'area' THEN 1 END) > 0
                      OR COUNT(CASE WHEN v.level = 'supervisor' THEN 1 END) > 0
                    THEN 'Y'
                    ELSE 'N'
                END AS status_visit

            FROM list_toko_pareto_team_elite l

            LEFT JOIN master_distributors md
                ON l.distributor_code = md.distributor_code

            LEFT JOIN team_elite_code_mappings t
                ON t.siso_code = md.supervisor_code

            LEFT JOIN fsalesman f
                ON t.team_elite_code = f.\"SLSNO\"

            LEFT JOIN visit_data v
                ON l.customer_code_prc = v.custno

            WHERE l.pilar IN ('1. RWO','2. PNR','3. NGVO')
              {$whereSql}

            GROUP BY
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                t.team_elite_code,
                f.\"SLSNAME\",
                l.distributor_code,
                md.distributor_name,
                l.customer_code_prc,
                l.uniq_kd,
                l.customer_name,
                l.pilar
            {$havingSql}
        ";

        return [$rawSql, $bindings];
    }

    public function export()
    {
        list($rawSql, $bindings) = $this->buildQueryAndBindings();
        
        if ($this->activeTab === 'summary') {
            $finalSql = "
                SELECT
                    region_code,
                    region_name,
                    area_code,
                    area_name,
                    supervisor_code,
                    supervisor_name,
                    distributor_code,
                    distributor_name,
                    pilar,
                    COUNT(*) AS total_outlets,
                    SUM(CASE WHEN status_visit = 'Y' THEN 1 ELSE 0 END) AS visited_outlets,
                    SUM(visit_region) AS visit_region,
                    SUM(visit_area) AS visit_area,
                    SUM(visit_supervisor) AS visit_supervisor
                FROM (
                    {$rawSql}
                ) as sub
                GROUP BY
                    region_code,
                    region_name,
                    area_code,
                    area_name,
                    supervisor_code,
                    supervisor_name,
                    distributor_code,
                    distributor_name,
                    pilar
            ";
            $orderBySql = " ORDER BY region_name ASC, area_name ASC, supervisor_name ASC, distributor_name ASC, pilar ASC";
        } else {
            $finalSql = $rawSql;
            $orderBySql = " ORDER BY md.region_name ASC, md.area_name ASC, supervisor_name ASC, l.customer_name ASC";
        }

        $finalQuerySql = $finalSql . $orderBySql;
        $items = DB::select($finalQuerySql, $bindings);

        \App\Helpers\ActivityLogger::log('Export Monitoring Pareto', "Mengekspor data Monitoring Outlet Pareto ({$this->activeTab}) dari {$this->startDate} s/d {$this->endDate}.");

        return Excel::download(
            new MonitoringOutletParetoExport($items, $this->activeTab),
            'Monitoring_Outlet_Pareto_' . $this->activeTab . '_' . $this->startDate . '_to_' . $this->endDate . '.xlsx'
        );
    }

    public function render()
    {
        $user = auth()->user();

        // 1. Regions
        $regionQuery = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $regions = $regionQuery->orderBy('region_name')->get();

        // 2. Areas
        $areas = [];
        if ($this->filterRegion) {
            $areaQuery = DB::table('master_distributors')
                ->select('area_code', 'area_name')
                ->where('region_code', $this->filterRegion)
                ->whereNotNull('area_code')
                ->distinct();
            if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
                $areaQuery->whereIn('region_code', (array) $user->region_code);
            }
            $areas = $areaQuery->orderBy('area_name')->get();
        }

        // 3. Supervisors
        $supervisors = [];
        if ($this->filterArea) {
            $spvQuery = DB::table('master_distributors as md')
                ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                ->join('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
                ->select('t.team_elite_code as supervisor_code', 'f.SLSNAME as supervisor_name')
                ->where('md.area_code', $this->filterArea)
                ->distinct();
            if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
                $spvQuery->whereIn('md.region_code', (array) $user->region_code);
            }
            $supervisors = $spvQuery->orderBy('f.SLSNAME')->get();
        }

        list($rawSql, $bindings) = $this->buildQueryAndBindings();

        // KPI calculation (always based on rawSql)
        $kpiBindings = $bindings;
        $kpiSql = "
            SELECT
                COUNT(*) as total_outlets,
                SUM(CASE WHEN status_visit = 'Y' THEN 1 ELSE 0 END) as visited_outlets,
                SUM(CASE WHEN pilar = '1. RWO' THEN 1 ELSE 0 END) as total_rwo,
                SUM(CASE WHEN pilar = '1. RWO' AND status_visit = 'Y' THEN 1 ELSE 0 END) as visited_rwo,
                SUM(CASE WHEN pilar = '2. PNR' THEN 1 ELSE 0 END) as total_pnr,
                SUM(CASE WHEN pilar = '2. PNR' AND status_visit = 'Y' THEN 1 ELSE 0 END) as visited_pnr,
                SUM(CASE WHEN pilar = '3. NGVO' THEN 1 ELSE 0 END) as total_ngvo,
                SUM(CASE WHEN pilar = '3. NGVO' AND status_visit = 'Y' THEN 1 ELSE 0 END) as visited_ngvo,
                SUM(visit_region) as total_visit_region,
                SUM(visit_area) as total_visit_area,
                SUM(visit_supervisor) as total_visit_supervisor
            FROM (
                {$rawSql}
            ) as sub
        ";
        $kpi = DB::selectOne($kpiSql, $kpiBindings);

        // Adjust final query for selected tab (Summary vs Detail)
        if ($this->activeTab === 'summary') {
            $finalSql = "
                SELECT
                    region_code,
                    region_name,
                    area_code,
                    area_name,
                    supervisor_code,
                    supervisor_name,
                    distributor_code,
                    distributor_name,
                    pilar,
                    COUNT(*) AS total_outlets,
                    SUM(CASE WHEN status_visit = 'Y' THEN 1 ELSE 0 END) AS visited_outlets,
                    SUM(visit_region) AS visit_region,
                    SUM(visit_area) AS visit_area,
                    SUM(visit_supervisor) AS visit_supervisor
                FROM (
                    {$rawSql}
                ) as sub
                GROUP BY
                    region_code,
                    region_name,
                    area_code,
                    area_name,
                    supervisor_code,
                    supervisor_name,
                    distributor_code,
                    distributor_name,
                    pilar
            ";
            $orderBySql = " ORDER BY region_name ASC, area_name ASC, supervisor_name ASC, distributor_name ASC, pilar ASC";
            
            // Execute all summary rows directly without pagination
            $items = DB::select($finalSql . $orderBySql, $bindings);
            $data = collect($items);
        } else {
            $finalSql = $rawSql;
            $orderBySql = " ORDER BY md.region_name ASC, md.area_name ASC, supervisor_name ASC, l.customer_name ASC";
            
            $countSql = "SELECT COUNT(*) as total FROM ({$finalSql}) as total_sub";
            $totalItems = DB::selectOne($countSql, $bindings)->total;

            $perPage = 100;
            $page = $this->getPage();

            $paginatedSql = $finalSql . $orderBySql . " LIMIT ? OFFSET ?";
            $bindings[] = $perPage;
            $bindings[] = ($page - 1) * $perPage;

            $items = DB::select($paginatedSql, $bindings);

            $data = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $totalItems,
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        }

        return view('livewire.call-plan.jks-team-elite.monitoring-outlet-pareto.index', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
            'kpi' => $kpi
        ])->layout('layouts.app');
    }
}
