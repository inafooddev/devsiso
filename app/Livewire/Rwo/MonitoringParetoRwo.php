<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use App\Exports\MonitoringOutletParetoExport;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringParetoRwo extends Component
{
    use WithPagination;

    #[Title('Monitoring Visit RWO')]

    public $startDate;
    public $endDate;
    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterSupervisor = '';
    public $filterStatusVisit = '';
    public $filterJenisReward = ''; // '', 'CB', 'BB'
    public $filterRewardPercent = ''; // '', '0.025', '0.020', '0.015', 'BB'
    public $activeTab = 'detail';
    public $isFilterModalOpen = false;

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'search' => ['except' => ''],
        'filterRegion' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterSupervisor' => ['except' => ''],
        'filterStatusVisit' => ['except' => ''],
        'filterJenisReward' => ['except' => ''],
        'filterRewardPercent' => ['except' => ''],
    ];

    public function mount()
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
        $this->applyDefaultUserHierarchy();
    }

    private function getUserScopeCacheKey(): string
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return 'admin';
        }
        return 'u_' . $user->id;
    }

    private function getSupervisorSisoCodes(?string $teamEliteCode): array
    {
        if (empty($teamEliteCode)) {
            return [];
        }

        $cacheKey = 'rwo_spv_siso_' . md5(trim($teamEliteCode));

        return Cache::remember($cacheKey, 600, function () use ($teamEliteCode) {
            return DB::table('team_elite_code_mappings')
                ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$teamEliteCode])
                ->pluck('siso_code')
                ->map(fn($c) => trim((string) $c))
                ->filter()
                ->values()
                ->toArray();
        });
    }

    private function applyUserScopeToDistributorQuery($query, string $alias = '')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        $prefix = $alias ? $alias . '.' : '';

        if (!empty($user->supervisor_code)) {
            $sisoCodes = $this->getSupervisorSisoCodes($user->supervisor_code);
            if (!empty($sisoCodes)) {
                $query->whereIn($prefix . 'supervisor_code', $sisoCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
            $query->whereIn($prefix . 'area_code', $user->area_code);
        } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
            $query->whereIn($prefix . 'region_code', $user->region_code);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function applyDefaultUserHierarchy(): void
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return;
        }

        $regionQuery = DB::table('master_distributors')->select('region_code')->whereNotNull('region_code')->distinct();
        $this->applyUserScopeToDistributorQuery($regionQuery);
        $regions = $regionQuery->get();

        if ($regions->count() === 1) {
            $this->filterRegion = $regions->first()->region_code;

            $areaQuery = DB::table('master_distributors')
                ->select('area_code')
                ->whereNotNull('area_code')
                ->where('region_code', $this->filterRegion)
                ->distinct();
            $this->applyUserScopeToDistributorQuery($areaQuery);
            $areas = $areaQuery->get();

            if ($areas->count() === 1) {
                $this->filterArea = $areas->first()->area_code;

                $spvQuery = DB::table('master_distributors as md')
                    ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                    ->select('t.team_elite_code as supervisor_code')
                    ->where('md.area_code', $this->filterArea)
                    ->distinct();
                $this->applyUserScopeToDistributorQuery($spvQuery, 'md');
                $spvs = $spvQuery->get();

                if ($spvs->count() === 1) {
                    $this->filterSupervisor = $spvs->first()->supervisor_code;
                }
            }
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatusVisit() { $this->resetPage(); }

    public function updatedStartDate()
    {
        if (!empty($this->startDate) && !empty($this->endDate) && $this->startDate > $this->endDate) {
            $this->endDate = $this->startDate;
        }
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        if (!empty($this->startDate) && !empty($this->endDate) && $this->endDate < $this->startDate) {
            $this->startDate = $this->endDate;
        }
        $this->resetPage();
    }

    public function toggleJenisReward(string $value): void
    {
        $newVal = ($this->filterJenisReward === $value) ? '' : $value;
        $this->filterJenisReward = $newVal;
        $this->updatedFilterJenisReward($newVal);
    }

    public function toggleStatusVisit(string $value): void
    {
        $this->filterStatusVisit = ($this->filterStatusVisit === $value) ? '' : $value;
        $this->resetPage();
    }

    public function clearQuickFilters(): void
    {
        $this->filterJenisReward = '';
        $this->filterRewardPercent = '';
        $this->filterStatusVisit = '';
        $this->resetPage();
    }

    public function updatedFilterJenisReward($value)
    {
        if ($value === 'BB' && in_array($this->filterRewardPercent, ['0.025', '0.020', '0.015'])) {
            $this->filterRewardPercent = '';
        } elseif ($value === 'CB' && $this->filterRewardPercent === 'BB') {
            $this->filterRewardPercent = '';
        }
        $this->resetPage();
    }

    public function updatedFilterRewardPercent($value)
    {
        if ($value === 'BB') {
            $this->filterJenisReward = 'BB';
        } elseif (in_array($value, ['0.025', '0.020', '0.015']) && $this->filterJenisReward === 'BB') {
            $this->filterJenisReward = 'CB';
        }
        $this->resetPage();
    }

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
        $this->reset([
            'search',
            'filterRegion',
            'filterArea',
            'filterSupervisor',
            'filterStatusVisit',
            'filterJenisReward',
            'filterRewardPercent',
        ]);

        $this->applyDefaultUserHierarchy();
        $this->isFilterModalOpen = false;
        $this->resetPage();
    }

    private function buildQueryAndBindings()
    {
        $user = auth()->user();
        $bindings = [];
        $bindings[] = $this->startDate ?: date('Y-m-01');
        $bindings[] = $this->endDate ?: date('Y-m-t');

        // Determine active kuartal for target join
        $currentMonth = date('n', strtotime($this->startDate ?: date('Y-m-01')));
        $kuartalNum = (int) ceil($currentMonth / 3);
        $bindings[] = $kuartalNum;

        $whereClauses = [];

        // Permissions check
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $sisoCodes = $this->getSupervisorSisoCodes($user->supervisor_code);
                if (!empty($sisoCodes)) {
                    $placeholders = implode(',', array_fill(0, count($sisoCodes), '?'));
                    $whereClauses[] = "md.supervisor_code IN ($placeholders)";
                    foreach ($sisoCodes as $code) {
                        $bindings[] = $code;
                    }
                } else {
                    $whereClauses[] = "1 = 0";
                }
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $areaCodes = $user->area_code;
                $placeholders = implode(',', array_fill(0, count($areaCodes), '?'));
                $whereClauses[] = "md.area_code IN ($placeholders)";
                foreach ($areaCodes as $code) {
                    $bindings[] = $code;
                }
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionCodes = $user->region_code;
                $placeholders = implode(',', array_fill(0, count($regionCodes), '?'));
                $whereClauses[] = "md.region_code IN ($placeholders)";
                foreach ($regionCodes as $code) {
                    $bindings[] = $code;
                }
            } else {
                $whereClauses[] = "1 = 0";
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
            $sisoCodes = $this->getSupervisorSisoCodes($this->filterSupervisor);
            if (!empty($sisoCodes)) {
                $placeholders = implode(',', array_fill(0, count($sisoCodes), '?'));
                $whereClauses[] = "md.supervisor_code IN ($placeholders)";
                foreach ($sisoCodes as $code) {
                    $bindings[] = $code;
                }
            } else {
                $whereClauses[] = "1 = 0";
            }
        }

        // Jenis Reward Filter ('CB' or 'BB')
        if ($this->filterJenisReward === 'CB') {
            $whereClauses[] = "COALESCE(rwo.jenis_reward, 'CB') = 'CB'";
        } elseif ($this->filterJenisReward === 'BB') {
            $whereClauses[] = "COALESCE(rwo.jenis_reward, 'CB') = 'BB'";
        }

        // Reward Percent / Tier Filter
        if ($this->filterRewardPercent) {
            if ($this->filterRewardPercent === '0.025') {
                $whereClauses[] = "COALESCE(rwo.jenis_reward, 'CB') = 'CB' AND rwo.total_target >= 90000000";
            } elseif ($this->filterRewardPercent === '0.020') {
                $whereClauses[] = "COALESCE(rwo.jenis_reward, 'CB') = 'CB' AND (rwo.total_target >= 30000000 AND rwo.total_target < 90000000)";
            } elseif ($this->filterRewardPercent === '0.015') {
                $whereClauses[] = "COALESCE(rwo.jenis_reward, 'CB') = 'CB' AND (rwo.total_target < 30000000 OR rwo.total_target IS NULL)";
            } elseif ($this->filterRewardPercent === 'BB') {
                $whereClauses[] = "COALESCE(rwo.jenis_reward, 'CB') = 'BB'";
            }
        }

        // Status Visit Filter ('Y' or 'N')
        if ($this->filterStatusVisit === 'Y') {
            $whereClauses[] = "(COALESCE(v.visit_region, 0) > 0 OR COALESCE(v.visit_area, 0) > 0 OR COALESCE(v.visit_supervisor, 0) > 0)";
        } elseif ($this->filterStatusVisit === 'N') {
            $whereClauses[] = "(COALESCE(v.visit_region, 0) = 0 AND COALESCE(v.visit_area, 0) = 0 AND COALESCE(v.visit_supervisor, 0) = 0)";
        }

        // Search Filter
        if (trim((string) $this->search) !== '') {
            $whereClauses[] = "(l.customer_code_prc ILIKE ? OR l.uniq_kd ILIKE ? OR l.customer_name ILIKE ? OR md.distributor_name ILIKE ? OR spv.supervisor_name ILIKE ?)";
            $searchTerm = '%' . trim((string) $this->search) . '%';
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }

        $whereSql = '';
        if (count($whereClauses) > 0) {
            $whereSql = ' AND ' . implode(' AND ', $whereClauses);
        }

        $rawSql = "
            WITH visit_data AS (
                SELECT
                    z.custno,
                    MAX(CASE WHEN LOWER(z.\"level\") = 'region' THEN 1 ELSE 0 END) AS visit_region,
                    COUNT(CASE WHEN LOWER(z.\"level\") = 'region' THEN 1 END) AS count_region,
                    MAX(CASE WHEN LOWER(z.\"level\") = 'region' THEN z.tanggal END) AS last_visit_region,
                    MAX(CASE WHEN LOWER(z.\"level\") = 'area' THEN 1 ELSE 0 END) AS visit_area,
                    COUNT(CASE WHEN LOWER(z.\"level\") = 'area' THEN 1 END) AS count_area,
                    MAX(CASE WHEN LOWER(z.\"level\") = 'area' THEN z.tanggal END) AS last_visit_area,
                    MAX(CASE WHEN LOWER(z.\"level\") = 'supervisor' THEN 1 ELSE 0 END) AS visit_supervisor,
                    COUNT(CASE WHEN LOWER(z.\"level\") = 'supervisor' THEN 1 END) AS count_supervisor,
                    MAX(CASE WHEN LOWER(z.\"level\") = 'supervisor' THEN z.tanggal END) AS last_visit_supervisor
                FROM zv_summary_visit_team_elite z
                WHERE z.status_visit = 'Y'
                  AND z.tanggal BETWEEN ? AND ?
                GROUP BY z.custno
            ),
            potensi_rwo AS (
                SELECT DISTINCT ON (customer_code)
                    customer_code,
                    distributor_code,
                    total_target,
                    COALESCE(NULLIF(TRIM(jenis_reward), ''), 'CB') AS jenis_reward
                FROM list_potensi_rwo
                WHERE kuartal = ?
                  AND customer_code IS NOT NULL
                ORDER BY customer_code, id DESC
            ),
            spv_map AS (
                SELECT DISTINCT ON (TRIM(t.siso_code))
                    TRIM(t.siso_code) AS siso_code,
                    t.team_elite_code AS supervisor_code,
                    UPPER(f.\"SLSNAME\") AS supervisor_name
                FROM team_elite_code_mappings t
                LEFT JOIN fsalesman f
                    ON TRIM(t.team_elite_code) = TRIM(f.\"SLSNO\")
                WHERE t.siso_code IS NOT NULL AND TRIM(t.siso_code) != ''
                ORDER BY TRIM(t.siso_code)
            )
            SELECT
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                spv.supervisor_code,
                spv.supervisor_name,
                l.distributor_code,
                md.distributor_name,
                l.customer_code_prc AS customer_code,
                l.uniq_kd,
                l.customer_name,
                l.pilar,
                COALESCE(rwo.jenis_reward, 'CB') AS jenis_reward,
                COALESCE(rwo.total_target, 0) AS total_target,
                CASE
                    WHEN COALESCE(rwo.jenis_reward, 'CB') = 'BB' THEN 0
                    WHEN rwo.total_target >= 90000000 THEN 0.025
                    WHEN rwo.total_target >= 30000000 THEN 0.020
                    ELSE 0.015
                END AS reward_percent,
                CASE
                    WHEN rwo.total_target >= 90000000 THEN 'RSM'
                    WHEN rwo.total_target >= 30000000 THEN 'ASM'
                    ELSE 'SPV'
                END AS pic,
                COALESCE(v.visit_region, 0) AS visit_region,
                COALESCE(v.count_region, 0) AS count_region,
                v.last_visit_region,
                COALESCE(v.visit_area, 0) AS visit_area,
                COALESCE(v.count_area, 0) AS count_area,
                v.last_visit_area,
                COALESCE(v.visit_supervisor, 0) AS visit_supervisor,
                COALESCE(v.count_supervisor, 0) AS count_supervisor,
                v.last_visit_supervisor,
                CASE
                    WHEN COALESCE(v.visit_region, 0) > 0
                      OR COALESCE(v.visit_area, 0) > 0
                      OR COALESCE(v.visit_supervisor, 0) > 0
                    THEN 'Y'
                    ELSE 'N'
                END AS status_visit
            FROM list_toko_pareto_team_elite l
            LEFT JOIN master_distributors md
                ON l.distributor_code = md.distributor_code
            LEFT JOIN spv_map spv
                ON spv.siso_code = TRIM(md.supervisor_code)
            LEFT JOIN visit_data v
                ON l.customer_code_prc = v.custno
            LEFT JOIN potensi_rwo rwo
                ON l.uniq_kd = rwo.customer_code
            WHERE l.pilar = '1. RWO'
              {$whereSql}
        ";

        return [$rawSql, $bindings];
    }

    public function export()
    {
        list($rawSql, $bindings) = $this->buildQueryAndBindings();

        $finalQuerySql = $rawSql . " ORDER BY md.region_name ASC, md.area_name ASC, md.distributor_name ASC, l.customer_name ASC";
        $items = DB::select($finalQuerySql, $bindings);

        \App\Helpers\ActivityLogger::log('Export Monitoring Visit RWO', "Mengekspor rincian Monitoring Visit RWO dari {$this->startDate} s/d {$this->endDate}.");

        return Excel::download(
            new MonitoringOutletParetoExport($items, 'rwo_detail'),
            'Monitoring_Visit_RWO_' . $this->startDate . '_to_' . $this->endDate . '.xlsx'
        );
    }

    public function render()
    {
        $scopeKey = $this->getUserScopeCacheKey();

        // 1. Regions (cached 10m per user scope)
        $regions = Cache::remember('rwo_mon_regions_' . $scopeKey, 600, function () {
            $regionQuery = DB::table('master_distributors')
                ->select('region_code', 'region_name')
                ->whereNotNull('region_code')
                ->distinct();
            $this->applyUserScopeToDistributorQuery($regionQuery);
            return $regionQuery->orderBy('region_name')->get();
        });

        // 2. Areas (cached 10m per user scope + region)
        $areas = [];
        if ($this->filterRegion) {
            $areas = Cache::remember('rwo_mon_areas_' . $scopeKey . '_' . $this->filterRegion, 600, function () {
                $areaQuery = DB::table('master_distributors')
                    ->select('area_code', 'area_name')
                    ->where('region_code', $this->filterRegion)
                    ->whereNotNull('area_code')
                    ->distinct();
                $this->applyUserScopeToDistributorQuery($areaQuery);
                return $areaQuery->orderBy('area_name')->get();
            });
        }

        // 3. Supervisors (cached 10m per user scope + area)
        $supervisors = [];
        if ($this->filterArea) {
            $supervisors = Cache::remember('rwo_mon_spvs_' . $scopeKey . '_' . $this->filterArea, 600, function () {
                $spvQuery = DB::table('master_distributors as md')
                    ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                    ->join('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
                    ->select('t.team_elite_code as supervisor_code', 'f.SLSNAME as supervisor_name')
                    ->where('md.area_code', $this->filterArea)
                    ->distinct();
                $this->applyUserScopeToDistributorQuery($spvQuery, 'md');
                return $spvQuery->orderBy('f.SLSNAME')->get();
            });
        }

        list($rawSql, $bindings) = $this->buildQueryAndBindings();

        // Single KPI + Total Count calculation strictly for 1. RWO
        $kpiSql = "
            SELECT
                COUNT(*) as total_outlets,
                COUNT(CASE WHEN jenis_reward = 'CB' THEN 1 END) as total_cb,
                COUNT(CASE WHEN jenis_reward = 'BB' THEN 1 END) as total_bb,
                COALESCE(SUM(CASE WHEN status_visit = 'Y' THEN 1 ELSE 0 END), 0) as visited_outlets,
                COALESCE(SUM(CASE WHEN status_visit = 'N' THEN 1 ELSE 0 END), 0) as unvisited_outlets,
                COALESCE(SUM(visit_region), 0) as total_visit_region,
                COALESCE(SUM(visit_area), 0) as total_visit_area,
                COALESCE(SUM(visit_supervisor), 0) as total_visit_supervisor
            FROM (
                {$rawSql}
            ) as sub
        ";
        $kpi = DB::selectOne($kpiSql, $bindings);

        $totalItems = (int) ($kpi->total_outlets ?? 0);
        $perPage = 100;
        $page = $this->getPage();

        $orderBySql = " ORDER BY md.region_name ASC, md.area_name ASC, md.distributor_name ASC, l.customer_name ASC";
        $paginatedSql = $rawSql . $orderBySql . " LIMIT ? OFFSET ?";
        $pageBindings = array_merge($bindings, [$perPage, ($page - 1) * $perPage]);

        $items = DB::select($paginatedSql, $pageBindings);

        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $totalItems,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.rwo.monitoring-pareto-rwo', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
            'kpi' => $kpi,
        ])->layout('layouts.app');
    }
}

