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
        
        if (!$user) {
            $query->whereRaw('1 = 0');
            return $query;
        }

        if ($user->hasRole('admin')) {
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
    protected function getStoreQuery(?string $kuartal = null, bool $withDetailStats = false, ?string $customerCode = null, ?string $distributorCode = null)
    {
        $zvSub = DB::table('zv_so_per_toko_2026')
            ->select(
                'kd_dist', 
                'uniq_kd', 
                DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
                DB::raw('SUM(neto) as total_achievement'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value')
            );

        if ($kuartal !== null && $kuartal !== '') {
            $zvSub->whereRaw('EXTRACT(QUARTER FROM bulan) = ?', [(int)$kuartal]);
        }
        if ($customerCode) {
            $zvSub->where('uniq_kd', $customerCode);
        }
        if ($distributorCode) {
            $zvSub->where('kd_dist', $distributorCode);
        }

        $zvSub->groupBy('kd_dist', 'uniq_kd', DB::raw('EXTRACT(QUARTER FROM bulan)'));

        $query = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoin('list_toko_pareto_team_elite as lt', function($join) {
                $join->on('lt.uniq_kd', '=', 'l.customer_code')
                     ->on('lt.distributor_code', '=', 'l.distributor_code');
            })
            ->leftJoin('remark_list_potensi_rwo as rm', function($join) use ($kuartal) {
                $join->on('rm.customer_code', '=', 'l.customer_code')
                     ->on('rm.distributor_code', '=', 'l.distributor_code');
                if ($kuartal !== null && $kuartal !== '') {
                    $join->where('rm.kuartal', '=', (string)$kuartal);
                } else {
                    $join->on(DB::raw('CAST(rm.kuartal AS TEXT)'), '=', DB::raw('CAST(l.kuartal AS TEXT)'));
                }
            })
            ->leftJoinSub(
                $zvSub,
                'zv',
                function($join) {
                    $join->on('zv.kd_dist', '=', 'l.distributor_code')
                         ->on('zv.uniq_kd', '=', 'l.customer_code')
                         ->on('zv.kuartal', '=', 'l.kuartal');
                }
            );

        $selectColumns = [
            'l.*', 
            DB::raw('COALESCE(lt.customer_code_prc, r.eskalink_code) as customer_prc'),
            'md.region_name', 'md.area_name', 'md.supervisor_name', 'md.distributor_name',
            'l.alamat as address',
            'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
            'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
            'r.foto_toko2', 'r.foto_toko3',
            'skb.is_approved', 'skb.foto_skb as skb_foto', 'skb.reason as skb_reason',
            'rm.remark as remark_khusus',
            DB::raw("CASE WHEN l.jenis_reward = 'CB' THEN (CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END) ELSE '' END AS status_skb"),
            DB::raw("CASE WHEN l.jenis_reward = 'BB' THEN ''
                WHEN 
                NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                /*
                NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                */
                NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap"),
            'zv.total_achievement',
            'zv.month_1_value',
            'zv.month_2_value',
            'zv.month_3_value',
        ];

        if ($withDetailStats) {
            $zvStatsSub = DB::table('zv_so_per_toko_2026')
                ->select(
                    'kd_dist', 
                    'uniq_kd',
                    DB::raw('MAX(neto) as max_transaction'),
                    DB::raw('AVG(neto) as avg_transaction'),
                    DB::raw('SUM(neto) as total_transaction')
                );
            if ($customerCode) {
                $zvStatsSub->where('uniq_kd', $customerCode);
            }
            if ($distributorCode) {
                $zvStatsSub->where('kd_dist', $distributorCode);
            }
            $zvStatsSub->groupBy('kd_dist', 'uniq_kd');

            $zvLastSub = DB::table('zv_so_per_toko_2026')
                ->selectRaw('DISTINCT ON (kd_dist, uniq_kd) kd_dist, uniq_kd, neto as last_transaction_value, bulan as last_transaction_date');
            if ($customerCode) {
                $zvLastSub->where('uniq_kd', $customerCode);
            }
            if ($distributorCode) {
                $zvLastSub->where('kd_dist', $distributorCode);
            }
            $zvLastSub->orderBy('kd_dist')
                ->orderBy('uniq_kd')
                ->orderBy('bulan', 'desc');

            $query->leftJoinSub(
                $zvStatsSub,
                'zv_stats',
                function($join) {
                    $join->on('zv_stats.kd_dist', '=', 'l.distributor_code')
                         ->on('zv_stats.uniq_kd', '=', 'l.customer_code');
                }
            )->leftJoinSub(
                $zvLastSub,
                'zv_last',
                function($join) {
                    $join->on('zv_last.kd_dist', '=', 'l.distributor_code')
                         ->on('zv_last.uniq_kd', '=', 'l.customer_code');
                }
            );

            $selectColumns = array_merge($selectColumns, [
                'zv_stats.max_transaction',
                'zv_stats.avg_transaction',
                'zv_stats.total_transaction',
                'zv_last.last_transaction_value',
                'zv_last.last_transaction_date',
            ]);
        }

        return $query->select($selectColumns);
    }

    /**
     * Calculate prorated SQL expressions based on selected quarter.
     */
    protected function getProratedSqlExpressions($kuartalStr): array
    {
        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)($kuartalStr ?: $currentQuarter);

        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
            $kpiAchievementSql = "SUM(COALESCE(zv.month_1_value, 0))";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
            $kpiAchievementSql = "SUM(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
            $kpiAchievementSql = "SUM(COALESCE(zv.total_achievement, 0))";
        }

        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";
        $progressExpr = "($achievementSql / NULLIF($proratedTargetSql, 0)) * 100";

        return [
            'kuartal' => $kuartal,
            'currentQuarter' => $currentQuarter,
            'multiplier' => $multiplier,
            'achievementSql' => $achievementSql,
            'kpiAchievementSql' => $kpiAchievementSql,
            'proratedTargetSql' => $proratedTargetSql,
            'progressExpr' => $progressExpr,
        ];
    }

    /**
     * Apply RWO filters consistently across table, KPI, export, and map queries.
     */
    protected function applyRwoFilters($query, array $filters): array
    {
        $appliedKuartal = $filters['kuartal'] ?? '';
        $appliedRegion = $filters['region'] ?? '';
        $appliedArea = $filters['area'] ?? '';
        $appliedSupervisor = $filters['supervisor'] ?? '';
        $appliedDistributor = $filters['distributor'] ?? '';
        $search = $filters['search'] ?? '';
        $appliedStatusSkb = $filters['statusSkb'] ?? 'Semua';
        $appliedStatusData = $filters['statusData'] ?? 'Semua';
        $appliedStatusReward = $filters['statusReward'] ?? 'Semua';
        $appliedStatusProgress = $filters['statusProgress'] ?? 'Semua';
        $appliedStatusTransaksi = $filters['statusTransaksi'] ?? 'Semua';

        if ($appliedKuartal) {
            $query->where('l.kuartal', $appliedKuartal);
        }
        if ($appliedRegion) {
            $query->where('md.region_code', $appliedRegion);
        }
        if ($appliedArea) {
            $query->where('md.area_code', $appliedArea);
        }
        if ($appliedSupervisor) {
            $query->where('md.supervisor_code', $appliedSupervisor);
        }
        if ($appliedDistributor) {
            $query->where('l.distributor_code', $appliedDistributor);
        }

        if ($search) {
            $q = '%' . strtolower($search) . '%';
            $query->where(function($sub) use ($q) {
                $sub->whereRaw('LOWER(l.customer_name) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(l.customer_code) LIKE ?', [$q]);
            });
        }

        if ($appliedStatusSkb !== 'Semua') {
            $query->where('l.jenis_reward', 'CB');
            if ($appliedStatusSkb === 'Sudah') {
                $query->whereNotNull('skb.customer_code');
            } elseif ($appliedStatusSkb === 'Belum') {
                $query->whereNull('skb.customer_code');
            } elseif ($appliedStatusSkb === 'Approve') {
                $query->whereNotNull('skb.customer_code')->where('skb.is_approved', true);
            } elseif ($appliedStatusSkb === 'Reject') {
                $query->whereNotNull('skb.customer_code')->where(function($q) {
                    $q->where('skb.is_approved', false)->orWhereNull('skb.is_approved');
                });
            }
        }

        if ($appliedStatusData !== 'Semua') {
            $query->where('l.jenis_reward', '!=', 'BB');
            $fieldsCheck = [
                'r.no_hp', 'r.nama_pemilik_toko',
                // 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                // 'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek',
                'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3'
            ];
            if ($appliedStatusData === 'Lengkap') {
                foreach ($fieldsCheck as $f) {
                    $query->whereNotNull($f)->where(DB::raw("TRIM($f)"), '!=', '');
                }
            } else {
                $query->where(function($sub) use ($fieldsCheck) {
                    foreach ($fieldsCheck as $f) {
                        $sub->orWhereNull($f)->orWhere(DB::raw("TRIM($f)"), '=', '');
                    }
                });
            }
        }

        if ($appliedStatusReward !== 'Semua') {
            if ($appliedStatusReward === '1KTN') {
                $query->where('l.jenis_reward', 'BB');
            } elseif ($appliedStatusReward === '2.5%') {
                $query->where('l.jenis_reward', 'CB')->where('l.total_target', '>=', 90000000);
            } elseif ($appliedStatusReward === '2%') {
                $query->where('l.jenis_reward', 'CB')->where('l.total_target', '>=', 30000000)->where('l.total_target', '<', 90000000);
            } elseif ($appliedStatusReward === '1.5%') {
                $query->where('l.jenis_reward', 'CB')->where(function($q) {
                    $q->whereNull('l.total_target')->orWhere('l.total_target', '<', 30000000);
                });
            }
        }

        $proratedExpr = $this->getProratedSqlExpressions($appliedKuartal);
        $achievementSql = $proratedExpr['achievementSql'];
        $progressExpr = $proratedExpr['progressExpr'];

        if ($appliedStatusProgress !== 'Semua') {
            if ($appliedStatusProgress === '1. HIJAU') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 100");
            } elseif ($appliedStatusProgress === '2. KUNING') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100");
            } elseif ($appliedStatusProgress === '3. MERAH') {
                $query->whereRaw("COALESCE($progressExpr, 0) < 80");
            }
        }

        if ($appliedStatusTransaksi !== 'Semua') {
            if ($appliedStatusTransaksi === 'Sudah') {
                $query->whereRaw("COALESCE($achievementSql, 0) > 0");
            } elseif ($appliedStatusTransaksi === 'Belum') {
                $query->whereRaw("COALESCE($achievementSql, 0) <= 0");
            }
        }

        return $proratedExpr;
    }
}
