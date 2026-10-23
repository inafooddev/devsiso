<?php

namespace App\Livewire\Report\AnalisaKunjunganSe;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisaKunjunganSeReasonExport;

class Reason extends Component
{
    use EnforcesMenuPermissions;

    #[Title('Summary Reason Kunjungan SE')]
    protected string $menuRoute = 'report.analisa-kunjungan-se.index';

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

    #[Url(as: 'type')]
    public $reasonType = 'BUY'; // 'BUY', 'VISIT', 'ALL'

    #[Url(as: 'q')]
    public $search = '';

    // Applied Filters
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedDistributor = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';
    public $appliedReasonType = 'BUY';

    // Table Sorting (Default: Area, then Distributor)
    public $sortField = 'territory';
    public $sortDirection = 'asc';

    // Drilldown Modal
    public $showDetailModal = false;
    public $modalSalesmanName = '';
    public $modalSalesmanCode = '';
    public $modalFilterReason = null;
    public $modalStoreList = [];

    public function mount()
    {
        $this->authorizeAction('can_view');
        $this->canExport = auth()->check() && auth()->user()->hasMenuAccess($this->menuRoute, 'can_export');
        $this->resolveUserAccess();

        if (empty($this->startDate) || empty($this->endDate)) {
            $this->setDatePreset('this_month');
        }

        $this->loadRegions();

        // Restore filter dari session jika URL kosong
        if (empty($this->selectedRegion) && session()->has('analisa_kunjungan_se_shared_filter')) {
            $saved = session()->get('analisa_kunjungan_se_shared_filter');
            $this->selectedRegion = $saved['region'] ?? '';
            $this->selectedArea = $saved['area'] ?? '';
            $this->selectedDistributor = $saved['distributor'] ?? '';
            if (!empty($saved['startDate'])) $this->startDate = $saved['startDate'];
            if (!empty($saved['endDate'])) $this->endDate = $saved['endDate'];
        }

        if (!empty($this->selectedRegion)) {
            $this->updatedSelectedRegion($this->selectedRegion);
            if (!empty($this->selectedArea)) {
                $this->updatedSelectedArea($this->selectedArea);
                if (!empty($this->selectedDistributor)) {
                    $this->updatedSelectedDistributor($this->selectedDistributor);
                }
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
                    $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                } elseif ($start->diffInDays($end) > 31) {
                    $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
                }
            } else {
                $this->endDate = $start->copy()->addDays(30)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Keep existing values
        }
    }

    public function updatedEndDate($value): void
    {
        if (empty($value)) return;

        try {
            $end = Carbon::parse($value);
            $start = !empty($this->startDate) ? Carbon::parse($this->startDate) : null;

            if ($start) {
                if ($start->gt($end)) {
                    $this->startDate = $end->copy()->subDays(30)->format('Y-m-d');
                } elseif ($start->diffInDays($end) > 31) {
                    $this->startDate = $end->copy()->subDays(30)->format('Y-m-d');
                }
            } else {
                $this->startDate = $end->copy()->subDays(30)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Keep existing values
        }
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
            case 'this_week':
                $this->startDate = $today->copy()->startOfWeek()->format('Y-m-d');
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
        }
    }

    protected function loadRegions()
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

            if (count($this->distributors) === 1 && empty($this->selectedDistributor)) {
                $this->selectedDistributor = $this->distributors[0]->distributor_code;
            }
        }
    }

    public function updatedSelectedDistributor($value)
    {
        if ($value && $this->accessLevel === 'supervisor' && !empty($this->allowedDistributorCodes) && !in_array($value, $this->allowedDistributorCodes)) {
            $this->selectedDistributor = '';
        }
    }

    public function setReasonType(string $type)
    {
        if (in_array($type, ['BUY', 'VISIT', 'ALL'])) {
            $this->reasonType = $type;
            $this->appliedReasonType = $type;
        }
    }

    public function applyFilter()
    {
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedDistributor = $this->selectedDistributor;
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;
        $this->appliedReasonType = $this->reasonType;

        session()->put('analisa_kunjungan_se_shared_filter', [
            'region' => $this->appliedRegion,
            'area' => $this->appliedArea,
            'distributor' => $this->appliedDistributor,
            'salesman' => '',
            'startDate' => $this->appliedStartDate,
            'endDate' => $this->appliedEndDate,
        ]);
    }

    public function resetFilter()
    {
        session()->forget('analisa_kunjungan_se_shared_filter');

        $this->selectedRegion = count($this->regions) === 1 ? $this->regions[0]->region_code : '';
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->areas = [];
        $this->distributors = [];
        $this->search = '';
        $this->reasonType = 'BUY';
        $this->sortField = 'territory';
        $this->sortDirection = 'asc';

        $this->setDatePreset('this_month');

        if ($this->selectedRegion) {
            $this->updatedSelectedRegion($this->selectedRegion);
        }

        $this->applyFilter();
    }

    public function resetFilters(): void
    {
        $this->resetFilter();
    }

    public function updatedSearch(): void
    {
        // Search updates automatically reactively
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['territory', 'distributor_name', 'area_name', 'salesman_name', 'salesman_code']) ? 'asc' : 'desc';
        }
    }

    /**
     * Compute distinct reason descriptions in the applied scope and period (sorted by overall count desc).
     */
    #[Computed]
    public function reasonColumns()
    {
        if (empty($this->appliedRegion)) {
            return collect();
        }

        $query = DB::table('rpt_visit_an_h as rvah')
            ->join('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->where('rvah.RID', '!=', 'HOINA')
            ->where('rvah.RID', $this->appliedRegion);

        if (!empty($this->appliedArea)) {
            $query->where('rvah.EID', $this->appliedArea);
        }
        if (!empty($this->appliedDistributor)) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }
        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        }
        if ($this->appliedReasonType !== 'ALL') {
            $query->where('rvar.REASON_TYPE', $this->appliedReasonType);
        }

        return $query->select('rvar.REASON_DESC as raw_desc', DB::raw('COUNT(*) as total'))
            ->groupBy('rvar.REASON_DESC')
            ->orderByDesc('total')
            ->get()
            ->map(function($r) {
                $clean = preg_replace('/\s*\(.*?\)\s*/', '', (string)$r->raw_desc);
                $r->short_desc = trim($clean) !== '' ? trim($clean) : $r->raw_desc;
                return $r;
            });
    }

    /**
     * Compute raw grouped reason data per SE.
     */
    #[Computed]
    public function reasonData()
    {
        if (empty($this->appliedRegion)) {
            return collect();
        }

        $query = DB::table('rpt_visit_an_h as rvah')
            ->join('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->where('rvah.RID', '!=', 'HOINA')
            ->where('rvah.RID', $this->appliedRegion);

        if (!empty($this->appliedArea)) {
            $query->where('rvah.EID', $this->appliedArea);
        }
        if (!empty($this->appliedDistributor)) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }
        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        }
        if ($this->appliedReasonType !== 'ALL') {
            $query->where('rvar.REASON_TYPE', $this->appliedReasonType);
        }

        $rows = $query->select(
            'rvah.MUID as salesman_code',
            'rvah.MUNAME as salesman_name',
            'rvah.BID as distributor_code',
            'rvah.BNAME as distributor_name',
            'rvah.EID as area_code',
            'rvah.ENAME as area_name',
            'rvar.REASON_DESC as reason_desc',
            DB::raw('COUNT(*) as count')
        )
        ->groupBy(
            'rvah.MUID',
            'rvah.MUNAME',
            'rvah.BID',
            'rvah.BNAME',
            'rvah.EID',
            'rvah.ENAME',
            'rvar.REASON_DESC'
        )
        ->get();

        // Group by salesman_code
        $grouped = $rows->groupBy('salesman_code')->map(function($items) {
            $first = $items->first();
            $reasonsMap = [];
            $total = 0;
            $maxCount = 0;
            $maxDesc = '';

            foreach ($items as $item) {
                $cnt = (int)$item->count;
                $reasonsMap[$item->reason_desc] = $cnt;
                $total += $cnt;
                if ($cnt > $maxCount) {
                    $maxCount = $cnt;
                    $maxDesc = $item->reason_desc;
                }
            }

            return (object)[
                'salesman_code' => $first->salesman_code,
                'salesman_name' => $first->salesman_name,
                'distributor_code' => $first->distributor_code,
                'distributor_name' => $first->distributor_name,
                'area_code' => $first->area_code,
                'area_name' => $first->area_name,
                'reasons' => $reasonsMap,
                'total_reason' => $total,
                'max_count' => $maxCount,
                'max_desc' => $maxDesc,
            ];
        })->values();

        // Filter search term
        if (!empty($this->search)) {
            $term = strtolower(trim($this->search));
            $grouped = $grouped->filter(function($row) use ($term) {
                return str_contains(strtolower($row->salesman_name ?? ''), $term) ||
                       str_contains(strtolower($row->salesman_code ?? ''), $term) ||
                       str_contains(strtolower($row->distributor_name ?? ''), $term) ||
                       str_contains(strtolower($row->area_name ?? ''), $term);
            })->values();
        }

        // Sorting
        $sortField = $this->sortField;
        $isDesc = $this->sortDirection === 'desc';

        if ($sortField === 'territory' || $sortField === 'area_name' || $sortField === 'distributor_name') {
            return $grouped->sortBy([
                ['area_name', $isDesc ? 'desc' : 'asc'],
                ['distributor_name', $isDesc ? 'desc' : 'asc'],
                ['salesman_name', 'asc'],
            ])->values();
        }

        if ($sortField === 'salesman_name') {
            return $grouped->sortBy([
                ['salesman_name', $isDesc ? 'desc' : 'asc'],
                ['area_name', 'asc'],
                ['distributor_name', 'asc'],
            ])->values();
        }

        if ($sortField === 'salesman_code') {
            return $grouped->sortBy([
                ['salesman_code', $isDesc ? 'desc' : 'asc'],
                ['area_name', 'asc'],
                ['distributor_name', 'asc'],
            ])->values();
        }

        if ($sortField === 'total_reason') {
            return $grouped->sortBy([
                ['total_reason', $isDesc ? 'desc' : 'asc'],
                ['area_name', 'asc'],
                ['distributor_name', 'asc'],
            ])->values();
        }

        // Specific reason column sorting
        return $grouped->sortBy([
            [function($row) use ($sortField) {
                return $row->reasons[$sortField] ?? 0;
            }, $isDesc ? 'desc' : 'asc'],
            ['area_name', 'asc'],
            ['distributor_name', 'asc'],
            ['salesman_name', 'asc'],
        ])->values();
    }

    /**
     * Compute Column Totals across all filtered SEs.
     */
    #[Computed]
    public function columnTotals()
    {
        $data = $this->reasonData();
        $cols = $this->reasonColumns();
        $totals = [];

        foreach ($cols as $col) {
            $totals[$col->raw_desc] = $data->sum(fn($row) => $row->reasons[$col->raw_desc] ?? 0);
        }
        $totals['__grand_total__'] = $data->sum('total_reason');

        return $totals;
    }

    /**
     * Compute KPI cards summary.
     */
    #[Computed]
    public function summaryKPIs()
    {
        $data = $this->reasonData();
        $cols = $this->reasonColumns();

        $totalCases = $data->sum('total_reason');
        $totalSe = $data->count();
        $avgPerSe = $totalSe > 0 ? round($totalCases / $totalSe, 1) : 0;
        $topReason = $cols->first();

        return (object)[
            'total_cases' => $totalCases,
            'total_se' => $totalSe,
            'avg_per_se' => $avgPerSe,
            'top_reason_name' => $topReason ? $topReason->short_desc : '-',
            'top_reason_count' => $topReason ? $topReason->total : 0,
        ];
    }

    /**
     * Drilldown modal: view list of stores for a specific SE.
     */
    public function openDetailModal($salesmanCode, $reasonDesc = null)
    {
        $this->modalSalesmanCode = $salesmanCode;
        $this->modalFilterReason = $reasonDesc;

        $seRow = $this->reasonData()->firstWhere('salesman_code', $salesmanCode);
        $this->modalSalesmanName = $seRow ? $seRow->salesman_name : $salesmanCode;

        $query = DB::table('rpt_visit_an_h as rvah')
            ->join('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->where('rvah.MUID', $salesmanCode)
            ->where('rvah.RID', $this->appliedRegion);

        if (!empty($this->appliedArea)) {
            $query->where('rvah.EID', $this->appliedArea);
        }
        if (!empty($this->appliedDistributor)) {
            $query->where('rvah.BID', $this->appliedDistributor);
        }
        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween('rvah.TANGGAL', [$this->appliedStartDate, $this->appliedEndDate]);
        }
        if ($this->appliedReasonType !== 'ALL') {
            $query->where('rvar.REASON_TYPE', $this->appliedReasonType);
        }
        if (!empty($reasonDesc)) {
            $query->where('rvar.REASON_DESC', $reasonDesc);
        }

        $this->modalStoreList = $query->select(
            DB::raw('rvah."TANGGAL"::date as tanggal'),
            DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
            DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
            'rvah.CUSTNO as custno',
            'rvah.CUSTNAME as custname',
            'rvah.FLAG_PJP as flag_pjp',
            'rvah.FLAG_VISIT as flag_visit',
            'rvah.FLAG_BUY as flag_buy',
            'rvar.REASON_TYPE as reason_type',
            'rvar.REASON_DESC as reason_desc',
            'rvar.REASON_NOTE as reason_note'
        )
        ->orderBy(DB::raw('rvah."TANGGAL"::date'), 'desc')
        ->orderBy('rvah.TIME_IN', 'asc')
        ->limit(300)
        ->get()
        ->toArray();

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->modalStoreList = [];
        $this->modalFilterReason = null;
    }

    public function exportExcel()
    {
        $this->authorizeAction('can_export');
        $rows = $this->reasonData();

        if ($rows->isEmpty()) {
            $this->dispatch('notify', msg: 'Tidak ada data untuk diexport.', type: 'error');
            return;
        }

        $typeLabel = match($this->appliedReasonType) {
            'BUY' => 'NotBuy',
            'VISIT' => 'NotVisit',
            default => 'All',
        };

        $filename = 'Summary_Reason_' . $typeLabel . '_SE_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AnalisaKunjunganSeReasonExport($rows, $this->reasonColumns(), $this->columnTotals()), $filename);
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan-se.reason', [
            'columns' => $this->reasonColumns(),
            'rows' => $this->reasonData(),
            'totals' => $this->columnTotals(),
            'kpis' => $this->summaryKPIs(),
        ])->layout('layouts.app');
    }
}
