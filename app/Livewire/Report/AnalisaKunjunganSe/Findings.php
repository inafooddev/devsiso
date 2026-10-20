<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeFindingsExport;

class Findings extends Component
{
    use EnforcesMenuPermissions, WithPagination;

    #[Title('Temuan Anomali Kunjungan SE')]
    protected string $menuRoute = 'report.analisa-kunjungan-se.index';

    public $activeTab = 'findings';

    // Permissions & Access Levels
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

    // Filter Selections
    #[Url(as: 'region')]
    public $selectedRegion = '';

    #[Url(as: 'area')]
    public $selectedArea = '';

    #[Url(as: 'distributor')]
    public $selectedDistributor = '';

    #[Url(as: 'salesman')]
    public $selectedSalesman = '';

    #[Url(as: 'startDate')]
    public $startDate = '';

    #[Url(as: 'endDate')]
    public $endDate = '';

    #[Url(as: 'type')]
    public $typeFilter = 'all';

    #[Url(as: 'severity')]
    public $severityFilter = 'all';

    #[Url(as: 'q')]
    public $search = '';

    public $perPage = 25;
    public $sortField = 'tanggal';
    public $sortDirection = 'desc';

    // Applied Filters
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedDistributor = '';
    public $appliedSalesman = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';

    public function mount()
    {
        $this->authorizeAction('can_view');
        $this->canExport = auth()->check() && auth()->user()->hasMenuAccess($this->menuRoute, 'can_export');
        $this->resolveUserAccess();

        if (empty($this->startDate) || empty($this->endDate)) {
            $this->setDatePreset('today');
        }

        $this->loadRegions();

        // Restore filter dari session jika tidak ada parameter URL spesifik
        if (empty($this->selectedRegion) && session()->has('analisa_kunjungan_se_shared_filter')) {
            $saved = session()->get('analisa_kunjungan_se_shared_filter');
            $this->selectedRegion = $saved['region'] ?? '';
            $this->selectedArea = $saved['area'] ?? '';
            $this->selectedDistributor = $saved['distributor'] ?? '';
            $this->selectedSalesman = $saved['salesman'] ?? '';
            if (!empty($saved['startDate'])) $this->startDate = $saved['startDate'];
            if (!empty($saved['endDate'])) $this->endDate = $saved['endDate'];
        }

        // Check if query params were passed from other tabs or restored from session
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

    public function loadRegions()
    {
        $query = DB::table('master_regions')
            ->select('region_code', 'region_name')
            ->where('region_code', '!=', 'HOINA')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name');

        if ($this->accessLevel !== 'nasional' && !empty($this->allowedRegionCodes)) {
            $query->whereIn('region_code', $this->allowedRegionCodes);
        }

        $this->regions = $query->get()->toArray();

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

            $query = DB::table('master_areas')
                ->where('region_code', $value)
                ->whereNotNull('area_code')
                ->select('area_code', 'area_name')
                ->distinct()
                ->orderBy('area_name');

            if (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes)) {
                $query->whereIn('area_code', $this->allowedAreaCodes);
            }

            $this->areas = $query->get()->toArray();

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

            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('area_code', $value)
                ->where('distributor_code', '!=', 'HOINA')
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct()
                ->orderBy('distributor_name');

            if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
                $query->whereIn('distributor_code', $this->allowedDistributorCodes);
            }

            $this->distributors = $query->get()->toArray();

            if (count($this->distributors) === 1 && empty($this->selectedDistributor)) {
                $this->selectedDistributor = $this->distributors[0]->distributor_code;
                $this->updatedSelectedDistributor($this->selectedDistributor);
            } else {
                // Auto-load seluruh salesman di area ini jika distributor belum dipilih ("Semua Distributor")
                $this->updatedSelectedDistributor($this->selectedDistributor ?: '');
            }
        }
    }

    public function updatedSelectedDistributor($value)
    {
        $this->selectedSalesman = '';
        $this->salesmen = [];

        if ($value) {
            if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes) && !in_array($value, $this->allowedDistributorCodes)) {
                $this->selectedDistributor = '';
                return;
            }

            $latestDate = DB::table('rpt_visit_an_h')
                ->where('BID', $value)
                ->where('RID', '!=', 'HOINA')
                ->whereNotNull('MUID')
                ->max('TANGGAL');

            if ($latestDate) {
                $startOfLatestMonth = Carbon::parse($latestDate)->startOfMonth()->format('Y-m-d');

                $subQuery = DB::table('rpt_visit_an_h')
                    ->where('BID', $value)
                    ->where('RID', '!=', 'HOINA')
                    ->whereNotNull('MUID')
                    ->where('TANGGAL', '>=', $startOfLatestMonth)
                    ->select(DB::raw('DISTINCT ON ("MUID") "MUID" as salesman_code, "MUNAME" as salesman_name'))
                    ->orderBy('MUID')
                    ->orderBy('TANGGAL', 'desc')
                    ->orderBy('ID', 'desc');

                $this->salesmen = DB::table(DB::raw("({$subQuery->toSql()}) as latest_se"))
                    ->mergeBindings($subQuery)
                    ->orderBy('salesman_name', 'asc')
                    ->get()
                    ->toArray();
            } else {
                $this->salesmen = [];
            }
        } elseif (!empty($this->selectedArea)) {
            // Jika pilih "Semua Distributor", muat seluruh salesman di area ini
            $latestDate = DB::table('rpt_visit_an_h')
                ->where('EID', $this->selectedArea)
                ->where('RID', '!=', 'HOINA')
                ->whereNotNull('MUID')
                ->max('TANGGAL');

            if ($latestDate) {
                $startOfLatestMonth = Carbon::parse($latestDate)->startOfMonth()->format('Y-m-d');

                $subQuery = DB::table('rpt_visit_an_h')
                    ->where('EID', $this->selectedArea)
                    ->where('RID', '!=', 'HOINA')
                    ->whereNotNull('MUID')
                    ->where('TANGGAL', '>=', $startOfLatestMonth)
                    ->select(DB::raw('DISTINCT ON ("MUID") "MUID" as salesman_code, "MUNAME" as salesman_name'))
                    ->orderBy('MUID')
                    ->orderBy('TANGGAL', 'desc')
                    ->orderBy('ID', 'desc');

                $this->salesmen = DB::table(DB::raw("({$subQuery->toSql()}) as latest_se"))
                    ->mergeBindings($subQuery)
                    ->orderBy('salesman_name', 'asc')
                    ->get()
                    ->toArray();
            } else {
                $this->salesmen = [];
            }
        }
    }

    public function updatedStartDate($value): void
    {
        if (empty($value)) return;

        try {
            $start = Carbon::parse($value);
            $end = !empty($this->endDate) ? Carbon::parse($this->endDate) : null;

            if ($end) {
                if ($end->lt($start)) {
                    $this->endDate = $start->format('Y-m-d');
                    return;
                }
                if ($start->diffInDays($end) > 30) {
                    $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                    $this->dispatch('notify', msg: 'Rentang tanggal disesuaikan otomatis (maksimal 31 hari).', type: 'info');
                }
            }
        } catch (\Exception $e) {}
    }

    public function updatedEndDate($value): void
    {
        if (empty($value)) return;

        try {
            $end = Carbon::parse($value);
            $start = !empty($this->startDate) ? Carbon::parse($this->startDate) : null;

            if ($start) {
                if ($start->gt($end)) {
                    $this->startDate = $end->format('Y-m-d');
                    return;
                }
                if ($start->diffInDays($end) > 30) {
                    $this->startDate = $end->copy()->subDays(30)->format('Y-m-d');
                    $this->dispatch('notify', msg: 'Rentang tanggal disesuaikan otomatis (maksimal 31 hari).', type: 'info');
                }
            }
        } catch (\Exception $e) {}
    }

    public function setDatePreset(string $preset): void
    {
        $now = Carbon::now();
        switch ($preset) {
            case 'today':
                $this->startDate = $now->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'last_7_days':
                $this->startDate = $now->copy()->subDays(6)->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = $now->copy()->subDays(30)->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
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

        if (empty($this->startDate) || empty($this->endDate)) {
            $this->setDatePreset('today');
        }

        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor ?: '';
        $this->appliedSalesman = $this->selectedSalesman ?: '';
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        // Persist filter ke session lintas tab menu
        session()->put('analisa_kunjungan_se_shared_filter', [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'distributor' => $this->appliedDistributor,
            'salesman' => $this->appliedSalesman,
            'startDate' => $this->appliedStartDate,
            'endDate' => $this->appliedEndDate,
        ]);

        // Bersihkan cache jika user menekan tombol Filter secara eksplisit agar data ter-refresh
        if ($forceRefresh) {
            Cache::forget($this->getCacheKey());
        }

        $this->resetPage();
    }

    public function resetFilters()
    {
        Cache::forget($this->getCacheKey());
        session()->forget('analisa_kunjungan_se_shared_filter');

        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        $this->typeFilter = 'all';
        $this->severityFilter = 'all';
        $this->search = '';

        $this->setDatePreset('today');

        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedDistributor = '';
        $this->appliedSalesman = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';

        $this->areas = [];
        $this->distributors = [];
        $this->salesmen = [];

        $this->loadRegions();
        $this->resetPage();
    }

    public function setTypeFilter(string $type)
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function setSeverityFilter(string $severity)
    {
        $this->severityFilter = $severity;
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

    public function sortByColumn(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['salesman_name', 'custname']) ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public static function cleanCoordinate($rawLat, $rawLon): ?array
    {
        if ($rawLat === null || $rawLon === null) return null;

        $strLat = trim(str_replace(',', '.', (string)$rawLat));
        $strLon = trim(str_replace(',', '.', (string)$rawLon));

        if ($strLat === '' || $strLon === '') return null;
        if (!is_numeric($strLat) || !is_numeric($strLon)) return null;

        $lat = (float)$strLat;
        $lon = (float)$strLon;

        if (is_nan($lat) || is_nan($lon) || is_infinite($lat) || is_infinite($lon)) {
            return null;
        }

        if (abs($lat) < 0.000001 && abs($lon) < 0.000001) {
            return null;
        }

        if (abs($lat) > 90 && abs($lon) <= 90) {
            $tmp = $lat;
            $lat = $lon;
            $lon = $tmp;
        }

        if (abs($lat) > 90 || abs($lon) > 180) {
            return null;
        }

        return ['lat' => $lat, 'lon' => $lon];
    }

    public static function calculateDistance($lat1, $lon1, $lat2, $lon2): int
    {
        $c1 = self::cleanCoordinate($lat1, $lon1);
        $c2 = self::cleanCoordinate($lat2, $lon2);
        if (!$c1 || !$c2) return 0;

        $earthRadius = 6371000;
        $latFrom = deg2rad($c1['lat']);
        $lonFrom = deg2rad($c1['lon']);
        $latTo = deg2rad($c2['lat']);
        $lonTo = deg2rad($c2['lon']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $val = pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2);
        $val = max(0.0, min(1.0, $val));
        $angle = 2 * asin(sqrt($val));

        $dist = round($angle * $earthRadius);
        return (is_nan($dist) || is_infinite($dist)) ? 0 : (int)$dist;
    }

    public static function parseSecondsFromTimeString($timeStr): int
    {
        if (empty($timeStr)) return 0;
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})/', (string)$timeStr, $m)) {
            return ($m[1] * 3600) + ($m[2] * 60) + (int)$m[3];
        }
        return 0;
    }

    public function getCacheKey(): string
    {
        $userId = auth()->id() ?? 'guest';
        return "se_findings_{$userId}_{$this->appliedRegion}_{$this->appliedArea}_{$this->appliedDistributor}_{$this->appliedSalesman}_{$this->appliedStartDate}_{$this->appliedEndDate}";
    }

    #[Computed]
    public function allFindings()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            return collect();
        }

        $cacheKey = $this->getCacheKey();

        return Cache::remember($cacheKey, 300, function () {
            return $this->computeAllFindings();
        });
    }

    protected function computeAllFindings()
    {

        // Ambil titik koordinat seluruh distributor di area ini (atau 1 distributor spesifik)
        $distPoints = [];
        $mDistQuery = DB::table('distributor_implementasi_eskalink as die')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->whereNotNull('die.eskalink_code')
            ->whereNotNull('md.latitude')
            ->whereNotNull('md.longitude')
            ->where('md.latitude', '!=', 0)
            ->where('md.longitude', '!=', 0)
            ->select('die.eskalink_code as distributor_code', 'md.distributor_name', 'md.latitude', 'md.longitude');

        if (!empty($this->appliedDistributor)) {
            $mDistQuery->where('die.eskalink_code', $this->appliedDistributor);
        } else {
            $mDistQuery->where('md.region_code', $this->appliedRegion)
                       ->where('md.area_code', $this->appliedArea);
        }

        $mDists = $mDistQuery->get();
        foreach ($mDists as $mDist) {
            $cleanD = self::cleanCoordinate($mDist->latitude, $mDist->longitude);
            if ($cleanD) {
                $distPoints[$mDist->distributor_code] = [
                    'code' => $mDist->distributor_code,
                    'name' => trim(preg_replace('/[\r\n\t]+/', ' ', (string)($mDist->distributor_name ?? $mDist->distributor_code))),
                    'lat' => $cleanD['lat'],
                    'lon' => $cleanD['lon'],
                ];
            }
        }

        $query = DB::table('rpt_visit_an_h as rvah')
            ->where('rvah.RID', '!=', 'HOINA')
            ->where('rvah.RID', $this->appliedRegion)
            ->where('rvah.EID', $this->appliedArea)
            ->where('rvah.FLAG_PJP', 'R');

        if (!empty($this->appliedDistributor)) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }

        if (!empty($this->appliedSalesman)) {
            $query->where('rvah.MUID', $this->appliedSalesman);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $query->where('rvah.TANGGAL', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $query->where('rvah.TANGGAL', '<=', $this->appliedEndDate);
        }

        $rawRows = $query->select(
                'rvah.ID as id',
                'rvah.RID as region_code',
                'rvah.RNAME as region_name',
                'rvah.EID as area_code',
                'rvah.ENAME as area_name',
                'rvah.BID as distributor_code',
                'rvah.BNAME as distributor_name',
                'rvah.MUID as salesman_code',
                'rvah.MUNAME as salesman_name',
                'rvah.CUSTNO as custno',
                'rvah.CUSTNAME as custname',
                'rvah.CUSTADD1 as address',
                DB::raw('rvah."TANGGAL"::date as tanggal'),
                DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
                DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
                DB::raw('rvah."TIME_CONSUME"::time as time_consume'),
                DB::raw('rvah."TIME_TRAVEL"::time as time_travel'),
                'rvah.ORDER_QTY as qty_order',
                'rvah.ORDER_VAL as val_order',
                'rvah.FLAG_PJP as flag_pjp',
                'rvah.FLAG_VISIT as flag_visit',
                'rvah.FLAG_EC as flag_ec',
                'rvah.M_LA as master_lat',
                'rvah.M_LG as master_lon',
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon'
            )
            ->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
            ->orderBy('rvah.TIME_IN', 'asc')
            ->orderBy('rvah.ID', 'asc')
            ->get();

        $findingsList = [];
        $grouped = $rawRows->groupBy(fn($r) => $r->tanggal . '_' . $r->salesman_code);

        foreach ($grouped as $groupKey => $items) {
            $visited = $items->where('flag_visit', 'Y')->values();
            $visitCount = $visited->count();

            // 1. Koordinat Kembar (Duplicate Coordinates) dalam 1 hari untuk toko berbeda
            $duplicateMap = [];
            for ($i = 0; $i < $visitCount; $i++) {
                $p1 = $visited[$i];
                $c1 = self::cleanCoordinate($p1->visit_lat, $p1->visit_lon);
                if (!$c1) continue;

                for ($j = $i + 1; $j < $visitCount; $j++) {
                    $p2 = $visited[$j];
                    if ($p1->custno !== $p2->custno) {
                        $c2 = self::cleanCoordinate($p2->visit_lat, $p2->visit_lon);
                        if (!$c2) continue;

                        $d = self::calculateDistance($c1['lat'], $c1['lon'], $c2['lat'], $c2['lon']);
                        if ($d <= 3) {
                            $duplicateMap[$p1->id][] = $p2->custname . ($d > 0 ? " ({$d}m)" : " (0m)");
                            $duplicateMap[$p2->id][] = $p1->custname . ($d > 0 ? " ({$d}m)" : " (0m)");
                        }
                    }
                }
            }

            // 2. Teleportasi / Perjalanan Mustahil (> 3km dalam <= 60 detik)
            $teleportMap = [];
            $sortedVisited = $visited->filter(fn($x) => !empty($x->time_in))->sortBy('time_in')->values();
            for ($k = 0; $k < $sortedVisited->count() - 1; $k++) {
                $curr = $sortedVisited[$k];
                $next = $sortedVisited[$k + 1];

                $cCurr = self::cleanCoordinate($curr->visit_lat, $curr->visit_lon);
                $cNext = self::cleanCoordinate($next->visit_lat, $next->visit_lon);

                if ($cCurr && $cNext) {
                    $distMeters = self::calculateDistance($cCurr['lat'], $cCurr['lon'], $cNext['lat'], $cNext['lon']);
                    $travelSec = self::parseSecondsFromTimeString($next->time_travel);

                    if ($distMeters >= 3000 && $travelSec > 0 && $travelSec <= 60) {
                        $speedKmh = round(($distMeters / 1000) / ($travelSec / 3600));
                        $teleportMap[$next->id] = "Jarak {$distMeters}m dalam {$travelSec}d ({$speedKmh} km/j)";
                    }
                }
            }

            // Pindai anomali setiap baris
            foreach ($items as $item) {
                $itemAnomalies = [];
                $cleanVisit = self::cleanCoordinate($item->visit_lat, $item->visit_lon);
                $cleanMaster = self::cleanCoordinate($item->master_lat, $item->master_lon);

                $distPoint = $distPoints[$item->distributor_code] ?? null;
                $distToDepo = null;
                if ($distPoint && $cleanVisit) {
                    $distToDepo = self::calculateDistance($cleanVisit['lat'], $cleanVisit['lon'], $distPoint['lat'], $distPoint['lon']);
                }

                $distToMaster = null;
                if ($cleanMaster && $cleanVisit) {
                    $distToMaster = self::calculateDistance($cleanVisit['lat'], $cleanVisit['lon'], $cleanMaster['lat'], $cleanMaster['lon']);
                }

                // Anomali 1: Kunjungan di Titik Depo / Distributor (Radius <= 100m)
                if ($item->flag_visit === 'Y' && $distToDepo !== null && $distToDepo <= 100) {
                    $itemAnomalies[] = [
                        'type' => 'at_distributor',
                        'label' => 'Kunjungan di Depo',
                        'severity' => 'high',
                        'icon' => 'building-office',
                        'detail' => "Check-in berjarak {$distToDepo}m dari kantor/gudang distributor",
                    ];
                }

                // Anomali 2: Titik Koordinat Kembar (Clone GPS)
                if (isset($duplicateMap[$item->id])) {
                    $otherStores = implode(', ', array_slice(array_unique($duplicateMap[$item->id]), 0, 2));
                    $itemAnomalies[] = [
                        'type' => 'duplicate_coord',
                        'label' => 'Koordinat Kembar',
                        'severity' => 'high',
                        'icon' => 'document-duplicate',
                        'detail' => "Koordinat GPS kembar (≤ 3m) dengan: {$otherStores}",
                    ];
                }

                // Anomali 3: Teleportasi / Perjalanan Mustahil
                if (isset($teleportMap[$item->id])) {
                    $itemAnomalies[] = [
                        'type' => 'impossible_travel',
                        'label' => 'Perjalanan Mustahil',
                        'severity' => 'high',
                        'icon' => 'bolt',
                        'detail' => $teleportMap[$item->id],
                    ];
                }

                // Anomali 4: Order Tanpa Kunjungan Fisik (Ghost Order)
                if ($item->flag_ec === 'Y' && $item->flag_visit !== 'Y') {
                    $itemAnomalies[] = [
                        'type' => 'ec_no_visit',
                        'label' => 'Order Tanpa Kunjungan',
                        'severity' => 'high',
                        'icon' => 'shopping-cart',
                        'detail' => 'Tercatat transaksi Effective Call (EC) tetapi toko tidak dikunjungi',
                    ];
                }

                // Anomali 5: Durasi Kunjungan Sangat Singkat (< 2 menit)
                if ($item->flag_visit === 'Y' && !empty($item->time_consume) && $item->time_consume < '00:02:00' && $item->time_consume !== '00:00:00') {
                    $consumeSec = self::parseSecondsFromTimeString($item->time_consume);
                    $itemAnomalies[] = [
                        'type' => 'quick_visit',
                        'label' => 'Durasi Kunjungan Kilat',
                        'severity' => 'medium',
                        'icon' => 'clock',
                        'detail' => "Waktu kunjungan hanya {$consumeSec} detik (< 2 menit)",
                    ];
                }

                // Anomali 6: Di Luar Radius Master Toko (> 50m)
                if ($item->flag_visit === 'Y' && $item->flag_pjp === 'R' && $distToMaster !== null && $distToMaster > 50) {
                    $itemAnomalies[] = [
                        'type' => 'out_of_radius',
                        'label' => 'Di Luar Radius',
                        'severity' => 'medium',
                        'icon' => 'map-pin',
                        'detail' => "Jarak check-in {$distToMaster}m dari titik master (> 50m)",
                    ];
                }

                // Anomali 7: GPS Kosong / Blank
                if ($item->flag_visit === 'Y' && !$cleanVisit) {
                    $itemAnomalies[] = [
                        'type' => 'no_gps',
                        'label' => 'GPS Kosong / Blank',
                        'severity' => 'low',
                        'icon' => 'no-symbol',
                        'detail' => 'Koordinat kunjungan tidak valid atau kosong (0, 0)',
                    ];
                }

                if (!empty($itemAnomalies)) {
                    $hasHigh = collect($itemAnomalies)->contains('severity', 'high');
                    $hasMedium = collect($itemAnomalies)->contains('severity', 'medium');
                    $highestSeverity = $hasHigh ? 'high' : ($hasMedium ? 'medium' : 'low');

                    $findingsList[] = (object) [
                        'id' => $item->id,
                        'tanggal' => $item->tanggal,
                        'formatted_date' => Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d M Y'),
                        'time_in' => $item->time_in ? substr($item->time_in, 0, 5) : '-',
                        'time_out' => $item->time_out ? substr($item->time_out, 0, 5) : '-',
                        'salesman_code' => $item->salesman_code,
                        'salesman_name' => $item->salesman_name,
                        'distributor_code' => $item->distributor_code,
                        'distributor_name' => $item->distributor_name,
                        'custno' => $item->custno,
                        'custname' => $item->custname,
                        'address' => $item->address,
                        'anomalies' => $itemAnomalies,
                        'primary_severity' => $highestSeverity,
                        'anomaly_types' => collect($itemAnomalies)->pluck('type')->toArray(),
                        'clean_visit' => $cleanVisit,
                        'clean_master' => $cleanMaster,
                        'master_lat' => $cleanMaster ? $cleanMaster['lat'] : null,
                        'master_lon' => $cleanMaster ? $cleanMaster['lon'] : null,
                        'visit_lat' => $cleanVisit ? $cleanVisit['lat'] : null,
                        'visit_lon' => $cleanVisit ? $cleanVisit['lon'] : null,
                        'distributor_point' => $distPoint,
                        'dist_to_distributor' => $distToDepo,
                        'dist_to_master' => $distToMaster,
                        'time_consume' => $item->time_consume,
                        'time_travel' => $item->time_travel,
                        'val_order' => (float)$item->val_order,
                        'qty_order' => (int)$item->qty_order,
                        'flag_ec' => $item->flag_ec,
                        'flag_visit' => $item->flag_visit,
                        'flag_pjp' => $item->flag_pjp,
                    ];
                }
            }
        }

        return collect($findingsList);
    }

    #[Computed]
    public function summaryMetrics()
    {
        $all = $this->allFindings();
        if ($all->isEmpty()) {
            return (object) [
                'total_findings' => 0,
                'high_count' => 0,
                'medium_count' => 0,
                'low_count' => 0,
                'type_counts' => [
                    'at_distributor' => 0,
                    'duplicate_coord' => 0,
                    'impossible_travel' => 0,
                    'ec_no_visit' => 0,
                    'quick_visit' => 0,
                    'out_of_radius' => 0,
                    'no_gps' => 0,
                ],
                'top_salesmen' => collect(),
            ];
        }

        $highCount = $all->where('primary_severity', 'high')->count();
        $mediumCount = $all->where('primary_severity', 'medium')->count();
        $lowCount = $all->where('primary_severity', 'low')->count();

        $typeCounts = [
            'at_distributor' => $all->filter(fn($x) => in_array('at_distributor', $x->anomaly_types))->count(),
            'duplicate_coord' => $all->filter(fn($x) => in_array('duplicate_coord', $x->anomaly_types))->count(),
            'impossible_travel' => $all->filter(fn($x) => in_array('impossible_travel', $x->anomaly_types))->count(),
            'ec_no_visit' => $all->filter(fn($x) => in_array('ec_no_visit', $x->anomaly_types))->count(),
            'quick_visit' => $all->filter(fn($x) => in_array('quick_visit', $x->anomaly_types))->count(),
            'out_of_radius' => $all->filter(fn($x) => in_array('out_of_radius', $x->anomaly_types))->count(),
            'no_gps' => $all->filter(fn($x) => in_array('no_gps', $x->anomaly_types))->count(),
        ];

        // Top 5 Salesmen with most findings (identitas per distributor_code + salesman_code)
        $topSalesmen = $all->groupBy(fn($x) => ($x->distributor_code ?? '') . '_' . ($x->salesman_code ?? ''))->map(function ($items) {
            $first = $items->first();
            return (object) [
                'salesman_code' => $first->salesman_code ?? '',
                'salesman_name' => $first->salesman_name ?? $first->salesman_code,
                'distributor_name' => $first->distributor_name ?? '',
                'total' => $items->count(),
                'high' => $items->where('primary_severity', 'high')->count(),
                'medium' => $items->where('primary_severity', 'medium')->count(),
            ];
        })->sortByDesc('total')->take(5)->values();

        return (object) [
            'total_findings' => $all->count(),
            'high_count' => $highCount,
            'medium_count' => $mediumCount,
            'low_count' => $lowCount,
            'type_counts' => $typeCounts,
            'top_salesmen' => $topSalesmen,
        ];
    }

    public static function anomalyCategoryDefinitions(): array
    {
        return [
            'at_distributor' => [
                'type' => 'at_distributor',
                'label' => 'Kunjungan di Titik Depo',
                'description' => 'Check-in di dalam radius kantor/gudang distributor',
                'severity' => 'high',
                'order' => 1,
            ],
            'duplicate_coord' => [
                'type' => 'duplicate_coord',
                'label' => 'Koordinat Kembar',
                'description' => 'Titik GPS identik/kembar (toleransi ≤ 3m) untuk toko berbeda di hari yang sama',
                'severity' => 'high',
                'order' => 2,
            ],
            'ec_no_visit' => [
                'type' => 'ec_no_visit',
                'label' => 'Order Tanpa Kunjungan Fisik',
                'description' => 'Tercatat transaksi EC tetapi toko tidak dikunjungi fisik (Ghost Visit)',
                'severity' => 'high',
                'order' => 3,
            ],
            'impossible_travel' => [
                'type' => 'impossible_travel',
                'label' => 'Teleportasi / Kecepatan Tidak Wajar',
                'description' => 'Kecepatan perpindahan antar toko tidak masuk akal (> 120 km/jam)',
                'severity' => 'high',
                'order' => 4,
            ],
            'quick_visit' => [
                'type' => 'quick_visit',
                'label' => 'Kunjungan Kilat (< 2 Menit)',
                'description' => 'Durasi kunjungan terlalu singkat di bawah SOP (< 2 menit)',
                'severity' => 'medium',
                'order' => 5,
            ],
            'out_of_radius' => [
                'type' => 'out_of_radius',
                'label' => 'Di Luar Radius Master Toko (> 50m)',
                'description' => 'Jarak check-in lebih dari 50 meter dari titik master toko',
                'severity' => 'medium',
                'order' => 6,
            ],
            'no_gps' => [
                'type' => 'no_gps',
                'label' => 'GPS Kosong / Blank (0, 0)',
                'description' => 'Kunjungan tidak memiliki data koordinat GPS yang valid',
                'severity' => 'low',
                'order' => 7,
            ],
        ];
    }

    #[Computed]
    public function filteredFindings()
    {
        $collection = $this->allFindings();
        if ($collection->isEmpty()) {
            return collect();
        }

        // Filter: Anomaly Type
        if ($this->typeFilter !== 'all') {
            $collection = $collection->filter(function ($item) {
                return in_array($this->typeFilter, $item->anomaly_types);
            });
        }

        // Filter: Severity
        if ($this->severityFilter !== 'all') {
            $collection = $collection->filter(function ($item) {
                return $item->primary_severity === $this->severityFilter;
            });
        }

        // Filter: Search query
        if (!empty(trim($this->search))) {
            $needle = strtolower(trim($this->search));
            $collection = $collection->filter(function ($item) use ($needle) {
                return str_contains(strtolower($item->custname ?? ''), $needle)
                    || str_contains(strtolower($item->custno ?? ''), $needle)
                    || str_contains(strtolower($item->salesman_name ?? ''), $needle)
                    || str_contains(strtolower($item->distributor_name ?? ''), $needle)
                    || str_contains(strtolower($item->address ?? ''), $needle);
            });
        }

        return $collection;
    }

    #[Computed]
    public function seHierarchies()
    {
        $filtered = $this->filteredFindings();
        if ($filtered->isEmpty()) {
            return collect();
        }

        $definitions = self::anomalyCategoryDefinitions();
        $groupedBySe = $filtered->groupBy(fn($item) => ($item->distributor_code ?? '') . '_' . ($item->salesman_code ?? ''));

        $result = $groupedBySe->map(function ($items, $hierarchyKey) use ($definitions) {
            $first = $items->first();
            $salesmanCode = $first->salesman_code ?? '';
            $salesmanName = $first->salesman_name ?? $salesmanCode;

            // Kelompokkan item per Jenis Temuan Anomali
            $categories = [];
            foreach ($definitions as $type => $def) {
                // Filter baris toko yang memiliki anomali tipe ini
                $matchingItems = $items->filter(function ($item) use ($type) {
                    return in_array($type, $item->anomaly_types);
                })->values();

                if ($matchingItems->isNotEmpty()) {
                    // Beri keterangan detail anomali yang spesifik
                    $itemsWithSpecificDetail = $matchingItems->map(function ($row) use ($type) {
                        $specificAnomaly = collect($row->anomalies)->firstWhere('type', $type);
                        $rowCopy = clone $row;
                        $rowCopy->current_anomaly_detail = $specificAnomaly['detail'] ?? ($specificAnomaly['label'] ?? '-');
                        return $rowCopy;
                    });

                    // Urutkan toko di dalam kategori berdasarkan jam kunjungan
                    $sortedItems = $itemsWithSpecificDetail->sortBy(fn($x) => $x->tanggal . ' ' . $x->time_in)->values();

                    $categories[$type] = (object) [
                        'type' => $type,
                        'label' => $def['label'],
                        'description' => $def['description'],
                        'severity' => $def['severity'],
                        'count' => $matchingItems->count(),
                        'items' => $sortedItems,
                        'total_val_order' => $matchingItems->sum('val_order'),
                        'total_ec' => $matchingItems->where('flag_ec', 'Y')->count(),
                    ];
                }
            }

            // Metrik agregat per SE
            $uniqueStores = $items->pluck('custno')->unique()->count();
            $highCount = $items->where('primary_severity', 'high')->count();
            $mediumCount = $items->where('primary_severity', 'medium')->count();
            $lowCount = $items->where('primary_severity', 'low')->count();
            $totalValOrder = $items->sum('val_order');
            $totalEc = $items->where('flag_ec', 'Y')->count();

            return (object) [
                'hierarchy_key' => $hierarchyKey,
                'salesman_code' => $salesmanCode,
                'salesman_name' => $salesmanName,
                'distributor_code' => $first->distributor_code ?? '',
                'distributor_name' => $first->distributor_name ?? '',
                'total_findings' => $items->count(),
                'high_count' => $highCount,
                'medium_count' => $mediumCount,
                'low_count' => $lowCount,
                'unique_stores' => $uniqueStores,
                'total_val_order' => $totalValOrder,
                'total_ec' => $totalEc,
                'categories' => collect($categories)->sortBy(fn($c) => $definitions[$c->type]['order'] ?? 99)->values(),
            ];
        });

        // Urutkan SE berdasarkan total temuan terbanyak
        return $result->sortByDesc('total_findings')->values();
    }

    #[Computed]
    public function paginatedSe()
    {
        $hierarchies = $this->seHierarchies();
        if ($hierarchies->isEmpty()) {
            return new LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        $currentPage = $this->getPage();
        $total = $hierarchies->count();
        $items = $hierarchies->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator($items, $total, $this->perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    #[Computed]
    public function paginatedFindings()
    {
        $collection = $this->filteredFindings();
        if ($collection->isEmpty()) {
            return new LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        // Sorting
        $isDesc = ($this->sortDirection === 'desc');
        switch ($this->sortField) {
            case 'tanggal':
                $collection = $isDesc 
                    ? $collection->sortByDesc(fn($x) => $x->tanggal . ' ' . $x->time_in) 
                    : $collection->sortBy(fn($x) => $x->tanggal . ' ' . $x->time_in);
                break;
            case 'salesman_name':
                $collection = $isDesc ? $collection->sortByDesc('salesman_name') : $collection->sortBy('salesman_name');
                break;
            case 'custname':
                $collection = $isDesc ? $collection->sortByDesc('custname') : $collection->sortBy('custname');
                break;
            case 'severity':
                $weights = ['high' => 3, 'medium' => 2, 'low' => 1];
                $collection = $isDesc 
                    ? $collection->sortByDesc(fn($x) => $weights[$x->primary_severity] ?? 0)
                    : $collection->sortBy(fn($x) => $weights[$x->primary_severity] ?? 0);
                break;
            case 'val_order':
                $collection = $isDesc ? $collection->sortByDesc('val_order') : $collection->sortBy('val_order');
                break;
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $collection->count();
        $items = $collection->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator($items, $total, $this->perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }

    public function exportExcel()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedRegion) || empty($this->appliedArea)) {
            $this->dispatch('notify', msg: 'Tentukan filter Region dan Area terlebih dahulu.', type: 'warning');
            return;
        }

        $findings = $this->filteredFindings();
        if ($findings->isEmpty()) {
            $this->dispatch('notify', msg: 'Tidak ada data temuan anomali untuk diexport.', type: 'error');
            return;
        }

        $seHierarchies = $this->seHierarchies();

        $filename = 'Temuan_Anomali_SE_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AnalisaKunjunganSeFindingsExport($seHierarchies, $findings), $filename);
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan-se.findings', [
            'metrics' => $this->summaryMetrics(),
            'seHierarchies' => $this->paginatedSe(),
            'totalFilteredFindings' => $this->filteredFindings()->count(),
            'paginated' => $this->paginatedFindings(),
            'canExport' => $this->canExport,
            'accessLevel' => $this->accessLevel,
            'activeTab' => $this->activeTab,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'distributors' => $this->distributors,
            'salesmen' => $this->salesmen,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedDistributor' => $this->selectedDistributor,
            'selectedSalesman' => $this->selectedSalesman,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedSalesman' => $this->appliedSalesman,
            'appliedStartDate' => $this->appliedStartDate,
            'appliedEndDate' => $this->appliedEndDate,
            'typeFilter' => $this->typeFilter,
            'severityFilter' => $this->severityFilter,
            'search' => $this->search,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage,
        ])->layout('layouts.app');
    }
}
