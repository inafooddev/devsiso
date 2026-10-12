<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;

class DashboardRwo extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';

    public $kuartals = [];
    public $bulans = [];
    public $regions = [];
    public $areas = [];

    public $kuartal = '';
    public $bulan = '';
    public $region = '';
    public $area = '';

    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // Request-level cache
    private $cachedMetrics = null;
    private $cachedChartData = null;

    public function mount()
    {
        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $user = auth()->user();
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $regionQuery->whereIn('region_code', $regions);
            }
        }
        $this->regions = $regionQuery->get();

        // Auto-select region and area based on access scope
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionCodes = array_values(array_diff((array) $user->region_code, ['HOINA']));
            if (count($regionCodes) === 1) {
                $this->region = (string) $regionCodes[0];
                
                $areaQuery = DB::table('master_areas')
                    ->where('region_code', $this->region)
                    ->orderBy('area_name');
                
                if (!empty($user->area_code)) {
                    $areaCodes = (array) $user->area_code;
                    $areaQuery->whereIn('area_code', $areaCodes);
                    $this->areas = $areaQuery->get();
                    
                    if (count($areaCodes) === 1) {
                        $this->area = (string) $areaCodes[0];
                    }
                } else {
                    $this->areas = $areaQuery->get();
                }
            }
        }

        $currentMonth = (int) date('n');
        $this->kuartal = (string) ceil($currentMonth / 3);
        $this->populateBulans();
        $this->bulan = (string) $currentMonth;
    }

    private function populateBulans(): void
    {
        $this->bulans = [];
        if (empty($this->kuartal)) {
            return;
        }

        $q = (int) $this->kuartal;
        $startMonth = ($q - 1) * 3 + 1;
        $endMonth = $q * 3;

        for ($m = $startMonth; $m <= $endMonth; $m++) {
            if (isset($this->monthNames[$m])) {
                $this->bulans[] = [
                    'value' => (string) $m,
                    'label' => $this->monthNames[$m],
                ];
            }
        }
    }

    public function updatedKuartal($value)
    {
        $this->populateBulans();

        if (empty($value)) {
            $this->bulan = '';
            return;
        }

        $currentMonth = (int) date('n');
        $currentQuarter = (int) ceil($currentMonth / 3);

        if ((int) $value === $currentQuarter) {
            $this->bulan = (string) $currentMonth;
        } else {
            $this->bulan = '';
        }
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        if (empty($value)) {
            $this->areas = [];
            return;
        }

        $user = auth()->user();
        $areaQuery = DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name');

        if ($user && !$user->hasRole('admin') && !empty($user->area_code)) {
            $areaQuery->whereIn('area_code', (array) $user->area_code);
        }

        $this->areas = $areaQuery->get();
    }

    public function drillDown(string $level, string $code): void
    {
        if (empty($code) || $code === 'UNKNOWN') {
            return;
        }

        if ($level === 'Region') {
            $this->region = $code;
            $this->updatedRegion($code);
        } elseif ($level === 'Area') {
            $this->area = $code;
        } else {
            return;
        }

        $this->cachedMetrics = null;
        $this->cachedChartData = null;
        $this->dispatch('dashboard-updated', metrics: $this->getDashboardMetrics(), chartData: $this->getChartData());
    }

    public function drillUp(): void
    {
        $user = auth()->user();
        $lockedRegion = false;
        $lockedArea = false;

        if ($user && !$user->hasRole('admin')) {
            $regionCodes = array_values(array_diff((array) ($user->region_code ?? []), ['HOINA']));
            if (count($regionCodes) === 1) {
                $lockedRegion = true;
            }
            if (!empty($user->area_code) && count((array) $user->area_code) === 1) {
                $lockedArea = true;
            }
        }

        if (!empty($this->area) && !$lockedArea) {
            $this->area = '';
        } elseif (!empty($this->region) && !$lockedRegion) {
            $this->region = '';
            $this->area = '';
            $this->areas = [];
        } else {
            return;
        }

        $this->cachedMetrics = null;
        $this->cachedChartData = null;
        $this->dispatch('dashboard-updated', metrics: $this->getDashboardMetrics(), chartData: $this->getChartData());
    }

    public function resetFilters()
    {
        $currentMonth = (int) date('n');
        $this->kuartal = (string) ceil($currentMonth / 3);
        $this->populateBulans();
        $this->bulan = (string) $currentMonth;

        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            $this->region = '';
            $this->area = '';
            $this->areas = [];
        } else {
            $regionCodes = array_values(array_diff((array) ($user->region_code ?? []), ['HOINA']));
            if (count($regionCodes) !== 1) {
                $this->region = '';
                $this->area = '';
                $this->areas = [];
            } elseif (empty($user->area_code) || count((array) $user->area_code) !== 1) {
                $this->area = '';
            }
        }

        $this->cachedMetrics = null;
        $this->cachedChartData = null;
        $this->dispatch('dashboard-updated', metrics: $this->getDashboardMetrics(), chartData: $this->getChartData());
    }

    public function updated()
    {
        $this->cachedMetrics = null;
        $this->cachedChartData = null;
        $this->dispatch('dashboard-updated', metrics: $this->getDashboardMetrics(), chartData: $this->getChartData());
    }

    private function applyAccessScope($query)
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
            $query->whereIn("md.area_code", (array) $user->area_code);
        } elseif (!empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn("md.region_code", $regions);
            }
        }

        return $query;
    }

    public function getPeriodInfo(): array
    {
        $year = (int) date('Y');
        $kuartalNum = !empty($this->kuartal) ? (int) $this->kuartal : null;
        $selectedMonth = !empty($this->bulan) ? (int) $this->bulan : null;

        if ($kuartalNum !== null) {
            $firstMonthOfQ = ($kuartalNum - 1) * 3 + 1;
            $lastMonthOfQ = $kuartalNum * 3;

            if ($selectedMonth !== null && $selectedMonth >= $firstMonthOfQ && $selectedMonth <= $lastMonthOfQ) {
                $multiplier = $selectedMonth - $firstMonthOfQ + 1;
                $endMonth = $selectedMonth;
            } else {
                $multiplier = 3;
                $endMonth = $lastMonthOfQ;
            }

            $startDate = sprintf('%04d-%02d-01', $year, $firstMonthOfQ);
            $endDate = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $endMonth)));

            $startMonthName = $this->monthNames[$firstMonthOfQ] ?? '';
            $endMonthName = $this->monthNames[$endMonth] ?? '';
            $monthRangeLabel = ($firstMonthOfQ === $endMonth)
                ? "{$startMonthName} {$year}"
                : "{$startMonthName} – {$endMonthName} {$year}";

            return [
                'multiplier' => $multiplier,
                'jksDateStart' => $startDate,
                'jksDateEnd' => $endDate,
                'prorataBadge' => "{$multiplier}/3 Target (Q{$kuartalNum})",
                'periodLabel' => $monthRangeLabel,
            ];
        }

        return [
            'multiplier' => 3,
            'jksDateStart' => sprintf('%04d-01-01', $year),
            'jksDateEnd' => sprintf('%04d-12-31', $year),
            'prorataBadge' => 'Full Target (Semua Kuartal)',
            'periodLabel' => "Januari – Desember {$year}",
        ];
    }

    public function getDashboardMetrics()
    {
        if ($this->cachedMetrics !== null) {
            return $this->cachedMetrics;
        }

        $periodInfo = $this->getPeriodInfo();
        $multiplier = $periodInfo['multiplier'];
        $jksDateStart = $periodInfo['jksDateStart'];
        $jksDateEnd = $periodInfo['jksDateEnd'];
        $kuartalNum = !empty($this->kuartal) ? (int) $this->kuartal : null;

        // 1. Subquery JKS (deduplicated per custno + kode_team)
        $jksSubquery = DB::table('jks_team_elite')
            ->select('custno', 'kode_team')
            ->when($jksDateStart, fn($q) => $q->whereDate('tanggal', '>=', $jksDateStart))
            ->when($jksDateEnd, fn($q) => $q->whereDate('tanggal', '<=', $jksDateEnd))
            ->groupBy('custno', 'kode_team');

        // 2. Subquery Team Elite Code Mapping (deduplicated per siso_code)
        $teSubquery = DB::table('team_elite_code_mappings')
            ->select('siso_code', DB::raw('MAX(team_elite_code) as team_elite_code'))
            ->groupBy('siso_code');

        // 3. Subquery Reward Outlet (deduplicated per customer_code)
        $rewardSubquery = DB::table('reward_outlet')
            ->selectRaw('DISTINCT ON (customer_code) customer_code, eskalink_code, no_hp, nama_pemilik_toko, latitude, longitude, foto_toko2, foto_toko3')
            ->orderBy('customer_code');

        // 4. Subquery SKB (deduplicated per customer_code + distributor_code + kuartal)
        $skbSubquery = DB::table('surat_kesepakatan_bersama_rwo')
            ->select(
                'customer_code',
                'distributor_code',
                'kuartal',
                DB::raw('MAX(CASE WHEN is_approved = true THEN 1 ELSE 0 END) as is_approved_true'),
                DB::raw('MAX(CASE WHEN is_approved = false THEN 1 ELSE 0 END) as is_approved_false')
            )
            ->when($this->kuartal, fn($q) => $q->where('kuartal', $this->kuartal))
            ->groupBy('customer_code', 'distributor_code', 'kuartal');

        // 5. Subquery ZV Sales (with push-down filter on quarter)
        $zvSubquery = DB::table('zv_so_per_toko_2026')
            ->select(
                'kd_dist',
                'uniq_kd',
                DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
                DB::raw('SUM(neto) as total_achievement'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value')
            )
            ->when($kuartalNum, fn($q) => $q->whereRaw('EXTRACT(QUARTER FROM bulan) = ?', [$kuartalNum]))
            ->groupBy('kd_dist', 'uniq_kd', DB::raw('EXTRACT(QUARTER FROM bulan)'));

        $query = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoinSub($teSubquery, 'te', function($join) {
                $join->on('te.siso_code', '=', 'md.supervisor_code');
            })
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->leftJoinSub($rewardSubquery, 'r', function($join) {
                $join->on('r.customer_code', '=', 'l.customer_code');
            })
            ->leftJoinSub($skbSubquery, 'skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoinSub($jksSubquery, 'jks', function($join) {
                $join->on('jks.custno', '=', 'r.eskalink_code')
                     ->on('jks.kode_team', '=', 'te.team_elite_code');
            })
            ->leftJoinSub($zvSubquery, 'zv', function($join) {
                $join->on('zv.kd_dist', '=', 'l.distributor_code')
                     ->on('zv.uniq_kd', '=', 'l.customer_code')
                     ->on('zv.kuartal', '=', 'l.kuartal');
            });

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
        }
        
        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";
        $progressExpr = "($achievementSql / NULLIF($proratedTargetSql, 0)) * 100";

        $this->applyAccessScope($query);

        if ($this->kuartal) {
            $query->where('l.kuartal', $this->kuartal);
        }
        if ($this->region) {
            $query->where('md.region_code', $this->region);
        }
        if ($this->area) {
            $query->where('md.area_code', $this->area);
        }

        $dataCompleteCond = "
            NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
            NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
            NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
            NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
            NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
            NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
        ";

        $query->select(
            DB::raw('COUNT(DISTINCT l.customer_code) as total_toko'),
            DB::raw('SUM(l.total_target) as total_target'),
            DB::raw("SUM($proratedTargetSql) as target_prorata"),
            DB::raw("SUM($achievementSql) as total_achievement"),
            DB::raw("COUNT(DISTINCT CASE WHEN $achievementSql > 0 THEN l.customer_code END) as toko_transaksi"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 100 THEN l.customer_code END) as toko_hijau"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100 THEN l.customer_code END) as toko_kuning"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) < 80 THEN l.customer_code END) as toko_merah"),
            DB::raw('COUNT(DISTINCT jks.custno) as total_jks'),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' THEN l.customer_code END) as total_toko_cb"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.customer_code IS NOT NULL THEN skb.customer_code END) as sudah_skb"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.is_approved_true = 1 THEN skb.customer_code END) as skb_approve"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.is_approved_false = 1 THEN skb.customer_code END) as skb_reject"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.customer_code IS NULL THEN l.customer_code END) as belum_skb"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward != 'BB' AND ($dataCompleteCond) THEN l.customer_code END) as data_lengkap"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward != 'BB' AND NOT ($dataCompleteCond) THEN l.customer_code END) as data_belum")
        );

        $this->cachedMetrics = (array) $query->first();
        return $this->cachedMetrics;
    }

    public function getChartData()
    {
        if ($this->cachedChartData !== null) {
            return $this->cachedChartData;
        }

        $periodInfo = $this->getPeriodInfo();
        $multiplier = $periodInfo['multiplier'];
        $kuartalNum = !empty($this->kuartal) ? (int) $this->kuartal : null;

        $teSubquery = DB::table('team_elite_code_mappings')
            ->select('siso_code', DB::raw('MAX(team_elite_code) as team_elite_code'))
            ->groupBy('siso_code');

        $zvSubquery = DB::table('zv_so_per_toko_2026')
            ->select(
                'kd_dist',
                'uniq_kd',
                DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
                DB::raw('SUM(neto) as total_achievement'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value')
            )
            ->when($kuartalNum, fn($q) => $q->whereRaw('EXTRACT(QUARTER FROM bulan) = ?', [$kuartalNum]))
            ->groupBy('kd_dist', 'uniq_kd', DB::raw('EXTRACT(QUARTER FROM bulan)'));

        $query = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoinSub($teSubquery, 'te', function($join) {
                $join->on('te.siso_code', '=', 'md.supervisor_code');
            })
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->leftJoinSub($zvSubquery, 'zv', function($join) {
                $join->on('zv.kd_dist', '=', 'l.distributor_code')
                     ->on('zv.uniq_kd', '=', 'l.customer_code')
                     ->on('zv.kuartal', '=', 'l.kuartal');
            });

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
        }
        
        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";

        $this->applyAccessScope($query);

        if ($this->kuartal) {
            $query->where('l.kuartal', $this->kuartal);
        }
        if ($this->region) {
            $query->where('md.region_code', $this->region);
        }
        if ($this->area) {
            $query->where('md.area_code', $this->area);
        }

        // Determine grouping level and name dynamically
        $groupField = 'md.region_name';
        $groupCodeExpr = 'MAX(md.region_code)';
        $groupName = 'Region';
        if ($this->area) {
            $groupField = 'f."SLSNAME"';
            $groupCodeExpr = 'NULL';
            $groupName = 'Supervisor';
        } elseif ($this->region) {
            $groupField = 'md.area_name';
            $groupCodeExpr = 'MAX(md.area_code)';
            $groupName = 'Area';
        }

        $query->select(
            DB::raw("COALESCE($groupField, 'UNKNOWN') as category"),
            DB::raw("$groupCodeExpr as code"),
            DB::raw("SUM($proratedTargetSql) as target_prorata"),
            DB::raw("SUM($achievementSql) as total_achievement")
        )
        ->groupBy(DB::raw("COALESCE($groupField, 'UNKNOWN')"))
        ->orderByRaw("SUM($proratedTargetSql) DESC");

        $results = $query->get();

        $categories = [];
        $codes = [];
        $targetData = [];
        $achievementData = [];

        foreach ($results as $row) {
            $categories[] = $row->category;
            $codes[] = $row->code;
            $targetData[] = round((float)$row->target_prorata);
            $achievementData[] = round((float)$row->total_achievement);
        }

        $this->cachedChartData = [
            'categories' => $categories,
            'codes' => $codes,
            'target' => $targetData,
            'achievement' => $achievementData,
            'groupName' => $groupName
        ];
        return $this->cachedChartData;
    }

    public function render()
    {
        return view('livewire.rwo.dashboard-rwo', [
            'metrics' => $this->getDashboardMetrics(),
            'chartData' => $this->getChartData(),
            'periodInfo' => $this->getPeriodInfo(),
        ])->layout('layouts.app');
    }
}
