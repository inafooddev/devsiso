<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryListPotensiExport;

class SummaryListPotensi extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';
    protected string $menuPermission = 'RWO / Summary List Potensi';
    public $title = 'Summary List Potensi';

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

        // Auto-select region & area for scoped non-admin users
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
        $this->areas = empty($value) ? [] : DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name')
            ->get();
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

    public function getRecords()
    {
        $periodInfo = $this->getPeriodInfo();
        $multiplier = $periodInfo['multiplier'];
        $jksDateStart = $periodInfo['jksDateStart'];
        $jksDateEnd = $periodInfo['jksDateEnd'];
        $kuartalNum = !empty($this->kuartal) ? (int) $this->kuartal : null;

        // 1. Subquery JKS (deduplicated per custno + kode_team within cumulative date range)
        $jksSubquery = DB::table('jks_team_elite')
            ->select('custno', 'kode_team')
            ->when($jksDateStart, fn($q) => $q->whereDate('tanggal', '>=', $jksDateStart))
            ->when($jksDateEnd, fn($q) => $q->whereDate('tanggal', '<=', $jksDateEnd))
            ->groupBy('custno', 'kode_team');

        // 2. Subquery Team Elite Code Mapping (deduplicated per siso_code to prevent row multiplication)
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

        // 5. Subquery ZV Sales (with push-down filter on quarter for faster execution)
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
            'md.region_name',
            'md.area_name',
            'f.SLSNAME as supervisor_name',
            'md.distributor_code',
            'md.distributor_name',
            DB::raw('COUNT(DISTINCT l.customer_code) as total_toko'),
            DB::raw('SUM(l.total_target) as total_target'),
            DB::raw("SUM($proratedTargetSql) as target_prorata"),
            DB::raw("SUM($achievementSql) as total_achievement"),
            DB::raw("COUNT(DISTINCT CASE WHEN $achievementSql > 0 THEN l.customer_code END) as toko_transaksi"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 100 THEN l.customer_code END) as toko_hijau"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100 THEN l.customer_code END) as toko_kuning"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) < 80 THEN l.customer_code END) as toko_merah"),
            DB::raw('COUNT(DISTINCT jks.custno) as total_jks'),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.customer_code IS NOT NULL THEN skb.customer_code END) as sudah_skb"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.is_approved_true = 1 THEN skb.customer_code END) as skb_approve"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward = 'CB' AND skb.is_approved_false = 1 THEN skb.customer_code END) as skb_reject"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward != 'BB' AND ($dataCompleteCond) THEN l.customer_code END) as data_lengkap"),
            DB::raw("COUNT(DISTINCT CASE WHEN l.jenis_reward != 'BB' AND NOT ($dataCompleteCond) THEN l.customer_code END) as data_belum")
        )
        ->groupBy('md.region_name', 'md.area_name', 'f.SLSNAME', 'md.distributor_code', 'md.distributor_name')
        ->orderBy('md.region_name')
        ->orderBy('md.area_name')
        ->orderBy('f.SLSNAME')
        ->orderBy('md.distributor_name');

        $rawRecords = $query->get();
        
        $groupedRecords = [];
        foreach ($rawRecords as $row) {
            $rKey = $row->region_name ?? '-';
            $aKey = $row->area_name ?? '-';
            $sKey = $row->supervisor_name ?? '-';

            // Init Region
            if (!isset($groupedRecords[$rKey])) {
                $groupedRecords[$rKey] = [
                    'name' => $rKey,
                    'total_toko' => 0, 'total_target' => 0, 'total_jks' => 0, 'sudah_skb' => 0, 'skb_approve' => 0, 'skb_reject' => 0,
                    'data_lengkap' => 0, 'data_belum' => 0,
                    'target_prorata' => 0, 'total_achievement' => 0,
                    'toko_transaksi' => 0, 'toko_hijau' => 0, 'toko_kuning' => 0, 'toko_merah' => 0,
                    'areas' => []
                ];
            }
            // Init Area
            if (!isset($groupedRecords[$rKey]['areas'][$aKey])) {
                $groupedRecords[$rKey]['areas'][$aKey] = [
                    'name' => $aKey,
                    'total_toko' => 0, 'total_target' => 0, 'total_jks' => 0, 'sudah_skb' => 0, 'skb_approve' => 0, 'skb_reject' => 0,
                    'data_lengkap' => 0, 'data_belum' => 0,
                    'target_prorata' => 0, 'total_achievement' => 0,
                    'toko_transaksi' => 0, 'toko_hijau' => 0, 'toko_kuning' => 0, 'toko_merah' => 0,
                    'supervisors' => []
                ];
            }
            // Init Supervisor
            if (!isset($groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey])) {
                $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey] = [
                    'name' => $sKey,
                    'total_toko' => 0, 'total_target' => 0, 'total_jks' => 0, 'sudah_skb' => 0, 'skb_approve' => 0, 'skb_reject' => 0,
                    'data_lengkap' => 0, 'data_belum' => 0,
                    'target_prorata' => 0, 'total_achievement' => 0,
                    'toko_transaksi' => 0, 'toko_hijau' => 0, 'toko_kuning' => 0, 'toko_merah' => 0,
                    'cabang' => []
                ];
            }

            $dataBelum = (int) $row->data_belum;

            // Accumulate metrics for Region
            $groupedRecords[$rKey]['total_toko'] += $row->total_toko;
            $groupedRecords[$rKey]['total_target'] += $row->total_target;
            $groupedRecords[$rKey]['total_jks'] += $row->total_jks;
            $groupedRecords[$rKey]['sudah_skb'] += $row->sudah_skb;
            $groupedRecords[$rKey]['skb_approve'] += $row->skb_approve;
            $groupedRecords[$rKey]['skb_reject'] += $row->skb_reject;
            $groupedRecords[$rKey]['data_lengkap'] += $row->data_lengkap;
            $groupedRecords[$rKey]['data_belum'] += $dataBelum;
            $groupedRecords[$rKey]['target_prorata'] += $row->target_prorata;
            $groupedRecords[$rKey]['total_achievement'] += $row->total_achievement;
            $groupedRecords[$rKey]['toko_transaksi'] += $row->toko_transaksi;
            $groupedRecords[$rKey]['toko_hijau'] += $row->toko_hijau;
            $groupedRecords[$rKey]['toko_kuning'] += $row->toko_kuning;
            $groupedRecords[$rKey]['toko_merah'] += $row->toko_merah;

            // Accumulate metrics for Area
            $groupedRecords[$rKey]['areas'][$aKey]['total_toko'] += $row->total_toko;
            $groupedRecords[$rKey]['areas'][$aKey]['total_target'] += $row->total_target;
            $groupedRecords[$rKey]['areas'][$aKey]['total_jks'] += $row->total_jks;
            $groupedRecords[$rKey]['areas'][$aKey]['sudah_skb'] += $row->sudah_skb;
            $groupedRecords[$rKey]['areas'][$aKey]['skb_approve'] += $row->skb_approve;
            $groupedRecords[$rKey]['areas'][$aKey]['skb_reject'] += $row->skb_reject;
            $groupedRecords[$rKey]['areas'][$aKey]['data_lengkap'] += $row->data_lengkap;
            $groupedRecords[$rKey]['areas'][$aKey]['data_belum'] += $dataBelum;
            $groupedRecords[$rKey]['areas'][$aKey]['target_prorata'] += $row->target_prorata;
            $groupedRecords[$rKey]['areas'][$aKey]['total_achievement'] += $row->total_achievement;
            $groupedRecords[$rKey]['areas'][$aKey]['toko_transaksi'] += $row->toko_transaksi;
            $groupedRecords[$rKey]['areas'][$aKey]['toko_hijau'] += $row->toko_hijau;
            $groupedRecords[$rKey]['areas'][$aKey]['toko_kuning'] += $row->toko_kuning;
            $groupedRecords[$rKey]['areas'][$aKey]['toko_merah'] += $row->toko_merah;

            // Accumulate metrics for Supervisor
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_toko'] += $row->total_toko;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_target'] += $row->total_target;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_jks'] += $row->total_jks;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['sudah_skb'] += $row->sudah_skb;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['skb_approve'] += $row->skb_approve;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['skb_reject'] += $row->skb_reject;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['data_lengkap'] += $row->data_lengkap;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['data_belum'] += $dataBelum;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['target_prorata'] += $row->target_prorata;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_achievement'] += $row->total_achievement;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['toko_transaksi'] += $row->toko_transaksi;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['toko_hijau'] += $row->toko_hijau;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['toko_kuning'] += $row->toko_kuning;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['toko_merah'] += $row->toko_merah;

            // Add Cabang
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['cabang'][] = [
                'distributor_code' => $row->distributor_code,
                'distributor_name' => $row->distributor_name ?? '-',
                'total_toko' => $row->total_toko,
                'total_target' => $row->total_target,
                'total_jks' => $row->total_jks,
                'sudah_skb' => $row->sudah_skb,
                'skb_approve' => $row->skb_approve,
                'skb_reject' => $row->skb_reject,
                'data_lengkap' => $row->data_lengkap,
                'data_belum' => $dataBelum,
                'target_prorata' => $row->target_prorata,
                'total_achievement' => $row->total_achievement,
                'toko_transaksi' => $row->toko_transaksi,
                'toko_hijau' => $row->toko_hijau,
                'toko_kuning' => $row->toko_kuning,
                'toko_merah' => $row->toko_merah
            ];
        }

        return $groupedRecords;
    }

    public function render()
    {
        return view('livewire.rwo.summary-list-potensi', [
            'records' => $this->getRecords(),
            'periodInfo' => $this->getPeriodInfo(),
        ])->layout('layouts.app');
    }

    public function exportExcel()
    {
        $records = $this->getRecords();
        return Excel::download(new SummaryListPotensiExport($records), 'Summary_List_Potensi_RWO.xlsx');
    }
}
