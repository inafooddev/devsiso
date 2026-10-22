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
use App\Exports\AnalisaKunjunganSeTrackingExport;

class Tracking extends Component
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
    public $monthOptions = [];

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

    // Status Filter: 'all', 'solved', 'recurrent', 'new', 'normal'
    public $statusFilter = 'all';
    public $showAllStores = false; // Jika false, fokus pada toko yang pernah ada temuan anomali
    public $search = '';
    public $perPage = 25;

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

        // Restore filter dari session jika URL kosong
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
        $cacheKey = "tracking_rpt_regions_" . ($this->accessLevel !== 'nasional' ? implode('_', $this->allowedRegionCodes) : 'all');

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

            $cacheKey = "tracking_rpt_areas_{$value}_" . implode('_', $this->allowedAreaCodes);

            $this->areas = Cache::remember($cacheKey, 3600, function () use ($value) {
                $query = DB::table('rpt_visit_an_h')
                    ->where('RID', $value)
                    ->where('EID', '!=', 'HOINA')
                    ->whereNotNull('EID')
                    ->whereNotNull('ENAME');

                if (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes)) {
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
            if (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes) && !in_array($value, $this->allowedAreaCodes)) {
                $this->selectedArea = '';
                return;
            }

            $cacheKey = "tracking_rpt_dists_{$this->selectedRegion}_{$value}_" . implode('_', $this->allowedDistributorCodes);

            $this->distributors = Cache::remember($cacheKey, 3600, function () use ($value) {
                $query = DB::table('rpt_visit_an_h')
                    ->where('RID', $this->selectedRegion)
                    ->where('EID', $value)
                    ->where('BID', '!=', 'HOINA')
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

            if (count($this->distributors) === 1 && empty($this->selectedDistributor)) {
                $this->selectedDistributor = $this->distributors[0]->distributor_code;
                $this->updatedSelectedDistributor($this->selectedDistributor);
            } else {
                $this->updatedSelectedDistributor($this->selectedDistributor ?: '');
            }
        }
    }

    public function updatedSelectedDistributor($value)
    {
        $this->selectedSalesman = '';
        $this->salesmen = [];

        if ($this->selectedRegion && $this->selectedArea) {
            $distKey = $value ?: 'all';
            $cacheKey = "tracking_rpt_se_{$this->selectedRegion}_{$this->selectedArea}_{$distKey}";

            $this->salesmen = Cache::remember($cacheKey, 3600, function () use ($value) {
                $subQuery = DB::table('rpt_visit_an_h')
                    ->where('RID', $this->selectedRegion)
                    ->where('EID', $this->selectedArea)
                    ->where('MUID', '!=', 'HOINA')
                    ->whereNotNull('MUID')
                    ->whereNotNull('MUNAME');

                if (!empty($value)) {
                    $subQuery->where('BID', $value);
                }

                if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes) && empty($value)) {
                    $subQuery->whereIn('BID', $this->allowedDistributorCodes);
                }

                // Ambil single SE terbaru per kode MUID (jika ada pergantian personil, ambil nama yang paling baru)
                $subQuery->select(DB::raw('DISTINCT ON ("MUID") "MUID" as salesman_code, "MUNAME" as salesman_name'))
                    ->orderBy('MUID')
                    ->orderBy('TANGGAL', 'desc')
                    ->orderBy('ID', 'desc');

                return DB::table(DB::raw("({$subQuery->toSql()}) as latest_se"))
                    ->mergeBindings($subQuery)
                    ->orderBy('salesman_name', 'asc')
                    ->get()
                    ->toArray();
            });
        }
    }

    public function applyFilter(bool $forceRefresh = true)
    {
        if (empty($this->selectedRegion)) {
            $this->dispatch('notify', msg: 'Pilih Region terlebih dahulu.', type: 'warning');
            return;
        }

        if (empty($this->selectedArea)) {
            $this->dispatch('notify', msg: 'Pilih Area terlebih dahulu.', type: 'warning');
            return;
        }

        $this->appliedYear = (int)$this->selectedYear;
        $this->appliedMonth = (int)$this->selectedMonth;
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor ?: '';
        $this->appliedSalesman = $this->selectedSalesman ?: '';

        // Hitung start dan end date bulan tersebut untuk shared session
        $startDate = sprintf('%04d-%02d-01', $this->appliedYear, $this->appliedMonth);
        $endDate = Carbon::parse($startDate)->endOfMonth()->format('Y-m-d');

        session()->put('analisa_kunjungan_se_shared_filter', [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'distributor' => $this->appliedDistributor,
            'salesman' => $this->appliedSalesman,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        if ($forceRefresh) {
            Cache::forget($this->getCacheKey());
        }

        $this->resetPage();
    }

    public function resetFilters()
    {
        Cache::forget($this->getCacheKey());
        session()->forget('analisa_kunjungan_se_shared_filter');

        $this->selectedYear = (int) date('Y');
        $this->selectedMonth = (int) date('n');
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->statusFilter = 'all';
        $this->showAllStores = false;
        $this->search = '';

        $this->appliedYear = $this->selectedYear;
        $this->appliedMonth = $this->selectedMonth;
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedDistributor = '';
        $this->appliedSalesman = '';

        $this->resetPage();
    }

    public function setStatusFilter(string $status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function toggleShowAllStores()
    {
        $this->showAllStores = !$this->showAllStores;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getCacheKey(): string
    {
        $userId = auth()->id() ?? 'guest';
        return "se_tracking_v2_{$userId}_{$this->appliedYear}_{$this->appliedMonth}_{$this->appliedRegion}_{$this->appliedArea}_{$this->appliedDistributor}_{$this->appliedSalesman}";
    }

    /**
     * Mengambil pembagian minggu resmi (M1..M4/M5) dari master_calender secara kronologis
     */
    #[Computed]
    public function monthWeeks(): array
    {
        if (empty($this->appliedYear) || empty($this->appliedMonth)) {
            return [];
        }

        $weeksRaw = DB::table('master_calender')
            ->where('year', $this->appliedYear)
            ->where('month', $this->appliedMonth)
            ->select('week_year', DB::raw('MIN(date) as min_date'), DB::raw('MAX(date) as max_date'))
            ->groupBy('week_year')
            ->orderBy(DB::raw('MIN(date)'))
            ->get();

        $result = [];
        $wNum = 1;
        foreach ($weeksRaw as $w) {
            $startFormatted = Carbon::parse($w->min_date)->locale('id')->translatedFormat('d M');
            $endFormatted = Carbon::parse($w->max_date)->locale('id')->translatedFormat('d M');

            $result[$wNum] = [
                'week_num' => $wNum,
                'week_year' => (int)$w->week_year,
                'label' => 'M' . $wNum,
                'start_date' => $w->min_date,
                'end_date' => $w->max_date,
                'range_label' => $startFormatted . ' – ' . $endFormatted,
            ];
            $wNum++;
        }

        return $result;
    }

    /**
     * Komputasi matriks kunjungan toko per minggu dalam 1 bulan
     */
    #[Computed]
    public function allStoreMatrix()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            return collect();
        }

        $cacheKey = $this->getCacheKey();

        return Cache::remember($cacheKey, 300, function () {
            return $this->computeStoreMatrix();
        });
    }

    protected function computeStoreMatrix()
    {
        $weeksMap = $this->monthWeeks();
        if (empty($weeksMap)) {
            return collect();
        }

        $availableWeeks = array_keys($weeksMap);
        $weekYearToSeq = [];
        foreach ($weeksMap as $seq => $wInfo) {
            $weekYearToSeq[$wInfo['week_year']] = $seq;
        }

        // 1. Load koordinat distributor
        $distPoints = [];
        $mDistQuery = DB::table('master_distributors as md')
            ->whereNotNull('md.latitude')
            ->whereNotNull('md.longitude');

        if ($this->appliedRegion) $mDistQuery->where('md.region_code', $this->appliedRegion);
        if ($this->appliedArea) $mDistQuery->where('md.area_code', $this->appliedArea);
        if ($this->appliedDistributor) $mDistQuery->where('md.distributor_code', $this->appliedDistributor);

        $mDists = $mDistQuery->get();
        foreach ($mDists as $mDist) {
            $cleanD = Findings::cleanCoordinate($mDist->latitude, $mDist->longitude);
            if ($cleanD) {
                $distPoints[$mDist->distributor_code] = [
                    'code' => $mDist->distributor_code,
                    'name' => trim(preg_replace('/[\r\n\t]+/', ' ', (string)($mDist->distributor_name ?? $mDist->distributor_code))),
                    'lat' => $cleanD['lat'],
                    'lon' => $cleanD['lon'],
                ];
            }
        }

        // 2. Query seluruh data kunjungan dalam bulan tersebut
        $query = DB::table('rpt_visit_an_h as rvah')
            ->join('master_calender as mc', DB::raw('rvah."TANGGAL"::date'), '=', 'mc.date')
            ->where('rvah.RID', '!=', 'HOINA')
            ->where('rvah.RID', $this->appliedRegion)
            ->where('rvah.EID', $this->appliedArea)
            ->whereYear(DB::raw('rvah."TANGGAL"::date'), $this->appliedYear)
            ->whereMonth(DB::raw('rvah."TANGGAL"::date'), $this->appliedMonth)
            ->where('rvah.FLAG_PJP', 'R'); // Sesuai aturan: hanya toko jadwal PJP

        if (!empty($this->appliedDistributor)) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }

        if (!empty($this->appliedSalesman)) {
            $query->where('rvah.MUID', $this->appliedSalesman);
        }

        // Security level
        if ($this->accessLevel === 'region' && !empty($this->allowedRegionCodes)) {
            $query->whereIn('rvah.RID', $this->allowedRegionCodes);
        } elseif ($this->accessLevel === 'area' && !empty($this->allowedAreaCodes)) {
            $query->whereIn('rvah.EID', $this->allowedAreaCodes);
        } elseif ($this->accessLevel === 'supervisor') {
            if (!empty($this->allowedDistributorCodes)) {
                $query->whereIn('rvah.BID', $this->allowedDistributorCodes);
            }
        }

        $rawRows = $query->select(
            'rvah.ID as id',
            'rvah.CUSTNO as custno',
            'rvah.CUSTNAME as custname',
            'rvah.CUSTADD1 as address',
            'rvah.MUID as salesman_code',
            'rvah.MUNAME as salesman_name',
            'rvah.BID as distributor_code',
            'rvah.BNAME as distributor_name',
            DB::raw('rvah."TANGGAL"::date as tanggal'),
            DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
            DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
            DB::raw('rvah."TIME_CONSUME"::time as time_consume'),
            'rvah.FLAG_VISIT as flag_visit',
            'rvah.FLAG_EC as flag_ec',
            'rvah.ORDER_VAL as val_order',
            'rvah.ORDER_QTY as qty_order',
            'rvah.V_LA as visit_lat',
            'rvah.V_LG as visit_lon',
            'rvah.M_LA as master_lat',
            'rvah.M_LG as master_lon',
            'mc.week_year as week_year'
        )->get();

        if ($rawRows->isEmpty()) {
            return collect();
        }

        // Ambil data alasan kunjungan (rpt_visit_an_r) untuk memvalidasi toko tutup (Closed-Store Filter)
        $startDate = sprintf('%04d-%02d-01', $this->appliedYear, $this->appliedMonth);
        $endDate = Carbon::create($this->appliedYear, $this->appliedMonth, 1)->endOfMonth()->format('Y-m-d');

        $reasonsQuery = DB::table('rpt_visit_an_r')
            ->where('RID', '!=', 'HOINA')
            ->where('RID', $this->appliedRegion)
            ->where('EID', $this->appliedArea)
            ->whereBetween('TANGGAL', [$startDate, $endDate]);

        if (!empty($this->appliedDistributor)) {
            $reasonsQuery->where('BID', $this->appliedDistributor);
        }
        if (!empty($this->appliedSalesman)) {
            $reasonsQuery->where('MUID', $this->appliedSalesman);
        }

        $reasonsMap = $reasonsQuery->select('HID', 'MUID', 'CUSTNO', 'TANGGAL', 'REASON_ID', 'REASON_DESC')
            ->get()
            ->groupBy(fn($r) => $r->HID . '_' . $r->MUID . '_' . $r->CUSTNO . '_' . $r->TANGGAL);

        // 3. Deteksi Anomali Koordinat Kembar (Smart Market Filter) per sales per tanggal
        $groupedByDay = $rawRows->groupBy(fn($r) => $r->tanggal . '_' . $r->salesman_code);
        $duplicateMap = [];

        foreach ($groupedByDay as $groupKey => $dayItems) {
            $visited = $dayItems->where('flag_visit', 'Y')->values();
            $visitCount = $visited->count();

            for ($i = 0; $i < $visitCount; $i++) {
                $p1 = $visited[$i];
                $c1 = Findings::cleanCoordinate($p1->visit_lat, $p1->visit_lon);
                if (!$c1) continue;

                for ($j = $i + 1; $j < $visitCount; $j++) {
                    $p2 = $visited[$j];
                    if ($p1->custno !== $p2->custno) {
                        $c2 = Findings::cleanCoordinate($p2->visit_lat, $p2->visit_lon);
                        if (!$c2) continue;

                        $d = Findings::calculateDistance($c1['lat'], $c1['lon'], $c2['lat'], $c2['lon']);
                        if ($d <= 3) {
                            $m1 = Findings::cleanCoordinate($p1->master_lat, $p1->master_lon);
                            $m2 = Findings::cleanCoordinate($p2->master_lat, $p2->master_lon);

                            $t1 = !empty($p1->time_in) ? strtotime($p1->tanggal . ' ' . $p1->time_in) : null;
                            $t2 = !empty($p2->time_in) ? strtotime($p2->tanggal . ' ' . $p2->time_in) : null;
                            $timeDiff = ($t1 && $t2) ? abs($t1 - $t2) : null;

                            // Jika kedua master toko valid dan berdekatan (<= 100m) serta jeda waktu wajar (>= 2 menit),
                            // adalah KUNJUNGAN PASAR YANG SAH -> Lewati
                            if ($m1 && $m2) {
                                $dMaster = Findings::calculateDistance($m1['lat'], $m1['lon'], $m2['lat'], $m2['lon']);
                                if ($dMaster <= 100 && ($timeDiff === null || $timeDiff >= 120)) {
                                    continue;
                                }
                            }

                            $reason = '';
                            if (!$m1 || !$m2) {
                                $reason = ' (Master GPS kosong)';
                            } elseif ($timeDiff !== null && $timeDiff < 120) {
                                $reason = " (Check-in instan: {$timeDiff}d)";
                            } elseif (isset($dMaster)) {
                                $reason = " (Jarak master {$dMaster}m)";
                            }

                            $duplicateMap[$p1->id][] = $p2->custname . ($d > 0 ? " ({$d}m{$reason})" : " (0m{$reason})");
                            $duplicateMap[$p2->id][] = $p1->custname . ($d > 0 ? " ({$d}m{$reason})" : " (0m{$reason})");
                        }
                    }
                }
            }
        }

        // 4. Evaluasi anomali pada setiap baris kunjungan
        $evaluatedVisits = [];
        foreach ($rawRows as $item) {
            $itemAnomalies = [];
            $cleanVisit = Findings::cleanCoordinate($item->visit_lat, $item->visit_lon);
            $cleanMaster = Findings::cleanCoordinate($item->master_lat, $item->master_lon);

            $distPoint = $distPoints[$item->distributor_code] ?? null;
            $distToDepo = null;
            if ($distPoint && $cleanVisit) {
                $distToDepo = Findings::calculateDistance($cleanVisit['lat'], $cleanVisit['lon'], $distPoint['lat'], $distPoint['lon']);
            }

            $distToMaster = null;
            if ($cleanMaster && $cleanVisit) {
                $distToMaster = Findings::calculateDistance($cleanVisit['lat'], $cleanVisit['lon'], $cleanMaster['lat'], $cleanMaster['lon']);
            }

            $distMasterToDepo = null;
            if ($distPoint && $cleanMaster) {
                $distMasterToDepo = Findings::calculateDistance($cleanMaster['lat'], $cleanMaster['lon'], $distPoint['lat'], $distPoint['lon']);
            }

            // Anomali 1: Kunjungan di Titik Depo
            if ($item->flag_visit === 'Y' && $distToDepo !== null && $distToDepo <= 100) {
                if (!$cleanMaster) {
                    $depoDetail = "Check-in di depo ({$distToDepo}m) • Master GPS KOSONG (0, 0)";
                } elseif ($distMasterToDepo !== null && $distMasterToDepo <= 100) {
                    $depoDetail = "Check-in di depo ({$distToDepo}m) • Master Toko TERDAFTAR DI DEPO ({$distMasterToDepo}m)";
                } else {
                    $depoDetail = "Check-in di depo ({$distToDepo}m) • Master Toko DI LUAR DEPO ({$distMasterToDepo}m)";
                }

                $itemAnomalies[] = [
                    'type' => 'at_distributor',
                    'label' => 'Di Depo',
                    'severity' => 'high',
                    'icon' => 'building-office',
                    'detail' => $depoDetail,
                ];
            }

            // Anomali 2: Koordinat Kembar
            if (isset($duplicateMap[$item->id])) {
                $otherStores = implode(', ', array_slice(array_unique($duplicateMap[$item->id]), 0, 2));
                $itemAnomalies[] = [
                    'type' => 'duplicate_coord',
                    'label' => 'Kembar',
                    'severity' => 'high',
                    'icon' => 'document-duplicate',
                    'detail' => "Koordinat GPS kembar (≤ 3m) dengan: {$otherStores}",
                ];
            }

            // Anomali 3: Durasi Kunjungan Kilat (< 2 menit)
            // Smart Closed-Store Filter:
            // Jika toko fisik terbukti tutup (TUTUP / BELUM BUKA / AKSES TERTUTUP) dan non-transaksi (FLAG_EC = 'N'),
            // maka kunjungan kilat (< 2 menit) otomatis DIKECUALIKAN dari anomali (sah secara operasional).
            if ($item->flag_visit === 'Y' && !empty($item->time_consume) && $item->time_consume < '00:02:00' && $item->time_consume !== '00:00:00') {
                $reasonKey = $item->id . '_' . $item->salesman_code . '_' . $item->custno . '_' . $item->tanggal;
                $itemReasons = $reasonsMap->get($reasonKey);
                $reasonDesc = $itemReasons ? $itemReasons->pluck('REASON_DESC')->join(' | ') : null;
                $isClosedStore = $reasonDesc && preg_match('/(TUTUP|BELUM BUKA|TERTUTUP)/i', $reasonDesc);
                $isEc = ($item->flag_ec === 'Y');

                if (!$isEc && $isClosedStore) {
                    // Lolos / Sah: Sales datang namun toko fisik tutup, wajar durasi singkat (< 2 menit).
                } else {
                    $consumeSec = Findings::parseSecondsFromTimeString($item->time_consume);
                    if ($isEc) {
                        $itemAnomalies[] = [
                            'type' => 'quick_visit',
                            'label' => 'Kilat Bertransaksi',
                            'severity' => 'high',
                            'icon' => 'clock',
                            'detail' => "Order kilat {$consumeSec} detik (< 2 menit)",
                        ];
                    } else {
                        $itemAnomalies[] = [
                            'type' => 'quick_visit',
                            'label' => 'Kilat',
                            'severity' => 'medium',
                            'icon' => 'clock',
                            'detail' => "Kunjungan hanya {$consumeSec} detik (< 2 menit)",
                        ];
                    }
                }
            }

            // Anomali 4: Di Luar Radius Master Toko (> 50m)
            if ($item->flag_visit === 'Y' && $distToMaster !== null && $distToMaster > 50) {
                $itemAnomalies[] = [
                    'type' => 'out_of_radius',
                    'label' => 'Luar Radius',
                    'severity' => 'medium',
                    'icon' => 'map-pin',
                    'detail' => "Jarak check-in {$distToMaster}m dari master (> 50m)",
                ];
            }

            // Anomali 5: GPS Kosong / Blank
            if ($item->flag_visit === 'Y' && !$cleanVisit) {
                $itemAnomalies[] = [
                    'type' => 'no_gps',
                    'label' => 'GPS Kosong',
                    'severity' => 'low',
                    'icon' => 'no-symbol',
                    'detail' => 'Koordinat GPS kosong (0, 0)',
                ];
            }

            $itemCopy = clone $item;
            $itemCopy->anomalies = $itemAnomalies;
            $itemCopy->has_anomaly = !empty($itemAnomalies);
            $itemCopy->dist_to_depo = $distToDepo;
            $itemCopy->dist_to_master = $distToMaster;
            $itemCopy->clean_master = $cleanMaster;
            $itemCopy->clean_visit = $cleanVisit;
            $itemCopy->week_num = $weekYearToSeq[(int)($item->week_year ?? 0)] ?? 1;

            $evaluatedVisits[] = $itemCopy;
        }

        // 5. Kelompokkan per Toko x SE: salesman_code + '_' + custno
        // Mapping nama SE single yang terbaru untuk setiap salesman_code (mengantisipasi pergantian personil di tengah jalan)
        $latestSeMap = collect($evaluatedVisits)
            ->sortByDesc(fn($x) => ($x->tanggal ?? '') . ' ' . ($x->time_in ?? ''))
            ->groupBy('salesman_code')
            ->mapWithKeys(fn($group, $code) => [$code => $group->first()->salesman_name]);

        $byStore = collect($evaluatedVisits)->groupBy(fn($x) => $x->salesman_code . '_' . $x->custno);
        $matrixList = [];

        foreach ($byStore as $storeKey => $storeVisits) {
            $first = $storeVisits->first();
            $byWeek = $storeVisits->groupBy('week_num');

            $weeksData = [];
            $hasAnyAnomaly = false;
            $weeksWithAnomaly = [];
            $weeksNormal = [];
            $weeksVisited = [];

            foreach ($availableWeeks as $wNum) {
                $wVisits = $byWeek->get($wNum, collect());

                if ($wVisits->isEmpty()) {
                    $weeksData[$wNum] = (object) [
                        'status' => 'skip',
                        'severity' => 'skip',
                        'label' => 'Tidak Ada Visit',
                        'detail' => 'Toko tidak dikunjungi pada minggu ini',
                        'icon' => 'minus',
                        'anomalies' => [],
                        'visit_count' => 0,
                        'total_val_order' => 0,
                    ];
                } else {
                    $weeksVisited[] = $wNum;
                    $anomalousVisits = $wVisits->filter(fn($v) => $v->has_anomaly);
                    $totalValOrder = $wVisits->sum('val_order');

                    if ($anomalousVisits->isNotEmpty()) {
                        $hasAnyAnomaly = true;
                        $weeksWithAnomaly[] = $wNum;

                        $weekAnomalies = [];
                        foreach ($anomalousVisits as $av) {
                            $weekAnomalies = array_merge($weekAnomalies, $av->anomalies);
                        }

                        $hasHigh = collect($weekAnomalies)->contains('severity', 'high');
                        $hasMed = collect($weekAnomalies)->contains('severity', 'medium');
                        $sev = $hasHigh ? 'high' : ($hasMed ? 'medium' : 'low');

                        // Pilih anomali terparah untuk label utama
                        $primary = collect($weekAnomalies)->firstWhere('severity', 'high') 
                                ?? collect($weekAnomalies)->firstWhere('severity', 'medium') 
                                ?? $weekAnomalies[0];

                        $weeksData[$wNum] = (object) [
                            'status' => 'anomaly',
                            'severity' => $sev,
                            'label' => $primary['label'],
                            'detail' => $primary['detail'],
                            'icon' => $primary['icon'],
                            'type' => $primary['type'],
                            'anomalies' => $weekAnomalies,
                            'visit_count' => $wVisits->count(),
                            'total_val_order' => $totalValOrder,
                            'latest_visit' => $wVisits->last(),
                        ];
                    } else {
                        $weeksNormal[] = $wNum;
                        $lastVisit = $wVisits->last();
                        $dur = $lastVisit->time_consume ? substr($lastVisit->time_consume, 0, 5) : '-';

                        $weeksData[$wNum] = (object) [
                            'status' => 'normal',
                            'severity' => 'normal',
                            'label' => 'Normal (' . $dur . ')',
                            'detail' => 'Kunjungan patuh SOP (Valid)',
                            'icon' => 'check-circle',
                            'type' => 'normal',
                            'anomalies' => [],
                            'visit_count' => $wVisits->count(),
                            'total_val_order' => $totalValOrder,
                            'latest_visit' => $lastVisit,
                        ];
                    }
                }
            }

            // 6. Tentukan Status Tren Perbaikan (Auto Trend Status)
            $latestWeekVisited = !empty($weeksVisited) ? max($weeksVisited) : null;
            $trendStatus = 'normal'; // default

            if ($hasAnyAnomaly) {
                if ($latestWeekVisited) {
                    $latestWeekData = $weeksData[$latestWeekVisited];
                    if ($latestWeekData->status === 'normal') {
                        // Kunjungan terakhir sudah NORMAL! Berarti ada perbaikan/solved!
                        $trendStatus = 'solved';
                    } else {
                        // Kunjungan terakhir masih anomali
                        if (count($weeksWithAnomaly) >= 2) {
                            // Berulang kali di >= 2 minggu
                            $trendStatus = 'recurrent';
                        } else {
                            // Anomali baru pertama kali muncul di minggu terakhir
                            $trendStatus = 'new';
                        }
                    }
                } else {
                    $trendStatus = 'skip';
                }
            } else {
                $trendStatus = 'normal';
            }

            $matrixList[] = (object) [
                'store_key' => $storeKey,
                'salesman_code' => $first->salesman_code,
                'salesman_name' => $latestSeMap->get($first->salesman_code, $first->salesman_name),
                'distributor_code' => $first->distributor_code,
                'distributor_name' => $first->distributor_name,
                'custno' => $first->custno,
                'custname' => $first->custname,
                'address' => $first->address,
                'has_master_gps' => $first->clean_master !== null,
                'master_lat' => $first->clean_master ? $first->clean_master['lat'] : null,
                'master_lon' => $first->clean_master ? $first->clean_master['lon'] : null,
                'weeks' => $weeksData,
                'has_any_anomaly' => $hasAnyAnomaly,
                'trend_status' => $trendStatus,
                'anomaly_weeks_count' => count($weeksWithAnomaly),
                'normal_weeks_count' => count($weeksNormal),
                'total_val_order' => $storeVisits->sum('val_order'),
            ];
        }

        // Urutkan toko: Toko bermasalah kronis di atas, lalu anomali baru, membaik, dan normal
        $trendOrder = [
            'recurrent' => 1,
            'new' => 2,
            'solved' => 3,
            'skip' => 4,
            'normal' => 5,
        ];

        return collect($matrixList)->sortBy(fn($x) => [
            $trendOrder[$x->trend_status] ?? 99,
            -$x->anomaly_weeks_count,
            $x->salesman_name,
            $x->custname,
        ])->values();
    }

    #[Computed]
    public function summaryMetrics()
    {
        $all = $this->allStoreMatrix();
        if ($all->isEmpty()) {
            return (object) [
                'total_stores' => 0,
                'total_with_anomalies' => 0,
                'solved_count' => 0,
                'recurrent_count' => 0,
                'new_count' => 0,
                'normal_count' => 0,
            ];
        }

        return (object) [
            'total_stores' => $all->count(),
            'total_with_anomalies' => $all->where('has_any_anomaly', true)->count(),
            'solved_count' => $all->where('trend_status', 'solved')->count(),
            'recurrent_count' => $all->where('trend_status', 'recurrent')->count(),
            'new_count' => $all->where('trend_status', 'new')->count(),
            'normal_count' => $all->where('trend_status', 'normal')->count(),
        ];
    }

    #[Computed]
    public function filteredStoreMatrix()
    {
        $collection = $this->allStoreMatrix();
        if ($collection->isEmpty()) {
            return collect();
        }

        // Default: hanya tampilkan toko yang pernah bermasalah, kecuali user mencentang 'showAllStores' atau memilih pill filter 'Normal'
        if (!$this->showAllStores && $this->statusFilter !== 'normal') {
            $collection = $collection->where('has_any_anomaly', true);
        }

        // Filter status tren
        if ($this->statusFilter !== 'all') {
            $collection = $collection->where('trend_status', $this->statusFilter);
        }

        // Pencarian teks
        if (!empty($this->search)) {
            $q = strtolower(trim($this->search));
            $collection = $collection->filter(function ($item) use ($q) {
                return str_contains(strtolower($item->custname ?? ''), $q)
                    || str_contains(strtolower($item->custno ?? ''), $q)
                    || str_contains(strtolower($item->address ?? ''), $q)
                    || str_contains(strtolower($item->salesman_name ?? ''), $q)
                    || str_contains(strtolower($item->distributor_name ?? ''), $q);
            });
        }

        return $collection->values();
    }

    #[Computed]
    public function paginatedStores()
    {
        $filtered = $this->filteredStoreMatrix();
        if ($filtered->isEmpty()) {
            return new LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        $page = $this->getPage();
        $items = $filtered->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function exportExcel()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            $this->dispatch('notify', msg: 'Pilih Region dan Area terlebih dahulu untuk export.', type: 'warning');
            return;
        }

        $stores = $this->filteredStoreMatrix();
        $weeksMap = $this->monthWeeks();

        $monthName = $this->monthOptions[$this->appliedMonth] ?? $this->appliedMonth;
        $fileName = "Tracking_Mingguan_Temuan_SE_{$this->appliedRegion}_{$this->appliedArea}_{$monthName}_{$this->appliedYear}.xlsx";

        $meta = [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'distributor' => $this->appliedDistributor ?: 'Semua Distributor',
            'salesman' => $this->appliedSalesman ?: 'Semua Salesman',
            'month' => $monthName,
            'year' => $this->appliedYear,
            'statusFilter' => $this->statusFilter,
        ];

        return Excel::download(new AnalisaKunjunganSeTrackingExport($stores, $weeksMap, $meta), $fileName);
    }

    public function render()
    {
        $weeksMap = $this->monthWeeks();
        $metrics = $this->summaryMetrics();
        $stores = $this->paginatedStores();

        return view('livewire.report.analisa-kunjungan-se.tracking', [
            'weeksMap' => $weeksMap,
            'metrics' => $metrics,
            'stores' => $stores,
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
            'statusFilter' => $this->statusFilter,
            'showAllStores' => $this->showAllStores,
            'search' => $this->search,
            'perPage' => $this->perPage,
            'canExport' => $this->canExport,
        ])->layout('layouts.app');
    }
}
