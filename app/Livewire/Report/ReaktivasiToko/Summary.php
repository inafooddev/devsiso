<?php

namespace App\Livewire\Report\ReaktivasiToko;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\ReportReaktivasiToko;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Summary extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $filterBulan = '';

    #[Url]
    public $filterTahun = '';

    #[Url]
    public $filterRegion = '';

    #[Url]
    public $filterArea = '';

    #[Url]
    public $filterSupervisor = '';

    #[Url]
    public $filterDistributor = '';

    public function mount()
    {
        if (empty($this->filterBulan)) $this->filterBulan = date('m');
        if (empty($this->filterTahun)) $this->filterTahun = date('Y');
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'filterBulan', 'filterTahun', 'filterRegion', 'filterArea', 'filterSupervisor', 'filterDistributor'])) {
            $this->resetPage();
        }

        if ($property === 'filterRegion') {
            $this->filterArea = '';
            $this->filterSupervisor = '';
            $this->filterDistributor = '';
        } elseif ($property === 'filterArea') {
            $this->filterSupervisor = '';
            $this->filterDistributor = '';
        } elseif ($property === 'filterSupervisor') {
            $this->filterDistributor = '';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterRegion', 'filterArea', 'filterSupervisor', 'filterDistributor']);
        $this->filterBulan = date('m');
        $this->filterTahun = date('Y');
        $this->resetPage();
    }

    public function applyRbac($query)
    {
        $user = auth()->user();
        if (!$user) return $query;

        $accessLevel = $user->getAccessLevel();

        if ($accessLevel === 'supervisor') {
            $query->where('supervisor_code', $user->supervisor_code);
        } elseif ($accessLevel === 'area') {
            $query->whereIn('area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'region') {
            $query->whereIn('region_code', (array) $user->region_code);
        }

        return $query;
    }

    #[Computed]
    public function hierarchy()
    {
        $userId = auth()->id();
        return \Illuminate\Support\Facades\Cache::remember('reaktivasi_toko_hierarchy_' . $userId, 3600, function () {
            $query = ReportReaktivasiToko::select('region', 'area', 'supervisor', 'distributor')
                ->whereNotNull('region')
                ->distinct();
            return $this->applyRbac($query)->get();
        });
    }

    #[Computed]
    public function filterOptions()
    {
        $data = $this->hierarchy;

        if (!empty($this->filterRegion)) {
            $data = $data->where('region', $this->filterRegion);
        }

        $areas = $data->pluck('area')->filter()->unique()->sort();

        if (!empty($this->filterArea)) {
            $data = $data->where('area', $this->filterArea);
        }

        $supervisors = $data->pluck('supervisor')->filter()->unique()->sort();

        if (!empty($this->filterSupervisor)) {
            $data = $data->where('supervisor', $this->filterSupervisor);
        }

        $distributors = $data->pluck('distributor')->filter()->unique()->sort();

        return [
            'regions' => $this->hierarchy->pluck('region')->filter()->unique()->sort(),
            'areas' => $areas,
            'supervisors' => $supervisors,
            'distributors' => $distributors,
        ];
    }

    #[Computed]
    public function kpiStats()
    {
        $summaries = $this->summaries(true); 
        
        $totalToko = $summaries->sum('total_toko');
        $totalAktif = $summaries->sum('total_aktif');
        $gap = $totalToko - $totalAktif;
        $totalTransaksi = $summaries->sum('total_transaksi_rp');

        return [
            'total_toko' => $totalToko,
            'total_aktif' => $totalAktif,
            'pct_aktif' => $totalToko > 0 ? round(($totalAktif / $totalToko) * 100, 1) : 0,
            'gap' => $gap,
            'pct_gap' => $totalToko > 0 ? round(($gap / $totalToko) * 100, 1) : 0,
            'total_transaksi' => $totalTransaksi,
            
            'types' => [
                'SO' => [
                    'total' => $t = $summaries->sum('total_so'),
                    'aktif' => $a = $summaries->sum('aktif_so'),
                    'gap' => $g = $t - $a,
                    'pct_aktif' => $t > 0 ? round(($a / $t) * 100, 1) : 0,
                    'pct_gap' => $t > 0 ? round(($g / $t) * 100, 1) : 0,
                ],
                'G' => [
                    'total' => $t = $summaries->sum('total_g'),
                    'aktif' => $a = $summaries->sum('aktif_g'),
                    'gap' => $g = $t - $a,
                    'pct_aktif' => $t > 0 ? round(($a / $t) * 100, 1) : 0,
                    'pct_gap' => $t > 0 ? round(($g / $t) * 100, 1) : 0,
                ],
                'SG' => [
                    'total' => $t = $summaries->sum('total_sg'),
                    'aktif' => $a = $summaries->sum('aktif_sg'),
                    'gap' => $g = $t - $a,
                    'pct_aktif' => $t > 0 ? round(($a / $t) * 100, 1) : 0,
                    'pct_gap' => $t > 0 ? round(($g / $t) * 100, 1) : 0,
                ],
                'R' => [
                    'total' => $t = $summaries->sum('total_r'),
                    'aktif' => $a = $summaries->sum('aktif_r'),
                    'gap' => $g = $t - $a,
                    'pct_aktif' => $t > 0 ? round(($a / $t) * 100, 1) : 0,
                    'pct_gap' => $t > 0 ? round(($g / $t) * 100, 1) : 0,
                ],
            ]
        ];
    }

    public function summaries($forKpi = false)
    {
        $selectedDate = Carbon::createFromDate($this->filterTahun ?: date('Y'), $this->filterBulan ?: date('m'), 1);
        $monthStart = $selectedDate->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $selectedDate->copy()->endOfMonth()->format('Y-m-d');
        
        $avgMonthStart = $selectedDate->copy()->subMonths(6)->format('Y-m-01');
        $avgMonthEnd = $selectedDate->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $baseQuery = ReportReaktivasiToko::query()
            ->select(
                'uniq_kd', 'custno',
                DB::raw("MAX(region) as region"),
                DB::raw("MAX(area) as area"),
                DB::raw("MAX(supervisor) as supervisor"),
                DB::raw("MAX(distributor) as distributor"),
                DB::raw("SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6 as avg_6_months"),
                DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END) as pencapaian_bulan_ini")
            );

        $baseQuery = $this->applyRbac($baseQuery);
        $baseQuery->groupBy('uniq_kd', 'custno');

        if (!empty($this->filterRegion)) $baseQuery->where('region', $this->filterRegion);
        if (!empty($this->filterArea)) $baseQuery->where('area', $this->filterArea);
        if (!empty($this->filterSupervisor)) $baseQuery->where('supervisor', $this->filterSupervisor);
        if (!empty($this->filterDistributor)) $baseQuery->where('distributor', $this->filterDistributor);

        $sql = $baseQuery->toSql();
        $bindings = $baseQuery->getBindings();

        $query = DB::table(DB::raw("({$sql}) as sub"))
            ->mergeBindings($baseQuery->getQuery())
            ->select(
                'region', 'area', 'supervisor', 'distributor',
                DB::raw('COUNT(*) as total_toko'),
                DB::raw('SUM(CASE WHEN pencapaian_bulan_ini > 0 THEN 1 ELSE 0 END) as total_aktif'),
                DB::raw('SUM(CASE WHEN pencapaian_bulan_ini <= 0 OR pencapaian_bulan_ini IS NULL THEN 1 ELSE 0 END) as gap'),
                DB::raw('SUM(pencapaian_bulan_ini) as total_transaksi_rp'),
                
                DB::raw('SUM(CASE WHEN avg_6_months > 10000000 THEN 1 ELSE 0 END) as total_so'),
                DB::raw('SUM(CASE WHEN avg_6_months > 10000000 AND pencapaian_bulan_ini > 0 THEN 1 ELSE 0 END) as aktif_so'),
                
                DB::raw('SUM(CASE WHEN avg_6_months >= 5000000 AND avg_6_months <= 10000000 THEN 1 ELSE 0 END) as total_g'),
                DB::raw('SUM(CASE WHEN avg_6_months >= 5000000 AND avg_6_months <= 10000000 AND pencapaian_bulan_ini > 0 THEN 1 ELSE 0 END) as aktif_g'),
                
                DB::raw('SUM(CASE WHEN avg_6_months >= 3000000 AND avg_6_months < 5000000 THEN 1 ELSE 0 END) as total_sg'),
                DB::raw('SUM(CASE WHEN avg_6_months >= 3000000 AND avg_6_months < 5000000 AND pencapaian_bulan_ini > 0 THEN 1 ELSE 0 END) as aktif_sg'),
                
                DB::raw('SUM(CASE WHEN avg_6_months < 3000000 OR avg_6_months IS NULL THEN 1 ELSE 0 END) as total_r'),
                DB::raw('SUM(CASE WHEN (avg_6_months < 3000000 OR avg_6_months IS NULL) AND pencapaian_bulan_ini > 0 THEN 1 ELSE 0 END) as aktif_r')
            )
            ->groupBy('region', 'area', 'supervisor', 'distributor')
            ->orderBy('region')
            ->orderBy('area')
            ->orderBy('supervisor')
            ->orderBy('distributor');

        if (!empty($this->search)) {
            $query->where('distributor', 'ilike', '%' . $this->search . '%')
                  ->orWhere('supervisor', 'ilike', '%' . $this->search . '%');
        }

        if ($forKpi) {
            return $query->get();
        }

        return $query->paginate(150);
    }

    #[Computed]
    public function paginatedSummaries()
    {
        return $this->summaries(false);
    }

    public function render()
    {
        return view('livewire.report.reaktivasi-toko.summary')->layout('layouts.app');
    }
}
