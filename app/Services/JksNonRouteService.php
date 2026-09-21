<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JksNonRouteService
{
    /**
     * Get paginated and filtered Non Route JKS list.
     *
     * @param string $search
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredPaginatedList($search = '', $filters = [], $perPage = 50)
    {
        // Require at least one hierarchy filter to prevent loading massive unindexed data initially
        if (empty($search) && empty($filters['region']) && empty($filters['area']) && empty($filters['supervisor']) && empty($filters['distributor'])) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return $this->getBaseQuery($search, $filters)
            ->orderByDesc('mto.avg_value_net')
            ->orderBy('mto.distributor_name')
            ->orderBy('mto.customer_name')
            ->paginate($perPage);
    }

    /**
     * Build the base query for Outlet Non JKS.
     *
     * @param string $search
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getBaseQuery($search = '', $filters = [])
    {
        $bulan = $filters['bulan'] ?? date('Y-m-01');
        $baseBulan = \Carbon\Carbon::parse($bulan);
        
        $query = DB::table('jks_se_master_toko_ool as mto')
            ->leftJoin('jks_salesmans as js', function($join) {
                $join->on('mto.distributor_code', '=', 'js.distributor_code')
                     ->on('mto.customer_code', '=', 'js.customer_code');
            })
            ->leftJoin('jks_se_toko_ool_remarks as mtor', function($join) {
                $join->on('mto.distributor_code', '=', 'mtor.distributor_code')
                     ->on('mto.customer_code', '=', 'mtor.customer_code');
            })
            ->leftJoin('jks_approvals as ja', function($join) {
                $join->on('mto.distributor_code', '=', 'ja.distributor_code')
                     ->where('ja.status', '=', 'PENDING')
                     ->where('ja.action_type', '=', 'TAMBAH_JADWAL')
                     ->whereRaw("ja.payload->>'customer_code' = mto.customer_code");
            });

        // Dynamic 6 months history joins
        $selectCols = [
            'mto.id',
            'mto.region_code',
            'mto.region_name',
            'mto.area_code',
            'mto.area_name',
            'mto.supervisor_code',
            'mto.supervisor_name',
            'mto.distributor_code',
            'mto.distributor_name',
            'mto.customer_code',
            'mto.customer_eska',
            'mto.customer_name',
            'mto.alamat',
            'mto.avg_value_net',
            'mtor.remark',
            'ja.id as pending_approval_id'
        ];

        for ($i = 1; $i <= 6; $i++) {
            $m = $baseBulan->copy()->subMonths($i)->format('Y-m-01');
            $alias = "hist{$i}";
            $query->leftJoin("zv_so_per_toko_2026 as {$alias}", function($join) use ($alias, $m) {
                $join->on('mto.customer_code', '=', "{$alias}.uniq_kd")
                     ->on('mto.distributor_code', '=', "{$alias}.kd_dist")
                     ->where("{$alias}.bulan", '=', $m);
            });
            $selectCols[] = "{$alias}.neto as history_{$i}";
        }

        $query->select($selectCols)
            ->whereNull('js.id')
            ->when($filters['region'] ?? null, function ($q, $region) {
                return $q->where('mto.region_code', $region);
            })
            ->when($filters['area'] ?? null, function ($q, $area) {
                return $q->where('mto.area_code', $area);
            })
            ->when($filters['supervisor'] ?? null, function ($q, $supervisor) {
                return $q->where('mto.supervisor_code', $supervisor);
            })
            ->when($filters['distributor'] ?? null, function ($q, $distributor) {
                return $q->where('mto.distributor_code', $distributor);
            })
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('mto.customer_code', 'ilike', '%' . $search . '%')
                          ->orWhere('mto.customer_name', 'ilike', '%' . $search . '%')
                          ->orWhere('mto.alamat', 'ilike', '%' . $search . '%');
                }
            });
            
        $user = auth()->user();
        if ($user && !$user->hasRole(['admin', 'spm'])) {
            $accessLevel = $user->getAccessLevel();
            if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
                // mto.supervisor_code is team_elite_code, and user->supervisor_code is team_elite_code
                $query->whereIn('mto.supervisor_code', (array) $user->supervisor_code);
            } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
                $query->whereIn('mto.area_code', (array) $user->area_code);
            } elseif ($accessLevel === 'region' && !empty($user->region_code)) {
                $query->whereIn('mto.region_code', (array) $user->region_code);
            }
        }
        
        return $query;
    }
}
