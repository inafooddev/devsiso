<?php

namespace App\Livewire\SisoAnalytics;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
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
     * Terapkan batasan akses wilayah user (Role-based access).
     */
    private function applyRegionAccess($query, $tableAlias = 'md')
    {
        $user = auth()->user();
        if (!$user) return $query;

        if ($user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->region_code) && is_array($user->region_code)) {
            $query->whereIn("{$tableAlias}.region_code", $user->region_code);
        }

        if (!empty($user->area_code) && is_array($user->area_code)) {
            $query->whereIn("{$tableAlias}.area_code", $user->area_code);
        }

        if (!empty($user->supervisor_code)) {
            $query->where("{$tableAlias}.supervisor_code", $user->supervisor_code);
        }

        return $query;
    }

    public function mount()
    {
        $latestPeriod = DB::table('t_sellingout')
            ->select('THN', 'BLN')
            ->orderByDesc('THN')
            ->orderByDesc(DB::raw('"BLN"::int'))
            ->first();

        if ($latestPeriod) {
            $this->selectedYear = (int)$latestPeriod->THN;
            $this->selectedMonth = (int)$latestPeriod->BLN;
        }

        $this->loadDropdownOptions();
    }

    public function loadDropdownOptions()
    {
        $regionQuery = DB::table('master_distributors as md')
            ->where('is_active', true)
            ->whereNotNull('region_name')
            ->where('region_name', '!=', '');
        $this->applyRegionAccess($regionQuery, 'md');
        $this->regionOptions = $regionQuery->distinct()->pluck('region_name')->sort()->values()->toArray();

        $areaQuery = DB::table('master_distributors as md')
            ->where('is_active', true)
            ->whereNotNull('area_name')
            ->where('area_name', '!=', '');
        if ($this->selectedRegion) {
            $areaQuery->where('md.region_name', $this->selectedRegion);
        }
        $this->applyRegionAccess($areaQuery, 'md');
        $this->areaOptions = $areaQuery->distinct()->pluck('area_name')->sort()->values()->toArray();

        $distQuery = DB::table('master_distributors as md')
            ->where('is_active', true)
            ->select('distributor_code', 'distributor_name');
        if ($this->selectedRegion) {
            $distQuery->where('md.region_name', $this->selectedRegion);
        }
        if ($this->selectedArea) {
            $distQuery->where('md.area_name', $this->selectedArea);
        }
        $this->applyRegionAccess($distQuery, 'md');
        $this->distributorOptions = $distQuery->orderBy('distributor_name')->get()->toArray();

        $this->brandOptions = DB::table('product_masters')
            ->where('is_active', true)
            ->whereNotNull('brand_name')
            ->distinct()
            ->pluck('brand_name')
            ->sort()
            ->values()
            ->toArray();
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

    public function updatedSelectedBrand()
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

    /**
     * Data Grafik 1: Tren Bulanan Sell In vs Sell Out (Jan s/d Des).
     */
    public function getMonthlyTrendProperty()
    {
        $year = (int)$this->selectedYear;

        // Sell In 12 Bulan
        $si = DB::table('selling_ins as si')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
            ->whereRaw('EXTRACT(YEAR FROM si.invoice_date) = ?', [$year])
            ->where('md.is_active', true);

        if ($this->selectedRegion) $si->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $si->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $si->where('si.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($si, 'md');

        $siData = $si->select(
            DB::raw('EXTRACT(MONTH FROM si.invoice_date)::int as bln'),
            DB::raw('SUM(si.qty) as qty_in'),
            DB::raw('SUM(si.total) as val_in')
        )->groupBy('bln')->get()->keyBy('bln');

        // Sell Out 12 Bulan
        $so = DB::table('t_sellingout as so')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
            ->where('so.THN', (string)$year)
            ->where('md.is_active', true);

        if ($this->selectedRegion) $so->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $so->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $so->where('so.KDDIST', $this->selectedDistributor);
        $this->applyRegionAccess($so, 'md');

        $soData = $so->select(
            DB::raw('"so"."BLN"::int as bln'),
            DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out'),
            DB::raw('SUM("so"."NETT") as val_out')
        )->groupBy('bln')->get()->keyBy('bln');

        // Target 12 Bulan (target_per_depo)
        $branchesQuery = DB::table('master_distributors as md')
            ->where('md.is_active', true)
            ->whereNotNull('md.branch_name');
        if ($this->selectedRegion) $branchesQuery->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $branchesQuery->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $branchesQuery->where('md.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($branchesQuery, 'md');
        $activeBranches = $branchesQuery->distinct()->pluck('md.branch_name')->toArray();

        $targetRaw = DB::table('target_per_depo')
            ->whereYear('bulan', $year)
            ->whereIn('cabang', $activeBranches)
            ->selectRaw('EXTRACT(MONTH FROM bulan)::int as bln, SUM(target) as total_target')
            ->groupBy('bln')
            ->get()
            ->keyBy('bln');

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

            // Rasio Sell Out terhadap Sell In
            $ratio = $inQty > 0 ? round(($outQty / $inQty) * 100, 1) : null;
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
     * Data Grafik 2: Komparasi per Brand (Sell In vs Sell Out).
     */
    public function getBrandComparisonProperty()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;

        $si = DB::table('selling_ins as si')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
            ->whereRaw('EXTRACT(YEAR FROM si.invoice_date) = ?', [$year])
            ->whereRaw('EXTRACT(MONTH FROM si.invoice_date) = ?', [$month])
            ->where('md.is_active', true);
        if ($this->selectedRegion) $si->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $si->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $si->where('si.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($si, 'md');

        $siSub = $si->select('si.kode_barang', DB::raw('SUM(si.qty) as qty_in'), DB::raw('SUM(si.total) as val_in'))
            ->groupBy('si.kode_barang');

        $so = DB::table('t_sellingout as so')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
            ->where('so.THN', (string)$year)
            ->where('so.BLN', (string)$month)
            ->where('md.is_active', true);
        if ($this->selectedRegion) $so->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $so->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $so->where('so.KDDIST', $this->selectedDistributor);
        $this->applyRegionAccess($so, 'md');

        $soSub = $so->select('so.KDITEMPRC', DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out'), DB::raw('SUM("so"."NETT") as val_out'))
            ->groupBy('so.KDITEMPRC');

        $brands = DB::table('product_masters as pm')
            ->leftJoinSub($siSub, 'si', 'si.kode_barang', '=', 'pm.product_id')
            ->leftJoinSub($soSub, 'so', 'so.KDITEMPRC', '=', 'pm.product_id')
            ->whereNotNull('pm.brand_name')
            ->where('pm.brand_name', '!=', '')
            ->select(
                'pm.brand_name',
                DB::raw('COALESCE(SUM(si.qty_in), 0) as qty_in'),
                DB::raw('COALESCE(SUM(si.val_in), 0) as val_in'),
                DB::raw('COALESCE(SUM(so.qty_out), 0) as qty_out'),
                DB::raw('COALESCE(SUM(so.val_out), 0) as val_out')
            )
            ->groupBy('pm.brand_name')
            ->orderByDesc(DB::raw('COALESCE(SUM(si.qty_in), 0) + COALESCE(SUM(so.qty_out), 0)'))
            ->limit(7)
            ->get();

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];

        foreach ($brands as $b) {
            $categories[] = $b->brand_name;
            $seriesIn[] = $this->unitMode === 'ktn' ? round((float)$b->qty_in, 1) : round((float)$b->val_in, 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round((float)$b->qty_out, 1) : round((float)$b->val_out, 0);
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
        ];
    }

    /**
     * Data Grafik 3: Sebaran Wilayah / Region (Sell In vs Sell Out).
     */
    public function getRegionComparisonProperty()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;

        $si = DB::table('selling_ins as si')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
            ->whereRaw('EXTRACT(YEAR FROM si.invoice_date) = ?', [$year])
            ->whereRaw('EXTRACT(MONTH FROM si.invoice_date) = ?', [$month])
            ->where('md.is_active', true);
        $this->applyRegionAccess($si, 'md');

        $siSub = $si->select('si.distributor_code', DB::raw('SUM(si.qty) as qty_in'), DB::raw('SUM(si.total) as val_in'))
            ->groupBy('si.distributor_code');

        $so = DB::table('t_sellingout as so')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
            ->where('so.THN', (string)$year)
            ->where('so.BLN', (string)$month)
            ->where('md.is_active', true);
        $this->applyRegionAccess($so, 'md');

        $soSub = $so->select('so.KDDIST', DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out'), DB::raw('SUM("so"."NETT") as val_out'))
            ->groupBy('so.KDDIST');

        $regions = DB::table('master_distributors as md')
            ->leftJoinSub($siSub, 'si', 'si.distributor_code', '=', 'md.distributor_code')
            ->leftJoinSub($soSub, 'so', 'so.KDDIST', '=', 'md.distributor_code')
            ->where('md.is_active', true)
            ->whereNotNull('md.region_name')
            ->where('md.region_name', '!=', '')
            ->select(
                'md.region_name',
                DB::raw('COALESCE(SUM(si.qty_in), 0) as qty_in'),
                DB::raw('COALESCE(SUM(si.val_in), 0) as val_in'),
                DB::raw('COALESCE(SUM(so.qty_out), 0) as qty_out'),
                DB::raw('COALESCE(SUM(so.val_out), 0) as val_out')
            )
            ->groupBy('md.region_name')
            ->orderByDesc(DB::raw('COALESCE(SUM(si.qty_in), 0) + COALESCE(SUM(so.qty_out), 0)'))
            ->get();

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];

        foreach ($regions as $r) {
            $categories[] = $r->region_name;
            $seriesIn[] = $this->unitMode === 'ktn' ? round((float)$r->qty_in, 1) : round((float)$r->val_in, 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round((float)$r->qty_out, 1) : round((float)$r->val_out, 0);
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
        ];
    }

    /**
     * Data Grafik 4: Top 8 SKU Volume Penjualan Terbesar.
     */
    public function getTopSkuComparisonProperty()
    {
        $all = $this->getProductComparisonQuery((int)$this->selectedYear, (int)$this->selectedMonth);
        $top8 = $all->sortByDesc(function ($i) {
            return $i->qty_in_ktn + $i->qty_out_ktn;
        })->take(8);

        $categories = [];
        $seriesIn = [];
        $seriesOut = [];

        foreach ($top8 as $item) {
            $name = $item->product_name ?? $item->product_id;
            $categories[] = strlen($name) > 22 ? substr($name, 0, 20) . '..' : $name;
            $seriesIn[] = $this->unitMode === 'ktn' ? round((float)$item->qty_in_ktn, 1) : round((float)$item->value_in, 0);
            $seriesOut[] = $this->unitMode === 'ktn' ? round((float)$item->qty_out_ktn, 1) : round((float)$item->value_out, 0);
        }

        return [
            'categories' => $categories,
            'seriesIn' => $seriesIn,
            'seriesOut' => $seriesOut,
        ];
    }

    /**
     * Data Tabel & Perhitungan (Sell In vs Sell Out Tanpa Hubungan Stok).
     */
    protected function getComparisonData()
    {
        $year = (int)$this->selectedYear;
        $month = (int)$this->selectedMonth;

        if ($this->viewMode === 'product') {
            return $this->getProductComparisonQuery($year, $month);
        } else {
            return $this->getDistributorComparisonQuery($year, $month);
        }
    }

    protected function getProductComparisonQuery($year, $month)
    {
        $sellIn = DB::table('selling_ins as si')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
            ->whereRaw('EXTRACT(YEAR FROM si.invoice_date) = ?', [$year])
            ->whereRaw('EXTRACT(MONTH FROM si.invoice_date) = ?', [$month])
            ->where('md.is_active', true);

        if ($this->selectedRegion) $sellIn->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $sellIn->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $sellIn->where('si.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($sellIn, 'md');

        $sellInSub = $sellIn->select(
            'si.kode_barang as product_id',
            DB::raw('SUM(si.qty) as qty_in_ktn'),
            DB::raw('SUM(si.total) as value_in')
        )->groupBy('si.kode_barang');

        $sellOut = DB::table('t_sellingout as so')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
            ->where('so.THN', (string)$year)
            ->where('so.BLN', (string)$month)
            ->where('md.is_active', true);

        if ($this->selectedRegion) $sellOut->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $sellOut->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $sellOut->where('so.KDDIST', $this->selectedDistributor);
        $this->applyRegionAccess($sellOut, 'md');

        $sellOutSub = $sellOut->select(
            'so.KDITEMPRC as product_id',
            DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out_ktn'),
            DB::raw('SUM("so"."NETT") as value_out')
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

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(pm.product_id) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(pm.product_name) LIKE ?', [$search]);
            });
        }

        $rawResults = $query->select(
            'pm.product_id',
            'pm.product_name',
            'pm.brand_name',
            'pm.sub_brand_name',
            DB::raw('COALESCE(si.qty_in_ktn, 0) as qty_in_ktn'),
            DB::raw('COALESCE(si.value_in, 0) as value_in'),
            DB::raw('COALESCE(so.qty_out_ktn, 0) as qty_out_ktn'),
            DB::raw('COALESCE(so.value_out, 0) as value_out')
        )->get();

        return $rawResults->map(function ($row) {
            $inKtn = (float)$row->qty_in_ktn;
            $outKtn = (float)$row->qty_out_ktn;
            $gapKtn = $inKtn - $outKtn;

            $inVal = (float)$row->value_in;
            $outVal = (float)$row->value_out;
            $gapVal = $inVal - $outVal;

            $ratio = $inKtn > 0 ? round(($outKtn / $inKtn) * 100, 1) : ($outKtn > 0 ? 999.0 : 0.0);

            // Status & Keterangan Murni Sell In vs Sell Out
            if ($outKtn == 0 && $inKtn > 0) {
                $status = 'no_out';
                $statusLabel = '0 Sell Out';
                $badgeClass = 'badge-ghost';
                $analysis = 'Ada Sell In ' . number_format($inKtn, 0, ',', '.') . ' Ktn, tapi belum ada Sell Out.';
            } elseif ($inKtn == 0 && $outKtn > 0) {
                $status = 'high_out';
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
        $sellIn = DB::table('selling_ins as si')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
            ->whereRaw('EXTRACT(YEAR FROM si.invoice_date) = ?', [$year])
            ->whereRaw('EXTRACT(MONTH FROM si.invoice_date) = ?', [$month])
            ->where('md.is_active', true);

        if ($this->selectedRegion) $sellIn->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $sellIn->where('md.area_name', $this->selectedArea);
        $this->applyRegionAccess($sellIn, 'md');

        $sellInSub = $sellIn->select(
            'si.distributor_code',
            DB::raw('SUM(si.qty) as qty_in_ktn'),
            DB::raw('SUM(si.total) as value_in')
        )->groupBy('si.distributor_code');

        $sellOut = DB::table('t_sellingout as so')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
            ->where('so.THN', (string)$year)
            ->where('so.BLN', (string)$month)
            ->where('md.is_active', true);

        if ($this->selectedRegion) $sellOut->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $sellOut->where('md.area_name', $this->selectedArea);
        $this->applyRegionAccess($sellOut, 'md');

        $sellOutSub = $sellOut->select(
            'so.KDDIST as distributor_code',
            DB::raw('SUM("so"."TTL_QTY_KTN") as qty_out_ktn'),
            DB::raw('SUM("so"."NETT") as value_out')
        )->groupBy('so.KDDIST');

        $query = DB::table('master_distributors as md')
            ->leftJoinSub($sellInSub, 'si', 'si.distributor_code', '=', 'md.distributor_code')
            ->leftJoinSub($sellOutSub, 'so', 'so.distributor_code', '=', 'md.distributor_code')
            ->where('md.is_active', true)
            ->where(function ($q) {
                $q->whereNotNull('si.qty_in_ktn')
                  ->orWhereNotNull('so.qty_out_ktn');
            });

        if ($this->selectedRegion) $query->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $query->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $query->where('md.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($query, 'md');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(md.distributor_code) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(md.distributor_name) LIKE ?', [$search]);
            });
        }

        $rawResults = $query->select(
            'md.distributor_code',
            'md.distributor_name',
            'md.region_name',
            'md.area_name',
            'md.branch_name',
            DB::raw('COALESCE(si.qty_in_ktn, 0) as qty_in_ktn'),
            DB::raw('COALESCE(si.value_in, 0) as value_in'),
            DB::raw('COALESCE(so.qty_out_ktn, 0) as qty_out_ktn'),
            DB::raw('COALESCE(so.value_out, 0) as value_out')
        )->get();

        // Target per cabang untuk bulan berjalan
        $targetDate = sprintf('%04d-%02d-01', $year, $month);
        $targets = DB::table('target_per_depo')
            ->where('bulan', $targetDate)
            ->selectRaw('cabang, SUM(target) as total_target')
            ->groupBy('cabang')
            ->pluck('total_target', 'cabang')
            ->toArray();

        return $rawResults->map(function ($row) use ($targets) {
            $inKtn = (float)$row->qty_in_ktn;
            $outKtn = (float)$row->qty_out_ktn;
            $gapKtn = $inKtn - $outKtn;

            $inVal = (float)$row->value_in;
            $outVal = (float)$row->value_out;
            $gapVal = $inVal - $outVal;

            $branchTarget = (float)($targets[$row->branch_name] ?? 0);
            $achIn = ($branchTarget > 0 && $inVal > 0) ? round(($inVal / $branchTarget) * 100, 1) : null;
            $achOut = ($branchTarget > 0 && $outVal > 0) ? round(($outVal / $branchTarget) * 100, 1) : null;

            $ratio = $inKtn > 0 ? round(($outKtn / $inKtn) * 100, 1) : ($outKtn > 0 ? 999.0 : 0.0);

            if ($outKtn == 0 && $inKtn > 0) {
                $status = 'no_out';
                $statusLabel = '0 Sell Out';
                $badgeClass = 'badge-ghost';
                $analysis = 'Distributor ada Sell In tapi belum ada Sell Out bulan ini.';
            } elseif ($inKtn == 0 && $outKtn > 0) {
                $status = 'high_out';
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
     * Ringkasan Komersial Sell In vs Sell Out (Bebas Kata Stok).
     */
    public function getKpiSummaryProperty()
    {
        $allData = $this->getComparisonData();

        $totalInKtn = $allData->sum('qty_in_ktn');
        $totalOutKtn = $allData->sum('qty_out_ktn');
        $totalInVal = $allData->sum('value_in');
        $totalOutVal = $allData->sum('value_out');

        $overallRatio = ($totalInKtn > 0) ? round(($totalOutKtn / $totalInKtn) * 100, 1) : 0;
        $netGapKtn = $totalInKtn - $totalOutKtn;
        $netGapVal = $totalInVal - $totalOutVal;

        // Target Bulanan dari target_per_depo
        $branchesQuery = DB::table('master_distributors as md')
            ->where('md.is_active', true)
            ->whereNotNull('md.branch_name');
        if ($this->selectedRegion) $branchesQuery->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $branchesQuery->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $branchesQuery->where('md.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($branchesQuery, 'md');
        $activeBranches = $branchesQuery->distinct()->pluck('md.branch_name')->toArray();

        $targetDate = sprintf('%04d-%02d-01', $this->selectedYear, $this->selectedMonth);
        $totalTargetVal = (float)DB::table('target_per_depo')
            ->where('bulan', $targetDate)
            ->whereIn('cabang', $activeBranches)
            ->sum('target');

        // Pencapaian terhadap Target (%)
        $achInPct = $totalTargetVal > 0 ? round(($totalInVal / $totalTargetVal) * 100, 1) : 0;
        $achOutPct = $totalTargetVal > 0 ? round(($totalOutVal / $totalTargetVal) * 100, 1) : 0;

        // Pertumbuhan MoM (dibanding bulan sebelumnya)
        $prevMonth = $this->selectedMonth == 1 ? 12 : $this->selectedMonth - 1;
        $prevYear = $this->selectedMonth == 1 ? $this->selectedYear - 1 : $this->selectedYear;

        $prevSi = DB::table('selling_ins as si')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'si.distributor_code')
            ->whereRaw('EXTRACT(YEAR FROM si.invoice_date) = ?', [$prevYear])
            ->whereRaw('EXTRACT(MONTH FROM si.invoice_date) = ?', [$prevMonth])
            ->where('md.is_active', true);
        if ($this->selectedRegion) $prevSi->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $prevSi->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $prevSi->where('si.distributor_code', $this->selectedDistributor);
        $this->applyRegionAccess($prevSi, 'md');
        $prevInVal = (float)$prevSi->selectRaw('SUM(si.total) as total')->value('total');

        $prevSo = DB::table('t_sellingout as so')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
            ->where('so.THN', (string)$prevYear)
            ->where('so.BLN', (string)$prevMonth)
            ->where('md.is_active', true);
        if ($this->selectedRegion) $prevSo->where('md.region_name', $this->selectedRegion);
        if ($this->selectedArea) $prevSo->where('md.area_name', $this->selectedArea);
        if ($this->selectedDistributor) $prevSo->where('so.KDDIST', $this->selectedDistributor);
        $this->applyRegionAccess($prevSo, 'md');
        $prevOutVal = (float)$prevSo->selectRaw('SUM("so"."NETT") as total')->value('total');

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
        $data = $this->getComparisonData();
        $filename = "SISO_Penjualan_{$this->selectedYear}_{$this->selectedMonth}.xlsx";
        return Excel::download(new SisoAnalyticsExport($data, $this->viewMode, $this->selectedYear, $this->selectedMonth), $filename);
    }

    public function render()
    {
        $allData = $this->getComparisonData();

        // Sorting
        $sortField = $this->sortField;
        $sortDirection = $this->sortDirection;
        $sorted = $allData->sortBy(function ($item) use ($sortField) {
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

        return view('livewire.siso-analytics.index', [
            'items' => $paginator,
            'kpi' => $this->kpiSummary,
            'chartMonthly' => $this->monthlyTrend,
            'chartBrand' => $this->brandComparison,
            'chartRegion' => $this->regionComparison,
            'chartTopSku' => $this->topSkuComparison,
            'viewMode' => $this->viewMode,
            'unitMode' => $this->unitMode,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
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
