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
                    'area_name' => $md->area_name,
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

            // Calculate Percentages and Insentif SO
            foreach ($groupedBySpv as $spvCode => $spv) {
                $pencapaian = 0;
                $targetTotal = $spv['total_target_reguler'];
                
                if ($targetTotal > 0) {
                    $pencapaian = ($spv['total_aktual_so'] / $targetTotal) * 100;
                }
                
                $groupedBySpv[$spvCode]['pencapaian_persen'] = $pencapaian;
                
                // INS SO Calculation
                $insSo = 0;
                
                if ($pencapaian >= 120) {
                    if ($targetTotal >= 2000000000) $insSo = 2500000;
                    elseif ($targetTotal >= 1000000000) $insSo = 2250000;
                    else $insSo = 2000000;
                } elseif ($pencapaian >= 110) {
                    if ($targetTotal >= 2000000000) $insSo = 2250000;
                    elseif ($targetTotal >= 1000000000) $insSo = 2000000;
                    else $insSo = 1750000;
                } elseif ($pencapaian >= 100) {
                    if ($targetTotal >= 2000000000) $insSo = 2000000;
                    elseif ($targetTotal >= 1000000000) $insSo = 1750000;
                    else $insSo = 1500000;
                } elseif ($pencapaian >= 90) {
                    if ($targetTotal >= 2000000000) $insSo = 500000;
                    elseif ($targetTotal >= 1000000000) $insSo = 400000;
                    else $insSo = 300000;
                }
                
                $groupedBySpv[$spvCode]['ins_so'] = $insSo;
            }

            // Sort SPVs by Area then Supervisor Name
            usort($groupedBySpv, function($a, $b) {
                $areaA = $a['distributors'][0]['area_name'] ?? '';
                $areaB = $b['distributors'][0]['area_name'] ?? '';
                
                if ($areaA === $areaB) {
                    return strcmp($a['supervisor_name'], $b['supervisor_name']);
                }
                return strcmp($areaA, $areaB);
            });

            $spvData = $groupedBySpv;
        }

        // Calculate Grand Totals
        $grandTotal = [
            'target_so' => 0,
            'aktual_so' => 0,
            'ins_so' => 0,
            'pencapaian_persen' => 0
        ];

        foreach ($spvData as $spv) {
            foreach ($spv['distributors'] as $dist) {
                $grandTotal['target_so'] += $dist['target_so'];
                $grandTotal['aktual_so'] += $dist['aktual_so'];
            }
            $grandTotal['ins_so'] += $spv['ins_so'];
        }

        if ($grandTotal['target_so'] > 0) {
            $grandTotal['pencapaian_persen'] = ($grandTotal['aktual_so'] / $grandTotal['target_so']) * 100;
        }

        return view('livewire.others.insentif.perhitungan.insentif-spv', [
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'spvData' => $spvData,
            'grandTotal' => $grandTotal
        ]);
    }
}
