<?php

namespace App\Livewire\Mapping\UnmappedSalesman;

use Livewire\Component;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\Salesman;
use App\Models\SalesmanMapping;
use App\Exports\UnmappedSalesmansExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filter
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $monthFilter;
    public $yearFilter;
    public $search = '';

    // Dropdown data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // State
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;

    // Properti untuk Modal Mapping
    public $isMapModalOpen = false;
    public $currentSalesmanToMap = null; // [dist_code, salesman_code, salesman_name]
    public $principalSalesmans = []; 
    public $selectedPrincipalSalesman; 

    protected $queryString = ['search'];

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

    /**
     * Helper untuk memastikan distributor terkait berada di dalam wilayah user
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // 1. Terapkan akses region ke dropdown
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national'
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
        }

        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        
        $query = MasterArea::query();
        if ($value) {
            $query->where('region_code', $value);
        }
        
        // Amankan dropdown area
        $this->applyRegionAccess($query);
        
        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        
        $query = MasterDistributor::query();
        if ($value) {
            $query->where('area_code', $value);
        }
        
        // Amankan dropdown distributor
        $this->applyRegionAccess($query);
        
        $this->distributors = $value ? $query->orderBy('is_active','desc')->get() : collect();
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->hasAppliedFilters = true;
        $this->isFilterModalOpen = false;
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'monthFilter', 'yearFilter', 'search']);
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
        $this->areas = collect();
        $this->distributors = collect();
        $this->hasAppliedFilters = false;

        // Auto-select ulang setelah reset jika user biasa
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    /**
     * Membuat query dasar untuk data salesman yang belum terpetakan.
     */
    private function buildQuery()
    {
        // Subquery untuk salesman_mappings
        $salesmanMappingsSub = DB::table('salesman_mappings')
            ->select('distributor_code', 'salesman_code_dist', DB::raw('MIN(salesman_code_prc) as salesman_code_prc'))
            ->groupBy('distributor_code', 'salesman_code_dist');

        // Query utama
        $query = DB::table('sales_invoice_distributor as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code')
            ->leftJoinSub($salesmanMappingsSub, 'c', function ($join) {
                $join->on('a.distributor_code', '=', 'c.distributor_code')
                     ->on('a.salesman_code', '=', 'c.salesman_code_dist');
            })
            ->leftJoin('salesmans as d', 'c.salesman_code_prc', '=', 'd.salesman_code')
            ->select(
                'a.distributor_code',
                'b.distributor_name',
                'a.salesman_code', 
                'a.salesman_name'  
            )
            ->whereNull('d.salesman_code') 
            ->whereNotNull('a.salesman_code')
            ->where('a.salesman_code', '!=', '')
            ->groupBy(
                'a.distributor_code',
                'b.distributor_name',
                'a.salesman_code',
                'a.salesman_name'
            );

        // --- PROTEKSI KEAMANAN DATA ---
        // 'b' adalah alias untuk master_distributors
        $this->applyRegionAccess($query, 'b.region_code');

        // Terapkan filter
        if ($this->regionFilter) {
            $query->where('b.region_code', $this->regionFilter);
        }
        if ($this->areaFilter) {
            $query->where('b.area_code', $this->areaFilter);
        }
        if ($this->distributorFilter) {
            $query->where('a.distributor_code', $this->distributorFilter);
        }
        
        if ($this->monthFilter && $this->yearFilter) {
            $startDate = Carbon::create($this->yearFilter, $this->monthFilter, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $query->whereBetween('a.invoice_date', [$startDate, $endDate]);
        }
        
        if ($this->search) {
             $query->where(function($q) {
                $q->where('a.salesman_code', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('a.salesman_name', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('b.distributor_name', 'ILIKE', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function render()
    {
        $salesmans = collect();

        if ($this->hasAppliedFilters) {
            $query = $this->buildQuery();
            $salesmans = $query->paginate(15);
        }

        return view('livewire.mapping.unmapped-salesman.index', [
            'salesmans' => $salesmans,
        ])->layout('layouts.app');
    }

    // Logika untuk Modal Mapping
    public function openMapModal($distributorCode, $salesmanCode, $salesmanName)
    {
        // Security Check: Pastikan user hanya bisa membuka modal untuk regionnya
        if (!$this->checkDistributorAccess($distributorCode)) {
            session()->flash('error', 'Anda tidak memiliki otoritas pada distributor ini.');
            return;
        }

        $this->currentSalesmanToMap = [
            'distributor_code' => $distributorCode,
            'salesman_code_dist' => $salesmanCode,
            'salesman_name_dist' => $salesmanName,
        ];
        
        // Reset pilihan sebelumnya
        $this->reset(['selectedPrincipalSalesman']);

        // Langsung isi daftar salesman principal berdasarkan distributor (Tidak butuh scope karena sudah terfilter dari parameter modal)
        $this->principalSalesmans = Salesman::where('distributor_code', $distributorCode)
                                    ->orderBy('salesman_name')
                                    ->get();

        $this->isMapModalOpen = true;
    }

    public function saveMapping()
    {
        // Double Security Check sebelum memanipulasi database
        if (!$this->checkDistributorAccess($this->currentSalesmanToMap['distributor_code'])) {
            session()->flash('error', 'Anda tidak memiliki otoritas pada distributor ini.');
            $this->isMapModalOpen = false;
            return;
        }

        $this->validate([
            'selectedPrincipalSalesman' => 'required|string|exists:salesmans,salesman_code' 
        ], [
            'selectedPrincipalSalesman.required' => 'Anda harus memilih salesman principal.'
        ]);

        SalesmanMapping::updateOrCreate(
            [
                'distributor_code' => $this->currentSalesmanToMap['distributor_code'],
                'salesman_code_dist' => $this->currentSalesmanToMap['salesman_code_dist'],
            ],
            [
                'salesman_name_dist' => $this->currentSalesmanToMap['salesman_name_dist'],
                'salesman_code_prc' => $this->selectedPrincipalSalesman,
            ]
        );

        $this->isMapModalOpen = false;
        session()->flash('message', 'Salesman berhasil dipetakan.');
    }

    public function export()
    {
        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu sebelum mengekspor data.');
             return;
        }

        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

        // Validasi ekstra untuk Export (Mencegah manipulasi user biasa)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!empty($finalRegionFilter) && !in_array($finalRegionFilter, $user->region_code)) {
                $finalRegionFilter = ''; 
            }
        }

        $filters = [
            'regionFilter' => $finalRegionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'monthFilter' => $this->monthFilter,
            'yearFilter' => $this->yearFilter,
            'search' => $this->search,
            // Parameter khusus untuk proteksi Array Scope pada proses Excel Export
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [],
        ];

        return Excel::download(new UnmappedSalesmansExport($filters), 'laporan_salesman_belum_terpetakan.xlsx');
    }
}