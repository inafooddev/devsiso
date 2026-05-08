<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SidebarComposer
{
    public function compose(View $view)
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $cacheKey = 'unmapped_counts_' . $user->id;

        // Cache counts for 5 minutes to avoid heavy queries on every page load
        $counts = Cache::remember($cacheKey, 300, function () use ($user) {
            return [
                'products' => $this->getUnmappedProductCount($user),
                'salesmen' => $this->getUnmappedSalesmanCount($user),
            ];
        });

        $view->with('unmappedCounts', $counts);
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
            $query->whereIn('b.region_code', $user->region_code);
        }

        // Limit to last 6 months to keep it relevant and performant
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
            $query->whereIn('b.region_code', $user->region_code);
        }

        // Limit to last 6 months
        $query->where('a.invoice_date', '>=', now()->subMonths(6)->startOfMonth());

        return $query->select(DB::raw('COUNT(DISTINCT a.distributor_code || a.salesman_code) as count'))
                     ->first()->count ?? 0;
    }
}
