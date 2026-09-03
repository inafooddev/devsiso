<?php

namespace App\Livewire\Report\ReaktivasiToko;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\ReportReaktivasiToko;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
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

    #[Url]
    public $filterStatus = ''; // 'aktif', 'tidak_aktif'

    #[Url]
    public $filterType = ''; // 'SO', 'G', 'SG', 'R'

    public function mount()
    {
        if (empty($this->filterBulan)) $this->filterBulan = date('m');
        if (empty($this->filterTahun)) $this->filterTahun = date('Y');
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'filterBulan', 'filterTahun', 'filterRegion', 'filterArea', 'filterSupervisor', 'filterDistributor', 'filterStatus', 'filterType'])) {
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
        $this->reset(['search', 'filterRegion', 'filterArea', 'filterSupervisor', 'filterDistributor', 'filterStatus', 'filterType']);
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
    public function stores()
    {
        $selectedDate = Carbon::createFromDate($this->filterTahun ?: date('Y'), $this->filterBulan ?: date('m'), 1);
        $monthStart = $selectedDate->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $selectedDate->copy()->endOfMonth()->format('Y-m-d');
        
        // 6 months average (prior to selected month)
        // e.g. If selected month is August, then calculate from February 1st to July 31st
        $avgMonthStart = $selectedDate->copy()->subMonths(6)->format('Y-m-01');
        $avgMonthEnd = $selectedDate->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $yearStart = "{$this->filterTahun}-01-01";
        $yearEnd = "{$this->filterTahun}-12-31";

        $query = ReportReaktivasiToko::query()
            ->select(
                'uniq_kd', 'custno',
                DB::raw("MAX(custname) as custname"),
                DB::raw("MAX(alamat) as alamat"),
                DB::raw("MAX(region) as region"),
                DB::raw("MAX(area) as area"),
                DB::raw("MAX(supervisor) as supervisor"),
                DB::raw("MAX(distributor) as distributor"),
                DB::raw("MAX(bulan) as last_transaksi"),
                DB::raw("SUM(CASE WHEN bulan >= '$yearStart' AND bulan <= '$yearEnd' THEN neto ELSE 0 END) as total_transaksi"),
                DB::raw("SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6 as avg_6_months"),
                DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END) as pencapaian_bulan_ini")
            );

        $query = $this->applyRbac($query);

        // Add 12 columns for the selected year
        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            $start = "{$this->filterTahun}-{$m}-01";
            $end = Carbon::parse($start)->endOfMonth()->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN bulan >= '$start' AND bulan <= '$end' THEN neto ELSE 0 END) as bln_$m"));
        }

        $query->groupBy('uniq_kd', 'custno')
              ->orderByRaw("MAX(region) ASC")
              ->orderByRaw("MAX(area) ASC")
              ->orderByRaw("MAX(distributor) ASC")
              ->orderByRaw("(SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6) DESC");

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('custname', 'ilike', '%' . $this->search . '%')
                  ->orWhere('custno', 'ilike', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filterRegion)) {
            $query->where('region', $this->filterRegion);
        }

        if (!empty($this->filterArea)) {
            $query->where('area', $this->filterArea);
        }

        if (!empty($this->filterSupervisor)) {
            $query->where('supervisor', $this->filterSupervisor);
        }

        if (!empty($this->filterDistributor)) {
            $query->where('distributor', $this->filterDistributor);
        }

        if ($this->filterStatus === 'aktif') {
            $query->having(DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END)"), '>', 0);
        } elseif ($this->filterStatus === 'tidak_aktif') {
            $query->having(DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END)"), '<=', 0)
                  ->orHavingRaw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END) IS NULL");
        }

        $avgCalc = "SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6";
        if (!empty($this->filterType)) {
            if ($this->filterType === 'SO') {
                $query->having(DB::raw($avgCalc), '>', 10000000);
            } elseif ($this->filterType === 'G') {
                $query->having(DB::raw($avgCalc), '>=', 5000000)
                      ->having(DB::raw($avgCalc), '<=', 10000000);
            } elseif ($this->filterType === 'SG') {
                $query->having(DB::raw($avgCalc), '>=', 3000000)
                      ->having(DB::raw($avgCalc), '<', 5000000);
            } elseif ($this->filterType === 'R') {
                $query->having(DB::raw($avgCalc), '<', 3000000)
                      ->orHavingRaw("($avgCalc) IS NULL");
            }
        }

        return $query->paginate(30);
    }

    public function render()
    {
        return view('livewire.report.reaktivasi-toko.index')->layout('layouts.app');
    }
}
