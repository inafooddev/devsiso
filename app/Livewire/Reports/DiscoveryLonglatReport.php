<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\DiscoveryLonglatExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DiscoveryLonglatReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $isReady = true;
    
    public $selectedRegion = '';
    public $selectedArea = '';

    protected $queryString = ['startDate', 'endDate', 'selectedRegion', 'selectedArea'];

    public $regions = [];
    public $areas = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $this->regions = DB::table('master_regions')->orderBy('region_name')->get()->toArray();
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        if ($value) {
            $this->areas = DB::table('master_areas')->where('region_code', $value)->orderBy('area_name')->get()->toArray();
        } else {
            $this->areas = [];
        }
    }

    public function processData()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
        
        $this->resetPage();
        $this->isReady = true;
    }

    public function downloadExcel()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $fileName = 'Discovery_LongLat_' . $this->startDate . '_to_' . $this->endDate . '.xlsx';
        return Excel::download(new DiscoveryLonglatExport($this->startDate, $this->endDate, $this->selectedRegion, $this->selectedArea), $fileName);
    }

    public function render()
    {
        $data = collect();
        $summaryData = collect();

        if ($this->isReady) {
            $safeStart = Carbon::parse($this->startDate)->format('Y-m-d');
            $safeEnd = Carbon::parse($this->endDate)->format('Y-m-d');

            $baseQuery = DB::table('customer_prc_eska as cpe')
                ->join(DB::raw("(
                    SELECT 
                        \"BID\",
                        \"CUSTNO\",
                        \"V_LA\",
                        \"V_LG\",
                        \"TANGGAL\",
                        ROW_NUMBER() OVER (PARTITION BY \"BID\", \"CUSTNO\" ORDER BY \"TANGGAL\" DESC) as rn
                    FROM rpt_visit_an_h
                    WHERE \"FLAG_PJP\" = 'R' 
                      AND \"FLAG_VISIT\" = 'Y'
                      AND \"TANGGAL\"::date BETWEEN '{$safeStart}' AND '{$safeEnd}'
                      AND \"V_LA\" IS NOT NULL AND \"V_LA\" <> 0 AND \"V_LA\" BETWEEN -90 AND 90
                      AND \"V_LG\" IS NOT NULL AND \"V_LG\" <> 0 AND \"V_LG\" BETWEEN -180 AND 180
                ) as lv"), function($join) {
                    $join->on('cpe.kodecabang', '=', 'lv.BID')
                         ->on('cpe.custno', '=', 'lv.CUSTNO');
                })
                ->where(function($q) {
                    $q->where('cpe.la', '0')
                      ->orWhereNull('cpe.la')
                      ->orWhere('cpe.la', '');
                })
                ->where(function($q) {
                    $q->where('cpe.lg', '0')
                      ->orWhereNull('cpe.lg')
                      ->orWhere('cpe.lg', '');
                })
                ->where('lv.rn', 1);

            $summaryQuery = (clone $baseQuery)
                ->select('cpe.region_code', DB::raw('COUNT(*) as total'))
                ->groupBy('cpe.region_code')
                ->get()
                ->keyBy('region_code');

            $summaryData = collect($this->regions)->map(function($region) use ($summaryQuery) {
                return (object) [
                    'region_code' => $region->region_code,
                    'region_name' => $region->region_name,
                    'total' => isset($summaryQuery[$region->region_code]) ? $summaryQuery[$region->region_code]->total : 0
                ];
            });

            $query = clone $baseQuery;
            if ($this->selectedRegion) {
                $query->where('cpe.region_code', $this->selectedRegion);
            }
            if ($this->selectedArea) {
                $query->where('cpe.area_code', $this->selectedArea);
            }

            $data = $query
                ->select(
                    'cpe.kodecabang',
                    'cpe.custno',
                    'cpe.custname',
                    'lv.V_LA',
                    'lv.V_LG',
                    'lv.TANGGAL',
                    'cpe.region_code',
                    'cpe.area_code'
                )->paginate(100);
        }

        return view('livewire.reports.discovery-longlat-report', [
            'data' => $data,
            'summaryData' => $summaryData
        ])->layout('layouts.app');
    }
}
