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
        $this->kuartal = $filters['appliedKuartal'] ?? '';
        // Just forward the filters to the JS
        $this->dispatch('open-map-modal', filters: $filters);
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
