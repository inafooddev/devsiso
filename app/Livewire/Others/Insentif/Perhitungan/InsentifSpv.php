<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifMasterDistributor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InsentifSpv extends Component
{
    public $filterBulan;
    public $filterRegion;
    public $filterArea;
    public $search = '';

    protected $lockedRegions = null;
    protected $lockedAreas = null;

    public function mount()
    {
        $latest = InsentifMasterDistributor::max('bulan');
        $this->filterBulan = $latest ?: date('Y-m');
        
        $user = Auth::user();
        $level = $user->getAccessLevel();

        if ($level === 'region') {
            $regionCodes = (array) $user->region_code;
            $this->lockedRegions = InsentifMasterDistributor::whereIn('region_code', $regionCodes)
                ->whereNotNull('region_name')
                ->distinct()
                ->pluck('region_name')
                ->toArray();

            if (count($this->lockedRegions) === 1) {
                $this->filterRegion = $this->lockedRegions[0];
            } elseif (count($this->lockedRegions) > 0) {
                $this->filterRegion = $this->lockedRegions[0]; // Set default
            }

        } elseif ($level === 'area') {
            $areaCodes = (array) $user->area_code;
            $rows = InsentifMasterDistributor::whereIn('area_code', $areaCodes)
                ->whereNotNull('area_name')
                ->distinct()
                ->get(['region_name', 'area_name']);

            $this->lockedAreas   = $rows->pluck('area_name')->unique()->values()->toArray();
            $this->lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();

            if (count($this->lockedRegions) === 1) {
                $this->filterRegion = $this->lockedRegions[0];
            }
            if (count($this->lockedAreas) === 1) {
                $this->filterArea = $this->lockedAreas[0];
            }
        } else {
            $firstRegion = DB::table('insentif_master_distributors')
                ->whereNotNull('region_name')
                ->orderBy('region_name')
                ->value('region_name');
                
            $this->filterRegion = $firstRegion ?? '';
        }
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
    }

    public function render()
    {
        $user = Auth::user();
        $accessLevel = $user->getAccessLevel();

        $listBulan = InsentifMasterDistributor::select('bulan')->distinct()->orderBy('bulan', 'desc')->pluck('bulan');
        
        $regionQuery = InsentifMasterDistributor::select('region_name')->whereNotNull('region_name')->distinct()->orderBy('region_name');
        if ($this->lockedRegions !== null) {
            $regionQuery->whereIn('region_name', $this->lockedRegions);
        }
        $listRegions = $regionQuery->pluck('region_name');
        
        $listAreas = collect();
        if ($this->filterRegion) {
            $areaQuery = InsentifMasterDistributor::select('area_name')
                ->where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->distinct()
                ->orderBy('area_name');

            if ($this->lockedAreas !== null) {
                $areaQuery->whereIn('area_name', $this->lockedAreas);
            }
            $listAreas = $areaQuery->pluck('area_name');
        }

        $headers = [];
        $spvData = [];

        if ($this->filterBulan && $this->filterRegion) {
            // Target bulan in target_per_depo is stored as date, e.g. '2026-07-01'
            $targetMonthFilter = $this->filterBulan . '-01';

            // 1. Ambil Header Grup VTKP yang aktif untuk Region ini
            $headers = \App\Models\InsentifHeaderGrup::whereHas('regions', function($q) {
                $q->where('region_name', $this->filterRegion);
            })->with('details')->orderBy('nama_header')->get();

            // 2. Fetch VTKP Targets (SPV)
            $vtkpTargetsRaw = DB::table('target_spv_vtkps')
                ->where('bulan', $this->filterBulan)
                ->get();
            $vtkpTargets = [];
            foreach ($vtkpTargetsRaw as $t) {
                // Key: cabang + produk_grup
                $key = strtoupper(trim($t->cabang) . '_' . trim($t->produk_grup));
                $vtkpTargets[$key] = (float)$t->target;
            }

            // 3. Fetch Actuals VTKP (QTY per SE -> summed up per distributor)
            $qtyActualsRaw = DB::table('insentif_qty_per_ses')
                ->select('distributor_code', 'product_group_3', DB::raw('SUM(qty_ctn) as total_qty'))
                ->where('bulan', $this->filterBulan)
                ->groupBy('distributor_code', 'product_group_3')
                ->get();
            $qtyActuals = [];
            foreach ($qtyActualsRaw as $a) {
                // Key: dist_code + pg3
                $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->product_group_3));
                $qtyActuals[$key] = (float)$a->total_qty;
            }

            // 4. Fetch IPT data per distributor
            $iptDataRaw = DB::table('insentif_se_ipts')
                ->select('distributor_code', DB::raw('SUM(sku) as total_sku'), DB::raw('SUM(ec) as total_ec'))
                ->where('bulan', $this->filterBulan)
                ->groupBy('distributor_code')
                ->get();
            $iptData = [];
            foreach ($iptDataRaw as $ipt) {
                $iptData[strtoupper(trim($ipt->distributor_code))] = [
                    'sku' => (float)$ipt->total_sku,
                    'ec' => (float)$ipt->total_ec
                ];
            }

            // 1. Ambil pondasi MPP (Master SPV)
            $spvQuery = \App\Models\InsentifMasterSpv::where('bulan', $this->filterBulan)
                ->where('region_name', $this->filterRegion);

            if (!empty($this->filterArea)) {
                if (is_array($this->filterArea)) {
                    $spvQuery->whereIn('area_name', $this->filterArea);
                } else {
                    $spvQuery->where('area_name', $this->filterArea);
                }
            }

            // Jika ada pencarian
            if ($this->search) {
                $spvQuery->where(function($q) {
                    $q->where('supervisor_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('supervisor_code', 'ilike', '%' . $this->search . '%')
                      ->orWhere('cabang', 'ilike', '%' . $this->search . '%')
                      ->orWhereExists(function($sub) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('insentif_master_distributors as imd')
                              ->whereColumn('imd.cabang', 'insentif_master_spvs.cabang')
                              ->where('imd.bulan', $this->filterBulan)
                              ->where('imd.distributor_name', 'ilike', '%' . $this->search . '%');
                      });
                });
            }

            $spvMasterData = $spvQuery->orderBy('area_name')->orderBy('cabang')->get();
            $cabangList = $spvMasterData->pluck('cabang')->toArray();

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

            // Fetch RWO data
            $rwoData = DB::table('insentif_spv_rwo')
                ->where('bulan', $this->filterBulan)
                ->get()
                ->keyBy('distributor_code');

            $distributorDataRaw = InsentifMasterDistributor::where('bulan', $this->filterBulan)
                ->whereIn('cabang', $cabangList)
                ->get()
                ->groupBy('cabang');

            $groupedBySpv = [];

            foreach ($spvMasterData as $spvMaster) {
                $spvKey = $spvMaster->supervisor_code ?: 'VACANT_' . $spvMaster->id;
                $spvCode = $spvMaster->supervisor_code ?: 'Vacant';
                $spvName = $spvMaster->supervisor_name ?: 'Vacant';
                $cabang = $spvMaster->cabang;

                if (!isset($groupedBySpv[$spvKey])) {
                    $groupedBySpv[$spvKey] = [
                        'supervisor_code' => $spvCode,
                        'supervisor_name' => $spvName,
                        'total_target_reguler' => 0,
                        'total_aktual_so' => 0,
                        'total_rwo_peserta' => 0,
                        'total_rwo_achieve' => 0,
                        'total_ipt_sku' => 0,
                        'total_ipt_ec' => 0,
                        'rowspan' => 0,
                        'cabangs' => []
                    ];
                }

                if (!isset($groupedBySpv[$spvKey]['cabangs'][$cabang])) {
                    $groupedBySpv[$spvKey]['cabangs'][$cabang] = [
                        'cabang' => $cabang,
                        'area_name' => $spvMaster->area_name,
                        'distributors' => [],
                        'rowspan' => 0,
                        'vtkp_achievements' => [],
                        'total_insentif_vtkp' => 0
                    ];

                    // Tambahkan target reguler hanya SEKALI per cabang
                    $target = isset($targets[$cabang]) ? (float)$targets[$cabang]->target : 0;
                    $groupedBySpv[$spvKey]['total_target_reguler'] += $target;
                }

                $target = isset($targets[$cabang]) ? (float)$targets[$cabang]->target : 0;
                $dists = $distributorDataRaw->get($cabang, []);

                if (count($dists) == 0) {
                    // Create dummy distributor row
                    $groupedBySpv[$spvKey]['cabangs'][$cabang]['distributors'][] = [
                        'area_name' => $spvMaster->area_name,
                        'distributor_code' => '-',
                        'distributor_name' => 'VACANT',
                        'cabang' => $cabang,
                        'target_so' => $target,
                        'aktual_so' => 0,
                        'rwo_peserta' => 0,
                        'rwo_achieve' => 0,
                        'ipt_sku' => 0,
                        'ipt_ec' => 0,
                    ];
                    $groupedBySpv[$spvKey]['cabangs'][$cabang]['rowspan'] += 1;
                    $groupedBySpv[$spvKey]['rowspan'] += 1;
                } else {
                    foreach ($dists as $idx => $md) {
                        $distCode = $md->distributor_code;
                        $actual = isset($actuals[$distCode]) ? (float)$actuals[$distCode]->total_actual : 0;
                        
                        $rwoPeserta = isset($rwoData[$distCode]) ? (int)$rwoData[$distCode]->total_potensi : 0;
                        $rwoAchieve = isset($rwoData[$distCode]) ? (int)$rwoData[$distCode]->capai_target : 0;
                        
                        $iptSku = isset($iptData[strtoupper(trim($distCode))]) ? (float)$iptData[strtoupper(trim($distCode))]['sku'] : 0;
                        $iptEc = isset($iptData[strtoupper(trim($distCode))]) ? (float)$iptData[strtoupper(trim($distCode))]['ec'] : 0;

                        $distributorData = [
                            'area_name' => $md->area_name,
                            'distributor_code' => $distCode,
                            'distributor_name' => $md->distributor_name,
                            'cabang' => $cabang,
                            'target_so' => $target,
                            'aktual_so' => $actual,
                            'rwo_peserta' => $rwoPeserta,
                            'rwo_achieve' => $rwoAchieve,
                            'ipt_sku' => $iptSku,
                            'ipt_ec' => $iptEc,
                        ];

                        $groupedBySpv[$spvKey]['cabangs'][$cabang]['distributors'][] = $distributorData;
                        $groupedBySpv[$spvKey]['cabangs'][$cabang]['rowspan'] += 1;
                        $groupedBySpv[$spvKey]['rowspan'] += 1;

                        $groupedBySpv[$spvKey]['total_aktual_so'] += $actual;
                        $groupedBySpv[$spvKey]['total_rwo_peserta'] += $rwoPeserta;
                        $groupedBySpv[$spvKey]['total_rwo_achieve'] += $rwoAchieve;
                        $groupedBySpv[$spvKey]['total_ipt_sku'] += $iptSku;
                        $groupedBySpv[$spvKey]['total_ipt_ec'] += $iptEc;
                    }
                }
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

                // VTKP Calculation per Cabang
                $totalInsentifVtkpSPV = 0;
                
                foreach ($spv['cabangs'] as $cabang => $cabData) {
                    $totalInsentifVtkpCabang = 0;
                    
                    foreach ($headers as $h) {
                        $branchTarget = 0;
                        $branchReal = 0;
                        
                        foreach ($h->details as $d) {
                            $targetKey = strtoupper(trim($cabang) . '_' . trim($d->product_group_3));
                            $branchTarget += ($vtkpTargets[$targetKey] ?? 0);
                        }
                        
                        foreach ($cabData['distributors'] as $dist) {
                            foreach ($h->details as $d) {
                                $actualKey = strtoupper(trim($dist['distributor_code']) . '_' . trim($d->product_group_3));
                                $branchReal += ($qtyActuals[$actualKey] ?? 0);
                            }
                        }
                        
                        $branchGrowth = 0;
                        if ($branchTarget > 0) {
                            $branchGrowth = (($branchReal - $branchTarget) / $branchTarget) * 100;
                        } elseif ($branchReal > 0) {
                            $branchGrowth = 100;
                        }
                        
                        $branchInsentif = 0;
                        if ($branchTarget > 0) {
                            if ($branchGrowth >= 30) {
                                $branchInsentif = ($branchReal - $branchTarget) * 600;
                            } elseif ($branchGrowth >= 20) {
                                $branchInsentif = ($branchReal - $branchTarget) * 400;
                            } elseif ($branchGrowth >= 10) {
                                $branchInsentif = ($branchReal - $branchTarget) * 250;
                            }
                        }
                        
                        // Syarat Insentif VTKP SPV: Pencapaian Value (Reguler) >= 80% (menggunakan pencapaian SPV)
                        if ($pencapaian < 80) {
                            $branchInsentif = 0;
                        }
                        
                        $totalInsentifVtkpCabang += $branchInsentif;
                        
                        $groupedBySpv[$spvCode]['cabangs'][$cabang]['vtkp_achievements'][$h->nama_header] = [
                            'target' => $branchTarget,
                            'real' => $branchReal,
                            'growth' => $branchGrowth,
                            'insentif' => $branchInsentif
                        ];
                    }
                    
                    $groupedBySpv[$spvCode]['cabangs'][$cabang]['total_insentif_vtkp'] = $totalInsentifVtkpCabang;
                    $totalInsentifVtkpSPV += $totalInsentifVtkpCabang;
                }
                
                $groupedBySpv[$spvCode]['total_insentif_vtkp'] = $totalInsentifVtkpSPV;

                // RWO Calculation
                $groupedBySpv[$spvCode]['rwo_achieve_pct'] = $groupedBySpv[$spvCode]['total_rwo_peserta'] > 0 
                    ? ($groupedBySpv[$spvCode]['total_rwo_achieve'] / $groupedBySpv[$spvCode]['total_rwo_peserta']) * 100 
                    : 0;
                
                $rwoInsentif = 0;
                $rwoPct = $groupedBySpv[$spvCode]['rwo_achieve_pct'];
                
                if ($rwoPct >= 90) {
                    $rwoInsentif = 900000;
                } elseif ($rwoPct >= 80) {
                    $rwoInsentif = 700000;
                } elseif ($rwoPct >= 70) {
                    $rwoInsentif = 500000;
                }
                
                $groupedBySpv[$spvCode]['insentif_rwo'] = $rwoInsentif;

                // IPT Calculation
                $totalSku = $groupedBySpv[$spvCode]['total_ipt_sku'];
                $totalEc = $groupedBySpv[$spvCode]['total_ipt_ec'];
                $ipt = $totalEc > 0 ? floor($totalSku / $totalEc) : 0;
                $groupedBySpv[$spvCode]['ipt'] = $ipt;

                $iptInsentif = 0;
                if ($ipt >= 12) {
                    $iptInsentif = 600000;
                } elseif ($ipt >= 8) {
                    $iptInsentif = 500000;
                } elseif ($ipt >= 7) {
                    $iptInsentif = 250000;
                } elseif ($ipt >= 5) {
                    $iptInsentif = 150000;
                }
                $groupedBySpv[$spvCode]['insentif_ipt'] = $iptInsentif;

                // Grand Total Insentif (All components)
                $totalAllInsentif = $groupedBySpv[$spvCode]['ins_so'] 
                                    + $groupedBySpv[$spvCode]['total_insentif_vtkp'] 
                                    + $groupedBySpv[$spvCode]['insentif_rwo'] 
                                    + $groupedBySpv[$spvCode]['insentif_ipt'];
                
                $groupedBySpv[$spvCode]['total_all_insentif'] = $totalAllInsentif;
                $groupedBySpv[$spvCode]['tabungan_30'] = $totalAllInsentif * 0.3;
                $groupedBySpv[$spvCode]['transfer_70'] = $totalAllInsentif * 0.7;
            }

            // Sort SPVs by Area then Supervisor Name
            usort($groupedBySpv, function($a, $b) {
                $cabangsA = reset($a['cabangs']);
                $areaA = $cabangsA['distributors'][0]['area_name'] ?? '';
                
                $cabangsB = reset($b['cabangs']);
                $areaB = $cabangsB['distributors'][0]['area_name'] ?? '';
                
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
            'pencapaian_persen' => 0,
            'total_insentif_vtkp' => 0,
            'rwo_peserta' => 0,
            'rwo_achieve' => 0,
            'rwo_achieve_pct' => 0,
            'insentif_rwo' => 0,
            'ipt_sku' => 0,
            'ipt_ec' => 0,
            'ipt' => 0,
            'insentif_ipt' => 0,
            'total_all_insentif' => 0,
            'tabungan_30' => 0,
            'transfer_70' => 0,
            'vtkp' => []
        ];

        foreach ($headers as $h) {
            $grandTotal['vtkp'][$h->nama_header] = ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
        }

        foreach ($spvData as $spv) {
            $grandTotal['target_so'] += $spv['total_target_reguler'];
            $grandTotal['aktual_so'] += $spv['total_aktual_so'];
            $grandTotal['ins_so'] += $spv['ins_so'];
            $grandTotal['total_insentif_vtkp'] += $spv['total_insentif_vtkp'];
            
            $grandTotal['rwo_peserta'] += $spv['total_rwo_peserta'];
            $grandTotal['rwo_achieve'] += $spv['total_rwo_achieve'];
            $grandTotal['insentif_rwo'] += $spv['insentif_rwo'];

            $grandTotal['ipt_sku'] += $spv['total_ipt_sku'];
            $grandTotal['ipt_ec'] += $spv['total_ipt_ec'];
            $grandTotal['insentif_ipt'] += $spv['insentif_ipt'];
            
            $grandTotal['total_all_insentif'] += $spv['total_all_insentif'];
            $grandTotal['tabungan_30'] += $spv['tabungan_30'];
            $grandTotal['transfer_70'] += $spv['transfer_70'];

            foreach ($spv['cabangs'] as $cabData) {
                foreach ($headers as $h) {
                    if (isset($cabData['vtkp_achievements'][$h->nama_header])) {
                        $ach = $cabData['vtkp_achievements'][$h->nama_header];
                        $grandTotal['vtkp'][$h->nama_header]['target'] += $ach['target'];
                        $grandTotal['vtkp'][$h->nama_header]['real'] += $ach['real'];
                        $grandTotal['vtkp'][$h->nama_header]['insentif'] += $ach['insentif'];
                    }
                }
            }
        }
        
        foreach ($headers as $h) {
            if ($grandTotal['vtkp'][$h->nama_header]['target'] > 0) {
                $grandTotal['vtkp'][$h->nama_header]['growth'] = ($grandTotal['vtkp'][$h->nama_header]['real'] / $grandTotal['vtkp'][$h->nama_header]['target']) * 100;
            }
        }

        if ($grandTotal['target_so'] > 0) {
            $grandTotal['pencapaian_persen'] = ($grandTotal['aktual_so'] / $grandTotal['target_so']) * 100;
        }
        
        if ($grandTotal['rwo_peserta'] > 0) {
            $grandTotal['rwo_achieve_pct'] = ($grandTotal['rwo_achieve'] / $grandTotal['rwo_peserta']) * 100;
        }

        if ($grandTotal['ipt_ec'] > 0) {
            $grandTotal['ipt'] = floor($grandTotal['ipt_sku'] / $grandTotal['ipt_ec']);
        }

        foreach ($headers as $h) {
            $tgt = $grandTotal['vtkp'][$h->nama_header]['target'];
            $real = $grandTotal['vtkp'][$h->nama_header]['real'];
            if ($tgt > 0) {
                $grandTotal['vtkp'][$h->nama_header]['growth'] = (($real - $tgt) / $tgt) * 100;
            } elseif ($real > 0) {
                $grandTotal['vtkp'][$h->nama_header]['growth'] = 100;
            }
        }

        return view('livewire.others.insentif.perhitungan.insentif-spv', [
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'headers' => $headers,
            'spvData' => $spvData,
            'grandTotal' => $grandTotal,
            'accessLevel' => $accessLevel
        ]);
    }
}
