<?php

namespace App\Livewire\SisoAnalytics;

use App\Exports\RekonStokExport;
use App\Exports\RekonStokTemplateExport;
use App\Imports\DistributorStockImport;
use App\Services\SalesDiagnosticService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class RekonStok extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Filter Periode
    public int $selectedYear = 2026;
    public int $selectedMonth = 10;

    // Mode Tampilan Tabel ('product' vs 'distributor')
    public string $viewMode = 'product';

    // Filter Teritorial & Pencarian
    public string $selectedRegion = '';
    public string $selectedArea = '';
    public string $selectedSupervisor = '';
    public string $selectedDistributor = '';
    public string $search = '';

    // Filter Cepat Status Anomali & Kecepatan
    public string $selectedStatusFilter = 'ALL';
    public string $selectedMovementFilter = 'ALL';

    // Pagination & Sorting
    public int $perPage = 25;
    public string $sortField = 'product_name';
    public string $sortDirection = 'asc';

    // State Upload Modal
    public bool $showUploadModal = false;
    public $stockFile = null;
    public int $uploadMonth = 10;
    public int $uploadYear = 2026;
    public ?string $uploadSuccessMessage = null;
    public ?string $uploadErrorMessage = null;
    public ?array $uploadSummary = null;
    public bool $isProcessingUpload = false;

    // State Modal Sebaran Depo
    public bool $showDepoModal = false;
    public ?string $selectedDepoProductId = null;
    public ?string $selectedDepoProductName = null;
    public array $depoBreakdownList = [];
    public array $depoBreakdownSummary = [];

    // Akses Level User
    public string $userAccessLevel = 'nasional';
    public bool $isRegionLocked = false;
    public bool $isAreaLocked = false;

    protected $queryString = [
        'selectedYear'           => ['except' => 0],
        'selectedMonth'          => ['except' => 0],
        'viewMode'               => ['except' => 'product'],
        'selectedRegion'         => ['except' => ''],
        'selectedArea'           => ['except' => ''],
        'selectedDistributor'    => ['except' => ''],
        'selectedStatusFilter'   => ['except' => 'ALL'],
        'selectedMovementFilter' => ['except' => 'ALL'],
        'search'                 => ['except' => ''],
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

        $this->uploadYear = $this->selectedYear;
        $this->uploadMonth = $this->selectedMonth;
        $this->sortField = $this->viewMode === 'distributor' ? 'region_name' : 'product_name';
        $this->sortDirection = 'asc';

        $this->ensureCurrentPeriodInitialized();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedStatusFilter() { $this->resetPage(); }
    public function updatingSelectedMovementFilter() { $this->resetPage(); }
    public function updatingSelectedRegion() { $this->selectedArea = ''; $this->selectedDistributor = ''; $this->resetPage(); }
    public function updatingSelectedArea() { $this->selectedDistributor = ''; $this->resetPage(); }
    public function updatingSelectedDistributor() { $this->resetPage(); }
    public function updatedSelectedMonth() { $this->ensureCurrentPeriodInitialized(); $this->resetPage(); }
    public function updatedSelectedYear() { $this->ensureCurrentPeriodInitialized(); $this->resetPage(); }

    public function setViewMode(string $mode)
    {
        if (in_array($mode, ['product', 'distributor'], true)) {
            $this->viewMode = $mode;
            $this->sortField = $mode === 'distributor' ? 'region_name' : 'product_name';
            $this->sortDirection = 'asc';
            $this->resetPage();
        }
    }

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

    public function setStatusFilter(string $status)
    {
        $this->selectedStatusFilter = ($this->selectedStatusFilter === $status && $status !== 'ALL') ? 'ALL' : $status;
        $this->resetPage();
    }

    public function setMovementFilter(string $filter)
    {
        $this->selectedMovementFilter = ($this->selectedMovementFilter === $filter && $filter !== 'ALL') ? 'ALL' : $filter;
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['region_name', 'distributor_name', 'product_name', 'product_id', 'total_distributor', 'sisa_stok_berjalan', 'selisih_awal', 'doi_days'], true) ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedStatusFilter = 'ALL';
        $this->selectedMovementFilter = 'ALL';
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

    public function openUploadModal()
    {
        $this->uploadYear = $this->selectedYear;
        $this->uploadMonth = $this->selectedMonth;
        $this->stockFile = null;
        $this->uploadSuccessMessage = null;
        $this->uploadErrorMessage = null;
        $this->uploadSummary = null;
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->stockFile = null;
    }

    public function openDepoModal(string $productId): void
    {
        $this->selectedDepoProductId = $productId;
        $this->selectedDepoProductName = DB::table('product_masters')
            ->where('product_id', $productId)
            ->value('product_name') ?? $productId;

        $this->loadDepoBreakdown();
        $this->showDepoModal = true;
    }

    public function closeDepoModal(): void
    {
        $this->showDepoModal = false;
        $this->selectedDepoProductId = null;
        $this->selectedDepoProductName = null;
        $this->depoBreakdownList = [];
        $this->depoBreakdownSummary = [];
    }

    public function loadDepoBreakdown(): void
    {
        if (empty($this->selectedDepoProductId)) {
            $this->depoBreakdownList = [];
            $this->depoBreakdownSummary = [];
            return;
        }

        $prevPeriod = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $prevBulan = $prevPeriod->month;
        $prevTahun = $prevPeriod->year;

        $start3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonths(3)->startOfMonth()->toDateString();
        $end3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth()->endOfMonth()->toDateString();

        $so3lmSub = DB::table('t_sellingout as so')
            ->whereBetween('so.INVOICE_DATE', [$start3LM, $end3LM])
            ->where('so.KDITEMPRC', $this->selectedDepoProductId)
            ->groupBy('so.KDDIST')
            ->select(
                'so.KDDIST as distributor_code',
                DB::raw('ROUND((SUM("so"."TTL_QTY_KTN") / 3.0)::numeric, 1) as avg_3lm_sellout')
            );

        $query = DB::table('distributor_stocks as ds')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'ds.distributor_code')
            ->leftJoin('distributor_stocks as prev_ds', function ($join) use ($prevBulan, $prevTahun) {
                $join->on('prev_ds.distributor_code', '=', 'ds.distributor_code')
                     ->on('prev_ds.product_id', '=', 'ds.product_id')
                     ->where('prev_ds.bulan', '=', $prevBulan)
                     ->where('prev_ds.tahun', '=', $prevTahun);
            })
            ->leftJoinSub($so3lmSub, 'so3lm', 'so3lm.distributor_code', '=', 'ds.distributor_code')
            ->where('ds.tahun', $this->selectedYear)
            ->where('ds.bulan', $this->selectedMonth)
            ->where('ds.product_id', $this->selectedDepoProductId)
            ->where('md.is_active', true);

        $this->applyRegionAccess($query, 'md');

        if (!empty($this->selectedRegion)) {
            $query->where('md.region_name', $this->selectedRegion);
        }
        if (!empty($this->selectedArea)) {
            $query->where('md.area_name', $this->selectedArea);
        }
        if (!empty($this->selectedDistributor)) {
            $query->where('ds.distributor_code', $this->selectedDistributor);
        }

        $query->whereRaw('(ds.stock_awal_ktn <> 0 OR ds.sellin_ktn <> 0 OR ds.sellout_ktn <> 0 OR ds.stock_teoritis_ktn <> 0 OR COALESCE(so3lm.avg_3lm_sellout, 0) <> 0)')
            ->select(
                'ds.distributor_code',
                'md.distributor_name',
                'md.branch_name',
                'md.region_name',
                'md.area_name',
                'ds.stock_awal_ktn',
                'ds.sellin_ktn',
                DB::raw('(ds.stock_awal_ktn + ds.sellin_ktn) as total_stok_ketersediaan'),
                'ds.sellout_ktn',
                'ds.stock_teoritis_ktn as sisa_stok_berjalan',
                DB::raw('COALESCE(so3lm.avg_3lm_sellout, 0.0) as avg_3lm_sellout'),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) > 0 THEN ROUND(((ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout, 0)) * 100)::numeric, 1)
                    WHEN ds.stock_teoritis_ktn > 0 THEN 999999.0
                    ELSE 0.0
                END as ratio_sisa_avg"),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) > 0 THEN ROUND((ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0))::numeric, 1)
                    WHEN ds.stock_teoritis_ktn > 0 THEN 999999.0
                    ELSE 0.0
                END as doi_days"),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) <= 0 THEN 'DEAD'
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) >= 100 OR (COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND (ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0)) <= 25) THEN 'FAST'
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) >= 20 OR (COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND (ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0)) <= 45) THEN 'MEDIUM'
                    ELSE 'SLOW'
                END as movement_class"),
                DB::raw("CASE
                    WHEN ds.stock_teoritis_ktn < 0 THEN 'PHANTOM_SALES'
                    WHEN ds.stock_teoritis_ktn > 0 AND (COALESCE(so3lm.avg_3lm_sellout, 0) = 0 OR (ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0)) > 50) THEN 'OVER_STOCK'
                    WHEN ds.stock_teoritis_ktn >= 0 AND (ds.sellout_ktn > 0 OR COALESCE(so3lm.avg_3lm_sellout, 0) > 0) AND (COALESCE(so3lm.avg_3lm_sellout, 0) = 0 OR ((ds.stock_teoritis_ktn / NULLIF(so3lm.avg_3lm_sellout, 0)) * 100) < 80) THEN 'UNDER_STOCK'
                    ELSE 'IDEAL'
                END as sales_flow_status")
            )
            ->orderBy('ds.stock_teoritis_ktn', 'desc');

        $rows = $query->get()->map(function ($item) {
            return (array)$item;
        })->toArray();

        $this->depoBreakdownList = $rows;
        $this->depoBreakdownSummary = [
            'total_dist'      => count($rows),
            'sum_stock_awal'  => (float)array_sum(array_column($rows, 'stock_awal_ktn')),
            'sum_sellin'      => (float)array_sum(array_column($rows, 'sellin_ktn')),
            'sum_total_stock' => (float)array_sum(array_column($rows, 'total_stok_ketersediaan')),
            'sum_sellout'     => (float)array_sum(array_column($rows, 'sellout_ktn')),
            'sum_sisa_stok'   => (float)array_sum(array_column($rows, 'sisa_stok_berjalan')),
            'sum_avg_3lm'     => (float)array_sum(array_column($rows, 'avg_3lm_sellout')),
        ];
    }

    public function processUpload()
    {
        $this->validate([
            'stockFile'   => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'uploadMonth' => 'required|integer|between:1,12',
            'uploadYear'  => 'required|integer|between:2020,2035',
        ], [
            'stockFile.required' => 'Silakan pilih file Excel/CSV terlebih dahulu.',
            'stockFile.mimes'    => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'stockFile.max'      => 'Ukuran file tidak boleh melebihi 20MB.',
        ]);

        $this->isProcessingUpload = true;
        $this->uploadErrorMessage = null;
        $this->uploadSuccessMessage = null;

        try {
            $fileName = $this->stockFile->getClientOriginalName();
            $importer = new DistributorStockImport(
                $this->uploadMonth,
                $this->uploadYear,
                auth()->id(),
                $fileName
            );

            Excel::import($importer, $this->stockFile->getRealPath());
            $res = $importer->getResult();

            if (!empty($res['success'])) {
                $this->uploadSummary = $res;
                $this->uploadSuccessMessage = $res['message'] ?? 'Berhasil memproses upload stok distributor!';

                $this->selectedYear = $this->uploadYear;
                $this->selectedMonth = $this->uploadMonth;

                $next = Carbon::createFromDate($this->uploadYear, $this->uploadMonth, 1)->addMonth();
                app(SalesDiagnosticService::class)->syncRunningMonth($next->month, $next->year);
            } else {
                $this->uploadErrorMessage = $res['message'] ?? 'Terjadi kendala saat memproses baris data.';
            }
        } catch (\Throwable $e) {
            Log::error('Upload Stok Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->uploadErrorMessage = 'Gagal memproses file: ' . $e->getMessage();
        } finally {
            $this->isProcessingUpload = false;
        }
    }

    public function downloadTemplate()
    {
        $fileName = "Template_Upload_Stok_Distributor.xlsx";
        return Excel::download(new RekonStokTemplateExport($this->selectedDistributor ?: null), $fileName);
    }

    public function recalculateCurrentPeriod()
    {
        try {
            $service = app(SalesDiagnosticService::class);
            $count = $service->syncRunningMonth($this->selectedMonth, $this->selectedYear);

            session()->flash('success', "Diagnosa & monitoring periode {$this->selectedMonth}/{$this->selectedYear} berhasil diperbarui ({$count} data tersinkron)!");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal hitung ulang: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $query = $this->buildQuery();
        $this->applySorting($query);
        $data = $query->get();
        $modeSuffix = $this->viewMode === 'product' ? 'Per_Produk' : 'Per_Distributor';
        $fileName = "Diagnosa_Aliran_Stok_SISO_{$modeSuffix}_{$this->selectedYear}_{$this->selectedMonth}.xlsx";

        return Excel::download(new RekonStokExport($data, $this->viewMode, $this->selectedYear, $this->selectedMonth), $fileName);
    }

    /**
     * Subquery agregasi dasar (Per Produk atau Per Distributor) sebelum filter status & pencarian teks.
     */
    private function buildAggregatedSubquery()
    {
        $prevPeriod = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $prevBulan = $prevPeriod->month;
        $prevTahun = $prevPeriod->year;

        $now = Carbon::now();
        $daysInMonth = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->daysInMonth;
        $elapsedDays = ($now->year === $this->selectedYear && $now->month === $this->selectedMonth)
            ? max(1, $now->day)
            : $daysInMonth;

        $base = DB::table('distributor_stocks as ds')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'ds.distributor_code')
            ->leftJoin('product_masters as pm', 'pm.product_id', '=', 'ds.product_id')
            ->leftJoin('distributor_stocks as prev_ds', function ($join) use ($prevBulan, $prevTahun) {
                $join->on('prev_ds.distributor_code', '=', 'ds.distributor_code')
                     ->on('prev_ds.product_id', '=', 'ds.product_id')
                     ->where('prev_ds.bulan', '=', $prevBulan)
                     ->where('prev_ds.tahun', '=', $prevTahun);
            })
            ->where('ds.tahun', $this->selectedYear)
            ->where('ds.bulan', $this->selectedMonth)
            ->where('md.is_active', true);

        $this->applyRegionAccess($base, 'md');

        if (!empty($this->selectedRegion)) {
            $base->where('md.region_name', $this->selectedRegion);
        }
        if (!empty($this->selectedArea)) {
            $base->where('md.area_name', $this->selectedArea);
        }
        if (!empty($this->selectedDistributor)) {
            $base->where('ds.distributor_code', $this->selectedDistributor);
        }

        if ($this->viewMode === 'distributor') {
            $grouped = $base->groupBy(
                'ds.distributor_code',
                'md.distributor_name',
                'md.branch_name',
                'md.region_name',
                'md.area_name'
            )->select(
                'ds.distributor_code',
                'md.distributor_name',
                'md.branch_name',
                'md.region_name',
                'md.area_name',
                DB::raw("NULL::varchar as product_id"),
                DB::raw("NULL::varchar as product_name"),
                DB::raw("1 as total_distributor"),
                DB::raw('SUM(COALESCE(prev_ds.stock_teoritis_ktn, 0)) as stok_awal_sistem'),
                DB::raw('SUM(ds.stock_awal_ktn) as stok_closing_lalu'),
                DB::raw('SUM(ds.stock_awal_ktn - COALESCE(prev_ds.stock_teoritis_ktn, 0)) as selisih_awal'),
                DB::raw('SUM(ds.sellin_ktn) as sellin_ktn'),
                DB::raw('SUM(ds.stock_awal_ktn + ds.sellin_ktn) as total_stok_ketersediaan'),
                DB::raw('SUM(ds.sellout_ktn) as sellout_ktn'),
                DB::raw('SUM(ds.stock_teoritis_ktn) as sisa_stok_berjalan'),
                DB::raw('SUM(ds.total_value) as total_value')
            );
        } else {
            $grouped = $base->groupBy(
                'ds.product_id',
                'pm.product_name'
            )->select(
                DB::raw("NULL::varchar as distributor_code"),
                DB::raw("NULL::varchar as distributor_name"),
                DB::raw("NULL::varchar as branch_name"),
                DB::raw("NULL::varchar as region_name"),
                DB::raw("NULL::varchar as area_name"),
                'ds.product_id',
                'pm.product_name',
                DB::raw('COUNT(DISTINCT ds.distributor_code) as total_distributor'),
                DB::raw('SUM(COALESCE(prev_ds.stock_teoritis_ktn, 0)) as stok_awal_sistem'),
                DB::raw('SUM(ds.stock_awal_ktn) as stok_closing_lalu'),
                DB::raw('SUM(ds.stock_awal_ktn - COALESCE(prev_ds.stock_teoritis_ktn, 0)) as selisih_awal'),
                DB::raw('SUM(ds.sellin_ktn) as sellin_ktn'),
                DB::raw('SUM(ds.stock_awal_ktn + ds.sellin_ktn) as total_stok_ketersediaan'),
                DB::raw('SUM(ds.sellout_ktn) as sellout_ktn'),
                DB::raw('SUM(ds.stock_teoritis_ktn) as sisa_stok_berjalan'),
                DB::raw('SUM(ds.total_value) as total_value')
            );
        }

        // Hitung rentang 3LM (3 bulan terakhir sebelum bulan yang dipilih)
        $start3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonths(3)->startOfMonth()->toDateString();
        $end3LM = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth()->endOfMonth()->toDateString();

        // Hitung DOI, AVG 3LM Sell Out & Status Diagnosa di atas hasil agregasi (sembunyikan baris yang semuanya 0)
        $withDiag = DB::query()->fromSub($grouped, 'g');

        if ($this->viewMode === 'distributor') {
            $so3lmSub = DB::table('t_sellingout as so')
                ->whereBetween('so.INVOICE_DATE', [$start3LM, $end3LM])
                ->groupBy('so.KDDIST')
                ->select(
                    'so.KDDIST as distributor_code',
                    DB::raw('ROUND((SUM("so"."TTL_QTY_KTN") / 3.0)::numeric, 1) as avg_3lm_sellout')
                );
            $withDiag->leftJoinSub($so3lmSub, 'so3lm', 'so3lm.distributor_code', '=', 'g.distributor_code');
        } else {
            $soQuery = DB::table('t_sellingout as so')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'so.KDDIST')
                ->whereBetween('so.INVOICE_DATE', [$start3LM, $end3LM])
                ->where('md.is_active', true);

            $this->applyRegionAccess($soQuery, 'md');

            if (!empty($this->selectedRegion)) {
                $soQuery->where('md.region_name', $this->selectedRegion);
            }
            if (!empty($this->selectedArea)) {
                $soQuery->where('md.area_name', $this->selectedArea);
            }
            if (!empty($this->selectedDistributor)) {
                $soQuery->where('so.KDDIST', $this->selectedDistributor);
            }

            $so3lmSub = $soQuery->groupBy('so.KDITEMPRC')
                ->select(
                    'so.KDITEMPRC as product_id',
                    DB::raw('ROUND((SUM("so"."TTL_QTY_KTN") / 3.0)::numeric, 1) as avg_3lm_sellout')
                );

            $withDiag->leftJoinSub($so3lmSub, 'so3lm', 'so3lm.product_id', '=', 'g.product_id');
        }

        $withDiag->whereRaw('(g.total_stok_ketersediaan <> 0 OR g.sellout_ktn <> 0)')
            ->select(
                'g.*',
                DB::raw('COALESCE(so3lm.avg_3lm_sellout, 0.0) as avg_3lm_sellout'),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) > 0 THEN ROUND(((g.sisa_stok_berjalan / NULLIF(so3lm.avg_3lm_sellout, 0)) * 100)::numeric, 1)
                    WHEN g.sisa_stok_berjalan > 0 THEN 999999.0
                    ELSE 0.0
                END as ratio_sisa_avg"),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) > 0 THEN ROUND((g.sisa_stok_berjalan / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0))::numeric, 1)
                    WHEN g.sisa_stok_berjalan > 0 THEN 999999.0
                    ELSE 0.0
                END as doi_days"),
                DB::raw("CASE
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) <= 0 THEN 'DEAD'
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) >= 100 OR (COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND (g.sisa_stok_berjalan / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0)) <= 25) THEN 'FAST'
                    WHEN COALESCE(so3lm.avg_3lm_sellout, 0) >= 20 OR (COALESCE(so3lm.avg_3lm_sellout, 0) > 0 AND (g.sisa_stok_berjalan / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0)) <= 45) THEN 'MEDIUM'
                    ELSE 'SLOW'
                END as movement_class"),
                DB::raw("CASE
                    WHEN g.sisa_stok_berjalan < 0 THEN 'PHANTOM_SALES'
                    WHEN g.sisa_stok_berjalan > 0 AND (COALESCE(so3lm.avg_3lm_sellout, 0) = 0 OR (g.sisa_stok_berjalan / NULLIF(so3lm.avg_3lm_sellout / 30.0, 0)) > 50) THEN 'OVER_STOCK'
                    WHEN g.sisa_stok_berjalan >= 0 AND (g.sellout_ktn > 0 OR COALESCE(so3lm.avg_3lm_sellout, 0) > 0) AND (COALESCE(so3lm.avg_3lm_sellout, 0) = 0 OR ((g.sisa_stok_berjalan / NULLIF(so3lm.avg_3lm_sellout, 0)) * 100) < 80) THEN 'UNDER_STOCK'
                    ELSE 'IDEAL'
                END as sales_flow_status")
            );

        return DB::query()->fromSub($withDiag, 'agg');
    }

    /**
     * Query Builder Utama untuk Tabel & Export
     */
    private function buildQuery()
    {
        $query = $this->buildAggregatedSubquery();

        if ($this->selectedStatusFilter !== 'ALL') {
            if ($this->selectedStatusFilter === 'VARIANCE') {
                $query->whereRaw('ABS(agg.selisih_awal) > 2');
            } else {
                $query->where('agg.sales_flow_status', $this->selectedStatusFilter);
            }
        }

        if ($this->selectedMovementFilter !== 'ALL') {
            $query->where('agg.movement_class', $this->selectedMovementFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . strtolower(trim($this->search)) . '%';
            if ($this->viewMode === 'distributor') {
                $query->where(function ($q) use ($s) {
                    $q->whereRaw('LOWER(agg.distributor_code) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(COALESCE(agg.distributor_name, \'\')) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(COALESCE(agg.branch_name, \'\')) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(COALESCE(agg.region_name, \'\')) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(COALESCE(agg.area_name, \'\')) LIKE ?', [$s]);
                });
            } else {
                $query->where(function ($q) use ($s) {
                    $q->whereRaw('LOWER(agg.product_id) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(COALESCE(agg.product_name, \'\')) LIKE ?', [$s]);
                });
            }
        }

        return $query;
    }

    /**
     * Hitung Kartu KPI Ringkasan (Sinkron dengan ViewMode Produk / Distributor)
     */
    private function getSummaryMetrics(): array
    {
        $base = $this->buildAggregatedSubquery();

        $metrics = $base->selectRaw("
            COUNT(*) as total_records,
            SUM(CASE WHEN ABS(agg.selisih_awal) > 2 THEN 1 ELSE 0 END) as count_variance,
            SUM(CASE WHEN agg.sales_flow_status = 'PHANTOM_SALES' THEN 1 ELSE 0 END) as count_phantom,
            SUM(CASE WHEN agg.sales_flow_status = 'OVER_STOCK' THEN 1 ELSE 0 END) as count_overstock,
            SUM(CASE WHEN agg.sales_flow_status = 'UNDER_STOCK' THEN 1 ELSE 0 END) as count_understock,
            SUM(CASE WHEN agg.sales_flow_status = 'IDEAL' THEN 1 ELSE 0 END) as count_ideal,
            SUM(COALESCE(agg.stok_closing_lalu, 0)) as total_stock_ktn,
            SUM(CASE WHEN agg.sales_flow_status = 'OVER_STOCK' THEN COALESCE(agg.total_value, 0) ELSE 0 END) as value_stuck_overstock,
            SUM(COALESCE(agg.total_value, 0)) as total_stock_value,
            SUM(CASE WHEN agg.movement_class = 'FAST' THEN 1 ELSE 0 END) as count_fast,
            SUM(CASE WHEN agg.movement_class = 'MEDIUM' THEN 1 ELSE 0 END) as count_medium,
            SUM(CASE WHEN agg.movement_class = 'SLOW' THEN 1 ELSE 0 END) as count_slow,
            SUM(CASE WHEN agg.movement_class = 'DEAD' THEN 1 ELSE 0 END) as count_dead
        ")->first();

        return [
            'total_records'        => (int)($metrics->total_records ?? 0),
            'count_variance'       => (int)($metrics->count_variance ?? 0),
            'count_phantom'        => (int)($metrics->count_phantom ?? 0),
            'count_overstock'      => (int)($metrics->count_overstock ?? 0),
            'count_understock'     => (int)($metrics->count_understock ?? 0),
            'count_ideal'          => (int)($metrics->count_ideal ?? 0),
            'total_stock_ktn'      => (float)($metrics->total_stock_ktn ?? 0),
            'value_stuck_overstock'=> (float)($metrics->value_stuck_overstock ?? 0),
            'total_stock_value'    => (float)($metrics->total_stock_value ?? 0),
            'count_fast'           => (int)($metrics->count_fast ?? 0),
            'count_medium'         => (int)($metrics->count_medium ?? 0),
            'count_slow'           => (int)($metrics->count_slow ?? 0),
            'count_dead'           => (int)($metrics->count_dead ?? 0),
        ];
    }

    private function applySorting($query): void
    {
        if ($this->sortField === 'region_name') {
            if ($this->viewMode === 'distributor') {
                $query->orderBy('agg.region_name', $this->sortDirection)
                      ->orderBy('agg.area_name', $this->sortDirection)
                      ->orderBy('agg.distributor_name', $this->sortDirection);
                return;
            }
            $this->sortField = 'product_name';
        }

        if ($this->sortField === 'distributor_name') {
            if ($this->viewMode === 'distributor') {
                $query->orderBy('agg.distributor_name', $this->sortDirection);
                return;
            }
            $this->sortField = 'product_name';
        }

        $validSorts = [
            'product_id'              => 'agg.product_id',
            'product_name'            => 'agg.product_name',
            'total_distributor'       => 'agg.total_distributor',
            'stok_awal_sistem'        => 'agg.stok_awal_sistem',
            'stok_closing_lalu'       => 'agg.stok_closing_lalu',
            'selisih_awal'            => 'agg.selisih_awal',
            'sellin_ktn'              => 'agg.sellin_ktn',
            'total_stok_ketersediaan' => 'agg.total_stok_ketersediaan',
            'sellout_ktn'             => 'agg.sellout_ktn',
            'sisa_stok_berjalan'      => 'agg.sisa_stok_berjalan',
            'avg_3lm_sellout'         => 'agg.avg_3lm_sellout',
            'ratio_sisa_avg'          => 'agg.ratio_sisa_avg',
            'doi_days'                => 'agg.doi_days',
            'movement_class'          => 'agg.movement_class',
            'sales_flow_status'       => 'agg.sales_flow_status',
        ];

        $orderCol = $validSorts[$this->sortField] ?? ($this->viewMode === 'distributor' ? 'agg.region_name' : 'agg.product_name');
        $query->orderBy($orderCol, $this->sortDirection);

        if ($this->viewMode === 'distributor') {
            $query->orderBy('agg.region_name', 'asc')
                  ->orderBy('agg.area_name', 'asc')
                  ->orderBy('agg.distributor_name', 'asc');
        } else {
            $query->orderBy('agg.product_name', 'asc')
                  ->orderBy('agg.product_id', 'asc');
        }
    }

    public function render()
    {
        $metrics = $this->getSummaryMetrics();

        $query = $this->buildQuery();
        $this->applySorting($query);

        $stocks = $query->paginate($this->perPage);

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
        $prevDate = (clone $currDate)->subMonth();
        $currMonthName = $currDate->translatedFormat('M');
        $prevMonthName = $prevDate->translatedFormat('M');

        $isPeriodRunning = DB::table('distributor_stocks')
            ->where('tahun', $this->selectedYear)
            ->where('bulan', $this->selectedMonth)
            ->where('file_name', 'RUNNING_ESTIMATE')
            ->exists();

        return view('livewire.siso-analytics.rekon-stok', [
            'stocks'               => $stocks,
            'metrics'              => $metrics,
            'statusPills'          => SalesDiagnosticService::getStatusPills(),
            'regions'              => $regions,
            'areas'                => $areas,
            'distributors'         => $distributors,
            'regionOptions'        => $regions->values()->toArray(),
            'areaOptions'          => $areas->values()->toArray(),
            'distributorOptions'   => $distributors->values()->toArray(),
            'months'               => $months,
            'years'                => $years,
            'isPeriodRunning'      => $isPeriodRunning,
            'currMonthName'        => $currMonthName,
            'prevMonthName'        => $prevMonthName,
            'viewMode'             => $this->viewMode,
            'selectedYear'         => $this->selectedYear,
            'selectedMonth'        => $this->selectedMonth,
            'selectedRegion'       => $this->selectedRegion,
            'selectedArea'         => $this->selectedArea,
            'selectedDistributor'  => $this->selectedDistributor,
            'selectedStatusFilter'   => $this->selectedStatusFilter,
            'selectedMovementFilter' => $this->selectedMovementFilter,
            'search'                 => $this->search,
            'sortField'            => $this->sortField,
            'sortDirection'        => $this->sortDirection,
            'isRegionLocked'       => $this->isRegionLocked,
            'isAreaLocked'         => $this->isAreaLocked,
            'showUploadModal'      => $this->showUploadModal,
            'uploadMonth'          => $this->uploadMonth,
            'uploadYear'           => $this->uploadYear,
            'uploadErrorMessage'   => $this->uploadErrorMessage,
            'uploadSuccessMessage' => $this->uploadSuccessMessage,
            'uploadSummary'        => $this->uploadSummary,
        ])->layout('layouts.app');
    }

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
