<?php

namespace App\Livewire\Others\Qceskalink;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Imports\SellingOutEskalinkImport;
use App\Exports\SalesComparisonExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use App\Traits\EnforcesMenuPermissions;

class SalesComparison extends Component
{
    use WithPagination;
    use WithFileUploads;
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'dashboard.sales-comparison';

    protected $paginationTheme = 'tailwind';

    // Inline Filters
    public $search = '';
    public $monthFilter;
    public $regionFilter = '';
    public $implementasiFilter = 'ALL'; // ALL, Y (Sudah Tarik), N (Belum Tarik)
    public $statusFilter = 'ALL';       // ALL, OK, NOT_OK

    // Sorting
    public $sortField = 'region_name';
    public $sortDirection = 'asc';

    // UI States
    public $isImportModalOpen = false;
    public $isFiltered = true;

    // Import
    public $importFile;

    // Dropdown options
    public $regionsOption = [];

    // Summary data
    public $summary = null;

    /**
     * Helper to filter Query based on user region access.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, (array) $user->region_code);
        }
        return $query;
    }

    public function mount()
    {
        $this->monthFilter = Carbon::now()->subMonth()->format('Y-m');
        $this->regionFilter = '';
        $this->implementasiFilter = 'ALL';
        $this->statusFilter = 'ALL';

        // Load Region Options dengan Proteksi Akses
        $query = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('is_active', true)
            ->where('region_code', '!=', 'HOINA')
            ->distinct();

        $this->applyRegionAccess($query);
        $this->regionsOption = $query->orderBy('region_name')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
    {
        $this->resetPage();
    }

    public function updatingRegionFilter()
    {
        $this->resetPage();
    }

    public function updatingImplementasiFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            // Untuk kolom angka/selisih, default klik pertama adalah desc (nilai terbesar di atas)
            $numericFields = ['qty_pcs', 'ld4', 'bb', 'net_eska', 'net_siso', 'selisih'];
            $this->sortDirection = in_array($field, $numericFields) ? 'desc' : 'asc';
        }
        $this->resetPage();
    }

    public function quickFilter($implementasi = 'ALL', $status = 'ALL')
    {
        $this->implementasiFilter = $implementasi;
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->regionFilter = '';
        $this->implementasiFilter = 'ALL';
        $this->statusFilter = 'ALL';
        $this->sortField = 'region_name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function export()
    {
        $fileName = 'Sales_Comparison_' . $this->monthFilter . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(
            new SalesComparisonExport(
                $this->monthFilter,
                $this->regionFilter,
                $this->implementasiFilter,
                $this->statusFilter,
                $this->search,
                $this->sortField,
                $this->sortDirection
            ),
            $fileName
        );
    }

    public function updatedImportFile()
    {
        $this->validateOnly('importFile', [
            'importFile' => 'file|max:102400|mimes:xlsx,xls'
        ]);
    }

    public function import()
    {
        $this->authorizeAction('can_import');

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
        $month = $this->monthFilter ?: Carbon::now()->subMonth()->format('Y-m');
        $startDate = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($month)->endOfMonth()->format('Y-m-d');

        // Subquery Selling Out Eskalink (ESKA)
        $soeSub = DB::table('selling_out_eskalink')
            ->select(
                'branch_code',
                DB::raw('COUNT(*) AS row_count'),
                DB::raw('SUM(qty3_pcs) AS qty_pcs'),
                DB::raw('SUM(gross_amount) AS gross'),
                DB::raw('SUM(line_discount_4) AS ld4'),
                DB::raw('SUM(line_discount_8) AS bb'),
                DB::raw('SUM(dpp) AS dpp'),
                DB::raw('SUM(tax) AS tax'),
                DB::raw('SUM(nett_amount) AS net_eska')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('branch_code');

        // Subquery Sales Invoice Distributor (SID)
        $sidSub = DB::table('sales_invoice_distributor')
            ->select(
                'distributor_code',
                DB::raw('SUM(net_amount) AS net_siso')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('distributor_code');

        // Base Query (Master Distributor Implementasi Eskalink)
        $baseQuery = DB::table('distributor_implementasi_eskalink as die')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->leftJoinSub($soeSub, 'soe', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoinSub($sidSub, 'sid', 'sid.distributor_code', '=', 'die.distributor_code')
            ->where('die.implementasi', 'Y')
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA');

        // Proteksi akses region user
        $this->applyRegionAccess($baseQuery, 'md.region_code');

        // Filter Region
        if (!empty($this->regionFilter)) {
            $baseQuery->where('md.region_code', $this->regionFilter);
        }

        // Filter Search
        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $baseQuery->where(function ($q) use ($s) {
                $q->where('md.distributor_name', 'ilike', $s)
                  ->orWhere('die.eskalink_code', 'ilike', $s)
                  ->orWhere('md.distributor_code', 'ilike', $s)
                  ->orWhere('md.area_name', 'ilike', $s)
                  ->orWhere('md.region_name', 'ilike', $s);
            });
        }

        // 1. Summary KPI (Poin A: Pisahkan OK dan NOT OK hanya untuk cabang yang sudah tarik data SO Eska)
        $this->summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) AS total_branch,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) = 0 THEN 1 ELSE 0 END) AS net_siso_zero,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) <> 0 THEN 1 ELSE 0 END) AS net_siso_non_zero,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) <> 0 AND ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) < 1000 THEN 1 ELSE 0 END) AS total_ok,
                SUM(CASE WHEN FLOOR(COALESCE(soe.net_eska, 0)) <> 0 AND ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) >= 1000 THEN 1 ELSE 0 END) AS total_not_ok
            ')
            ->first();

        // 2. Filter Status Penarikan & Status Selisih untuk Tabel dan Grand Total
        $filteredQuery = clone $baseQuery;

        if ($this->implementasiFilter === 'Y') {
            $filteredQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) <> 0');
        } elseif ($this->implementasiFilter === 'N') {
            $filteredQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) = 0');
        }

        if ($this->statusFilter === 'OK') {
            $filteredQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) <> 0')
                          ->whereRaw('ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) < 1000');
        } elseif ($this->statusFilter === 'NOT_OK') {
            $filteredQuery->whereRaw('FLOOR(COALESCE(soe.net_eska, 0)) <> 0')
                          ->whereRaw('ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) >= 1000');
        }

        // 3. Poin B: Hitung Grand Total untuk seluruh baris yang terfilter
        $totals = (clone $filteredQuery)
            ->selectRaw('
                COALESCE(SUM(COALESCE(soe.qty_pcs, 0)), 0) AS total_qty_pcs,
                COALESCE(SUM(COALESCE(soe.ld4, 0)), 0) AS total_ld4,
                COALESCE(SUM(COALESCE(soe.bb, 0)), 0) AS total_bb,
                COALESCE(SUM(FLOOR(COALESCE(soe.net_eska, 0))), 0) AS total_net_eska,
                COALESCE(SUM(FLOOR(COALESCE(sid.net_siso, 0))), 0) AS total_net_siso,
                COALESCE(SUM(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))), 0) AS total_selisih
            ')
            ->first();

        // 4. Poin C: Main Table Query dengan Dynamic Sorting
        $tableQuery = (clone $filteredQuery)
            ->select(
                'md.region_code',
                'md.region_name',
                'md.area_code AS entity_code',
                'md.area_name AS entity_name',
                'die.eskalink_code AS branch_code',
                'md.distributor_name AS branch_name',
                DB::raw('COALESCE(soe.row_count, 0) AS row_count'),
                DB::raw('COALESCE(soe.qty_pcs, 0) AS qty_pcs'),
                DB::raw('COALESCE(soe.gross, 0) AS gross'),
                DB::raw('COALESCE(soe.ld4, 0) AS ld4'),
                DB::raw('COALESCE(soe.bb, 0) AS bb'),
                DB::raw('FLOOR(COALESCE(soe.dpp, 0)) AS dpp'),
                DB::raw('FLOOR(COALESCE(soe.tax, 0)) AS tax'),
                DB::raw('FLOOR(COALESCE(soe.net_eska, 0)) AS net_eska'),
                DB::raw('FLOOR(COALESCE(sid.net_siso, 0)) AS net_siso'),
                DB::raw('FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0)) AS selisih'),
                DB::raw('ABS(FLOOR(COALESCE(soe.net_eska, 0)) - FLOOR(COALESCE(sid.net_siso, 0))) AS abs_selisih')
            );

        $allowedSorts = [
            'region_name' => 'md.region_name',
            'entity_name' => 'md.area_name',
            'branch_code' => 'die.eskalink_code',
            'branch_name' => 'md.distributor_name',
            'qty_pcs'     => 'qty_pcs',
            'ld4'         => 'ld4',
            'bb'          => 'bb',
            'net_eska'    => 'net_eska',
            'net_siso'    => 'net_siso',
            'selisih'     => 'abs_selisih',
        ];

        $orderCol = $allowedSorts[$this->sortField] ?? 'md.region_name';
        $dir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $comparisons = $tableQuery
            ->orderBy($orderCol, $dir)
            ->orderBy('md.region_name')
            ->orderBy('md.area_name')
            ->orderBy('md.distributor_name')
            ->paginate(100);

        return view('livewire.others.qceskalink.sales-comparison', [
            'comparisons' => $comparisons,
            'summary'     => $this->summary,
            'totals'      => $totals,
        ])->layout('layouts.app');
    }
}
