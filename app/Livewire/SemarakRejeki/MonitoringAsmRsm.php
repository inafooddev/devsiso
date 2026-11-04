<?php

namespace App\Livewire\SemarakRejeki;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\SemarakRejekiRsmTarget;
use App\Models\SemarakRejekiAsmTarget;
use App\Exports\SemarakRejekiAsmRsmExport;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class MonitoringAsmRsm extends Component
{
    // Filter State
    public $year = 2026;
    public $viewMode = 'accordion'; // 'accordion' | 'table'
    public $filterRegion = '';      // '', 'JAWA_1', 'JAWA_2', 'PULAU', 'SUMATERA_1', 'SUMATERA_2'
    public $filterArea = '';
    public $filterStatus = 'semua'; // 'semua', 'on_track_l2', 'on_track_l1', 'warning', 'hangus'
    public $filterJalur = 'semua';  // 'semua', 'kelompok', 'independen'
    public $search = '';
    public $showRewardClosing = false; // Default false (Fase Monitoring Berjalan - Nominal reward disembunyikan/terkunci s/d closing agar tidak rancu)

    // Tactical Detail Modal State
    public $isDetailModalOpen = false;
    public $selectedAsmId = null;
    public $selectedAsmDetail = null;
    public $selectedCabangList = [];
    public $selectedSpvList = [];
    public $selectedDistributorList = [];
    public $selectedProductBreakdown = [];
    public $selectedTacticalActionPlan = [];

    // Rules & Proposal Modal State
    public $isRulesModalOpen = false;

    // Feedback Alert
    public $alertSuccess = null;
    public $alertError = null;

    public function mount()
    {
        // Initial setup
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
    }

    public function resetFilters()
    {
        $this->filterRegion = '';
        $this->filterArea = '';
        $this->filterStatus = 'semua';
        $this->filterJalur = 'semua';
        $this->search = '';
    }

    public function toggleRewardClosing()
    {
        $this->showRewardClosing = !$this->showRewardClosing;
    }

    public function refreshData()
    {
        Cache::forget("semarak_rejeki_asm_rsm_sellin_{$this->year}");
        $this->alertSuccess = "Data realisasi Sell In ASM & RSM berhasil di-refresh.";
    }

    /**
     * Hitung sisa hari kerja aktif bulan berjalan (Tanpa Hari Minggu)
     */
    public function getRemainingDaysInfo()
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        $evalMonth = ($currentYear == $this->year && in_array($currentMonth, [10, 11, 12])) ? $currentMonth : 11;
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $evalMonth, $this->year);
        $currentDay = ($currentMonth == $evalMonth && $currentYear == $this->year) ? (int)date('j') : 1;

        $totalWorkingDays = 0;
        $currentWorkingDay = 0;
        $remainingWorkingDays = 0;

        for ($d = 1; $d <= $totalDaysInMonth; $d++) {
            $dayOfWeek = (int)date('w', strtotime(sprintf('%04d-%02d-%02d', $this->year, $evalMonth, $d)));
            if ($dayOfWeek !== 0) { // 0 = Minggu
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
     * Query ringkasan data penjualan Sell In dari database (Single Query Cached)
     */
    private function getSellingInSummary(): Collection
    {
        return Cache::remember("semarak_rejeki_asm_rsm_sellin_{$this->year}", 300, function () {
            return DB::table('selling_in')
                ->where('tahun', $this->year)
                ->where('bulan', '>=', "{$this->year}-10-01")
                ->where('bulan', '<=', "{$this->year}-12-31")
                ->whereIn('nama_produk_mapping', ['OKEBIS BISKUIT KELAPA 28', 'OKEBIS CHOCOLATE COOKIES 28'])
                ->select(
                    'rsm',
                    'asm',
                    'cabang',
                    'kd_spv',
                    'nama_spv',
                    'kd_distributor',
                    'nama_distributor',
                    'nama_produk_mapping',
                    'bulan',
                    DB::raw('SUM(ktn_net) as total_ktn'),
                    DB::raw('SUM(value_net) as total_val')
                )
                ->groupBy('rsm', 'asm', 'cabang', 'kd_spv', 'nama_spv', 'kd_distributor', 'nama_distributor', 'nama_produk_mapping', 'bulan')
                ->get();
        });
    }

    /**
     * Kompilasi lengkap seluruh metrik Region (RSM) dan Area (ASM)
     */
    public function compileData(): array
    {
        $salesRaw = $this->getSellingInSummary();
        $daysInfo = $this->getRemainingDaysInfo();

        $regions = SemarakRejekiRsmTarget::with(['asmTargets' => function ($q) {
            $q->where('is_active', true)->orderBy('urutan');
        }])
        ->where('tahun', $this->year)
        ->where('is_active', true)
        ->orderBy('urutan')
        ->get();

        $compiledRegions = [];
        $flatAsmList = [];

        $grandTargetL1Ktn = 0;
        $grandTargetL2Ktn = 0;
        $grandRealKtn = 0;
        $grandRealVal = 0;
        $grandEstimatedRewardPool = 0;

        $countEligibleAsm = 0;
        $countWarningAsm = 0;
        $countHangusAsm = 0;
        $countL2Asm = 0;
        $countL1Asm = 0;

        foreach ($regions as $reg) {
            $regTargetL1Bln = (float)$reg->target_l1_monthly;
            $regTargetL2Bln = (float)$reg->target_l2_monthly;
            $regTargetL1_3Bln = $regTargetL1Bln * 3;
            $regTargetL2_3Bln = $regTargetL2Bln * 3;

            $regOktKtn = 0;
            $regNovKtn = 0;
            $regDesKtn = 0;
            $regTotalKtn = 0;
            $regTotalVal = 0;

            $compiledAsms = [];

            foreach ($reg->asmTargets as $asm) {
                // Filter baris penjualan untuk ASM ini
                $asmRows = $salesRaw->filter(function ($row) use ($asm) {
                    return trim(strtoupper($row->asm)) === trim(strtoupper($asm->asm_db_name));
                });

                $oktKtn = (float)$asmRows->filter(function ($r) {
                    return date('n', strtotime($r->bulan)) == 10;
                })->sum('total_ktn');

                $novKtn = (float)$asmRows->filter(function ($r) {
                    return date('n', strtotime($r->bulan)) == 11;
                })->sum('total_ktn');

                $desKtn = (float)$asmRows->filter(function ($r) {
                    return date('n', strtotime($r->bulan)) == 12;
                })->sum('total_ktn');

                $totalKtn = (float)$asmRows->sum('total_ktn');
                $totalVal = (float)$asmRows->sum('total_val');

                $regOktKtn += $oktKtn;
                $regNovKtn += $novKtn;
                $regDesKtn += $desKtn;
                $regTotalKtn += $totalKtn;
                $regTotalVal += $totalVal;

                // Target ASM
                $asml1Bln = (float)$asm->target_l1_monthly;
                $asml2Bln = (float)$asm->target_l2_monthly;
                $asml1_3Bln = $asml1Bln * 3;
                $asml2_3Bln = $asml2Bln * 3;

                // Milestone Checks (Klausul 5)
                $m1Min70 = $asml1Bln * 0.70;
                $m1Passed = $oktKtn >= $m1Min70;
                $m1Pct = $asml1Bln > 0 ? round(($oktKtn / $asml1Bln) * 100, 1) : 0;

                $m2Min80 = ($asml1Bln * 2) * 0.80;
                $kum2Ktn = $oktKtn + $novKtn;
                $m2Passed = $kum2Ktn >= $m2Min80;
                $m2Pct = ($asml1Bln * 2) > 0 ? round(($kum2Ktn / ($asml1Bln * 2)) * 100, 1) : 0;

                $m3Passed = $totalKtn >= $asml1_3Bln;
                $m3PctL1 = $asml1_3Bln > 0 ? round(($totalKtn / $asml1_3Bln) * 100, 1) : 0;
                $m3PctL2 = $asml2_3Bln > 0 ? round(($totalKtn / $asml2_3Bln) * 100, 1) : 0;

                // Pacing bulanan berjalan (terhadap target 1 bulan)
                $pacingPctL1 = $asml1Bln > 0 ? round(($totalKtn / $asml1Bln) * 100, 1) : 0;
                $pacingPctL2 = $asml2Bln > 0 ? round(($totalKtn / $asml2Bln) * 100, 1) : 0;

                // Hitung GAP Karton terhadap Target L1 & L2 (Positif = Surplus, Negatif = Defisit/Kekurangan)
                $gapL1 = $totalKtn - $asml1Bln;
                $gapL2 = $totalKtn - $asml2Bln;
                $asmHitL1 = ($totalKtn >= $asml1Bln) && $m1Passed;
                $asmHitL2 = ($totalKtn >= $asml2Bln) && $m1Passed;

                $compiledAsms[] = [
                    'id'               => $asm->id,
                    'area_code'        => $asm->area_code,
                    'area_name'        => $asm->area_name,
                    'asm_db_name'      => $asm->asm_db_name,
                    'region_code'      => $reg->region_code,
                    'region_name'      => $reg->region_name,
                    'nomor_proposal'   => $reg->nomor_proposal,
                    'max_ly'           => $asm->max_ly,
                    'target_l1_bln'    => $asml1Bln,
                    'target_l2_bln'    => $asml2Bln,
                    'target_l1_3bln'   => $asml1_3Bln,
                    'target_l2_3bln'   => $asml2_3Bln,
                    'reward_asm_l1'    => (float)$asm->reward_asm_l1,
                    'reward_asm_l2'    => (float)$asm->reward_asm_l2,
                    'okt_ktn'          => $oktKtn,
                    'nov_ktn'          => $novKtn,
                    'des_ktn'          => $desKtn,
                    'kum2_ktn'         => $kum2Ktn,
                    'total_ktn'        => $totalKtn,
                    'total_val'        => $totalVal,
                    'gap_l1'           => $gapL1,
                    'gap_l2'           => $gapL2,
                    'asm_hit_l1'       => $asmHitL1,
                    'asm_hit_l2'       => $asmHitL2,
                    'm1_min_70'        => $m1Min70,
                    'm1_passed'        => $m1Passed,
                    'm1_pct'           => $m1Pct,
                    'm2_min_80'        => $m2Min80,
                    'm2_passed'        => $m2Passed,
                    'm2_pct'           => $m2Pct,
                    'm3_passed'        => $m3Passed,
                    'm3_pct_l1'        => $m3PctL1,
                    'm3_pct_l2'        => $m3PctL2,
                    'pacing_pct_l1'    => $pacingPctL1,
                    'pacing_pct_l2'    => $pacingPctL2,
                ];
            }

            // Hitung status ketercapaian Region
            $regM1Min70 = $regTargetL1Bln * 0.70;
            $regM1Passed = $regOktKtn >= $regM1Min70;
            $regM1Pct = $regTargetL1Bln > 0 ? round(($regOktKtn / $regTargetL1Bln) * 100, 1) : 0;

            $regM2Min80 = ($regTargetL1Bln * 2) * 0.80;
            $regKum2Ktn = $regOktKtn + $regNovKtn;
            $regM2Passed = $regKum2Ktn >= $regM2Min80;

            $regPctL1 = $regTargetL1Bln > 0 ? round(($regTotalKtn / $regTargetL1Bln) * 100, 1) : 0;
            $regPctL2 = $regTargetL2Bln > 0 ? round(($regTotalKtn / $regTargetL2Bln) * 100, 1) : 0;

            $regGapL1 = $regTotalKtn - $regTargetL1Bln;
            $regGapL2 = $regTotalKtn - $regTargetL2Bln;

            // Evaluasi kekompakan seluruh ASM dalam Region:
            $totalAsmsInReg = count($compiledAsms);
            $asmsPassedL1Count = 0;
            $asmsPassedL2Count = 0;

            foreach ($compiledAsms as $cAsm) {
                if ($cAsm['asm_hit_l1']) $asmsPassedL1Count++;
                if ($cAsm['asm_hit_l2']) $asmsPassedL2Count++;
            }

            $allAsmsPassedL1 = ($totalAsmsInReg > 0 && $asmsPassedL1Count === $totalAsmsInReg);

            // SKENARIO 2: EVALUASI LEVEL REGION DULU, BARU PER AREA!
            if (!$regM1Passed) {
                // Region Total Gagal Milestone Bulan I (<70%) -> RSM Hangus, Kelompok Resmi Gugur
                $regTierStatus = 'region_hangus';
                $regStatusLabel = "Milestone Gagal ({$regM1Pct}% < 70%)";
                $regStatusBadge = 'badge-error text-white font-bold';
                $regRewardRsm = 0;
            } else {
                // Region Total LOLOS Milestone Bulan I (>=70%)!
                // Hak RSM Hidup dan Berhak Sesuai Pacing Volume Region:
                if ($allAsmsPassedL1) {
                    // Seluruh area kompak lolos L1! Gerbang L2 terbuka bersama.
                    if ($regTotalKtn >= $regTargetL2Bln) {
                        $regRewardRsm = (float)$reg->reward_rsm_l2;
                        $regTierStatus = 'layer_2';
                        $regStatusLabel = 'Peluang Layer 2 (Kompak)';
                        $regStatusBadge = 'badge-success text-white font-bold';
                    } else {
                        $regRewardRsm = (float)$reg->reward_rsm_l1;
                        $regTierStatus = 'layer_1';
                        $regStatusLabel = 'Peluang Layer 1 (Kompak)';
                        $regStatusBadge = 'badge-info text-white font-bold';
                    }
                } else {
                    // Ada area yang masih mengejar L1: RSM tetap on-track sesuai volume total region!
                    $laggingCount = $totalAsmsInReg - $asmsPassedL1Count;
                    if ($regTotalKtn >= $regTargetL2Bln) {
                        $regRewardRsm = (float)$reg->reward_rsm_l2;
                        $regTierStatus = 'fokus_l1';
                        $regStatusLabel = "Fokus Kunci L1 ({$laggingCount} Area di Zona Kejar)";
                        $regStatusBadge = 'badge-warning text-amber-950 font-bold';
                    } elseif ($regTotalKtn >= $regTargetL1Bln) {
                        $regRewardRsm = (float)$reg->reward_rsm_l1;
                        $regTierStatus = 'fokus_l1';
                        $regStatusLabel = "Fokus Kunci L1 ({$laggingCount} Area di Zona Kejar)";
                        $regStatusBadge = 'badge-warning text-amber-950 font-bold';
                    } else {
                        // Volume region masih menuju L1 tapi milestone 70% aman
                        $regRewardRsm = (float)$reg->reward_rsm_l1;
                        $regTierStatus = 'fokus_l1';
                        $regStatusLabel = "Zona Kejar L1 (Milestone {$regM1Pct}% Aman)";
                        $regStatusBadge = 'badge-info text-white font-bold';
                    }
                }
            }

            // Evaluasi hak & estimasi reward ASM:
            foreach ($compiledAsms as &$asmItem) {
                if (!$asmItem['m1_passed']) {
                    // Gagal batas minimum milestone bulan I (Hukuman ditanggung ASM sendiri)
                    $asmItem['jalur'] = 'Hangus';
                    $asmItem['estimasi_reward'] = 0;
                    $asmItem['hak_reward'] = 0;
                    $asmItem['status_key'] = 'hangus';
                    $asmItem['status_badge'] = 'badge-error text-white font-bold';
                    $asmItem['status_label'] = 'Milestone Gagal (Hangus)';
                    $asmItem['status_desc'] = 'Tidak memenuhi syarat minimal 70% di Bulan I (Oktober). Reward dinyatakan hangus.';
                    $countHangusAsm++;
                } else {
                    // ASM Lolos Milestone Bulan I (>=70%):
                    if ($regTierStatus === 'region_hangus') {
                        // Region gagal milestone -> ASM berprestasi diselamatkan via Jalur Independen (Klausul 7)
                        if ($asmItem['asm_hit_l2']) {
                            $asmItem['jalur'] = 'Independen';
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l2'];
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l2'];
                            $asmItem['status_key'] = 'on_track_l2';
                            $asmItem['status_badge'] = 'badge-success text-white font-bold';
                            $asmItem['status_label'] = 'Peluang L2 (Independen ⭐)';
                            $asmItem['status_desc'] = 'Region gagal milestone, ASM mandiri berhasil menembus target Layer 2 sesuai Klausul 7.';
                            $countL2Asm++;
                            $countEligibleAsm++;
                        } elseif ($asmItem['asm_hit_l1']) {
                            $asmItem['jalur'] = 'Independen';
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['status_key'] = 'on_track_l1';
                            $asmItem['status_badge'] = 'badge-info text-white font-bold';
                            $asmItem['status_label'] = 'Peluang L1 (Independen ⭐)';
                            $asmItem['status_desc'] = 'Region gagal milestone, ASM mandiri berhasil menembus target Layer 1 sesuai Klausul 7.';
                            $countL1Asm++;
                            $countEligibleAsm++;
                        } else {
                            $asmItem['jalur'] = 'Zona Kejar';
                            $asmItem['estimasi_reward'] = 0;
                            $asmItem['hak_reward'] = 0;
                            $asmItem['status_key'] = 'warning';
                            $asmItem['status_badge'] = 'badge-warning text-amber-950 font-bold';
                            $asmItem['status_label'] = 'Zona Kejar Target';
                            $asmItem['status_desc'] = 'Realisasi masih di bawah target Layer 1.';
                            $countWarningAsm++;
                        }
                    } elseif ($regTierStatus === 'layer_2') {
                        // Region Lolos KELOMPOK Layer 2 (Seluruh Area Kompak Lolos L1 & Region Tembus L2)
                        $asmItem['jalur'] = 'Kelompok';
                        if ($asmItem['asm_hit_l2']) {
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l2'];
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l2'];
                            $asmItem['status_key'] = 'on_track_l2';
                            $asmItem['status_badge'] = 'badge-success text-white font-bold';
                            $asmItem['status_label'] = 'Peluang Layer 2 (Kelompok)';
                            $asmItem['status_desc'] = 'Region dan seluruh ASM kompak menembus target Layer 2.';
                            $countL2Asm++;
                            $countEligibleAsm++;
                        } else {
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['status_key'] = 'on_track_l1';
                            $asmItem['status_badge'] = 'badge-info text-white font-bold';
                            $asmItem['status_label'] = 'Peluang Layer 1 (Kelompok)';
                            $asmItem['status_desc'] = 'Region Layer 2, pencapaian area di Layer 1.';
                            $countL1Asm++;
                            $countEligibleAsm++;
                        }
                    } elseif ($regTierStatus === 'layer_1') {
                        // Region Lolos KELOMPOK Layer 1 (Seluruh Area Kompak L1, Region di Tier L1)
                        $asmItem['jalur'] = 'Kelompok';
                        if ($asmItem['asm_hit_l2']) {
                            // Capped to Layer 1 karena status kelompok region adalah Layer 1 ("Kelompok wajib sama")
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['status_key'] = 'on_track_l1';
                            $asmItem['status_badge'] = 'badge-info text-white font-bold';
                            $asmItem['status_label'] = 'Peluang L1 (Cap Reg 🔒)';
                            $asmItem['status_desc'] = 'Volume mencapai Layer 2, namun estimasi reward dicapping ke Layer 1 mengikuti posisi Region Kelompok.';
                            $countL1Asm++;
                            $countEligibleAsm++;
                        } else {
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['status_key'] = 'on_track_l1';
                            $asmItem['status_badge'] = 'badge-info text-white font-bold';
                            $asmItem['status_label'] = 'Peluang Layer 1 (Kelompok)';
                            $asmItem['status_desc'] = 'Memenuhi target Layer 1 selaras dengan Region.';
                            $countL1Asm++;
                            $countEligibleAsm++;
                        }
                    } else {
                        // fokus_l1: Region Lolos Milestone & Kelompok Berjalan ("Fokus ke L1!")
                        $asmItem['jalur'] = 'Kelompok';
                        if ($asmItem['asm_hit_l1']) {
                            // ASM sudah capai target L1 atau lebih: Statusnya Aman di L1 & Bantu Rekan!
                            // Tidak dimunculkan L2 dulu agar tim fokus mengamankan L1 bersama
                            $asmItem['estimasi_reward'] = $asmItem['reward_asm_l1']; // Kunci di L1 dulu
                            $asmItem['hak_reward'] = $asmItem['reward_asm_l1'];
                            $asmItem['status_key'] = 'on_track_l1';
                            $asmItem['status_badge'] = 'badge-info text-white font-bold';
                            $asmItem['status_label'] = 'Aman di L1 (Kawal Tim L1)';
                            $asmItem['status_desc'] = 'Target L1 pribadi sudah aman. Kawal rekan sekelompok agar lolos L1 untuk membuka gerbang Layer 2.';
                            $countL1Asm++;
                            $countEligibleAsm++;
                        } else {
                            // Masih di zona kejar L1
                            $asmItem['estimasi_reward'] = 0;
                            $asmItem['hak_reward'] = 0;
                            $asmItem['status_key'] = 'warning';
                            $asmItem['status_badge'] = 'badge-warning text-amber-950 font-bold';
                            $asmItem['status_label'] = 'Zona Kejar L1';
                            $asmItem['status_desc'] = 'Masih mengejar target Layer 1. Segera penuhi kekurangan karton demi kekompakan tim.';
                            $countWarningAsm++;
                        }
                    }
                }

                $grandEstimatedRewardPool += $asmItem['estimasi_reward'];
                $flatAsmList[] = $asmItem;
            }
            unset($asmItem);

            $grandTargetL1Ktn += $regTargetL1Bln;
            $grandTargetL2Ktn += $regTargetL2Bln;
            $grandRealKtn += $regTotalKtn;
            $grandRealVal += $regTotalVal;
            $grandEstimatedRewardPool += $regRewardRsm;

            $compiledRegions[] = [
                'id'                   => $reg->id,
                'region_code'          => $reg->region_code,
                'region_name'          => $reg->region_name,
                'rsm_db_pattern'       => $reg->rsm_db_pattern,
                'nomor_proposal'       => $reg->nomor_proposal,
                'max_ly'               => $reg->max_ly,
                'target_l1_bln'        => $regTargetL1Bln,
                'target_l2_bln'        => $regTargetL2Bln,
                'target_l1_3bln'       => $regTargetL1_3Bln,
                'target_l2_3bln'       => $regTargetL2_3Bln,
                'reward_rsm_l1'        => (float)$reg->reward_rsm_l1,
                'reward_rsm_l2'        => (float)$reg->reward_rsm_l2,
                'reward_rsm_estimasi'  => $regRewardRsm,
                'reward_rsm_hak'       => $regRewardRsm,
                'okt_ktn'              => $regOktKtn,
                'nov_ktn'              => $regNovKtn,
                'des_ktn'              => $regDesKtn,
                'kum2_ktn'             => $regKum2Ktn,
                'total_ktn'            => $regTotalKtn,
                'total_val'            => $regTotalVal,
                'gap_l1'               => $regGapL1,
                'gap_l2'               => $regGapL2,
                'asms_passed_l1_count' => $asmsPassedL1Count,
                'asms_passed_l2_count' => $asmsPassedL2Count,
                'all_asms_passed_l1'   => $allAsmsPassedL1,
                'm1_min_70'            => $regM1Min70,
                'm1_passed'            => $regM1Passed,
                'm1_pct'               => $regM1Pct,
                'm2_min_80'            => $regM2Min80,
                'm2_passed'            => $regM2Passed,
                'pct_l1'               => $regPctL1,
                'pct_l2'               => $regPctL2,
                'tier_status'          => $regTierStatus,
                'status_label'         => $regStatusLabel,
                'status_badge'         => $regStatusBadge,
                'asms'                 => $compiledAsms,
            ];
        }

        $overallPctL1 = $grandTargetL1Ktn > 0 ? round(($grandRealKtn / $grandTargetL1Ktn) * 100, 1) : 0;
        $overallPctL2 = $grandTargetL2Ktn > 0 ? round(($grandRealKtn / $grandTargetL2Ktn) * 100, 1) : 0;

        return [
            'regions'                  => $compiledRegions,
            'flat_asms'                => $flatAsmList,
            'days_info'                => $daysInfo,
            'grand_target_l1_ktn'      => $grandTargetL1Ktn,
            'grand_target_l2_ktn'      => $grandTargetL2Ktn,
            'grand_real_ktn'           => $grandRealKtn,
            'grand_gap_l1'             => $grandRealKtn - $grandTargetL1Ktn,
            'grand_gap_l2'             => $grandRealKtn - $grandTargetL2Ktn,
            'grand_real_val'           => $grandRealVal,
            'grand_real_val_fmt'       => number_format($grandRealVal / 1000000000, 2, ',', '.') . ' M',
            'grand_estimated_pool'     => $grandEstimatedRewardPool,
            'grand_estimated_pool_fmt' => 'Rp ' . number_format($grandEstimatedRewardPool, 0, ',', '.'),
            'overall_pct_l1'           => $overallPctL1,
            'overall_pct_l2'           => $overallPctL2,
            'total_asm_count'          => count($flatAsmList),
            'eligible_asm_count'       => $countEligibleAsm,
            'l2_asm_count'             => $countL2Asm,
            'l1_asm_count'             => $countL1Asm,
            'warning_asm_count'        => $countWarningAsm,
            'hangus_asm_count'         => $countHangusAsm,
        ];
    }

    /**
     * Buka Modal Pusat Kendali Taktis untuk ASM Tertentu
     */
    public function openDetailModal($asmId)
    {
        $compiled = $this->compileData();
        $targetAsm = null;

        foreach ($compiled['flat_asms'] as $item) {
            if ($item['id'] == $asmId) {
                $targetAsm = $item;
                break;
            }
        }

        if (!$targetAsm) {
            $this->alertError = "Data ASM tidak ditemukan.";
            return;
        }

        $salesRaw = $this->getSellingInSummary();
        $daysInfo = $compiled['days_info'];

        // Ambil baris detail untuk ASM ini
        $asmRows = $salesRaw->filter(function ($row) use ($targetAsm) {
            return trim(strtoupper($row->asm)) === trim(strtoupper($targetAsm['asm_db_name']));
        });

        // 1. Rollup Per Cabang
        $cabangMap = [];
        foreach ($asmRows as $r) {
            $cName = $r->cabang ?: 'CABANG LAIN';
            if (!isset($cabangMap[$cName])) {
                $cabangMap[$cName] = [
                    'cabang'        => $cName,
                    'spv_names'     => [],
                    'dist_names'    => [],
                    'okt_ktn'       => 0,
                    'nov_ktn'       => 0,
                    'des_ktn'       => 0,
                    'total_ktn'     => 0,
                    'total_val'     => 0,
                ];
            }
            $blnNum = (int)date('n', strtotime($r->bulan));
            if ($blnNum == 10) $cabangMap[$cName]['okt_ktn'] += (float)$r->total_ktn;
            if ($blnNum == 11) $cabangMap[$cName]['nov_ktn'] += (float)$r->total_ktn;
            if ($blnNum == 12) $cabangMap[$cName]['des_ktn'] += (float)$r->total_ktn;

            $cabangMap[$cName]['total_ktn'] += (float)$r->total_ktn;
            $cabangMap[$cName]['total_val'] += (float)$r->total_val;

            if ($r->nama_spv && !in_array($r->nama_spv, $cabangMap[$cName]['spv_names'])) {
                $cabangMap[$cName]['spv_names'][] = $r->nama_spv;
            }
            if ($r->nama_distributor && !in_array($r->nama_distributor, $cabangMap[$cName]['dist_names'])) {
                $cabangMap[$cName]['dist_names'][] = $r->nama_distributor;
            }
        }

        // Urutkan cabang berdasarkan total penjualan karton tertinggi
        usort($cabangMap, function ($a, $b) {
            return $b['total_ktn'] <=> $a['total_ktn'];
        });

        $totalAsmKtn = max(0.01, $targetAsm['total_ktn']);
        foreach ($cabangMap as &$cb) {
            $cb['pct_contribution'] = round(($cb['total_ktn'] / $totalAsmKtn) * 100, 1);
            if ($cb['pct_contribution'] >= 25) {
                $cb['status_badge'] = 'badge-success text-white';
                $cb['status_label'] = 'Pareto Penopang ⭐';
            } elseif ($cb['pct_contribution'] >= 12) {
                $cb['status_badge'] = 'badge-info text-white';
                $cb['status_label'] = 'Solid Contributor 🟢';
            } elseif ($cb['pct_contribution'] >= 5) {
                $cb['status_badge'] = 'badge-warning text-amber-950';
                $cb['status_label'] = 'Aktif / Perlu Akselerasi ⚡';
            } else {
                $cb['status_badge'] = 'badge-error text-white';
                $cb['status_label'] = 'Rendah / Kritis 🔴';
            }
        }
        unset($cb);

        // 2. Rollup Per SPV
        $spvMap = [];
        foreach ($asmRows as $r) {
            $spvKey = $r->kd_spv ?: 'NO_SPV';
            if (!isset($spvMap[$spvKey])) {
                $spvMap[$spvKey] = [
                    'kd_spv'        => $r->kd_spv,
                    'nama_spv'      => $r->nama_spv ?: 'SPV Non-Terdaftar',
                    'cabang_list'   => [],
                    'total_ktn'     => 0,
                    'total_val'     => 0,
                ];
            }
            $spvMap[$spvKey]['total_ktn'] += (float)$r->total_ktn;
            $spvMap[$spvKey]['total_val'] += (float)$r->total_val;
            if ($r->cabang && !in_array($r->cabang, $spvMap[$spvKey]['cabang_list'])) {
                $spvMap[$spvKey]['cabang_list'][] = $r->cabang;
            }
        }
        usort($spvMap, function ($a, $b) {
            return $b['total_ktn'] <=> $a['total_ktn'];
        });
        foreach ($spvMap as &$sp) {
            $sp['pct_contribution'] = round(($sp['total_ktn'] / $totalAsmKtn) * 100, 1);
        }
        unset($sp);

        // 3. Rollup Per Distributor (Depo)
        $distMap = [];
        foreach ($asmRows as $r) {
            $dKey = $r->kd_distributor ?: 'NO_DIST';
            if (!isset($distMap[$dKey])) {
                $distMap[$dKey] = [
                    'kd_distributor'   => $r->kd_distributor,
                    'nama_distributor' => $r->nama_distributor ?: 'Distributor Non-Terdaftar',
                    'cabang'           => $r->cabang,
                    'okt_ktn'          => 0,
                    'nov_ktn'          => 0,
                    'des_ktn'          => 0,
                    'total_ktn'        => 0,
                    'total_val'        => 0,
                ];
            }
            $blnNum = (int)date('n', strtotime($r->bulan));
            if ($blnNum == 10) $distMap[$dKey]['okt_ktn'] += (float)$r->total_ktn;
            if ($blnNum == 11) $distMap[$dKey]['nov_ktn'] += (float)$r->total_ktn;
            if ($blnNum == 12) $distMap[$dKey]['des_ktn'] += (float)$r->total_ktn;

            $distMap[$dKey]['total_ktn'] += (float)$r->total_ktn;
            $distMap[$dKey]['total_val'] += (float)$r->total_val;
        }
        usort($distMap, function ($a, $b) {
            return $b['total_ktn'] <=> $a['total_ktn'];
        });
        foreach ($distMap as &$dst) {
            $dst['pct_contribution'] = round(($dst['total_ktn'] / $totalAsmKtn) * 100, 1);
        }
        unset($dst);

        // 4. Breakdown SKU Produk
        $prodKelapa = (float)$asmRows->filter(function ($r) {
            return str_contains($r->nama_produk_mapping, 'KELAPA');
        })->sum('total_ktn');
        $prodChoco = (float)$asmRows->filter(function ($r) {
            return str_contains($r->nama_produk_mapping, 'CHOCOLATE') || str_contains($r->nama_produk_mapping, 'COOKIES');
        })->sum('total_ktn');

        $prodBreakdown = [
            'kelapa_ktn' => $prodKelapa,
            'kelapa_pct' => $totalAsmKtn > 0 ? round(($prodKelapa / $totalAsmKtn) * 100, 1) : 0,
            'choco_ktn'  => $prodChoco,
            'choco_pct'  => $totalAsmKtn > 0 ? round(($prodChoco / $totalAsmKtn) * 100, 1) : 0,
        ];

        // 5. Kalkulator Run-Rate Sisa Target Harian
        $remDays = $daysInfo['remaining_days'];
        $gapL1 = max(0, $targetAsm['target_l1_bln'] - $targetAsm['total_ktn']);
        $gapL2 = max(0, $targetAsm['target_l2_bln'] - $targetAsm['total_ktn']);

        $dailyNeededL1 = $remDays > 0 ? round($gapL1 / $remDays, 1) : 0;
        $dailyNeededL2 = $remDays > 0 ? round($gapL2 / $remDays, 1) : 0;

        $cabangCount = max(1, count($cabangMap));
        $dailyPerCabangL1 = round($dailyNeededL1 / $cabangCount, 1);
        $dailyPerCabangL2 = round($dailyNeededL2 / $cabangCount, 1);

        // 6. Action Plan Builder
        $topCabang = $cabangMap[0] ?? null;
        $underCabangs = array_filter($cabangMap, function ($c) {
            return $c['pct_contribution'] < 10;
        });
        $underCabangNames = implode(', ', array_slice(array_column($underCabangs, 'cabang'), 0, 3));

        $actionSteps = [];
        if (($targetAsm['status_label'] ?? '') === 'Aman di L1 (Kawal Tim L1)') {
            $actionSteps[] = [
                'title' => 'Misi Solidaritas: Kawal Rekan Se-Region Lolos Layer 1! 🤝',
                'desc'  => "Volume penjualan area {$targetAsm['area_name']} ({$targetAsm['total_ktn']} Ktn) sudah aman di Layer 1. Sesuai prinsip kekompakan tim, peluang reward Layer 2 baru akan resmi dibuka jika SELURUH area di region ini kompak lolos Layer 1. Bantu dan semangati rekan satu region Anda agar kelompok lolos L1!",
                'badge' => 'Kekompakan Tim 🛡️',
                'theme' => 'info',
            ];
        }

        if ($gapL2 <= 0) {
            $actionSteps[] = [
                'title' => 'Pertahankan Realisasi Penjualan!',
                'desc'  => "Area {$targetAsm['area_name']} telah melampaui target volume ({$targetAsm['target_l2_bln']} Ktn). Pastikan tidak terjadi retur faktur penjualan dan kawal stok di cabang-cabang utama.",
                'badge' => 'Volume Aman 🏆',
                'theme' => 'success',
            ];
        } else {
            if ($gapL1 > 0) {
                $actionSteps[] = [
                    'title' => "Prioritas 1: Kunci Batas Layer 1 (Kurang " . number_format($gapL1, 0, ',', '.') . " Ktn)",
                    'desc'  => "Dibutuhkan percepatan ~{$dailyNeededL1} Ktn/hari kerja di sisa {$remDays} hari aktif bulan ini (rata-rata ~{$dailyPerCabangL1} Ktn/hari per cabang).",
                    'badge' => 'Kejar Layer 1 🎯',
                    'theme' => 'warning',
                ];
            } else {
                $actionSteps[] = [
                    'title' => "Target Layer 1 Sudah Terlampaui!",
                    'desc'  => "Amankan peluang naik tingkat ke Layer 2. Kekurangan hanya " . number_format($gapL2, 0, ',', '.') . " Ktn (~{$dailyNeededL2} Ktn/hari kerja).",
                    'badge' => 'Akselerasi Layer 2 🚀',
                    'theme' => 'info',
                ];
            }
        }

        if (!empty($underCabangNames)) {
            $actionSteps[] = [
                'title' => 'Optimalisasi Cabang yang Masih Tertinggal',
                'desc'  => "Pencapaian saat ini sangat bertumpu pada " . ($topCabang ? $topCabang['cabang'] : 'cabang utama') . ". Segera hubungi SPV di cabang {$underCabangNames} untuk menjemput repeat order.",
                'badge' => 'Pemerataan Beban Cabang 🏢',
                'theme' => 'primary',
            ];
        }

        if ($prodBreakdown['choco_pct'] < 30) {
            $actionSteps[] = [
                'title' => 'Dorong Penetrasi Okebis Chocolate Cookies 28',
                'desc'  => "Porsi penjualan Chocolate Cookies baru {$prodBreakdown['choco_pct']}%. Manfaatkan promo kelipatan varian untuk menggenjot volume karton di grosir dan pareto.",
                'badge' => 'Product Mix Upselling 🍪',
                'theme' => 'secondary',
            ];
        }

        $this->selectedAsmDetail = array_merge($targetAsm, [
            'gap_l1'              => $gapL1,
            'gap_l2'              => $gapL2,
            'daily_needed_l1'     => $dailyNeededL1,
            'daily_needed_l2'     => $dailyNeededL2,
            'daily_per_cabang_l1' => $dailyPerCabangL1,
            'daily_per_cabang_l2' => $dailyPerCabangL2,
            'cabang_count'        => $cabangCount,
            'dist_count'          => count($distMap),
            'spv_count'           => count($spvMap),
        ]);

        $this->selectedCabangList = $cabangMap;
        $this->selectedSpvList = $spvMap;
        $this->selectedDistributorList = $distMap;
        $this->selectedProductBreakdown = $prodBreakdown;
        $this->selectedTacticalActionPlan = $actionSteps;
        $this->selectedAsmId = $asmId;
        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedAsmId = null;
        $this->selectedAsmDetail = null;
        $this->selectedCabangList = [];
        $this->selectedSpvList = [];
        $this->selectedDistributorList = [];
        $this->selectedProductBreakdown = [];
        $this->selectedTacticalActionPlan = [];
    }

    public function openRulesModal()
    {
        $this->isRulesModalOpen = true;
    }

    public function closeRulesModal()
    {
        $this->isRulesModalOpen = false;
    }

    /**
     * Export data ke Excel
     */
    public function exportExcel()
    {
        $data = $this->compileData();
        return Excel::download(
            new SemarakRejekiAsmRsmExport($data, $this->year),
            "Semarak_Rejeki_Reward_ASM_RSM_{$this->year}.xlsx"
        );
    }

    public function render()
    {
        $compiled = $this->compileData();

        // Terapkan filter pada tampilan
        $filteredRegions = $compiled['regions'];
        $filteredAsms = $compiled['flat_asms'];

        if ($this->filterRegion) {
            $filteredRegions = array_filter($filteredRegions, function ($r) {
                return $r['region_code'] === $this->filterRegion;
            });
            $filteredAsms = array_filter($filteredAsms, function ($a) {
                return $a['region_code'] === $this->filterRegion;
            });
        }

        if ($this->filterArea) {
            $filteredAsms = array_filter($filteredAsms, function ($a) {
                return $a['area_code'] === $this->filterArea;
            });
        }

        if ($this->filterStatus !== 'semua') {
            $filteredAsms = array_filter($filteredAsms, function ($a) {
                return $a['status_key'] === $this->filterStatus;
            });
        }

        if ($this->filterJalur !== 'semua') {
            $filteredAsms = array_filter($filteredAsms, function ($a) {
                return strtolower($a['jalur']) === strtolower($this->filterJalur);
            });
        }

        if ($this->search) {
            $term = strtolower($this->search);
            $filteredAsms = array_filter($filteredAsms, function ($a) use ($term) {
                return str_contains(strtolower($a['area_name']), $term)
                    || str_contains(strtolower($a['asm_db_name']), $term)
                    || str_contains(strtolower($a['region_name']), $term);
            });
        }

        // Terapkan filter anak ASM pada tampilan hierarki accordion agar sinkron dengan flat table
        if ($this->filterArea || $this->filterStatus !== 'semua' || $this->filterJalur !== 'semua' || $this->search) {
            foreach ($filteredRegions as $rKey => &$rVal) {
                $rVal['asms'] = array_values(array_filter($rVal['asms'], function ($a) {
                    $matchArea = empty($this->filterArea) || ($a['area_code'] === $this->filterArea);
                    $matchStatus = ($this->filterStatus === 'semua') || ($a['status_key'] === $this->filterStatus);
                    $matchJalur = ($this->filterJalur === 'semua') || (strtolower($a['jalur']) === strtolower($this->filterJalur));
                    $matchSearch = true;
                    if ($this->search) {
                        $term = strtolower($this->search);
                        $matchSearch = str_contains(strtolower($a['area_name']), $term)
                            || str_contains(strtolower($a['asm_db_name']), $term)
                            || str_contains(strtolower($a['region_name']), $term);
                    }
                    return $matchArea && $matchStatus && $matchJalur && $matchSearch;
                }));
            }
            unset($rVal);

            // Sembunyikan region jika seluruh area di dalamnya tidak memenuhi filter
            $filteredRegions = array_values(array_filter($filteredRegions, function ($r) {
                return !empty($r['asms']);
            }));
        }

        return view('livewire.semarak-rejeki.monitoring-asm-rsm', [
            'compiled'                   => $compiled,
            'regions'                    => $filteredRegions,
            'flatAsms'                   => $filteredAsms,
            'daysInfo'                   => $compiled['days_info'],
            'year'                       => $this->year,
            'viewMode'                   => $this->viewMode,
            'showRewardClosing'          => $this->showRewardClosing,
            'filterRegion'               => $this->filterRegion,
            'filterArea'                 => $this->filterArea,
            'filterStatus'               => $this->filterStatus,
            'filterJalur'                => $this->filterJalur,
            'search'                     => $this->search,
            'alertSuccess'               => $this->alertSuccess,
            'alertError'                 => $this->alertError,
            'isDetailModalOpen'          => $this->isDetailModalOpen,
            'selectedAsmId'              => $this->selectedAsmId,
            'selectedAsmDetail'          => $this->selectedAsmDetail,
            'selectedCabangList'         => $this->selectedCabangList,
            'selectedSpvList'            => $this->selectedSpvList,
            'selectedDistributorList'    => $this->selectedDistributorList,
            'selectedProductBreakdown'   => $this->selectedProductBreakdown,
            'selectedTacticalActionPlan' => $this->selectedTacticalActionPlan,
            'isRulesModalOpen'           => $this->isRulesModalOpen,
        ]);
    }
}
