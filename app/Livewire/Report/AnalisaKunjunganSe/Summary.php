<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeSummaryExport;
use Carbon\Carbon;

class Summary extends Component
{
    use EnforcesMenuPermissions, WithPagination;

    #[Title('Summary Kunjungan SE')]
    protected string $menuRoute = 'report.analisa-kunjungan-se.index';

    public $activeTab = 'summary';

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

    // Filter Selections
    #[Url(as: 'region')]
    public $selectedRegion = '';

    #[Url(as: 'area')]
    public $selectedArea = '';

    #[Url(as: 'distributor')]
    public $selectedDistributor = '';

    #[Url(as: 'startDate')]
    public $startDate = '';

    #[Url(as: 'endDate')]
    public $endDate = '';

    #[Url(as: 'q')]
    public $search = '';

    // Applied Filters
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedDistributor = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';

    // Sorting
    public $sortField = 'distributor_name';
    public $sortDirection = 'asc';

    public function mount()
    {
        $this->authorizeAction('can_view');
        $this->canExport = auth()->check() && auth()->user()->hasMenuAccess($this->menuRoute, 'can_export');
        $this->resolveUserAccess();

        if (empty($this->startDate) || empty($this->endDate)) {
            $this->setDatePreset('this_month');
        }

        $this->loadRegions();

        // If URL has pre-filled region, apply them automatically
        if (!empty($this->selectedRegion)) {
            $this->updatedSelectedRegion($this->selectedRegion);
            if (!empty($this->selectedArea)) {
                $this->updatedSelectedArea($this->selectedArea);
            }
            $this->applyFilter();
        }
    }

    /**
     * Smart date range validation: ensures endDate >= startDate and duration <= 31 days.
     */
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

    /**
     * Smart date range validation: ensures startDate <= endDate and duration <= 31 days.
     */
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
        $today = Carbon::today();

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

    public function previousMonth(): void
    {
        $currentStart = !empty($this->startDate) ? Carbon::parse($this->startDate) : Carbon::today();
        $prevMonth = $currentStart->copy()->subMonthNoOverflow();
        $this->startDate = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
        $this->endDate = $prevMonth->copy()->endOfMonth()->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $currentStart = !empty($this->startDate) ? Carbon::parse($this->startDate) : Carbon::today();
        $nextMonth = $currentStart->copy()->addMonthNoOverflow();
        $today = Carbon::today();

        $this->startDate = $nextMonth->copy()->startOfMonth()->format('Y-m-d');
        $end = $nextMonth->copy()->endOfMonth();
        $this->endDate = $end->gt($today) ? $today->format('Y-m-d') : $end->format('Y-m-d');

        if (Carbon::parse($this->startDate)->gt($today)) {
            $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
            $this->endDate = $today->format('Y-m-d');
        }
    }

    protected function resolveUserAccess(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->accessLevel = $user->getAccessLevel();

        if ($this->accessLevel === 'region') {
            $this->allowedRegionCodes = (array) ($user->region_code ?? []);
        } elseif ($this->accessLevel === 'area') {
            $this->allowedAreaCodes = (array) ($user->area_code ?? []);
            $this->allowedRegionCodes = DB::table('master_areas')
                ->whereIn('area_code', $this->allowedAreaCodes)
                ->pluck('region_code')
                ->unique()
                ->filter()
                ->values()
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
        $this->areas = [];
        $this->distributors = [];

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

            // Load all distributors for this region by default (when area is "Semua Area")
            $this->updatedSelectedArea('');

            if (count($this->areas) === 1 && empty($this->selectedArea)) {
                $this->selectedArea = $this->areas[0]->area_code;
                $this->updatedSelectedArea($this->selectedArea);
            }
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->selectedDistributor = '';
        $this->distributors = [];

        if ($this->selectedRegion) {
            if ($value && in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes) && !in_array($value, $this->allowedAreaCodes)) {
                $this->selectedArea = '';
                return;
            }

            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('distributor_code', '!=', 'HOINA')
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct()
                ->orderBy('distributor_name');

            if (!empty($value)) {
                $query->where('area_code', $value);
            } elseif (in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes)) {
                $query->whereIn('area_code', $this->allowedAreaCodes);
            }

            if ($this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes)) {
                $query->whereIn('distributor_code', $this->allowedDistributorCodes);
            }

            $this->distributors = $query->get()->toArray();
        }
    }

    public function applyFilter()
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);

            if ($start->gt($end)) {
                $this->dispatch('notify', msg: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', type: 'error');
                return;
            }

            if ($start->diffInDays($end) > 30) {
                $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                $this->dispatch('notify', msg: 'Rentang waktu disesuaikan otomatis maksimal 31 hari.', type: 'info');
            }
        }

        if (empty($this->selectedRegion)) {
            $this->dispatch('notify', msg: 'Harap pilih minimal Region.', type: 'error');
            return;
        }

        if ($this->accessLevel === 'region' && !empty($this->allowedRegionCodes) && !in_array($this->selectedRegion, $this->allowedRegionCodes)) {
            $this->dispatch('notify', msg: 'Anda tidak memiliki akses ke Region ini.', type: 'error');
            return;
        }

        if (!empty($this->selectedArea) && in_array($this->accessLevel, ['area', 'supervisor']) && !empty($this->allowedAreaCodes) && !in_array($this->selectedArea, $this->allowedAreaCodes)) {
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
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;

        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->search = '';

        $this->setDatePreset('this_month');

        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedDistributor = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';

        $this->areas = [];
        $this->distributors = [];

        $this->loadRegions();
        $this->resetPage();
    }

    public function sortByColumn(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            // Text columns default to 'asc' (A-Z), numeric columns default to 'desc' (highest first)
            $this->sortDirection = in_array($field, ['distributor_name', 'distributor_code', 'salesman_name', 'salesman_code']) ? 'asc' : 'desc';
        }
    }

    public static function formatDurationHours($seconds)
    {
        if (!$seconds || $seconds <= 0) return '0j 0m';
        $h = floor($seconds / 3600);
        $m = round(($seconds % 3600) / 60);
        return "{$h}j {$m}m";
    }

    public static function formatDurationMinutes($seconds)
    {
        if (!$seconds || $seconds <= 0) return '0m 0d';
        $m = floor($seconds / 60);
        $s = round($seconds % 60);
        return "{$m}m {$s}d";
    }

    #[Computed]
    public function summaryData()
    {
        if (empty($this->appliedRegion)) {
            return collect();
        }

        $subQuery = DB::table('rpt_visit_an_h as rvah')
            ->where('rvah.RID', '!=', 'HOINA')
            ->where('rvah.RID', $this->appliedRegion);

        if (!empty($this->appliedArea)) {
            $subQuery->where('rvah.EID', $this->appliedArea);
        }

        // Security check
        if ($this->accessLevel === 'region' && !empty($this->allowedRegionCodes)) {
            $subQuery->whereIn('rvah.RID', $this->allowedRegionCodes);
        } elseif ($this->accessLevel === 'area' && !empty($this->allowedAreaCodes)) {
            $subQuery->whereIn('rvah.EID', $this->allowedAreaCodes);
        } elseif ($this->accessLevel === 'supervisor') {
            if (!empty($this->allowedDistributorCodes)) {
                $subQuery->whereIn('rvah.BID', $this->allowedDistributorCodes);
            } elseif (!empty($this->allowedAreaCodes)) {
                $subQuery->whereIn('rvah.EID', $this->allowedAreaCodes);
            }
        }

        if (!empty($this->appliedDistributor)) {
            $subQuery->where('rvah.BID', $this->appliedDistributor);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $subQuery->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $subQuery->where('rvah.TANGGAL', '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $subQuery->where('rvah.TANGGAL', '<=', $this->appliedEndDate);
        }

        // Subquery: Distinct per hari per toko (1 Toko per Hari = Maksimal 1 Call target & realisasi)
        $subQuery->select(
            'rvah.MUID as salesman_code',
            'rvah.MUNAME as salesman_name',
            'rvah.BID as distributor_code',
            'rvah.BNAME as distributor_name',
            'rvah.EID as area_code',
            'rvah.ENAME as area_name',
            'rvah.RID as region_code',
            'rvah.RNAME as region_name',
            DB::raw('rvah."TANGGAL"::date as tanggal'),
            'rvah.CUSTNO as custno',
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" = 'R' THEN 1 ELSE 0 END) as is_pjp"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" != 'R' THEN 1 ELSE 0 END) as is_xc"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as is_visit"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as is_ec"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" = 'R' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as is_pjp_visit"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" = 'R' AND rvah.\"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as is_pjp_ec"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" != 'R' AND rvah.\"FLAG_VISIT\" = 'Y' THEN 1 ELSE 0 END) as is_xc_visit"),
            DB::raw("MAX(CASE WHEN rvah.\"FLAG_PJP\" != 'R' AND rvah.\"FLAG_EC\" = 'Y' THEN 1 ELSE 0 END) as is_xc_ec"),
            DB::raw('SUM(COALESCE(rvah."ORDER_VAL", 0)) as cust_order_val'),
            DB::raw('SUM(COALESCE(EXTRACT(EPOCH FROM rvah."TIME_CONSUME"::time), 0)) as cust_consume_sec'),
            DB::raw('SUM(COALESCE(EXTRACT(EPOCH FROM rvah."TIME_TRAVEL"::time), 0)) as cust_travel_sec')
        )
        ->groupBy(
            'rvah.MUID',
            'rvah.MUNAME',
            'rvah.BID',
            'rvah.BNAME',
            'rvah.EID',
            'rvah.ENAME',
            'rvah.RID',
            'rvah.RNAME',
            DB::raw('rvah."TANGGAL"::date'),
            'rvah.CUSTNO'
        );

        // Main Query: Agregasi per SE per periode (Sum dari toko unik harian)
        $mainQuery = DB::table(DB::raw("({$subQuery->toSql()}) as d"))
            ->mergeBindings($subQuery)
            ->select(
                'salesman_code',
                'salesman_name',
                'distributor_code',
                'distributor_name',
                'area_code',
                'area_name',
                'region_code',
                'region_name',
                DB::raw('COUNT(DISTINCT tanggal) as total_days'),
                DB::raw('COUNT(*) as total_pc'),
                DB::raw('SUM(is_visit) as total_ac'),
                DB::raw('SUM(is_ec) as total_ec'),
                DB::raw('SUM(is_pjp) as exc_pc'),
                DB::raw('SUM(is_pjp_visit) as exc_ac'),
                DB::raw('SUM(is_pjp_ec) as exc_ec'),
                DB::raw('SUM(is_xc) as just_pc'),
                DB::raw('SUM(is_xc_visit) as just_ac'),
                DB::raw('SUM(is_xc_ec) as just_ec'),
                DB::raw('SUM(cust_order_val) as total_order_val'),
                DB::raw('SUM(cust_consume_sec) as total_consume_sec'),
                DB::raw('SUM(cust_travel_sec) as total_travel_sec')
            )
            ->groupBy(
                'salesman_code',
                'salesman_name',
                'distributor_code',
                'distributor_name',
                'area_code',
                'area_name',
                'region_code',
                'region_name'
            );

        $results = $mainQuery->get();

        // Calculate calculated fields & map
        $mapped = $results->map(function ($row) {
            $totalPc = (int) $row->total_pc;
            $totalAc = (int) $row->total_ac;
            $totalEc = (int) $row->total_ec;

            $excPc = (int) $row->exc_pc;
            $excAc = (int) $row->exc_ac;
            $excEc = (int) $row->exc_ec;

            $justPc = (int) $row->just_pc;
            $justAc = (int) $row->just_ac;
            $justEc = (int) $row->just_ec;

            $valOrder = (float) $row->total_order_val;
            $consumeSec = (float) $row->total_consume_sec;
            $travelSec = (float) $row->total_travel_sec;

            // Percentages
            $row->total_ac_percent = $totalPc > 0 ? round(($totalAc / $totalPc) * 100, 1) : 0;
            $row->total_ec_percent = $totalAc > 0 ? round(($totalEc / $totalAc) * 100, 1) : 0;

            $row->exc_ac_percent = $excPc > 0 ? round(($excAc / $excPc) * 100, 1) : 0;
            $row->exc_ec_percent = $excAc > 0 ? round(($excEc / $excAc) * 100, 1) : 0;

            $row->just_ac_percent = $justPc > 0 ? round(($justAc / $justPc) * 100, 1) : 0;
            $row->just_ec_percent = $justAc > 0 ? round(($justEc / $justAc) * 100, 1) : 0;

            // Averages per actual call (toko visit)
            $avgConsume = $totalAc > 0 ? round($consumeSec / $totalAc) : 0;
            $avgTravel = $totalAc > 0 ? round($travelSec / $totalAc) : 0;

            $row->avg_consume_sec = $avgConsume;
            $row->avg_travel_sec = $avgTravel;

            // Display Strings for Durations
            $row->total_jam_toko_str = self::formatDurationHours($consumeSec);
            $row->avg_jam_toko_str = self::formatDurationMinutes($avgConsume);
            $row->total_jam_jalan_str = self::formatDurationHours($travelSec);
            $row->avg_jam_jalan_str = self::formatDurationMinutes($avgTravel);

            return $row;
        });

        // Filter by search query if any
        if (!empty($this->search)) {
            $term = strtolower(trim($this->search));
            $mapped = $mapped->filter(function ($row) use ($term) {
                return str_contains(strtolower($row->salesman_name ?? ''), $term) ||
                       str_contains(strtolower($row->salesman_code ?? ''), $term) ||
                       str_contains(strtolower($row->distributor_name ?? ''), $term);
            });
        }

        // Sort collection
        $sortField = $this->sortField;
        $isDesc = $this->sortDirection === 'desc';

        if ($sortField === 'distributor_name') {
            return $mapped->sortBy([
                ['distributor_name', $isDesc ? 'desc' : 'asc'],
                ['salesman_name', 'asc'],
            ])->values();
        }

        return $mapped->sortBy([
            [$sortField, $isDesc ? 'desc' : 'asc'],
            ['distributor_name', 'asc'],
            ['salesman_name', 'asc'],
        ])->values();
    }

    #[Computed]
    public function grandTotal()
    {
        $rows = $this->summaryData();
        if ($rows->isEmpty()) return null;

        $totalDays = $rows->sum('total_days');

        $totalPc = $rows->sum('total_pc');
        $totalAc = $rows->sum('total_ac');
        $totalEc = $rows->sum('total_ec');

        $excPc = $rows->sum('exc_pc');
        $excAc = $rows->sum('exc_ac');
        $excEc = $rows->sum('exc_ec');

        $justPc = $rows->sum('just_pc');
        $justAc = $rows->sum('just_ac');
        $justEc = $rows->sum('just_ec');

        $totalOrderVal = $rows->sum('total_order_val');
        $totalConsumeSec = $rows->sum('total_consume_sec');
        $totalTravelSec = $rows->sum('total_travel_sec');

        $totalAcPercent = $totalPc > 0 ? round(($totalAc / $totalPc) * 100, 1) : 0;
        $totalEcPercent = $totalAc > 0 ? round(($totalEc / $totalAc) * 100, 1) : 0;

        $excAcPercent = $excPc > 0 ? round(($excAc / $excPc) * 100, 1) : 0;
        $excEcPercent = $excAc > 0 ? round(($excEc / $excAc) * 100, 1) : 0;

        $justAcPercent = $justPc > 0 ? round(($justAc / $justPc) * 100, 1) : 0;
        $justEcPercent = $justAc > 0 ? round(($justEc / $justAc) * 100, 1) : 0;

        $avgConsumeSec = $totalAc > 0 ? round($totalConsumeSec / $totalAc) : 0;
        $avgTravelSec = $totalAc > 0 ? round($totalTravelSec / $totalAc) : 0;

        return (object) [
            'total_se' => $rows->count(),
            'total_days' => $totalDays,
            'total_pc' => $totalPc,
            'total_ac' => $totalAc,
            'total_ec' => $totalEc,
            'total_ac_percent' => $totalAcPercent,
            'total_ec_percent' => $totalEcPercent,
            'exc_pc' => $excPc,
            'exc_ac' => $excAc,
            'exc_ec' => $excEc,
            'exc_ac_percent' => $excAcPercent,
            'exc_ec_percent' => $excEcPercent,
            'just_pc' => $justPc,
            'just_ac' => $justAc,
            'just_ec' => $justEc,
            'just_ac_percent' => $justAcPercent,
            'just_ec_percent' => $justEcPercent,
            'total_order_val' => $totalOrderVal,
            'total_consume_sec' => $totalConsumeSec,
            'total_travel_sec' => $totalTravelSec,
            'avg_consume_sec' => $avgConsumeSec,
            'avg_travel_sec' => $avgTravelSec,
            'total_jam_toko_str' => self::formatDurationHours($totalConsumeSec),
            'avg_jam_toko_str' => self::formatDurationMinutes($avgConsumeSec),
            'total_jam_jalan_str' => self::formatDurationHours($totalTravelSec),
            'avg_jam_jalan_str' => self::formatDurationMinutes($avgTravelSec),
        ];
    }

    #[Computed]
    public function kpiStats()
    {
        $gt = $this->grandTotal();
        if (!$gt) {
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

        $pcCallPercent = $gt->total_pc > 0 ? round(($gt->exc_pc / $gt->total_pc) * 100, 1) : 0;
        $xcCallPercent = $gt->total_pc > 0 ? round(($gt->just_pc / $gt->total_pc) * 100, 1) : 0;

        $totActiveSec = $gt->total_consume_sec + $gt->total_travel_sec;
        $timeInStorePercent = $totActiveSec > 0 ? (int) round(($gt->total_consume_sec / $totActiveSec) * 100) : 50;

        return [
            'total_call' => $gt->total_pc,
            'pc' => $gt->exc_pc,
            'xc' => $gt->just_pc,
            'pc_call_percent' => $pcCallPercent,
            'xc_call_percent' => $xcCallPercent,
            'total_visit' => $gt->total_ac,
            'visit_ac' => $gt->exc_ac,
            'visit_ac_percent' => $gt->exc_ac_percent,
            'visit_xc' => $gt->just_ac,
            'visit_xc_percent' => $gt->just_ac_percent,
            'total_visit_percent' => $gt->total_ac_percent,
            'total_ec' => $gt->total_ec,
            'ec_pc' => $gt->exc_ec,
            'ec_pc_percent' => $gt->exc_ec_percent,
            'ec_xc' => $gt->just_ec,
            'ec_xc_percent' => $gt->just_ec_percent,
            'total_ec_percent' => $gt->total_ec_percent,
            'total_order_val' => $gt->total_order_val,
            'val_pc' => $gt->total_order_val, // overall total
            'val_xc' => 0,
            'aov' => $gt->total_ec > 0 ? round($gt->total_order_val / $gt->total_ec) : 0,
            'total_work_str' => self::formatDurationHours($totActiveSec),
            'time_in_store_percent' => $timeInStorePercent,
            'consume_badge' => $gt->total_jam_toko_str . " ({$gt->avg_jam_toko_str})",
            'travel_badge' => $gt->total_jam_jalan_str . " ({$gt->avg_jam_jalan_str})",
        ];
    }

    public function exportExcel()
    {
        $this->authorizeAction('can_export');
        $rows = $this->summaryData();

        if ($rows->isEmpty()) {
            $this->dispatch('notify', msg: 'Tidak ada data untuk diexport.', type: 'error');
            return;
        }

        $filename = 'Summary_Kunjungan_SE_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AnalisaKunjunganSeSummaryExport($rows), $filename);
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan-se.summary', [
            'rows' => $this->summaryData(),
            'grandTotal' => $this->grandTotal(),
            'kpiStats' => $this->kpiStats(),
            'canExport' => $this->canExport,
            'accessLevel' => $this->accessLevel,
            'activeTab' => $this->activeTab,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'distributors' => $this->distributors,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedDistributor' => $this->selectedDistributor,
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedStartDate' => $this->appliedStartDate,
            'appliedEndDate' => $this->appliedEndDate,
            'search' => $this->search,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ])->layout('layouts.app');
    }
}
