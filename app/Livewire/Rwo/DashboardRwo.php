<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DashboardRwo extends Component
{
    public $kuartals = [];
    public $regions = [];
    public $areas = [];

    public $kuartal = '';
    public $region = '';
    public $area = '';
    
    public $jksDateStart = '';
    public $jksDateEnd = '';

    // Request-level cache
    private $cachedMetrics = null;
    private $cachedChartData = null;

    public function mount()
    {
        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $user = auth()->user();
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $this->regions = $regionQuery->get();

        // Auto-select region and area based on access scope
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->region_code)) {
                $regionCodes = (array) $user->region_code;
                if (count($regionCodes) === 1) {
                    $this->region = (string) $regionCodes[0];
                    
                    // Populate areas for this region
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
        }

        $currentMonth = (int) date('n');
        $this->kuartal = (string) ceil($currentMonth / 3);
        
        $this->jksDateStart = date('Y-m-01');
        $this->jksDateEnd = date('Y-m-t');
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        $this->areas = empty($value) ? [] : DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name')
            ->get();
    }

    public function updated()
    {
        // Whenever any filter (kuartal, region, area) is updated, dispatch event to Alpine
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
            $query->whereIn("md.region_code", (array) $user->region_code);
        }

        return $query;
    }

    public function getDashboardMetrics()
    {
        if ($this->cachedMetrics !== null) {
            return $this->cachedMetrics;
        }

        $jksSubquery = DB::table('jks_team_elite')
            ->select('custno', 'kode_team')
            ->when($this->jksDateStart, fn($q) => $q->whereDate('tanggal', '>=', $this->jksDateStart))
            ->when($this->jksDateEnd, fn($q) => $q->whereDate('tanggal', '<=', $this->jksDateEnd))
            ->groupBy('custno', 'kode_team');

        $query = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoinSub($jksSubquery, 'jks', function($join) {
                $join->on('jks.custno', '=', 'r.eskalink_code')
                     ->on('jks.kode_team', '=', 'te.team_elite_code');
            })
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist',
                        'uniq_kd',
                        DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
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
            );

        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartalNum = (int)($this->kuartal ?: $currentQuarter);
        
        $multiplier = 3;
        if ($kuartalNum === $currentQuarter) {
            $firstMonthOfQ = ($kuartalNum - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartalNum > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0) + COALESCE(zv.month_3_value, 0))";
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

        $query->select(
            DB::raw('COUNT(DISTINCT l.customer_code) as total_toko'),
            DB::raw('SUM(l.total_target) as total_target'),
            DB::raw("SUM($proratedTargetSql) as target_prorata"),
            DB::raw("SUM($achievementSql) as total_achievement"),
            DB::raw("COUNT(DISTINCT CASE WHEN $achievementSql > 0 THEN l.customer_code END) as toko_transaksi"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 100 THEN l.customer_code END) as toko_hijau"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100 THEN l.customer_code END) as toko_kuning"),
            DB::raw("COUNT(DISTINCT CASE WHEN $proratedTargetSql > 0 AND COALESCE($progressExpr, 0) < 80 THEN l.customer_code END) as toko_merah"),
            DB::raw('COUNT(DISTINCT jks.custno) as total_jks'),
            DB::raw('COUNT(DISTINCT CASE WHEN skb.customer_code IS NOT NULL THEN skb.customer_code END) as sudah_skb'),
            DB::raw('COUNT(DISTINCT CASE WHEN skb.is_approved = true THEN skb.customer_code END) as skb_approve'),
            DB::raw('COUNT(DISTINCT CASE WHEN skb.is_approved = false THEN skb.customer_code END) as skb_reject'),
            DB::raw("COUNT(DISTINCT CASE WHEN 
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
                THEN l.customer_code END) as data_lengkap")
        );

        $this->cachedMetrics = (array) $query->first();
        return $this->cachedMetrics;
    }

    public function getChartData()
    {
        if ($this->cachedChartData !== null) {
            return $this->cachedChartData;
        }

        $query = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist',
                        'uniq_kd',
                        DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
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
            );

        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartalNum = (int)($this->kuartal ?: $currentQuarter);
        
        $multiplier = 3;
        if ($kuartalNum === $currentQuarter) {
            $firstMonthOfQ = ($kuartalNum - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartalNum > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0) + COALESCE(zv.month_3_value, 0))";
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
        $groupName = 'Region';
        if ($this->area) {
            $groupField = 'f."SLSNAME"';
            $groupName = 'Supervisor';
        } elseif ($this->region) {
            $groupField = 'md.area_name';
            $groupName = 'Area';
        }

        $query->select(
            DB::raw("COALESCE($groupField, 'UNKNOWN') as category"),
            DB::raw("SUM($proratedTargetSql) as target_prorata"),
            DB::raw("SUM($achievementSql) as total_achievement")
        )
        ->groupBy(DB::raw("COALESCE($groupField, 'UNKNOWN')"))
        ->orderBy(DB::raw('2'), 'desc'); // Order by target desc

        $results = $query->get();

        $categories = [];
        $targetData = [];
        $achievementData = [];

        foreach ($results as $row) {
            $categories[] = $row->category;
            $targetData[] = round((float)$row->target_prorata);
            $achievementData[] = round((float)$row->total_achievement);
        }

        $this->cachedChartData = [
            'categories' => $categories,
            'target' => $targetData,
            'achievement' => $achievementData,
            'groupName' => $groupName
        ];
        return $this->cachedChartData;
    }

    public function render()
    {
        $kuartalNum = (int)($this->kuartal ?: ceil(date('n') / 3));
        $monthsMap = [
            1 => ['Januari', 'Februari', 'Maret'],
            2 => ['April', 'Mei', 'Juni'],
            3 => ['Juli', 'Agustus', 'September'],
            4 => ['Oktober', 'November', 'Desember']
        ];
        $activeMonths = $monthsMap[$kuartalNum] ?? [];

        return view('livewire.rwo.dashboard-rwo', [
            'metrics' => $this->getDashboardMetrics(),
            'chartData' => $this->getChartData(),
            'activeMonths' => $activeMonths
        ])->layout('layouts.app');
    }
}
