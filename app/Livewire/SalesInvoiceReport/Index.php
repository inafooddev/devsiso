<?php

namespace App\Livewire\SalesInvoiceReport;

use Livewire\Component;
use App\Models\SalesInvoiceDistributor;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ConfigSalesInvoiceDistributor;
use App\Exports\SalesInvoiceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    // Properti untuk filter
    public $regionFilter = [];
    public $areaFilter = [];
    public $monthFilter;
    public $yearFilter;
    public $search = '';
    public $statusFilter;

    // Properti untuk filter EKSPOR
    public $exportRegionFilter;
    public $exportAreaFilter;
    public $exportDistributorFilter;
    public $exportMonthFilter;
    public $exportYearFilter;

    // Properti untuk data dropdown
    public $regions = [];
    public $areas = [];
    public $exportAreas = [];
    public $exportDistributors = [];

    // State untuk modal
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;
    public $isExportModalOpen = false;

    /**
     * Helper untuk memfilter query berdasarkan akses region user.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();
        
        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            // Kita gunakan table prefix jika kolomnya ambigu (seperti saat JOIN)
            $query->whereIn($column, $user->region_code);
        }
        
        return $query;
    }

    /**
     * Inisialisasi komponen.
     */
    public function mount()
    {
        // Filter daftar region yang muncul di dropdown awal
        $this->regions = $this->applyRegionAccess(MasterRegion::query())
            ->orderBy('region_name')
            ->where('region_code', '<>', 'HOINA')
            ->get();

        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
    }

    /**
     * Memperbarui daftar area saat region dipilih.
     */
    public function updatedRegionFilter()
    {
        $query = MasterArea::query();

        if (!empty($this->regionFilter)) {
            $query->whereIn('region_code', $this->regionFilter);
        } else {
            // Jika tidak ada region yang dipilih, tetap batasi berdasarkan hak akses user
            $this->applyRegionAccess($query);
        }

        $this->areas = $query->orderBy('area_name')->get();

        // Validasi agar area yang terpilih sebelumnya tetap valid dalam scope region baru
        $availableAreaCodes = $this->areas->pluck('area_code')->toArray();
        $this->areaFilter = array_intersect($this->areaFilter, $availableAreaCodes);
    }

    /**
     * Terapkan filter dan tutup modal.
     */
    public function applyFilters()
    {
        $this->isFilterModalOpen = false;
        $this->hasAppliedFilters = true;
    }

    /**
     * Reset semua filter.
     */
    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'monthFilter', 'yearFilter', 'search', 'statusFilter']);
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
        $this->areas = collect();
        $this->applyFilters();
    }

    public function selectAllRegions()
    {
        $this->regionFilter = $this->regions->pluck('region_code')->toArray();
        $this->updatedRegionFilter();
    }

    public function clearRegions()
    {
        $this->reset('regionFilter');
        $this->updatedRegionFilter();
    }

    public function selectAllAreas()
    {
        $this->areaFilter = $this->areas->pluck('area_code')->toArray();
    }

    public function clearAreas()
    {
        $this->reset('areaFilter');
    }

    // --- LOGIKA UNTUK FILTER EKSPOR (DI DALAM MODAL) ---
    public function updatedExportRegionFilter($value)
    {
        $this->reset(['exportAreaFilter', 'exportDistributorFilter']);
        
        $query = MasterArea::query();
        if ($value) {
            $query->where('region_code', $value);
        } else {
            $this->applyRegionAccess($query);
        }
        
        $this->exportAreas = $query->orderBy('area_name')->get();
    }

    public function updatedExportAreaFilter($value)
    {
        $this->reset('exportDistributorFilter');
        $this->exportDistributors = $value ? MasterDistributor::where('area_code', $value)->orderBy('distributor_name')->get() : collect();
    }

    /**
     * Render komponen.
     */
    public function render()
    {
        $summaryData = collect();
        $grandTotals = null;

        if ($this->hasAppliedFilters) {
            // 1. Subquery untuk Summary Sales
            $salesSummary = SalesInvoiceDistributor::query()
                ->select('distributor_code',
                    DB::raw('MAX(invoice_date) as last_up'),
                    DB::raw('COUNT(*) as baris'),
                    DB::raw('SUM(carton_qty) as ktn'),
                    DB::raw('SUM(pack_qty) as pak'),
                    DB::raw('SUM(pcs_qty) as pcs'),
                    DB::raw('SUM(quantity) as qty'),
                    DB::raw('SUM(gross_amount) as gross_raw'),
                    DB::raw('SUM(discount4) as discount4_raw'),
                    DB::raw('SUM(discount8) as discount8_raw'),
                    DB::raw('SUM(dpp) as dpp_raw'),
                    DB::raw('SUM(tax) as tax_raw'),
                    DB::raw('SUM(net_amount) as nett_raw')
                )
                ->groupBy('distributor_code');

            if ($this->monthFilter) $salesSummary->whereMonth('invoice_date', $this->monthFilter);
            if ($this->yearFilter) $salesSummary->whereYear('invoice_date', $this->yearFilter);

            // 2. Query Utama Master Distributor
            $kodemapingQuery = ConfigSalesInvoiceDistributor::select('distributor_code')
                ->whereColumn('distributor_code', 'master_distributors.distributor_code')
                ->limit(1);

            $query = MasterDistributor::query()
                ->select('master_distributors.*', 's.*')
                ->selectSub($kodemapingQuery, 'kodemaping')
                ->leftJoinSub($salesSummary, 's', fn ($join) => $join->on('master_distributors.distributor_code', '=', 's.distributor_code'));
            
            // --- PENERAPAN SCOPE KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'master_distributors.region_code');

            // --- FILTER TAMBAHAN DARI UI ---
            if ($this->search) {
                $query->where(fn($q) => $q->where('master_distributors.region_name', 'ilike', "%{$this->search}%")
                      ->orWhere('master_distributors.area_name', 'ilike', "%{$this->search}%")
                      ->orWhere('master_distributors.distributor_name', 'ilike', "%{$this->search}%"));
            }
            if (!empty($this->regionFilter)) $query->whereIn('master_distributors.region_code', $this->regionFilter);
            if (!empty($this->areaFilter)) $query->whereIn('master_distributors.area_code', $this->areaFilter);
            if ($this->statusFilter !== '' && $this->statusFilter !== null) $query->where('master_distributors.is_active', $this->statusFilter);
            
            $summaryData = $query->orderBy('master_distributors.region_name')
                                 ->orderBy('master_distributors.area_name')
                                 ->get();

            // Hitung total keseluruhan
            $grandTotals = [
                'total_baris' => $summaryData->sum('baris'),
                'total_ktn' => $summaryData->sum('ktn'),
                'total_pak' => $summaryData->sum('pak'),
                'total_pcs' => $summaryData->sum('pcs'),
                'total_quantity' => $summaryData->sum('qty'),
                'total_gross' => $summaryData->sum('gross_raw'),
                'total_cashback' => $summaryData->sum('discount4_raw'),
                'total_bonusbarang' => $summaryData->sum('discount8_raw'),
                'total_dpp' => $summaryData->sum('dpp_raw'),
                'total_tax' => $summaryData->sum('tax_raw'),
                'total_nett' => $summaryData->sum('nett_raw'),
            ];
        }

        return view('livewire.sales-invoice-report.index', [
            'summaryData' => $summaryData,
            'grandTotals' => $grandTotals,
        ])->layout('layouts.app');
    }

    public function export()
    {
        $this->isExportModalOpen = false;

        // Untuk Ekspor, kita pastikan region yang dilempar ke Excel juga tervalidasi hak aksesnya
        $finalRegionFilter = $this->exportRegionFilter;
        
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            // Jika user iseng mengosongkan region di modal ekspor, 
            // kita paksa ekspor semua region miliknya.
            if (empty($finalRegionFilter)) {
                $finalRegionFilter = $user->region_code;
            } 
            // Jika user memilih region, pastikan region tersebut ada dalam hak aksesnya
            elseif (is_array($finalRegionFilter)) {
                $finalRegionFilter = array_intersect($finalRegionFilter, $user->region_code);
            } else {
                if (!in_array($finalRegionFilter, $user->region_code)) {
                    $finalRegionFilter = $user->region_code;
                }
            }
        }

        $filters = [
            'regionFilter' => $finalRegionFilter,
            'areaFilter' => $this->exportAreaFilter,
            'distributorFilter' => $this->exportDistributorFilter,
            'monthFilter' => $this->exportMonthFilter,
            'yearFilter' => $this->exportYearFilter,
        ];

        return Excel::download(new SalesInvoiceExport($filters), 'laporan_sales_invoice_detail.xlsx');
    }
}