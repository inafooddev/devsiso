<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifMasterDistributor;
use App\Services\InsentifCalculatorService;

class Summary extends Component
{
    public $filterBulan;
    public $filterRegion = '';
    public $filterArea = '';
    public $filterLevel = '';
    public $search = '';

    public function updatedFilterRegion()
    {
        $this->filterArea = ''; // reset area saat region berubah
    }

    public function mount()
    {
        $latest = InsentifMasterDistributor::max('bulan');
        $this->filterBulan = $latest ?: date('Y-m');
    }

    public function render()
    {
        $listBulan = InsentifMasterDistributor::select('bulan')
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan');

        if ($listBulan->isEmpty()) {
            $listBulan = collect([$this->filterBulan]);
        }

        $listRegion = InsentifMasterDistributor::select('region_name')
            ->whereNotNull('region_name')
            ->distinct()
            ->orderBy('region_name')
            ->pluck('region_name');

        $listArea = collect();
        if ($this->filterRegion) {
            $listArea = InsentifMasterDistributor::select('area_name')
                ->where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->distinct()
                ->orderBy('area_name')
                ->pluck('area_name');
        }

        $service = new InsentifCalculatorService();
        $summaryData = collect();
        $grandTotalInsentif = 0;

        if ($this->filterBulan) {
            $region = $this->filterRegion ?: null;
            $area  = $this->filterArea ?: null;

            // Level order: KACAB=1, SPV=2, SE=3 (untuk sorting)
            $levelOrder = ['KACAB' => 1, 'SPV' => 2, 'SE' => 3];

            // Get KACAB
            if ($this->filterLevel == '' || $this->filterLevel == 'KACAB') {
                $kacabDataRaw = $service->calculateKacab($this->filterBulan, $region, $area, $this->search);
                foreach ($kacabDataRaw as $kacab) {
                    if ($kacab['trf'] > 0) {
                        $summaryData->push([
                            'level' => 'KACAB',
                            'level_order' => 1,
                            'area_name' => $kacab['area_name'],
                            'cabang' => $kacab['cabang'],
                            'nama' => $kacab['nama_kacab'],
                            'kode' => '-',
                            'thp' => $kacab['trf']
                        ]);
                        $grandTotalInsentif += $kacab['trf'];
                    }
                }
            }

            // Get SPV
            if ($this->filterLevel == '' || $this->filterLevel == 'SPV') {
                $spvDataRaw = $service->calculateSpv($this->filterBulan, $region, $area, $this->search);
                foreach ($spvDataRaw['spvData'] as $spv) {
                    if ($spv['transfer_70'] > 0) {
                        $areaName = '';
                        $cabangs = [];
                        foreach ($spv['cabangs'] as $c => $cData) {
                            $cabangs[] = $c;
                            if (empty($areaName) && !empty($cData['area_name'])) {
                                $areaName = $cData['area_name'];
                            }
                        }
                        $firstCabang = $cabangs[0] ?? '';

                        $summaryData->push([
                            'level' => 'SPV',
                            'level_order' => 2,
                            'area_name' => $areaName,
                            'cabang' => implode(', ', $cabangs),
                            'cabang_sort' => $firstCabang, // untuk sort
                            'nama' => $spv['supervisor_name'],
                            'kode' => $spv['supervisor_code'],
                            'thp' => $spv['transfer_70']
                        ]);
                        $grandTotalInsentif += $spv['transfer_70'];
                    }
                }
            }

            // Get SE
            if ($this->filterLevel == '' || $this->filterLevel == 'SE') {
                $seDataRaw = $service->calculateSe($this->filterBulan, $region, $area, $this->search);
                foreach ($seDataRaw['salesmenData'] as $se) {
                    if ($se['thp'] > 0) {
                        $summaryData->push([
                            'level' => 'SE',
                            'level_order' => 3,
                            'area_name' => $se['area_name'],
                            'cabang' => $se['cabang'],
                            'cabang_sort' => $se['cabang'],
                            'nama' => $se['salesman_name'],
                            'kode' => $se['salesman_code'],
                            'thp' => $se['thp']
                        ]);
                        $grandTotalInsentif += $se['thp'];
                    }
                }
            }
        }

        // Sort: area_name -> cabang -> level (KACAB, SPV, SE)
        $summaryData = $summaryData->sortBy([
            ['area_name', 'asc'],
            ['cabang', 'asc'],
            ['level_order', 'asc'],
        ])->values();

        return view('livewire.others.insentif.perhitungan.summary', [
            'listBulan' => $listBulan,
            'listRegion' => $listRegion,
            'listArea' => $listArea,
            'summaryData' => $summaryData,
            'grandTotalInsentif' => $grandTotalInsentif
        ]);
    }
}
