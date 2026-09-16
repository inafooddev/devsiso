<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JksSalesmanService
{
    /**
     * Get paginated and filtered JKS Salesman list.
     *
     * @param string $search
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    /**
     * Build the base query for JKS Salesman.
     *
     * @param string $search
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getBaseQuery($search = '', $filters = [])
    {
        return DB::table('jks_salesmans as js')
            ->select([
                'js.id',
                'js.bulan',
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'tecm.team_elite_code as supervisor_code',
                'ms.description as supervisor_name',
                'md.distributor_code',
                'md.distributor_name',
                's.salesman_code',
                's.salesman_name',
                'js.customer_code as uniq_kode',
                'ltpte.customer_code_prc as customer_code',
                'ltpte.customer_name',
                'ltpte.customer_address',
                'ltpte.kecamatan',
                'ltpte.desa',
                'ltpte.latitude',
                'ltpte.longitude',
                'ltpte.pilar',
                'ltpte.target',
                'js.h1', 'js.h2', 'js.h3', 'js.h4', 'js.h5', 'js.h6', 'js.h7',
                'js.w1', 'js.w2', 'js.w3', 'js.w4'
            ])
            ->leftJoin('master_distributors as md', 'js.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('master_supervisors as ms', 'md.supervisor_code', '=', 'ms.supervisor_code')
            ->leftJoin('salesmans as s', function($join) {
                $join->on('js.distributor_code', '=', 's.distributor_code')
                     ->on('js.salesman_code', '=', 's.salesman_code');
            })
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->when($filters['region'] ?? null, function ($q, $region) {
                return $q->where('md.region_code', $region);
            })
            ->when($filters['area'] ?? null, function ($q, $area) {
                return $q->where('md.area_code', $area);
            })
            ->when($filters['supervisor'] ?? null, function ($q, $supervisor) {
                return $q->where('md.supervisor_code', $supervisor);
            })
            ->when($filters['distributor'] ?? null, function ($q, $distributor) {
                return $q->where('md.distributor_code', $distributor);
            })
            ->when($filters['salesman'] ?? null, function ($q, $salesman) {
                return $q->where('js.salesman_code', $salesman);
            })
            ->when($filters['bulan'] ?? null, function ($q, $bulan) {
                return $q->where('js.bulan', 'like', $bulan . '%');
            })
            ->when($filters['hari'] ?? null, function ($q, $hari) {
                if ($hari === 'non_rute') {
                    // Non Rute: h1-h6 semua bukan Y (termasuk yang hanya h7 atau kosong)
                    // DAN tidak ada minggu yang terisi
                    return $q->where(function ($queryBuilder) {
                        $queryBuilder->where(function ($sub) {
                            // Tidak ada hari Senin-Sabtu
                            $sub->where(fn($q) => $q->whereNull('js.h1')->orWhere('js.h1', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.h2')->orWhere('js.h2', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.h3')->orWhere('js.h3', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.h4')->orWhere('js.h4', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.h5')->orWhere('js.h5', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.h6')->orWhere('js.h6', '!=', 'Y'));
                        })->orWhere(function ($sub) {
                            // Atau tidak ada minggu yang terisi (w1-w4 semua bukan Y)
                            $sub->where(fn($q) => $q->whereNull('js.w1')->orWhere('js.w1', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.w2')->orWhere('js.w2', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.w3')->orWhere('js.w3', '!=', 'Y'))
                                ->where(fn($q) => $q->whereNull('js.w4')->orWhere('js.w4', '!=', 'Y'));
                        });
                    });
                }
                return $q->where('js.' . $hari, 'Y');
            })
            ->when($filters['minggu'] ?? null, function ($q, $minggu) {
                return $q->where(function ($sub) use ($minggu) {
                    if ($minggu === 'ganjil') {
                        $sub->where('js.w1', 'Y')->orWhere('js.w3', 'Y');
                    } elseif ($minggu === 'genap') {
                        $sub->where('js.w2', 'Y')->orWhere('js.w4', 'Y');
                    } elseif ($minggu === 'non_rute') {
                        // Semua hari kosong ATAU semua minggu kosong (artinya JKS tidak valid/dihapus/kosong)
                        $sub->where(function ($q1) {
                            $q1->where(fn($q) => $q->whereNull('js.h1')->orWhere('js.h1', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.h2')->orWhere('js.h2', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.h3')->orWhere('js.h3', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.h4')->orWhere('js.h4', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.h5')->orWhere('js.h5', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.h6')->orWhere('js.h6', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.h7')->orWhere('js.h7', '!=', 'Y'));
                        })->orWhere(function ($q2) {
                            $q2->where(fn($q) => $q->whereNull('js.w1')->orWhere('js.w1', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.w2')->orWhere('js.w2', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.w3')->orWhere('js.w3', '!=', 'Y'))
                               ->where(fn($q) => $q->whereNull('js.w4')->orWhere('js.w4', '!=', 'Y'));
                        });
                    }
                });
            })
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('js.customer_code', 'ilike', '%' . $search . '%')
                          ->orWhere('ltpte.customer_code_prc', 'ilike', '%' . $search . '%')
                          ->orWhere('s.salesman_name', 'ilike', '%' . $search . '%')
                          ->orWhere('ltpte.customer_address', 'ilike', '%' . $search . '%');
                }
            });
    }

    /**
     * Get paginated and filtered JKS Salesman list.
     *
     * @param string $search
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredPaginatedList($search = '', $filters = [], $perPage = 100)
    {
        if (empty($search) && empty($filters['region']) && empty($filters['area']) && empty($filters['supervisor']) && empty($filters['distributor'])) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return $this->getBaseQuery($search, $filters)
            ->orderBy('s.salesman_name')
            ->orderByDesc('js.h1')
            ->orderByDesc('js.h2')
            ->orderByDesc('js.h3')
            ->orderByDesc('js.h4')
            ->orderByDesc('js.h5')
            ->orderByDesc('js.h6')
            ->orderByDesc('js.h7')
            ->orderByDesc('js.w1')
            ->orderByDesc('js.w2')
            ->orderByDesc('js.w3')
            ->orderByDesc('js.w4')
            ->orderBy('ltpte.kecamatan')
            ->orderBy('ltpte.desa')
            ->paginate($perPage);
    }

    /**
     * Get the query builder for export.
     *
     * @param string $search
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function getExportQuery($search = '', $filters = [])
    {
        return $this->getBaseQuery($search, $filters)
            ->orderBy('s.salesman_name')
            ->orderBy('ltpte.customer_name');
    }

    /**
     * Get KPI Summary for the filtered parameters (ignoring hari and minggu).
     *
     * @param array $filters
     * @return object|null
     */
    public function getKpiSummary($filters = [])
    {
        // Jika tidak ada filter hierarki yang aktif, jangan tampilkan KPI
        if (empty($filters['region']) && empty($filters['area']) && empty($filters['supervisor']) && empty($filters['distributor']) && empty($filters['salesman'])) {
            return null;
        }

        return DB::table('jks_salesmans as js')
            ->leftJoin('master_distributors as md', 'js.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->when($filters['region'] ?? null, function ($q, $region) {
                return $q->where('md.region_code', $region);
            })
            ->when($filters['area'] ?? null, function ($q, $area) {
                return $q->where('md.area_code', $area);
            })
            ->when($filters['supervisor'] ?? null, function ($q, $supervisor) {
                return $q->where('md.supervisor_code', $supervisor);
            })
            ->when($filters['distributor'] ?? null, function ($q, $distributor) {
                return $q->where('md.distributor_code', $distributor);
            })
            ->when($filters['salesman'] ?? null, function ($q, $salesman) {
                return $q->where('js.salesman_code', $salesman);
            })
            ->when($filters['bulan'] ?? null, function ($q, $bulan) {
                return $q->where('js.bulan', 'like', $bulan . '%');
            })
            ->selectRaw("
                COUNT(DISTINCT CONCAT(js.bulan, js.distributor_code, js.salesman_code, js.customer_code)) as all_ro,
                SUM(CASE WHEN ltpte.latitude IS NULL OR ltpte.longitude IS NULL THEN 1 ELSE 0 END) as non_gps,
                COUNT(DISTINCT CASE WHEN 
                    (js.h1 = 'Y' OR js.h2 = 'Y' OR js.h3 = 'Y' OR js.h4 = 'Y' OR js.h5 = 'Y' OR js.h6 = 'Y') 
                    AND 
                    (js.w1 = 'Y' OR js.w2 = 'Y' OR js.w3 = 'Y' OR js.w4 = 'Y') 
                THEN CONCAT(js.bulan, js.distributor_code, js.salesman_code, js.customer_code) ELSE NULL END) as total_toko,
                SUM(CASE WHEN js.w1 = 'Y' OR js.w3 = 'Y' THEN 1 ELSE 0 END) as total_ganjil,
                SUM(CASE WHEN js.w2 = 'Y' OR js.w4 = 'Y' THEN 1 ELSE 0 END) as total_genap,
                SUM(CASE WHEN js.h1 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN 1 ELSE 0 END) as h1_ganjil,
                SUM(CASE WHEN js.h1 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h1_genap,
                SUM(CASE WHEN js.h2 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN 1 ELSE 0 END) as h2_ganjil,
                SUM(CASE WHEN js.h2 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h2_genap,
                SUM(CASE WHEN js.h3 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN 1 ELSE 0 END) as h3_ganjil,
                SUM(CASE WHEN js.h3 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h3_genap,
                SUM(CASE WHEN js.h4 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN 1 ELSE 0 END) as h4_ganjil,
                SUM(CASE WHEN js.h4 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h4_genap,
                SUM(CASE WHEN js.h5 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN 1 ELSE 0 END) as h5_ganjil,
                SUM(CASE WHEN js.h5 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h5_genap,
                SUM(CASE WHEN js.h6 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN 1 ELSE 0 END) as h6_ganjil,
                SUM(CASE WHEN js.h6 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h6_genap,
                SUM(CASE WHEN js.h1 = 'Y' AND (ltpte.latitude IS NULL OR ltpte.longitude IS NULL) THEN 1 ELSE 0 END) as h1_non_gps,
                SUM(CASE WHEN js.h2 = 'Y' AND (ltpte.latitude IS NULL OR ltpte.longitude IS NULL) THEN 1 ELSE 0 END) as h2_non_gps,
                SUM(CASE WHEN js.h3 = 'Y' AND (ltpte.latitude IS NULL OR ltpte.longitude IS NULL) THEN 1 ELSE 0 END) as h3_non_gps,
                SUM(CASE WHEN js.h4 = 'Y' AND (ltpte.latitude IS NULL OR ltpte.longitude IS NULL) THEN 1 ELSE 0 END) as h4_non_gps,
                SUM(CASE WHEN js.h5 = 'Y' AND (ltpte.latitude IS NULL OR ltpte.longitude IS NULL) THEN 1 ELSE 0 END) as h5_non_gps,
                SUM(CASE WHEN js.h6 = 'Y' AND (ltpte.latitude IS NULL OR ltpte.longitude IS NULL) THEN 1 ELSE 0 END) as h6_non_gps
            ")
            ->first();
    }

    /**
     * Get start and end dates for each week (M1-M4) based on the given month.
     * 
     * @param string $bulan (Format: 'YYYY-MM')
     * @return \Illuminate\Support\Collection
     */
    public function getMingguDateRanges($bulan)
    {
        if (empty($bulan)) return collect([]);

        return DB::table('master_calender')
            ->where('date', 'like', $bulan . '%')
            ->select('week_month', DB::raw('MIN(date) as start_date'), DB::raw('MAX(date) as end_date'))
            ->groupBy('week_month')
            ->orderBy('week_month')
            ->get()
            ->keyBy('week_month');
    }
}
