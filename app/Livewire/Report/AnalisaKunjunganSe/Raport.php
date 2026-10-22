<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeRaportExport;

class Raport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    protected $menuRoute = 'report.analisa-kunjungan-se.index';

    public $canExport = false;
    public $accessLevel = 'nasional';
    public $allowedRegionCodes = [];
    public $allowedAreaCodes = [];
    public $allowedDistributorCodes = [];

    // Filter Options
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $salesmen = [];
    public $yearOptions = [];
    public $monthOptions = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    // Selected Filters
    public $selectedYear;
    public $selectedMonth;
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    public $selectedSalesman = '';

    // Applied Filters
    public $appliedYear;
    public $appliedMonth;
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedDistributor = '';
    public $appliedSalesman = '';

    // UI State
    public $gradeFilter = 'all'; // 'all', 'A', 'B', 'C', 'D'
    public $search = '';
    public $sortField = 'skor_total';
    public $sortDirection = 'desc';
    public $perPage = 25;

    // Modal State
    public $showModal = false;
    public $modalData = null;

    public function mount()
    {
        $this->authorizeAction('can_view');
        $this->canExport = auth()->check() && auth()->user()->hasMenuAccess($this->menuRoute, 'can_export');
        $this->resolveUserAccess();

        $this->yearOptions = DB::table('master_calender')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($this->yearOptions)) {
            $this->yearOptions = [(int) date('Y')];
        }

        $this->monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $defaultYear = in_array((int)date('Y'), $this->yearOptions) ? (int)date('Y') : $this->yearOptions[0];
        $this->selectedYear = (int) request()->query('year', $defaultYear);
        $this->selectedMonth = (int) request()->query('month', date('n'));

        $this->loadRegions();

        // Restore filter dari session jika ada
        if (empty($this->selectedRegion) && session()->has('analisa_kunjungan_se_shared_filter')) {
            $saved = session()->get('analisa_kunjungan_se_shared_filter');
            $this->selectedRegion = $saved['region'] ?? '';
            $this->selectedArea = $saved['area'] ?? '';
            $this->selectedDistributor = $saved['distributor'] ?? '';
            $this->selectedSalesman = $saved['salesman'] ?? '';

            if (!empty($saved['startDate'])) {
                try {
                    $d = Carbon::parse($saved['startDate']);
                    $this->selectedYear = $d->year;
                    $this->selectedMonth = $d->month;
                } catch (\Exception $e) {}
            }
        }

        // URL query params override
        $urlRegion = request()->query('region');
        $urlArea = request()->query('area');
        $urlDistributor = request()->query('distributor');
        $urlSalesman = request()->query('salesman');
        $urlStartDate = request()->query('startDate');

        if ($urlRegion) $this->selectedRegion = $urlRegion;
        if ($urlArea) $this->selectedArea = $urlArea;
        if ($urlDistributor) $this->selectedDistributor = $urlDistributor;
        if ($urlSalesman) $this->selectedSalesman = $urlSalesman;

        if ($urlStartDate) {
            try {
                $d = Carbon::parse($urlStartDate);
                $this->selectedYear = $d->year;
                $this->selectedMonth = $d->month;
            } catch (\Exception $e) {}
        }

        if (!empty($this->selectedRegion)) {
            $this->updatedSelectedRegion($this->selectedRegion);
            if (!empty($this->selectedArea)) {
                $this->updatedSelectedArea($this->selectedArea);
                if (!empty($this->selectedDistributor)) {
                    $this->updatedSelectedDistributor($this->selectedDistributor);
                }
            }
            $this->applyFilter(forceRefresh: false);
        }
    }

    public function resolveUserAccess()
    {
        $user = auth()->user();
        if (!$user) return;

        $this->accessLevel = $user->access_level ?? 'nasional';

        if ($this->accessLevel === 'region') {
            $this->allowedRegionCodes = DB::table('user_regions')
                ->where('user_id', $user->id)
                ->pluck('region_code')
                ->toArray();
        } elseif ($this->accessLevel === 'area') {
            $this->allowedAreaCodes = DB::table('user_areas')
                ->where('user_id', $user->id)
                ->pluck('area_code')
                ->toArray();

            $this->allowedRegionCodes = DB::table('master_areas')
                ->whereIn('area_code', $this->allowedAreaCodes)
                ->pluck('region_code')
                ->unique()
                ->toArray();
        } elseif ($this->accessLevel === 'supervisor') {
            $hierarchy = DB::table('vw_distributor_hierarchy')
                ->where('supervisor_code', $user->supervisor_code)
                ->get();

            $this->allowedRegionCodes = $hierarchy->pluck('region_code')->unique()->filter()->values()->toArray();
            $this->allowedAreaCodes = $hierarchy->pluck('area_code')->unique()->filter()->values()->toArray();
            $this->allowedDistributorCodes = $hierarchy->pluck('distributor_code')->unique()->filter()->values()->toArray();

            if (empty($this->allowedRegionCodes)) {
                $mapping = DB::table('team_elite_code_mappings')
                    ->where('team_elite_code', $user->supervisor_code)
                    ->first();
                if ($mapping) {
                    if ($mapping->region_code) $this->allowedRegionCodes = [$mapping->region_code];
                    if ($mapping->area_code) $this->allowedAreaCodes = [$mapping->area_code];
                }
            }
        }
    }

    protected function authorizeAction(string $action): void
    {
        if (auth()->check() && !auth()->user()->hasMenuAccess($this->menuRoute, $action)) {
            abort(403, 'Akses tidak diizinkan');
        }
    }

    public function loadRegions()
    {
        $cacheKey = "raport_rpt_regions_" . ($this->accessLevel !== 'nasional' ? implode('_', $this->allowedRegionCodes) : 'all');

        $this->regions = Cache::remember($cacheKey, 3600, function () {
            $query = DB::table('rpt_visit_an_h')
                ->where('RID', '!=', 'HOINA')
                ->whereNotNull('RID')
                ->whereNotNull('RNAME');

            if ($this->accessLevel !== 'nasional' && !empty($this->allowedRegionCodes)) {
                $query->whereIn('RID', $this->allowedRegionCodes);
            }

            return $query->select('RID as region_code', 'RNAME as region_name')
                ->distinct()
                ->orderBy('RNAME')
                ->get()
                ->toArray();
        });

        if (count($this->regions) === 1 && empty($this->selectedRegion)) {
            $this->selectedRegion = $this->regions[0]->region_code;
            $this->updatedSelectedRegion($this->selectedRegion);
        }
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->areas = [];
        $this->distributors = [];
        $this->salesmen = [];

        if ($value) {
            if ($this->accessLevel !== 'nasional' && !empty($this->allowedRegionCodes) && !in_array($value, $this->allowedRegionCodes)) {
                $this->selectedRegion = '';
                return;
            }

            $cacheKey = "raport_rpt_areas_{$value}_" . implode('_', $this->allowedAreaCodes);

            $this->areas = Cache::remember($cacheKey, 3600, function () use ($value) {
                $query = DB::table('rpt_visit_an_h')
                    ->where('RID', '!=', 'HOINA')
                    ->where('RID', $value)
                    ->whereNotNull('EID')
                    ->whereNotNull('ENAME');

                if ($this->accessLevel === 'area' && !empty($this->allowedAreaCodes)) {
                    $query->whereIn('EID', $this->allowedAreaCodes);
                } elseif ($this->accessLevel === 'supervisor' && !empty($this->allowedAreaCodes)) {
                    $query->whereIn('EID', $this->allowedAreaCodes);
                }

                return $query->select('EID as area_code', 'ENAME as area_name')
                    ->distinct()
                    ->orderBy('ENAME')
                    ->get()
                    ->toArray();
            });

            if (count($this->areas) === 1 && empty($this->selectedArea)) {
                $this->selectedArea = $this->areas[0]->area_code;
                $this->updatedSelectedArea($this->selectedArea);
            }
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->distributors = [];
        $this->salesmen = [];

        if ($value && $this->selectedRegion) {
            $cacheKey = "raport_rpt_distributors_{$this->selectedRegion}_{$value}_" . implode('_', $this->allowedDistributorCodes);

            $this->distributors = Cache::remember($cacheKey, 3600, function () use ($value) {
                $query = DB::table('rpt_visit_an_h')
                    ->where('RID', '!=', 'HOINA')
                    ->where('RID', $this->selectedRegion)
                    ->where('EID', $value)
                    ->whereNotNull('BID')
                    ->whereNotNull('BNAME');

                if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
                    $query->whereIn('BID', $this->allowedDistributorCodes);
                }

                return $query->select('BID as distributor_code', 'BNAME as distributor_name')
                    ->distinct()
                    ->orderBy('BNAME')
                    ->get()
                    ->toArray();
            });

            $this->loadSalesmenList();
        }
    }

    public function updatedSelectedDistributor($value)
    {
        $this->selectedSalesman = '';
        $this->loadSalesmenList();
    }

    protected function loadSalesmenList()
    {
        $this->salesmen = [];
        if (!$this->selectedRegion || !$this->selectedArea) return;

        $cacheKey = "raport_rpt_salesmen_{$this->selectedRegion}_{$this->selectedArea}_" . ($this->selectedDistributor ?: 'all');

        $this->salesmen = Cache::remember($cacheKey, 1800, function () {
            $query = DB::table('rpt_visit_an_h')
                ->where('RID', '!=', 'HOINA')
                ->where('RID', $this->selectedRegion)
                ->where('EID', $this->selectedArea)
                ->whereNotNull('MUID')
                ->whereNotNull('MUNAME');

            if ($this->selectedDistributor) {
                $query->where('BID', $this->selectedDistributor);
            } elseif ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
                $query->whereIn('BID', $this->allowedDistributorCodes);
            }

            return $query->select('MUID as salesman_code', 'MUNAME as salesman_name')
                ->distinct('MUID')
                ->orderBy('MUID')
                ->orderBy('TANGGAL', 'desc')
                ->orderBy('ID', 'desc')
                ->get()
                ->sortBy('salesman_name')
                ->values()
                ->toArray();
        });
    }

    public function applyFilter(bool $forceRefresh = true)
    {
        if (empty($this->selectedRegion) || empty($this->selectedArea)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Pilih Region dan Area terlebih dahulu untuk menampilkan Raport Kunjungan SE.'
            ]);
            return;
        }

        $this->appliedYear = (int) $this->selectedYear;
        $this->appliedMonth = (int) $this->selectedMonth;
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor;
        $this->appliedSalesman = $this->selectedSalesman;

        $this->saveSharedFilter();

        if ($forceRefresh) {
            $this->resetPage();
        }
    }

    public function resetFilter()
    {
        $defaultYear = in_array((int)date('Y'), $this->yearOptions) ? (int)date('Y') : $this->yearOptions[0];
        $this->selectedYear = $defaultYear;
        $this->selectedMonth = (int) date('n');

        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->areas = [];
        $this->distributors = [];
        $this->salesmen = [];

        $this->appliedYear = null;
        $this->appliedMonth = null;
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedDistributor = '';
        $this->appliedSalesman = '';

        $this->gradeFilter = 'all';
        $this->search = '';

        session()->forget('analisa_kunjungan_se_shared_filter');
        $this->loadRegions();
        $this->resetPage();
    }

    protected function saveSharedFilter(): void
    {
        $startDate = sprintf('%04d-%02d-01', $this->appliedYear, $this->appliedMonth);
        $endDate = Carbon::create($this->appliedYear, $this->appliedMonth, 1)->endOfMonth()->format('Y-m-d');

        session()->put('analisa_kunjungan_se_shared_filter', [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'distributor' => $this->appliedDistributor,
            'salesman' => $this->appliedSalesman,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function setGradeFilter(string $grade)
    {
        $this->gradeFilter = $grade;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['salesman_name', 'salesman_code', 'distributor_name']) ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    #[Computed]
    public function allRaportData()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            return collect([]);
        }

        $startDate = sprintf('%04d-%02d-01', $this->appliedYear, $this->appliedMonth);
        $endDate = Carbon::create($this->appliedYear, $this->appliedMonth, 1)->endOfMonth()->format('Y-m-d');

        $userScope = auth()->id() ?? 'guest';
        $roleScope = $this->accessLevel ?? 'nasional';
        $cacheKey = "raport_full_v5_{$userScope}_{$roleScope}_{$this->appliedRegion}_{$this->appliedArea}_" . 
            ($this->appliedDistributor ?: 'all') . "_" . 
            ($this->appliedSalesman ?: 'all') . "_{$this->appliedYear}_{$this->appliedMonth}";

        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            // 1. Target Hari Kerja
            $targetHariKerja = DB::table('master_calender')
                ->whereBetween('date', [$startDate, $endDate])
                ->where('day_number', '!=', 7)
                ->where(function ($q) {
                    $q->whereNull('libur_nasional')->orWhere('libur_nasional', 0);
                })
                ->count();
            if ($targetHariKerja <= 0) $targetHariKerja = 25;

            // 2. Latest SE Names (Deduplicated Single SE)
            $latestNames = DB::table('rpt_visit_an_h')
                ->select('MUID', 'MUNAME')
                ->distinct('MUID')
                ->where('RID', '!=', 'HOINA')
                ->where('RID', $this->appliedRegion)
                ->where('EID', $this->appliedArea)
                ->whereNotNull('MUID')
                ->orderBy('MUID')
                ->orderBy('TANGGAL', 'desc')
                ->orderBy('ID', 'desc')
                ->pluck('MUNAME', 'MUID');

            // 3. Quantitative Summary per SE
            $query = DB::table('rpt_visit_an_h as rvah')
                ->where('rvah.RID', $this->appliedRegion)
                ->where('rvah.EID', $this->appliedArea)
                ->whereBetween('rvah.TANGGAL', [$startDate, $endDate]);

            if (!empty($this->appliedDistributor)) {
                $query->where('rvah.BID', $this->appliedDistributor);
            } elseif ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
                $query->whereIn('rvah.BID', $this->allowedDistributorCodes);
            }

            if (!empty($this->appliedSalesman)) {
                $query->where('rvah.MUID', $this->appliedSalesman);
            }

            $rawSummary = $query->select(
                'rvah.MUID as salesman_code',
                'rvah.BID as distributor_code',
                DB::raw('MAX(rvah."BNAME") as distributor_name'),
                DB::raw('MAX(rvah."ENAME") as area_name'),
                DB::raw('MAX(rvah."RNAME") as region_name'),
                DB::raw('COUNT(CASE WHEN rvah."FLAG_PJP" = \'R\' THEN 1 END) as total_pc'),
                DB::raw('COUNT(CASE WHEN rvah."FLAG_PJP" = \'R\' AND rvah."FLAG_VISIT" = \'Y\' THEN 1 END) as total_ac'),
                DB::raw('COUNT(CASE WHEN rvah."FLAG_PJP" = \'R\' AND rvah."FLAG_VISIT" = \'Y\' AND rvah."FLAG_EC" = \'Y\' THEN 1 END) as total_ec'),
                DB::raw('COALESCE(SUM(rvah."ORDER_VAL"), 0) as total_value'),
                DB::raw('COALESCE(SUM(rvah."ORDER_QTY"), 0) as total_qty'),
                DB::raw('COUNT(DISTINCT CASE WHEN rvah."FLAG_VISIT" = \'Y\' THEN rvah."TANGGAL"::date END) as hari_aktif'),
                DB::raw('COALESCE(SUM(EXTRACT(EPOCH FROM rvah."TIME_CONSUME"::time)), 0) as seconds_consume'),
                DB::raw('COALESCE(SUM(EXTRACT(EPOCH FROM rvah."TIME_TRAVEL"::time)), 0) as seconds_travel')
            )
            ->groupBy('rvah.MUID', 'rvah.BID')
            ->get();

            // Benchmark Average Value Area
            $avgAreaValue = $rawSummary->where('total_value', '>', 0)->avg('total_value');
            if (!$avgAreaValue || $avgAreaValue <= 0) {
                $avgAreaValue = null;
            }

            // 4. Anomaly Findings
            $findingsObj = new \App\Livewire\Report\AnalisaKunjunganSe\Findings();
            $findingsObj->appliedRegion = $this->appliedRegion;
            $findingsObj->appliedArea = $this->appliedArea;
            $findingsObj->appliedDistributor = $this->appliedDistributor;
            $findingsObj->appliedSalesman = $this->appliedSalesman;
            $findingsObj->appliedStartDate = $startDate;
            $findingsObj->appliedEndDate = $endDate;
            $findingsObj->accessLevel = $this->accessLevel;
            $findingsObj->allowedDistributorCodes = $this->allowedDistributorCodes;
            $findings = $findingsObj->allFindings();

            $findingsGrouped = $findings->groupBy(fn($f) => ($f->distributor_code ?? '') . '_' . ($f->salesman_code ?? ''));

            $results = [];

            foreach ($rawSummary as $row) {
                $seKey = ($row->distributor_code ?? '') . '_' . ($row->salesman_code ?? '');
                $seFindings = $findingsGrouped->get($seKey, collect([]));

                $pc = (int)$row->total_pc;
                $ac = (int)$row->total_ac;
                $ec = (int)$row->total_ec;
                $val = (float)$row->total_value;
                $qty = (int)$row->total_qty;
                $hariAktif = (int)$row->hari_aktif;
                $secConsume = (float)$row->seconds_consume;
                $secTravel = (float)$row->seconds_travel;
                $secTotal = $secConsume + $secTravel;

                // Anomali breakdown
                $depoCount = 0;
                $kembarCount = 0;
                $radiusCount = 0;
                $kilatCount = 0;
                $kilatEcCount = 0;
                $kilatNonEcCount = 0;
                $noGpsCount = 0;

                foreach ($seFindings as $f) {
                    $types = $f->anomaly_types ?? [];
                    if (in_array('at_distributor', $types)) $depoCount++;
                    if (in_array('duplicate_coord', $types)) $kembarCount++;
                    if (in_array('out_of_radius', $types)) $radiusCount++;
                    if (in_array('quick_visit', $types)) {
                        $kilatCount++;
                        if (($f->flag_ec ?? '') === 'Y') {
                            $kilatEcCount++;
                        } else {
                            $kilatNonEcCount++;
                        }
                    }
                    if (in_array('no_gps', $types)) $noGpsCount++;
                }

                $totalAnomalies = $depoCount + $kembarCount + $radiusCount + $kilatCount + $noGpsCount;

                // Handle Edge Case: SE dengan 0 kunjungan (tidak ada aktivitas)
                if ($ac <= 0) {
                    $scoreP1 = 0.0;
                    $scoreP2 = 0.0;
                    $scoreP3 = 0.0;
                    $scoreP4 = 0.0;
                    $scoreAcPc = 0.0;
                    $scoreHariAktif = 0.0;
                    $scoreSr = 0.0;
                    $scoreVal = 0.0;
                    $valRatio = 0.0;
                    $penaltyRate = 0.0;
                    $scoreStoreRatio = 0.0;
                    $scoreDuration = 0.0;
                    $totalScore = 0.0;
                    $grade = 'D';
                    $gradeLabel = 'Kurang (Tidak Ada Kunjungan)';
                    $gradeColor = 'rose';
                    $cleanRate = 0.0;
                    $pctAcPc = 0.0;
                    $strikeRate = 0.0;
                    $dropSize = 0.0;
                    $pctStore = 0.0;
                    $avgMinutesPerStore = 0.0;
                    $strengths = ['Belum ada riwayat aktivitas kunjungan di lapangan pada periode ini.'];
                    $weaknesses = ['Realisasi kunjungan toko 0 (belum ada aktivitas di lapangan).'];
                    $spvRec = 'Investigasi status penugasan dan konfirmasi kendala operasional Sales Executive.';
                } else {
                    // --- PILAR 1: PRODUKTIVITAS KUNJUNGAN (Bobot: 30%) ---
                    $pctAcPc = ($pc > 0) ? ($ac / $pc) * 100 : 0;
                    if ($pctAcPc >= 100) $scoreAcPc = 100;
                    elseif ($pctAcPc >= 90) $scoreAcPc = 85 + (($pctAcPc - 90) * 1.5);
                    elseif ($pctAcPc >= 80) $scoreAcPc = 70 + (($pctAcPc - 80) * 1.5);
                    else $scoreAcPc = min(70, ($pctAcPc / 80) * 70);

                    $scoreHariAktif = min(100, ($hariAktif / max(1, $targetHariKerja)) * 100);
                    $scoreP1 = ($scoreAcPc * 0.8) + ($scoreHariAktif * 0.2);

                    // --- PILAR 2: EFEKTIVITAS PENJUALAN (Bobot: 30%) ---
                    $strikeRate = ($ac > 0) ? ($ec / $ac) * 100 : 0;
                    if ($strikeRate >= 50) $scoreSr = 100;
                    elseif ($strikeRate >= 40) $scoreSr = 85 + (($strikeRate - 40) * 1.5);
                    elseif ($strikeRate >= 30) $scoreSr = 70 + (($strikeRate - 30) * 1.5);
                    elseif ($strikeRate >= 20) $scoreSr = 55 + (($strikeRate - 20) * 1.5);
                    else $scoreSr = max(20, $strikeRate * 2.75);

                    if ($val <= 0) {
                        $scoreVal = 0;
                        $valRatio = 0;
                    } elseif ($avgAreaValue !== null) {
                        $valRatio = $val / $avgAreaValue;
                        if ($valRatio >= 1.3) $scoreVal = 100;
                        elseif ($valRatio >= 1.0) $scoreVal = 85 + (($valRatio - 1.0) / 0.3 * 15);
                        elseif ($valRatio >= 0.7) $scoreVal = 70 + (($valRatio - 0.7) / 0.3 * 15);
                        elseif ($valRatio >= 0.4) $scoreVal = 55 + (($valRatio - 0.4) / 0.3 * 15);
                        else $scoreVal = max(30, $valRatio * 100);
                    } else {
                        // Tidak ada pembanding penjualan di area (hanya SE ini yang bertransaksi)
                        $scoreVal = 85;
                        $valRatio = 1.0;
                    }

                    $dropSize = ($ec > 0) ? ($val / $ec) : 0;
                    $scoreP2 = ($scoreSr * 0.6) + ($scoreVal * 0.4);

                    // --- PILAR 3: KEPATUHAN SOP & INTEGRITAS (Bobot: 25%) ---
                    $distinctDirtyVisits = $seFindings->count();
                    $cleanVisitsCount = max(0, $ac - $distinctDirtyVisits);
                    $cleanRate = round(($cleanVisitsCount / $ac) * 100, 1);

                    $acDiv = max(1, $ac);
                    $penaltyRate = (($depoCount / $acDiv) * 100 * 3.0) + 
                                   (($kembarCount / $acDiv) * 100 * 2.5) + 
                                   (($radiusCount / $acDiv) * 100 * 1.0) + 
                                   (($kilatEcCount / $acDiv) * 100 * 2.0) + 
                                   (($kilatNonEcCount / $acDiv) * 100 * 0.5) +
                                   (($noGpsCount / $acDiv) * 100 * 0.2);
                    $scoreP3 = round(max(0, 100 - $penaltyRate), 1);

                    // --- PILAR 4: MANAJEMEN WAKTU KERJA (Bobot: 15%) ---
                    if ($secTotal <= 0) {
                        $pctStore = 0.0;
                        $scoreStoreRatio = 0.0;
                        $avgMinutesPerStore = 0.0;
                        $scoreDuration = 0.0;
                    } else {
                        $pctStore = ($secConsume / $secTotal) * 100;
                        if ($pctStore >= 50) $scoreStoreRatio = 100;
                        elseif ($pctStore >= 40) $scoreStoreRatio = 80 + (($pctStore - 40) * 2.0);
                        elseif ($pctStore >= 30) $scoreStoreRatio = 60 + (($pctStore - 30) * 2.0);
                        else $scoreStoreRatio = max(25, $pctStore * 2.0);

                        $avgMinutesPerStore = ($secConsume / 60 / $ac);
                        if ($avgMinutesPerStore >= 7 && $avgMinutesPerStore <= 20) $scoreDuration = 100;
                        elseif (($avgMinutesPerStore >= 5 && $avgMinutesPerStore < 7) || ($avgMinutesPerStore > 20 && $avgMinutesPerStore <= 30)) $scoreDuration = 85;
                        elseif ($avgMinutesPerStore >= 3 && $avgMinutesPerStore < 5) $scoreDuration = 70;
                        else $scoreDuration = 50;
                    }

                    $scoreP4 = ($scoreStoreRatio * 0.6) + ($scoreDuration * 0.4);

                    // --- SKOR TOTAL (0 - 100) & GRADE ---
                    $totalScore = ($scoreP1 * 0.30) + ($scoreP2 * 0.30) + ($scoreP3 * 0.25) + ($scoreP4 * 0.15);
                    $totalScore = round($totalScore, 1);

                    if ($totalScore >= 85.0) {
                        $grade = 'A';
                        $gradeLabel = 'Sangat Baik (Role Model)';
                        $gradeColor = 'emerald';
                    } elseif ($totalScore >= 70.0) {
                        $grade = 'B';
                        $gradeLabel = 'Baik (Memenuhi Standar)';
                        $gradeColor = 'blue';
                    } elseif ($totalScore >= 55.0) {
                        $grade = 'C';
                        $gradeLabel = 'Cukup (Perlu Evaluasi)';
                        $gradeColor = 'amber';
                    } else {
                        $grade = 'D';
                        $gradeLabel = 'Kurang (Pembinaan Khusus)';
                        $gradeColor = 'rose';
                    }

                    // Strengths
                    $strengths = [];
                    if ($scoreP1 >= 95) $strengths[] = 'Kepatuhan rute PJP sempurna (' . round($pctAcPc, 1) . '% tercapai).';
                    elseif ($scoreP1 >= 88) $strengths[] = 'Realisasi kunjungan sangat baik dan konsisten.';
                    if ($strikeRate >= 40) $strengths[] = 'Efektivitas penjualan tinggi (Strike Rate: ' . round($strikeRate, 1) . '%).';
                    if (isset($valRatio) && $valRatio >= 1.0) $strengths[] = 'Total omset penjualan berada di atas rata-rata area.';
                    if ($scoreP3 >= 70) $strengths[] = 'Integritas GPS kunjungan terpelihara dengan baik (' . $cleanRate . '% kunjungan bersih).';
                    if ($scoreP4 >= 80) $strengths[] = 'Alokasi waktu kerja sangat efisien di toko pelanggan (' . round($pctStore, 1) . '% di toko).';
                    if (empty($strengths)) $strengths[] = 'Menyelesaikan operasional bulanan sesuai jadwal penugasan.';

                    // Weaknesses / Areas for Improvement
                    $weaknesses = [];
                    if ($depoCount > 0) $weaknesses[] = "Terdeteksi {$depoCount} check-in di titik depo distributor.";
                    if ($kembarCount > 5) $weaknesses[] = "Terdeteksi {$kembarCount} koordinat kembar di toko berbeda.";
                    if ($radiusCount > 5) $weaknesses[] = "Terdapat {$radiusCount} kunjungan di luar radius master (> 50m).";
                    if ($kilatCount > 30) $weaknesses[] = "Banyak kunjungan kilat < 2 menit ({$kilatCount} kali), kualitas interaksi berpotensi rendah.";
                    if ($strikeRate < 30) $weaknesses[] = 'Strike rate order masih di bawah 30% (' . round($strikeRate, 1) . '%), perlu peningkatan teknik penawaran.';
                    if ($pctAcPc < 85) $weaknesses[] = 'Pencapaian rute PJP masih rendah (' . round($pctAcPc, 1) . '%), banyak toko terlewat.';
                    if (empty($weaknesses)) $weaknesses[] = 'Tidak ditemukan kelemahan operasional yang signifikan.';

                    // Supervisor Action Recommendations
                    if ($grade === 'A') {
                        $spvRec = 'Prioritas penerima insentif penuh / reward bulanan. Rekomendasikan sebagai role model best practice tim penjualan.';
                    } elseif ($grade === 'B') {
                        $spvRec = 'Performa stabil sesuai standar operasional. Dorong peningkatan basket size order dan kebersihan integritas GPS.';
                    } elseif ($grade === 'C') {
                        $spvRec = 'Lakukan evaluasi & joint-visit lapangan bersama SPV untuk membedah teknik closing transaksi dan validasi titik toko.';
                    } else {
                        $spvRec = 'Berikan sesi pembinaan khusus / teguran tertulis. Wajibkan verifikasi fisik rute kunjungan dan ketertiban titik GPS.';
                    }
                }

                $name = $latestNames[$row->salesman_code] ?? $row->salesman_name ?? $row->salesman_code;

                $results[] = (object) [
                    'salesman_code' => $row->salesman_code,
                    'salesman_name' => $name,
                    'distributor_code' => $row->distributor_code,
                    'distributor_name' => $row->distributor_name,
                    'area_code' => $this->appliedArea,
                    'area_name' => $row->area_name,
                    'region_code' => $this->appliedRegion,
                    'region_name' => $row->region_name,
                    'period_month' => $this->appliedMonth,
                    'period_year' => $this->appliedYear,
                    'period_label' => $this->monthOptions[$this->appliedMonth] . ' ' . $this->appliedYear,

                    // Raw metrics
                    'total_pc' => $pc,
                    'total_ac' => $ac,
                    'total_ec' => $ec,
                    'pct_ac_pc' => round($pctAcPc, 1),
                    'strike_rate' => round($strikeRate, 1),
                    'total_value' => $val,
                    'total_qty' => $qty,
                    'drop_size' => round($dropSize),
                    'hari_aktif' => $hariAktif,
                    'target_hari_kerja' => $targetHariKerja,
                    'pct_store_time' => round($pctStore, 1),
                    'avg_minutes_per_store' => round($avgMinutesPerStore, 1),

                    // Anomalies
                    'count_depo' => $depoCount,
                    'count_kembar' => $kembarCount,
                    'count_radius' => $radiusCount,
                    'count_kilat' => $kilatCount,
                    'count_no_gps' => $noGpsCount,
                    'total_anomalies' => $totalAnomalies,
                    'clean_rate' => $cleanRate,

                    // 4 Pillars Scores (0 - 100)
                    'score_pilar_1' => round($scoreP1, 1),
                    'score_pilar_2' => round($scoreP2, 1),
                    'score_pilar_3' => round($scoreP3, 1),
                    'score_pilar_4' => round($scoreP4, 1),

                    // Sub-scores for formula transparency
                    'score_ac_pc' => round($scoreAcPc, 1),
                    'score_hari_aktif' => round($scoreHariAktif, 1),
                    'score_strike_rate' => round($scoreSr, 1),
                    'score_omset' => round($scoreVal, 1),
                    'avg_area_value' => round($avgAreaValue ?? 0),
                    'val_ratio' => round($valRatio ?? 0, 2),
                    'penalty_rate' => round($penaltyRate, 1),
                    'count_kilat_ec' => $kilatEcCount,
                    'count_kilat_non_ec' => $kilatNonEcCount,
                    'score_store_ratio' => round($scoreStoreRatio, 1),
                    'score_duration' => round($scoreDuration, 1),

                    // Total & Grade
                    'skor_total' => $totalScore,
                    'grade' => $grade,
                    'grade_label' => $gradeLabel,
                    'grade_color' => $gradeColor,

                    // Analysis
                    'strengths' => $strengths,
                    'weaknesses' => $weaknesses,
                    'spv_recommendation' => $spvRec,
                ];
            }

            // Assign overall rank sorted by total score descending
            $collection = collect($results)->sortByDesc('skor_total')->values();
            foreach ($collection as $idx => $item) {
                $item->rank = $idx + 1;
            }

            return $collection;
        });
    }

    #[Computed]
    public function summaryMetrics()
    {
        $all = $this->allRaportData();
        if ($all->isEmpty()) {
            return [
                'total_se' => 0,
                'avg_score' => 0,
                'grade_a' => 0,
                'grade_b' => 0,
                'grade_c' => 0,
                'grade_d' => 0,
                'top_performer' => null,
                'needs_coaching' => null,
            ];
        }

        $top = $all->first();
        $bottom = $all->sortBy('skor_total')->first();

        return [
            'total_se' => $all->count(),
            'avg_score' => round($all->avg('skor_total'), 1),
            'grade_a' => $all->where('grade', 'A')->count(),
            'grade_b' => $all->where('grade', 'B')->count(),
            'grade_c' => $all->where('grade', 'C')->count(),
            'grade_d' => $all->where('grade', 'D')->count(),
            'top_performer' => $top,
            'needs_coaching' => $bottom,
        ];
    }

    #[Computed]
    public function filteredRaportData()
    {
        $collection = $this->allRaportData();

        // Filter Grade
        if ($this->gradeFilter !== 'all') {
            $collection = $collection->where('grade', $this->gradeFilter);
        }

        // Search
        if (!empty($this->search)) {
            $needle = mb_strtolower(trim($this->search));
            $collection = $collection->filter(function ($item) use ($needle) {
                return str_contains(mb_strtolower($item->salesman_name ?? ''), $needle) ||
                       str_contains(mb_strtolower($item->salesman_code ?? ''), $needle) ||
                       str_contains(mb_strtolower($item->distributor_name ?? ''), $needle) ||
                       str_contains(mb_strtolower($item->distributor_code ?? ''), $needle);
            });
        }

        // Sort
        if ($this->sortDirection === 'asc') {
            $collection = $collection->sortBy($this->sortField)->values();
        } else {
            $collection = $collection->sortByDesc($this->sortField)->values();
        }

        return $collection;
    }

    public function openRaport(string $salesmanCode, string $distributorCode)
    {
        $item = $this->allRaportData()->first(function ($r) use ($salesmanCode, $distributorCode) {
            return $r->salesman_code === $salesmanCode && $r->distributor_code === $distributorCode;
        });

        if ($item) {
            $this->modalData = $item;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->modalData = null;
    }

    public function exportExcel()
    {
        if (!$this->canExport) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Anda tidak memiliki hak akses ekspor.']);
            return;
        }

        $data = $this->filteredRaportData();
        if ($data->isEmpty()) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'Tidak ada data untuk diekspor.']);
            return;
        }

        $meta = [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'period' => ($this->monthOptions[$this->appliedMonth] ?? '') . ' ' . $this->appliedYear,
        ];

        $filename = 'Raport_SE_' . $this->appliedRegion . '_' . $this->appliedArea . '_' . $this->appliedYear . '_' . sprintf('%02d', $this->appliedMonth) . '.xlsx';

        return Excel::download(new AnalisaKunjunganSeRaportExport($data, $meta), $filename);
    }

    public function render()
    {
        $items = $this->filteredRaportData();
        $page = $this->getPage();
        $perPage = (int) $this->perPage;

        $paginated = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.report.analisa-kunjungan-se.raport', [
            'raportList' => $paginated,
            'metrics' => $this->summaryMetrics(),
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedSalesman' => $this->appliedSalesman,
            'appliedYear' => $this->appliedYear,
            'appliedMonth' => $this->appliedMonth,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedDistributor' => $this->selectedDistributor,
            'selectedSalesman' => $this->selectedSalesman,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'distributors' => $this->distributors,
            'salesmen' => $this->salesmen,
            'yearOptions' => $this->yearOptions,
            'monthOptions' => $this->monthOptions,
            'accessLevel' => $this->accessLevel,
            'gradeFilter' => $this->gradeFilter,
            'search' => $this->search,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage,
            'canExport' => $this->canExport,
            'showModal' => $this->showModal,
            'modalData' => $this->modalData,
        ])->layout('layouts.app');
    }
}
