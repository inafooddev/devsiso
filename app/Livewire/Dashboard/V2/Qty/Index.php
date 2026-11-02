<?php

namespace App\Livewire\Dashboard\V2\Qty;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Dashboard\Traits\WithAccessFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Index extends Component
{
    use WithPagination, WithAccessFilter;

    // Filters
    public string $selectedYear = '';
    public array $selectedMonths = []; // multi-select: [] = all months
    public string $selectedProdukLine = 'ALL'; // ALL | REG | FEST
    public string $selectedKategory = ''; // '' | TOPITEM | NPD | OTHER
    public string $selectedTopitem = ''; // '' | specific top item
    public string $selectedSubbrand = ''; // '' | specific subbrand

    // Breakdown Dimension Toggle
    public string $breakdownBy = 'Nasional'; // Nasional | Region | Area | Supervisor | Cabang

    // Hierarchy Dynamic Filters
    public array $listRegions = [];
    public array $listAreas = [];
    public array $listSupervisors = [];
    public array $listCabangs = [];

    public string $filterRegion = '';
    public string $filterArea = '';
    public string $filterSupervisor = '';
    public string $filterCabang = '';

    // Product Filter Options
    public array $listKategories = [];
    public array $listTopitems = [];
    public array $listSubbrands = [];

    // Available years
    public array $yearOptions = [];

    public array $monthOptions = [
        ['id' => '1',  'name' => 'Januari'],
        ['id' => '2',  'name' => 'Februari'],
        ['id' => '3',  'name' => 'Maret'],
        ['id' => '4',  'name' => 'April'],
        ['id' => '5',  'name' => 'Mei'],
        ['id' => '6',  'name' => 'Juni'],
        ['id' => '7',  'name' => 'Juli'],
        ['id' => '8',  'name' => 'Agustus'],
        ['id' => '9',  'name' => 'September'],
        ['id' => '10', 'name' => 'Oktober'],
        ['id' => '11', 'name' => 'November'],
        ['id' => '12', 'name' => 'Desember'],
    ];

    // Charts JSON
    public $chartContribution = '{}';
    public $chartRegionHBar = '{}';
    public $chartMonthlyBar = '{}';
    public $chartSalesTrend = '{}';
    public $chartMapData = '[]';

    // Tables Data
    public array $topSubbrand = [];
    public array $topCabangIn = [];
    public array $topCabangOut = [];
    public array $topKategori = [];

    // Pivot Matrix
    public string $pivotDataJson = '{}';
    public string $pivotGrandTotalJson = '{}';
    public string $pivotLevel1Name = 'Region';
    public string $pivotLevel2Name = 'Area';
    public int $pivotMaxMonth = 12;

    // KPI Card Data
    public array $kpiData = [];

    public function mount()
    {
        $userLevel = auth()->user() ? auth()->user()->getAccessLevel() : 'nasional';
        if ($userLevel === 'region') {
            $this->breakdownBy = 'Region';
        } elseif ($userLevel === 'area') {
            $this->breakdownBy = 'Area';
        } elseif ($userLevel === 'supervisor') {
            $this->breakdownBy = 'Supervisor';
        }

        // Years from sellin_per_cabang_per_qty
        $query = DB::table('sellin_per_cabang_per_qty')
            ->selectRaw('EXTRACT(YEAR FROM bulan)::int as yr')
            ->distinct();
            
        $this->applyAccessFilter($query);
        
        $years = $query->orderBy('yr', 'desc')
            ->pluck('yr')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y')];
        }

        $this->yearOptions = array_map(function($yr) {
            return ['id' => (string)$yr, 'name' => (string)$yr];
        }, $years);

        if (!in_array($this->selectedYear, $years)) {
            $this->selectedYear = (string)$years[0];
        }

        $this->loadProductFilterOptions();
        $this->loadHierarchyFilters();
        $this->reload();
    }

    private function loadProductFilterOptions()
    {
        $getBase = function() {
            $q = DB::table('master_produk_lama');
            if (!empty($this->selectedProdukLine) && in_array($this->selectedProdukLine, ['REG', 'FEST'])) {
                $q->where('produk_line', $this->selectedProdukLine);
            }
            return $q;
        };

        // 1. Kategori (difilter oleh Produk Line)
        $this->listKategories = $getBase()
            ->whereNotNull('kategory')->where('kategory', '!=', '')
            ->distinct()->orderBy('kategory')->pluck('kategory')->toArray();

        if (!empty($this->selectedKategory) && !in_array($this->selectedKategory, $this->listKategories)) {
            $this->selectedKategory = '';
        }

        // 2. Top Item (difilter oleh Produk Line dan Kategori jika dipilih)
        $qTop = $getBase();
        if (!empty($this->selectedKategory)) {
            $qTop->where('kategory', $this->selectedKategory);
        }
        $this->listTopitems = $qTop
            ->whereNotNull('topitem')->where('topitem', '!=', '')
            ->distinct()->orderBy('topitem')->pluck('topitem')->toArray();

        if (!empty($this->selectedTopitem) && !in_array($this->selectedTopitem, $this->listTopitems)) {
            $this->selectedTopitem = '';
        }

        // 3. Subbrand (difilter oleh Produk Line, Kategori, dan Top Item jika dipilih)
        $qSub = $getBase();
        if (!empty($this->selectedKategory)) {
            $qSub->where('kategory', $this->selectedKategory);
        }
        if (!empty($this->selectedTopitem)) {
            $qSub->where('topitem', $this->selectedTopitem);
        }
        $this->listSubbrands = $qSub
            ->whereNotNull('subbrand')->where('subbrand', '!=', '')
            ->distinct()->orderBy('subbrand')->pluck('subbrand')->toArray();

        if (!empty($this->selectedSubbrand) && !in_array($this->selectedSubbrand, $this->listSubbrands)) {
            $this->selectedSubbrand = '';
        }
    }

    private function loadHierarchyFilters()
    {
        $baseQuery = DB::table('sellin_per_cabang_per_qty')
            ->where('region', '!=', 'HOINA');
            
        $this->applyAccessFilter($baseQuery);

        $this->listRegions = (clone $baseQuery)->whereNotNull('region')->where('region', '!=', '')->distinct()->pluck('region')->sort()->values()->toArray();
        $this->listAreas = (clone $baseQuery)->whereNotNull('area')->where('area', '!=', '')->distinct()->pluck('area')->sort()->values()->toArray();
        $this->listCabangs = (clone $baseQuery)->whereNotNull('cabang')->where('cabang', '!=', '')->distinct()->pluck('cabang')->sort()->values()->toArray();

        // Supervisors from master_distributors & master_supervisors
        $spvQuery = DB::table('master_distributors as md')
            ->leftJoin('master_supervisors as ms', 'md.supervisor_code', '=', 'ms.supervisor_code')
            ->where('md.is_active', true);

        $user = auth()->user();
        if ($user && $user->getAccessLevel() !== 'nasional') {
            $this->applyAccessFilter($spvQuery, 'md.', 'branch_name');
        }

        $this->listSupervisors = $spvQuery->selectRaw("COALESCE(ms.description, md.supervisor_name, 'VACANT') as spv")
            ->distinct()
            ->pluck('spv')
            ->sort()
            ->values()
            ->toArray();

        // Auto-select filters if there is only 1 option (restricted by RLS)
        if (count($this->listRegions) === 1) {
            $this->filterRegion = $this->listRegions[0];
        }
        if (count($this->listAreas) === 1) {
            $this->filterArea = $this->listAreas[0];
        }
        if (count($this->listSupervisors) === 1) {
            $this->filterSupervisor = $this->listSupervisors[0];
        }
    }

    public function setBreakdownBy(string $dimension): void
    {
        $this->breakdownBy = $dimension;
        $this->updatedBreakdownBy();
    }

    public function updatedBreakdownBy()
    {
        $this->filterRegion = count($this->listRegions) === 1 ? $this->listRegions[0] : '';
        $this->filterArea = count($this->listAreas) === 1 ? $this->listAreas[0] : '';
        $this->filterSupervisor = count($this->listSupervisors) === 1 ? $this->listSupervisors[0] : '';
        $this->filterCabang = count($this->listCabangs) === 1 ? $this->listCabangs[0] : '';
        
        $this->reload();
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
        $this->filterSupervisor = '';
        $this->filterCabang = '';
        if ($this->filterRegion) {
            $this->breakdownBy = 'Region';
        } else {
            $this->breakdownBy = 'Nasional';
        }
        $this->reload();
    }

    public function updatedFilterArea()
    {
        $this->filterSupervisor = '';
        $this->filterCabang = '';
        if ($this->filterArea) {
            $this->breakdownBy = 'Area';
        } else {
            $this->breakdownBy = $this->filterRegion ? 'Region' : 'Nasional';
        }
        $this->reload();
    }

    public function updatedFilterSupervisor()
    {
        $this->filterCabang = '';
        if ($this->filterSupervisor) {
            $this->breakdownBy = 'Supervisor';
        } else {
            $this->breakdownBy = $this->filterArea ? 'Area' : ($this->filterRegion ? 'Region' : 'Nasional');
        }
        $this->reload();
    }

    public function updatedFilterCabang()
    {
        if ($this->filterCabang) {
            $this->breakdownBy = 'Cabang';
        } else {
            $this->breakdownBy = $this->filterSupervisor ? 'Supervisor' : ($this->filterArea ? 'Area' : ($this->filterRegion ? 'Region' : 'Nasional'));
        }
        $this->reload();
    }

    public function updatedSelectedYear()        { $this->reload(); }
    public function updatedSelectedMonths()      { $this->reload(); }
    public function updatedSelectedProdukLine()
    {
        $this->loadProductFilterOptions();
        $this->reload();
    }

    public function updatedSelectedKategory()
    {
        $this->loadProductFilterOptions();
        $this->reload();
    }

    public function updatedSelectedTopitem()
    {
        $this->loadProductFilterOptions();
        $this->reload();
    }

    public function updatedSelectedSubbrand()
    {
        $this->reload();
    }

    private function reload()
    {
        $this->loadKpiData();
        $this->loadChartData();
        $this->loadPivotData();
        $this->loadTableData();
        $this->dispatch('charts-updated');
    }

    /**
     * DUAL-ENGINE 1: TERRITORY & TREND RAW DATA
     */
    private function getRawTerritoryData()
    {
        $monthsKey = implode('-', $this->selectedMonths);
        $cacheKey = "qty_territory_v2_{$this->breakdownBy}_{$this->selectedYear}_{$monthsKey}_{$this->selectedProdukLine}_{$this->selectedKategory}_{$this->selectedTopitem}_{$this->selectedSubbrand}_{$this->filterRegion}_{$this->filterArea}_{$this->filterSupervisor}_{$this->filterCabang}";

        return Cache::remember($cacheKey, now()->addHours(6), function() {
            $ty = (int)$this->selectedYear;
            $ly = $ty - 1;

            // Product filters
            $whereProd = [];
            $bindings = [];
            
            if (!empty($this->selectedProdukLine) && in_array($this->selectedProdukLine, ['REG', 'FEST'])) {
                $whereProd[] = "p.produk_line = ?";
                $bindings[] = $this->selectedProdukLine;
            }
            if (!empty($this->selectedKategory)) {
                $whereProd[] = "p.kategory = ?";
                $bindings[] = $this->selectedKategory;
            }
            if (!empty($this->selectedTopitem)) {
                $whereProd[] = "p.topitem = ?";
                $bindings[] = $this->selectedTopitem;
            }
            if (!empty($this->selectedSubbrand)) {
                $whereProd[] = "p.subbrand = ?";
                $bindings[] = $this->selectedSubbrand;
            }

            // Access Filter (Cabang Level)
            $allowedCabangs = [];
            $user = auth()->user();
            if ($user && $user->getAccessLevel() !== 'nasional') {
                $masterQuery = DB::table('vw_distributor_hierarchy');
                $level = $user->getAccessLevel();
                if ($level === 'supervisor') {
                    $masterQuery->where('supervisor_code', $user->supervisor_code);
                } elseif ($level === 'area') {
                    $masterQuery->whereIn('area_code', (array)$user->area_code);
                } elseif ($level === 'region') {
                    $masterQuery->whereIn('region_code', (array)$user->region_code);
                }
                $allowedCabangs = $masterQuery->distinct()->pluck('branch_name')->toArray();
            }

            $cabangFilterSql = '';
            if ($user && $user->getAccessLevel() !== 'nasional') {
                if (empty($allowedCabangs)) {
                    $cabangFilterSql = " AND s.cabang = 'INVALID_ACCESS_NO_CABANG'";
                } else {
                    $cabangListStr = "'" . implode("','", array_map('addslashes', $allowedCabangs)) . "'";
                    $cabangFilterSql = " AND s.cabang IN ({$cabangListStr})";
                }
            }

            $prodWhereSql = !empty($whereProd) ? ' AND ' . implode(' AND ', $whereProd) : '';

            $sql = "
                WITH produk_dedup AS (
                    SELECT DISTINCT ON (divisi)
                        divisi,
                        produk_line,
                        kategory,
                        subbrand,
                        topitem
                    FROM master_produk_lama
                    ORDER BY divisi, (CASE WHEN status_product = 'Aktif' THEN 1 ELSE 2 END)
                ),
                raw_in AS (
                    SELECT 
                        EXTRACT(YEAR FROM s.bulan)::int as yr,
                        EXTRACT(MONTH FROM s.bulan)::int as mnth,
                        s.region as region_name,
                        s.area as area_name,
                        s.cabang as branch_name,
                        s.kd_distributor,
                        SUM(s.qty_ctn) as in_qty
                    FROM sellin_per_cabang_per_qty s
                    LEFT JOIN produk_dedup p ON s.nama_produk_mapping = p.divisi
                    WHERE s.bulan >= '{$ly}-01-01' AND s.bulan <= '{$ty}-12-31'
                      AND s.region != 'HOINA'
                      {$cabangFilterSql}
                      {$prodWhereSql}
                    GROUP BY 1, 2, s.region, s.area, s.cabang, s.kd_distributor
                ),
                raw_out AS (
                    SELECT 
                        EXTRACT(YEAR FROM s.bulan)::int as yr,
                        EXTRACT(MONTH FROM s.bulan)::int as mnth,
                        s.region as region_name,
                        s.area as area_name,
                        s.cabang as branch_name,
                        s.kd_distributor,
                        SUM(s.qty_ctn) as out_qty
                    FROM sellout_per_cabang_per_qty s
                    LEFT JOIN produk_dedup p ON s.nama_produk_mapping = p.divisi
                    WHERE s.bulan >= '{$ly}-01-01' AND s.bulan <= '{$ty}-12-31'
                      AND s.region != 'HOINA'
                      {$cabangFilterSql}
                      {$prodWhereSql}
                    GROUP BY 1, 2, s.region, s.area, s.cabang, s.kd_distributor
                ),
                unioned AS (
                    SELECT yr, mnth, region_name, area_name, branch_name, kd_distributor, in_qty, 0 as out_qty FROM raw_in
                    UNION ALL
                    SELECT yr, mnth, region_name, area_name, branch_name, kd_distributor, 0 as in_qty, out_qty FROM raw_out
                )
                SELECT 
                    u.yr,
                    u.mnth,
                    u.region_name,
                    u.area_name,
                    COALESCE(ms.description, md.supervisor_name, 'VACANT') as supervisor_name,
                    u.branch_name,
                    AVG(md.latitude) as lat,
                    AVG(md.longitude) as lng,
                    SUM(u.in_qty) as total_in_qty,
                    SUM(u.out_qty) as total_out_qty
                FROM unioned u
                LEFT JOIN master_distributors md ON u.kd_distributor = md.distributor_code
                LEFT JOIN master_supervisors ms ON md.supervisor_code = ms.supervisor_code
                GROUP BY 
                    u.yr, u.mnth, 
                    u.region_name, u.area_name, 
                    COALESCE(ms.description, md.supervisor_name, 'VACANT'), 
                    u.branch_name
            ";

            // Bind parameters for raw_in and raw_out (two sets)
            $allBindings = array_merge($bindings, $bindings);
            $rows = DB::select($sql, $allBindings);

            return collect($rows);
        });
    }

    /**
     * DUAL-ENGINE 2: PRODUCT BREAKDOWN RAW DATA
     */
    private function getProductBreakdownData()
    {
        $monthsKey = implode('-', $this->selectedMonths);
        $cacheKey = "qty_product_v2_{$this->selectedYear}_{$monthsKey}_{$this->selectedProdukLine}_{$this->selectedKategory}_{$this->selectedTopitem}_{$this->selectedSubbrand}_{$this->breakdownBy}_{$this->filterRegion}_{$this->filterArea}_{$this->filterSupervisor}_{$this->filterCabang}";

        return Cache::remember($cacheKey, now()->addHours(6), function() {
            $ty = (int)$this->selectedYear;

            $whereFilter = [];
            if (!empty($this->selectedProdukLine) && in_array($this->selectedProdukLine, ['REG', 'FEST'])) {
                $whereFilter[] = "p.produk_line = '{$this->selectedProdukLine}'";
            }
            if (!empty($this->selectedKategory)) {
                $safeKat = addslashes($this->selectedKategory);
                $whereFilter[] = "p.kategory = '{$safeKat}'";
            }
            if (!empty($this->selectedTopitem)) {
                $safeTi = addslashes($this->selectedTopitem);
                $whereFilter[] = "p.topitem = '{$safeTi}'";
            }
            if (!empty($this->selectedSubbrand)) {
                $safeSb = addslashes($this->selectedSubbrand);
                $whereFilter[] = "p.subbrand = '{$safeSb}'";
            }

            $whereClause = !empty($whereFilter) ? ' WHERE ' . implode(' AND ', $whereFilter) : '';

            // Month filtering
            if (!empty($this->selectedMonths)) {
                $monthList = implode(',', array_map('intval', $this->selectedMonths));
                $monthClause = " AND EXTRACT(MONTH FROM s.bulan) IN ({$monthList})";
            } else {
                $maxM = DB::table('sellin_per_cabang_per_qty')
                    ->whereRaw("EXTRACT(YEAR FROM bulan) = {$ty}")
                    ->where('qty_ctn', '>', 0)
                    ->selectRaw("MAX(EXTRACT(MONTH FROM bulan)) as max_m")
                    ->value('max_m') ?: 12;
                $monthClause = " AND EXTRACT(MONTH FROM s.bulan) <= {$maxM}";
            }

            // Access Filter (Cabang Level)
            $allowedCabangs = [];
            $user = auth()->user();
            if ($user && $user->getAccessLevel() !== 'nasional') {
                $masterQuery = DB::table('vw_distributor_hierarchy');
                $level = $user->getAccessLevel();
                if ($level === 'supervisor') {
                    $masterQuery->where('supervisor_code', $user->supervisor_code);
                } elseif ($level === 'area') {
                    $masterQuery->whereIn('area_code', (array)$user->area_code);
                } elseif ($level === 'region') {
                    $masterQuery->whereIn('region_code', (array)$user->region_code);
                }
                $allowedCabangs = $masterQuery->distinct()->pluck('branch_name')->toArray();
            }

            $cabangFilterSql = '';
            if ($user && $user->getAccessLevel() !== 'nasional') {
                if (empty($allowedCabangs)) {
                    $cabangFilterSql = " AND s.cabang = 'INVALID_ACCESS_NO_CABANG'";
                } else {
                    $cabangListStr = "'" . implode("','", array_map('addslashes', $allowedCabangs)) . "'";
                    $cabangFilterSql = " AND s.cabang IN ({$cabangListStr})";
                }
            }

            $terrFilter = [];
            if ($this->breakdownBy === 'Region' && !empty($this->filterRegion)) {
                $safeR = addslashes($this->filterRegion);
                $terrFilter[] = "s.region = '{$safeR}'";
            } elseif ($this->breakdownBy === 'Area' && !empty($this->filterArea)) {
                $safeA = addslashes($this->filterArea);
                $terrFilter[] = "s.area = '{$safeA}'";
            } elseif ($this->breakdownBy === 'Supervisor' && !empty($this->filterSupervisor)) {
                $spvDistributors = DB::table('master_distributors as md')
                    ->leftJoin('master_supervisors as ms', 'md.supervisor_code', '=', 'ms.supervisor_code')
                    ->whereRaw("COALESCE(ms.description, md.supervisor_name, 'VACANT') = ?", [$this->filterSupervisor])
                    ->pluck('md.distributor_code')
                    ->toArray();

                if (!empty($spvDistributors)) {
                    $distInList = "'" . implode("','", array_map('addslashes', $spvDistributors)) . "'";
                    $terrFilter[] = "s.kd_distributor IN ({$distInList})";
                } else {
                    $terrFilter[] = "1 = 0";
                }
            } elseif ($this->breakdownBy === 'Cabang' && !empty($this->filterCabang)) {
                $safeC = addslashes($this->filterCabang);
                $terrFilter[] = "s.cabang = '{$safeC}'";
            }

            $terrClause = !empty($terrFilter) ? ' AND ' . implode(' AND ', $terrFilter) : '';

            $sql = "
                WITH produk_dedup AS (
                    SELECT DISTINCT ON (divisi)
                        divisi,
                        produk_line,
                        kategory,
                        subbrand,
                        topitem
                    FROM master_produk_lama
                    ORDER BY divisi, (CASE WHEN status_product = 'Aktif' THEN 1 ELSE 2 END)
                ),
                raw_in AS (
                    SELECT s.nama_produk_mapping, SUM(s.qty_ctn) as in_qty
                    FROM sellin_per_cabang_per_qty s
                    WHERE EXTRACT(YEAR FROM s.bulan) = {$ty}
                      AND s.region != 'HOINA'
                      {$monthClause}
                      {$cabangFilterSql}
                      {$terrClause}
                    GROUP BY s.nama_produk_mapping
                ),
                raw_out AS (
                    SELECT s.nama_produk_mapping, SUM(s.qty_ctn) as out_qty
                    FROM sellout_per_cabang_per_qty s
                    WHERE EXTRACT(YEAR FROM s.bulan) = {$ty}
                      AND s.region != 'HOINA'
                      {$monthClause}
                      {$cabangFilterSql}
                      {$terrClause}
                    GROUP BY s.nama_produk_mapping
                ),
                unioned AS (
                    SELECT nama_produk_mapping, in_qty, 0 as out_qty FROM raw_in
                    UNION ALL
                    SELECT nama_produk_mapping, 0 as in_qty, out_qty FROM raw_out
                )
                SELECT 
                    COALESCE(p.produk_line, 'REG') as produk_line,
                    COALESCE(p.kategory, 'OTHER') as kategory,
                    COALESCE(p.subbrand, 'OTHER') as subbrand,
                    COALESCE(p.topitem, 'OTHER') as topitem,
                    SUM(u.in_qty) as total_in,
                    SUM(u.out_qty) as total_out,
                    SUM(u.in_qty + u.out_qty) as total_vol
                FROM unioned u
                LEFT JOIN produk_dedup p ON u.nama_produk_mapping = p.divisi
                {$whereClause}
                GROUP BY p.produk_line, p.kategory, p.subbrand, p.topitem
            ";

            return collect(DB::select($sql));
        });
    }

    private function getFilteredTerritoryData()
    {
        $data = $this->getRawTerritoryData();

        if ($this->breakdownBy === 'Region' && !empty($this->filterRegion)) {
            $data = $data->where('region_name', $this->filterRegion);
        } elseif ($this->breakdownBy === 'Area' && !empty($this->filterArea)) {
            $data = $data->where('area_name', $this->filterArea);
        } elseif ($this->breakdownBy === 'Supervisor' && !empty($this->filterSupervisor)) {
            $data = $data->where('supervisor_name', $this->filterSupervisor);
        } elseif ($this->breakdownBy === 'Cabang' && !empty($this->filterCabang)) {
            $data = $data->where('branch_name', $this->filterCabang);
        }

        return $data;
    }

    private function loadKpiData(): void
    {
        $rawData = $this->getFilteredTerritoryData();
        $ty = (int)$this->selectedYear;
        $ly = $ty - 1;

        $ytdMaxMonth = $rawData->where('yr', $ty)->where('total_in_qty', '>', 0)->max('mnth') ?: 12;

        if (!empty($this->selectedMonths)) {
            $filteredData = $rawData->whereIn('mnth', $this->selectedMonths);
            $monthCount = count($this->selectedMonths);
        } else {
            $filteredData = $rawData->where('mnth', '<=', $ytdMaxMonth);
            $monthCount = $ytdMaxMonth;
        }

        $totalInTy = (float) $filteredData->where('yr', $ty)->sum('total_in_qty');
        $totalOutTy = (float) $filteredData->where('yr', $ty)->sum('total_out_qty');

        $totalInLy = (float) $filteredData->where('yr', $ly)->sum('total_in_qty');
        $totalOutLy = (float) $filteredData->where('yr', $ly)->sum('total_out_qty');

        $growthIn = $totalInLy > 0 ? round((($totalInTy - $totalInLy) / $totalInLy) * 100, 1) : 0;
        $growthOut = $totalOutLy > 0 ? round((($totalOutTy - $totalOutLy) / $totalOutLy) * 100, 1) : 0;

        $avgIn = $monthCount > 0 ? (int) ($totalInTy / $monthCount) : 0;
        $avgOut = $monthCount > 0 ? (int) ($totalOutTy / $monthCount) : 0;

        $this->kpiData = [
            'total_in_ty'     => $totalInTy,
            'total_out_ty'    => $totalOutTy,
            'total_in_ly'     => $totalInLy,
            'total_out_ly'    => $totalOutLy,
            'growth_in_pct'   => $growthIn,
            'growth_out_pct'  => $growthOut,
            'avg_in_monthly'  => $avgIn,
            'avg_out_monthly' => $avgOut,
            'gap_qty'         => $totalInTy - $totalOutTy,
            'ratio_in_out'    => $totalOutTy > 0 ? round(($totalInTy / $totalOutTy) * 100, 1) : 0,
        ];
    }

    private function loadChartData()
    {
        $filteredData = $this->getFilteredTerritoryData();
        $ty = (int)$this->selectedYear;
        $ly = $ty - 1;

        $ytdMaxMonth = $filteredData->where('yr', $ty)->where('total_in_qty', '>', 0)->max('mnth') ?: 12;
        $monthScopedData = !empty($this->selectedMonths) 
            ? $filteredData->whereIn('mnth', $this->selectedMonths) 
            : $filteredData->where('mnth', '<=', $ytdMaxMonth);

        // 1. Resolve Dimension & Labels for Breakdown
        $groupCol = 'region_name';
        switch ($this->breakdownBy) {
            case 'Nasional':
                $groupCol = 'region_name';
                break;
            case 'Region':
                $groupCol = empty($this->filterRegion) ? 'region_name' : 'area_name';
                break;
            case 'Area':
                $groupCol = empty($this->filterArea) ? 'area_name' : 'supervisor_name';
                break;
            case 'Supervisor':
                $groupCol = empty($this->filterSupervisor) ? 'supervisor_name' : 'branch_name';
                break;
            case 'Cabang':
                $groupCol = 'branch_name';
                break;
        }

        $labels = $this->resolveLabels();

        // Calculate volume for each label and sort descending by volume (top performers)
        $labelVolumes = [];
        foreach ($labels as $label) {
            $rowsTY = $monthScopedData->where('yr', $ty)->where($groupCol, $label);
            $inQty  = (int) $rowsTY->sum('total_in_qty');
            $outQty = (int) $rowsTY->sum('total_out_qty');
            $labelVolumes[$label] = [
                'label' => $label,
                'in'    => $inQty,
                'out'   => $outQty,
                'total' => $inQty + $outQty
            ];
        }

        uasort($labelVolumes, fn($a, $b) => $b['total'] <=> $a['total']);

        if (count($labelVolumes) > 15) {
            $labelVolumes = array_slice($labelVolumes, 0, 15, true);
        }

        if (empty($labelVolumes)) {
            $sortedLabels  = ['No Data'];
            $seriesInHBar  = [0];
            $seriesOutHBar = [0];
        } else {
            $sortedLabels  = array_column($labelVolumes, 'label');
            $seriesInHBar  = array_column($labelVolumes, 'in');
            $seriesOutHBar = array_column($labelVolumes, 'out');
        }

        // Region HBar (In vs Out Side-by-Side)
        $this->chartRegionHBar = json_encode([
            'labels' => $sortedLabels,
            'in'     => $seriesInHBar,
            'out'    => $seriesOutHBar,
        ]);

        // 2. Monthly Trend (In vs Out Side-by-Side for 12 months)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyIn = [];
        $monthlyOut = [];

        foreach (range(1, 12) as $mIdx) {
            $mRows = $filteredData->where('yr', $ty)->where('mnth', $mIdx);
            $monthlyIn[]  = (int) $mRows->sum('total_in_qty');
            $monthlyOut[] = (int) $mRows->sum('total_out_qty');
        }

        $this->chartMonthlyBar = json_encode([
            'labels' => $months,
            'in'     => $monthlyIn,
            'out'    => $monthlyOut,
        ]);

        // 3. Multi-line Year-over-Year Trend (In vs Out per Month)
        $seriesInTy = [];
        $seriesInLy = [];
        $seriesOutTy = [];
        $seriesOutLy = [];

        // Detect latest month with activity in TY to avoid false zero drops for future unreached months
        $tyRowsWithData = $filteredData->where('yr', $ty)->filter(fn($r) => $r->total_in_qty > 0 || $r->total_out_qty > 0);
        $maxMonthTy = $tyRowsWithData->max('mnth') ?? 12;

        for ($m = 1; $m <= 12; $m++) {
            $mRowsTy = $filteredData->where('yr', $ty)->where('mnth', $m);
            $mRowsLy = $filteredData->where('yr', $ly)->where('mnth', $m);

            $inTyVal  = (int) $mRowsTy->sum('total_in_qty');
            $outTyVal = (int) $mRowsTy->sum('total_out_qty');
            $inLyVal  = (int) $mRowsLy->sum('total_in_qty');
            $outLyVal = (int) $mRowsLy->sum('total_out_qty');

            if ($m > $maxMonthTy) {
                $seriesInTy[]  = null;
                $seriesOutTy[] = null;
            } else {
                $seriesInTy[]  = $inTyVal;
                $seriesOutTy[] = $outTyVal;
            }

            $seriesInLy[]  = $inLyVal;
            $seriesOutLy[] = $outLyVal;
        }

        $this->chartSalesTrend = json_encode([
            'labels'   => $months,
            'year_ty'  => $ty,
            'year_ly'  => $ly,
            'in_ty'    => $seriesInTy,
            'in_ly'    => $seriesInLy,
            'out_ty'   => $seriesOutTy,
            'out_ly'   => $seriesOutLy,
        ]);

        // 4. Product Contribution Donut (Kategori or Subbrand)
        $prodData = $this->getProductBreakdownData();
        $donutDim = !empty($this->selectedKategory) ? 'subbrand' : 'kategory';
        $groupedKat = $prodData->groupBy($donutDim);
        
        $items = [];
        foreach ($groupedKat as $name => $rows) {
            $inSum  = (int) $rows->sum('total_in');
            $outSum = (int) $rows->sum('total_out');
            $totSum = $inSum + $outSum;

            if ($totSum > 0) {
                $items[] = [
                    'name'  => $name,
                    'in'    => $inSum,
                    'out'   => $outSum,
                    'total' => $totSum,
                ];
            }
        }

        // Sort descending by total volume
        usort($items, fn($a, $b) => $b['total'] <=> $a['total']);

        // Limit slices to max 6 + "LAINNYA" (total 7 slices) to keep donut clean and readable
        $maxSlices = 6;
        if (count($items) > $maxSlices + 1) {
            $topItems = array_slice($items, 0, $maxSlices);
            $otherItems = array_slice($items, $maxSlices);

            $otherIn  = array_sum(array_column($otherItems, 'in'));
            $otherOut = array_sum(array_column($otherItems, 'out'));

            $topItems[] = [
                'name'  => 'LAINNYA',
                'in'    => $otherIn,
                'out'   => $otherOut,
                'total' => $otherIn + $otherOut,
            ];
            $finalItems = $topItems;
        } else {
            $finalItems = $items;
        }

        $donutLabels = [];
        $donutSeriesIn = [];
        $donutSeriesOut = [];
        $donutSeriesTotal = [];
        $donutDetails = [];

        foreach ($finalItems as $it) {
            $donutLabels[]       = $it['name'];
            $donutSeriesIn[]     = $it['in'];
            $donutSeriesOut[]    = $it['out'];
            $donutSeriesTotal[]  = $it['total'];

            $donutDetails[] = [
                'kategory' => $it['name'],
                'in'       => $it['in'],
                'out'      => $it['out'],
                'total'    => $it['total']
            ];
        }

        if (empty($donutLabels)) {
            $donutLabels = ['No Data'];
            $donutSeriesIn = [0];
            $donutSeriesOut = [0];
            $donutSeriesTotal = [0];
        }

        $this->chartContribution = json_encode([
            'labels'     => $donutLabels,
            'series_in'  => $donutSeriesIn,
            'series_out' => $donutSeriesOut,
            'series_tot' => $donutSeriesTotal,
            'total_in'   => array_sum($donutSeriesIn),
            'total_out'  => array_sum($donutSeriesOut),
            'total_all'  => array_sum($donutSeriesTotal),
            'details'    => $donutDetails
        ]);

        // 5. Leaflet Map Data (deprecated - replaced by monthly bar 1:3 ratio)
        $this->chartMapData = '[]';
    }

    private function loadPivotData()
    {
        $ty = (int)$this->selectedYear;
        $ly = $ty - 1;

        $monthsKey = implode('-', $this->selectedMonths);
        $cacheKey = "qty_subbrand_pivot_{$ty}_{$monthsKey}_{$this->selectedProdukLine}_{$this->selectedKategory}_{$this->selectedTopitem}_{$this->selectedSubbrand}_{$this->breakdownBy}_{$this->filterRegion}_{$this->filterArea}_{$this->filterSupervisor}_{$this->filterCabang}";

        $rows = Cache::remember($cacheKey, now()->addHours(6), function() use ($ty, $ly) {
            $whereProd = [];
            $bindings = [];
            
            if (!empty($this->selectedProdukLine) && in_array($this->selectedProdukLine, ['REG', 'FEST'])) {
                $whereProd[] = "p.produk_line = ?";
                $bindings[] = $this->selectedProdukLine;
            }
            if (!empty($this->selectedKategory)) {
                $whereProd[] = "p.kategory = ?";
                $bindings[] = $this->selectedKategory;
            }
            if (!empty($this->selectedTopitem)) {
                $whereProd[] = "p.topitem = ?";
                $bindings[] = $this->selectedTopitem;
            }
            if (!empty($this->selectedSubbrand)) {
                $whereProd[] = "p.subbrand = ?";
                $bindings[] = $this->selectedSubbrand;
            }

            $whereTerritory = [];
            if ($this->breakdownBy === 'Region' && !empty($this->filterRegion)) {
                $whereTerritory[] = "s.region = ?";
                $bindings[] = $this->filterRegion;
            } elseif ($this->breakdownBy === 'Area' && !empty($this->filterArea)) {
                $whereTerritory[] = "s.area = ?";
                $bindings[] = $this->filterArea;
            } elseif ($this->breakdownBy === 'Supervisor' && !empty($this->filterSupervisor)) {
                $spvDistributors = DB::table('master_distributors as md')
                    ->leftJoin('master_supervisors as ms', 'md.supervisor_code', '=', 'ms.supervisor_code')
                    ->whereRaw("COALESCE(ms.description, md.supervisor_name, 'VACANT') = ?", [$this->filterSupervisor])
                    ->pluck('md.distributor_code')
                    ->toArray();

                if (!empty($spvDistributors)) {
                    $distInList = "'" . implode("','", array_map('addslashes', $spvDistributors)) . "'";
                    $whereTerritory[] = "s.kd_distributor IN ({$distInList})";
                } else {
                    $whereTerritory[] = "1 = 0";
                }
            } elseif ($this->breakdownBy === 'Cabang' && !empty($this->filterCabang)) {
                $whereTerritory[] = "s.cabang = ?";
                $bindings[] = $this->filterCabang;
            }

            // Access Filter (Cabang Level)
            $allowedCabangs = [];
            $user = auth()->user();
            if ($user && $user->getAccessLevel() !== 'nasional') {
                $masterQuery = DB::table('vw_distributor_hierarchy');
                $level = $user->getAccessLevel();
                if ($level === 'supervisor') {
                    $masterQuery->where('supervisor_code', $user->supervisor_code);
                } elseif ($level === 'area') {
                    $masterQuery->whereIn('area_code', (array)$user->area_code);
                } elseif ($level === 'region') {
                    $masterQuery->whereIn('region_code', (array)$user->region_code);
                }
                $allowedCabangs = $masterQuery->distinct()->pluck('branch_name')->toArray();
            }

            $cabangFilterSql = '';
            if ($user && $user->getAccessLevel() !== 'nasional') {
                if (empty($allowedCabangs)) {
                    $cabangFilterSql = " AND s.cabang = 'INVALID_ACCESS_NO_CABANG'";
                } else {
                    $cabangListStr = "'" . implode("','", array_map('addslashes', $allowedCabangs)) . "'";
                    $cabangFilterSql = " AND s.cabang IN ({$cabangListStr})";
                }
            }

            $prodWhereSql = !empty($whereProd) ? ' AND ' . implode(' AND ', $whereProd) : '';
            $terrWhereSql = !empty($whereTerritory) ? ' AND ' . implode(' AND ', $whereTerritory) : '';

            $sql = "
                WITH produk_dedup AS (
                    SELECT DISTINCT ON (divisi)
                        divisi,
                        produk_line,
                        kategory,
                        subbrand,
                        topitem
                    FROM master_produk_lama
                    ORDER BY divisi, (CASE WHEN status_product = 'Aktif' THEN 1 ELSE 2 END)
                ),
                raw_in AS (
                    SELECT 
                        EXTRACT(YEAR FROM s.bulan)::int as yr,
                        EXTRACT(MONTH FROM s.bulan)::int as mnth,
                        COALESCE(p.subbrand, 'OTHER') as subbrand,
                        SUM(s.qty_ctn) as in_qty
                    FROM sellin_per_cabang_per_qty s
                    LEFT JOIN produk_dedup p ON s.nama_produk_mapping = p.divisi
                    WHERE s.bulan >= '{$ly}-01-01' AND s.bulan <= '{$ty}-12-31'
                      AND s.region != 'HOINA'
                      {$cabangFilterSql}
                      {$terrWhereSql}
                      {$prodWhereSql}
                    GROUP BY 1, 2, 3
                ),
                raw_out AS (
                    SELECT 
                        EXTRACT(YEAR FROM s.bulan)::int as yr,
                        EXTRACT(MONTH FROM s.bulan)::int as mnth,
                        COALESCE(p.subbrand, 'OTHER') as subbrand,
                        SUM(s.qty_ctn) as out_qty
                    FROM sellout_per_cabang_per_qty s
                    LEFT JOIN produk_dedup p ON s.nama_produk_mapping = p.divisi
                    WHERE s.bulan >= '{$ly}-01-01' AND s.bulan <= '{$ty}-12-31'
                      AND s.region != 'HOINA'
                      {$cabangFilterSql}
                      {$terrWhereSql}
                      {$prodWhereSql}
                    GROUP BY 1, 2, 3
                ),
                unioned AS (
                    SELECT yr, mnth, subbrand, in_qty, 0 as out_qty FROM raw_in
                    UNION ALL
                    SELECT yr, mnth, subbrand, 0 as in_qty, out_qty FROM raw_out
                )
                SELECT 
                    yr,
                    mnth,
                    subbrand,
                    SUM(in_qty) as total_in_qty,
                    SUM(out_qty) as total_out_qty
                FROM unioned
                GROUP BY yr, mnth, subbrand
                ORDER BY subbrand
            ";

            $allBindings = array_merge($bindings, $bindings);
            return DB::select($sql, $allBindings);
        });

        $pivot = [];
        $grandTotal = [];
        $maxMonth = 1;

        foreach ($rows as $row) {
            $m = (int)$row->mnth;
            $y = (int)$row->yr;
            if ($y === $ty && ((float)$row->total_in_qty > 0 || (float)$row->total_out_qty > 0) && $m > $maxMonth) {
                $maxMonth = $m;
            }
        }
        $this->pivotMaxMonth = $maxMonth;

        for ($m = 1; $m <= 12; $m++) {
            $grandTotal[$m] = ['in' => 0, 'out' => 0];
        }
        $grandTotal['YTD'] = ['in' => 0, 'out' => 0];

        foreach ($rows as $row) {
            $sb = $row->subbrand;
            $m  = (int)$row->mnth;
            $y  = (int)$row->yr;
            
            if ($y !== $ty) continue;
            if ($m < 1 || $m > 12) continue;

            if (!isset($pivot[$sb])) {
                $pivot[$sb] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $pivot[$sb][$i] = ['in' => 0, 'out' => 0];
                }
                $pivot[$sb]['YTD'] = ['in' => 0, 'out' => 0];
            }

            $inVal  = (float)$row->total_in_qty;
            $outVal = (float)$row->total_out_qty;

            $pivot[$sb][$m]['in']  += $inVal;
            $pivot[$sb][$m]['out'] += $outVal;
            $grandTotal[$m]['in']  += $inVal;
            $grandTotal[$m]['out'] += $outVal;

            $isIncludedInTotal = !empty($this->selectedMonths) 
                ? in_array($m, $this->selectedMonths) 
                : ($m <= $maxMonth);

            if ($isIncludedInTotal) {
                $pivot[$sb]['YTD']['in']  += $inVal;
                $pivot[$sb]['YTD']['out'] += $outVal;
                $grandTotal['YTD']['in']  += $inVal;
                $grandTotal['YTD']['out'] += $outVal;
            }
        }

        ksort($pivot);

        $this->pivotDataJson = json_encode(empty($pivot) ? new \stdClass() : $pivot);
        $this->pivotGrandTotalJson = json_encode($grandTotal);
    }

    private function loadTableData()
    {
        $filteredData = $this->getFilteredTerritoryData();
        $ty = (int)$this->selectedYear;

        $ytdMaxMonth = $filteredData->where('yr', $ty)->where('total_in_qty', '>', 0)->max('mnth') ?: 12;
        $monthScopedData = !empty($this->selectedMonths) 
            ? $filteredData->whereIn('mnth', $this->selectedMonths) 
            : $filteredData->where('mnth', '<=', $ytdMaxMonth);

        // Top Cabang In & Out
        $groupedCabang = $monthScopedData->where('yr', $ty)->groupBy('branch_name');
        $cabangSummaries = [];
        foreach ($groupedCabang as $c => $rows) {
            $cabangSummaries[] = [
                'cabang' => $c,
                'in'     => (float) $rows->sum('total_in_qty'),
                'out'    => (float) $rows->sum('total_out_qty'),
            ];
        }

        $cabangCol = collect($cabangSummaries);
        $this->topCabangIn  = $cabangCol->sortByDesc('in')->take(10)->values()->toArray();
        $this->topCabangOut = $cabangCol->sortByDesc('out')->take(10)->values()->toArray();

        // Top Subbrand
        $prodData = $this->getProductBreakdownData();
        $groupedSubbrand = $prodData->groupBy('subbrand');
        $subbrandList = [];
        foreach ($groupedSubbrand as $sb => $rows) {
            $subbrandList[] = [
                'subbrand' => $sb,
                'in'       => (float) $rows->sum('total_in'),
                'out'      => (float) $rows->sum('total_out'),
                'total'    => (float) $rows->sum('total_vol')
            ];
        }
        $this->topSubbrand = collect($subbrandList)->sortByDesc('total')->take(10)->values()->toArray();

        // Top Kategori
        $groupedKat = $prodData->groupBy('kategory');
        $katList = [];
        foreach ($groupedKat as $k => $rows) {
            $katList[] = [
                'kategory' => $k,
                'in'       => (float) $rows->sum('total_in'),
                'out'      => (float) $rows->sum('total_out'),
                'total'    => (float) $rows->sum('total_vol')
            ];
        }
        $this->topKategori = collect($katList)->sortByDesc('total')->values()->toArray();
    }

    private function resolveLabels(): array
    {
        $filteredData = $this->getFilteredTerritoryData();
        $ty = (int)$this->selectedYear;
        $activeYearData = $filteredData->where('yr', $ty);

        switch ($this->breakdownBy) {
            case 'Nasional':
                return $activeYearData->pluck('region_name')->filter()->unique()->values()->toArray();
            case 'Region':
                return empty($this->filterRegion)
                    ? $activeYearData->pluck('region_name')->filter()->unique()->values()->toArray()
                    : $activeYearData->pluck('area_name')->filter()->unique()->values()->toArray();
            case 'Area':
                return empty($this->filterArea)
                    ? $activeYearData->pluck('area_name')->filter()->unique()->values()->toArray()
                    : $activeYearData->pluck('supervisor_name')->filter()->unique()->values()->toArray();
            case 'Supervisor':
                return empty($this->filterSupervisor)
                    ? $activeYearData->pluck('supervisor_name')->filter()->unique()->values()->toArray()
                    : $activeYearData->pluck('branch_name')->filter()->unique()->values()->toArray();
            case 'Cabang':
                return $activeYearData->pluck('branch_name')->filter()->unique()->values()->toArray();
            default:
                return ['No Data'];
        }
    }

    public function getBreakdownLabelProperty(): string
    {
        switch ($this->breakdownBy) {
            case 'Nasional':
                return 'Nasional (Region)';
            case 'Region':
                return !empty($this->filterRegion) ? "Area ({$this->filterRegion})" : 'Region';
            case 'Area':
                return !empty($this->filterArea) ? "Supervisor ({$this->filterArea})" : 'Area';
            case 'Supervisor':
                return !empty($this->filterSupervisor) ? "Cabang ({$this->filterSupervisor})" : 'Supervisor';
            case 'Cabang':
                return !empty($this->filterCabang) ? "Cabang ({$this->filterCabang})" : 'Cabang';
            default:
                return $this->breakdownBy;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.v2.qty.index', [
            'pivotData'       => json_decode($this->pivotDataJson, true) ?: [],
            'pivotGrandTotal' => json_decode($this->pivotGrandTotalJson, true) ?: []
        ])->layout('layouts.app');
    }
}
