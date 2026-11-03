<?php

namespace App\Livewire\SisoAnalytics;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\SalesDiagnosticService;
use App\Exports\RekomPoExport;
use Maatwebsite\Excel\Facades\Excel;

class RekomPo extends Component
{
    use WithPagination;

    // Filter Periode
    public int $selectedYear = 2026;
    public int $selectedMonth = 10;

    // Filter Teritorial & Pencarian
    public string $selectedRegion = '';
    public string $selectedArea = '';
    public string $selectedSupervisor = '';
    public string $selectedDistributor = '';
    public string $search = '';

    // Filter Kecepatan & Kesehatan Stok Distributor
    public string $selectedSpeedFilter = 'ALL';         // 'ALL', 'FAST', 'MEDIUM', 'SLOW'
    public string $selectedStockStatusFilter = 'ALL';   // 'ALL', 'UNDER_STOCK', 'SAFE', 'OVER_STOCK'

    // Pagination & Sorting
    public int $perPage = 25;
    public string $sortField = 'total_rekom_po_ktn';
    public string $sortDirection = 'desc';

    // Akses Level User
    public string $userAccessLevel = 'nasional';
    public bool $isRegionLocked = false;
    public bool $isAreaLocked = false;

    // State Modal Detail Rekomendasi PO per Distributor
    public bool $showPoModal = false;
    public ?string $selectedDistributorCode = null;
    public array $selectedDistributorData = [];
    public array $poDetailList = [];
    public string $poDetailFilter = 'BUTUH_PO';         // 'BUTUH_PO' (stok < 120%), 'ALL' (semua SKU)
    public array $poDetailSummary = [];

    protected $queryString = [
        'selectedYear'              => ['except' => 0],
        'selectedMonth'             => ['except' => 0],
        'selectedRegion'            => ['except' => ''],
        'selectedArea'              => ['except' => ''],
        'selectedDistributor'       => ['except' => ''],
        'selectedSpeedFilter'       => ['except' => 'ALL'],
        'selectedStockStatusFilter' => ['except' => 'ALL'],
        'search'                    => ['except' => ''],
    ];

    public function mount()
    {
        $this->resolveUserAccess();

        $latest = DB::table('distributor_stocks')
            ->select('tahun', 'bulan')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if ($latest) {
            $this->selectedYear = (int)$latest->tahun;
            $this->selectedMonth = (int)$latest->bulan;
        } else {
            $this->selectedYear = (int)date('Y');
            $this->selectedMonth = (int)date('n');
        }

        $this->ensureCurrentPeriodInitialized();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedSpeedFilter() { $this->resetPage(); }
    public function updatingSelectedStockStatusFilter() { $this->resetPage(); }
    public function updatingSelectedRegion() { $this->selectedArea = ''; $this->selectedDistributor = ''; $this->resetPage(); }
    public function updatingSelectedArea() { $this->selectedDistributor = ''; $this->resetPage(); }
    public function updatingSelectedDistributor() { $this->resetPage(); }
    public function updatedSelectedMonth() { $this->ensureCurrentPeriodInitialized(); $this->resetPage(); }
    public function updatedSelectedYear() { $this->ensureCurrentPeriodInitialized(); $this->resetPage(); }

    private function ensureCurrentPeriodInitialized(): void
    {
        $hasData = DB::table('distributor_stocks')
            ->where('tahun', $this->selectedYear)
            ->where('bulan', $this->selectedMonth)
            ->exists();

        if (!$hasData) {
            app(SalesDiagnosticService::class)->syncRunningMonth($this->selectedMonth, $this->selectedYear);
        }
    }

    public function setSpeedFilter(string $filter)
    {
        $this->selectedSpeedFilter = ($this->selectedSpeedFilter === $filter && $filter !== 'ALL') ? 'ALL' : $filter;
        $this->resetPage();
    }

    public function setStockStatusFilter(string $status)
    {
        $this->selectedStockStatusFilter = ($this->selectedStockStatusFilter === $status && $status !== 'ALL') ? 'ALL' : $status;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedSpeedFilter = 'ALL';
        $this->selectedStockStatusFilter = 'ALL';
        $this->search = '';

        if (!$this->isAreaLocked) {
            $this->selectedArea = '';
        }
        if (!$this->isRegionLocked) {
            $this->selectedRegion = '';
        }
        $this->selectedDistributor = '';
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['distributor_name', 'region_name', 'doi_distributor', 'ratio_stok_distributor'], true) ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    /**
     * Subquery agregasi per Distributor (dengan kalkulasi SKU stok < 120%)
     */
    private function buildAggregatedSubquery()
    {
        $start3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonths(3)->startOfMonth()->toDateString();
        $end3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth()->endOfMonth()->toDateString();

        // 1. Subquery 3LM Sell Out per Distributor & SKU
        $so3lmSub = DB::table('t_sellingout as so')
            ->whereBetween('so.INVOICE_DATE', [$start3LM, $end3LM])
            ->groupBy('so.KDDIST', 'so.KDITEMPRC')
            ->select(
                'so.KDDIST as distributor_code',
                'so.KDITEMPRC as product_id',
                DB::raw('ROUND((SUM("so"."TTL_QTY_KTN") / 3.0)::numeric, 1) as avg_3lm_sellout')
            );

        // 2. Base Stok Distributor & SKU level
        $baseSku = DB::table('distributor_stocks as ds')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'ds.distributor_code')
            ->leftJoin('product_masters as pm', 'pm.product_id', '=', 'ds.product_id')
            ->leftJoinSub($so3lmSub, 'so3lm', function ($join) {
                $join->on('so3lm.distributor_code', '=', 'ds.distributor_code')
                     ->on('so3lm.product_id', '=', 'ds.product_id');
            })
            ->where('ds.tahun', $this->selectedYear)
            ->where('ds.bulan', $this->selectedMonth)
            ->where('md.is_active', true);

        $this->applyRegionAccess($baseSku, 'md');

        if (!empty($this->selectedRegion)) {
            $baseSku->where('md.region_name', $this->selectedRegion);
        }
        if (!empty($this->selectedArea)) {
            $baseSku->where('md.area_name', $this->selectedArea);
        }
        if (!empty($this->selectedDistributor)) {
            $baseSku->where('ds.distributor_code', $this->selectedDistributor);
        }

        $baseSku->whereRaw('(ds.stock_awal_ktn <> 0 OR ds.sellin_ktn <> 0 OR ds.sellout_ktn <> 0 OR ds.stock_teoritis_ktn <> 0 OR COALESCE(so3lm.avg_3lm_sellout, 0) <> 0)')
            ->select(
                'ds.distributor_code',
                'md.distributor_name',
                'md.branch_name',
                'md.region_name',
                'md.area_name',
                'ds.product_id',
                DB::raw('COALESCE(pm.is_active, false) as is_active'),
                DB::raw('COALESCE(pm.price_zone1, 0) as price_zone1'),
                'ds.stock_awal_ktn',
                'ds.sellin_ktn',
                DB::raw('(ds.stock_awal_ktn + ds.sellin_ktn) as total_stok_ketersediaan'),
                'ds.sellout_ktn',
                'ds.stock_teoritis_ktn as sisa_stok_berjalan',
                DB::raw('COALESCE(so3lm.avg_3lm_sellout, 0.0) as avg_3lm_sellout'),
                DB::raw("CASE
                    WHEN COALESCE(pm.is_active, false) = true AND COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND ds.stock_teoritis_ktn < (1.2 * so3lm.avg_3lm_sellout)
                    THEN GREATEST(0, CEIL((1.2 * so3lm.avg_3lm_sellout) - ds.stock_teoritis_ktn))
                    ELSE 0
                END as rekom_po_ktn")
            );

        // 3. Roll up to Distributor Level
        $distSummary = DB::query()->fromSub($baseSku, 'sku')
            ->groupBy(
                'sku.distributor_code',
                'sku.distributor_name',
                'sku.branch_name',
                'sku.region_name',
                'sku.area_name'
            )
            ->select(
                'sku.distributor_code',
                'sku.distributor_name',
                'sku.branch_name',
                'sku.region_name',
                'sku.area_name',
                DB::raw('COUNT(DISTINCT sku.product_id) as total_sku_active'),
                DB::raw('SUM(sku.stock_awal_ktn) as total_stok_awal'),
                DB::raw('SUM(sku.sellin_ktn) as total_sellin'),
                DB::raw('SUM(sku.total_stok_ketersediaan) as total_stok_ketersediaan'),
                DB::raw('SUM(sku.sellout_ktn) as total_sellout'),
                DB::raw('SUM(sku.sisa_stok_berjalan) as total_sisa_stok'),
                DB::raw('SUM(sku.avg_3lm_sellout) as total_avg_3lm'),
                DB::raw("CASE
                    WHEN SUM(sku.avg_3lm_sellout) > 0
                    THEN ROUND((SUM(sku.sisa_stok_berjalan) / NULLIF(SUM(sku.avg_3lm_sellout) / 30.0, 0))::numeric, 1)
                    WHEN SUM(sku.sisa_stok_berjalan) > 0 THEN 999.0
                    ELSE 0.0
                END as doi_distributor"),
                DB::raw("CASE
                    WHEN SUM(sku.avg_3lm_sellout) > 0
                    THEN ROUND(((SUM(sku.sisa_stok_berjalan) / NULLIF(SUM(sku.avg_3lm_sellout), 0)) * 100)::numeric, 1)
                    WHEN SUM(sku.sisa_stok_berjalan) > 0 THEN 999.0
                    ELSE 0.0
                END as ratio_stok_distributor"),
                DB::raw('SUM(CASE WHEN sku.rekom_po_ktn > 0 THEN 1 ELSE 0 END) as count_sku_butuh_po'),
                DB::raw('SUM(sku.rekom_po_ktn) as total_rekom_po_ktn'),
                DB::raw('SUM(sku.rekom_po_ktn * sku.price_zone1) as total_rekom_po_value')
            );

        // 4. Wrap with speed & stock health status
        $final = DB::query()->fromSub($distSummary, 'd')
            ->select(
                'd.*',
                DB::raw("CASE
                    WHEN d.doi_distributor <= 30 AND d.total_avg_3lm > 0 THEN 'FAST'
                    WHEN d.doi_distributor <= 45 AND d.total_avg_3lm > 0 THEN 'MEDIUM'
                    ELSE 'SLOW'
                END as speed_status"),
                DB::raw("CASE
                    WHEN d.ratio_stok_distributor < 80 THEN 'UNDER_STOCK'
                    WHEN d.ratio_stok_distributor <= 120 THEN 'SAFE'
                    ELSE 'OVER_STOCK'
                END as stock_status")
            );

        return DB::query()->fromSub($final, 'agg');
    }

    /**
     * Query Utama dengan Filter
     */
    private function buildQuery()
    {
        $query = $this->buildAggregatedSubquery();

        if ($this->selectedSpeedFilter !== 'ALL') {
            $query->where('agg.speed_status', $this->selectedSpeedFilter);
        }

        if ($this->selectedStockStatusFilter !== 'ALL') {
            $query->where('agg.stock_status', $this->selectedStockStatusFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . strtolower(trim($this->search)) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(agg.distributor_code) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(COALESCE(agg.distributor_name, \'\')) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(COALESCE(agg.branch_name, \'\')) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(COALESCE(agg.region_name, \'\')) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(COALESCE(agg.area_name, \'\')) LIKE ?', [$s]);
            });
        }

        return $query;
    }

    /**
     * KPI Metrics Ringkasan Wilayah
     */
    private function getSummaryMetrics(): array
    {
        $base = $this->buildAggregatedSubquery();

        $m = $base->selectRaw("
            COUNT(*) as total_distributor,
            SUM(CASE WHEN agg.stock_status = 'UNDER_STOCK' THEN 1 ELSE 0 END) as count_understock,
            SUM(CASE WHEN agg.stock_status = 'SAFE' THEN 1 ELSE 0 END) as count_safe,
            SUM(CASE WHEN agg.stock_status = 'OVER_STOCK' THEN 1 ELSE 0 END) as count_overstock,
            SUM(CASE WHEN agg.speed_status = 'FAST' THEN 1 ELSE 0 END) as count_fast,
            SUM(CASE WHEN agg.speed_status = 'MEDIUM' THEN 1 ELSE 0 END) as count_medium,
            SUM(CASE WHEN agg.speed_status = 'SLOW' THEN 1 ELSE 0 END) as count_slow,
            SUM(COALESCE(agg.total_sisa_stok, 0)) as sum_sisa_stok,
            SUM(COALESCE(agg.total_rekom_po_ktn, 0)) as sum_rekom_po_ktn,
            SUM(COALESCE(agg.total_rekom_po_value, 0)) as sum_rekom_po_value,
            SUM(COALESCE(agg.count_sku_butuh_po, 0)) as sum_sku_butuh_po
        ")->first();

        return [
            'total_distributor'   => (int)($m->total_distributor ?? 0),
            'count_understock'    => (int)($m->count_understock ?? 0),
            'count_safe'          => (int)($m->count_safe ?? 0),
            'count_overstock'     => (int)($m->count_overstock ?? 0),
            'count_fast'          => (int)($m->count_fast ?? 0),
            'count_medium'        => (int)($m->count_medium ?? 0),
            'count_slow'          => (int)($m->count_slow ?? 0),
            'sum_sisa_stok'       => (float)($m->sum_sisa_stok ?? 0),
            'sum_rekom_po_ktn'    => (int)($m->sum_rekom_po_ktn ?? 0),
            'sum_rekom_po_value'  => (float)($m->sum_rekom_po_value ?? 0),
            'sum_sku_butuh_po'    => (int)($m->sum_sku_butuh_po ?? 0),
        ];
    }

    private function applySorting($query): void
    {
        $validSorts = [
            'distributor_code'        => 'agg.distributor_code',
            'distributor_name'        => 'agg.distributor_name',
            'region_name'             => 'agg.region_name',
            'total_stok_awal'         => 'agg.total_stok_awal',
            'total_sellin'            => 'agg.total_sellin',
            'total_stok_ketersediaan' => 'agg.total_stok_ketersediaan',
            'total_sellout'           => 'agg.total_sellout',
            'total_sisa_stok'         => 'agg.total_sisa_stok',
            'total_avg_3lm'           => 'agg.total_avg_3lm',
            'doi_distributor'         => 'agg.doi_distributor',
            'ratio_stok_distributor'  => 'agg.ratio_stok_distributor',
            'count_sku_butuh_po'      => 'agg.count_sku_butuh_po',
            'total_rekom_po_ktn'      => 'agg.total_rekom_po_ktn',
            'total_rekom_po_value'    => 'agg.total_rekom_po_value',
            'speed_status'            => 'agg.speed_status',
            'stock_status'            => 'agg.stock_status',
        ];

        $orderCol = $validSorts[$this->sortField] ?? 'agg.total_rekom_po_ktn';
        $query->orderBy($orderCol, $this->sortDirection);

        if ($this->sortField !== 'distributor_name') {
            $query->orderBy('agg.distributor_name', 'asc');
        }
    }

    // ==========================================
    // MODAL DETAIL REKOMENDASI PO PER DISTRIBUTOR
    // ==========================================

    public function openPoDetailModal(string $distributorCode): void
    {
        $this->selectedDistributorCode = $distributorCode;

        $dist = DB::table('master_distributors')
            ->where('distributor_code', $distributorCode)
            ->first();

        $this->selectedDistributorData = [
            'distributor_code' => $distributorCode,
            'distributor_name' => $dist->distributor_name ?? $distributorCode,
            'branch_name'      => $dist->branch_name ?? '-',
            'region_name'      => $dist->region_name ?? '-',
            'area_name'        => $dist->area_name ?? '-',
        ];

        $this->poDetailFilter = 'BUTUH_PO';
        $this->loadPoDetails();
        $this->showPoModal = true;
    }

    public function closePoModal(): void
    {
        $this->showPoModal = false;
        $this->selectedDistributorCode = null;
        $this->selectedDistributorData = [];
        $this->poDetailList = [];
        $this->poDetailSummary = [];
    }

    public function setPoDetailFilter(string $filter): void
    {
        $this->poDetailFilter = $filter;
        $this->loadPoDetails();
    }

    public function loadPoDetails(): void
    {
        if (empty($this->selectedDistributorCode)) {
            $this->poDetailList = [];
            $this->poDetailSummary = [];
            return;
        }

        $start3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonths(3)->startOfMonth()->toDateString();
        $end3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth()->endOfMonth()->toDateString();

        $so3lmSub = DB::table('t_sellingout as so')
            ->whereBetween('so.INVOICE_DATE', [$start3LM, $end3LM])
            ->where('so.KDDIST', $this->selectedDistributorCode)
            ->groupBy('so.KDITEMPRC')
            ->select(
                'so.KDITEMPRC as product_id',
                DB::raw('ROUND((SUM("so"."TTL_QTY_KTN") / 3.0)::numeric, 1) as avg_3lm_sellout')
            );

        $query = DB::table('distributor_stocks as ds')
            ->leftJoin('product_masters as pm', 'pm.product_id', '=', 'ds.product_id')
            ->leftJoinSub($so3lmSub, 'so3lm', 'so3lm.product_id', '=', 'ds.product_id')
            ->where('ds.tahun', $this->selectedYear)
            ->where('ds.bulan', $this->selectedMonth)
            ->where('ds.distributor_code', $this->selectedDistributorCode)
            ->whereRaw('(ds.stock_awal_ktn <> 0 OR ds.sellin_ktn <> 0 OR ds.sellout_ktn <> 0 OR ds.stock_teoritis_ktn <> 0 OR COALESCE(so3lm.avg_3lm_sellout, 0) <> 0)')
            ->select(
                'ds.product_id',
                'pm.product_name',
                DB::raw('COALESCE(pm.is_active, false) as is_active'),
                DB::raw('COALESCE(pm.price_zone1, 0) as price_zone1'),
                'ds.stock_awal_ktn',
                'ds.sellin_ktn',
                'ds.sellout_ktn',
                'ds.stock_teoritis_ktn as sisa_stok_berjalan',
                DB::raw('COALESCE(so3lm.avg_3lm_sellout, 0.0) as avg_3lm_sellout'),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) > 0
                    THEN ROUND(((ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout, 0)) * 100)::numeric, 1)
                    WHEN ds.stock_teoritis_ktn > 0 THEN 999.0
                    ELSE 0.0
                END as ratio_stok"),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) > 0
                    THEN ROUND((ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0))::numeric, 1)
                    WHEN ds.stock_teoritis_ktn > 0 THEN 999.0
                    ELSE 0.0
                END as doi_days"),
                DB::raw('ROUND((1.2 * COALESCE(so3lm.avg_3lm_sellout, 0.0))::numeric, 1) as target_stok_120'),
                DB::raw("CASE
                    WHEN COALESCE(pm.is_active, false) = true AND COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND ds.stock_teoritis_ktn < (1.2 * so3lm.avg_3lm_sellout)
                    THEN GREATEST(0, CEIL((1.2 * so3lm.avg_3lm_sellout) - ds.stock_teoritis_ktn))
                    ELSE 0
                END as rekom_po_ktn")
            )
            ->orderBy(DB::raw("CASE
                WHEN COALESCE(pm.is_active, false) = true AND COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND ds.stock_teoritis_ktn < (1.2 * so3lm.avg_3lm_sellout)
                THEN GREATEST(0, CEIL((1.2 * so3lm.avg_3lm_sellout) - ds.stock_teoritis_ktn))
                ELSE 0
            END"), 'desc')
            ->orderBy('ds.product_id', 'asc');

        $allRows = $query->get()->map(function ($row) {
            $poKtn = (int)($row->rekom_po_ktn ?? 0);
            $price = (float)($row->price_zone1 ?? 0);
            $val   = $poKtn * $price;
            $avg   = (float)($row->avg_3lm_sellout ?? 0);
            $sisa  = (float)($row->sisa_stok_berjalan ?? 0);
            $doi   = (float)($row->doi_days ?? 0);
            $isActive = (bool)($row->is_active ?? false);

            // Klasifikasi Urgensi (Jika nonaktif, otomatis INACTIVE dan tidak direkomendasikan PO)
            if (!$isActive) {
                $urgensi = 'INACTIVE';
            } elseif ($poKtn > 0) {
                $urgensi = ($doi <= 10 || $sisa <= 0) ? 'URGENT' : 'REORDER';
            } elseif ($avg <= 0) {
                $urgensi = 'DEAD';
            } else {
                $urgensi = 'SAFE';
            }

            return [
                'product_id'           => $row->product_id,
                'product_name'         => $row->product_name ?? $row->product_id,
                'is_active'            => $isActive,
                'price_zone1'          => $price,
                'stock_awal_ktn'          => (float)($row->stock_awal_ktn ?? 0),
                'sellin_ktn'              => (float)($row->sellin_ktn ?? 0),
                'total_stok_ketersediaan' => (float)(($row->stock_awal_ktn ?? 0) + ($row->sellin_ktn ?? 0)),
                'sellout_ktn'             => (float)($row->sellout_ktn ?? 0),
                'sisa_stok_berjalan'      => $sisa,
                'avg_3lm_sellout'      => $avg,
                'ratio_stok'           => (float)($row->ratio_stok ?? 0),
                'doi_days'             => $doi,
                'target_stok_120'      => (float)($row->target_stok_120 ?? 0),
                'rekom_po_ktn'         => $poKtn,
                'total_rekom_po_value' => $val,
                'urgensi_status'       => $urgensi,
            ];
        });

        // Summary calculated from ALL rows
        $totalSku = $allRows->count();
        $totalButuhPo = $allRows->where('rekom_po_ktn', '>', 0)->count();
        $totalKtn = $allRows->sum('rekom_po_ktn');
        $totalVal = $allRows->sum('total_rekom_po_value');
        $urgentCount = $allRows->where('urgensi_status', 'URGENT')->count();
        $reorderCount = $allRows->where('urgensi_status', 'REORDER')->count();
        $inactiveCount = $allRows->where('is_active', false)->count();

        $this->poDetailSummary = [
            'total_sku'      => $totalSku,
            'sku_butuh_po'   => $totalButuhPo,
            'total_ktn'      => $totalKtn,
            'total_val'      => $totalVal,
            'urgent_count'   => $urgentCount,
            'reorder_count'  => $reorderCount,
            'inactive_count' => $inactiveCount,
        ];

        if ($this->poDetailFilter === 'BUTUH_PO') {
            $this->poDetailList = $allRows->where('rekom_po_ktn', '>', 0)->values()->toArray();
        } else {
            $this->poDetailList = $allRows->values()->toArray();
        }
    }

    public function exportSummary()
    {
        $query = $this->buildQuery();
        $this->applySorting($query);
        $data = $query->get();

        $fileName = "Summary_Rekomendasi_PO_Distributor_{$this->selectedYear}_{$this->selectedMonth}.xlsx";
        return Excel::download(new RekomPoExport($data, 'summary', $this->selectedYear, $this->selectedMonth), $fileName);
    }

    public function exportDistributorPo()
    {
        if (empty($this->selectedDistributorCode)) return null;

        // Ambil data detail seluruh SKU yang butuh PO
        $data = collect($this->poDetailList);
        $distName = $this->selectedDistributorData['distributor_name'] ?? $this->selectedDistributorCode;
        $cleanDist = preg_replace('/[^A-Za-z0-9_\-]/', '_', $distName);

        $fileName = "PO_{$cleanDist}_{$this->selectedYear}_{$this->selectedMonth}.xlsx";
        return Excel::download(new RekomPoExport($data, 'detail', $this->selectedYear, $this->selectedMonth, $distName), $fileName);
    }

    public function render()
    {
        $metrics = $this->getSummaryMetrics();

        $query = $this->buildQuery();
        $this->applySorting($query);
        $distributorsList = $query->paginate($this->perPage);

        $regions = $this->getAvailableRegions();
        $areas = $this->getAvailableAreas();
        $distributors = $this->getAvailableDistributors();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $years = range((int)date('Y') + 1, 2023);

        $currDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $currMonthName = $currDate->translatedFormat('F');

        return view('livewire.siso-analytics.rekom-po', [
            'distributorsList'     => $distributorsList,
            'metrics'              => $metrics,
            'regions'              => $regions,
            'areas'                => $areas,
            'distributors'         => $distributors,
            'regionOptions'        => $regions->values()->toArray(),
            'areaOptions'          => $areas->values()->toArray(),
            'distributorOptions'   => $distributors->values()->toArray(),
            'months'               => $months,
            'years'                => $years,
            'currMonthName'        => $currMonthName,
            'selectedYear'         => $this->selectedYear,
            'selectedMonth'        => $this->selectedMonth,
            'selectedRegion'       => $this->selectedRegion,
            'selectedArea'         => $this->selectedArea,
            'selectedDistributor'  => $this->selectedDistributor,
            'selectedSpeedFilter'  => $this->selectedSpeedFilter,
            'selectedStockStatusFilter' => $this->selectedStockStatusFilter,
            'search'               => $this->search,
            'sortField'            => $this->sortField,
            'sortDirection'        => $this->sortDirection,
            'isRegionLocked'       => $this->isRegionLocked,
            'isAreaLocked'         => $this->isAreaLocked,
        ])->layout('layouts.app');
    }

    // ==========================================
    // USER ACCESS CONTROL
    // ==========================================

    private function resolveUserAccess(): void
    {
        $user = auth()->user();
        if (!$user) return;

        if ($user->hasRole('admin') || $user->hasRole('super_admin') || $user->hasRole('Super Admin')) {
            $this->userAccessLevel = 'nasional';
            return;
        }

        $spvCode = trim((string)($user->supervisor_code ?? ''));
        if (!empty($spvCode)) {
            $this->userAccessLevel = 'supervisor';
            $this->selectedSupervisor = $spvCode;
            $spvDist = DB::table('master_distributors')
                ->where('is_active', true)
                ->where(function ($q) use ($spvCode) {
                    $q->where('supervisor_code', $spvCode)
                      ->orWhereIn('supervisor_code', function ($sub) use ($spvCode) {
                          $sub->select('siso_code')
                              ->from('team_elite_code_mappings')
                              ->whereRaw('TRIM(team_elite_code) = TRIM(?)', [$spvCode])
                              ->whereNotNull('siso_code')
                              ->where('siso_code', '!=', '');
                      });
                })->first();

            if ($spvDist) {
                $this->selectedRegion = $spvDist->region_name ?? '';
                $this->selectedArea   = $spvDist->area_name ?? '';
                $this->isRegionLocked = true;
                $this->isAreaLocked   = true;
            }
            return;
        }

        $areas = array_values(array_filter((array)($user->area_code ?? [])));
        if (!empty($areas)) {
            $this->userAccessLevel = 'area';
            $firstArea = DB::table('master_distributors')
                ->whereIn('area_code', $areas)
                ->where('is_active', true)
                ->first();
            if ($firstArea) {
                $this->selectedRegion = $firstArea->region_name ?? '';
                $this->selectedArea   = $firstArea->area_name ?? '';
                $this->isRegionLocked = true;
                $this->isAreaLocked   = (count($areas) === 1);
            }
            return;
        }

        $regions = array_values(array_filter((array)($user->region_code ?? [])));
        if (!empty($regions)) {
            $this->userAccessLevel = 'region';
            $firstReg = DB::table('master_distributors')
                ->whereIn('region_code', $regions)
                ->where('is_active', true)
                ->first();
            if ($firstReg) {
                $this->selectedRegion = $firstReg->region_name ?? '';
                $this->isRegionLocked = (count($regions) === 1);
            }
            return;
        }

        $this->userAccessLevel = 'nasional';
    }

    private function applyRegionAccess($query, string $tableAlias = 'md')
    {
        $user = auth()->user();
        if (!$user || $this->userAccessLevel === 'nasional') {
            return $query;
        }

        if ($this->userAccessLevel === 'region') {
            $regions = array_values(array_filter((array)($user->region_code ?? [])));
            if (!empty($regions)) {
                $query->whereIn("{$tableAlias}.region_code", $regions);
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        if ($this->userAccessLevel === 'area') {
            $areas = array_values(array_filter((array)($user->area_code ?? [])));
            if (!empty($areas)) {
                $query->whereIn("{$tableAlias}.area_code", $areas);
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        if ($this->userAccessLevel === 'supervisor') {
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

    private function getAvailableRegions()
    {
        $q = DB::table('master_distributors')->where('is_active', true);
        $this->applyRegionAccess($q, 'master_distributors');
        return $q->whereNotNull('region_name')->where('region_name', '!=', '')->distinct()->orderBy('region_name')->pluck('region_name');
    }

    private function getAvailableAreas()
    {
        $q = DB::table('master_distributors')->where('is_active', true);
        $this->applyRegionAccess($q, 'master_distributors');
        if (!empty($this->selectedRegion)) {
            $q->where('region_name', $this->selectedRegion);
        }
        return $q->whereNotNull('area_name')->where('area_name', '!=', '')->distinct()->orderBy('area_name')->pluck('area_name');
    }

    private function getAvailableDistributors()
    {
        $q = DB::table('master_distributors')->where('is_active', true);
        $this->applyRegionAccess($q, 'master_distributors');
        if (!empty($this->selectedRegion)) $q->where('region_name', $this->selectedRegion);
        if (!empty($this->selectedArea)) $q->where('area_name', $this->selectedArea);
        return $q->orderBy('distributor_name')->get(['distributor_code', 'distributor_name']);
    }
}
