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

    // ======================
    // FILTER
    // ======================
    public $selectedMonth;              // YYYY-MM
    public $selectedRegions = [];       // multi select
    public $selectedImplementasi = 'ALL'; // Default: ALL (Semua)

    // ======================
    // UI STATE
    // ======================
    public $showFilterModal = false;
    public $isFiltered = false;
    public $showImportModal = false;

    // Import Variable
    public $importFile;

    // ======================
    // OPTIONS
    // ======================
    public $regionsOption = [];

    // ======================
    // SUMMARY (GLOBAL)
    // ======================
    public $summary = null;

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }

        return $query;
    }

    // ======================
    // MOUNT
    // ======================
    public function mount()
    {
        $this->selectedMonth = date('Y-m');
        $this->selectedImplementasi = 'ALL'; // Set default awal ke Semua

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
            $this->selectedRegions = [$this->regionsOption->first()->region_code];
        }
    }

    // ======================
    // SELECT ALL REGION
    // ======================
    public function selectAllRegions()
    {
        $this->selectedRegions = $this->regionsOption
            ->pluck('region_code')
            ->toArray();
    }

    // ======================
    // APPLY FILTER
    // ======================
    public function filter()
    {
        $this->validate([
            'selectedMonth'        => 'required',
            'selectedRegions'      => 'required|array|min:1',
            'selectedImplementasi' => 'required|in:Y,N,ALL', 
        ]);

        // Security check: Pastikan SEMUA region yang difilter (array) valid sesuai akses login
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $unauthorizedRegions = array_diff($this->selectedRegions, $user->region_code);
            if (!empty($unauthorizedRegions)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk memfilter beberapa wilayah yang dipilih.');
                return;
            }
        }

        $this->isFiltered = true;
        $this->showFilterModal = false;
        $this->resetPage();
    }

    public function updatedImportFile()
    {
        $this->validateOnly('importFile', [
            'importFile' => 'file|max:102400|mimes:xlsx,xls'
        ]);
    }

    public function import()
    {
        // Hanya Admin yang biasanya diizinkan mengimpor data massal ini
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
            session()->flash('success', 'Import berhasil!');
            $this->showImportModal = false;
            $this->importFile = null;
            $this->mount(); 
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    // ======================
    // RENDER
    // ======================
    public function render()
    {
        $comparisons = collect();

        if ($this->isFiltered) {

            // ----------------------
            // DATE RANGE
            // ----------------------
            $startDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)
                ->startOfMonth()->format('Y-m-d');

            $endDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)
                ->endOfMonth()->format('Y-m-d');

            // --- PROTEKSI KEAMANAN DATA RAW QUERY ---
            // Kita saring ulang array region untuk memastikan aman dari payload bypass
            $finalRegions = $this->selectedRegions;
            $user = auth()->user();
            if (!$user->hasRole('admin') && !empty($user->region_code)) {
                $finalRegions = array_intersect($finalRegions, $user->region_code);
                // Fallback jika kosong (user memanipulasi semua array sehingga terbuang semua)
                if (empty($finalRegions)) {
                    $finalRegions = $user->region_code;
                }
            }

            $regionsString = "'" . implode("','", $finalRegions) . "'";

            // =====================================================
            // SUMMARY QUERY (NO PAGINATION)
            // =====================================================
            $this->summary = DB::table(DB::raw("(
                SELECT
                    die.eskalink_code AS branch_code,
                    FLOOR(COALESCE(soe.net_eska, 0)) AS net_eska,
                    FLOOR(COALESCE(sid.net_siso, 0)) AS net_siso
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
                SUM(CASE WHEN net_eska <> 0 THEN 1 ELSE 0 END) AS net_siso_non_zero
            ')
            ->first();

            // =====================================================
            // MAIN TABLE QUERY (WITH PAGINATION)
            // =====================================================
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
            if ($this->selectedImplementasi === 'Y') {
                // Sudah = Net Eska > 0
                $query->where('net_eska', '>', 0);
            } elseif ($this->selectedImplementasi === 'N') {
                // Belum = Net Eska = 0
                $query->where('net_eska', '=', 0);
            }
            // Jika ALL, tidak ada filter tambahan (tampilkan semua)

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