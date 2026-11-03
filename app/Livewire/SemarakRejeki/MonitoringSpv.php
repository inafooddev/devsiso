<?php

namespace App\Livewire\SemarakRejeki;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\SemarakRejekiRewardTier;
use App\Models\SemarakRejekiSpvTarget;
use App\Exports\SemarakRejekiSpvExport;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class MonitoringSpv extends Component
{
    use WithPagination;

    // Filters & View Mode
    public $year = 2026;
    public $viewMode = 'grid'; // 'grid' | 'table'
    public $filterWilayah = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterStatus = 'semua';
    public $search = '';
    public $perPage = 15;

    // Action Plan Modal State
    public $isActionModalOpen = false;
    public $selectedSpvCode = null;
    public $selectedSpvDetail = null;
    public $selectedDistributors = [];
    public $selectedTierLadder = [];
    public $selectedActionPlan = [];

    // Edit Target Modal State (Admin / SPM only)
    public $isTargetModalOpen = false;
    public $editingTargetId = null;
    public $formSpvCode = '';
    public $formSpvName = '';
    public $formWilayah = '';
    public $formTarget3Bln = 0;
    public $formKodePaket = '';
    public $formTargetM1 = 0;
    public $formTargetM2 = 0;
    public $formTargetM3 = 0;
    public $formCatatan = '';

    // Manage Tiers Modal State (Admin / SPM only)
    public $isTierModalOpen = false;
    public $activeTierWilayah = 'jawa';
    public $editingTierId = null;
    public $formTierNamaHadiah = '';
    public $formTierNilaiHadiah = 0;
    public $formTierTargetKtn = 0;

    // Feedback message
    public $alertSuccess = null;
    public $alertError = null;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        // Inisialisasi awal
    }

    public function updatedFilterWilayah()
    {
        $this->filterRegion = '';
        $this->filterArea = '';
        $this->resetPage();
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
        $this->resetPage();
    }

    public function updatedFilterArea()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterWilayah = '';
        $this->filterRegion = '';
        $this->filterArea = '';
        $this->filterStatus = 'semua';
        $this->search = '';
        $this->resetPage();
    }

    public function getCanManageProperty()
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'admspm', 'spvspm']) : false;
    }

    public function refreshData()
    {
        Cache::forget("semarak_rejeki_sales_map_{$this->year}");
        $this->alertSuccess = "Data kalkulasi penjualan berhasil di-refresh.";
    }

    /**
     * Mengambil mapping penjualan per SPV dan per Bulan
     */
    private function getSalesMap()
    {
        return Cache::remember("semarak_rejeki_sales_map_{$this->year}", 300, function () {
            $salesRaw = DB::table('t_sellingout as ts')
                ->join('master_distributors as md', 'md.distributor_code', '=', 'ts.KDDIST')
                ->whereIn('ts.NAMAITEMPRC', ['OKEBIS BISKUIT KELAPA 28', 'OKEBIS CHOCOLATE COOKIES 28'])
                ->whereRaw('CAST(ts."THN" AS INTEGER) = ?', [$this->year])
                ->whereIn(DB::raw('CAST(ts."BLN" AS INTEGER)'), [10, 11, 12])
                ->whereRaw('CAST(ts."NETT" AS NUMERIC) <> 0')
                ->select(
                    'md.supervisor_code as siso_code',
                    'ts.BLN',
                    DB::raw('SUM(CAST(ts."TTL_QTY_KTN" AS NUMERIC)) as total_ktn')
                )
                ->groupBy('md.supervisor_code', 'ts.BLN')
                ->get();

            $map = [];
            foreach ($salesRaw as $row) {
                $bln = (int)$row->BLN;
                $map[$row->siso_code][$bln] = (float)$row->total_ktn;
            }
            return $map;
        });
    }

    /**
     * Hitung sisa hari aktif bulan berjalan (Tanpa Hari Minggu)
     */
    private function getRemainingDaysInfo()
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        $evalMonth = ($currentYear == $this->year && in_array($currentMonth, [10, 11, 12])) ? $currentMonth : 11;
        
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $evalMonth, $this->year);
        $currentDay = ($currentMonth == $evalMonth && $currentYear == $this->year) ? (int)date('j') : 1;

        // Hitung hari kerja (Senin - Sabtu, tanpa hari Minggu)
        $totalWorkingDays = 0;
        $currentWorkingDay = 0;
        $remainingWorkingDays = 0;

        for ($d = 1; $d <= $totalDaysInMonth; $d++) {
            $dayOfWeek = (int)date('w', strtotime(sprintf('%04d-%02d-%02d', $this->year, $evalMonth, $d)));
            // 0 = Minggu (Sunday), 1..6 = Senin..Sabtu
            if ($dayOfWeek !== 0) {
                $totalWorkingDays++;
                if ($d <= $currentDay) {
                    $currentWorkingDay++;
                }
                if ($d >= $currentDay) {
                    $remainingWorkingDays++;
                }
            }
        }

        $monthNames = [10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        return [
            'eval_month'          => $evalMonth,
            'eval_month_name'     => $monthNames[$evalMonth] ?? 'November',
            'current_day'         => $currentDay,
            'current_working_day' => max(1, $currentWorkingDay),
            'total_days'          => $totalDaysInMonth,
            'total_working_days'  => $totalWorkingDays,
            'remaining_days'      => max(1, $remainingWorkingDays),
        ];
    }

    /**
     * Bangun data terkompilasi untuk seluruh SPV
     */
    private function compileSpvData()
    {
        $user = auth()->user();
        $accessLevel = $user ? $user->getAccessLevel() : 'nasional';

        $query = SemarakRejekiSpvTarget::with('tier')
            ->leftJoin('fsalesman as f', DB::raw('f."SLSNO"'), '=', 'semarak_rejeki_spv_targets.supervisor_code')
            ->where('semarak_rejeki_spv_targets.tahun', $this->year)
            ->where('semarak_rejeki_spv_targets.is_active', true)
            ->select('semarak_rejeki_spv_targets.*', DB::raw('f."SLSNAME" as fsalesman_name'));

        // Scoping akses user
        if ($accessLevel === 'supervisor') {
            $query->where(function($q) use ($user) {
                $q->where('semarak_rejeki_spv_targets.supervisor_code', $user->supervisor_code)
                  ->orWhere('semarak_rejeki_spv_targets.siso_code', $user->supervisor_code);
            });
        }

        $targets = $query->get();
        $salesMap = $this->getSalesMap();
        $daysInfo = $this->getRemainingDaysInfo();

        // Ambil info nama SPV dan wilayah dari master_distributors, team_elite & fsalesman (Hanya distributor aktif)
        $spvMasterInfo = DB::table('master_distributors as md')
            ->leftJoin('team_elite_code_mappings as tecm', 'tecm.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', DB::raw('f."SLSNO"'), '=', 'tecm.team_elite_code')
            ->where('md.is_active', true)
            ->where(function($q) {
                $q->whereNull('md.resign_date')
                  ->orWhere('md.resign_date', '>=', '2026-10-01');
            })
            ->select(
                'md.supervisor_code as siso_code',
                'md.supervisor_name',
                DB::raw('f."SLSNAME" as slsname'),
                'md.region_name',
                'md.area_name',
                'tecm.team_elite_code',
                DB::raw('COUNT(DISTINCT md.distributor_code) as total_distributor')
            )
            ->groupBy('md.supervisor_code', 'md.supervisor_name', DB::raw('f."SLSNAME"'), 'md.region_name', 'md.area_name', 'tecm.team_elite_code')
            ->get()
            ->keyBy('team_elite_code');

        $allTiers = SemarakRejekiRewardTier::where('is_active', true)
            ->orderBy('target_karton', 'desc')
            ->get()
            ->groupBy('kategori_wilayah');

        $compiled = [];

        foreach ($targets as $tg) {
            $master = $spvMasterInfo->get($tg->supervisor_code);
            $regionName = $master ? $master->region_name : '-';
            $areaName = $master ? $master->area_name : '-';
            $spvName = !empty($tg->fsalesman_name)
                ? $tg->fsalesman_name
                : ($master && !empty($master->slsname) ? $master->slsname : ($master ? $master->supervisor_name : $tg->supervisor_code));
            $distCount = $master ? $master->total_distributor : 0;

            // Filter Scoping tambahan jika level area / region
            if ($accessLevel === 'area' && !empty($user->area_code)) {
                $userAreas = (array)$user->area_code;
                $spvAreaCode = DB::table('team_elite_code_mappings')->where('team_elite_code', $tg->supervisor_code)->value('area_code');
                if (!in_array($spvAreaCode, $userAreas)) continue;
            } elseif ($accessLevel === 'region' && !empty($user->region_code)) {
                $userRegions = (array)$user->region_code;
                $spvRegCode = DB::table('team_elite_code_mappings')->where('team_elite_code', $tg->supervisor_code)->value('region_code');
                if (!in_array($spvRegCode, $userRegions)) continue;
            }

            // Realisasi per bulan
            $m1 = $salesMap[$tg->siso_code][10] ?? 0;
            $m2 = $salesMap[$tg->siso_code][11] ?? 0;
            $m3 = $salesMap[$tg->siso_code][12] ?? 0;

            $targetM1 = (float)$tg->target_m1_okt;
            $targetM2 = (float)$tg->target_m2_nov;
            $targetM3 = (float)$tg->target_m3_des;
            $targetTotal = (float)$tg->target_karton_3bln;

            // Evaluasi Milestone 1 (Oktober: min 70%)
            $m1Min70 = $targetM1 * 0.70;
            $m1Pct = $targetM1 > 0 ? ($m1 / $targetM1) * 100 : 0;
            $isM1Pass = $m1 >= $m1Min70;

            // Evaluasi Milestone 2 (Kumulatif Okt + Nov: min 80%)
            $kum2Actual = $m1 + $m2;
            $kum2Target = $targetM1 + $targetM2;
            $kum2Min80 = $kum2Target * 0.80;
            $kum2Pct = $kum2Target > 0 ? ($kum2Actual / $kum2Target) * 100 : 0;
            $isKum2Pass = $kum2Actual >= $kum2Min80;

            // Evaluasi Total 3 Bulan (min 100%)
            $totalActual = $m1 + $m2 + $m3;
            $totalPct = $targetTotal > 0 ? ($totalActual / $targetTotal) * 100 : 0;
            $isTotalPass = $totalActual >= $targetTotal;

            // Analisa Kesenjangan & Status
            $statusKey = 'on_track';
            $statusLabel = 'Aman (On Track)';
            $badgeColor = 'success';
            $actionAdvice = '';
            $gapMilestone = 0;
            $dailyRate = 0;

            if (!$isM1Pass) {
                $statusKey = 'hangus';
                $statusLabel = 'Gugur di Bulan 1 (<70%)';
                $badgeColor = 'error';
                $deficit = max(0, $m1Min70 - $m1);
                $m1Fmt = number_format($m1, 1, ',', '.');
                $m1Min70Fmt = number_format($m1Min70, 1, ',', '.');
                $deficitFmt = number_format($deficit, 1, ',', '.');
                $actionAdvice = "Realisasi Oktober ({$m1Fmt} Ktn) di bawah batas minimal 70% ({$m1Min70Fmt} Ktn). Kurang {$deficitFmt} Ktn.";
            } else {
                if ($isKum2Pass) {
                    $statusKey = 'on_track';
                    $statusLabel = 'Aman (Lolos Kumulatif)';
                    $badgeColor = 'success';
                    $kum2PctFmt = number_format($kum2Pct, 1, ',', '.');
                    $actionAdvice = "Performa baik! Kumulatif sudah mencapai {$kum2PctFmt}%. Pertahankan ritme penjualan.";
                } else {
                    $statusKey = 'warning';
                    $statusLabel = 'Perlu Kejar Batas 80%';
                    $badgeColor = 'warning';
                    $gapMilestone = max(0, $kum2Min80 - $kum2Actual);
                    $dailyRate = $daysInfo['remaining_days'] > 0 ? ($gapMilestone / $daysInfo['remaining_days']) : $gapMilestone;
                    $gapMilestoneFmt = number_format($gapMilestone, 1, ',', '.');
                    $dailyRateFmt = number_format($dailyRate, 1, ',', '.');
                    $actionAdvice = "Kurang {$gapMilestoneFmt} Ktn lagi untuk batas aman 80%. Wajib jual ~{$dailyRateFmt} Ktn/hari kerja di sisa {$daysInfo['remaining_days']} hari kerja.";
                }
            }

            // Auto Upgrade Checker
            $tiersForRegion = $allTiers->get($tg->kategori_wilayah, collect());
            $eligibleTier = $tiersForRegion->first(function($tier) use ($totalActual) {
                return $totalActual >= $tier->target_karton;
            });

            $upgradedTier = null;
            $nextTier = null;
            $gapToNextTier = 0;

            if ($eligibleTier && $eligibleTier->target_karton > $targetTotal) {
                $upgradedTier = $eligibleTier;
                $statusKey = 'upgrade_ready';
                $statusLabel = 'Auto Upgrade Ready!';
                $badgeColor = 'primary';
            }

            $currentTierTarget = $upgradedTier ? $upgradedTier->target_karton : $targetTotal;
            $nextTier = $tiersForRegion->reverse()->first(function($tier) use ($currentTierTarget) {
                return $tier->target_karton > $currentTierTarget;
            });

            if ($nextTier) {
                $gapToNextTier = max(0, $nextTier->target_karton - $totalActual);
                if ($isM1Pass && $isKum2Pass) {
                    $gapFmt = number_format($gapToNextTier, 1, ',', '.');
                    $actionAdvice .= " Peluang: Tambah +{$gapFmt} Ktn lagi untuk upgrade ke Paket {$nextTier->kode_paket} ({$nextTier->nama_hadiah})!";
                }
            }

            $compiled[] = [
                'target_id'           => $tg->id,
                'supervisor_code'     => $tg->supervisor_code,
                'siso_code'           => $tg->siso_code,
                'supervisor_name'     => $spvName,
                'region_name'         => $regionName,
                'area_name'           => $areaName,
                'kategori_wilayah'    => $tg->kategori_wilayah,
                'distributor_count'   => $distCount,
                'kode_paket_awal'     => $tg->kode_paket_awal,
                'nama_hadiah_awal'    => $tg->tier ? $tg->tier->nama_hadiah : '-',
                'reward_category'     => SemarakRejekiRewardTier::detectCategory($tg->tier ? $tg->tier->nama_hadiah : ''),
                'target_3bln'         => $targetTotal,
                'target_m1'           => $targetM1,
                'm1_min_70'           => $m1Min70,
                'realisasi_m1'        => $m1,
                'pct_m1'              => round($m1Pct, 1),
                'is_m1_pass'          => $isM1Pass,
                'status_m1'           => $isM1Pass ? 'Lolos (≥70%)' : 'Gugur (<70%)',
                'target_m2'           => $targetM2,
                'realisasi_m2'        => $m2,
                'target_kum2'         => $kum2Target,
                'kum2_min_80'         => $kum2Min80,
                'realisasi_kum2'      => $kum2Actual,
                'pct_kum2'            => round($kum2Pct, 1),
                'is_kum2_pass'        => $isKum2Pass,
                'status_kum2'         => $isKum2Pass ? 'Lolos (≥80%)' : 'Belum Capai',
                'target_m3'           => $targetM3,
                'realisasi_m3'        => $m3,
                'total_realisasi_3bln'=> $totalActual,
                'pct_total'           => round($totalPct, 1),
                'is_total_pass'       => $isTotalPass,
                'status_key'          => $statusKey,
                'status_label'        => $statusLabel,
                'badge_color'         => $badgeColor,
                'gap_milestone'       => $gapMilestone,
                'daily_rate_needed'   => $dailyRate,
                'action_advice'       => $actionAdvice,
                'paket_tercapai'      => $upgradedTier ? $upgradedTier->kode_paket : $tg->kode_paket_awal,
                'hadiah_tercapai'     => $upgradedTier ? $upgradedTier->nama_hadiah : ($tg->tier ? $tg->tier->nama_hadiah : '-'),
                'is_upgraded'         => $upgradedTier !== null,
                'next_tier'           => $nextTier,
                'gap_to_next_tier'    => $gapToNextTier,
                'catatan'             => $tg->catatan,
            ];
        }

        return $compiled;
    }

    /**
     * Buka Modal Analisa & Action Plan Detail SPV
     */
    public function openActionModal($spvCode)
    {
        $allData = collect($this->compileSpvData());
        $spv = $allData->firstWhere('supervisor_code', $spvCode);

        if (!$spv) {
            $this->alertError = "Data SPV tidak ditemukan.";
            return;
        }

        $this->selectedSpvCode = $spvCode;
        $this->selectedSpvDetail = $spv;

        // Ambil data kontribusi distributor AKTIF di bawah SPV ini
        $distributors = DB::table('master_distributors as md')
            ->where('md.supervisor_code', $spv['siso_code'])
            ->where('md.is_active', true)
            ->where(function($q) {
                $q->whereNull('md.resign_date')
                  ->orWhere('md.resign_date', '>=', '2026-10-01');
            })
            ->select('md.distributor_code', 'md.distributor_name', 'md.branch_name', 'md.area_name')
            ->get();

        $distCodes = $distributors->pluck('distributor_code')->toArray();

        // Ambil penjualan per distributor
        $distSales = DB::table('t_sellingout as ts')
            ->whereIn('ts.KDDIST', $distCodes)
            ->whereIn('ts.NAMAITEMPRC', ['OKEBIS BISKUIT KELAPA 28', 'OKEBIS CHOCOLATE COOKIES 28'])
            ->whereRaw('CAST(ts."THN" AS INTEGER) = ?', [$this->year])
            ->whereIn(DB::raw('CAST(ts."BLN" AS INTEGER)'), [10, 11])
            ->whereRaw('CAST(ts."NETT" AS NUMERIC) <> 0')
            ->select(
                'ts.KDDIST',
                'ts.BLN',
                DB::raw('SUM(CAST(ts."TTL_QTY_KTN" AS NUMERIC)) as total_ktn')
            )
            ->groupBy('ts.KDDIST', 'ts.BLN')
            ->get();

        $distSalesMap = [];
        foreach ($distSales as $ds) {
            $bln = (int)$ds->BLN;
            $distSalesMap[$ds->KDDIST][$bln] = (float)$ds->total_ktn;
        }

        $distList = [];
        $totalDistActual = max(0.01, $spv['realisasi_kum2']);

        foreach ($distributors as $d) {
            $okt = $distSalesMap[$d->distributor_code][10] ?? 0;
            $nov = $distSalesMap[$d->distributor_code][11] ?? 0;
            $total = $okt + $nov;
            $contrib = round(($total / $totalDistActual) * 100, 1);

            $statusOrder = '🟢 Rutin Order';
            if ($total == 0) {
                $statusOrder = '🔴 Belum Ada Order';
            } elseif ($nov == 0) {
                $statusOrder = '🟡 Belum Order Bulan Ini';
            }

            $distList[] = [
                'distributor_code' => $d->distributor_code,
                'distributor_name' => $d->distributor_name,
                'branch_name'      => $d->branch_name,
                'okt_ktn'          => round($okt, 2),
                'nov_ktn'          => round($nov, 2),
                'total_ktn'        => round($total, 2),
                'contribution_pct' => $contrib,
                'status_order'     => $statusOrder,
            ];
        }

        usort($distList, function($a, $b) {
            return $b['total_ktn'] <=> $a['total_ktn'];
        });

        // Bangun Simulasi Tangga Paket Hadiah (Mulai dari Paket Awal ke atas, tidak ada downgrade)
        $actual3Bln = (float) $spv['total_realisasi_3bln'];
        $initialTarget = (float) $spv['target_3bln'];

        $ladderSlice = SemarakRejekiRewardTier::where('kategori_wilayah', $spv['kategori_wilayah'])
            ->where('is_active', true)
            ->where('target_karton', '>=', $initialTarget)
            ->orderBy('target_karton', 'asc')
            ->take(4)
            ->get()
            ->values();

        $ladder = [];
        foreach ($ladderSlice as $idx => $t) {
            $tKtn = (float) $t->target_karton;
            $isInitial = $t->kode_paket === $spv['kode_paket_awal'];
            $isCurrentTarget = $t->kode_paket === $spv['paket_tercapai'];
            $isAchieved = $actual3Bln >= $tKtn;
            $pct = $tKtn > 0 ? min(100, round(($actual3Bln / $tKtn) * 100, 1)) : 0;
            $gap = max(0, $tKtn - $actual3Bln);
            $diffFromInitial = $tKtn - $initialTarget;

            $stepLabel = $isInitial ? 'Paket Awal SPV' : 'Upgrade +' . $idx;

            $ladder[] = [
                'kode_paket'        => $t->kode_paket,
                'nama_hadiah'       => $t->nama_hadiah,
                'reward_category'   => $t->reward_category,
                'target_karton'     => $tKtn,
                'is_initial'        => $isInitial,
                'is_current_target' => $isCurrentTarget,
                'is_achieved'       => $isAchieved,
                'pct_progress'      => $pct,
                'gap_ktn'           => $gap,
                'diff_from_initial' => $diffFromInitial,
                'step_label'        => $stepLabel,
            ];
        }

        // Bangun Rencana Action Plan Taktis SPV (Spesifik sesuai kondisi SPV & Depo)
        $daysInfo = $this->getRemainingDaysInfo();
        $remDays = max(1, (int) $daysInfo['remaining_days']);
        $distCount = max(1, count($distList));
        $distCollection = collect($distList);
        $unOrderedNovNames = $distCollection->where('nov_ktn', '<=', 0)->pluck('distributor_name')->take(2)->join(', ');
        $unOrderedNovCount = $distCollection->where('nov_ktn', '<=', 0)->count();
        $topDist = $distCollection->first();
        $lowestDist = $distCollection->last();

        if (!$spv['is_m1_pass']) {
            $deficitM1 = max(0, $spv['m1_min_70'] - $spv['realisasi_m1']);
            $m1Fmt = number_format($spv['realisasi_m1'], 1, ',', '.');
            $m1MinFmt = number_format($spv['m1_min_70'], 1, ',', '.');
            $defFmt = number_format($deficitM1, 1, ',', '.');
            $regDailyFmt = number_format($spv['target_m2'] / 25, 1, ',', '.');
            $targetM2Fmt = number_format($spv['target_m2'], 0, ',', '.');

            $depoStep = $unOrderedNovCount > 0
                ? "Terdapat {$unOrderedNovCount} depo aktif yang belum membukukan penjualan di bulan November ({$unOrderedNovNames}). Segera lakukan pengecekan stok dan dorong distribusi ke toko."
                : ($lowestDist ? "Fokus tingkatkan penjualan di depo {$lowestDist['distributor_name']} (saat ini baru {$lowestDist['total_ktn']} Ktn / {$lowestDist['contribution_pct']}%) agar kontribusi antar depo lebih merata." : "Lakukan pembinaan intensif pada seluruh depo di bawah supervisi ini.");

            $actionPlan = [
                'theme'    => 'rose',
                'badge'    => 'Evaluasi & Pemulihan',
                'headline' => 'Evaluasi Ketertinggalan Tahap 1 (Okt < 70%) & Pemulihan Volume Area',
                'steps'    => [
                    [
                        'title' => 'Evaluasi Kekurangan Tahap 1 (Oktober)',
                        'desc'  => "Realisasi Oktober tercatat {$m1Fmt} Ktn dari syarat minimal wajib 70% ({$m1MinFmt} Ktn), sehingga Kurang {$defFmt} Ktn. Pastikan seluruh faktur selling out Oktober dari {$distCount} depo sudah ter-input lengkap.",
                    ],
                    [
                        'title' => 'Tindak Lanjut Depo Belum Order / Kontribusi Rendah',
                        'desc'  => $depoStep,
                    ],
                    [
                        'title' => 'Kawal Target Penjualan Reguler Q4 (Nov–Des)',
                        'desc'  => "Tetap amankan ritme penjualan reguler bulanan sebesar {$targetM2Fmt} Ktn/bulan (~{$regDailyFmt} Ktn/hari kerja) untuk menjaga pencapaian KPI omset area.",
                    ],
                ],
            ];
        } elseif (!$spv['is_kum2_pass']) {
            $gapKum2 = max(0, $spv['gap_milestone']);
            $gapKum2Fmt = number_format($gapKum2, 1, ',', '.');
            $dailyFmt = number_format($spv['daily_rate_needed'], 1, ',', '.');
            $perDepoDailyFmt = number_format($spv['daily_rate_needed'] / $distCount, 1, ',', '.');
            $remTo100Fmt = number_format(max(0, $spv['target_3bln'] - $spv['kum2_min_80']), 1, ',', '.');

            $depoStep = $unOrderedNovCount > 0
                ? "Segera aktifkan penjualan pada {$unOrderedNovCount} depo yang belum ada realisasi di bulan November ({$unOrderedNovNames}), sembari menjaga repeat order di depo utama (" . ($topDist['distributor_name'] ?? '-') . ")."
                : ($lowestDist && $distCount > 1
                    ? "Genjot kontribusi depo {$lowestDist['distributor_name']} (baru {$lowestDist['contribution_pct']}%) agar beban kejar ~{$dailyFmt} Ktn/hari tidak hanya bertumpu pada {$topDist['distributor_name']}."
                    : "Fokus eksekusi harian bersama salesman depo " . ($topDist['distributor_name'] ?? '') . " pada toko grosir & pareto.");

            $actionPlan = [
                'theme'    => 'amber',
                'badge'    => 'Prioritas Kejar 80%',
                'headline' => ' Rencana Kejar Batas Wajib 80% (Okt-Nov) di Sisa Hari Kerja November',
                'steps'    => [
                    [
                        'title' => "Target Harian Wajib: ~{$dailyFmt} Ktn/Hari Kerja",
                        'desc'  => "Untuk menutup kekurangan Kurang {$gapKum2Fmt} Ktn di sisa {$remDays} hari kerja November, rata-rata tiap depo wajib menjual ~{$perDepoDailyFmt} Ktn/hari kerja.",
                    ],
                    [
                        'title' => 'Pembagian Fokus Eksekusi Depo',
                        'desc'  => $depoStep,
                    ],
                    [
                        'title' => "Penguncian Hadiah Paket {$spv['kode_paket_awal']} ({$spv['nama_hadiah_awal']})",
                        'desc'  => "Setelah batas wajib 80% Okt-Nov terlampaui, sisa target menuju 100% di bulan Desember tinggal {$remTo100Fmt} Ktn lagi.",
                    ],
                ],
            ];
        } else {
            $gapTotal = max(0, $spv['target_3bln'] - $spv['total_realisasi_3bln']);
            $gapTotalFmt = number_format($gapTotal, 1, ',', '.');

            $step1Title = $gapTotal > 0
                ? "Kunci Sisa Target 100% Paket {$spv['kode_paket_awal']} (Kurang {$gapTotalFmt} Ktn)"
                : "Target 100% Paket {$spv['kode_paket_awal']} Sudah Terkunci!";
            $step1Desc = $gapTotal > 0
                ? "Syarat wajib Okt (≥70%) dan Okt-Nov (≥80%) sudah aman. Fokus selesaikan sisa Kurang {$gapTotalFmt} Ktn untuk mengamankan penuh hadiah {$spv['nama_hadiah_awal']}."
                : "Realisasi 3 bulan sudah melampaui 100% target Paket {$spv['kode_paket_awal']} ({$spv['nama_hadiah_awal']}). Pastikan tidak ada retur faktur yang mengurangi nett penjualan.";

            if ($spv['next_tier']) {
                $gapUpFmt = number_format($spv['gap_to_next_tier'], 1, ',', '.');
                $upPerDepoFmt = number_format($spv['gap_to_next_tier'] / $distCount, 1, ',', '.');
                $step2Title = "Akselerasi Auto-Upgrade ke Paket {$spv['next_tier']->kode_paket} ({$spv['next_tier']->nama_hadiah})";
                $step2Desc = "Cukup kejar tambahan Kurang {$gapUpFmt} Ktn lagi (rata-rata ~{$upPerDepoFmt} Ktn per depo aktif) hingga akhir Desember untuk otomatis naik ke Paket {$spv['next_tier']->kode_paket}.";
            } else {
                $step2Title = 'Posisi Paket Tertinggi Tercapai';
                $step2Desc = 'SPV sudah berada pada tingkat hadiah tertinggi untuk wilayah ' . ucfirst($spv['kategori_wilayah']) . '. Pertahankan volume penjualan hingga penutupan program 31 Desember.';
            }

            $depoStep = $unOrderedNovCount > 0
                ? "Optimalkan {$unOrderedNovCount} depo yang belum bergerak di bulan November ({$unOrderedNovNames}) sebagai sumber karton tambahan untuk mengejar auto-upgrade."
                : "Pertahankan ritme repeat order di depo kontributor utama (" . ($topDist['distributor_name'] ?? '-') . ") dan kawal ketersediaan stok Okebis 28 di seluruh depo.";

            $actionPlan = [
                'theme'    => 'emerald',
                'badge'    => 'Aman & Peluang Upgrade',
                'headline' => 'Strategi Penguncian Hadiah 100% & Akselerasi Auto-Upgrade Paket',
                'steps'    => [
                    [
                        'title' => $step1Title,
                        'desc'  => $step1Desc,
                    ],
                    [
                        'title' => $step2Title,
                        'desc'  => $step2Desc,
                    ],
                    [
                        'title' => 'Optimalisasi Kontribusi & Repeat Order Depo',
                        'desc'  => $depoStep,
                    ],
                ],
            ];
        }

        $this->selectedTierLadder = $ladder;
        $this->selectedDistributors = $distList;
        $this->selectedActionPlan = $actionPlan;
        $this->isActionModalOpen = true;
    }

    public function closeActionModal()
    {
        $this->isActionModalOpen = false;
        $this->selectedSpvCode = null;
        $this->selectedSpvDetail = null;
        $this->selectedDistributors = [];
        $this->selectedTierLadder = [];
        $this->selectedActionPlan = [];
    }

    /**
     * Buka Modal Edit Target SPV (Khusus Admin / SPM)
     */
    public function openEditTargetModal($targetId)
    {
        if (!$this->canManage) {
            $this->alertError = "Akses ditolak: Hanya Admin & SPM yang dapat mengubah target.";
            return;
        }

        $target = SemarakRejekiSpvTarget::with('tier')->find($targetId);
        if (!$target) return;

        $spvName = DB::table('fsalesman')
            ->where(DB::raw('"SLSNO"'), $target->supervisor_code)
            ->value(DB::raw('"SLSNAME"'))
            ?? DB::table('master_distributors')->where('supervisor_code', $target->siso_code)->value('supervisor_name')
            ?? $target->supervisor_code;

        $this->editingTargetId = $target->id;
        $this->formSpvCode = $target->supervisor_code;
        $this->formSpvName = $spvName;
        $this->formWilayah = $target->kategori_wilayah;
        $this->formTarget3Bln = (float)$target->target_karton_3bln;
        $this->formKodePaket = $target->kode_paket_awal;
        $this->formTargetM1 = (float)$target->target_m1_okt;
        $this->formTargetM2 = (float)$target->target_m2_nov;
        $this->formTargetM3 = (float)$target->target_m3_des;
        $this->formCatatan = $target->catatan;

        $this->isTargetModalOpen = true;
    }

    public function updateTarget3BlnCalculations()
    {
        $val = (float)$this->formTarget3Bln;
        $this->formTargetM1 = round($val / 3, 2);
        $this->formTargetM2 = round($val / 3, 2);
        $this->formTargetM3 = round($val - ($this->formTargetM1 + $this->formTargetM2), 2);

        $matchedTier = SemarakRejekiRewardTier::where('kategori_wilayah', $this->formWilayah)
            ->where('target_karton', $val)
            ->first();

        if ($matchedTier) {
            $this->formKodePaket = $matchedTier->kode_paket;
        }
    }

    public function saveTarget()
    {
        if (!$this->canManage) {
            abort(403, 'Akses Ditolak.');
        }

        $this->validate([
            'formTarget3Bln' => 'required|numeric|min:1',
            'formTargetM1'   => 'required|numeric|min:0',
            'formTargetM2'   => 'required|numeric|min:0',
            'formTargetM3'   => 'required|numeric|min:0',
        ]);

        $target = SemarakRejekiSpvTarget::find($this->editingTargetId);
        if (!$target) return;

        $tier = SemarakRejekiRewardTier::where('kategori_wilayah', $target->kategori_wilayah)
            ->where('kode_paket', $this->formKodePaket)
            ->first();

        $target->update([
            'target_karton_3bln' => $this->formTarget3Bln,
            'target_m1_okt'      => $this->formTargetM1,
            'target_m2_nov'      => $this->formTargetM2,
            'target_m3_des'      => $this->formTargetM3,
            'kode_paket_awal'    => $this->formKodePaket,
            'tier_id'            => $tier ? $tier->id : $target->tier_id,
            'catatan'            => $this->formCatatan,
        ]);

        $this->isTargetModalOpen = false;
        $this->alertSuccess = "Target SPV {$target->supervisor_code} berhasil diperbarui.";
    }

    public function closeTargetModal()
    {
        $this->isTargetModalOpen = false;
        $this->editingTargetId = null;
    }

    /**
     * Deteksi daftar wilayah promo ('jawa' | 'pulau') yang diizinkan untuk user login
     */
    private function getAllowedWilayahs($compiledData): array
    {
        $user = auth()->user();
        if (!$user || $user->hasRole(['admin', 'admspm', 'spvspm', 'spm'])) {
            return ['jawa', 'pulau'];
        }

        $accessLevel = $user->getAccessLevel();
        $fromData = $compiledData->pluck('kategori_wilayah')->filter()->unique()->values()->toArray();

        if ($accessLevel !== 'nasional') {
            if (!empty($fromData)) {
                return $fromData;
            }

            $regions = (array) ($user->region_code ?? []);
            if (!empty($regions)) {
                $allowed = [];
                foreach ($regions as $reg) {
                    if (in_array($reg, ['INAJWA1', 'INAJWA2'])) {
                        $allowed[] = 'jawa';
                    } elseif ($reg === 'HOINA') {
                        return ['jawa', 'pulau'];
                    } else {
                        $allowed[] = 'pulau';
                    }
                }
                return array_values(array_unique($allowed));
            }
        }

        return !empty($fromData) ? $fromData : ['jawa', 'pulau'];
    }

    /**
     * Deteksi wilayah yang tampil di Modal Master Hadiah sesuai scope user & view filter aktif
     */
    private function getVisibleTierWilayahs($compiledData, $filteredData = null): array
    {
        $allowed = $this->getAllowedWilayahs($compiledData);
        if (count($allowed) === 1) {
            return $allowed;
        }

        if ($this->filterWilayah && in_array($this->filterWilayah, $allowed)) {
            return [$this->filterWilayah];
        }

        if (($this->filterRegion || $this->filterArea) && $filteredData !== null) {
            $fromView = $filteredData->pluck('kategori_wilayah')->filter()->unique()->values()->toArray();
            if (count($fromView) === 1 && in_array($fromView[0], $allowed)) {
                return $fromView;
            }
        }

        return $allowed;
    }

    /**
     * Buka Modal Katalog Paket & Hadiah (Dapat dilihat semua user, disesuaikan dengan wilayah view/user)
     */
    public function openTierModal()
    {
        $compiledData = collect($this->compileSpvData());
        $filtered = $compiledData->filter(function ($item) {
            if ($this->filterWilayah && $item['kategori_wilayah'] !== $this->filterWilayah) return false;
            if ($this->filterRegion && $item['region_name'] !== $this->filterRegion) return false;
            if ($this->filterArea && $item['area_name'] !== $this->filterArea) return false;
            return true;
        });

        $visibleWilayahs = $this->getVisibleTierWilayahs($compiledData, $filtered);
        if (!in_array($this->activeTierWilayah, $visibleWilayahs)) {
            $this->activeTierWilayah = $visibleWilayahs[0] ?? 'jawa';
        }

        $this->editingTierId = null;
        $this->isTierModalOpen = true;
    }

    public function editTier($tierId)
    {
        if (!$this->canManage) {
            $this->alertError = "Akses ditolak: Hanya Admin & SPM yang dapat mengubah hadiah.";
            return;
        }

        $tier = SemarakRejekiRewardTier::find($tierId);
        if (!$tier) return;

        $this->editingTierId = $tier->id;
        $this->formTierNamaHadiah = $tier->nama_hadiah;
        $this->formTierNilaiHadiah = (float)$tier->nilai_hadiah;
        $this->formTierTargetKtn = (int)$tier->target_karton;
    }

    public function saveTier()
    {
        if (!$this->canManage) {
            abort(403, 'Akses Ditolak.');
        }

        $tier = SemarakRejekiRewardTier::find($this->editingTierId);
        if (!$tier) return;

        $trx = $this->formTierTargetKtn * 220500;

        $tier->update([
            'nama_hadiah'     => $this->formTierNamaHadiah,
            'nilai_hadiah'    => $this->formTierNilaiHadiah,
            'target_karton'   => $this->formTierTargetKtn,
            'nilai_transaksi' => $trx,
        ]);

        $this->editingTierId = null;
        $this->alertSuccess = "Data Paket {$tier->kode_paket} ({$tier->kategori_wilayah}) berhasil diperbarui.";
    }

    public function closeTierModal()
    {
        $this->isTierModalOpen = false;
        $this->editingTierId = null;
    }

    /**
     * Terapkan filter aktif (Wilayah, Region, Area, Status, Search) pada kumpulan data SPV
     */
    protected function applyActiveFilters(Collection $compiledData): Collection
    {
        return $compiledData->filter(function ($item) {
            if ($this->filterWilayah && $item['kategori_wilayah'] !== $this->filterWilayah) return false;
            if ($this->filterRegion && $item['region_name'] !== $this->filterRegion) return false;
            if ($this->filterArea && $item['area_name'] !== $this->filterArea) return false;
            if ($this->filterStatus !== 'semua' && $item['status_key'] !== $this->filterStatus) return false;
            if ($this->search) {
                $term = strtolower($this->search);
                $matchSpv = str_contains(strtolower($item['supervisor_name']), $term);
                $matchCode = str_contains(strtolower($item['supervisor_code']), $term);
                $matchArea = str_contains(strtolower($item['area_name']), $term);
                $matchHadiah = str_contains(strtolower($item['nama_hadiah_awal']), $term);
                if (!$matchSpv && !$matchCode && !$matchArea && !$matchHadiah) return false;
            }
            return true;
        });
    }

    /**
     * Export ke Excel (mengikuti filter aktif di layar)
     */
    public function exportExcel()
    {
        $compiledData = collect($this->compileSpvData());
        $filteredData = $this->applyActiveFilters($compiledData)->values()->all();

        return Excel::download(
            new SemarakRejekiSpvExport($filteredData, $this->year),
            "Semarak_Rejeki_Reward_SPV_{$this->year}.xlsx"
        );
    }

    public function render()
    {
        $compiledData = collect($this->compileSpvData());
        $allowedWilayahs = $this->getAllowedWilayahs($compiledData);

        // Ambil filter list dropdown sesuai hak akses wilayah user (Chain Filter: Wilayah -> Region -> Area)
        $allWilayahLabels = ['jawa' => 'Regional Jawa', 'pulau' => 'Regional Pulau'];
        $wilayahList = array_intersect_key($allWilayahLabels, array_flip($allowedWilayahs));

        // 1. Chain Level 1: Wilayah Promo
        if (count($allowedWilayahs) === 1) {
            $this->filterWilayah = $allowedWilayahs[0];
        } elseif (!empty($this->filterWilayah) && !in_array($this->filterWilayah, $allowedWilayahs, true)) {
            $this->filterWilayah = '';
        }

        // 2. Chain Level 2: Region (tergantung pada Wilayah Promo yang dipilih jika ada)
        $dataForRegions = $this->filterWilayah
            ? $compiledData->where('kategori_wilayah', $this->filterWilayah)
            : $compiledData;

        $regions = $dataForRegions->pluck('region_name')
            ->filter(fn($v) => !empty($v) && $v !== '-')
            ->unique()
            ->sort()
            ->values();

        if (count($allowedWilayahs) === 1 && $regions->count() === 1) {
            $this->filterRegion = $regions->first();
        } elseif (!empty($this->filterRegion) && !$regions->contains($this->filterRegion)) {
            $this->filterRegion = '';
        }

        // 3. Chain Level 3: Area (tergantung pada Region yang dipilih)
        $dataForAreas = $this->filterRegion
            ? $dataForRegions->where('region_name', $this->filterRegion)
            : collect();

        $areas = $dataForAreas->pluck('area_name')
            ->filter(fn($v) => !empty($v) && $v !== '-')
            ->unique()
            ->sort()
            ->values();

        if (count($allowedWilayahs) === 1 && $regions->count() === 1 && $areas->count() === 1) {
            $this->filterArea = $areas->first();
        } elseif (!empty($this->filterArea) && !$areas->contains($this->filterArea)) {
            $this->filterArea = '';
        }

        // Data terfilter secara geografis (Wilayah, Region, Area) untuk KPI Eksekutif
        $geoScopedData = $compiledData->filter(function ($item) {
            if ($this->filterWilayah && $item['kategori_wilayah'] !== $this->filterWilayah) return false;
            if ($this->filterRegion && $item['region_name'] !== $this->filterRegion) return false;
            if ($this->filterArea && $item['area_name'] !== $this->filterArea) return false;
            return true;
        });

        // Hitung KPI Eksekutif (Ringkasan mengikuti filter wilayah aktif)
        $totalSpv = $geoScopedData->count();
        $onTrackCount = $geoScopedData->where('status_key', 'on_track')->count() + $geoScopedData->where('status_key', 'upgrade_ready')->count();
        $warningCount = $geoScopedData->where('status_key', 'warning')->count();
        $hangusCount = $geoScopedData->where('status_key', 'hangus')->count();
        $totalTargetKarton = $geoScopedData->sum('target_3bln');
        $totalActualKarton = $geoScopedData->sum('total_realisasi_3bln');
        $overallPct = $totalTargetKarton > 0 ? round(($totalActualKarton / $totalTargetKarton) * 100, 1) : 0;

        $jawaCount = $compiledData->where('kategori_wilayah', 'jawa')->count();
        $pulauCount = $compiledData->where('kategori_wilayah', 'pulau')->count();
        $totalActiveDist = $geoScopedData->sum('distributor_count');
        $eligibleCount = $onTrackCount + $warningCount;
        $eligiblePct = $totalSpv > 0 ? round(($eligibleCount / $totalSpv) * 100, 1) : 0;

        $hargaKarton = 220500;
        $totalActualRp = $totalActualKarton * $hargaKarton;
        $totalTargetRp = $totalTargetKarton * $hargaKarton;
        $totalActualRpFmt = number_format($totalActualRp / 1000000000, 2, ',', '.') . ' M';
        $totalTargetRpFmt = number_format($totalTargetRp / 1000000000, 2, ',', '.') . ' M';

        // Filter Collection
        $filtered = $this->applyActiveFilters($compiledData);

        // Pagination manual pada Collection
        $currentPage = $this->getPage();
        $pagedData = $filtered->slice(($currentPage - 1) * $this->perPage, $this->perPage)->all();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $filtered->count(),
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $visibleTierWilayahs = $this->getVisibleTierWilayahs($compiledData, $filtered);
        if (!in_array($this->activeTierWilayah, $visibleTierWilayahs)) {
            $this->activeTierWilayah = $visibleTierWilayahs[0] ?? 'jawa';
        }

        $tiersData = SemarakRejekiRewardTier::where('kategori_wilayah', $this->activeTierWilayah)
            ->orderBy('urutan')
            ->get();

        $daysInfo = $this->getRemainingDaysInfo();

        return view('livewire.semarak-rejeki.monitoring-spv', [
            'year'                 => $this->year,
            'viewMode'             => $this->viewMode,
            'spvList'              => $paginator,
            'wilayahList'          => $wilayahList,
            'allowedWilayahs'      => $allowedWilayahs,
            'visibleTierWilayahs'  => $visibleTierWilayahs,
            'filterWilayah'        => $this->filterWilayah,
            'filterRegion'         => $this->filterRegion,
            'filterArea'           => $this->filterArea,
            'filterStatus'         => $this->filterStatus,
            'regions'              => $regions,
            'areas'                => $areas,
            'totalSpv'             => $totalSpv,
            'jawaCount'            => $jawaCount,
            'pulauCount'           => $pulauCount,
            'totalActiveDist'      => $totalActiveDist,
            'eligibleCount'        => $eligibleCount,
            'eligiblePct'          => $eligiblePct,
            'onTrackCount'         => $onTrackCount,
            'warningCount'         => $warningCount,
            'hangusCount'          => $hangusCount,
            'totalTargetKarton'    => $totalTargetKarton,
            'totalActualKarton'    => $totalActualKarton,
            'totalActualRpFmt'     => $totalActualRpFmt,
            'totalTargetRpFmt'     => $totalTargetRpFmt,
            'overallPct'           => $overallPct,
            'tiersData'            => $tiersData,
            'daysInfo'             => $daysInfo,
            'alertSuccess'         => $this->alertSuccess,
            'alertError'           => $this->alertError,
            'isActionModalOpen'    => $this->isActionModalOpen,
            'selectedSpvDetail'    => $this->selectedSpvDetail,
            'selectedDistributors' => $this->selectedDistributors,
            'selectedTierLadder'   => $this->selectedTierLadder,
            'selectedActionPlan'   => $this->selectedActionPlan,
            'isTargetModalOpen'    => $this->isTargetModalOpen,
            'formSpvName'          => $this->formSpvName,
            'formSpvCode'          => $this->formSpvCode,
            'formWilayah'          => $this->formWilayah,
            'formTarget3Bln'       => $this->formTarget3Bln,
            'formTargetM1'         => $this->formTargetM1,
            'formTargetM2'         => $this->formTargetM2,
            'formTargetM3'         => $this->formTargetM3,
            'formKodePaket'        => $this->formKodePaket,
            'formCatatan'          => $this->formCatatan,
            'isTierModalOpen'      => $this->isTierModalOpen,
            'activeTierWilayah'    => $this->activeTierWilayah,
            'editingTierId'        => $this->editingTierId,
            'formTierNamaHadiah'   => $this->formTierNamaHadiah,
            'formTierNilaiHadiah'  => $this->formTierNilaiHadiah,
        ]);
    }
}
