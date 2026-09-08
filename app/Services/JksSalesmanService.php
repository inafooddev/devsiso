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
    public function getFilteredPaginatedList($search = '', $filters = [], $perPage = 100)
    {
        // Jika tidak ada filter yang aktif dan pencarian kosong, kembalikan paginator kosong
        if (empty($search) && empty($filters['region']) && empty($filters['area']) && empty($filters['supervisor']) && empty($filters['distributor'])) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return DB::table('jks_salesmans as js')
            ->select([
                'js.id',
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
                // $bulan is 'YYYY-MM', DB stores 'YYYY-MM-DD', so match prefix
                return $q->where('js.bulan', 'like', $bulan . '%');
            })
            ->when($filters['hari'] ?? null, function ($q, $hari) {
                // $hari = h1, h2, ... h7
                // the database stores 'Y' or 'T' for the day columns
                return $q->where('js.' . $hari, 'Y');
            })
            ->when($filters['minggu'] ?? null, function ($q, $minggu) {
                return $q->where(function ($sub) use ($minggu) {
                    if ($minggu === 'ganjil') {
                        // Jika m1 dan m3 atau m1 atau m3 -> sama dengan m1=Y ATAU m3=Y
                        $sub->where('js.w1', 'Y')->orWhere('js.w3', 'Y');
                    } elseif ($minggu === 'genap') {
                        // Jika m2 dan m4 atau m2 atau m4 -> sama dengan m2=Y ATAU m4=Y
                        $sub->where('js.w2', 'Y')->orWhere('js.w4', 'Y');
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
            })
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
                COUNT(*) as total_toko,
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
                SUM(CASE WHEN js.h6 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN 1 ELSE 0 END) as h6_genap
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
