<?php

namespace App\Livewire\SisoAnalytics;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\MasterDistributor;
use App\Models\ProductMaster;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SisoAnalyticsExport;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Filter Utama
    public $selectedYear = 2026;
    public $selectedMonth = 8;
    public $selectedRegFest = 'ALL'; // 'ALL' | 'REG' | 'FEST'
    public $selectedStatus = 'ALL';  // 'ALL' | 'balanced' | 'high_out' | 'low_out' | 'no_out' | 'no_in'
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    public $selectedBrand = '';
    public $search = '';
    public $unitMode = 'ktn'; // 'ktn' (Karton) vs 'val' (Rupiah)

    // View Mode ('product' vs 'distributor')
    public $viewMode = 'product';

    // Sorting Table
    public $sortField = 'gap_qty';
    public $sortDirection = 'desc';

    // Tab Navigasi
    public $activeTab = 'siso';

    // Dropdown Data
    public $years = [2024, 2025, 2026, 2027];
    public $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    public $regionOptions = [];
    public $areaOptions = [];
    public $distributorOptions = [];
    public $brandOptions = [];

    /**
     * Ambil level akses wilayah user saat ini ('nasional' | 'region' | 'area' | 'supervisor').
     */
    protected function getCurrentAccessLevel(): string
    {
        $user = auth()->user();
        if (!$user) {
            return 'nasional';
        }
        return method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';
    }

    /**
     * Terapkan batasan akses wilayah user (Row-Level Security sesuai hierarki organisasi).
     */
    private function applyRegionAccess($query, $tableAlias = 'md')
    {
        $user = auth()->user();
        if (!$user) {
            return $query;
        }

        $level = $this->getCurrentAccessLevel();
        if ($level === 'nasional') {
            return $query;
        }

        if ($level === 'region') {
            $regions = array_values(array_filter((array)($user->region_code ?? [])));
            if (!empty($regions)) {
                $query->whereIn("{$tableAlias}.region_code", $regions);
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        if ($level === 'area') {
            $areas = array_values(array_filter((array)($user->area_code ?? [])));
            if (!empty($areas)) {
                $query->whereIn("{$tableAlias}.area_code", $areas);
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        if ($level === 'supervisor') {
            $spvCode = trim((string)($user->supervisor_code ?? ''));
            if ($spvCode !== '') {
                $query->where(function ($q) use ($tableAlias, $spvCode) {
                    $q->where("{$tableAlias}.supervisor_code", $spvCode)
                      ->orWhereIn("{$tableAlias}.supervisor_code", function ($sub) use ($spvCode) {
                          $sub->select('siso_code')
                              ->from('team_elite_code_mappings')
                              ->whereRaw('TRIM(team_elite_code) = TRIM(?)', [$spvCode])
                              ->whereNotNull('siso_code')
                              ->where('siso_code', '!=', '');
                      });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        return $query;
    }

    /**
     * Terapkan filter Brand berdasarkan master produk (sinkron untuk tabel SI & SO).
     */
    private function applyBrandFilter($query, $productCodeColumn)
    {
        if (!empty($this->selectedBrand)) {
            $brand = $this->selectedBrand;
            $query->whereIn($productCodeColumn, function ($sub) use ($brand) {
                $sub->select('product_id')
                    ->from('product_masters')
                    ->where('brand_name', $brand);
            });
        }

        return $query;
    }

    public function mount()
    {
        $latestPeriod = Cache::remember('siso_analytics_latest_period', 600, function () {
            $curYearStart = date('Y') . '-01-01';
            $row = DB::table('t_sellingout')
                ->where('INVOICE_DATE', '>=', $curYearStart)
                ->select('THN', 'BLN')
                ->orderByDesc('THN')
                ->orderByDesc(DB::raw('"BLN"::int'))
                ->first();

            if (!$row) {
                $row = DB::table('t_sellingout')
                    ->select('THN', 'BLN')
                    ->orderByDesc('THN')
                    ->orderByDesc(DB::raw('"BLN"::int'))
                    ->first();
            }
            return $row;
        });

        if ($latestPeriod) {
            $this->selectedYear = (int)$latestPeriod->THN;
            $this->selectedMonth = (int)$latestPeriod->BLN;
        }

        $this->loadDropdownOptions();
    }

    public function loadDropdownOptions()
    {
        $mdQuery = DB::table('master_distributors as md')
            ->where('is_active', true)
            ->select('distributor_code', 'distributor_name', 'region_name', 'area_name');
        $this->applyRegionAccess($mdQuery, 'md');
        $allDist = $mdQuery->orderBy('distributor_name')->get();

        $this->regionOptions = $allDist->pluck('region_name')
            ->filter(fn($v) => !empty($v))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Auto-select & lock Region jika user hanya memiliki akses ke 1 Region, atau reset jika di luar cakupan
        if (count($this->regionOptions) === 1) {
            $this->selectedRegion = $this->regionOptions[0];
        } elseif (!empty($this->selectedRegion) && !in_array($this->selectedRegion, $this->regionOptions, true)) {
            $this->selectedRegion = '';
        }

        $filteredForArea = $this->selectedRegion
            ? $allDist->where('region_name', $this->selectedRegion)
            : $allDist;

        $this->areaOptions = $filteredForArea->pluck('area_name')
            ->filter(fn($v) => !empty($v))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Auto-select & lock Area jika hanya ada 1 Area dalam cakupan akses user, atau reset jika tidak valid
        if (count($this->areaOptions) === 1) {
            $this->selectedArea = $this->areaOptions[0];
        } elseif (!empty($this->selectedArea) && !in_array($this->selectedArea, $this->areaOptions, true)) {
            $this->selectedArea = '';
        }

        $filteredForDist = $filteredForArea;
        if ($this->selectedArea) {
            $filteredForDist = $filteredForDist->where('area_name', $this->selectedArea);
        }

        $this->distributorOptions = $filteredForDist->map(function ($d) {
            return (object)[
                'distributor_code' => $d->distributor_code,
                'distributor_name' => $d->distributor_name,
            ];
        })->values()->toArray();

        $allowedDistCodes = array_map(fn($d) => $d->distributor_code, $this->distributorOptions);
        if (count($allowedDistCodes) === 1) {
            $this->selectedDistributor = $allowedDistCodes[0];
        } elseif (!empty($this->selectedDistributor) && !in_array($this->selectedDistributor, $allowedDistCodes, true)) {
            $this->selectedDistributor = '';
        }

        $this->brandOptions = Cache::remember('siso_analytics_brand_options', 600, function () {
            return DB::table('product_masters')
                ->where('is_active', true)
                ->whereNotNull('brand_name')
                ->distinct()
                ->pluck('brand_name')
                ->sort()
                ->values()
                ->toArray();
        });
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedRegion()
    {
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->loadDropdownOptions();
        $this->resetPage();
    }

    public function updatedSelectedArea()
    {
        $this->selectedDistributor = '';
        $this->loadDropdownOptions();
        $this->resetPage();
    }

    public function updatedSelectedDistributor()
    {
        $this->resetPage();
    }

    public function updatedSelectedRegFest()
    {
        $this->resetPage();
    }

    public function updatedSelectedBrand()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function setUnitMode($mode)
    {
        $this->unitMode = $mode;
        if ($mode === 'val' && $this->sortField === 'gap_qty') {
            $this->sortField = 'gap_val';
        } elseif ($mode === 'ktn' && $this->sortField === 'gap_val') {
            $this->sortField = 'gap_qty';
        }
    }

    public function setRegFest($type)
    {
        $this->selectedRegFest = $type;
        $this->resetPage();
    }

    public function setStatus($status)
    {
        if ($status === 'ALL') {
            $this->selectedStatus = 'ALL';
        } else {
            $this->selectedStatus = ($this->selectedStatus === $status) ? 'ALL' : $status;
        }
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedBrand = '';
        $this->selectedRegFest = 'ALL';
        $this->selectedStatus = 'ALL';
        $this->search = '';
        $this->loadDropdownOptions();
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    // Request-level Memoization Caches
    protected $cachedMonthlyRaw = null;
    protected $cachedMonthlyKey = null;
    protected $cachedProductBase = null;
    protected $cachedProductKey = null;
    protected $cachedDistributorBase = null;
    protected $cachedDistributorBaseKey = null;
    protected $cachedBranchTargets = null;
    protected $cachedBranchTargetsKey = null;
    protected $cachedComparisonData = null;
    protected $cachedComparisonKey = null;

    protected function getMonthDateRange(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));
        return [$start, $end];
    }

    protected function getUserAccessCachePrefix(): string
    {
        $user = auth()->user();
        if (!$user) {
            return 'siso_guest';
        }
        $level = $this->getCurrentAccessLevel();
        $scopeHash = substr(md5(json_encode([
            $level,
            $user->region_code ?? [],
            $user->area_code ?? [],
            $user->supervisor_code ?? '',
        ])), 0, 10);

        return 'siso_u' . $user->id . '_' . $scopeHash;
    }

    protected function getBranchTargetsCached(int $year, int $month): array
    {
        $key = "{$year}|{$month}|{$this->selectedRegFest}";
        if ($this->cachedBranchTargetsKey === $key && $this->cachedBranchTargets !== null) {
            return $this->cachedBranchTargets;
        }

        $cacheKey = 'siso_branch_targets_' . md5($key);
        $this->cachedBranchTargets = Cache::remember($cacheKey, 180, function () use ($year, $month) {
            $targetDate = sprintf('%04d-%02d-01', $year, $month);
            $targetQuery = DB::table('target_per_depo')
                ->where('bulan', $targetDate);

            if ($this->selectedRegFest !== 'ALL') {
                $targetQuery->where('reg_fest', $this->selectedRegFest);
            }

            return $targetQuery
                ->selectRaw('cabang, SUM(target) as total_target')
                ->groupBy('cabang')
                ->pluck('total_target', 'cabang')
                ->toArray();
        });
        $this->cachedBranchTargetsKey = $key;

        return $this->cachedBranchTargets;
    }

    protected function getDistributorBaseCached(int $year, int $month)
    {
        $key = implode('|', [$this->getUserAccessCachePrefix(), $year, $month, $this->selectedRegFest, $this->selectedBrand]);
        if ($this->cachedDistributorBaseKey === $key && $this->cachedDistributorBase !== null) {
            return $this->cachedDistributorBase;
        }

        $cacheKey = 'siso_dist_base_' . md5($key);
        $rows = Cache::remember($cacheKey, 180, function () use ($year, $month) {
            [$startDate, $endDate] = $this->getMonthDateRange($year, $month);

            $si = DB::table('selling_ins as si')
                ->whereBetween('si.invoice_date', [$startDate, $endDate]);
            if ($this->selectedRegFest !== 'ALL') $si->where('si.reg_fes', $this->selectedRegFest);
            $this->applyBrandFilter($si, 'si.kode_barang');

            $siSub = $si->select(
                'si.distributor_code',
                DB::raw('SUM(si.qty) as qty_in_ktn'),
                DB::raw('SUM(si.total) as value_in')
            )->groupBy('si.distributor_code');

            $so = DB::table('t_sellingout as so')
                ->whereBetween('so.INVOICE_DATE', [$startDate, $endDate])
                ->where('so.THN', (string)$year)
                ->where('so.BLN', (string)$month);
            if ($this->selectedRegFest !== 'ALL') $so->where('so.REG_FEST', $this->selectedRegFest);
            $this->applyBrandFilter($so, 'so.KDITEMPRC');

            $soSub = $so->select(
                'so.KDDIST as distributor_code',
                DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out_ktn'),
                DB::raw('SUM("so"."NETT") as value_out')
            )->groupBy('so.KDDIST');

            $query = DB::table('master_distributors as md')
                ->leftJoinSub($siSub, 'si', 'si.distributor_code', '=', 'md.distributor_code')
                ->leftJoinSub($soSub, 'so', 'so.distributor_code', '=', 'md.distributor_code')
                ->where('md.is_active', true);

            $this->applyRegionAccess($query, 'md');

            return $query->select(
                'md.distributor_code',
                'md.distributor_name',
                'md.region_name',
                'md.area_name',
                'md.branch_name',
                DB::raw('COALESCE(si.qty_in_ktn, 0) as qty_in_ktn'),
                DB::raw('COALESCE(si.value_in, 0) as value_in'),
                DB::raw('COALESCE(so.qty_out_ktn, 0) as qty_out_ktn'),
                DB::raw('COALESCE(so.value_out, 0) as value_out')
            )->get()->toArray();
        });

        $this->cachedDistributorBase = collect($rows);
        $this->cachedDistributorBaseKey = $key;

        return $this->cachedDistributorBase;
    }

    protected function getMonthlyRawCached(int $year): array
    {
        $key = implode('|', [
            $this->getUserAccessCachePrefix(),
            $year,
            $this->selectedRegFest,
            $this->selectedRegion,
            $this->selectedArea,
            $this->selectedDistributor,
            $this->selectedBrand,
        ]);

        if ($this->cachedMonthlyKey === $key && $this->cachedMonthlyRaw !== null) {
            return $this->cachedMonthlyRaw;
        }

        $distBase = $this->getDistributorBaseCached($year, (int)$this->selectedMonth);
        $filteredDist = $distBase;
        if ($this->selectedRegion) $filteredDist = $filteredDist->where('region_name', $this->selectedRegion);
        if ($this->selectedArea) $filteredDist = $filteredDist->where('area_name', $this->selectedArea);
        if ($this->selectedDistributor) $filteredDist = $filteredDist->where('distributor_code', $this->selectedDistributor);
        $activeBranches = $filteredDist->pluck('branch_name')->filter()->unique()->values()->toArray();

        $cacheKey = 'siso_monthly_raw_' . md5($key);
        $cachedData = Cache::remember($cacheKey, 180, function () use ($year, $activeBranches) {
            $startOfYear = "{$year}-01-01";
            $endOfYear = "{$year}-12-31";

            // Sell In 12 Bulan (dengan Partition Pruning pada invoice_date)
            $si = DB::table('selling_ins as si')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
                ->whereBetween('si.invoice_date', [$startOfYear, $endOfYear])
                ->where('md.is_active', true);

            if ($this->selectedRegion) $si->where('md.region_name', $this->selectedRegion);
            if ($this->selectedArea) $si->where('md.area_name', $this->selectedArea);
            if ($this->selectedDistributor) $si->where('si.distributor_code', $this->selectedDistributor);
            if ($this->selectedRegFest !== 'ALL') $si->where('si.reg_fes', $this->selectedRegFest);
            $this->applyBrandFilter($si, 'si.kode_barang');
            $this->applyRegionAccess($si, 'md');

            $siData = $si->select(
                DB::raw('EXTRACT(MONTH FROM si.invoice_date)::int as bln'),
                DB::raw('SUM(si.qty) as qty_in'),
                DB::raw('SUM(si.total) as val_in')
            )->groupBy('bln')->get()->keyBy('bln')->toArray();

            // Sell Out 12 Bulan (dengan Partition Pruning pada INVOICE_DATE)
            $so = DB::table('t_sellingout as so')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
                ->whereBetween('so.INVOICE_DATE', [$startOfYear, $endOfYear])
                ->where('so.THN', (string)$year)
                ->where('md.is_active', true);

            if ($this->selectedRegion) $so->where('md.region_name', $this->selectedRegion);
            if ($this->selectedArea) $so->where('md.area_name', $this->selectedArea);
            if ($this->selectedDistributor) $so->where('so.KDDIST', $this->selectedDistributor);
            if ($this->selectedRegFest !== 'ALL') $so->where('so.REG_FEST', $this->selectedRegFest);
            $this->applyBrandFilter($so, 'so.KDITEMPRC');
            $this->applyRegionAccess($so, 'md');

            $soData = $so->select(
                DB::raw('"so"."BLN"::int as bln'),
                DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out'),
                DB::raw('SUM("so"."NETT") as val_out')
            )->groupBy('bln')->get()->keyBy('bln')->toArray();

            $targetQuery = DB::table('target_per_depo')
                ->whereBetween('bulan', [$startOfYear, $endOfYear])
                ->whereIn('cabang', $activeBranches);

            if ($this->selectedRegFest !== 'ALL') {
                $targetQuery->where('reg_fest', $this->selectedRegFest);
            }

            $targetRaw = $targetQuery
                ->selectRaw('EXTRACT(MONTH FROM bulan)::int as bln, SUM(target) as total_target')
                ->groupBy('bln')
                ->get()
                ->keyBy('bln')
                ->toArray();

            return [
                'siData' => $siData,
                'soData' => $soData,
                'targetRaw' => $targetRaw,
                'activeBranches' => $activeBranches,
            ];
        });

        $this->cachedMonthlyRaw = $cachedData;
        $this->cachedMonthlyKey = $key;

        return $this->cachedMonthlyRaw;
    }

    /**
     * Data Grafik 1: Tren Bulanan Sell In vs Sell Out (Jan s/d Des).
     */
    public function getMonthlyTrendProperty()
    {
        $year = (int)$this->selectedYear;
        $raw = $this->getMonthlyRawCached($year);
        $siData = $raw['siData'];
        $soData = $raw['soData'];
        $targetRaw = $raw['targetRaw'];

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];
        $seriesTarget = [];
        $seriesRatio = [];
        $seriesAchIn = [];
        $seriesAchOut = [];
        $seriesGrowthIn = [];

        $prevInVal = 0;
        $prevOutVal = 0;

        for ($m = 1; $m <= 12; $m++) {
            $categories[] = $this->months[$m] ?? "Bln {$m}";
            
            $inQty = (float)($siData[$m]->qty_in ?? 0);
            $inVal = (float)($siData[$m]->val_in ?? 0);
            
            $outQty = (float)($soData[$m]->qty_out ?? 0);
            $outVal = (float)($soData[$m]->val_out ?? 0);

            $targetVal = (float)($targetRaw[$m]->total_target ?? 0);

            $seriesIn[] = $this->unitMode === 'ktn' ? round($inQty, 1) : round($inVal, 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round($outQty, 1) : round($outVal, 0);
            $seriesTarget[] = round($targetVal, 0);

            // Rasio Sell Out terhadap Sell In mengikuti unitMode
            $inMetric = $this->unitMode === 'val' ? $inVal : $inQty;
            $outMetric = $this->unitMode === 'val' ? $outVal : $outQty;
            $ratio = $inMetric > 0 ? round(($outMetric / $inMetric) * 100, 1) : null;
            $seriesRatio[] = $ratio;

            // % Pencapaian Target (Sell In vs Target)
            $achIn = ($targetVal > 0 && $inVal > 0) ? round(($inVal / $targetVal) * 100, 1) : null;
            $seriesAchIn[] = $achIn;

            // % Pencapaian Target (Sell Out vs Target)
            $achOut = ($targetVal > 0 && $outVal > 0) ? round(($outVal / $targetVal) * 100, 1) : null;
            $seriesAchOut[] = $achOut;

            // Pertumbuhan MoM (Sell In)
            $growthIn = ($prevInVal > 0 && $inVal > 0) ? round((($inVal - $prevInVal) / $prevInVal) * 100, 1) : null;
            $seriesGrowthIn[] = $growthIn;

            if ($inVal > 0) $prevInVal = $inVal;
            if ($outVal > 0) $prevOutVal = $outVal;
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
            'seriesTarget' => $seriesTarget,
            'seriesRatio' => $seriesRatio,
            'seriesAchIn' => $seriesAchIn,
            'seriesAchOut' => $seriesAchOut,
            'seriesGrowthIn' => $seriesGrowthIn,
        ];
    }

    /**
     * Data Grafik 2: Komparasi per Sub Brand (Khusus Top Item) — Dihitung langsung dari Product Base Cache (0 SQL Tambahan).
     */
    public function getSubBrandComparisonProperty()
    {
        $all = $this->getProductBaseCached((int)$this->selectedYear, (int)$this->selectedMonth);

        $subBrandTotals = [];

        foreach ($all as $row) {
            $sbIn = trim($row->si_subbrand ?? '');
            $qin = (float)($row->topitem_qty_in ?? 0);
            $vin = (float)($row->topitem_val_in ?? 0);

            if ($sbIn !== '' && ($qin > 0 || $vin > 0)) {
                if (!isset($subBrandTotals[$sbIn])) {
                    $subBrandTotals[$sbIn] = ['subbrand' => $sbIn, 'qty_in' => 0.0, 'val_in' => 0.0, 'qty_out' => 0.0, 'val_out' => 0.0];
                }
                $subBrandTotals[$sbIn]['qty_in'] += $qin;
                $subBrandTotals[$sbIn]['val_in'] += $vin;
            }

            $sbOut = trim($row->so_subbrand ?? '');
            $qout = (float)($row->topitem_qty_out ?? 0);
            $vout = (float)($row->topitem_val_out ?? 0);

            if ($sbOut !== '' && ($qout > 0 || $vout > 0)) {
                if (!isset($subBrandTotals[$sbOut])) {
                    $subBrandTotals[$sbOut] = ['subbrand' => $sbOut, 'qty_in' => 0.0, 'val_in' => 0.0, 'qty_out' => 0.0, 'val_out' => 0.0];
                }
                $subBrandTotals[$sbOut]['qty_out'] += $qout;
                $subBrandTotals[$sbOut]['val_out'] += $vout;
            }
        }

        $sorted = collect($subBrandTotals)->map(function ($item) {
            $item['total_qty'] = $item['qty_in'] + $item['qty_out'];
            $item['total_val'] = $item['val_in'] + $item['val_out'];
            return $item;
        })->sortByDesc(function ($item) {
            return $this->unitMode === 'val' ? $item['total_val'] : $item['total_qty'];
        })->take(8);

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];

        foreach ($sorted as $item) {
            $categories[] = $item['subbrand'];
            $seriesIn[] = $this->unitMode === 'ktn' ? round($item['qty_in'], 1) : round($item['val_in'], 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round($item['qty_out'], 1) : round($item['val_out'], 0);
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
        ];
    }

    public function getBrandComparisonProperty()
    {
        return $this->getSubBrandComparisonProperty();
    }

    /**
     * Data Grafik 3: Smart Auto-Drilldown Sebaran Wilayah (Dihitung dari Distributor Base Cache — 0 SQL Tambahan).
     */
    public function getRegionComparisonProperty()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;
        $distBase = $this->getDistributorBaseCached($year, $month);

        $accessLevel = $this->getCurrentAccessLevel();
        $user = auth()->user();

        // LEVEL 3 & 4: Filter Distributor dipilih, Filter Area dipilih, ATAU Login sebagai Supervisor
        if (!empty($this->selectedDistributor) || !empty($this->selectedArea) || $accessLevel === 'supervisor') {
            $peerRegion = $this->selectedRegion;
            $peerArea = $this->selectedArea;

            if (!empty($this->selectedDistributor) && (empty($peerArea) || empty($peerRegion))) {
                $distInfo = $distBase->firstWhere('distributor_code', $this->selectedDistributor);
                if ($distInfo) {
                    $peerRegion = $peerRegion ?: ($distInfo->region_name ?? '');
                    $peerArea = $peerArea ?: ($distInfo->area_name ?? '');
                }
            }

            $peers = $distBase->filter(function ($r) use ($peerArea, $peerRegion) {
                if (empty(trim($r->distributor_name ?? ''))) return false;
                if (!empty($peerArea)) return $r->area_name === $peerArea;
                if (!empty($peerRegion)) return $r->region_name === $peerRegion;
                return true;
            });

            $allPeers = $peers->filter(function ($r) {
                if (!empty($this->selectedDistributor) && $r->distributor_code === $this->selectedDistributor) {
                    return true;
                }
                return (float)$r->qty_in_ktn > 0 || (float)$r->qty_out_ktn > 0 || (float)$r->value_in > 0 || (float)$r->value_out > 0;
            })->sortByDesc(function ($r) {
                return $this->unitMode === 'val'
                    ? ((float)$r->value_in + (float)$r->value_out)
                    : ((float)$r->qty_in_ktn + (float)$r->qty_out_ktn);
            })->values();

            $totalPeers = $allPeers->count();
            $selectedRank = null;

            if (!empty($this->selectedDistributor)) {
                $foundIdx = $allPeers->search(fn($r) => $r->distributor_code === $this->selectedDistributor);
                if ($foundIdx !== false) {
                    $selectedRank = $foundIdx + 1;
                }
            }

            // Ambil maksimal 8 bar; jika distributor terpilih berada di luar Top 8, sisipkan di urutan ke-8
            $topRows = $allPeers->take(8);
            if (!empty($this->selectedDistributor) && !$topRows->contains('distributor_code', $this->selectedDistributor)) {
                $selectedRow = $allPeers->firstWhere('distributor_code', $this->selectedDistributor);
                if ($selectedRow) {
                    $topRows = $allPeers->take(7)->push($selectedRow)->values();
                }
            }

            $categories = [];
            $fullNames = [];
            $seriesIn = [];
            $seriesOut = [];
            $highlightIndex = null;

            foreach ($topRows->values() as $idx => $r) {
                $isTarget = !empty($this->selectedDistributor) && $r->distributor_code === $this->selectedDistributor;
                $peerRank = $allPeers->search(fn($item) => $item->distributor_code === $r->distributor_code) + 1;

                $rawName = trim($r->distributor_name);
                if ($isTarget) {
                    $highlightIndex = $idx;
                    $shortLbl = '★ ' . (strlen($rawName) > 16 ? substr($rawName, 0, 15) . '..' : $rawName);
                    $fullNames[] = "★ {$rawName} (Peringkat #{$peerRank} dari {$totalPeers} • Terpilih)";
                } else {
                    $shortLbl = strlen($rawName) > 20 ? substr($rawName, 0, 18) . '..' : $rawName;
                    $fullNames[] = "{$rawName} (Peringkat #{$peerRank} dari {$totalPeers})";
                }

                $categories[] = $shortLbl;
                $seriesIn[] = $this->unitMode === 'ktn' ? round((float)$r->qty_in_ktn, 1) : round((float)$r->value_in, 0);
                $seriesOut[] = $this->unitMode === 'ktn' ? round((float)$r->qty_out_ktn, 1) : round((float)$r->value_out, 0);
            }

            if ($accessLevel === 'supervisor') {
                $spvCode = trim((string)($user->supervisor_code ?? ''));
                $title = !empty($this->selectedDistributor)
                    ? 'Benchmark Distributor Supervisor'
                    : 'Sebaran per Distributor';
                $subtitle = 'Supervisor ' . ($spvCode ?: '-') . ($peerArea ? ' • Area ' . $peerArea : '');
            } else {
                $title = !empty($this->selectedDistributor)
                    ? 'Benchmark Distributor Se-Area'
                    : 'Sebaran per Distributor';
                $subtitle = 'Area ' . ($peerArea ?: $peerRegion ?: 'Wilayah');
            }
            $rankBadge = ($selectedRank !== null && $totalPeers > 0)
                ? "Peringkat #{$selectedRank} / {$totalPeers}"
                : null;

            return [
                'title' => $title,
                'subtitle' => $subtitle,
                'rankBadge' => $rankBadge,
                'highlightIndex' => $highlightIndex,
                'fullNames' => $fullNames,
                'categories' => $categories,
                'seriesIn' => $seriesIn,
                'seriesOut' => $seriesOut,
            ];
        }

        // LEVEL 1 (Nasional -> per Region) & LEVEL 2 (Region dipilih -> per Area)
        if (empty($this->selectedRegion)) {
            $groupField = 'region_name';
            $title = 'Sebaran per Region';
            $subtitle = 'Seluruh Wilayah Nasional';
            $sourceRows = $distBase;
        } else {
            $groupField = 'area_name';
            $title = 'Sebaran per Area';
            $subtitle = 'Region ' . $this->selectedRegion;
            $sourceRows = $distBase->where('region_name', $this->selectedRegion);
        }

        $grouped = $sourceRows->filter(fn($r) => !empty(trim($r->{$groupField} ?? '')))
            ->groupBy($groupField)
            ->map(function ($items, $label) {
                $qin = $items->sum('qty_in_ktn');
                $vin = $items->sum('value_in');
                $qout = $items->sum('qty_out_ktn');
                $vout = $items->sum('value_out');
                return (object)[
                    'label_name' => $label,
                    'qty_in' => $qin,
                    'val_in' => $vin,
                    'qty_out' => $qout,
                    'val_out' => $vout,
                    'sort_score' => $this->unitMode === 'val' ? ($vin + $vout) : ($qin + $qout),
                ];
            })
            ->filter(fn($r) => $r->qty_in > 0 || $r->qty_out > 0)
            ->sortByDesc('sort_score')
            ->take(10)
            ->values();

        $categories = [];
        $fullNames = [];
        $seriesIn = [];
        $seriesOut = [];

        foreach ($grouped as $r) {
            $lbl = trim($r->label_name);
            $categories[] = strlen($lbl) > 20 ? substr($lbl, 0, 18) . '..' : $lbl;
            $fullNames[] = $lbl;
            $seriesIn[] = $this->unitMode === 'ktn' ? round((float)$r->qty_in, 1) : round((float)$r->val_in, 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round((float)$r->qty_out, 1) : round((float)$r->val_out, 0);
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'rankBadge' => null,
            'highlightIndex' => null,
            'fullNames' => $fullNames,
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
        ];
    }

    /**
     * Data Grafik 4: Top 8 Sub Brand Penjualan Terbesar (Dihitung dari Product Base Cache — 0 SQL Tambahan).
     */
    public function getTopSubBrandTotalComparisonProperty()
    {
        $all = $this->getProductBaseCached((int)$this->selectedYear, (int)$this->selectedMonth);

        // Kelompokkan per Sub Brand
        $grouped = $all->groupBy(function ($item) {
            return trim($item->sub_brand_name ?? '') ?: 'LAIN-LAIN';
        })->map(function ($items, $subBrand) {
            $qin = $items->sum('qty_in_ktn');
            $vin = $items->sum('value_in');
            $qout = $items->sum('qty_out_ktn');
            $vout = $items->sum('value_out');

            return [
                'sub_brand' => $subBrand,
                'qty_in_ktn' => $qin,
                'value_in' => $vin,
                'qty_out_ktn' => $qout,
                'value_out' => $vout,
                'total_qty' => $qin + $qout,
                'total_val' => $vin + $vout,
            ];
        });

        // Urutkan berdasarkan total volume (ktn) atau total nilai (val)
        $top8 = $grouped->sortByDesc(function ($i) {
            return $this->unitMode === 'val' ? $i['total_val'] : $i['total_qty'];
        })->take(8);

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];

        foreach ($top8 as $item) {
            $name = $item['sub_brand'];
            $categories[] = strlen($name) > 22 ? substr($name, 0, 20) . '..' : $name;
            $seriesIn[] = $this->unitMode === 'ktn' ? round((float)$item['qty_in_ktn'], 1) : round((float)$item['value_in'], 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round((float)$item['qty_out_ktn'], 1) : round((float)$item['value_out'], 0);
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
        ];
    }

    public function getTopSkuComparisonProperty()
    {
        return $this->getTopSubBrandTotalComparisonProperty();
    }

    /**
     * Data Grafik 5: Target vs Realisasi per Depo / Cabang (Dihitung dari Distributor Base & Target Cache — 0 SQL Tambahan).
     */
    public function getDepoTargetComparisonProperty()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;

        $distRows = $this->getDistributorBaseCached($year, $month);
        if ($this->selectedRegion) $distRows = $distRows->where('region_name', $this->selectedRegion);
        if ($this->selectedArea) $distRows = $distRows->where('area_name', $this->selectedArea);
        if ($this->selectedDistributor) $distRows = $distRows->where('distributor_code', $this->selectedDistributor);

        $byBranch = $distRows->filter(fn($r) => !empty(trim($r->branch_name ?? '')))
            ->groupBy('branch_name');

        $allTargets = $this->getBranchTargetsCached($year, $month);

        $allBranches = $byBranch->map(function ($rows, $branch) use ($allTargets) {
            $qin = (float)$rows->sum('qty_in_ktn');
            $vin = (float)$rows->sum('value_in');
            $qout = (float)$rows->sum('qty_out_ktn');
            $vout = (float)$rows->sum('value_out');
            $tval = (float)($allTargets[$branch] ?? 0);

            return [
                'branch' => $branch,
                'qty_in' => $qin,
                'val_in' => $vin,
                'qty_out' => $qout,
                'val_out' => $vout,
                'target_val' => $tval,
                'ach_in_pct' => $tval > 0 ? round(($vin / $tval) * 100, 1) : null,
                'ratio_pct' => $qin > 0 ? round(($qout / $qin) * 100, 1) : 0,
                'sort_score' => $this->unitMode === 'val' ? max($vin, $vout, $tval) : ($qin + $qout),
            ];
        })->filter(function ($item) {
            return $item['qty_in'] > 0 || $item['qty_out'] > 0 || $item['target_val'] > 0;
        })->sortByDesc('sort_score')->take(8)->values();

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];
        $seriesTarget = [];
        $achInList = [];

        foreach ($allBranches as $b) {
            $name = $b['branch'];
            $categories[] = strlen($name) > 18 ? substr($name, 0, 16) . '..' : $name;
            $seriesIn[] = $this->unitMode === 'ktn' ? round($b['qty_in'], 1) : round($b['val_in'], 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round($b['qty_out'], 1) : round($b['val_out'], 0);
            $seriesTarget[] = round($b['target_val'], 0);
            $achInList[] = $b['ach_in_pct'];
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
            'seriesTarget' => $seriesTarget,
            'achInList' => $achInList,
        ];
    }

    /**
     * Data Grafik 6: Donut Distribusi Kesehatan Serapan (Status Breakdown).
     */
    public function getStatusDistributionProperty()
    {
        $all = $this->getComparisonData();

        $counts = [
            'balanced' => $all->where('status', 'balanced')->count(),
            'high_out' => $all->where('status', 'high_out')->count(),
            'low_out'  => $all->where('status', 'low_out')->count(),
            'no_out'   => $all->where('status', 'no_out')->count(),
            'no_in'    => $all->where('status', 'no_in')->count(),
        ];

        $total = array_sum($counts);
        $balancedPct = $total > 0 ? round(($counts['balanced'] / $total) * 100, 1) : 0;

        return [
            'labels' => ['Seimbang (75-125%)', 'Sell Out Tinggi (>125%)', 'Sell In Tinggi (<75%)', '0 Sell Out', '0 Sell In'],
            'shortLabels' => ['Seimbang', 'Sell Out Tinggi', 'Sell In Tinggi', '0 Sell Out', '0 Sell In'],
            'keys' => ['balanced', 'high_out', 'low_out', 'no_out', 'no_in'],
            'series' => array_values($counts),
            'colors' => ['#10b981', '#0ea5e9', '#f59e0b', '#64748b', '#a855f7'],
            'total' => $total,
            'balancedPct' => $balancedPct,
        ];
    }

    /**
     * Data Grafik 7: Diverging Bar Top Gap / Bottleneck (In minus Out).
     */
    public function getGapBottleneckProperty()
    {
        $all = $this->getComparisonData();
        $isVal = $this->unitMode === 'val';
        $gapField = $isVal ? 'gap_val' : 'gap_qty';

        // Top 4 Positive Gap (Sell In > Sell Out / Tertahan di Depo)
        $topOverSupply = $all->filter(fn($i) => ($i->{$gapField} ?? 0) > 0)
            ->sortByDesc($gapField)
            ->take(4);

        // Top 4 Negative Gap (Sell Out > Sell In / Serapan Tinggi)
        $topHighDemand = $all->filter(fn($i) => ($i->{$gapField} ?? 0) < 0)
            ->sortBy($gapField)
            ->take(4)
            ->reverse();

        $combined = $topOverSupply->concat($topHighDemand)->values();

        $categories = [];
        $fullNames = [];
        $series = [];
        $inValues = [];
        $outValues = [];

        foreach ($combined as $row) {
            $rawName = $this->viewMode === 'product'
                ? ($row->product_name ?? $row->product_id)
                : ($row->distributor_name ?? $row->distributor_code);

            $fullNames[] = $rawName;
            $categories[] = strlen($rawName) > 20 ? substr($rawName, 0, 18) . '..' : $rawName;

            $gapVal = (float)($row->{$gapField} ?? 0);
            $series[] = $isVal ? round($gapVal, 0) : round($gapVal, 1);
            $inValues[] = $isVal ? round((float)$row->value_in, 0) : round((float)$row->qty_in_ktn, 1);
            $outValues[] = $isVal ? round((float)$row->value_out, 0) : round((float)$row->qty_out_ktn, 1);
        }

        return [
            'categories' => $categories,
            'fullNames' => $fullNames,
            'series' => $series,
            'inValues' => $inValues,
            'outValues' => $outValues,
        ];
    }

    /**
     * Data Dasar Komparasi (Tanpa Filter Search & Status Tabel agar KPI & Grafik Akurat).
     */
    protected function getComparisonData()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;
        $cacheKey = implode('|', [
            $year,
            $month,
            $this->viewMode,
            $this->unitMode,
            $this->selectedRegFest,
            $this->selectedRegion,
            $this->selectedArea,
            $this->selectedDistributor,
            $this->selectedBrand,
        ]);

        if ($this->cachedComparisonKey === $cacheKey && $this->cachedComparisonData !== null) {
            return $this->cachedComparisonData;
        }

        if ($this->viewMode === 'product') {
            $this->cachedComparisonData = $this->getProductBaseCached($year, $month);
        } else {
            $this->cachedComparisonData = $this->getDistributorComparisonQuery($year, $month);
        }
        $this->cachedComparisonKey = $cacheKey;

        return $this->cachedComparisonData;
    }

    /**
     * Data Khusus Tabel & Export (Menerapkan Filter Search & Status pada Data Dasar).
     */
    protected function getFilteredTableData()
    {
        $data = $this->getComparisonData();

        if (!empty($this->search)) {
            $q = strtolower(trim($this->search));
            $data = $data->filter(function ($item) use ($q) {
                if ($this->viewMode === 'product') {
                    return str_contains(strtolower($item->product_id ?? ''), $q)
                        || str_contains(strtolower($item->product_name ?? ''), $q)
                        || str_contains(strtolower($item->brand_name ?? ''), $q)
                        || str_contains(strtolower($item->sub_brand_name ?? ''), $q);
                } else {
                    return str_contains(strtolower($item->distributor_code ?? ''), $q)
                        || str_contains(strtolower($item->distributor_name ?? ''), $q)
                        || str_contains(strtolower($item->branch_name ?? ''), $q)
                        || str_contains(strtolower($item->area_name ?? ''), $q);
                }
            })->values();
        }

        if (!empty($this->selectedStatus) && $this->selectedStatus !== 'ALL') {
            $data = $data->filter(function ($item) {
                return ($item->status ?? '') === $this->selectedStatus;
            })->values();
        }

        return $data;
    }

    protected function getProductBaseCached(int $year, int $month)
    {
        $key = implode('|', [
            $year,
            $month,
            $this->unitMode,
            $this->selectedRegFest,
            $this->selectedRegion,
            $this->selectedArea,
            $this->selectedDistributor,
            $this->selectedBrand,
        ]);

        if ($this->cachedProductKey === $key && $this->cachedProductBase !== null) {
            return $this->cachedProductBase;
        }

        $this->cachedProductBase = $this->getProductComparisonQuery($year, $month);
        $this->cachedProductKey = $key;

        return $this->cachedProductBase;
    }

    protected function getProductComparisonQuery($year, $month)
    {
        $rawCacheKey = 'siso_prod_raw_' . md5(implode('|', [
            $this->getUserAccessCachePrefix(),
            (int)$year,
            (int)$month,
            $this->selectedRegFest,
            $this->selectedRegion,
            $this->selectedArea,
            $this->selectedDistributor,
            $this->selectedBrand,
        ]));

        $rawRows = Cache::remember($rawCacheKey, 180, function () use ($year, $month) {
            [$startDate, $endDate] = $this->getMonthDateRange((int)$year, (int)$month);

            $sellIn = DB::table('selling_ins as si')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
                ->whereBetween('si.invoice_date', [$startDate, $endDate])
                ->where('md.is_active', true);

            if ($this->selectedRegion) $sellIn->where('md.region_name', $this->selectedRegion);
            if ($this->selectedArea) $sellIn->where('md.area_name', $this->selectedArea);
            if ($this->selectedDistributor) $sellIn->where('si.distributor_code', $this->selectedDistributor);
            if ($this->selectedRegFest !== 'ALL') $sellIn->where('si.reg_fes', $this->selectedRegFest);
            $this->applyBrandFilter($sellIn, 'si.kode_barang');
            $this->applyRegionAccess($sellIn, 'md');

            $sellInSub = $sellIn->select(
                'si.kode_barang as product_id',
                DB::raw('MAX(si.reg_fes) as reg_fest'),
                DB::raw('MAX(si.subbrand) as si_subbrand'),
                DB::raw('SUM(si.qty) as qty_in_ktn'),
                DB::raw('SUM(si.total) as value_in'),
                DB::raw("SUM(CASE WHEN si.topitem IS NOT NULL AND si.topitem != '' AND si.topitem != 'OTHER' THEN si.qty ELSE 0 END) as topitem_qty_in"),
                DB::raw("SUM(CASE WHEN si.topitem IS NOT NULL AND si.topitem != '' AND si.topitem != 'OTHER' THEN si.total ELSE 0 END) as topitem_val_in")
            )->groupBy('si.kode_barang');

            $sellOut = DB::table('t_sellingout as so')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
                ->whereBetween('so.INVOICE_DATE', [$startDate, $endDate])
                ->where('so.THN', (string)$year)
                ->where('so.BLN', (string)$month)
                ->where('md.is_active', true);

            if ($this->selectedRegion) $sellOut->where('md.region_name', $this->selectedRegion);
            if ($this->selectedArea) $sellOut->where('md.area_name', $this->selectedArea);
            if ($this->selectedDistributor) $sellOut->where('so.KDDIST', $this->selectedDistributor);
            if ($this->selectedRegFest !== 'ALL') $sellOut->where('so.REG_FEST', $this->selectedRegFest);
            $this->applyBrandFilter($sellOut, 'so.KDITEMPRC');
            $this->applyRegionAccess($sellOut, 'md');

            $sellOutSub = $sellOut->select(
                'so.KDITEMPRC as product_id',
                DB::raw('MAX("so"."REG_FEST") as reg_fest'),
                DB::raw('MAX("so"."SUBBRAND") as so_subbrand'),
                DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out_ktn'),
                DB::raw('SUM("so"."NETT") as value_out'),
                DB::raw('SUM(CASE WHEN "so"."TOPITEM" IS NOT NULL AND "so"."TOPITEM" != \'\' AND "so"."TOPITEM" != \'OTHER\' THEN "so"."TTL_QTY_KTN" ELSE 0 END) as topitem_qty_out'),
                DB::raw('SUM(CASE WHEN "so"."TOPITEM" IS NOT NULL AND "so"."TOPITEM" != \'\' AND "so"."TOPITEM" != \'OTHER\' THEN "so"."NETT" ELSE 0 END) as topitem_val_out')
            )->groupBy('so.KDITEMPRC');

            $query = DB::table('product_masters as pm')
                ->leftJoinSub($sellInSub, 'si', 'si.product_id', '=', 'pm.product_id')
                ->leftJoinSub($sellOutSub, 'so', 'so.product_id', '=', 'pm.product_id')
                ->where(function ($q) {
                    $q->whereNotNull('si.qty_in_ktn')
                      ->orWhereNotNull('so.qty_out_ktn');
                });

            if ($this->selectedBrand) {
                $query->where('pm.brand_name', $this->selectedBrand);
            }

            return $query->select(
                'pm.product_id',
                'pm.product_name',
                'pm.brand_name',
                'pm.sub_brand_name',
                DB::raw("COALESCE(si.reg_fest, so.reg_fest, '-') as reg_fest"),
                'si.si_subbrand',
                'so.so_subbrand',
                DB::raw('COALESCE(si.qty_in_ktn, 0) as qty_in_ktn'),
                DB::raw('COALESCE(si.value_in, 0) as value_in'),
                DB::raw('COALESCE(so.qty_out_ktn, 0) as qty_out_ktn'),
                DB::raw('COALESCE(so.value_out, 0) as value_out'),
                DB::raw('COALESCE(si.topitem_qty_in, 0) as topitem_qty_in'),
                DB::raw('COALESCE(si.topitem_val_in, 0) as topitem_val_in'),
                DB::raw('COALESCE(so.topitem_qty_out, 0) as topitem_qty_out'),
                DB::raw('COALESCE(so.topitem_val_out, 0) as topitem_val_out')
            )->get()->toArray();
        });

        return collect($rawRows)->map(function ($origRow) {
            $row = clone $origRow;
            $inKtn = (float)$row->qty_in_ktn;
            $outKtn = (float)$row->qty_out_ktn;
            $gapKtn = $inKtn - $outKtn;

            $inVal = (float)$row->value_in;
            $outVal = (float)$row->value_out;
            $gapVal = $inVal - $outVal;

            $inMetric = $this->unitMode === 'val' ? $inVal : $inKtn;
            $outMetric = $this->unitMode === 'val' ? $outVal : $outKtn;
            $ratio = $inMetric > 0 ? round(($outMetric / $inMetric) * 100, 1) : 0.0;

            // Status & Keterangan Murni Sell In vs Sell Out
            if ($outKtn == 0 && $inKtn > 0) {
                $status = 'no_out';
                $statusLabel = '0 Sell Out';
                $badgeClass = 'badge-ghost';
                $analysis = 'Ada Sell In ' . number_format($inKtn, 0, ',', '.') . ' Ktn, tapi belum ada Sell Out.';
            } elseif ($inKtn == 0 && $outKtn > 0) {
                $status = 'no_in';
                $statusLabel = '0 Sell In';
                $badgeClass = 'badge-info';
                $analysis = 'Ada Sell Out ' . number_format($outKtn, 0, ',', '.') . ' Ktn, tanpa ada Sell In bulan ini.';
            } elseif ($ratio > 125) {
                $status = 'high_out';
                $statusLabel = 'Sell Out Tinggi';
                $badgeClass = 'badge-info';
                $analysis = 'Sell Out (' . number_format($outKtn, 0, ',', '.') . ' Ktn) lebih besar dari Sell In (' . number_format($inKtn, 0, ',', '.') . ' Ktn). Rasio: ' . $ratio . '%.';
            } elseif ($ratio < 75) {
                $status = 'low_out';
                $statusLabel = 'Sell In Tinggi';
                $badgeClass = 'badge-warning';
                $analysis = 'Sell In (' . number_format($inKtn, 0, ',', '.') . ' Ktn) lebih besar dari Sell Out (' . number_format($outKtn, 0, ',', '.') . ' Ktn). Rasio: ' . $ratio . '%.';
            } else {
                $status = 'balanced';
                $statusLabel = 'Seimbang';
                $badgeClass = 'badge-success';
                $analysis = 'Sell In dan Sell Out seimbang (Rasio: ' . $ratio . '%).';
            }

            $row->reg_fest = $row->reg_fest ?? '-';
            $row->target_val = 0;
            $row->ach_in_pct = null;
            $row->ach_out_pct = null;
            $row->gap_qty = $gapKtn;
            $row->gap_val = $gapVal;
            $row->ratio = $ratio;
            $row->status = $status;
            $row->status_label = $statusLabel;
            $row->badge_class = $badgeClass;
            $row->analysis_narrative = $analysis;

            return $row;
        });
    }

    protected function getDistributorComparisonQuery($year, $month)
    {
        $distBase = $this->getDistributorBaseCached((int)$year, (int)$month);

        // Hitung jumlah distributor aktif per cabang & total Sell In cabang dari data dasar cabang
        $branchStats = $distBase->filter(fn($r) => (float)$r->qty_in_ktn > 0 || (float)$r->qty_out_ktn > 0 || (float)$r->value_in > 0 || (float)$r->value_out > 0)
            ->groupBy('branch_name')
            ->map(function ($rows) {
                return [
                    'dist_count' => $rows->count(),
                    'branch_in_val' => $rows->sum('value_in'),
                ];
            });

        $filtered = $distBase->filter(function ($r) {
            return (float)$r->qty_in_ktn > 0 || (float)$r->qty_out_ktn > 0 || (float)$r->value_in > 0 || (float)$r->value_out > 0;
        });

        if ($this->selectedRegion) $filtered = $filtered->where('region_name', $this->selectedRegion);
        if ($this->selectedArea) $filtered = $filtered->where('area_name', $this->selectedArea);
        if ($this->selectedDistributor) $filtered = $filtered->where('distributor_code', $this->selectedDistributor);

        $targets = $this->getBranchTargetsCached((int)$year, (int)$month);

        return $filtered->values()->map(function ($origRow) use ($targets, $branchStats) {
            $row = clone $origRow;
            $inKtn = (float)$row->qty_in_ktn;
            $outKtn = (float)$row->qty_out_ktn;
            $gapKtn = $inKtn - $outKtn;

            $inVal = (float)$row->value_in;
            $outVal = (float)$row->value_out;
            $gapVal = $inVal - $outVal;

            $branchTarget = (float)($targets[$row->branch_name] ?? 0);
            $distCount = (int)($branchStats[$row->branch_name]['dist_count'] ?? 1);
            $branchInVal = (float)($branchStats[$row->branch_name]['branch_in_val'] ?? $inVal);

            $achIn = ($branchTarget > 0 && $inVal > 0) ? round(($inVal / $branchTarget) * 100, 1) : null;
            $achOut = ($branchTarget > 0 && $outVal > 0) ? round(($outVal / $branchTarget) * 100, 1) : null;
            $branchAchIn = ($branchTarget > 0 && $branchInVal > 0) ? round(($branchInVal / $branchTarget) * 100, 1) : null;

            $inMetric = $this->unitMode === 'val' ? $inVal : $inKtn;
            $outMetric = $this->unitMode === 'val' ? $outVal : $outKtn;
            $ratio = $inMetric > 0 ? round(($outMetric / $inMetric) * 100, 1) : 0.0;

            if ($outKtn == 0 && $inKtn > 0) {
                $status = 'no_out';
                $statusLabel = '0 Sell Out';
                $badgeClass = 'badge-ghost';
                $analysis = 'Distributor ada Sell In tapi belum ada Sell Out bulan ini.';
            } elseif ($inKtn == 0 && $outKtn > 0) {
                $status = 'no_in';
                $statusLabel = '0 Sell In';
                $badgeClass = 'badge-info';
                $analysis = 'Distributor ada Sell Out tanpa ada Sell In bulan ini.';
            } elseif ($ratio > 125) {
                $status = 'high_out';
                $statusLabel = 'Sell Out Tinggi';
                $badgeClass = 'badge-info';
                $analysis = 'Sell Out distributor lebih besar dari Sell In (Rasio: ' . $ratio . '%).';
            } elseif ($ratio < 75) {
                $status = 'low_out';
                $statusLabel = 'Sell In Tinggi';
                $badgeClass = 'badge-warning';
                $analysis = 'Sell In distributor lebih besar dari Sell Out (Rasio: ' . $ratio . '%).';
            } else {
                $status = 'balanced';
                $statusLabel = 'Seimbang';
                $badgeClass = 'badge-success';
                $analysis = 'Sell In dan Sell Out distributor seimbang.';
            }

            $row->target_val = $branchTarget;
            $row->branch_dist_count = $distCount;
            $row->branch_ach_pct = $branchAchIn;
            $row->ach_in_pct = $achIn;
            $row->ach_out_pct = $achOut;
            $row->gap_qty = $gapKtn;
            $row->gap_val = $gapVal;
            $row->ratio = $ratio;
            $row->status = $status;
            $row->status_label = $statusLabel;
            $row->badge_class = $badgeClass;
            $row->analysis_narrative = $analysis;

            return $row;
        });
    }

    /**
     * Ringkasan Komersial Sell In vs Sell Out (Memanfaatkan Monthly Raw Cache untuk Target & MoM).
     */
    public function getKpiSummaryProperty()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;
        $allData = $this->getComparisonData();

        $totalInKtn = $allData->sum('qty_in_ktn');
        $totalOutKtn = $allData->sum('qty_out_ktn');
        $totalInVal = $allData->sum('value_in');
        $totalOutVal = $allData->sum('value_out');

        $overallRatio = $this->unitMode === 'val'
            ? (($totalInVal > 0) ? round(($totalOutVal / $totalInVal) * 100, 1) : 0)
            : (($totalInKtn > 0) ? round(($totalOutKtn / $totalInKtn) * 100, 1) : 0);

        $netGapKtn = $totalInKtn - $totalOutKtn;
        $netGapVal = $totalInVal - $totalOutVal;

        // Gunakan data bulanan yang sudah di-cache untuk Target bulan aktif & MoM (jika bulan 2..12)
        $monthlyRaw = $this->getMonthlyRawCached($year);
        $totalTargetVal = (float)($monthlyRaw['targetRaw'][$month]->total_target ?? 0);

        // Pencapaian terhadap Target (%)
        $achInPct = $totalTargetVal > 0 ? round(($totalInVal / $totalTargetVal) * 100, 1) : 0;
        $achOutPct = $totalTargetVal > 0 ? round(($totalOutVal / $totalTargetVal) * 100, 1) : 0;

        // Pertumbuhan MoM (dibanding bulan sebelumnya)
        if ($month > 1) {
            $prevMonth = $month - 1;
            $prevInVal = (float)($monthlyRaw['siData'][$prevMonth]->val_in ?? 0);
            $prevOutVal = (float)($monthlyRaw['soData'][$prevMonth]->val_out ?? 0);
        } else {
            $prevMonth = 12;
            $prevYear = $year - 1;
            [$prevStart, $prevEnd] = $this->getMonthDateRange($prevYear, $prevMonth);

            $prevSi = DB::table('selling_ins as si')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
                ->whereBetween('si.invoice_date', [$prevStart, $prevEnd])
                ->where('md.is_active', true);
            if ($this->selectedRegion) $prevSi->where('md.region_name', $this->selectedRegion);
            if ($this->selectedArea) $prevSi->where('md.area_name', $this->selectedArea);
            if ($this->selectedDistributor) $prevSi->where('si.distributor_code', $this->selectedDistributor);
            if ($this->selectedRegFest !== 'ALL') $prevSi->where('si.reg_fes', $this->selectedRegFest);
            $this->applyBrandFilter($prevSi, 'si.kode_barang');
            $this->applyRegionAccess($prevSi, 'md');
            $prevInVal = (float)$prevSi->selectRaw('SUM(si.total) as total')->value('total');

            $prevSo = DB::table('t_sellingout as so')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
                ->whereBetween('so.INVOICE_DATE', [$prevStart, $prevEnd])
                ->where('so.THN', (string)$prevYear)
                ->where('so.BLN', (string)$prevMonth)
                ->where('md.is_active', true);
            if ($this->selectedRegion) $prevSo->where('md.region_name', $this->selectedRegion);
            if ($this->selectedArea) $prevSo->where('md.area_name', $this->selectedArea);
            if ($this->selectedDistributor) $prevSo->where('so.KDDIST', $this->selectedDistributor);
            if ($this->selectedRegFest !== 'ALL') $prevSo->where('so.REG_FEST', $this->selectedRegFest);
            $this->applyBrandFilter($prevSo, 'so.KDITEMPRC');
            $this->applyRegionAccess($prevSo, 'md');
            $prevOutVal = (float)$prevSo->selectRaw('SUM("so"."NETT") as total')->value('total');
        }

        $growthInMoM = ($prevInVal > 0 && $totalInVal > 0) ? round((($totalInVal - $prevInVal) / $prevInVal) * 100, 1) : null;
        $growthOutMoM = ($prevOutVal > 0 && $totalOutVal > 0) ? round((($totalOutVal - $prevOutVal) / $prevOutVal) * 100, 1) : null;

        return [
            'total_in_ktn' => $totalInKtn,
            'total_out_ktn' => $totalOutKtn,
            'total_in_val' => $totalInVal,
            'total_out_val' => $totalOutVal,
            'total_target_val' => $totalTargetVal,
            'overall_ratio' => $overallRatio,
            'net_gap_ktn' => $netGapKtn,
            'net_gap_val' => $netGapVal,
            'ach_in_pct' => $achInPct,
            'ach_out_pct' => $achOutPct,
            'growth_in_mom' => $growthInMoM,
            'growth_out_mom' => $growthOutMoM,
            'total_items' => $allData->count(),
        ];
    }

    public function export()
    {
        $data = $this->getFilteredTableData();
        $filename = "SISO_Penjualan_{$this->selectedYear}_{$this->selectedMonth}_{$this->selectedRegFest}.xlsx";
        return Excel::download(new SisoAnalyticsExport($data, $this->viewMode, $this->selectedYear, $this->selectedMonth, $this->selectedRegFest), $filename);
    }

    public function render()
    {
        $tableData = $this->getFilteredTableData();

        // Sorting
        $sortField = $this->sortField;
        $sortDirection = $this->sortDirection;
        $sorted = $tableData->sortBy(function ($item) use ($sortField) {
            return $item->{$sortField} ?? 0;
        }, SORT_REGULAR, $sortDirection === 'desc');

        // Pagination
        $perPage = 25;
        $currentPage = $this->getPage();
        $paginatedItems = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        $chartSubBrand = $this->getSubBrandComparisonProperty();
        $chartTopSubBrand = $this->getTopSubBrandTotalComparisonProperty();

        return view('livewire.siso-analytics.index', [
            'items' => $paginator,
            'kpi' => $this->getKpiSummaryProperty(),
            'chartMonthly' => $this->getMonthlyTrendProperty(),
            'chartSubBrand' => $chartSubBrand,
            'chartBrand' => $chartSubBrand,
            'chartRegion' => $this->getRegionComparisonProperty(),
            'chartTopSku' => $chartTopSubBrand,
            'chartTopSubBrand' => $chartTopSubBrand,
            'chartDepoTarget' => $this->getDepoTargetComparisonProperty(),
            'chartStatusDonut' => $this->getStatusDistributionProperty(),
            'chartGapBottleneck' => $this->getGapBottleneckProperty(),
            'viewMode' => $this->viewMode,
            'unitMode' => $this->unitMode,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
            'selectedRegFest' => $this->selectedRegFest,
            'selectedStatus' => $this->selectedStatus,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedDistributor' => $this->selectedDistributor,
            'selectedBrand' => $this->selectedBrand,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'regionOptions' => $this->regionOptions,
            'areaOptions' => $this->areaOptions,
            'distributorOptions' => $this->distributorOptions,
            'brandOptions' => $this->brandOptions,
            'years' => $this->years,
            'months' => $this->months,
        ])->layout('layouts.app');
    }
}
