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
    public $filterLevel  = 'SE';
    public $search       = '';

    // Hak akses dihitung secara dinamis di getAccessRestrictions() untuk mencegah state lost

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
    }

    private function getAccessRestrictions($user, $level)
    {
        $lockedRegions = null;
        $lockedAreas = null;

        if ($level === 'region') {
            $regionCodes = (array) $user->region_code;
            $lockedRegions = InsentifMasterDistributor::whereIn('region_code', $regionCodes)
                ->whereNotNull('region_name')
                ->distinct()
                ->pluck('region_name')
                ->toArray();
        } elseif ($level === 'area') {
            $areaCodes = (array) $user->area_code;
            $rows = InsentifMasterDistributor::whereIn('area_code', $areaCodes)
                ->whereNotNull('area_name')
                ->distinct()
                ->get(['region_name', 'area_name']);

            $lockedAreas = $rows->pluck('area_name')->unique()->values()->toArray();
            $lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();
        } elseif ($level === 'supervisor') {
            $sisoCodes = [$user->supervisor_code];
            $rows = InsentifMasterDistributor::whereIn('supervisor_code', $sisoCodes)
                ->whereNotNull('region_name')
                ->distinct()
                ->get(['region_name', 'area_name']);

            $lockedAreas = $rows->pluck('area_name')->unique()->values()->toArray();
            $lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();
        }

        return [$lockedRegions, $lockedAreas];
    }

    public function mount()
    {
        $latest = InsentifMasterDistributor::max('bulan');
        $this->filterBulan = $latest ?: date('Y-m');

        $user = Auth::user();
        $level = $user->getAccessLevel();

        [$lockedRegions, $lockedAreas] = $this->getAccessRestrictions($user, $level);

        if ($lockedRegions !== null && count($lockedRegions) === 1) {
            $this->filterRegion = $lockedRegions[0];
        }
        if ($lockedAreas !== null && count($lockedAreas) === 1) {
            $this->filterArea = $lockedAreas[0];
        }
    }

    public function render()
    {
        $user        = Auth::user();
        $accessLevel = $user->getAccessLevel();

        $sisoCodes = null;
        if ($accessLevel === 'supervisor') {
            $sisoCodes = [$user->supervisor_code];
        }

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

        [$lockedRegions, $lockedAreas] = $this->getAccessRestrictions($user, $accessLevel);

        if ($lockedRegions !== null) {
            $regionQuery->whereIn('region_name', $lockedRegions);
        }
        if ($accessLevel === 'supervisor') {
            if (empty($sisoCodes)) {
                $regionQuery->whereRaw('1 = 0');
            } else {
                $regionQuery->whereIn('supervisor_code', $sisoCodes);
            }
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
            if ($lockedAreas !== null) {
                $areaQuery->whereIn('area_name', $lockedAreas);
            }
            if ($accessLevel === 'supervisor') {
                if (empty($sisoCodes)) {
                    $areaQuery->whereRaw('1 = 0');
                } else {
                    $areaQuery->whereIn('supervisor_code', $sisoCodes);
                }
            }
            $listArea = $areaQuery->pluck('area_name');
        }

        // ── Kalkulasi data ───────────────────────────────────────────────
        $service          = new InsentifCalculatorService();
        $summaryData      = collect();
        
        $totalKacab = 0; $kacabGetIncentive = 0;
        $totalSpv = 0; $spvGetIncentive = 0;
        $totalSe = 0; $seGetIncentive = 0;
        
        $grandTotalInsentif = 0;

        if ($this->filterBulan) {
            // Tentukan filter region & area efektif
            // - jika user dikunci, override dengan locked values bila filter user tidak pilih apa-apa
            $region = $this->filterRegion ?: null;
            $area   = $this->filterArea   ?: null;

            // Jika user level area dan tidak memilih area spesifik → pakai semua area yang boleh
            if ($lockedAreas !== null && !$area) {
                $area = $lockedAreas;
            }
            // Jika user level region dan tidak memilih region → pakai semua region yang boleh
            if ($lockedRegions !== null && !$region) {
                $region = count($lockedRegions) === 1 ? $lockedRegions[0] : null;
                // untuk multi-region di level region, biarkan null → service filter by area locked
            }

            // Get KACAB
            if ($this->filterLevel == '' || $this->filterLevel == 'KACAB') {
                $kacabDataRaw = $service->calculateKacab($this->filterBulan, $region, $area, $this->search, $sisoCodes);
                $totalKacab += count($kacabDataRaw);
                foreach ($kacabDataRaw as $kacab) {
                    if ($kacab['trf'] > 0) {
                        $kacabGetIncentive++;
                        $grandTotalInsentif += $kacab['trf'];
                    }
                    $summaryData->push([
                        'level'       => 'KACAB',
                        'level_order' => 1,
                        'area_name'   => $kacab['area_name'],
                        'cabang'      => $kacab['cabang'],
                        'nama'        => $kacab['nama_kacab'],
                        'kode'        => '-',
                        'target'      => $kacab['target'] ?? 0,
                        'sell_out'    => $kacab['sell_out'] ?? 0,
                        'percentage'  => $kacab['percentage'] ?? 0,
                        'nilai_insentif'=> $kacab['nilai_insentif'] ?? 0,
                        'thp'         => $kacab['trf'],
                    ]);
                }
            }

            // Get SPV
            if ($this->filterLevel == '' || $this->filterLevel == 'SPV') {
                $spvDataRaw = $service->calculateSpv($this->filterBulan, $region, $area, $this->search, $sisoCodes);
                $totalSpv += count($spvDataRaw['spvData']);
                foreach ($spvDataRaw['spvData'] as $spv) {
                    if ($spv['transfer_70'] > 0) {
                        $spvGetIncentive++;
                        $grandTotalInsentif += $spv['transfer_70'];
                    }
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
                        'target'      => $spv['total_target_reguler'] ?? 0,
                        'actual'      => $spv['total_aktual_so'] ?? 0,
                        'pencapaian_persen' => $spv['pencapaian_persen'] ?? 0,
                        'ins_so'      => $spv['ins_so'] ?? 0,
                        'insentif_vtkp' => $spv['total_insentif_vtkp'] ?? 0,
                        'rwo_achieve_pct' => $spv['rwo_achieve_pct'] ?? 0,
                        'insentif_rwo'=> $spv['insentif_rwo'] ?? 0,
                        'ipt'         => $spv['ipt'] ?? 0,
                        'insentif_ipt'=> $spv['insentif_ipt'] ?? 0,
                        'tabungan_30' => $spv['tabungan_30'] ?? 0,
                        'transfer_70' => $spv['transfer_70'] ?? 0,
                        'total_all_insentif' => $spv['total_all_insentif'] ?? 0,
                        'thp'         => $spv['transfer_70'],
                    ]);
                }
            }

            // Get SE
            if ($this->filterLevel == '' || $this->filterLevel == 'SE') {
                $seDataRaw = $service->calculateSe($this->filterBulan, $region, $area, $this->search, $sisoCodes);
                $totalSe += count($seDataRaw['salesmenData']);
                foreach ($seDataRaw['salesmenData'] as $se) {
                    if ($se['thp'] > 0) {
                        $seGetIncentive++;
                        $grandTotalInsentif += $se['thp'];
                    }
                    $summaryData->push([
                        'level'       => 'SE',
                        'level_order' => 3,
                        'area_name'   => $se['area_name'],
                        'cabang'      => $se['cabang'],
                        'cabang_sort' => $se['cabang'],
                        'nama'        => $se['salesman_name'],
                        'kode'        => $se['salesman_code'],
                        'thp'         => $se['thp'],
                        'distributor'    => $se['distributor'] ?? '-',
                        'target'         => $se['target'] ?? 0,
                        'actual'         => $se['actual'] ?? 0,
                        'insentif_value' => $se['insentif_value'] ?? 0,
                        'ach_so'         => $se['ach_so'] ?? 0,
                        'insentif_vtkp'  => $se['insentif_vtkp'] ?? 0,
                        'insentif_ec'    => $se['insentif_ec'] ?? 0,
                        'insentif_ipt'   => $se['insentif_ipt'] ?? 0,
                        'ach_sfa'        => $se['ach_sfa'] ?? 0,
                        'total_kotor'    => $se['total_kotor'] ?? 0,
                        'estimasi_insentif' => $se['estimasi_insentif'] ?? 0,
                    ]);
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
            
            'totalKacab'         => $totalKacab,
            'kacabGetIncentive'  => $kacabGetIncentive,
            'totalSpv'           => $totalSpv,
            'spvGetIncentive'    => $spvGetIncentive,
            'totalSe'            => $totalSe,
            'seGetIncentive'     => $seGetIncentive,
            
            'grandTotalInsentif' => $grandTotalInsentif,
            'accessLevel'        => $accessLevel,
        ]);
    }
}
