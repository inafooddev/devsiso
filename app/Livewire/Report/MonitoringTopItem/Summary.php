<?php

namespace App\Livewire\Report\MonitoringTopItem;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Summary extends Component
{
    public $month;
    public $year;
    public $region = '';

    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function updatedRegion() { /* trigger re-render */ }

    public function render()
    {
        $period = sprintf('%04d-%02d-01', $this->year, $this->month);
        $user = auth()->user();
        $accessLevel = $user ? $user->getAccessLevel() : 'nasional';

        $regionsQuery = DB::table('master_distributors')
            ->select('region_name')
            ->distinct()
            ->whereNotNull('region_name');

        // Hak Akses untuk filter dropdown
        if ($accessLevel === 'region') {
            $regionsQuery->whereIn('region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $regionsQuery->whereIn('area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $regionsQuery->where('supervisor_code', $user->supervisor_code);
        }

        $regions = $regionsQuery->orderBy('region_name')->pluck('region_name');

        // Subquery: Calculate how many products each store bought
        $innerQuery = DB::table('top_item_achievement as tia')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->leftJoin('master_produk_lama as mpl', 'tia.pcode_prc', '=', 'mpl.pcode_prc')
            ->where('tia.period', $period)
            ->where('tia.qty', '>', 0)
            ->when($this->region, function ($q) {
                $q->where('md.region_name', $this->region);
            });
            
        // Hak Akses untuk data query
        if ($accessLevel === 'region') {
            $innerQuery->whereIn('md.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $innerQuery->whereIn('md.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $innerQuery->where('md.supervisor_code', $user->supervisor_code);
        }

        $innerQuery->select(
                'md.region_name',
                'md.area_name',
                'md.distributor_name',
                'f.SLSNAME as supervisor_name',
                'tia.uniq_code', 
                DB::raw("COUNT(DISTINCT CASE WHEN mpl.kategory IN ('NPD', 'TOPITEM') THEN mpl.topitem END) as prd_count")
            )
            ->groupBy(
                'md.region_name', 
                'md.area_name', 
                'md.distributor_name', 
                'f.SLSNAME', 
                'tia.uniq_code'
            );

        // Outer query: Group by Region, Area, Supervisor, Distributor and sum the buckets
        $query = DB::query()
            ->fromSub($innerQuery, 'store_stats')
            ->select(
                'region_name',
                'area_name',
                'supervisor_name',
                'distributor_name',
                DB::raw('COUNT(uniq_code) as total_toko_aktif'),
                DB::raw('SUM(CASE WHEN prd_count = 1 THEN 1 ELSE 0 END) as beli_1'),
                DB::raw('SUM(CASE WHEN prd_count = 2 THEN 1 ELSE 0 END) as beli_2'),
                DB::raw('SUM(CASE WHEN prd_count = 3 THEN 1 ELSE 0 END) as beli_3'),
                DB::raw('SUM(CASE WHEN prd_count = 4 THEN 1 ELSE 0 END) as beli_4'),
                DB::raw('SUM(CASE WHEN prd_count = 5 THEN 1 ELSE 0 END) as beli_5'),
                DB::raw('SUM(CASE WHEN prd_count = 6 THEN 1 ELSE 0 END) as beli_6')
            )
            ->groupBy(
                'region_name', 
                'area_name', 
                'supervisor_name', 
                'distributor_name'
            )
            ->orderBy('region_name')
            ->orderBy('area_name')
            ->orderBy('supervisor_name')
            ->orderBy('distributor_name');

        $rawData = $query->get();

        // Process data to build a hierarchical tree with subtotals
        $groupedData = [];

        $createTotalTemplate = fn() => [
            'total_toko_aktif' => 0,
            'beli_1' => 0, 'beli_2' => 0, 'beli_3' => 0, 
            'beli_4' => 0, 'beli_5' => 0, 'beli_6' => 0
        ];

        $grandTotal = $createTotalTemplate();

        foreach ($rawData as $row) {
            $region = $row->region_name ?? 'UNKNOWN';
            $area = $row->area_name ?? 'UNKNOWN';
            $spv = $row->supervisor_name ?? 'UNKNOWN';

            if (!isset($groupedData[$region])) {
                $groupedData[$region] = [
                    'totals' => $createTotalTemplate(),
                    'areas' => []
                ];
            }

            if (!isset($groupedData[$region]['areas'][$area])) {
                $groupedData[$region]['areas'][$area] = [
                    'totals' => $createTotalTemplate(),
                    'spvs' => []
                ];
            }

            if (!isset($groupedData[$region]['areas'][$area]['spvs'][$spv])) {
                $groupedData[$region]['areas'][$area]['spvs'][$spv] = [
                    'totals' => $createTotalTemplate(),
                    'distributors' => []
                ];
            }

            $groupedData[$region]['areas'][$area]['spvs'][$spv]['distributors'][] = $row;

            // Accumulate totals
            $metrics = ['total_toko_aktif', 'beli_1', 'beli_2', 'beli_3', 'beli_4', 'beli_5', 'beli_6'];
            foreach ($metrics as $metric) {
                $val = (int) $row->$metric;
                $groupedData[$region]['totals'][$metric] += $val;
                $groupedData[$region]['areas'][$area]['totals'][$metric] += $val;
                $groupedData[$region]['areas'][$area]['spvs'][$spv]['totals'][$metric] += $val;
                $grandTotal[$metric] += $val;
            }
        }

        return view('livewire.report.monitoring-top-item.summary', [
            'groupedData' => $groupedData,
            'grandTotal' => $grandTotal,
            'regions' => $regions
        ]);
    }
}
