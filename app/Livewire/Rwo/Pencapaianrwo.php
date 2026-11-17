<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use App\Traits\RwoQueryBuilder;
use App\Models\RemarkListPotensiRwo;
use App\Models\StoreRemarkChat;

class Pencapaianrwo extends Component
{
    use WithPagination, EnforcesMenuPermissions, RwoQueryBuilder;

    protected string $menuRoute = 'rwo.listpotensirwo';

    // Filters
    public $search = '';
    // Applied Filters (used for actual queries)
    public $appliedKuartal = '';
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSupervisor = '';
    public $appliedDistributor = '';

    public $appliedStatusProgress = 'Semua';
    public $appliedStatusSkb = 'Semua';
    public $appliedStatusData = 'Semua';
    public $appliedStatusReward = 'Semua';
    public $appliedStatusTransaksi = 'Semua';

    // Status Filters
    public $statusProgress = 'Semua';
    public $statusSkb = 'Semua';
    public $statusData = 'Semua';
    public $statusReward = 'Semua';
    public $statusTransaksi = 'Semua';

    protected $listeners = ['apply-rwo-filter' => 'applyModalFilter'];

    public $sortField = 'wilayah';
    public $sortDirection = 'desc';

    // Modals
    public $selectedCustomerCode = null;
    public $selectedDistributorCode = null;
    public $isDetailModalOpen = false;
    public $remarkKhusus = '';
    protected $cachedSelectedStore = null;
    
    // Bubble chat properties
    public $newChatMessage = '';
    public $chatHistories = [];
    
    // Yearly history properties
    public $yearlyHistory = [];
    public $maxYearlyValue = 0;

    public function getSelectedStoreProperty()
    {
        if (!$this->selectedCustomerCode || !$this->selectedDistributorCode) {
            return null;
        }

        if ($this->cachedSelectedStore === null) {
            $this->cachedSelectedStore = $this->getStoreQuery(
                (string)$this->appliedKuartal,
                true,
                $this->selectedCustomerCode,
                $this->selectedDistributorCode
            )
                ->where('l.customer_code', $this->selectedCustomerCode)
                ->where('l.distributor_code', $this->selectedDistributorCode)
                ->where('l.kuartal', $this->appliedKuartal)
                ->first();
        }

        return $this->cachedSelectedStore;
    }

    public function getSalesMomentumAnalysisProperty()
    {
        $store = $this->selectedStore;
        if (!$store) {
            return null;
        }

        $avg = (float)($store->avg_transaction ?? 0);
        $lastValue = (float)($store->last_transaction_value ?? 0);
        $lastDate = $store->last_transaction_date;

        $currentMonthTurnover = 0;
        $currentMonthLabel = '';
        if (!empty($this->yearlyHistory)) {
            $latestYearly = end($this->yearlyHistory);
            $currentMonthTurnover = (float)($latestYearly['value'] ?? 0);
            $currentMonthLabel = $latestYearly['full_label'] ?? '';
        }

        if ($currentMonthTurnover > 0) {
            $evalValue = $currentMonthTurnover;
            $evalPeriod = $currentMonthLabel ?: 'Bulan Berjalan';
            $isCurrentMonth = true;
        } elseif ($lastValue > 0 && $lastDate) {
            $evalValue = $lastValue;
            $evalPeriod = \Carbon\Carbon::parse($lastDate)->translatedFormat('F Y');
            $isCurrentMonth = false;
        } else {
            $evalValue = 0;
            $evalPeriod = '-';
            $isCurrentMonth = false;
        }

        if ($avg <= 0) {
            return [
                'status' => 'NO_DATA',
                'label' => 'BELUM ADA BASELINE',
                'rangeCategory' => '-',
                'badgeClass' => 'badge-ghost',
                'ratio' => 0,
                'evalValue' => $evalValue,
                'avgValue' => 0,
                'evalPeriod' => $evalPeriod,
                'isCurrentMonth' => $isCurrentMonth,
                'diagnosis' => 'Belum ada data transaksi aktif yang mencukupi untuk menghitung rata-rata baseline toko.',
                'action' => 'Dorong order perdana untuk membangun histori transaksi dan pola belanja toko.'
            ];
        }

        $ratio = ($evalValue / $avg) * 100;

        if ($ratio < 100) {
            $status = 'UNDER';
            $label = 'UNDER (< 100%)';
            $rangeCategory = '< 100%';
            $badgeClass = 'badge-error text-white';
            $diffPercent = number_format(100 - $ratio, 1, ',', '.');
            $diagnosis = "Performa transaksi berada di bawah rata-rata historis (drop {$diffPercent}% dari baseline).";
            $action = 'Prioritaskan audit stok fisik di toko, cek potensi retur, barang slow-moving, atau adanya penetrasi produk kompetitor.';
        } elseif ($ratio <= 120) {
            $status = 'NORMAL';
            $label = 'NORMAL (100% - 120%)';
            $rangeCategory = '100% - 120%';
            $badgeClass = 'badge-success text-white';
            $diagnosis = 'Pola order konsisten dan sehat sesuai kapasitas normal toko (' . number_format($ratio, 1, ',', '.') . '% dari baseline).';
            $action = 'Pertahankan ritme kunjungan dan order rutin. Jaga ketersediaan stok produk pareto dan kelancaran pengiriman.';
        } elseif ($ratio <= 170) {
            $status = 'HIGH';
            $label = 'HIGH GROWTH (120% - 170%)';
            $rangeCategory = '120% - 170%';
            $badgeClass = 'badge-info text-white';
            $growthPercent = number_format($ratio - 100, 1, ',', '.');
            $diagnosis = "Terjadi peningkatan pembelian yang kuat (+{$growthPercent}% di atas rata-rata historis).";
            $action = 'Toko sedang bertumbuh. Peluang untuk upsell varian produk baru dan pertimbangkan kenaikan target kuartal berikutnya.';
        } else {
            $status = 'OVER';
            $label = 'OVER / SPIKE (> 170%)';
            $rangeCategory = '> 170%';
            $badgeClass = 'badge-warning text-white';
            $spikePercent = number_format($ratio - 100, 1, ',', '.');
            $diagnosis = "Terjadi lonjakan order ekstrem (+{$spikePercent}% di atas rata-rata historis).";
            $action = 'Waspadai penimbunan stok spekulatif (hoarding promo), pantau kelancaran sell-out ke konsumen, dan monitor risiko kredit/retur.';
        }

        return [
            'status' => $status,
            'label' => $label,
            'rangeCategory' => $rangeCategory,
            'badgeClass' => $badgeClass,
            'ratio' => $ratio,
            'evalValue' => $evalValue,
            'avgValue' => $avg,
            'evalPeriod' => $evalPeriod,
            'isCurrentMonth' => $isCurrentMonth,
            'diagnosis' => $diagnosis,
            'action' => $action
        ];
    }

    public function getStockVerificationProperty()
    {
        $store = $this->selectedStore;
        if (!$store) {
            return null;
        }

        $avg = (float)($store->avg_transaction ?? 0);
        $lastValue = (float)($store->last_transaction_value ?? 0);
        $lastDate = $store->last_transaction_date;

        // Check current month turnover in yearlyHistory
        $currentMonthTurnover = 0;
        $currentMonthLabel = '';
        if (!empty($this->yearlyHistory)) {
            $latestYearly = end($this->yearlyHistory);
            $currentMonthTurnover = (float)($latestYearly['value'] ?? 0);
            $currentMonthLabel = $latestYearly['full_label'] ?? '';
        }

        $now = \Carbon\Carbon::now()->startOfMonth();
        $lastMonth = $lastDate ? \Carbon\Carbon::parse($lastDate)->startOfMonth() : null;
        $monthsElapsed = $lastMonth ? (int)$lastMonth->diffInMonths($now) : 99;

        if ($currentMonthTurnover > 0) {
            $monthsElapsed = 0;
            $evalPeriod = $currentMonthLabel ?: 'Bulan Berjalan';
            $isCurrentMonth = true;
            $evalValue = $currentMonthTurnover;
        } elseif ($lastValue > 0 && $lastDate) {
            $evalPeriod = \Carbon\Carbon::parse($lastDate)->translatedFormat('F Y');
            $isCurrentMonth = false;
            $evalValue = $lastValue;
        } else {
            $evalPeriod = '-';
            $isCurrentMonth = false;
            $evalValue = 0;
        }

        // Consistency metrics
        $totalMonths = count($this->yearlyHistory);
        $activeMonths = 0;
        $consecutiveZero = 0;
        $countingZero = true;
        foreach (array_reverse($this->yearlyHistory) as $m) {
            if ((float)$m['value'] > 0) {
                $activeMonths++;
                $countingZero = false;
            } else {
                if ($countingZero) {
                    $consecutiveZero++;
                }
            }
        }
        $consistencyRate = $totalMonths > 0 ? round(($activeMonths / $totalMonths) * 100, 1) : 0;

        if ($consecutiveZero === 0) {
            $dormancyBadge = 'badge-success text-white';
            $dormancyText = '🟢 Aktif Bulan Ini';
        } elseif ($consecutiveZero === 1) {
            $dormancyBadge = 'badge-warning text-white';
            $dormancyText = '⚠️ 1 Bulan Belum Order';
        } else {
            $dormancyBadge = 'badge-error text-white';
            $dormancyText = "🚨 Alarm Dorman: {$consecutiveZero} Bln Tanpa Order";
        }

        $monthsCoverage = $avg > 0 ? round($lastValue / $avg, 1) : 0;
        $theoreticalBalance = max(0, $lastValue - ($avg * $monthsElapsed));

        if ($avg <= 0) {
            return [
                'status' => 'NO_DATA',
                'label' => 'BELUM ADA DATA BASELINE',
                'badgeClass' => 'badge-ghost',
                'borderClass' => 'border-base-300',
                'monthsCoverage' => 0,
                'monthsElapsed' => $monthsElapsed,
                'theoreticalBalance' => 0,
                'evalValue' => $evalValue,
                'avgValue' => 0,
                'evalPeriod' => $evalPeriod,
                'isCurrentMonth' => $isCurrentMonth,
                'consistencyRate' => $consistencyRate,
                'activeMonths' => $activeMonths,
                'totalMonths' => $totalMonths,
                'dormancyText' => $dormancyText,
                'dormancyBadge' => $dormancyBadge,
                'diagnosis' => 'Belum ada data transaksi aktif di 2026 untuk menghitung rata-rata konsumsi normal toko.',
                'action' => 'Dorong order perdana untuk membangun histori transaksi dan pola belanja toko.'
            ];
        }

        if ($currentMonthTurnover > 0 || $monthsElapsed === 0) {
            $status = 'ACTIVE';
            $label = '🟢 TRANSAKSI BERJALAN AKTIF';
            $badgeClass = 'badge-success text-white';
            $borderClass = 'border-success/30';
            $diagnosis = "Toko telah bertransaksi pada bulan berjalan senilai Rp " . number_format($currentMonthTurnover, 0, ',', '.') . " (" . round(($currentMonthTurnover / $avg) * 100, 1) . "% dari rata-rata). Siklus pasokan berjalan normal.";
            $action = 'Siklus pasokan lancar. Jaga pemenuhan order tepat waktu dan pastikan ketersediaan stok display di toko.';
        } elseif ($monthsCoverage >= 1.5 && $monthsElapsed <= ceil($monthsCoverage)) {
            $status = 'BUFFER_SAFE';
            $label = '🟢 CADANGAN STOK MEMADAI';
            $badgeClass = 'badge-success text-white';
            $borderClass = 'border-success/30';
            $diagnosis = "Pembelian terakhir ({$evalPeriod}) senilai Rp " . number_format($lastValue, 0, ',', '.') . " mencakup {$monthsCoverage} bulan konsumsi normal. Estimasi cadangan stok toko saat ini masih tersedia ~Rp " . number_format($theoreticalBalance, 0, ',', '.') . ".";
            $action = 'Toko memiliki ketahanan stok yang cukup. Fokus bantu percepatan sell-out ke konsumen dan monitor perputaran barang sebelum dorong PO baru.';
        } elseif ($monthsElapsed >= 3 && $lastValue < $avg) {
            $status = 'SLOW_MOVING';
            $label = '🟡 PERPUTARAN LAMBAT (Slow Moving)';
            $badgeClass = 'badge-warning text-white';
            $borderClass = 'border-warning/30';
            $diagnosis = "Toko tidak melakukan pembelian ulang selama {$monthsElapsed} bulan setelah transaksi kecil. Perputaran produk di toko ini terindikasi melambat.";
            $action = 'Lakukan kunjungan untuk evaluasi rak display toko. Pertimbangkan program promo display atau penyesuaian SKU yang lebih cepat laku.';
        } else {
            $status = 'STOCKOUT_RISK';
            $label = '🔴 CADANGAN HABIS / PERLU RE-ORDER';
            $badgeClass = 'badge-error text-white';
            $borderClass = 'border-error/30';
            $habisSejak = $monthsElapsed > ceil($monthsCoverage) ? round($monthsElapsed - $monthsCoverage) . ' bulan lalu.' : 'beberapa minggu lalu.';
            $diagnosis = "Pembelian terakhir ({$evalPeriod}) sebesar Rp " . number_format($lastValue, 0, ',', '.') . " (daya tahan {$monthsCoverage} bulan) secara kalkulasi konsumsi sudah terserap habis sejak {$habisSejak} Toko berisiko mengalami kekosongan barang.";
            $action = 'Prioritaskan kunjungan re-order segera. Cek ketersediaan fisik di toko untuk memastikan barang tidak kosong atau tergeser produk kompetitor.';
        }

        return [
            'status' => $status,
            'label' => $label,
            'badgeClass' => $badgeClass,
            'borderClass' => $borderClass,
            'monthsCoverage' => $monthsCoverage,
            'monthsElapsed' => $monthsElapsed,
            'theoreticalBalance' => $theoreticalBalance,
            'evalValue' => $evalValue,
            'avgValue' => $avg,
            'evalPeriod' => $evalPeriod,
            'isCurrentMonth' => $isCurrentMonth,
            'consistencyRate' => $consistencyRate,
            'activeMonths' => $activeMonths,
            'totalMonths' => $totalMonths,
            'dormancyText' => $dormancyText,
            'dormancyBadge' => $dormancyBadge,
            'diagnosis' => $diagnosis,
            'action' => $action
        ];
    }

    public function getTargetFeasibilityProperty()
    {
        $store = $this->selectedStore;
        if (!$store) {
            return null;
        }

        $avg = (float)($store->avg_transaction ?? 0);
        $totalTarget = (float)($store->total_target ?? 0);
        $jenisReward = $store->jenis_reward ?? '';

        if ($jenisReward === 'BB') {
            $rewardLabel = 'Reward 1KTN';
        } elseif ($jenisReward === 'CB') {
            if ($totalTarget >= 90000000) {
                $rewardLabel = 'Reward 2,5%';
            } elseif ($totalTarget >= 30000000) {
                $rewardLabel = 'Reward 2%';
            } else {
                $rewardLabel = 'Reward 1,5%';
            }
        } else {
            $rewardLabel = 'Reward RWO';
        }

        $prorated = $this->getProratedData($totalTarget, $store, $this->appliedKuartal);
        $activeAchievement = (float)($prorated['active_achievement'] ?? 0);
        $percent = (float)($prorated['percent'] ?? 0);
        $gap = max(0, $totalTarget - $activeAchievement);

        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $selectedQuarter = (int)($this->appliedKuartal ?: $currentQuarter);

        if ($selectedQuarter === $currentQuarter) {
            $firstMonthOfQ = ($selectedQuarter - 1) * 3 + 1;
            $currentMonthOfQ = $currentMonth - $firstMonthOfQ + 1;
            $remainingMonths = max(1, 3 - $currentMonthOfQ + 1);
        } elseif ($selectedQuarter > $currentQuarter) {
            $remainingMonths = 3;
        } else {
            $remainingMonths = 0;
        }

        $recommendedPO = $remainingMonths > 0 ? round($gap / $remainingMonths) : $gap;
        $feasibilityRatio = $avg > 0 ? round(($recommendedPO / $avg) * 100, 1) : 0;

        if ($gap <= 0) {
            $status = 'ACHIEVED';
            $label = '🟢 TARGET SUDAH TERCAPAI (100%+)';
            $badgeClass = 'badge-success text-white';
            $borderClass = 'border-success/30';
            $pitch = "Pencapaian toko sudah melampaui target (" . number_format($percent, 1, ',', '.') . "%). Reward {$rewardLabel} sudah di tangan. Arahkan salesman menjaga hubungan baik dan pasokan rutin.";
        } elseif ($selectedQuarter < $currentQuarter) {
            $status = 'MISSED';
            $label = '🔴 TARGET TIDAK TERCAPAI (LEWAT)';
            $badgeClass = 'badge-error text-white';
            $borderClass = 'border-error/30';
            $pitch = "Kuartal Q{$selectedQuarter} telah berakhir dengan kekurangan Rp " . number_format($gap, 0, ',', '.') . ". Evaluasi kendala toko agar di kuartal berikutnya tidak terulang.";
        } elseif ($avg <= 0 || $recommendedPO <= $avg) {
            $status = 'ON_TRACK';
            $label = '🟢 SANGAT REALISTIS (ON TRACK)';
            $badgeClass = 'badge-success text-white';
            $borderClass = 'border-success/30';
            $pitch = "Kekurangan target Rp " . number_format($gap, 0, ',', '.') . " membutuhkan order minimal Rp " . number_format($recommendedPO, 0, ',', '.') . "/bulan. Beban ini di bawah kapasitas belanja normal toko. Salesman cukup menjaga ritme order normal.";
        } elseif ($recommendedPO <= ($avg * 1.5)) {
            $status = 'NEED_PUSH';
            $label = '🟡 BUTUH DORONGAN PROMO (STRETCH)';
            $badgeClass = 'badge-warning text-white';
            $borderClass = 'border-warning/30';
            $pitch = "Dibutuhkan order Rp " . number_format($recommendedPO, 0, ',', '.') . "/bulan ({$feasibilityRatio}% dari rata-rata). Toko bisa lolos HANYA jika salesman aktif menawarkan paket promo bundling atau penambahan SKU baru.";
        } else {
            $status = 'HIGH_RISK';
            $label = '🔴 RISIKO TINGGI GAGAL';
            $badgeClass = 'badge-error text-white';
            $borderClass = 'border-error/30';
            $pitch = "Dibutuhkan order Rp " . number_format($recommendedPO, 0, ',', '.') . "/bulan ({$feasibilityRatio}% dari rata-rata toko). Beban sisa target sangat berat. Perlu negosiasi komitmen khusus pemilik toko atau promo ekstra principal.";
        }

        return [
            'status' => $status,
            'label' => $label,
            'badgeClass' => $badgeClass,
            'borderClass' => $borderClass,
            'rewardLabel' => $rewardLabel,
            'totalTarget' => $totalTarget,
            'activeAchievement' => $activeAchievement,
            'gap' => $gap,
            'percent' => $percent,
            'remainingMonths' => $remainingMonths,
            'recommendedPO' => $recommendedPO,
            'feasibilityRatio' => $feasibilityRatio,
            'pitch' => $pitch
        ];
    }

    public function getQtyHistoryProperty()
    {
        if (!$this->selectedCustomerCode || !$this->selectedDistributorCode) {
            return null;
        }

        $currentDate = \Carbon\Carbon::now();
        $endMonth = $currentDate->month;
        $endYear = $currentDate->year;

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = \Carbon\Carbon::create($endYear, $endMonth, 1)->subMonths($i);
            $months[] = [
                'key' => $d->format('Y-m'),
                'year' => (int) $d->year,
                'month' => (int) $d->month,
                'label' => $d->translatedFormat('M y'),
                'full_label' => $d->translatedFormat('F Y'),
            ];
        }

        $startDate = $months[0]['year'] . '-' . str_pad($months[0]['month'], 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = \Carbon\Carbon::create($months[5]['year'], $months[5]['month'], 1)->endOfMonth()->format('Y-m-d');

        $rawRows = DB::table('t_sellingout')
            ->select([
                DB::raw("COALESCE(\"SUBBRAND\", 'LAINNYA') as subbrand"),
                DB::raw("COALESCE(\"NAMAITEMPRC\", 'LAINNYA') as nama_item"),
                DB::raw("COALESCE(\"KDITEMPRC\", '') as kd_item"),
                DB::raw("COALESCE(\"KATEGORI\", 'OTHER') as kategori"),
                DB::raw("COALESCE(\"TOPITEM\", 'OTHER') as topitem"),
                DB::raw("CAST(\"THN\" AS integer) as thn"),
                DB::raw("CAST(\"BLN\" AS integer) as bln"),
                DB::raw("SUM(\"TTL_QTY_KTN\") as ktn"),
                DB::raw("SUM(\"NETT\") as nett")
            ])
            ->where('KDDIST', $this->selectedDistributorCode)
            ->where('KDUNIQ', $this->selectedCustomerCode)
            ->whereBetween('INVOICE_DATE', [$startDate, $endDate])
            ->groupBy([
                DB::raw("COALESCE(\"SUBBRAND\", 'LAINNYA')"),
                DB::raw("COALESCE(\"NAMAITEMPRC\", 'LAINNYA')"),
                DB::raw("COALESCE(\"KDITEMPRC\", '')"),
                DB::raw("COALESCE(\"KATEGORI\", 'OTHER')"),
                DB::raw("COALESCE(\"TOPITEM\", 'OTHER')"),
                DB::raw("CAST(\"THN\" AS integer)"),
                DB::raw("CAST(\"BLN\" AS integer)")
            ])
            ->get();

        $subbrands = [];
        $grandTotalPerMonth = array_fill_keys(array_column($months, 'key'), 0);
        $grandTotal6Bln = 0;

        foreach ($rawRows as $r) {
            $sub = trim($r->subbrand);
            $item = trim($r->nama_item);
            $monthKey = sprintf('%04d-%02d', $r->thn, $r->bln);
            $val = (float) $r->ktn;

            if (!isset($subbrands[$sub])) {
                $subbrands[$sub] = [
                    'name' => $sub,
                    'key' => md5($sub),
                    'kategori' => $r->kategori,
                    'topitem' => $r->topitem,
                    'monthly' => array_fill_keys(array_column($months, 'key'), 0),
                    'total' => 0,
                    'avg' => 0,
                    'trend' => 'flat',
                    'items' => []
                ];
            }

            if (!isset($subbrands[$sub]['items'][$item])) {
                $subbrands[$sub]['items'][$item] = [
                    'name' => $item,
                    'kd_item' => $r->kd_item,
                    'monthly' => array_fill_keys(array_column($months, 'key'), 0),
                    'total' => 0,
                    'avg' => 0
                ];
            }

            if (isset($subbrands[$sub]['monthly'][$monthKey])) {
                $subbrands[$sub]['monthly'][$monthKey] += $val;
                $subbrands[$sub]['items'][$item]['monthly'][$monthKey] += $val;
                
                $subbrands[$sub]['total'] += $val;
                $subbrands[$sub]['items'][$item]['total'] += $val;

                $grandTotalPerMonth[$monthKey] += $val;
                $grandTotal6Bln += $val;
            }
        }

        $numMonths = count($months);
        $latestMonthKey = $months[5]['key'];
        $prevMonthKey = $months[4]['key'];

        foreach ($subbrands as &$sb) {
            $sb['avg'] = $numMonths > 0 ? $sb['total'] / $numMonths : 0;
            $lastVal = $sb['monthly'][$latestMonthKey];
            $prevVal = $sb['monthly'][$prevMonthKey];
            if ($lastVal > $prevVal) {
                $sb['trend'] = 'up';
            } elseif ($lastVal < $prevVal) {
                $sb['trend'] = 'down';
            } else {
                $sb['trend'] = 'flat';
            }

            foreach ($sb['items'] as &$it) {
                $it['avg'] = $numMonths > 0 ? $it['total'] / $numMonths : 0;
            }
            uasort($sb['items'], fn($a, $b) => $b['total'] <=> $a['total']);
        }
        unset($sb, $it);

        uasort($subbrands, fn($a, $b) => $b['total'] <=> $a['total']);

        // 1. Drop SKUs Alert
        $dropSkus = [];
        $olderMonthKeys = array_slice(array_column($months, 'key'), 0, 4);

        foreach ($subbrands as $sbName => $sbData) {
            $olderSum = 0;
            $olderActiveCount = 0;
            foreach ($olderMonthKeys as $mk) {
                if ($sbData['monthly'][$mk] > 0) {
                    $olderSum += $sbData['monthly'][$mk];
                    $olderActiveCount++;
                }
            }
            $olderAvg = $olderActiveCount > 0 ? $olderSum / $olderActiveCount : 0;
            $latestVal = $sbData['monthly'][$latestMonthKey];
            $prevVal = $sbData['monthly'][$prevMonthKey];

            if ($olderActiveCount >= 2 && $olderAvg >= 3 && $latestVal <= 0 && $prevVal <= 0) {
                $dropSkus[] = [
                    'subbrand' => $sbName,
                    'status' => 'CRITICAL',
                    'badge' => 'badge-error text-white',
                    'duration' => '2 Bulan Terakhir (Nol)',
                    'historical_avg' => round($olderAvg, 1),
                    'lost_potential' => round($olderAvg, 1),
                    'action' => "Investigasi display toko dan tawarkan re-stock {$sbName} (potensi hilang ~" . number_format($olderAvg, 1, ',', '.') . " KTN/bln)."
                ];
            } elseif ($olderActiveCount >= 2 && $olderAvg >= 3 && $latestVal <= 0 && $prevVal > 0) {
                $dropSkus[] = [
                    'subbrand' => $sbName,
                    'status' => 'WARNING',
                    'badge' => 'badge-warning text-white',
                    'duration' => 'Bulan Ini Belum Order',
                    'historical_avg' => round($olderAvg, 1),
                    'lost_potential' => round($olderAvg, 1),
                    'action' => "Follow up re-order {$sbName} bulan ini sebelum pasokan terputus."
                ];
            }
        }

        // 2. Pareto Analysis
        $heroSubbrand = null;
        $heroPercent = 0;
        $top2Percent = 0;
        $allSubNames = array_keys($subbrands);

        if ($grandTotal6Bln > 0 && !empty($allSubNames)) {
            $heroSubbrand = $allSubNames[0];
            $heroTotal = $subbrands[$heroSubbrand]['total'];
            $heroPercent = round(($heroTotal / $grandTotal6Bln) * 100, 1);
            
            $top2Total = $heroTotal + (isset($allSubNames[1]) ? $subbrands[$allSubNames[1]]['total'] : 0);
            $top2Percent = round(($top2Total / $grandTotal6Bln) * 100, 1);
        }

        if ($heroPercent >= 70) {
            $paretoStatus = 'ACUTE';
            $paretoLabel = '🔴 Ketergantungan Akut';
            $paretoBadge = 'badge-error text-white';
            $paretoBorder = 'border-error/30';
            $paretoDesc = "Volume toko didominasi {$heroSubbrand} ({$heroPercent}%). Portofolio sangat rentan jika pasokan produk ini bermasalah.";
            $paretoAction = "Wajib diversifikasi pesanan ke kategori lain (wafer/biskuit kelapa) saat taking order.";
        } elseif ($heroPercent >= 45) {
            $paretoStatus = 'MODERATE';
            $paretoLabel = '🟡 Dominasi Moderat';
            $paretoBadge = 'badge-warning text-white';
            $paretoBorder = 'border-warning/30';
            $paretoDesc = "Subbrand utama {$heroSubbrand} menyumbang {$heroPercent}%, 2 produk teratas menopang {$top2Percent}%.";
            $paretoAction = "Mulai bangun lini produk ketiga agar portofolio semakin seimbang.";
        } else {
            $paretoStatus = 'HEALTHY';
            $paretoLabel = '🟢 Portofolio Sehat';
            $paretoBadge = 'badge-success text-white';
            $paretoBorder = 'border-success/30';
            $paretoDesc = "Sebaran produk seimbang. Penjualan tidak bergantung pada satu subbrand saja ({$heroSubbrand} hanya {$heroPercent}%).";
            $paretoAction = "Pertahankan variasi produk yang sudah kuat dan dorong repeat order rutin.";
        }

        // 3. Cross Selling White-Space (Prioritas Top Item Distributor)
        $priorityQuery = DB::table('t_sellingout')
            ->select('SUBBRAND', 'KATEGORI', DB::raw('SUM("TTL_QTY_KTN") as total_dist_ktn'))
            ->where('THN', '2026')
            ->whereIn('KATEGORI', ['TOPITEM', 'NPD'])
            ->whereNotNull('SUBBRAND');

        if ($this->selectedDistributorCode) {
            $priorityQuery->where('KDDIST', $this->selectedDistributorCode);
        }

        $prioritySubbrands = $priorityQuery
            ->groupBy('SUBBRAND', 'KATEGORI')
            ->orderBy('total_dist_ktn', 'desc')
            ->limit(10)
            ->get();

        $unpurchased = [];
        foreach ($prioritySubbrands as $ps) {
            $psName = trim($ps->SUBBRAND);
            if (!isset($subbrands[$psName]) || $subbrands[$psName]['total'] <= 0) {
                $unpurchased[] = [
                    'subbrand' => $psName,
                    'kategori' => $ps->KATEGORI,
                    'action' => "Toko belum pernah ambil {$psName} ({$ps->KATEGORI}). Tawarkan paket tester / bundling promo perdana."
                ];
                if (count($unpurchased) >= 2) break;
            }
        }

        // 4. Flavor Analysis on Hero Subbrand
        $flavorAnalysis = null;
        if ($heroSubbrand && !empty($subbrands[$heroSubbrand]['items'])) {
            $heroItems = array_values($subbrands[$heroSubbrand]['items']);
            $topFlavor = $heroItems[0];
            $bottomFlavor = end($heroItems);
            $heroTotal = $subbrands[$heroSubbrand]['total'];

            $topPercent = $heroTotal > 0 ? round(($topFlavor['total'] / $heroTotal) * 100, 1) : 0;
            $bottomPercent = $heroTotal > 0 ? round(($bottomFlavor['total'] / $heroTotal) * 100, 1) : 0;

            $flavorAnalysis = [
                'hero_subbrand' => $heroSubbrand,
                'top_flavor' => $topFlavor['name'],
                'top_total' => $topFlavor['total'],
                'top_percent' => $topPercent,
                'bottom_flavor' => $bottomFlavor['name'],
                'bottom_total' => $bottomFlavor['total'],
                'bottom_percent' => $bottomPercent,
                'tip' => "Fokus dorong repeat order varian favorit ({$topFlavor['name']}: {$topPercent}%). Jangan memaksakan varian lambat ({$bottomFlavor['name']}: {$bottomPercent}%) agar tidak macet di toko."
            ];
        }

        return [
            'months' => $months,
            'subbrands' => $subbrands,
            'grandTotalPerMonth' => $grandTotalPerMonth,
            'grandTotal6Bln' => $grandTotal6Bln,
            'grandTotalAvg' => $numMonths > 0 ? $grandTotal6Bln / $numMonths : 0,
            'kpi' => [
                'total_qty' => $grandTotal6Bln,
                'avg_qty' => $numMonths > 0 ? $grandTotal6Bln / $numMonths : 0,
                'active_subbrands' => count($subbrands),
                'hero_subbrand' => $heroSubbrand ?? '-',
                'hero_percent' => $heroPercent
            ],
            'dropSkus' => $dropSkus,
            'pareto' => [
                'status' => $paretoStatus,
                'label' => $paretoLabel,
                'badge' => $paretoBadge,
                'border' => $paretoBorder,
                'hero' => $heroSubbrand,
                'hero_percent' => $heroPercent,
                'top2_percent' => $top2Percent,
                'desc' => $paretoDesc,
                'action' => $paretoAction
            ],
            'crossSelling' => $unpurchased,
            'flavorAnalysis' => $flavorAnalysis
        ];
    }

    public function getVisitHistoryProperty()
    {
        $store = $this->selectedStore;
        if (!$store) {
            return null;
        }

        $customerPrc = trim($store->customer_prc ?? '');
        if (empty($customerPrc)) {
            return [
                'hasPrc' => false,
                'prc' => null,
                'customer_name' => $store->customer_name ?? '',
                'customer_code' => $store->customer_code ?? '',
            ];
        }

        $kuartal = (int) ($this->appliedKuartal ?: ceil(date('n') / 3));
        $year = 2026;

        $qRanges = [
            1 => ['start' => "{$year}-01-01", 'end' => "{$year}-03-31", 'label' => "Kuartal 1 (Jan - Mar {$year})"],
            2 => ['start' => "{$year}-04-01", 'end' => "{$year}-06-30", 'label' => "Kuartal 2 (Apr - Jun {$year})"],
            3 => ['start' => "{$year}-07-01", 'end' => "{$year}-09-30", 'label' => "Kuartal 3 (Jul - Sep {$year})"],
            4 => ['start' => "{$year}-10-01", 'end' => "{$year}-12-31", 'label' => "Kuartal 4 (Okt - Des {$year})"],
        ];
        $qInfo = $qRanges[$kuartal] ?? $qRanges[4];

        $rawVisits = DB::table('rpt_visit_an_h')
            ->where('BID', 'HOINA')
            ->where('CUSTNO', $customerPrc)
            ->whereBetween('TANGGAL', [$qInfo['start'], $qInfo['end']])
            ->select(
                'ID',
                'TANGGAL',
                'TIME_IN',
                'TIME_OUT',
                'TIME_CONSUME',
                'MUID',
                'MUNAME',
                'TEAMNAME',
                'FLAG_VISIT',
                'FLAG_BUY',
                'FLAG_EC',
                'FLAG_PJP',
                'ORDER_VAL',
                'ORDER_QTY',
                'PHOTONAME'
            )
            ->orderByDesc('TANGGAL')
            ->orderByDesc('TIME_IN')
            ->get();

        $rawReasons = DB::table('rpt_visit_an_r')
            ->where('BID', 'HOINA')
            ->where('CUSTNO', $customerPrc)
            ->whereBetween(DB::raw('CAST("TANGGAL" AS TEXT)'), [$qInfo['start'], $qInfo['end']])
            ->select('MUID', 'TANGGAL', 'HID', 'REASON_TYPE', 'REASON_ID', 'REASON_DESC', 'REASON_NOTE')
            ->get();

        $reasonsMap = [];
        foreach ($rawReasons as $r) {
            $rKey = strtoupper(trim($r->MUID)) . '_' . ((string)$r->TANGGAL) . '_' . trim($r->HID);
            if (!isset($reasonsMap[$rKey])) {
                $reasonsMap[$rKey] = [
                    'reason_type' => $r->REASON_TYPE,
                    'reason_id' => $r->REASON_ID,
                    'reason_desc' => $r->REASON_DESC,
                    'reason_note' => $r->REASON_NOTE,
                    'items' => []
                ];
            }
            $reasonsMap[$rKey]['items'][] = [
                'type' => $r->REASON_TYPE,
                'id' => $r->REASON_ID,
                'desc' => $r->REASON_DESC,
                'note' => $r->REASON_NOTE
            ];
        }

        // Group visits by date to detect Joint Visits
        $visitsByDate = [];
        foreach ($rawVisits as $v) {
            $tgl = $v->TANGGAL;
            $visitsByDate[$tgl][] = $v;
        }

        $timeline = [];
        $totalSchedules = count($rawVisits);
        $totalVisits = 0;
        $totalUnvisited = 0;
        $totalEC = 0;
        $totalNotBuy = 0;
        $totalOrderVal = 0;
        $totalOrderQty = 0;
        $totalDurationMins = 0;
        $lastVisit = null;

        $roles = [
            'SPV' => [
                'name' => 'Sales Supervisor (SPV)',
                'badge' => 'badge-primary',
                'schedules' => 0,
                'visits' => 0,
                'unvisited' => 0,
                'ec' => 0,
                'not_buy' => 0,
                'strike_rate' => 0,
                'order_val' => 0,
                'order_qty' => 0,
                'last_date' => null,
                'last_visitor' => null,
                'total_duration' => 0,
                'avg_duration' => 0,
            ],
            'ASM' => [
                'name' => 'Area Sales Manager (ASM)',
                'badge' => 'badge-secondary',
                'schedules' => 0,
                'visits' => 0,
                'unvisited' => 0,
                'ec' => 0,
                'not_buy' => 0,
                'strike_rate' => 0,
                'order_val' => 0,
                'order_qty' => 0,
                'last_date' => null,
                'last_visitor' => null,
                'total_duration' => 0,
                'avg_duration' => 0,
            ],
            'RSM' => [
                'name' => 'Regional Sales Manager (RSM)',
                'badge' => 'badge-accent',
                'schedules' => 0,
                'visits' => 0,
                'unvisited' => 0,
                'ec' => 0,
                'not_buy' => 0,
                'strike_rate' => 0,
                'order_val' => 0,
                'order_qty' => 0,
                'last_date' => null,
                'last_visitor' => null,
                'total_duration' => 0,
                'avg_duration' => 0,
            ],
        ];

        $reasonsNotBuy = [];
        $reasonsNotVisit = [];

        foreach ($rawVisits as $v) {
            $isVisit = strtoupper($v->FLAG_VISIT ?? 'N') === 'Y';
            $isBuy = strtoupper($v->FLAG_BUY ?? 'N') === 'Y';
            
            // Parse duration minutes
            $durationMins = 0;
            if (!empty($v->TIME_CONSUME)) {
                $parts = explode(':', explode('.', $v->TIME_CONSUME)[0]);
                if (count($parts) >= 2) {
                    $durationMins = ((int)$parts[0] * 60) + (int)$parts[1];
                }
            }

            // Role
            $muid = strtoupper(trim($v->MUID ?? ''));
            $role = 'SPV';
            if (str_starts_with($muid, 'RSM')) $role = 'RSM';
            elseif (str_starts_with($muid, 'ASM')) $role = 'ASM';

            // Update Role stats
            $roles[$role]['schedules']++;
            if ($isVisit) {
                $roles[$role]['visits']++;
                $roles[$role]['total_duration'] += $durationMins;
                if (!$roles[$role]['last_date']) {
                    $roles[$role]['last_date'] = $v->TANGGAL;
                    $roles[$role]['last_visitor'] = $v->MUNAME;
                }
            } else {
                $roles[$role]['unvisited']++;
            }

            if ($isBuy) {
                $roles[$role]['ec']++;
                $roles[$role]['order_val'] += (float) ($v->ORDER_VAL ?? 0);
                $roles[$role]['order_qty'] += (int) ($v->ORDER_QTY ?? 0);
            } elseif ($isVisit) {
                $roles[$role]['not_buy']++;
            }

            // Global stats
            if ($isVisit) {
                $totalVisits++;
                $totalDurationMins += $durationMins;
                if (!$lastVisit) {
                    $lastDateObj = \Carbon\Carbon::parse($v->TANGGAL);
                    $lastVisit = [
                        'date' => $v->TANGGAL,
                        'formatted_date' => $lastDateObj->translatedFormat('d M Y'),
                        'days_ago' => $lastDateObj->diffForHumans(\Carbon\Carbon::now(), ['parts' => 1]),
                        'visitor_name' => $v->MUNAME,
                        'visitor_role' => $role,
                        'visitor_muid' => $v->MUID,
                        'order_val' => (float) ($v->ORDER_VAL ?? 0),
                        'duration' => $durationMins
                    ];
                }
            } else {
                $totalUnvisited++;
            }

            if ($isBuy) {
                $totalEC++;
                $totalOrderVal += (float) ($v->ORDER_VAL ?? 0);
                $totalOrderQty += (int) ($v->ORDER_QTY ?? 0);
            } elseif ($isVisit) {
                $totalNotBuy++;
            }

            // Reason lookup from reasonsMap
            $vKey = strtoupper(trim($v->MUID ?? '')) . '_' . ((string)$v->TANGGAL) . '_' . trim($v->ID);
            $reasonInfo = $reasonsMap[$vKey] ?? null;
            $vReasonType = $reasonInfo['reason_type'] ?? null;
            $vReasonId = $reasonInfo['reason_id'] ?? null;
            $vReasonDesc = $reasonInfo['reason_desc'] ?? null;
            $vReasonNote = $reasonInfo['reason_note'] ?? null;

            if ($reasonInfo && count($reasonInfo['items']) > 1) {
                $uniqueDescs = array_unique(array_filter(array_column($reasonInfo['items'], 'desc')));
                $vReasonDesc = implode(' | ', $uniqueDescs);
                $uniqueNotes = array_unique(array_filter(array_column($reasonInfo['items'], 'note')));
                $vReasonNote = implode(' | ', $uniqueNotes);
            }

            // Reasons
            if (!$isBuy && $isVisit && !empty($vReasonDesc)) {
                $desc = trim($vReasonDesc);
                $code = trim($vReasonId ?? '');
                $key = $code ?: $desc;
                if (!isset($reasonsNotBuy[$key])) {
                    $reasonsNotBuy[$key] = ['code' => $code, 'desc' => $desc, 'count' => 0, 'notes' => []];
                }
                $reasonsNotBuy[$key]['count']++;
                if (!empty($vReasonNote)) {
                    $note = trim($vReasonNote);
                    if (!in_array($note, $reasonsNotBuy[$key]['notes'])) {
                        $reasonsNotBuy[$key]['notes'][] = $note;
                    }
                }
            }

            if (!$isVisit && !empty($vReasonDesc)) {
                $desc = trim($vReasonDesc);
                $code = trim($vReasonId ?? '');
                $key = $code ?: $desc;
                if (!isset($reasonsNotVisit[$key])) {
                    $reasonsNotVisit[$key] = ['code' => $code, 'desc' => $desc, 'count' => 0, 'notes' => []];
                }
                $reasonsNotVisit[$key]['count']++;
                if (!empty($vReasonNote)) {
                    $note = trim($vReasonNote);
                    if (!in_array($note, $reasonsNotVisit[$key]['notes'])) {
                        $reasonsNotVisit[$key]['notes'][] = $note;
                    }
                }
            }

            // Check if Joint Visit on this date (distinct MUID on same date)
            $sameDayVisits = $visitsByDate[$v->TANGGAL] ?? [];
            $distinctMuidsSameDay = collect($sameDayVisits)->pluck('MUID')->unique()->count();
            $isJoint = $distinctMuidsSameDay > 1;

            // Time in - out range
            $timeRange = '-';
            if (!empty($v->TIME_IN) && !empty($v->TIME_OUT)) {
                $in = substr(explode(' ', $v->TIME_IN)[1] ?? '', 0, 5);
                $out = substr(explode(' ', $v->TIME_OUT)[1] ?? '', 0, 5);
                if ($in && $out && $in !== '00:00') {
                    $timeRange = "{$in} - {$out}";
                }
            }

            $timeline[] = [
                'id' => $v->ID,
                'date' => $v->TANGGAL,
                'formatted_date' => \Carbon\Carbon::parse($v->TANGGAL)->translatedFormat('d M Y'),
                'time_in' => $v->TIME_IN,
                'time_out' => $v->TIME_OUT,
                'time_range' => $timeRange,
                'duration_minutes' => $durationMins,
                'role' => $role,
                'role_badge' => $roles[$role]['badge'],
                'muid' => $v->MUID,
                'muname' => $v->MUNAME,
                'teamname' => $v->TEAMNAME,
                'is_visit' => $isVisit,
                'is_buy' => $isBuy,
                'is_pjp' => strtoupper($v->FLAG_PJP ?? '') === 'R',
                'is_joint' => $isJoint,
                'order_val' => (float) ($v->ORDER_VAL ?? 0),
                'order_qty' => (int) ($v->ORDER_QTY ?? 0),
                'photoname' => $v->PHOTONAME,
                'reason_type' => $vReasonType,
                'reason_id' => $vReasonId,
                'reason_desc' => $vReasonDesc,
                'reason_note' => $vReasonNote,
            ];
        }

        // Compute averages and rates
        foreach ($roles as $rk => $rv) {
            $roles[$rk]['strike_rate'] = $rv['visits'] > 0 ? round(($rv['ec'] / $rv['visits']) * 100, 1) : 0;
            $roles[$rk]['avg_duration'] = $rv['visits'] > 0 ? round($rv['total_duration'] / $rv['visits'], 1) : 0;
        }

        $strikeRate = $totalVisits > 0 ? round(($totalEC / $totalVisits) * 100, 1) : 0;
        $avgDuration = $totalVisits > 0 ? round($totalDurationMins / $totalVisits, 1) : 0;

        return [
            'hasPrc' => true,
            'prc' => $customerPrc,
            'kuartal' => $kuartal,
            'kuartalLabel' => $qInfo['label'],
            'kpi' => [
                'total_schedules' => $totalSchedules,
                'total_visits' => $totalVisits,
                'total_unvisited' => $totalUnvisited,
                'total_ec' => $totalEC,
                'total_not_buy' => $totalNotBuy,
                'strike_rate' => $strikeRate,
                'total_order_val' => $totalOrderVal,
                'total_order_qty' => $totalOrderQty,
                'avg_duration' => $avgDuration,
                'last_visit' => $lastVisit,
            ],
            'roles' => $roles,
            'reasons' => [
                'not_buy' => array_values($reasonsNotBuy),
                'not_visit' => array_values($reasonsNotVisit),
            ],
            'timeline' => $timeline
        ];
    }

    public function getOrderFulfillmentProperty()
    {
        $store = $this->selectedStore;
        if (!$store) {
            return null;
        }

        $customerPrc = trim($store->customer_prc ?? '');
        $customerCode = $this->selectedCustomerCode;
        $distributorCode = $this->selectedDistributorCode;

        $kuartal = (int) ($this->appliedKuartal ?: ceil(date('n') / 3));
        $year = 2026;

        $qMonths = [
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12]
        ];
        $months = $qMonths[$kuartal] ?? [10, 11, 12];

        $monthLabels = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $qRanges = [
            1 => ['start' => "{$year}-01-01", 'end' => "{$year}-03-31", 'label' => "Kuartal 1 (Jan - Mar {$year})"],
            2 => ['start' => "{$year}-04-01", 'end' => "{$year}-06-30", 'label' => "Kuartal 2 (Apr - Jun {$year})"],
            3 => ['start' => "{$year}-07-01", 'end' => "{$year}-09-30", 'label' => "Kuartal 3 (Jul - Sep {$year})"],
            4 => ['start' => "{$year}-10-01", 'end' => "{$year}-12-31", 'label' => "Kuartal 4 (Okt - Des {$year})"],
        ];
        $qInfo = $qRanges[$kuartal] ?? $qRanges[4];

        // 1. Ambil Order PO dari rpt_visit_an_h jika ada PRC (Deduplikasi Sesi Joint Visit)
        $poSessions = [];
        $rawPoSum = 0;
        if (!empty($customerPrc)) {
            $rawVisits = DB::table('rpt_visit_an_h')
                ->where('BID', 'HOINA')
                ->where('CUSTNO', $customerPrc)
                ->whereBetween('TANGGAL', [$qInfo['start'], $qInfo['end']])
                ->where('FLAG_BUY', 'Y')
                ->select('ID', 'TANGGAL', 'TIME_IN', 'TIME_OUT', 'MUID', 'MUNAME', 'ORDER_VAL', 'ORDER_QTY')
                ->orderBy('TANGGAL')
                ->orderBy('TIME_IN')
                ->get();

            $sessionsByDate = [];
            foreach ($rawVisits as $v) {
                $tgl = $v->TANGGAL;
                $timeIn = $v->TIME_IN ? \Carbon\Carbon::parse($v->TIME_IN) : null;
                $rawPoSum += (float) ($v->ORDER_VAL ?? 0);

                $matchedKey = null;
                if (isset($sessionsByDate[$tgl])) {
                    foreach ($sessionsByDate[$tgl] as $sKey => $sData) {
                        if ($timeIn && $sData['start_time']) {
                            if (abs($timeIn->diffInMinutes($sData['start_time'])) <= 60) {
                                $matchedKey = $sKey;
                                break;
                            }
                        } else {
                            $matchedKey = $sKey;
                            break;
                        }
                    }
                }

                $muid = strtoupper(trim($v->MUID ?? ''));
                $role = 'SPV';
                if (str_starts_with($muid, 'RSM')) $role = 'RSM';
                elseif (str_starts_with($muid, 'ASM')) $role = 'ASM';

                $memberInfo = [
                    'muid' => $v->MUID,
                    'name' => $v->MUNAME,
                    'role' => $role,
                    'val' => (float) ($v->ORDER_VAL ?? 0),
                    'qty' => (int) ($v->ORDER_QTY ?? 0),
                    'time_in' => $v->TIME_IN
                ];

                if ($matchedKey !== null) {
                    $sessionsByDate[$tgl][$matchedKey]['members'][] = $memberInfo;
                    if ((float) $v->ORDER_VAL > $sessionsByDate[$tgl][$matchedKey]['val']) {
                        $sessionsByDate[$tgl][$matchedKey]['val'] = (float) $v->ORDER_VAL;
                        $sessionsByDate[$tgl][$matchedKey]['qty'] = (int) $v->ORDER_QTY;
                        $sessionsByDate[$tgl][$matchedKey]['lead_person'] = $v->MUNAME;
                        $sessionsByDate[$tgl][$matchedKey]['lead_role'] = $role;
                    }
                } else {
                    $sessionsByDate[$tgl][] = [
                        'date' => $tgl,
                        'start_time' => $timeIn,
                        'time_str' => $timeIn ? $timeIn->format('H:i') : '-',
                        'val' => (float) ($v->ORDER_VAL ?? 0),
                        'qty' => (int) ($v->ORDER_QTY ?? 0),
                        'lead_person' => $v->MUNAME,
                        'lead_role' => $role,
                        'members' => [$memberInfo]
                    ];
                }
            }

            foreach ($sessionsByDate as $tgl => $sList) {
                foreach ($sList as $s) {
                    $isJv = count($s['members']) > 1;
                    $poSessions[] = [
                        'date' => $s['date'],
                        'formatted_date' => \Carbon\Carbon::parse($s['date'])->translatedFormat('d M Y'),
                        'time' => $s['time_str'],
                        'val' => $s['val'],
                        'qty' => $s['qty'],
                        'lead_person' => $s['lead_person'],
                        'lead_role' => $s['lead_role'],
                        'is_jv' => $isJv,
                        'member_count' => count($s['members']),
                        'members' => $s['members']
                    ];
                }
            }
        }

        // Sort PO sessions desc
        usort($poSessions, fn($a, $b) => strcmp($b['date'] . $b['time'], $a['date'] . $a['time']));

        // 2. Ambil Faktur Realisasi SO dari t_sellingout
        $rawInvoices = DB::table('t_sellingout')
            ->where('THN', (string)$year)
            ->whereIn('BLN', array_map('strval', $months))
            ->where('KDDIST', $distributorCode)
            ->where('KDUNIQ', $customerCode)
            ->where('REG_FEST', 'REG')
            ->select(
                'INVOICE_NO',
                'INVOICE_DATE',
                'BLN',
                'SLSNAME',
                DB::raw('SUM("NETT") as netto'),
                DB::raw('SUM("TTL_QTY_KTN") as total_ktn'),
                DB::raw('COUNT(DISTINCT "NAMAITEMPRC") as total_sku')
            )
            ->groupBy('INVOICE_NO', 'INVOICE_DATE', 'BLN', 'SLSNAME')
            ->orderByDesc('INVOICE_DATE')
            ->get();

        $invoices = [];
        foreach ($rawInvoices as $inv) {
            $invoices[] = [
                'invoice_no' => $inv->INVOICE_NO,
                'date' => $inv->INVOICE_DATE,
                'formatted_date' => \Carbon\Carbon::parse($inv->INVOICE_DATE)->translatedFormat('d M Y'),
                'month' => (int)$inv->BLN,
                'slsname' => $inv->SLSNAME ?: '-',
                'netto' => (float)$inv->netto,
                'total_ktn' => (float)$inv->total_ktn,
                'total_sku' => (int)$inv->total_sku,
            ];
        }

        // 3. Breakdown Bulanan
        $monthlyComparison = [];
        $totalCleanPoVal = 0;
        $totalCleanPoQty = 0;
        $totalSoNetto = 0;
        $totalSoKtn = 0;

        foreach ($months as $m) {
            $mPoSessions = array_filter($poSessions, function($s) use ($m, $year) {
                $dt = \Carbon\Carbon::parse($s['date']);
                return (int)$dt->month === $m && (int)$dt->year === $year;
            });
            $mPoVal = array_sum(array_column($mPoSessions, 'val'));
            $mPoQty = array_sum(array_column($mPoSessions, 'qty'));

            $mInvoices = array_filter($invoices, fn($inv) => $inv['month'] === $m);
            $mSoVal = array_sum(array_column($mInvoices, 'netto'));
            $mSoKtn = array_sum(array_column($mInvoices, 'total_ktn'));

            $gap = $mSoVal - $mPoVal;
            $rate = 0;
            if ($mPoVal > 0) {
                $rate = round(($mSoVal / $mPoVal) * 100, 1);
            } elseif ($mSoVal > 0) {
                $rate = 100;
            }

            $status = 'Terpenuhi Optimal';
            $statusBadge = 'badge-success text-white';
            if ($mPoVal > 0 && $mSoVal == 0) {
                $status = 'Drop / Belum Kirim';
                $statusBadge = 'badge-error text-white';
            } elseif ($mPoVal > 0 && $rate < 80) {
                $status = 'Terkirim Parsial';
                $statusBadge = 'badge-warning text-white';
            } elseif ($mPoVal == 0 && $mSoVal > 0) {
                $status = 'Order Mandiri (Direct)';
                $statusBadge = 'badge-info text-white';
            } elseif ($mPoVal == 0 && $mSoVal == 0) {
                $status = 'Tidak Ada Transaksi';
                $statusBadge = 'badge-ghost opacity-60';
            }

            $monthlyComparison[] = [
                'month' => $m,
                'label' => $monthLabels[$m] . ' ' . $year,
                'po_val' => $mPoVal,
                'po_qty' => $mPoQty,
                'po_count' => count($mPoSessions),
                'so_val' => $mSoVal,
                'so_ktn' => $mSoKtn,
                'so_count' => count($mInvoices),
                'gap' => $gap,
                'rate' => $rate,
                'status' => $status,
                'status_badge' => $statusBadge,
            ];

            $totalCleanPoVal += $mPoVal;
            $totalCleanPoQty += $mPoQty;
            $totalSoNetto += $mSoVal;
            $totalSoKtn += $mSoKtn;
        }

        $quarterGap = $totalSoNetto - $totalCleanPoVal;
        $quarterFulfillment = $totalCleanPoVal > 0 ? round(($totalSoNetto / $totalCleanPoVal) * 100, 1) : ($totalSoNetto > 0 ? 100 : 0);
        $jvSavings = $rawPoSum - $totalCleanPoVal;

        $overallStatus = 'Terpenuhi Optimal';
        $overallBadge = 'badge-success text-white';
        if ($totalCleanPoVal > 0 && $totalSoNetto == 0) {
            $overallStatus = 'Drop Order Total';
            $overallBadge = 'badge-error text-white';
        } elseif ($totalCleanPoVal > 0 && $quarterFulfillment < 80) {
            $overallStatus = 'Terkirim Sebagian (Parsial)';
            $overallBadge = 'badge-warning text-white';
        } elseif ($totalCleanPoVal == 0 && $totalSoNetto > 0) {
            $overallStatus = 'Repeat Order Mandiri';
            $overallBadge = 'badge-info text-white';
        } elseif ($totalCleanPoVal == 0 && $totalSoNetto == 0) {
            $overallStatus = 'Tidak Ada Transaksi';
            $overallBadge = 'badge-ghost opacity-60';
        }

        return [
            'hasPrc' => !empty($customerPrc),
            'prc' => $customerPrc,
            'customer_code' => $customerCode,
            'customer_name' => $store->customer_name ?? '',
            'kuartal' => $kuartal,
            'kuartalLabel' => $qInfo['label'],
            'kpi' => [
                'total_po_val' => $totalCleanPoVal,
                'total_po_qty' => $totalCleanPoQty,
                'total_po_sessions' => count($poSessions),
                'total_po_raw' => $rawPoSum,
                'jv_savings' => $jvSavings,
                'has_jv' => $jvSavings > 0,
                'total_so_val' => $totalSoNetto,
                'total_so_ktn' => $totalSoKtn,
                'total_so_invoices' => count($invoices),
                'gap' => $quarterGap,
                'fulfillment_rate' => $quarterFulfillment,
                'overall_status' => $overallStatus,
                'overall_badge' => $overallBadge,
            ],
            'monthly' => $monthlyComparison,
            'po_sessions' => $poSessions,
            'invoices' => $invoices,
        ];
    }

    public function mount()
    {
        $currentMonth = (int) date('n');
        $currentQuarter = (string) ceil($currentMonth / 3);
        $this->appliedKuartal = $currentQuarter;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSortField()
    {
        $this->resetPage();
    }

    #[Livewire\Attributes\On('apply-rwo-filter')]
    public function applyModalFilter($filters)
    {
        $this->appliedKuartal = $filters['kuartal'] ?? '';
        $this->appliedRegion = $filters['region'] ?? '';
        $this->appliedArea = $filters['area'] ?? '';
        $this->appliedSupervisor = $filters['supervisor'] ?? '';
        $this->appliedDistributor = $filters['distributor'] ?? '';
        
        $this->resetPage();
    }

    public function updatedStatusProgress()
    {
        $this->appliedStatusProgress = $this->statusProgress;
        $this->resetPage();
    }

    public function updatedStatusSkb()
    {
        $this->appliedStatusSkb = $this->statusSkb;
        $this->resetPage();
    }

    public function updatedStatusData()
    {
        $this->appliedStatusData = $this->statusData;
        $this->resetPage();
    }

    public function updatedStatusReward()
    {
        $this->appliedStatusReward = $this->statusReward;
        $this->resetPage();
    }

    public function updatedStatusTransaksi()
    {
        $this->appliedStatusTransaksi = $this->statusTransaksi;
        $this->resetPage();
    }

    public function filterByKpiCard($type)
    {
        if ($type === 'transaksi') {
            $nextVal = $this->appliedStatusTransaksi === 'Sudah' ? 'Semua' : 'Sudah';
            $this->statusTransaksi = $nextVal;
            $this->appliedStatusTransaksi = $nextVal;
        } elseif ($type === 'hijau') {
            $nextVal = $this->appliedStatusProgress === '1. HIJAU' ? 'Semua' : '1. HIJAU';
            $this->statusProgress = $nextVal;
            $this->appliedStatusProgress = $nextVal;
        } elseif ($type === 'kuning') {
            $nextVal = $this->appliedStatusProgress === '2. KUNING' ? 'Semua' : '2. KUNING';
            $this->statusProgress = $nextVal;
            $this->appliedStatusProgress = $nextVal;
        } elseif ($type === 'merah') {
            $nextVal = $this->appliedStatusProgress === '3. MERAH' ? 'Semua' : '3. MERAH';
            $this->statusProgress = $nextVal;
            $this->appliedStatusProgress = $nextVal;
        }

        $this->resetPage();
    }

    public function clearWilayahFilter($level)
    {
        if ($level === 'region') {
            $this->appliedRegion = '';
            $this->appliedArea = '';
            $this->appliedSupervisor = '';
            $this->appliedDistributor = '';
        } elseif ($level === 'area') {
            $this->appliedArea = '';
            $this->appliedSupervisor = '';
            $this->appliedDistributor = '';
        } elseif ($level === 'supervisor') {
            $this->appliedSupervisor = '';
            $this->appliedDistributor = '';
        } elseif ($level === 'distributor') {
            $this->appliedDistributor = '';
        }

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->search = '';
        $currentMonth = (int) date('n');
        $this->appliedKuartal = (string) ceil($currentMonth / 3);
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedSupervisor = '';
        $this->appliedDistributor = '';
        
        $this->statusProgress = 'Semua';
        $this->statusSkb = 'Semua';
        $this->statusData = 'Semua';
        $this->statusReward = 'Semua';
        $this->statusTransaksi = 'Semua';

        $this->appliedStatusProgress = 'Semua';
        $this->appliedStatusSkb = 'Semua';
        $this->appliedStatusData = 'Semua';
        $this->appliedStatusReward = 'Semua';
        $this->appliedStatusTransaksi = 'Semua';
        
        $this->resetPage();
        
        // Broadcast to the filter component so it resets its UI as well
        $this->dispatch('rwo-filter-reset');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }


    public function showStoreDetail($customerCode, $distributorCode)
    {
        $this->selectedCustomerCode = $customerCode;
        $this->selectedDistributorCode = $distributorCode;
        $this->cachedSelectedStore = null;

        $store = $this->selectedStore;
        if ($store) {
            $this->isDetailModalOpen = true;
            $this->remarkKhusus = $store->remark_khusus ?? '';
            
            $this->loadChats();
            $this->loadYearlyHistory($customerCode, $distributorCode);
        }
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedCustomerCode = null;
        $this->selectedDistributorCode = null;
        $this->cachedSelectedStore = null;
        $this->remarkKhusus = '';
        $this->newChatMessage = '';
        $this->chatHistories = [];
        $this->yearlyHistory = [];
        $this->maxYearlyValue = 0;
    }

    public function saveRemarkKhusus()
    {
        if ($this->selectedCustomerCode && $this->selectedDistributorCode) {
            RemarkListPotensiRwo::updateOrCreate(
                [
                    'kuartal' => (string)$this->appliedKuartal,
                    'distributor_code' => $this->selectedDistributorCode,
                    'customer_code' => $this->selectedCustomerCode,
                ],
                [
                    'remark' => $this->remarkKhusus
                ]
            );
            $this->cachedSelectedStore = null;
            
            session()->flash('success', 'Remark khusus berhasil disimpan!');
        }
    }

    public function loadChats()
    {
        if ($this->selectedCustomerCode && $this->selectedDistributorCode) {
            $this->chatHistories = StoreRemarkChat::with('user')
                ->where('kuartal', (string)$this->appliedKuartal)
                ->where('distributor_code', $this->selectedDistributorCode)
                ->where('customer_code', $this->selectedCustomerCode)
                ->orderBy('created_at', 'asc')
                ->get();
        }
    }

    public function sendChat()
    {
        if (trim($this->newChatMessage) === '') {
            return;
        }

        if ($this->selectedCustomerCode && $this->selectedDistributorCode) {
            StoreRemarkChat::create([
                'kuartal' => (string)$this->appliedKuartal,
                'distributor_code' => $this->selectedDistributorCode,
                'customer_code' => $this->selectedCustomerCode,
                'user_id' => auth()->id(),
                'message' => trim($this->newChatMessage),
            ]);

            $this->newChatMessage = '';
            $this->loadChats();
        }
    }

    public function loadYearlyHistory($customerCode, $distributorCode)
    {
        $this->yearlyHistory = [];
        $this->maxYearlyValue = 0;

        $months = [];
        $currentYear = \Carbon\Carbon::now()->year;
        $currentMonth = \Carbon\Carbon::now()->month;
        
        for ($i = 1; $i <= $currentMonth; $i++) {
            $date = \Carbon\Carbon::create($currentYear, $i, 1);
            $months[$date->format('Y-m-d')] = [
                'label' => $date->translatedFormat('M'), 
                'full_label' => $date->translatedFormat('F Y'),
                'value' => 0,
                'status' => 'Dormant',
                'trend' => 'down' 
            ];
        }

        $startDate = \Carbon\Carbon::create($currentYear, 1, 1)->format('Y-m-d');
        $endDate = \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');

        $transactions = DB::table('zv_so_per_toko_2026')
            ->select('bulan', DB::raw('SUM(neto) as total_omset'))
            ->where('kd_dist', $distributorCode)
            ->where('uniq_kd', $customerCode)
            ->whereBetween('bulan', [$startDate, $endDate])
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();

        foreach ($transactions as $txn) {
            $monthKey = \Carbon\Carbon::parse($txn->bulan)->startOfMonth()->format('Y-m-d');
            if (isset($months[$monthKey])) {
                $months[$monthKey]['value'] = (float) $txn->total_omset;
                $months[$monthKey]['status'] = 'Aktif';
            }
        }

        $prevValue = null;
        $maxVal = 0;
        foreach ($months as $key => &$data) {
            if ($data['value'] > $maxVal) {
                $maxVal = $data['value'];
            }

            if ($prevValue !== null) {
                if ($data['value'] > $prevValue) {
                    $data['trend'] = 'up';
                } elseif ($data['value'] < $prevValue) {
                    $data['trend'] = 'down';
                } else {
                    $data['trend'] = 'flat';
                }
            } else {
                $data['trend'] = 'flat';
            }
            $prevValue = $data['value'];
        }

        $this->maxYearlyValue = $maxVal;
        // Keep it chronological.
        $this->yearlyHistory = array_values($months); 
    }

    public function getProratedData($target, $row, $kuartalStr)
    {
        $currentMonth = (int)date('n'); // 1-12
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$kuartalStr;
        
        $m1 = (float)($row->month_1_value ?? 0);
        $m2 = (float)($row->month_2_value ?? 0);
        $m3 = (float)($row->month_3_value ?? 0);
        
        // Determine multiplier (1, 2, or 3 months)
        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1; // 1, 2, or 3
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1; // Future quarter, default to 1 month target comparison
        } else {
            $multiplier = 3; // Past quarter, full 3 months comparison
        }
        
        $proratedTarget = ($target / 3) * $multiplier;
        
        // Calculate achievement based on active months
        if ($multiplier === 1) {
            $activeAchievement = $m1;
        } elseif ($multiplier === 2) {
            $activeAchievement = $m1 + $m2;
        } else {
            $activeAchievement = $m1 + $m2 + $m3;
        }
        
        $percent = $proratedTarget > 0 ? ($activeAchievement / $proratedTarget) * 100 : 0;
        
        if ($percent >= 100) {
            $colorLabel = '1. HIJAU';
        } elseif ($percent >= 80) {
            $colorLabel = '2. KUNING';
        } else {
            $colorLabel = '3. MERAH';
        }
        
        return [
            'prorated_target' => $proratedTarget,
            'active_achievement' => $activeAchievement,
            'percent' => $percent,
            'color_label' => $colorLabel,
            'multiplier' => $multiplier
        ];
    }

    protected function getCurrentFiltersArray(): array
    {
        return [
            'kuartal' => $this->appliedKuartal,
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'supervisor' => $this->appliedSupervisor,
            'distributor' => $this->appliedDistributor,
            'search' => $this->search,
            'statusSkb' => $this->appliedStatusSkb,
            'statusData' => $this->appliedStatusData,
            'statusReward' => $this->appliedStatusReward,
            'statusProgress' => $this->appliedStatusProgress,
            'statusTransaksi' => $this->appliedStatusTransaksi,
        ];
    }

    public function render()
    {
        $query = $this->getStoreQuery((string)$this->appliedKuartal, false);
        $this->applyAccessScope($query);

        $proratedExpr = $this->applyRwoFilters($query, $this->getCurrentFiltersArray());
        $kuartal = $proratedExpr['kuartal'];
        $currentQuarter = $proratedExpr['currentQuarter'];
        $multiplier = $proratedExpr['multiplier'];
        $achievementSql = $proratedExpr['achievementSql'];
        $kpiAchievementSql = $proratedExpr['kpiAchievementSql'];
        $proratedTargetSql = $proratedExpr['proratedTargetSql'];
        $progressExpr = $proratedExpr['progressExpr'];

        // KPI Card Stats (CLONE BEFORE PAGINATE!)
        $kpiQuery = clone $query;
        $kpiQuery->orders = null;

        // Sorting
        if ($this->sortField === 'gap') {
            $gapSql = "($proratedTargetSql - COALESCE($achievementSql, 0))";
            $query->orderByRaw("$gapSql {$this->sortDirection}");
        } elseif ($this->sortField === 'total_target') {
            $query->orderByRaw("COALESCE(l.total_target, 0) {$this->sortDirection}");
        } elseif ($this->sortField === 'prorated_target') {
            $query->orderByRaw("COALESCE($proratedTargetSql, 0) {$this->sortDirection}");
        } elseif ($this->sortField === 'actual') {
            $query->orderByRaw("COALESCE($achievementSql, 0) {$this->sortDirection}");
        } elseif ($this->sortField === 'percent') {
            $query->orderByRaw("COALESCE($progressExpr, 0) {$this->sortDirection}");
        } elseif ($this->sortField === 'wilayah') {
            $query->orderBy('md.region_name', 'asc')
                  ->orderBy('md.area_name', 'asc')
                  ->orderBy('md.supervisor_name', 'asc')
                  ->orderBy('md.distributor_name', 'asc')
                  ->orderBy('l.customer_name', 'asc');
        } else {
            // Default fallback
            $query->orderBy('l.customer_name', 'asc');
        }

        $records = $query->paginate(100);

        $kpiQuery->select(
            DB::raw('COUNT(DISTINCT l.customer_code) as total_toko'),
            DB::raw("SUM((l.total_target / 3.0) * $multiplier) as total_target"),
            DB::raw("$kpiAchievementSql as total_achievement"),
            DB::raw("COUNT(DISTINCT CASE WHEN $achievementSql > 0 THEN l.customer_code END) as toko_transaksi"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 100 THEN l.customer_code END) as toko_hijau"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100 THEN l.customer_code END) as toko_kuning"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) < 80 THEN l.customer_code END) as toko_merah")
        );
        $kpi = $kpiQuery->first() ?? (object)[
            'total_toko' => 0, 
            'total_target' => 0, 
            'total_achievement' => 0,
            'toko_transaksi' => 0,
            'toko_hijau' => 0,
            'toko_kuning' => 0,
            'toko_merah' => 0
        ];

        // Month Labels
        $firstMonthOfSelectedQ = ($kuartal - 1) * 3 + 1;
        $monthLabels = [
            date('M', mktime(0, 0, 0, $firstMonthOfSelectedQ, 1)),
            date('M', mktime(0, 0, 0, $firstMonthOfSelectedQ + 1, 1)),
            date('M', mktime(0, 0, 0, $firstMonthOfSelectedQ + 2, 1)),
        ];
        
        // Active Quarter context
        $activeQuarterLabel = "Q{$kuartal} " . date('Y');
        if ($kuartal === $currentQuarter) {
             $activeQuarterLabel .= " • Bulan ke-{$multiplier} (" . date('M') . ")";
        }

        return view('livewire.rwo.pencapaianrwo', [
            'records' => $records,
            'kpi' => $kpi,
            'monthLabels' => $monthLabels,
            'activeQuarterLabel' => $activeQuarterLabel
        ])->layout('layouts.app');
    }

    public function export()
    {
        $query = $this->getStoreQuery((string)$this->appliedKuartal, false);
        $this->applyAccessScope($query);
        $this->applyRwoFilters($query, $this->getCurrentFiltersArray());

        $query->orderBy('md.region_name', 'asc')
              ->orderBy('md.area_name', 'asc')
              ->orderBy('md.supervisor_name', 'asc')
              ->orderBy('l.distributor_code', 'asc');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PencapaianRwoExport($query, $this->appliedKuartal),
            'Pencapaian_RWO_Q' . $this->appliedKuartal . '_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function loadMapData()
    {
        $filters = [
            'appliedKuartal' => $this->appliedKuartal,
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedSupervisor' => $this->appliedSupervisor,
            'appliedDistributor' => $this->appliedDistributor,
            'search' => $this->search,
            'appliedStatusSkb' => $this->appliedStatusSkb,
            'appliedStatusData' => $this->appliedStatusData,
            'appliedStatusReward' => $this->appliedStatusReward,
            'appliedStatusProgress' => $this->appliedStatusProgress,
            'appliedStatusTransaksi' => $this->appliedStatusTransaksi,
        ];
        
        $this->dispatch('load-map-data', filters: $filters)->to(PencapaianrwoMap::class);
}
}
