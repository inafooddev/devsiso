<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifMasterDistributor;
use Illuminate\Support\Facades\DB;

class InsentifSpv extends Component
{
    public $filterBulan;
    public $filterRegion;
    public $filterArea;
    public $search = '';

    public function mount()
    {
        $this->filterBulan = '2026-07'; // Default for testing, usually date('Y-m')
        
        $firstRegion = DB::table('insentif_master_distributors')
            ->whereNotNull('region_name')
            ->orderBy('region_name')
            ->value('region_name');
            
        $this->filterRegion = $firstRegion ?? '';
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
    }

    public function render()
    {
        $listBulan = InsentifMasterDistributor::select('bulan')->distinct()->orderBy('bulan', 'desc')->pluck('bulan');
        $listRegions = InsentifMasterDistributor::select('region_name')->whereNotNull('region_name')->distinct()->orderBy('region_name')->pluck('region_name');
        
        $listAreas = collect();
        if ($this->filterRegion) {
            $listAreas = InsentifMasterDistributor::select('area_name')
                ->where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->distinct()
                ->orderBy('area_name')
                ->pluck('area_name');
        }

        $spvData = [];

        if ($this->filterBulan && $this->filterRegion) {
            // Target bulan in target_per_depo is stored as date, e.g. '2026-07-01'
            $targetMonthFilter = $this->filterBulan . '-01';

            $query = InsentifMasterDistributor::where('bulan', $this->filterBulan)
                ->where('region_name', $this->filterRegion);

            if ($this->filterArea) {
                $query->where('area_name', $this->filterArea);
            }

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('supervisor_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('supervisor_code', 'ilike', '%' . $this->search . '%')
                      ->orWhere('distributor_name', 'ilike', '%' . $this->search . '%');
                });
            }

            $masterData = $query->get();

            // Fetch Targets
            $targets = DB::table('target_per_depo')
                ->where('bulan', $targetMonthFilter)
                ->get()
                ->keyBy('cabang');

            // Fetch Actuals per distributor
            $actuals = DB::table('insentif_value_per_salesmans')
                ->select('distributor_code', DB::raw('SUM(actual) as total_actual'))
                ->where('bulan', $this->filterBulan)
                ->groupBy('distributor_code')
                ->get()
                ->keyBy('distributor_code');

            $groupedBySpv = [];

            foreach ($masterData as $md) {
                $spvCode = $md->supervisor_code ?? 'NO_SPV';
                $spvName = $md->supervisor_name ?? 'Tanpa Supervisor';
                $cabang = $md->cabang;
                $distCode = $md->distributor_code;

                if (!isset($groupedBySpv[$spvCode])) {
                    $groupedBySpv[$spvCode] = [
                        'supervisor_code' => $spvCode,
                        'supervisor_name' => $spvName,
                        'distributors' => [],
                        'total_target_reguler' => 0,
                        'total_aktual_so' => 0,
                        'rowspan' => 0
                    ];
                }

                $target = isset($targets[$cabang]) ? (float)$targets[$cabang]->target : 0;
                $actual = isset($actuals[$distCode]) ? (float)$actuals[$distCode]->total_actual : 0;

                $groupedBySpv[$spvCode]['distributors'][] = [
                    'distributor_code' => $distCode,
                    'distributor_name' => $md->distributor_name,
                    'cabang' => $cabang,
                    'target_so' => $target,
                    'aktual_so' => $actual,
                ];

                $groupedBySpv[$spvCode]['total_target_reguler'] += $target;
                $groupedBySpv[$spvCode]['total_aktual_so'] += $actual;
                $groupedBySpv[$spvCode]['rowspan'] += 1;
            }

            // Calculate Percentages
            foreach ($groupedBySpv as $spvCode => &$spv) {
                $spv['pencapaian_persen'] = 0;
                if ($spv['total_target_reguler'] > 0) {
                    $spv['pencapaian_persen'] = ($spv['total_aktual_so'] / $spv['total_target_reguler']) * 100;
                }
                $spv['ins_so'] = 0; // TBD mechanism
            }

            // Sort SPVs alphabetically
            usort($groupedBySpv, function($a, $b) {
                return strcmp($a['supervisor_name'], $b['supervisor_name']);
            });

            $spvData = $groupedBySpv;
        }

        return view('livewire.others.insentif.perhitungan.insentif-spv', [
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'spvData' => $spvData
        ]);
    }
}
