<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\PerbaikanTikorToko;

class GlobalNotification extends Component
{
    // Mapping Counts
    public $productCount = 0;
    public $salesmanCount = 0;
    public $unitCount = 0;
    public $mappingTotal = 0;

    // Tikor Counts
    public $tikorCount = 0;

    // JKS Approvals Counts
    public $jksCount = 0;

    public $totalNotifications = 0;

    protected $listeners = ['refreshNotifications' => 'updateCounts'];

    public function mount()
    {
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $user = Auth::user();
        if (!$user) return;

        // 1. Tikor Counts
        $tikorQuery = PerbaikanTikorToko::where('status', 'Pending');
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $tikorQuery->whereIn('region_code', is_array($user->region_code) ? $user->region_code : [$user->region_code]);
        }
        $this->tikorCount = $tikorQuery->count();

        // 2. JKS Approvals Counts
        $jksQuery = DB::table('jks_approvals')->where('status', 'PENDING');
        // Currently JKS Approvals page fetches all pending without scoping, we follow the same behavior.
        $this->jksCount = $jksQuery->count();

        // 3. Mapping Counts (Cached)
        $cacheKey = 'global_mapping_counts_' . $user->id;
        $mappingCounts = Cache::remember($cacheKey, 60, function () use ($user) {
            return [
                'products' => $this->getUnmappedProductCount($user),
                'salesmen' => $this->getUnmappedSalesmanCount($user),
                'units' => $this->getUnmappedUnitCount($user),
            ];
        });

        $this->productCount = $mappingCounts['products'];
        $this->salesmanCount = $mappingCounts['salesmen'];
        $this->unitCount = $mappingCounts['units'];
        $this->mappingTotal = $this->productCount + $this->salesmanCount + $this->unitCount;

        // Grand Total
        $this->totalNotifications = $this->tikorCount + $this->jksCount + $this->mappingTotal;
    }

    private function getUnmappedProductCount($user)
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
            ->whereNull('d.product_id');

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('b.region_code', (array)$user->region_code);
        }

        $query->where('a.invoice_date', '>=', now()->subMonths(6)->startOfMonth());
        return $query->select(DB::raw('COUNT(DISTINCT a.distributor_code || a.product_code) as count'))
                     ->first()->count ?? 0;
    }

    private function getUnmappedSalesmanCount($user)
    {
        $salesmanMappingsSub = DB::table('salesman_mappings')
            ->select('distributor_code', 'salesman_code_dist', DB::raw('MIN(salesman_code_prc) as salesman_code_prc'))
            ->groupBy('distributor_code', 'salesman_code_dist');

        $query = DB::table('sales_invoice_distributor as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code')
            ->leftJoinSub($salesmanMappingsSub, 'c', function ($join) {
                $join->on('a.distributor_code', '=', 'c.distributor_code')
                     ->on('a.salesman_code', '=', 'c.salesman_code_dist');
            })
            ->leftJoin('salesmans as d', 'c.salesman_code_prc', '=', 'd.salesman_code')
            ->whereNull('d.salesman_code')
            ->whereNotNull('a.salesman_code')
            ->where('a.salesman_code', '!=', '');

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('b.region_code', (array)$user->region_code);
        }

        $query->where('a.invoice_date', '>=', now()->subMonths(6)->startOfMonth());
        return $query->select(DB::raw('COUNT(DISTINCT a.distributor_code || a.salesman_code) as count'))
                     ->first()->count ?? 0;
    }

    private function getUnmappedUnitCount($user)
    {
        $query = DB::table('unmapped_units as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code');

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('b.region_code', (array)$user->region_code);
        }
        return $query->count();
    }

    public function render()
    {
        return view('livewire.global-notification');
    }
}
