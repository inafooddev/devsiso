<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait RwoQueryBuilder
{
    /**
     * Apply access scope to restrict queries based on user roles (Admin/Supervisor/Area/Region).
     */
    protected function applyAccessScope($query)
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $query->where(function($q) use ($user) {
                $q->where('te.team_elite_code', $user->supervisor_code)
                  ->orWhere('md.supervisor_code', $user->supervisor_code);
            });
        } elseif (!empty($user->area_code)) {
            $query->where('md.area_code', $user->area_code);
        } elseif (!empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('md.region_code', $regions);
            }
        }

        return $query;
    }

    /**
     * Get the base query for RWO stores with all necessary joins.
     */
    protected function getStoreQuery()
    {
        return DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoin('list_toko_pareto_team_elite as lt', 'lt.uniq_kd', '=', 'l.customer_code')
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist', 
                        'uniq_kd', 
                        DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
                        DB::raw('SUM(neto) as total_achievement'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value')
                    )
                    ->groupBy('kd_dist', 'uniq_kd', DB::raw('EXTRACT(QUARTER FROM bulan)')),
                'zv',
                function($join) {
                    $join->on('zv.kd_dist', '=', 'l.distributor_code')
                         ->on('zv.uniq_kd', '=', 'l.customer_code')
                         ->on('zv.kuartal', '=', 'l.kuartal');
                }
            )
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist', 
                        'uniq_kd',
                        DB::raw('MAX(neto) as max_transaction'),
                        DB::raw('AVG(neto) as avg_transaction'),
                        DB::raw('SUM(neto) as total_transaction')
                    )
                    ->groupBy('kd_dist', 'uniq_kd'),
                'zv_stats',
                function($join) {
                    $join->on('zv_stats.kd_dist', '=', 'l.distributor_code')
                         ->on('zv_stats.uniq_kd', '=', 'l.customer_code');
                }
            )
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->selectRaw('DISTINCT ON (kd_dist, uniq_kd) kd_dist, uniq_kd, neto as last_transaction_value, bulan as last_transaction_date')
                    ->orderBy('kd_dist')
                    ->orderBy('uniq_kd')
                    ->orderBy('bulan', 'desc'),
                'zv_last',
                function($join) {
                    $join->on('zv_last.kd_dist', '=', 'l.distributor_code')
                         ->on('zv_last.uniq_kd', '=', 'l.customer_code');
                }
            )
            ->select(
                'l.*', 
                'lt.customer_code_prc as customer_prc',
                'md.region_name', 'md.area_name', 'md.supervisor_name', 'md.distributor_name',
                'l.alamat as address',
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3',
                'skb.is_approved', 'skb.foto_skb as skb_foto', 'skb.reason as skb_reason',
                DB::raw("CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END AS status_skb"),
                DB::raw("CASE WHEN 
                    NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                    NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                    NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                    THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap"),
                'zv.total_achievement',
                'zv.month_1_value',
                'zv.month_2_value',
                'zv.month_3_value',
                'zv_stats.max_transaction',
                'zv_stats.avg_transaction',
                'zv_stats.total_transaction',
                'zv_last.last_transaction_value',
                'zv_last.last_transaction_date'
            );
    }
}
