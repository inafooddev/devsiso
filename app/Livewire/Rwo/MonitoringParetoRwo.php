<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
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
    public $filterRewardPercent = '';
    public $activeTab = 'detail'; // Hardcoded to detail view
    public $isFilterModalOpen = false;

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'search' => ['except' => ''],
        'filterRegion' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterSupervisor' => ['except' => ''],
        'filterStatusVisit' => ['except' => ''],
        'filterRewardPercent' => ['except' => ''],
    ];

    public function mount()
    {
        // Default date range to active month
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            // Auto-set Region
            $regionQuery = DB::table('master_distributors')->select('region_code')->whereNotNull('region_code')->distinct();
            if (!empty($user->supervisor_code)) {
                $sisoCodes = DB::table('team_elite_code_mappings')
                    ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                    ->pluck('siso_code')
                    ->toArray();
                if (!empty($sisoCodes)) {
                    $regionQuery->whereIn('supervisor_code', $sisoCodes);
                } else {
                    $regionQuery->whereRaw('1 = 0');
                }
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $regionQuery->whereIn('area_code', $user->area_code);
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionQuery->whereIn('region_code', $user->region_code);
            } else {
                $regionQuery->whereRaw('1 = 0');
            }
            
            $regions = $regionQuery->get();
            if ($regions->count() === 1) {
                $this->filterRegion = $regions->first()->region_code;
                
                // Auto-set Area
                $areaQuery = DB::table('master_distributors')->select('area_code')->whereNotNull('area_code')->where('region_code', $this->filterRegion)->distinct();
                if (!empty($user->supervisor_code)) {
                    if (!empty($sisoCodes)) {
                        $areaQuery->whereIn('supervisor_code', $sisoCodes);
                    } else {
                        $areaQuery->whereRaw('1 = 0');
                    }
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $areaQuery->whereIn('area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $areaQuery->whereIn('region_code', $user->region_code);
                } else {
                    $areaQuery->whereRaw('1 = 0');
                }

                $areas = $areaQuery->get();
                if ($areas->count() === 1) {
                    $this->filterArea = $areas->first()->area_code;
                    
                    // Auto-set Supervisor
                    $spvQuery = DB::table('master_distributors as md')
                        ->join('team_elite_code_mappings as t', 't.siso_code', '=', 'md.supervisor_code')
                        ->select('t.team_elite_code as supervisor_code')
                        ->where('md.area_code', $this->filterArea)
                        ->distinct();
                    
                    if (!empty($user->supervisor_code)) {
                        if (!empty($sisoCodes)) {
                            $spvQuery->whereIn('md.supervisor_code', $sisoCodes);
                        } else {
                            $spvQuery->whereRaw('1 = 0');
                        }
                    } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                        $spvQuery->whereIn('md.area_code', $user->area_code);
                    } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                        $spvQuery->whereIn('md.region_code', $user->region_code);
                    } else {
                        $spvQuery->whereRaw('1 = 0');
                    }

                    $spvs = $spvQuery->get();
                    if ($spvs->count() === 1) {
                        $this->filterSupervisor = $spvs->first()->supervisor_code;
                    }
                }
            }
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingFilterStatusVisit() { $this->resetPage(); }
    public function updatingFilterRewardPercent() { $this->resetPage(); }

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
        $this->reset(['filterRegion', 'filterArea', 'filterSupervisor', 'filterStatusVisit', 'filterRewardPercent']);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            $regions = DB::table('master_regions')->select('region_code');
            if (!empty($user->supervisor_code)) {
                $regions->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.region_code', 'master_regions.region_code')
                        ->where('tecm.team_elite_code', $user->supervisor_code);
                });
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $regions->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('team_elite_code_mappings as tecm')
                        ->whereColumn('tecm.region_code', 'master_regions.region_code')
                        ->whereIn('tecm.area_code', $user->area_code);
                });
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regions->whereIn('region_code', $user->region_code);
            } else {
                $regions->whereRaw('1 = 0');
            }
            $regions = $regions->get();
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

        // Determine active kuartal for target join
        $currentMonth = date('n', strtotime($this->startDate ?: date('Y-m-01')));
        $kuartalNum = (int) ceil($currentMonth / 3);
        $bindings[] = $kuartalNum;

        $whereClauses = [];

        // Permissions check
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $sisoCodes = DB::table('team_elite_code_mappings')
                    ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                    ->pluck('siso_code')
                    ->toArray();
                
                if (!empty($sisoCodes)) {
                    $placeholders = implode(',', array_fill(0, count($sisoCodes), '?'));
                    $whereClauses[] = "md.supervisor_code IN ($placeholders)";
                    foreach ($sisoCodes as $code) {
                        $bindings[] = trim($code);
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
            $sisoCodes = DB::table('team_elite_code_mappings')
                ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$this->filterSupervisor])
                ->pluck('siso_code')
                ->toArray();
            
            if (!empty($sisoCodes)) {
                $placeholders = implode(',', array_fill(0, count($sisoCodes), '?'));
                $whereClauses[] = "md.supervisor_code IN ($placeholders)";
                foreach ($sisoCodes as $code) {
                    $bindings[] = trim($code);
                }
            } else {
                $whereClauses[] = "1 = 0";
            }
        }

        // Reward Percent Filter
        if ($this->filterRewardPercent) {
            if ($this->filterRewardPercent == '0.025') {
                $whereClauses[] = "rwo.total_target >= 90000000";
            } elseif ($this->filterRewardPercent == '0.020') {
                $whereClauses[] = "(rwo.total_target >= 30000000 AND rwo.total_target < 90000000)";
            } elseif ($this->filterRewardPercent == '0.015') {
                $whereClauses[] = "(rwo.total_target < 30000000 OR rwo.total_target IS NULL)";
            }
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

        $whereSql = '';
        if (count($whereClauses) > 0) {
            $whereSql = ' AND ' . implode(' AND ', $whereClauses);
        }

        // Having clauses for status visit filter
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
                COALESCE(
                    CASE
                        WHEN rwo.total_target >= 90000000 THEN 0.025
                        WHEN rwo.total_target >= 30000000 THEN 0.020
                        ELSE 0.015
                    END,
                    0.015
                ) AS reward_percent,

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
                ON TRIM(t.siso_code) = TRIM(md.supervisor_code)

            LEFT JOIN fsalesman f
                ON TRIM(t.team_elite_code) = TRIM(f.\"SLSNO\")

            LEFT JOIN visit_data v
                ON l.customer_code_prc = v.custno

            LEFT JOIN list_potensi_rwo rwo
                ON l.uniq_kd = rwo.customer_code
                AND l.distributor_code = rwo.distributor_code
                AND rwo.kuartal = ?

            WHERE l.pilar = '1. RWO'
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
                l.pilar,
                rwo.total_target
            {$havingSql}
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
            new MonitoringOutletParetoExport($items, 'detail'), // Force detail template
            'Monitoring_Visit_RWO_' . $this->startDate . '_to_' . $this->endDate . '.xlsx'
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
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $sisoCodes = DB::table('team_elite_code_mappings')
                    ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                    ->pluck('siso_code')
                    ->toArray();

                if (!empty($sisoCodes)) {
                    $regionQuery->whereIn('supervisor_code', $sisoCodes);
                } else {
                    $regionQuery->whereRaw('1 = 0');
                }
            } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                $regionQuery->whereIn('area_code', $user->area_code);
            } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                $regionQuery->whereIn('region_code', $user->region_code);
            } else {
                $regionQuery->whereRaw('1 = 0');
            }
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
            if ($user && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    $sisoCodes = DB::table('team_elite_code_mappings')
                        ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                        ->pluck('siso_code')
                        ->toArray();

                    if (!empty($sisoCodes)) {
                        $areaQuery->whereIn('supervisor_code', $sisoCodes);
                    } else {
                        $areaQuery->whereRaw('1 = 0');
                    }
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $areaQuery->whereIn('area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $areaQuery->whereIn('region_code', $user->region_code);
                } else {
                    $areaQuery->whereRaw('1 = 0');
                }
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
            if ($user && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    $sisoCodes = DB::table('team_elite_code_mappings')
                        ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                        ->pluck('siso_code')
                        ->toArray();

                    if (!empty($sisoCodes)) {
                        $spvQuery->whereIn('md.supervisor_code', $sisoCodes);
                    } else {
                        $spvQuery->whereRaw('1 = 0');
                    }
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $spvQuery->whereIn('md.area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $spvQuery->whereIn('md.region_code', $user->region_code);
                } else {
                    $spvQuery->whereRaw('1 = 0');
                }
            }
            $supervisors = $spvQuery->orderBy('f.SLSNAME')->get();
        }

        list($rawSql, $bindings) = $this->buildQueryAndBindings();

        // KPI calculation strictly for 1. RWO
        $kpiBindings = $bindings;
        $kpiSql = "
            SELECT
                COUNT(*) as total_outlets,
                SUM(CASE WHEN status_visit = 'Y' THEN 1 ELSE 0 END) as visited_outlets,
                SUM(visit_region) as total_visit_region,
                SUM(visit_area) as total_visit_area,
                SUM(visit_supervisor) as total_visit_supervisor
            FROM (
                {$rawSql}
            ) as sub
        ";
        $kpi = DB::selectOne($kpiSql, $kpiBindings);

        // Fetch detail paginated data
        $finalSql = $rawSql;
        $orderBySql = " ORDER BY md.region_name ASC, md.area_name ASC, md.distributor_name ASC, l.customer_name ASC";
        
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

        return view('livewire.rwo.monitoring-pareto-rwo', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
            'kpi' => $kpi
        ])->layout('layouts.app');
    }
}
