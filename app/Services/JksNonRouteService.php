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
        return DB::table('jks_se_master_toko_ool as mto')
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
            })
            ->select([
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
            ])
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
    }
}
