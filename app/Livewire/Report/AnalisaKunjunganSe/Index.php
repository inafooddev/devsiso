<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeExport;
use Carbon\Carbon;

class Index extends Component
{
    use EnforcesMenuPermissions, WithPagination;

    #[Title('Analisa Kunjungan SE')]
    protected string $menuRoute = 'report.analisa-kunjungan-se.index';

    public $activeTab = 'detail';

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
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    public $selectedSalesman = '';
    public $startDate = '';
    public $endDate = '';

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

        $this->setDatePreset('this_month');
        
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->loadRegions();
    }

    /**
     * Smart date range validation: ensures endDate >= startDate and duration <= 31 days.
     */
    public function updatedStartDate($value): void
    {
        if (empty($value)) return;

        try {
            $start = \Carbon\Carbon::parse($value);
            $end = !empty($this->endDate) ? \Carbon\Carbon::parse($this->endDate) : null;

            if ($end) {
                // Rule 1: Jika endDate mendahului startDate, samakan endDate = startDate
                if ($end->lt($start)) {
                    $this->endDate = $start->format('Y-m-d');
                    return;
                }

                // Rule 2: Maksimal rentang 31 hari (diffInDays > 30)
                if ($start->diffInDays($end) > 30) {
                    $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                    $this->dispatch('notify', msg: 'Rentang tanggal disesuaikan otomatis (maksimal 31 hari).', type: 'info');
                }
            }
        } catch (\Exception $e) {}
    }

    /**
     * Smart date range validation: ensures startDate <= endDate and duration <= 31 days.
     */
    public function updatedEndDate($value): void
    {
        if (empty($value)) return;

        try {
            $end = \Carbon\Carbon::parse($value);
            $start = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : null;

            if ($start) {
                // Rule 1: Jika startDate melebihi endDate, samakan startDate = endDate
                if ($start->gt($end)) {
                    $this->startDate = $end->format('Y-m-d');
                    return;
                }

                // Rule 2: Maksimal rentang 31 hari (diffInDays > 30)
                if ($start->diffInDays($end) > 30) {
                    $this->startDate = $end->copy()->subDays(30)->format('Y-m-d');
                    $this->dispatch('notify', msg: 'Rentang tanggal disesuaikan otomatis (maksimal 31 hari).', type: 'info');
                }
            }
        } catch (\Exception $e) {}
    }

    /**
     * Set quick date preset (Hari Ini, 7 Hari Terakhir, Bulan Ini, 30 Hari, Bulan Lalu).
     */
    public function setDatePreset(string $preset): void
    {
        $today = \Carbon\Carbon::today();

        switch ($preset) {
            case 'today':
                $this->startDate = $today->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay()->format('Y-m-d');
                $this->startDate = $yesterday;
                $this->endDate = $yesterday;
                break;
            case 'last_7_days':
                $this->startDate = $today->copy()->subDays(6)->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = $today->copy()->subDays(29)->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $today->copy()->subMonthNoOverflow();
                $this->startDate = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    /**
     * Quick navigate to previous month.
     */
    public function previousMonth(): void
    {
        $currentStart = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : \Carbon\Carbon::today();
        $prevMonth = $currentStart->copy()->subMonthNoOverflow();
        $this->startDate = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
        $this->endDate = $prevMonth->copy()->endOfMonth()->format('Y-m-d');
    }

    /**
     * Quick navigate to next month (capped at today if future).
     */
    public function nextMonth(): void
    {
        $currentStart = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : \Carbon\Carbon::today();
        $nextMonth = $currentStart->copy()->addMonthNoOverflow();
        $today = \Carbon\Carbon::today();

        $this->startDate = $nextMonth->copy()->startOfMonth()->format('Y-m-d');
        $end = $nextMonth->copy()->endOfMonth();
        $this->endDate = $end->gt($today) ? $today->format('Y-m-d') : $end->format('Y-m-d');

        if (\Carbon\Carbon::parse($this->startDate)->gt($today)) {
            $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
            $this->endDate = $today->format('Y-m-d');
        }
    }

    /**
     * Resolve user access level and allowed territories (Region, Area, Distributor).
     */
    protected function resolveUserAccess(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->accessLevel = $user->getAccessLevel();

        if ($this->accessLevel === 'region') {
            $this->allowedRegionCodes = (array) ($user->region_code ?? []);
        } elseif ($this->accessLevel === 'area') {
            $this->allowedAreaCodes = (array) ($user->area_code ?? []);
            
            // For ASM users, region_code is often null in users table.
            // Retrieve associated region codes from master_areas.
            $this->allowedRegionCodes = DB::table('master_areas')
                ->whereIn('area_code', $this->allowedAreaCodes)
                ->pluck('region_code')
                ->unique()
                ->filter()
                ->values()
                ->toArray();
        } elseif ($this->accessLevel === 'supervisor') {
            // For supervisor, resolve allowed region, area, and distributors from vw_distributor_hierarchy
            $hierarchy = DB::table('vw_distributor_hierarchy')
                ->where('supervisor_code', $user->supervisor_code)
                ->get();

            $this->allowedRegionCodes = $hierarchy->pluck('region_code')->unique()->filter()->values()->toArray();
            $this->allowedAreaCodes = $hierarchy->pluck('area_code')->unique()->filter()->values()->toArray();
            $this->allowedDistributorCodes = $hierarchy->pluck('distributor_code')->unique()->filter()->values()->toArray();

            // Fallback to team_elite_code_mappings if hierarchy has no records
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
            // Security check: verify allowed region
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
            // Security check: verify allowed area
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
            }
        }
    }

    public function updatedSelectedDistributor($value)
    {
        $this->selectedSalesman = '';
        $this->salesmen = [];

        if ($value) {
            // Security check: verify allowed distributor for supervisor
            if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes) && !in_array($value, $this->allowedDistributorCodes)) {
                $this->selectedDistributor = '';
                return;
            }

            // Ambil tanggal transaksi terakhir untuk distributor ini agar hanya memuat SE aktif pada bulan terakhir
            $latestDate = DB::table('rpt_visit_an_h')
                ->where('BID', $value)
                ->where('RID', '!=', 'HOINA')
                ->whereNotNull('MUID')
                ->max('TANGGAL');

            if ($latestDate) {
                // Batas awal bulan terakhir transaksi (contoh: jika transaksi terakhir Oktober 2026, ambil per 2026-10-01)
                $startOfLatestMonth = \Carbon\Carbon::parse($latestDate)->startOfMonth()->format('Y-m-d');

                // Query SE dari bulan terakhir dengan DISTINCT ON ("MUID") untuk mencegah nama ganda/duplikat
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

            // Auto-select salesman pertama jika tersedia (SE bersifat wajib dipilih secara spesifik)
            if (count($this->salesmen) > 0) {
                $this->selectedSalesman = $this->salesmen[0]->salesman_code;
            }
        }
    }

    public function applyFilter()
    {
        if ($this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::parse($this->startDate);
            $end = \Carbon\Carbon::parse($this->endDate);
            
            if ($start->gt($end)) {
                $this->dispatch('notify', msg: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', type: 'error');
                return;
            }
            
            // Rentang waktu maksimal 31 hari (diffInDays > 30)
            if ($start->diffInDays($end) > 30) {
                $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                $this->dispatch('notify', msg: 'Rentang waktu disesuaikan otomatis maksimal 31 hari.', type: 'info');
            }
        }

        if (empty($this->selectedRegion) || empty($this->selectedArea) || empty($this->selectedDistributor) || empty($this->selectedSalesman)) {
            $this->dispatch('notify', msg: 'Harap lengkapi filter: Region, Area, Distributor, dan Salesman (SE).', type: 'error');
            return;
        }

        // Security check on applied filters
        if ($this->accessLevel === 'region' && !empty($this->allowedRegionCodes) && !in_array($this->selectedRegion, $this->allowedRegionCodes)) {
            $this->dispatch('notify', msg: 'Anda tidak memiliki akses ke Region ini.', type: 'error');
            return;
        }

        if (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes) && !in_array($this->selectedArea, $this->allowedAreaCodes)) {
            $this->dispatch('notify', msg: 'Anda tidak memiliki akses ke Area ini.', type: 'error');
            return;
        }

        if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes) && $this->selectedDistributor && !in_array($this->selectedDistributor, $this->allowedDistributorCodes)) {
            $this->dispatch('notify', msg: 'Anda tidak memiliki akses ke Distributor ini.', type: 'error');
            return;
        }

        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor;
        $this->appliedSalesman = $this->selectedSalesman;
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->selectedSalesman = '';
        
        $this->setDatePreset('this_month');
        
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

    public function setTab($tab)
    {
        if ($tab === 'summary') {
            $this->dispatch('notify', msg: 'Halaman Summary Kunjungan SE akan dibuat pada tahap berikutnya.', type: 'info');
            return;
        }
        $this->activeTab = $tab;
    }

    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
        
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return round($angle * $earthRadius); // in meters
    }

    public static function parseSecondsFromTimeString($timeStr)
    {
        if (empty($timeStr)) return 0;
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})/', $timeStr, $m)) {
            return ($m[1] * 3600) + ($m[2] * 60) + (int)$m[3];
        }
        return 0;
    }

    public static function formatDurationSeconds($sec)
    {
        if (!$sec || $sec <= 0) return '0m';
        $h = floor($sec / 3600);
        $m = round(($sec % 3600) / 60);
        if ($h > 0) {
            return "{$h}j {$m}m";
        }
        return "{$m}m";
    }

    public static function formatTimeStringHuman($timeStr)
    {
        if (empty($timeStr) || $timeStr === '-' || $timeStr === '00:00:00') return '-';
        $sec = self::parseSecondsFromTimeString($timeStr);
        if ($sec <= 0) return '-';
        $h = floor($sec / 3600);
        $m = floor(($sec % 3600) / 60);
        $s = $sec % 60;
        
        if ($h > 0) {
            return "{$h}j {$m}m";
        }
        if ($m > 0) {
            return $s > 0 ? "{$m}m {$s}d" : "{$m}m";
        }
        return "{$s}d";
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        return self::calculateDistance($lat1, $lon1, $lat2, $lon2);
    }

    public function getBaseQuery()
    {
        $query = DB::table('rpt_visit_an_h as rvah')
            ->where('rvah.RID', '!=', 'HOINA');

        // Apply mandatory row-level security (RLS) based on user access level
        if ($this->accessLevel === 'region' && !empty($this->allowedRegionCodes)) {
            $query->whereIn('rvah.RID', $this->allowedRegionCodes);
        } elseif ($this->accessLevel === 'area' && !empty($this->allowedAreaCodes)) {
            $query->whereIn('rvah.EID', $this->allowedAreaCodes);
        } elseif ($this->accessLevel === 'supervisor') {
            if (!empty($this->allowedDistributorCodes)) {
                $query->whereIn('rvah.BID', $this->allowedDistributorCodes);
            } elseif (!empty($this->allowedAreaCodes)) {
                $query->whereIn('rvah.EID', $this->allowedAreaCodes);
            }
        }

        if ($this->appliedRegion) {
            $query->where('rvah.RID', $this->appliedRegion);
        }

        if ($this->appliedArea) {
            $query->where('rvah.EID', $this->appliedArea);
        }

        if ($this->appliedDistributor) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }

        if ($this->appliedSalesman) {
            $query->where('rvah.MUID', $this->appliedSalesman);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $query->where('rvah.TANGGAL', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $query->where('rvah.TANGGAL', '<=', $this->appliedEndDate);
        }

        return $query;
    }

    #[Computed]
    public function dailyData()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea) || empty($this->appliedDistributor) || empty($this->appliedSalesman)) {
            return [
                'summaries' => collect(),
                'grandTotal' => null,
            ];
        }

        $rawRows = $this->getBaseQuery()
            ->select(
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
                DB::raw('rvah."TIME_PAUSE"::time as time_pause'),
                'rvah.ORDER_QTY as qty_order',
                'rvah.ORDER_VAL as val_order',
                'rvah.FLAG_PJP as flag_pjp',
                'rvah.FLAG_VISIT as flag_visit',
                'rvah.FLAG_EC as flag_ec',
                'rvah.FLAG_BUY as flag_buy',
                'rvah.FLAG_PAUSE as flag_pause',
                'rvah.M_LA as master_lat',
                'rvah.M_LG as master_lon',
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvar.REASON_TYPE as reason_type',
                'rvar.REASON_DESC as reason_desc'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
            ->orderBy('rvah.TIME_IN', 'asc')
            ->orderBy('rvah.ID', 'asc')
            ->get();

        $summaries = $rawRows->groupBy('tanggal')->map(function ($items, $tanggal) {
            $totalCall = $items->count();
            $pc = $items->where('flag_pjp', 'R')->count();
            $xc = $items->where('flag_pjp', 'N')->count();
            $visited = $items->where('flag_visit', 'Y');
            $totalVisit = $visited->count();
            $visitPercent = $totalCall > 0 ? round(($totalVisit / $totalCall) * 100, 1) : 0;
            
            $ec = $items->where('flag_ec', 'Y');
            $totalEc = $ec->count();
            $ecPercent = $totalVisit > 0 ? round(($totalEc / $totalVisit) * 100, 1) : 0;
            $totalValOrder = (float) $items->sum('val_order');
            
            // Sort items by time_in
            $sortedItems = $items->sortBy('time_in')->values();
            $firstIn = $sortedItems->first(fn($i) => !empty($i->time_in))?->time_in;
            $lastOut = $sortedItems->filter(fn($i) => !empty($i->time_out))->sortByDesc('time_out')->first()?->time_out;
            
            // Work duration
            $workDurationStr = '-';
            $workDurationSec = 0;
            if ($firstIn && $lastOut) {
                $secIn = self::parseSecondsFromTimeString($firstIn);
                $secOut = self::parseSecondsFromTimeString($lastOut);
                if ($secOut >= $secIn) {
                    $workDurationSec = $secOut - $secIn;
                    $workDurationStr = self::formatDurationSeconds($workDurationSec);
                }
            }

            // Distances & item enhancements
            $outOfRadiusCount = 0;
            $mapPoints = [];
            $rowNum = 1;
            foreach ($sortedItems as $item) {
                $item->row_num = $rowNum++;
                $dist = self::calculateDistance($item->master_lat, $item->master_lon, $item->visit_lat, $item->visit_lon);
                $item->distance = $dist;
                $item->is_out_of_radius = ($dist > 50);
                if ($item->is_out_of_radius) {
                    $outOfRadiusCount++;
                }

                // Format human-friendly time strings
                $item->time_consume_human = self::formatTimeStringHuman($item->time_consume);
                $item->time_travel_human = self::formatTimeStringHuman($item->time_travel);
                $item->time_in_fmt = !empty($item->time_in) ? substr($item->time_in, 0, 5) : '-';
                $item->time_out_fmt = !empty($item->time_out) ? substr($item->time_out, 0, 5) : '-';

                // Map points for daily map
                if ($item->flag_visit === 'Y' && !empty($item->visit_lat) && !empty($item->visit_lon)) {
                    $mapPoints[] = [
                        'lat' => $item->visit_lat,
                        'lon' => $item->visit_lon,
                        'name' => $item->custname,
                        'spv' => $item->salesman_name,
                        'date' => $tanggal,
                    ];
                }
            }
            
            // Time sums (in seconds)
            $consumeSec = 0;
            $travelSec = 0;
            foreach ($sortedItems as $item) {
                $consumeSec += self::parseSecondsFromTimeString($item->time_consume);
                $travelSec += self::parseSecondsFromTimeString($item->time_travel);
            }

            $totalActiveSec = $consumeSec + $travelSec;
            $consumePercent = $totalActiveSec > 0 ? (int) round(($consumeSec / $totalActiveSec) * 100) : 50;
            $travelPercent = 100 - $consumePercent;

            $carbonDate = Carbon::parse($tanggal);
            $isWeekend = $carbonDate->isSaturday() || $carbonDate->isSunday();
            $isSaturday = $carbonDate->isSaturday();

            return (object) [
                'tanggal' => $tanggal,
                'carbon_date' => $carbonDate,
                'day_name' => $carbonDate->locale('id')->translatedFormat('l'),
                'formatted_date' => $carbonDate->locale('id')->translatedFormat('d M Y'),
                'is_weekend' => $isWeekend,
                'is_saturday' => $isSaturday,
                'total_call' => $totalCall,
                'pc' => $pc,
                'xc' => $xc,
                'total_visit' => $totalVisit,
                'visit_percent' => $visitPercent,
                'total_ec' => $totalEc,
                'ec_percent' => $ecPercent,
                'total_val_order' => $totalValOrder,
                'is_top_sales' => $totalValOrder >= 5000000,
                'first_time_in' => $firstIn ? substr($firstIn, 0, 5) : '-',
                'last_time_out' => $lastOut ? substr($lastOut, 0, 5) : '-',
                'work_duration_sec' => $workDurationSec,
                'work_duration_str' => $workDurationStr,
                'consume_sec' => $consumeSec,
                'consume_str' => self::formatDurationSeconds($consumeSec),
                'travel_sec' => $travelSec,
                'travel_str' => self::formatDurationSeconds($travelSec),
                'consume_percent' => $consumePercent,
                'travel_percent' => $travelPercent,
                'out_of_radius_count' => $outOfRadiusCount,
                'map_points' => $mapPoints,
                'items' => $sortedItems,
            ];
        });

        // Calculate Grand Total across all dates
        $grandTotal = null;
        if ($summaries->isNotEmpty()) {
            $totCalls = $summaries->sum('total_call');
            $totPc = $summaries->sum('pc');
            $totXc = $summaries->sum('xc');
            $totVisits = $summaries->sum('total_visit');
            $totEc = $summaries->sum('total_ec');
            $totVal = $summaries->sum('total_val_order');
            $totConsumeSec = $summaries->sum('consume_sec');
            $totTravelSec = $summaries->sum('travel_sec');
            $totOutOfRadius = $summaries->sum('out_of_radius_count');
            $totDays = $summaries->count();

            $totWorkSec = $summaries->sum('work_duration_sec');
            $avgWorkSec = $totDays > 0 ? (int) round($totWorkSec / $totDays) : 0;
            $avgWorkStr = self::formatDurationSeconds($avgWorkSec);

            $totActiveSec = $totConsumeSec + $totTravelSec;
            $totConsumePercent = $totActiveSec > 0 ? (int) round(($totConsumeSec / $totActiveSec) * 100) : 50;
            $totTravelPercent = 100 - $totConsumePercent;

            $grandTotal = (object) [
                'total_days' => $totDays,
                'total_call' => $totCalls,
                'pc' => $totPc,
                'xc' => $totXc,
                'total_visit' => $totVisits,
                'visit_percent' => $totCalls > 0 ? round(($totVisits / $totCalls) * 100, 1) : 0,
                'total_ec' => $totEc,
                'ec_percent' => $totVisits > 0 ? round(($totEc / $totVisits) * 100, 1) : 0,
                'total_val_order' => $totVal,
                'avg_work_str' => $avgWorkStr,
                'consume_str' => self::formatDurationSeconds($totConsumeSec),
                'travel_str' => self::formatDurationSeconds($totTravelSec),
                'consume_percent' => $totConsumePercent,
                'travel_percent' => $totTravelPercent,
                'out_of_radius_count' => $totOutOfRadius,
            ];
        }

        return [
            'summaries' => $summaries,
            'grandTotal' => $grandTotal,
        ];
    }

    #[Computed]
    public function dataKunjungan()
    {
        return $this->dailyData()['summaries'];
    }

    #[Computed]
    public function kpiStats()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea) || empty($this->appliedDistributor) || empty($this->appliedSalesman)) {
            return [
                'total_call' => 0,
                'pc' => 0,
                'xc' => 0,
                'pc_call_percent' => 0,
                'xc_call_percent' => 0,
                'total_visit' => 0,
                'visit_ac' => 0,
                'visit_ac_percent' => 0,
                'visit_xc' => 0,
                'visit_xc_percent' => 0,
                'total_visit_percent' => 0,
                'total_ec' => 0,
                'ec_pc' => 0,
                'ec_pc_percent' => 0,
                'ec_xc' => 0,
                'ec_xc_percent' => 0,
                'total_ec_percent' => 0,
                'total_order_val' => 0,
                'val_pc' => 0,
                'val_xc' => 0,
                'aov' => 0,
                'total_work_str' => '0 Jam',
                'time_in_store_percent' => 0,
                'consume_badge' => '0j (avg 0m)',
                'travel_badge' => '0j (avg 0m)',
            ];
        }

        // 1, 2 & 3. KPI 1 (Total Plan Call), KPI 2 (Actual Visit) & KPI 3 (Effective Call) - Distinct per TANGGAL, MUID, CUSTNO
        $subDailyQuery = (clone $this->getBaseQuery())
            ->select(
                'rvah.TANGGAL',
                'rvah.MUID',
                'rvah.CUSTNO',
                DB::raw('MAX(rvah."FLAG_PJP") as flag_pjp'),
                DB::raw('MAX(CASE WHEN rvah."FLAG_VISIT" = \'Y\' THEN 1 ELSE 0 END) as is_visited'),
                DB::raw('MAX(CASE WHEN rvah."FLAG_EC" = \'Y\' THEN 1 ELSE 0 END) as is_ec')
            )
            ->groupBy('rvah.TANGGAL', 'rvah.MUID', 'rvah.CUSTNO');

        $dailyStats = DB::query()->fromSub($subDailyQuery, 'sub')
            ->select(
                DB::raw('COUNT(*) as total_call'),
                DB::raw("COALESCE(SUM(CASE WHEN flag_pjp = 'R' THEN 1 ELSE 0 END), 0) as pc"),
                DB::raw("COALESCE(SUM(CASE WHEN flag_pjp = 'N' THEN 1 ELSE 0 END), 0) as xc"),
                DB::raw("COALESCE(SUM(CASE WHEN is_visited = 1 THEN 1 ELSE 0 END), 0) as total_visit"),
                DB::raw("COALESCE(SUM(CASE WHEN is_visited = 1 AND flag_pjp = 'R' THEN 1 ELSE 0 END), 0) as visit_ac"),
                DB::raw("COALESCE(SUM(CASE WHEN is_visited = 1 AND flag_pjp = 'N' THEN 1 ELSE 0 END), 0) as visit_xc"),
                DB::raw("COALESCE(SUM(CASE WHEN is_ec = 1 THEN 1 ELSE 0 END), 0) as total_ec"),
                DB::raw("COALESCE(SUM(CASE WHEN is_ec = 1 AND flag_pjp = 'R' THEN 1 ELSE 0 END), 0) as ec_pc"),
                DB::raw("COALESCE(SUM(CASE WHEN is_ec = 1 AND flag_pjp = 'N' THEN 1 ELSE 0 END), 0) as ec_xc")
            )->first();

        $totalCall = (int) ($dailyStats->total_call ?? 0);
        $pc = (int) ($dailyStats->pc ?? 0);
        $xc = (int) ($dailyStats->xc ?? 0);
        $pcCallPercent = $totalCall > 0 ? round(($pc / $totalCall) * 100, 1) : 0;
        $xcCallPercent = $totalCall > 0 ? round(($xc / $totalCall) * 100, 1) : 0;

        $totalVisit = (int) ($dailyStats->total_visit ?? 0);
        $visitAc = (int) ($dailyStats->visit_ac ?? 0);
        $visitXc = (int) ($dailyStats->visit_xc ?? 0);

        // Card 2: AC % terhadap PC, XC % terhadap XC
        $visitAcPercent = $pc > 0 ? round(($visitAc / $pc) * 100, 1) : 0;
        $visitXcPercent = $xc > 0 ? round(($visitXc / $xc) * 100, 1) : 0;
        $totalVisitPercent = $totalCall > 0 ? round(($totalVisit / $totalCall) * 100, 1) : 0;

        // Card 3: EC vs Actual Visit (Konversi Kunjungan ke Order)
        $totalEc = (int) ($dailyStats->total_ec ?? 0);
        $ecPc = (int) ($dailyStats->ec_pc ?? 0);
        $ecXc = (int) ($dailyStats->ec_xc ?? 0);

        $totalEcPercent = $totalVisit > 0 ? round(($totalEc / $totalVisit) * 100, 1) : 0;
        $ecPcPercent = $visitAc > 0 ? round(($ecPc / $visitAc) * 100, 1) : 0;
        $ecXcPercent = $visitXc > 0 ? round(($ecXc / $visitXc) * 100, 1) : 0;

        // Card 4 & 5: Total Order Val & Waktu Kunjungan (Consume vs Travel)
        $stats = $this->getBaseQuery()
            ->select(
                DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as total_order_val'),
                DB::raw('SUM(CASE WHEN rvah."FLAG_PJP" = \'R\' THEN COALESCE(rvah."ORDER_VAL", 0) ELSE 0 END) as val_pc'),
                DB::raw('SUM(CASE WHEN rvah."FLAG_PJP" = \'N\' THEN COALESCE(rvah."ORDER_VAL", 0) ELSE 0 END) as val_xc'),
                DB::raw("
                    SUM(EXTRACT(EPOCH FROM (CASE 
                        WHEN rvah.\"TIME_CONSUME\" ~ '^[0-9]{1,2}:[0-9]{2}:[0-9]{2}' 
                        THEN rvah.\"TIME_CONSUME\"::interval 
                        ELSE '00:00:00'::interval 
                    END))) as total_consume_seconds
                "),
                DB::raw("
                    AVG(CASE 
                        WHEN rvah.\"FLAG_VISIT\" = 'Y' AND rvah.\"TIME_CONSUME\" ~ '^[0-9]{1,2}:[0-9]{2}:[0-9]{2}' AND rvah.\"TIME_CONSUME\" != '00:00:00'
                        THEN EXTRACT(EPOCH FROM rvah.\"TIME_CONSUME\"::interval) 
                    END) as avg_consume_seconds
                "),
                DB::raw("
                    SUM(EXTRACT(EPOCH FROM (CASE 
                        WHEN rvah.\"TIME_TRAVEL\" ~ '^[0-9]{1,2}:[0-9]{2}:[0-9]{2}' 
                        THEN rvah.\"TIME_TRAVEL\"::interval 
                        ELSE '00:00:00'::interval 
                    END))) as total_travel_seconds
                "),
                DB::raw("
                    AVG(CASE 
                        WHEN rvah.\"TIME_TRAVEL\" ~ '^[0-9]{1,2}:[0-9]{2}:[0-9]{2}' AND rvah.\"TIME_TRAVEL\" != '00:00:00'
                        THEN EXTRACT(EPOCH FROM rvah.\"TIME_TRAVEL\"::interval) 
                    END) as avg_travel_seconds
                ")
            )->first();

        $totalOrderVal = (float) ($stats->total_order_val ?? 0);
        $valPc = (float) ($stats->val_pc ?? 0);
        $valXc = (float) ($stats->val_xc ?? 0);
        $aov = $totalEc > 0 ? round($totalOrderVal / $totalEc) : 0;

        $totalConsumeSec = (float) ($stats->total_consume_seconds ?? 0);
        $avgConsumeSec = (float) ($stats->avg_consume_seconds ?? 0);
        $totalTravelSec = (float) ($stats->total_travel_seconds ?? 0);
        $avgTravelSec = (float) ($stats->avg_travel_seconds ?? 0);
        $totalWorkSec = $totalConsumeSec + $totalTravelSec;

        $timeInStorePercent = $totalWorkSec > 0 ? round(($totalConsumeSec / $totalWorkSec) * 100, 1) : 0;

        $totalWorkHours = floor($totalWorkSec / 3600);
        $totalWorkStr = $totalWorkHours > 0 ? number_format($totalWorkHours, 0, ',', '.') . ' Jam' : round($totalWorkSec / 60) . ' Menit';

        $totalConsumeHours = floor($totalConsumeSec / 3600);
        $consumeHoursStr = $totalConsumeHours > 0 ? number_format($totalConsumeHours, 0, ',', '.') . 'j' : round($totalConsumeSec / 60) . 'm';
        $avgConsumeStr = 'avg ' . round($avgConsumeSec / 60) . 'm';

        $totalTravelHours = floor($totalTravelSec / 3600);
        $travelHoursStr = $totalTravelHours > 0 ? number_format($totalTravelHours, 0, ',', '.') . 'j' : round($totalTravelSec / 60) . 'm';
        $avgTravelStr = 'avg ' . round($avgTravelSec / 60) . 'm';

        return [
            'total_call' => $totalCall,
            'pc' => $pc,
            'xc' => $xc,
            'pc_call_percent' => $pcCallPercent,
            'xc_call_percent' => $xcCallPercent,
            'total_visit' => $totalVisit,
            'visit_ac' => $visitAc,
            'visit_ac_percent' => $visitAcPercent,
            'visit_xc' => $visitXc,
            'visit_xc_percent' => $visitXcPercent,
            'total_visit_percent' => $totalVisitPercent,
            'total_ec' => $totalEc,
            'ec_pc' => $ecPc,
            'ec_pc_percent' => $ecPcPercent,
            'ec_xc' => $ecXc,
            'ec_xc_percent' => $ecXcPercent,
            'total_ec_percent' => $totalEcPercent,
            'total_order_val' => $totalOrderVal,
            'val_pc' => $valPc,
            'val_xc' => $valXc,
            'aov' => $aov,
            'total_work_str' => $totalWorkStr,
            'time_in_store_percent' => $timeInStorePercent,
            'consume_badge' => "{$consumeHoursStr} ({$avgConsumeStr})",
            'travel_badge' => "{$travelHoursStr} ({$avgTravelStr})",
        ];
    }

    #[Computed]
    public function mapPointsData()
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea) || empty($this->appliedDistributor) || empty($this->appliedSalesman)) {
            return [];
        }

        $query = $this->getBaseQuery()
            ->select(
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvah.CUSTNAME as custname',
                'rvah.MUNAME as salesman_name',
                DB::raw('rvah."TANGGAL"::date as tanggal')
            )
            ->where('rvah.FLAG_VISIT', 'Y')
            ->whereNotNull('rvah.V_LA')
            ->whereNotNull('rvah.V_LG');

        return $query->get()->map(function($row) {
            return [
                'lat' => $row->visit_lat,
                'lon' => $row->visit_lon,
                'name' => $row->custname,
                'spv' => $row->salesman_name,
                'date' => $row->tanggal ?? '-'
            ];
        })->toArray();
    }

    /**
     * Trigger modal and send map points data on demand via Livewire event.
     */
    public function openAllMapsModal(): void
    {
        if (empty($this->appliedRegion) || empty($this->appliedArea) || empty($this->appliedDistributor) || empty($this->appliedSalesman)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu untuk melihat peta sebaran.', type: 'warning');
            return;
        }

        $points = $this->mapPointsData();
        if (empty($points)) {
            $this->dispatch('notify', msg: 'Tidak ada titik koordinat kunjungan yang ditemukan pada filter ini.', type: 'info');
            return;
        }

        $this->dispatch('open-all-maps-modal', points: $points);
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        if (empty($this->appliedRegion) || empty($this->appliedArea) || empty($this->appliedDistributor) || empty($this->appliedSalesman)) {
            $this->dispatch('notify', msg: 'Harap terapkan filter terlebih dahulu sebelum melakukan export.', type: 'error');
            return;
        }

        $query = $this->getBaseQuery()
            ->select(
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
                DB::raw('rvah."TIME_PAUSE"::time as time_pause'),
                'rvah.ORDER_QTY as qty_order',
                'rvah.ORDER_VAL as val_order',
                'rvah.FLAG_PJP as flag_pjp',
                'rvah.FLAG_VISIT as flag_visit',
                'rvah.FLAG_EC as flag_ec',
                'rvah.FLAG_BUY as flag_buy',
                'rvah.FLAG_PAUSE as flag_pause',
                'rvah.M_LA as master_lat',
                'rvah.M_LG as master_lon',
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvar.REASON_TYPE as reason_type',
                'rvar.REASON_DESC as reason_desc'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            });

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return Excel::download(new AnalisaKunjunganSeExport($query), 'analisa_kunjungan_se_' . date('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan-se.index', [
            'dailyData' => $this->dailyData(),
            'dataKunjungan' => $this->dataKunjungan(),
            'kpiStats' => $this->kpiStats(),
            'mapPointsData' => $this->mapPointsData(),
            'canExport' => $this->canExport,
            'accessLevel' => $this->accessLevel,
            'activeTab' => $this->activeTab,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'distributors' => $this->distributors,
            'salesmen' => $this->salesmen,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedDistributor' => $this->selectedDistributor,
            'selectedSalesman' => $this->selectedSalesman,
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedSalesman' => $this->appliedSalesman,
            'appliedStartDate' => $this->appliedStartDate,
            'appliedEndDate' => $this->appliedEndDate,
        ])->layout('layouts.app');
    }
}

