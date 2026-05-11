<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MappingNotification extends Component
{
    public $productCount = 0;
    public $salesmanCount = 0;
    public $unitCount = 0;
    public $totalCount = 0;

    protected $listeners = ['refreshNotifications' => 'updateCounts'];

    public function mount()
    {
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $user = Auth::user();
        if (!$user) return;

        $cacheKey = 'mapping_notification_counts_' . $user->id;

        // Cache for 1 minute to stay relatively fresh but avoid query storm
        $counts = Cache::remember($cacheKey, 60, function () use ($user) {
            return [
                'products' => $this->getUnmappedProductCount($user),
                'salesmen' => $this->getUnmappedSalesmanCount($user),
                'units' => $this->getUnmappedUnitCount($user),
            ];
        });

        $this->productCount = $counts['products'];
        $this->salesmanCount = $counts['salesmen'];
        $this->unitCount = $counts['units'];
        $this->totalCount = $this->productCount + $this->salesmanCount + $this->unitCount;
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

        // Only count within last 6 months to stay relevant
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

        // Only count within last 6 months
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
        return view('livewire.mapping-notification');
    }
}
