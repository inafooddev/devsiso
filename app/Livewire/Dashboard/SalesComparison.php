<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Imports\SellingOutEskalinkImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class SalesComparison extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Filters
    public $monthFilter;
    public $regionsFilter = [];
    public $implementasiFilter = 'ALL'; 
    public $statusFilter = 'ALL'; // New filter: ALL, OK, NOT_OK

    // UI States
    public $isFilterModalOpen = false;
    public $isImportModalOpen = false;
    public $isFiltered = false;

    // Import
    public $importFile;

    // Dropdown options
    public $regionsOption = [];

    // Summary data
    public $summary = null;

    protected $queryString = ['isFiltered'];

    /**
     * Helper to filter Query based on user region access.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }
        return $query;
    }

    public function mount()
    {
        $this->monthFilter = date('Y-m');
        $this->implementasiFilter = 'ALL';

        // Load Region Awal dengan Proteksi
        $query = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('is_active', true)
            ->where('region_code', '!=', 'HOINA')
            ->distinct();

        $this->applyRegionAccess($query);
        $this->regionsOption = $query->orderBy('region_name')->get();

        // Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regionsOption) === 1) {
            $this->regionsFilter = [$this->regionsOption->first()->region_code];
        }

        // Restore filters from session
        if (session()->has('sales_comparison_filters')) {
            $filters = session()->get('sales_comparison_filters');
            $this->monthFilter = $filters['monthFilter'] ?? $this->monthFilter;
            $this->regionsFilter = $filters['regionsFilter'] ?? $this->regionsFilter;
            $this->implementasiFilter = $filters['implementasiFilter'] ?? $this->implementasiFilter;
            $this->statusFilter = $filters['statusFilter'] ?? 'ALL';
            $this->isFiltered = $filters['isFiltered'] ?? false;
        }
    }

    public function selectAllRegions()
    {
        $this->regionsFilter = collect($this->regionsOption)->pluck('region_code')->toArray();
    }

    public function applyFilters()
    {
        $this->validate([
            'monthFilter'        => 'required',
            'regionsFilter'      => 'required|array|min:1',
            'implementasiFilter' => 'required|in:Y,N,ALL', 
            'statusFilter'       => 'required|in:OK,NOT_OK,ALL',
        ]);

        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $unauthorizedRegions = array_diff($this->regionsFilter, $user->region_code);
            if (!empty($unauthorizedRegions)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk memfilter beberapa wilayah yang dipilih.');
                return;
            }
        }

        $this->isFiltered = true;
        $this->isFilterModalOpen = false;
        $this->resetPage();
        $this->saveFiltersToSession();
    }

    public function resetFilters()
    {
        $this->reset(['monthFilter', 'regionsFilter', 'implementasiFilter', 'statusFilter', 'isFiltered']);
        $this->monthFilter = date('Y-m');
        $this->implementasiFilter = 'ALL';
        $this->statusFilter = 'ALL';
        session()->forget('sales_comparison_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regionsOption) === 1) {
            $this->regionsFilter = [$this->regionsOption->first()->region_code];
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('sales_comparison_filters', [
            'monthFilter' => $this->monthFilter,
            'regionsFilter' => $this->regionsFilter,
            'implementasiFilter' => $this->implementasiFilter,
            'statusFilter' => $this->statusFilter,
            'isFiltered' => $this->isFiltered,
        ]);
    }

    public function updatedImportFile()
    {
        $this->validateOnly('importFile', [
            'importFile' => 'file|max:102400|mimes:xlsx,xls'
        ]);
    }

    public function import()
    {
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            session()->flash('error', 'Hanya Administrator yang diizinkan untuk mengimpor data Selling Out.');
            return;
        }

        $this->validate([
            'importFile' => 'file|max:102400|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new SellingOutEskalinkImport, $this->importFile);
            session()->flash('message', 'Import data Selling Out berhasil dilakukan.');
            $this->isImportModalOpen = false;
            $this->importFile = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $comparisons = collect();

        if ($this->isFiltered) {
            $startDate = Carbon::createFromFormat('Y-m', $this->monthFilter)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromFormat('Y-m', $this->monthFilter)->endOfMonth()->format('Y-m-d');

            $finalRegions = $this->regionsFilter;
            $user = auth()->user();
            if (!$user->hasRole('admin') && !empty($user->region_code)) {
                $finalRegions = array_intersect($finalRegions, $user->region_code);
                if (empty($finalRegions)) {
                    $finalRegions = $user->region_code;
                }
            }

            $regionsString = "'" . implode("','", $finalRegions) . "'";

            // Summary calculation
            $this->summary = DB::table(DB::raw("(
                SELECT
                    die.eskalink_code AS branch_code,
                    FLOOR(COALESCE(soe.net_eska, 0)) AS net_eska,
                    FLOOR(COALESCE(sid.net_siso, 0)) AS net_siso,
                    ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) AS abs_selisih
                FROM distributor_implementasi_eskalink die
                LEFT JOIN master_distributors md
                    ON md.distributor_code = die.distributor_code
                LEFT JOIN (
                    SELECT
                        branch_code,
                        SUM(nett_amount) AS net_eska
                    FROM selling_out_eskalink
                    WHERE invoice_date BETWEEN '$startDate' AND '$endDate'
                    GROUP BY branch_code
                ) soe ON soe.branch_code = die.eskalink_code
                LEFT JOIN (
                    SELECT
                        distributor_code,
                        SUM(net_amount) AS net_siso
                    FROM sales_invoice_distributor
                    WHERE invoice_date BETWEEN '$startDate' AND '$endDate'
                    GROUP BY distributor_code
                ) sid ON sid.distributor_code = die.distributor_code
                WHERE die.implementasi = 'Y'
                AND md.is_active = true
                AND md.region_code IN ($regionsString)
            ) x"))
            ->selectRaw('
                COUNT(*) AS total_branch,
                SUM(CASE WHEN net_eska = 0 THEN 1 ELSE 0 END) AS net_siso_zero,
                SUM(CASE WHEN net_eska <> 0 THEN 1 ELSE 0 END) AS net_siso_non_zero,
                SUM(CASE WHEN abs_selisih >= 1000 THEN 1 ELSE 0 END) AS total_not_ok
            ')
            ->first();

            // Main Query
            $query = DB::table(DB::raw("(
                WITH sid_sum AS (
                    SELECT
                        distributor_code,
                        SUM(net_amount) AS net_siso
                    FROM sales_invoice_distributor
                    WHERE invoice_date BETWEEN '$startDate' AND '$endDate'
                    GROUP BY distributor_code
                ),
                soe_sum AS (
                    SELECT
                        branch_code,
                        region_code,
                        region_name,
                        entity_code,
                        entity_name,
                        branch_name,
                        COUNT(*) AS row_count,
                        SUM(qty3_pcs) AS qty_pcs,
                        SUM(gross_amount) AS gross,
                        SUM(line_discount_4) AS ld4,
                        SUM(line_discount_8) AS bb,
                        SUM(dpp) AS dpp,
                        SUM(tax) AS tax,
                        SUM(nett_amount) AS net_eska
                    FROM selling_out_eskalink
                    WHERE invoice_date BETWEEN '$startDate' AND '$endDate'
                    GROUP BY
                        branch_code,
                        region_code,
                        region_name,
                        entity_code,
                        entity_name,
                        branch_name
                )
                SELECT
                    md.region_code,
                    md.region_name,
                    md.area_code AS entity_code,
                    md.area_name AS entity_name,
                    die.eskalink_code AS branch_code,
                    md.distributor_name AS branch_name,

                    COALESCE(soe.row_count, 0) AS row_count,
                    COALESCE(soe.qty_pcs, 0) AS qty_pcs,
                    COALESCE(soe.gross, 0) AS gross,
                    COALESCE(soe.ld4, 0) AS ld4,
                    COALESCE(soe.bb, 0) AS bb,

                    FLOOR(COALESCE(soe.dpp, 0)) AS dpp,
                    FLOOR(COALESCE(soe.tax, 0)) AS tax,
                    FLOOR(COALESCE(soe.net_eska, 0)) AS net_eska,
                    FLOOR(COALESCE(sid.net_siso, 0)) AS net_siso,

                    FLOOR(COALESCE(soe.net_eska, 0))
                        - FLOOR(COALESCE(sid.net_siso, 0)) AS selisih
                FROM distributor_implementasi_eskalink die
                LEFT JOIN master_distributors md
                    ON md.distributor_code = die.distributor_code
                LEFT JOIN soe_sum soe
                    ON soe.branch_code = die.eskalink_code
                LEFT JOIN sid_sum sid
                    ON sid.distributor_code = die.distributor_code
                WHERE die.implementasi = 'Y'
                AND md.is_active = true
                AND md.region_code IN ($regionsString)
            ) sales_data"));

            // Filter Implementasi (Sudah/Belum/Semua)
            if ($this->implementasiFilter === 'Y') {
                $query->where('net_eska', '>', 0);
            } elseif ($this->implementasiFilter === 'N') {
                $query->where('net_eska', '=', 0);
            }

            // Filter Status Selisih (OK / NOT OK)
            if ($this->statusFilter === 'OK') {
                $query->whereRaw('ABS(net_eska - net_siso) < 1000');
            } elseif ($this->statusFilter === 'NOT_OK') {
                $query->whereRaw('ABS(net_eska - net_siso) >= 1000');
            }

            $comparisons = $query
                ->orderBy('region_code')
                ->orderBy('entity_code')
                ->paginate(20);
        }

        return view('livewire.dashboard.sales-comparison', [
            'comparisons' => $comparisons,
            'summary'     => $this->summary,
        ])->layout('layouts.app');
    }
}