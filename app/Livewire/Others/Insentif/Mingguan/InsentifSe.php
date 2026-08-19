<?php

namespace App\Livewire\Others\Insentif\Mingguan;

use Livewire\Component;
use App\Models\InsentifMasterSalesman;
use App\Models\InsentifMasterDistributor;
use App\Models\InsentifHeaderGrup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InsentifSe extends Component
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
            // Coba ambil default region dari db jika ada
            $firstRegion = DB::table('insentif_mingguan_master_distributors')
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

        $listBulan = InsentifMasterSalesman::select('bulan')->distinct()->orderBy('bulan', 'desc')->pluck('bulan');
        
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
        $salesmenData = [];

        if ($this->filterBulan && $this->filterRegion) {
            // 1. Ambil Header Grup yang aktif untuk Region ini
            $headers = InsentifHeaderGrup::whereHas('regions', function($q) {
                $q->where('region_name', $this->filterRegion);
            })->with('details')->orderBy('nama_header')->get();

            $salesmenQuery = DB::table('insentif_mingguan_master_salesmans as ims')
                ->join('insentif_mingguan_master_distributors as imd', function($join) {
                    $join->on('ims.bulan', '=', 'imd.bulan')
                         ->on('ims.distributor_code', '=', 'imd.distributor_code');
                })
                ->where('ims.bulan', $this->filterBulan)
                ->where('imd.region_name', $this->filterRegion)
                ->where('ims.jenis_se', 'se');

            if (!empty($this->filterArea)) {
                if (is_array($this->filterArea)) {
                    $salesmenQuery->whereIn('imd.area_name', $this->filterArea);
                } else {
                    $salesmenQuery->where('imd.area_name', $this->filterArea);
                }
            }

            if (trim($this->search)) {
                $term = '%' . trim($this->search) . '%';
                $salesmenQuery->where(function($query) use ($term) {
                    $query->where('ims.sales_name', 'ILIKE', $term)
                          ->orWhere('ims.sales_code', 'ILIKE', $term)
                          ->orWhere('imd.distributor_name', 'ILIKE', $term);
                });
            }

            $salesmen = $salesmenQuery->select(
                    'imd.region_name as region',
                    'imd.area_name as area',
                    'imd.distributor_code as kd_dist',
                    'imd.distributor_name as distributor',
                    'imd.cabang',
                    'ims.sales_code as kode_se',
                    'ims.sales_name as nama_se'
                )
                ->orderBy('imd.distributor_name')
                ->orderBy('ims.sales_name')
                ->get();

            // 3. Pre-load Target SE (VTKPS) untuk Bulan ini
            $targetsRaw = DB::table('target_se_vtkps')
                ->where('bulan', $this->filterBulan)
                ->get();
            $targets = [];
            foreach ($targetsRaw as $t) {
                // Key: dist_code + sales_code + produk_grup
                $key = strtoupper(trim($t->distributor_code) . '_' . trim($t->salesman_code) . '_' . trim($t->produk_grup));
                $targets[$key] = (float)$t->target;
            }

            // 4. Pre-load Actual Qty (CTN) untuk Bulan ini
            $actualsRaw = DB::table('insentif_mingguan_qty_per_ses')
                ->where('bulan', $this->filterBulan)
                ->get();
            $actuals = [];
            foreach ($actualsRaw as $a) {
                // Key: dist_code + sales_code + pg3
                $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->sales_code) . '_' . trim($a->product_group_3));
                $actuals[$key] = (float)$a->qty_ctn;
            }

            // 4a. Pre-load Value Targets
            $valueTargetsRaw = DB::table('target_se_values')
                ->where('bulan', $this->filterBulan)
                ->get();
            $valueTargets = [];
            foreach ($valueTargetsRaw as $t) {
                $key = strtoupper(trim($t->distributor_code) . '_' . trim($t->salesman_code));
                $valueTargets[$key] = (float)$t->target;
            }

            // 4b. Pre-load Value Actuals
            $valueActualsRaw = DB::table('insentif_mingguan_value_per_salesmans')
                ->where('bulan', $this->filterBulan)
                ->get();
            $valueActuals = [];
            foreach ($valueActualsRaw as $a) {
                $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->sales_code));
                $valueActuals[$key] = (float)$a->actual;
            }

            // 4c. Pre-load RO (Route) Data
            $roRaw = DB::table('insentif_se_ro')
                ->where('bulan', $this->filterBulan)
                ->get();
            $roData = [];
            foreach ($roRaw as $r) {
                $key = strtoupper(trim($r->kodecabang) . '_' . trim($r->slsno));
                $roData[$key] = [
                    'frekuensi' => $r->frekuensi,
                    'total_customer' => $r->total_customer
                ];
            }

            // 4d. Pre-load Visits (Call) Data
            $visitsRaw = DB::table('insentif_se_visits')
                ->where('bulan', $this->filterBulan)
                ->get();
            $visitsData = [];
            foreach ($visitsRaw as $v) {
                $key = strtoupper(trim($v->distributor_code) . '_' . trim($v->salesman_code));
                $visitsData[$key] = [
                    'pc' => $v->pc,
                    'ac' => $v->ac,
                    'ec' => $v->ec
                ];
            }

            // 4e. Pre-load IPT Data
            $iptRaw = DB::table('insentif_mingguan_se_ipts')
                ->where('bulan', $this->filterBulan)
                ->get();
            $iptData = [];
            foreach ($iptRaw as $i) {
                $key = strtoupper(trim($i->distributor_code) . '_' . trim($i->sales_code));
                $iptData[$key] = [
                    'sku' => $i->sku,
                    'ec'  => $i->ec
                ];
            }

            // 5. Susun Data (Pivoting)
            foreach ($salesmen as $sm) {
                $row = (array)$sm;
                $row['achievements'] = [];

                // Hitung Value
                $valKey = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se));
                $valTarget = $valueTargets[$valKey] ?? 0;
                $valReal = $valueActuals[$valKey] ?? 0;
                $valAch = 0; // Achievement %
                if ($valTarget > 0) {
                    $valAch = ($valReal / $valTarget) * 100;
                } elseif ($valReal > 0) {
                    $valAch = 100;
                }
                
                $valInsentif = $this->hitungInsentifValue((float)$valTarget, (float)$valAch);

                $row['value_target'] = $valTarget;
                $row['value_real'] = $valReal;
                $row['value_ach'] = round($valAch);
                $row['value_insentif'] = $valInsentif;

                $total_insentif_vtkp = 0;
                foreach ($headers as $h) {
                    $targetVal = 0;
                    $realVal = 0;
                    foreach ($h->details as $d) {
                        $targetKey = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se) . '_' . trim($d->product_group_3));
                        $targetVal += ($targets[$targetKey] ?? 0);

                        $actualKey = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se) . '_' . trim($d->product_group_3));
                        $realVal += ($actuals[$actualKey] ?? 0);
                    }

                    // Hitung % Growth
                    $growth = 0;
                    if ($targetVal > 0) {
                        $growth = (($realVal - $targetVal) / $targetVal) * 100;
                    } else {
                        if ($realVal > 0) {
                            $growth = 100;
                        } else {
                            $growth = 0;
                        }
                    }

                    // Hitung VTKP Insentif (Syarat: Value Ach >= 60%)
                    $insentif = 0;
                    if ($targetVal > 0 && $valAch >= 60) {
                        $insentif = $this->hitungInsentifVtkp(round($growth), round($realVal));
                    }
                    $total_insentif_vtkp += $insentif;

                    $row['achievements'][$h->nama_header] = [
                        'target' => round($targetVal),
                        'real' => round($realVal),
                        'growth' => round($growth),
                        'insentif' => $insentif,
                    ];
                }

                $row['total_insentif_vtkp'] = $total_insentif_vtkp;

                // Hitung Effective Call (EC)
                $ro = $roData[$valKey] ?? ['frekuensi' => '-', 'total_customer' => 0];
                $visit = $visitsData[$valKey] ?? ['ac' => 0, 'ec' => 0];
                
                $row['frekuensi'] = $ro['frekuensi'];
                $row['ro'] = $ro['total_customer'];
                $row['ac'] = $visit['ac'];
                $row['ec'] = $visit['ec'];
                
                $persen_ec = 0;
                if ($row['ac'] > 0) {
                    $persen_ec = round(($row['ec'] / $row['ac']) * 100);
                }
                
                $ec_harian = 0;
                if ($row['ec'] > 0) {
                    $ec_harian = round($row['ec'] / 25);
                }
                
                $row['persen_ec'] = $persen_ec;
                $row['ec_harian'] = $ec_harian;
                
                // Hitung Insentif EC
                $insentif_ec = 0;
                // Syarat: Value (Persen Ach) >= 60%
                if ($row['value_ach'] >= 60) {
                    if ($persen_ec >= 80 && $ec_harian >= 16) {
                        $insentif_ec = 800000;
                    } elseif ($persen_ec >= 70 && $ec_harian >= 14) {
                        $insentif_ec = 500000;
                    } elseif ($persen_ec >= 60 && $ec_harian >= 12) {
                        $insentif_ec = 300000;
                    } elseif ($persen_ec >= 50 && $ec_harian >= 10) {
                        $insentif_ec = 50000;
                    }
                }
                $row['insentif_ec'] = $insentif_ec;

                // --- 4. IPT ---
                $iptRow = $iptData[$valKey] ?? null;
                $row['ipt_sku'] = $iptRow ? $iptRow['sku'] : 0;
                $row['ipt_ec']  = $iptRow ? $iptRow['ec'] : 0;
                $row['ipt']     = 0;
                
                if ($row['ipt_ec'] > 0) {
                    $row['ipt'] = floor($row['ipt_sku'] / $row['ipt_ec']);
                }

                $insentif_ipt = 0;
                // Syarat: RO (dari EC) >= 250
                if ($row['ro'] >= 250) {
                    if ($row['ipt'] >= 12) {
                        $insentif_ipt = 600000;
                    } elseif ($row['ipt'] >= 8) {
                        $insentif_ipt = 500000;
                    } elseif ($row['ipt'] >= 7) {
                        $insentif_ipt = 250000;
                    } elseif ($row['ipt'] >= 5) {
                        $insentif_ipt = 150000;
                    }
                }
                $row['insentif_ipt'] = $insentif_ipt;

                // --- 5. Penggunaan SFA ---
                $sfa_pc = $visit['pc'] ?? 0;
                $sfa_ac = $visit['ac'] ?? 0;
                $sfa_persen = 0;
                
                if ($sfa_pc == 0 && $sfa_ac == 0) {
                    // Pengecualian: Jika PC dan AC keduanya 0, anggap 100% (device error, dsb)
                    $sfa_persen = 100;
                } elseif ($sfa_pc > 0) {
                    $sfa_persen = round(($sfa_ac / $sfa_pc) * 100);
                }

                $row['sfa_pc'] = $sfa_pc;
                $row['sfa_ac'] = $sfa_ac;
                $row['sfa_persen'] = $sfa_persen;

                // --- 6. TOTAL INSENTIF ---
                $sum_insentif = $valInsentif + $row['total_insentif_vtkp'] + $insentif_ec + $insentif_ipt;
                
                if ($sfa_persen < 95) {
                    // Penalty: Jika SFA di bawah 95%, hanya dapat 25% dari total insentif
                    $row['total_insentif'] = 0.25 * $sum_insentif;
                } else {
                    $row['total_insentif'] = $sum_insentif;
                }

                // --- 7. PPH 5% & THP ---
                $row['pph_5'] = $row['total_insentif'] * 0.05;
                $row['thp'] = $row['total_insentif'] - $row['pph_5'];

                $salesmenData[] = $row;
            }
            // 6. Hitung Grand Total per Header & Value
            $grandTotals = [];
            $gtValueTarget = 0;
            $gtValueReal = 0;
            $gtValueInsentif = 0;
            
            foreach ($salesmenData as $row) {
                $gtValueTarget += $row['value_target'];
                $gtValueReal += $row['value_real'];
                $gtValueInsentif += $row['value_insentif'] ?? 0;
            }
            
            $gtValueAch = 0;
            if ($gtValueTarget > 0) {
                $gtValueAch = round(($gtValueReal / $gtValueTarget) * 100);
            } elseif ($gtValueReal > 0) {
                $gtValueAch = 100;
            }
            
            $grandTotalValue = [
                'target' => $gtValueTarget,
                'real' => $gtValueReal,
                'ach' => $gtValueAch,
                'insentif' => $gtValueInsentif,
            ];

            // VTKP
            $grandTotalVtkp = 0;
            foreach ($headers as $h) {
                $totalTarget = 0;
                $totalReal = 0;
                $totalInsentif = 0;
                foreach ($salesmenData as $row) {
                    $ach = $row['achievements'][$h->nama_header] ?? ['target' => 0, 'real' => 0, 'insentif' => 0];
                    $totalTarget += $ach['target'];
                    $totalReal += $ach['real'];
                    $totalInsentif += $ach['insentif'];
                }
                $totalGrowth = 0;
                if ($totalTarget > 0) {
                    $totalGrowth = round((($totalReal - $totalTarget) / $totalTarget) * 100);
                } elseif ($totalReal > 0) {
                    $totalGrowth = 100;
                }
                $grandTotals[$h->nama_header] = [
                    'target' => $totalTarget,
                    'real' => $totalReal,
                    'growth' => $totalGrowth,
                    'insentif' => $totalInsentif,
                ];
                $grandTotalVtkp += $totalInsentif;
            }
            
            // EC Grand Totals
            $gtEc = [
                'ro' => 0,
                'ac' => 0,
                'ec' => 0,
                'persen_ec' => 0,
                'ec_harian' => 0,
                'insentif' => 0,
            ];
            foreach ($salesmenData as $row) {
                $gtEc['ro'] += $row['ro'] ?? 0;
                $gtEc['ac'] += $row['ac'] ?? 0;
                $gtEc['ec'] += $row['ec'] ?? 0;
                $gtEc['insentif'] += $row['insentif_ec'] ?? 0;
            }
            if ($gtEc['ac'] > 0) {
                $gtEc['persen_ec'] = round(($gtEc['ec'] / $gtEc['ac']) * 100);
            }
            if ($gtEc['ec'] > 0) {
                $gtEc['ec_harian'] = round($gtEc['ec'] / 25);
            }
            
            // IPT Grand Totals
            $gtIpt = [
                'sku' => 0,
                'ec' => 0,
                'ipt' => 0,
                'insentif' => 0,
            ];
            foreach ($salesmenData as $row) {
                $gtIpt['sku'] += $row['ipt_sku'] ?? 0;
                $gtIpt['ec'] += $row['ipt_ec'] ?? 0;
                $gtIpt['insentif'] += $row['insentif_ipt'] ?? 0;
            }
            if ($gtIpt['ec'] > 0) {
                $gtIpt['ipt'] = floor($gtIpt['sku'] / $gtIpt['ec']);
            }

            // SFA Grand Totals
            $gtSfa = [
                'pc' => 0,
                'ac' => 0,
                'persen' => 0,
            ];
            $grandTotalKeseluruhan = 0;
            $grandTotalPph = 0;
            $grandTotalThp = 0;

            foreach ($salesmenData as $row) {
                $gtSfa['pc'] += $row['sfa_pc'] ?? 0;
                $gtSfa['ac'] += $row['sfa_ac'] ?? 0;
                $grandTotalKeseluruhan += $row['total_insentif'] ?? 0;
                $grandTotalPph += $row['pph_5'] ?? 0;
                $grandTotalThp += $row['thp'] ?? 0;
            }
            if ($gtSfa['pc'] == 0 && $gtSfa['ac'] == 0) {
                $gtSfa['persen'] = 100;
            } elseif ($gtSfa['pc'] > 0) {
                $gtSfa['persen'] = round(($gtSfa['ac'] / $gtSfa['pc']) * 100);
            }
        }

        return view('livewire.others.insentif.mingguan.insentif-se', [
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'headers' => $headers,
            'salesmenData' => $salesmenData,
            'grandTotals' => $grandTotals ?? [],
            'grandTotalValue' => $grandTotalValue ?? ['target'=>0, 'real'=>0, 'ach'=>0, 'insentif'=>0],
            'grandTotalVtkp' => $grandTotalVtkp ?? 0,
            'grandTotalEc' => $gtEc ?? ['ro'=>0, 'ac'=>0, 'ec'=>0, 'persen_ec'=>0, 'ec_harian'=>0, 'insentif'=>0],
            'grandTotalIpt' => $gtIpt ?? ['sku'=>0, 'ec'=>0, 'ipt'=>0, 'insentif'=>0],
            'grandTotalSfa' => $gtSfa ?? ['pc'=>0, 'ac'=>0, 'persen'=>0],
            'grandTotalKeseluruhan' => $grandTotalKeseluruhan ?? 0,
            'grandTotalPph' => $grandTotalPph ?? 0,
            'grandTotalThp' => $grandTotalThp ?? 0,
            'accessLevel' => $accessLevel,
        ]);
    }

    /**
     * Hitung insentif Value berdasarkan Target Rupiah (AB) dan Persentase Pencapaian (AD)
     */
    private function hitungInsentifValue(float $target, float $ach): int
    {
        if ($target >= 450000000) {
            if ($ach >= 125) return 1200000;
            if ($ach >= 100) return 1000000;
            if ($ach >= 90)  return 300000;
        } elseif ($target >= 350000000) {
            if ($ach >= 125) return 1000000;
            if ($ach >= 100) return 800000;
            if ($ach >= 90)  return 150000;
        } elseif ($target >= 250000000) {
            if ($ach >= 125) return 800000;
            if ($ach >= 100) return 500000;
            if ($ach >= 90)  return 0; // Sebenarnya 0
        }

        return 0;
    }

    /**
     * Hitung insentif VTKP berdasarkan rumus Excel:
     * L (Growth) >= 30%, 20%, 10%
     * K (Real Qty) >= 1500, 1000, 500, 200, 50
     */
    private function hitungInsentifVtkp(int $growth, int $real): int
    {
        if ($growth >= 30) {
            if ($real >= 1500) return 500000;
            if ($real >= 1000) return 350000;
            if ($real >= 500)  return 250000;
            if ($real >= 200)  return 150000;
            if ($real >= 50)   return 50000;
        } elseif ($growth >= 20) {
            if ($real >= 1500) return 350000;
            if ($real >= 1000) return 250000;
            if ($real >= 500)  return 150000;
            if ($real >= 200)  return 100000;
            if ($real >= 50)   return 35000;
        } elseif ($growth >= 10) {
            if ($real >= 1500) return 200000;
            if ($real >= 1000) return 150000;
            if ($real >= 500)  return 100000;
            if ($real >= 200)  return 75000;
            if ($real >= 50)   return 25000;
        }

        return 0;
    }
}
