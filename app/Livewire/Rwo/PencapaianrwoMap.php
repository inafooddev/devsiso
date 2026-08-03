<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use App\Traits\RwoQueryBuilder;

class PencapaianrwoMap extends Component
{
    use RwoQueryBuilder;

    public $kuartal = '';

    #[On('load-map-data')]
    public function loadMapData($filters)
    {
        // Extract filters to local variables
        $appliedKuartal = $filters['appliedKuartal'] ?? '';
        $appliedRegion = $filters['appliedRegion'] ?? '';
        $appliedArea = $filters['appliedArea'] ?? '';
        $appliedSupervisor = $filters['appliedSupervisor'] ?? '';
        $appliedDistributor = $filters['appliedDistributor'] ?? '';
        $search = $filters['search'] ?? '';
        $appliedStatusSkb = $filters['appliedStatusSkb'] ?? 'Semua';
        $appliedStatusData = $filters['appliedStatusData'] ?? 'Semua';
        $appliedStatusReward = $filters['appliedStatusReward'] ?? 'Semua';
        $appliedStatusProgress = $filters['appliedStatusProgress'] ?? 'Semua';

        $query = $this->getStoreQuery();
        $this->applyAccessScope($query);
        $this->kuartal = $appliedKuartal;

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
            $fieldsCheck = [
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
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
            if ($appliedStatusReward === '2.5%') {
                $query->where('l.total_target', '>=', 90000000);
            } elseif ($appliedStatusReward === '2%') {
                $query->where('l.total_target', '>=', 30000000)->where('l.total_target', '<', 90000000);
            } elseif ($appliedStatusReward === '1.5%') {
                $query->where(function($q) {
                    $q->whereNull('l.total_target')->orWhere('l.total_target', '<', 30000000);
                });
            }
        }

        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$appliedKuartal;
        
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
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
        }
        
        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";
        $progressExpr = "($achievementSql / NULLIF($proratedTargetSql, 0)) * 100";

        if ($appliedStatusProgress !== 'Semua') {
            if ($appliedStatusProgress === '1. HIJAU') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 100");
            } elseif ($appliedStatusProgress === '2. KUNING') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100");
            } elseif ($appliedStatusProgress === '3. MERAH') {
                $query->whereRaw("COALESCE($progressExpr, 0) < 80");
            }
        }

        // Add filter to only get valid coordinates
        $query->whereNotNull('r.latitude')
              ->whereNotNull('r.longitude')
              ->whereRaw("TRIM(r.latitude) != ''")
              ->whereRaw("TRIM(r.longitude) != ''");

        // Override select to prevent fetching unused heavy columns (saves memory)
        $query->select([
            'l.customer_code',
            'r.latitude',
            'r.longitude',
            'l.total_target'
        ]);

        $stores = $query->get();

        $mapData = [];
        foreach ($stores as $row) {
            $lat = (float) $row->latitude;
            $lng = (float) $row->longitude;
            
            // Validate coordinates range
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || ($lat == 0 && $lng == 0)) {
                continue;
            }
            
            $target = $row->total_target ?? 0;
            
            $color = '#64748b'; // default slate (for 1.5% or null)
            if ($target >= 90000000) {
                $color = '#eab308'; // yellow / gold
            } elseif ($target >= 30000000) {
                $color = '#3b82f6'; // blue
            }
            
            $mapData[] = [
                'code' => $row->customer_code,
                'lat' => $lat,
                'lng' => $lng,
                'color' => $color
            ];
        }

        $this->dispatch('open-map-modal', mapData: $mapData);
    }

    public function getStoreDetails($customerCode)
    {
        $kuartal = $this->kuartal;
        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$kuartal ?: $currentQuarter;
        
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

        $store = DB::table('list_potensi_rwo as l')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) use ($kuartal) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->where('skb.kuartal', '=', $kuartal);
            })
            ->leftJoin('remark_list_potensi_rwo as rm', function($join) use ($kuartal) {
                $join->on('rm.customer_code', '=', 'l.customer_code')
                     ->on('rm.distributor_code', '=', 'l.distributor_code')
                     ->where('rm.kuartal', '=', (string)$kuartal);
            })
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist', 
                        'uniq_kd', 
                        DB::raw('SUM(neto) as total_achievement'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value')
                    )
                    ->whereRaw('EXTRACT(QUARTER FROM bulan) = ?', [$kuartal])
                    ->groupBy('kd_dist', 'uniq_kd'),
                'zv',
                function($join) {
                    $join->on('zv.kd_dist', '=', 'l.distributor_code')
                         ->on('zv.uniq_kd', '=', 'l.customer_code');
                }
            )
            ->where('l.customer_code', $customerCode)
            ->where('l.kuartal', $kuartal)
            ->select([
                'l.customer_name',
                'l.total_target',
                'zv.month_1_value',
                'zv.month_2_value',
                'zv.month_3_value',
                'zv.total_achievement',
                'skb.customer_code as has_skb',
                'skb.is_approved',
                'rm.remark as action_remark'
            ])
            ->first();

        if (!$store) return null;

        $target = $store->total_target ?? 0;
        
        if ($multiplier === 1) {
            $actual = $store->month_1_value ?? 0;
        } elseif ($multiplier === 2) {
            $actual = ($store->month_1_value ?? 0) + ($store->month_2_value ?? 0);
        } else {
            $actual = $store->total_achievement ?? 0;
        }
        
        $proratedTarget = ($target / 3) * $multiplier;
        $gap = $actual - $proratedTarget;

        $statusSkb = 'Belum';
        $badgeClass = 'badge-ghost';
        if ($store->has_skb) {
            $statusSkb = 'Sudah - ';
            if ($store->is_approved === 1 || $store->is_approved === true) {
                $statusSkb .= 'Approved';
                $badgeClass = 'badge-success text-white';
            } elseif ($store->is_approved === 0 || $store->is_approved === false) {
                $statusSkb .= 'Rejected';
                $badgeClass = 'badge-error text-white';
            } else {
                $statusSkb .= 'Submitted';
                $badgeClass = 'badge-info text-white';
            }
        }

        return [
            'name' => $store->customer_name,
            'target' => number_format($target, 0, ',', '.'),
            'm1' => number_format($store->month_1_value ?? 0, 0, ',', '.'),
            'm2' => number_format($store->month_2_value ?? 0, 0, ',', '.'),
            'm3' => number_format($store->month_3_value ?? 0, 0, ',', '.'),
            'actual' => number_format($actual, 0, ',', '.'),
            'gap' => number_format($gap, 0, ',', '.'),
            'is_gap_negative' => $gap < 0,
            'status_skb' => $statusSkb,
            'badge_class' => $badgeClass,
            'action_remark' => $store->action_remark ?: '-'
        ];
    }

    public function render()
    {
        return view('livewire.rwo.pencapaianrwo-map');
    }
}
