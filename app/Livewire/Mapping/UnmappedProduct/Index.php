<?php

namespace App\Livewire\Mapping\UnmappedProduct;

use Livewire\Component;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ProductMaster;
use App\Models\ProductMapping;
use App\Exports\UnmappedProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'mapping.unmapped-products';

    // Filter properties
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

    // Modal states
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;
    public $isMapModalOpen = false;

    // Mapping Modal Properties
    public $currentProductToMap = null; 
    public $productSearch = '';
    public $principalProducts = [];
    public $selectedPrincipalProduct;
    public $selectedPrincipalProductName;

    protected $queryString = ['search'];

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

    /**
     * Helper to ensure distributor is within user's region
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // 1. Initial regions with access control
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA');
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Default Month/Year
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;

        // 3. Auto-select if only 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }

        // 4. Restore filters from session
        if (session()->has('unmapped_product_filters')) {
            $filters = session()->get('unmapped_product_filters');
            $this->regionFilter = $filters['regionFilter'] ?? $this->regionFilter;
            $this->areaFilter = $filters['areaFilter'] ?? null;
            $this->distributorFilter = $filters['distributorFilter'] ?? null;
            $this->monthFilter = $filters['monthFilter'] ?? $this->monthFilter;
            $this->yearFilter = $filters['yearFilter'] ?? $this->yearFilter;
            $this->search = $filters['search'] ?? '';
            $this->hasAppliedFilters = $filters['hasAppliedFilters'] ?? false;

            if ($this->regionFilter) {
                $areaQuery = MasterArea::where('region_code', $this->regionFilter);
                $this->applyRegionAccess($areaQuery);
                $this->areas = $areaQuery->orderBy('area_name')->get();
            }
            if ($this->areaFilter) {
                $distQuery = MasterDistributor::where('area_code', $this->areaFilter);
                $this->applyRegionAccess($distQuery);
                $this->distributors = $distQuery->orderBy('is_active', 'desc')->get();
            }
        }
    }

    // --- FILTER LOGIC ---
    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        $query = MasterArea::query();
        if ($value) $query->where('region_code', $value);
        $this->applyRegionAccess($query);
        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        $query = MasterDistributor::query();
        if ($value) $query->where('area_code', $value);
        $this->applyRegionAccess($query);
        $this->distributors = $value ? $query->orderBy('is_active','desc')->get() : collect();
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->hasAppliedFilters = true;
        $this->isFilterModalOpen = false;
        $this->saveFiltersToSession();
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'monthFilter', 'yearFilter', 'search']);
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
        $this->areas = collect();
        $this->distributors = collect();
        $this->hasAppliedFilters = false;
        session()->forget('unmapped_product_filters');

        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    protected function saveFiltersToSession()
    {
        session()->put('unmapped_product_filters', [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'monthFilter' => $this->monthFilter,
            'yearFilter' => $this->yearFilter,
            'search' => $this->search,
            'hasAppliedFilters' => $this->hasAppliedFilters,
        ]);
    }

    // --- QUERY BUILDING ---
    private function buildQuery()
    {
        $productMappingsSub = DB::table('product_mappings')
            ->select('distributor_code', 'product_code_dist', DB::raw('MIN(product_code_prc) as product_code_prc'))
            ->groupBy('distributor_code', 'product_code_dist');

        $query = DB::table('sales_invoice_distributor as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code')
            ->leftJoinSub($productMappingsSub, 'c', function ($join) {
                $join->on('a.distributor_code', '=', 'c.distributor_code')
                     ->on('a.product_code', '=', 'c.product_code_dist');
            })
            ->leftJoin('product_masters as d', 'c.product_code_prc', '=', 'd.product_id')
            ->select(
                'a.distributor_code',
                'b.distributor_name',
                'a.product_code',
                'a.product_name'
            )
            ->whereNull('d.product_id') 
            ->groupBy(
                'a.distributor_code',
                'b.distributor_name',
                'a.product_code',
                'a.product_name'
            );

        $this->applyRegionAccess($query, 'b.region_code');

        if ($this->regionFilter) $query->where('b.region_code', $this->regionFilter);
        if ($this->areaFilter) $query->where('b.area_code', $this->areaFilter);
        if ($this->distributorFilter) $query->where('a.distributor_code', $this->distributorFilter);
        
        if ($this->monthFilter && $this->yearFilter) {
            $startDate = Carbon::create($this->yearFilter, $this->monthFilter, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $query->whereBetween('a.invoice_date', [$startDate, $endDate]);
        }
        
        if ($this->search) {
             $query->where(function($q) {
                $q->where('a.product_code', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('a.product_name', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('b.distributor_name', 'ILIKE', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function render()
    {
        $products = collect();
        if ($this->hasAppliedFilters) {
            $query = $this->buildQuery();
            $products = $query->paginate(10);
        }

        return view('livewire.mapping.unmapped-product.index', [
            'products' => $products,
        ])->layout('layouts.app');
    }

    // --- MAPPING LOGIC ---
    public function openMapModal($distributorCode, $productCode, $productName)
    {
        if (!$this->checkDistributorAccess($distributorCode)) {
            session()->flash('error', 'Anda tidak memiliki otoritas pada distributor ini.');
            return;
        }

        $this->currentProductToMap = [
            'distributor_code' => $distributorCode,
            'product_code_dist' => $productCode,
            'product_name_dist' => $productName,
        ];
        $this->reset(['productSearch', 'principalProducts', 'selectedPrincipalProduct', 'selectedPrincipalProductName']);
        $this->isMapModalOpen = true;
    }

    public function updatedProductSearch($value)
    {
        if(strlen($value) < 2) {
            $this->principalProducts = collect();
            return;
        }
        $this->principalProducts = ProductMaster::where('product_name', 'ILIKE', '%' . $value . '%')
                                    ->orWhere('product_id', 'ILIKE', '%' . $value . '%')
                                    ->orderBy('is_active', 'desc')
                                    ->limit(10)
                                    ->get();
    }

    public function selectProduct($productId, $productName)
    {
        $this->selectedPrincipalProduct = $productId;
        $this->selectedPrincipalProductName = $productName;
        $this->productSearch = '';
        $this->principalProducts = collect();
    }

    public function saveMapping()
    {
        $this->authorizeAction('can_edit');

        if (!$this->checkDistributorAccess($this->currentProductToMap['distributor_code'])) {
            session()->flash('error', 'Anda tidak memiliki otoritas pada distributor ini.');
            $this->isMapModalOpen = false;
            return;
        }

        $this->validate([
            'selectedPrincipalProduct' => 'required|string|exists:product_masters,product_id'
        ]);

        ProductMapping::updateOrCreate(
            [
                'distributor_code' => $this->currentProductToMap['distributor_code'],
                'product_code_dist' => $this->currentProductToMap['product_code_dist'],
            ],
            [
                'product_name_dist' => $this->currentProductToMap['product_name_dist'],
                'product_code_prc' => $this->selectedPrincipalProduct,
            ]
        );

        $this->isMapModalOpen = false;
        
        // Refresh sidebar and navbar badges
        \Illuminate\Support\Facades\Cache::forget('unmapped_counts_' . auth()->id());
        \Illuminate\Support\Facades\Cache::forget('mapping_notification_counts_' . auth()->id());
        $this->dispatch('refreshNotifications');
        
        session()->flash('message', 'Produk berhasil dipetakan.');
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        if (!$this->hasAppliedFilters) {
             session()->flash('error', 'Terapkan filter terlebih dahulu.');
             return;
        }

        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

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
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [],
        ];

        return Excel::download(new UnmappedProductsExport($filters), 'laporan_produk_belum_terpetakan.xlsx');
    }

    public function updatedSearch()
    {
        if ($this->hasAppliedFilters) {
            $this->resetPage();
            $this->saveFiltersToSession();
        }
    }
}