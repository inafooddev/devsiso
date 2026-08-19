<?php

namespace App\Livewire\Others\Insentif\Mingguan;

use Livewire\Component;
use App\Models\InsentifMingguanMasterDistributor as InsentifMasterDistributor;
use App\Services\MingguanInsentifCalculatorService as InsentifCalculatorService;
use Illuminate\Support\Facades\Auth;

class Summary extends Component
{
    public $filterBulan;
    public $filterRegion = '';
    public $filterArea   = '';
    public $filterLevel  = '';
    public $search       = '';

    // Hak akses: dikunci berdasarkan user, null = bebas
    protected $lockedRegions = null; // array of region_name yang boleh
    protected $lockedAreas   = null; // array of area_name yang boleh

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
    }

    public function mount()
    {
        $latest = InsentifMasterDistributor::max('bulan');
        $this->filterBulan = $latest ?: date('Y-m');

        $user = Auth::user();
        $level = $user->getAccessLevel();

        // Kunci region / area berdasarkan hak akses user
        if ($level === 'region') {
            $regionCodes = (array) $user->region_code;
            $this->lockedRegions = InsentifMasterDistributor::whereIn('region_code', $regionCodes)
                ->whereNotNull('region_name')
                ->distinct()
                ->pluck('region_name')
                ->toArray();

            // Auto-set region jika hanya satu
            if (count($this->lockedRegions) === 1) {
                $this->filterRegion = $this->lockedRegions[0];
            }

        } elseif ($level === 'area') {
            $areaCodes = (array) $user->area_code;
            $rows = InsentifMasterDistributor::whereIn('area_code', $areaCodes)
                ->whereNotNull('area_name')
                ->distinct()
                ->get(['region_name', 'area_name']);

            $this->lockedAreas   = $rows->pluck('area_name')->unique()->values()->toArray();
            $this->lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();

            // Auto-set region & area jika masing-masing hanya satu
            if (count($this->lockedRegions) === 1) {
                $this->filterRegion = $this->lockedRegions[0];
            }
            if (count($this->lockedAreas) === 1) {
                $this->filterArea = $this->lockedAreas[0];
            }
        }
        // supervisor & nasional: tidak dikunci
    }

    public function render()
    {
        $user        = Auth::user();
        $accessLevel = $user->getAccessLevel();

        // ── Daftar Bulan ────────────────────────────────────────────────
        $listBulan = InsentifMasterDistributor::select('bulan')
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan');

        if ($listBulan->isEmpty()) {
            $listBulan = collect([$this->filterBulan]);
        }

        // ── Daftar Region (dibatasi hak akses) ─────────────────────────
        $regionQuery = InsentifMasterDistributor::select('region_name')
            ->whereNotNull('region_name')
            ->distinct()
            ->orderBy('region_name');

        if ($this->lockedRegions !== null) {
            $regionQuery->whereIn('region_name', $this->lockedRegions);
        }
        $listRegion = $regionQuery->pluck('region_name');

        // ── Daftar Area (dibatasi hak akses + region yang dipilih) ──────
        $listArea = collect();
        if ($this->filterRegion) {
            $areaQuery = InsentifMasterDistributor::select('area_name')
                ->where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->distinct()
                ->orderBy('area_name');

            // Jika user level area, batasi area yang tampil
            if ($this->lockedAreas !== null) {
                $areaQuery->whereIn('area_name', $this->lockedAreas);
            }
            $listArea = $areaQuery->pluck('area_name');
        }

        // ── Kalkulasi data ───────────────────────────────────────────────
        $service          = new InsentifCalculatorService();
        $summaryData      = collect();
        $grandTotalInsentif = 0;

        if ($this->filterBulan) {
            // Tentukan filter region & area efektif
            // - jika user dikunci, override dengan locked values bila filter user tidak pilih apa-apa
            $region = $this->filterRegion ?: null;
            $area   = $this->filterArea   ?: null;

            // Jika user level area dan tidak memilih area spesifik → pakai semua area yang boleh
            if ($this->lockedAreas !== null && !$area) {
                $area = $this->lockedAreas;
            }
            // Jika user level region dan tidak memilih region → pakai semua region yang boleh
            if ($this->lockedRegions !== null && !$region) {
                $region = count($this->lockedRegions) === 1 ? $this->lockedRegions[0] : null;
                // untuk multi-region di level region, biarkan null → service filter by area locked
            }

            // Get KACAB
            if ($this->filterLevel == '' || $this->filterLevel == 'KACAB') {
                $kacabDataRaw = $service->calculateKacab($this->filterBulan, $region, $area, $this->search);
                foreach ($kacabDataRaw as $kacab) {
                    if ($kacab['trf'] > 0) {
                        $summaryData->push([
                            'level'       => 'KACAB',
                            'level_order' => 1,
                            'area_name'   => $kacab['area_name'],
                            'cabang'      => $kacab['cabang'],
                            'nama'        => $kacab['nama_kacab'],
                            'kode'        => '-',
                            'thp'         => $kacab['trf'],
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
                        $cabangs  = [];
                        foreach ($spv['cabangs'] as $c => $cData) {
                            $cabangs[] = $c;
                            if (empty($areaName) && !empty($cData['area_name'])) {
                                $areaName = $cData['area_name'];
                            }
                        }
                        $summaryData->push([
                            'level'       => 'SPV',
                            'level_order' => 2,
                            'area_name'   => $areaName,
                            'cabang'      => implode(', ', $cabangs),
                            'cabang_sort' => $cabangs[0] ?? '',
                            'nama'        => $spv['supervisor_name'],
                            'kode'        => $spv['supervisor_code'],
                            'thp'         => $spv['transfer_70'],
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
                            'level'       => 'SE',
                            'level_order' => 3,
                            'area_name'   => $se['area_name'],
                            'cabang'      => $se['cabang'],
                            'cabang_sort' => $se['cabang'],
                            'nama'        => $se['salesman_name'],
                            'kode'        => $se['salesman_code'],
                            'thp'         => $se['thp'],
                        ]);
                        $grandTotalInsentif += $se['thp'];
                    }
                }
            }
        }

        // Sort: area → cabang → level_order
        $summaryData = $summaryData->sortBy([
            ['area_name',   'asc'],
            ['cabang',      'asc'],
            ['level_order', 'asc'],
        ])->values();

        return view('livewire.others.insentif.mingguan.summary', [
            'listBulan'          => $listBulan,
            'listRegion'         => $listRegion,
            'listArea'           => $listArea,
            'summaryData'        => $summaryData,
            'grandTotalInsentif' => $grandTotalInsentif,
            'accessLevel'        => $accessLevel,
        ]);
    }
}
