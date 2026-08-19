<?php

namespace App\Services;

use App\Models\InsentifMasterDistributor;
use App\Models\InsentifMasterSalesman;
use App\Models\InsentifMasterSpv;
use App\Models\InsentifHeaderGrup;
use App\Models\TargetKacab;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MingguanInsentifCalculatorService
{
    /**
     * Get unique regions for a month
     */
    public function getRegions()
    {
        return InsentifMasterDistributor::select('region_name')
            ->whereNotNull('region_name')
            ->distinct()
            ->orderBy('region_name')
            ->pluck('region_name');
    }

    /**
     * Get unique areas for a region
     */
    public function getAreas($region)
    {
        return InsentifMasterDistributor::select('area_name')
            ->where('region_name', $region)
            ->whereNotNull('area_name')
            ->distinct()
            ->orderBy('area_name')
            ->pluck('area_name');
    }

    /**
     * Get Kacab Insentif Data
     * Returns array of Kacab calculations
     */
    public function calculateKacab($bulan, $region = null, $area = null, $search = null)
    {
        $yearFilter = Carbon::parse($bulan . '-01')->format('Y');

        $query = InsentifMasterDistributor::where('bulan', $bulan);

        if ($region) {
            $query->where('region_name', $region);
        }

        if (!empty($area)) {
            if (is_array($area)) {
                $query->whereIn('area_name', $area);
            } else {
                $query->where('area_name', $area);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('cabang', 'ilike', '%' . $search . '%')
                  ->orWhere('distributor_name', 'ilike', '%' . $search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $search . '%');
            });
        }

        $masterData = $query->orderBy('area_name')->orderBy('cabang')->get();

        $targets = TargetKacab::where('tahun', $yearFilter)->get()->keyBy(function($item) {
            return strtoupper(trim($item->cabang));
        });

        $actuals = DB::table('insentif_mingguan_value_per_salesmans')
            ->select('distributor_code', DB::raw('SUM(actual) as total_actual'))
            ->where('bulan', $bulan)
            ->groupBy('distributor_code')
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->distributor_code));
            });

        $cabangToDistCode = [];
        foreach ($masterData as $md) {
            $c = strtoupper(trim($md->cabang));
            $dc = strtoupper(trim($md->distributor_code));
            $cabangToDistCode[$c] = $dc;
        }

        $mappings = \App\Models\InsentifKacabMapping::all();
        $childToParentMap = $mappings->pluck('parent_cabang', 'child_cabang')->toArray();
        $parentToChildrenMap = [];
        foreach ($mappings as $m) {
            $parentToChildrenMap[$m->parent_cabang][] = $m->child_cabang;
        }

        foreach ($childToParentMap as $child => $parent) {
            $childDistCode = $cabangToDistCode[$child] ?? null;
            $parentDistCode = $cabangToDistCode[$parent] ?? null;

            if ($childDistCode && $actuals->has($childDistCode)) {
                $childActual = $actuals->get($childDistCode)->total_actual;
                if ($parentDistCode) {
                    if ($actuals->has($parentDistCode)) {
                        $actuals->get($parentDistCode)->total_actual += $childActual;
                    } else {
                        $actuals->put($parentDistCode, (object)['distributor_code' => $parentDistCode, 'total_actual' => $childActual]);
                    }
                }
            }
        }

        $kacabData = [];

        foreach ($masterData as $md) {
            $cabang = strtoupper(trim($md->cabang));
            $distCode = strtoupper(trim($md->distributor_code));
            
            if (array_key_exists($cabang, $childToParentMap)) {
                continue;
            }

            $targetData = $targets->get($cabang);
            $target = $targetData ? (float) $targetData->target : 0;
            $insentif = $targetData ? (float) $targetData->insentif : 0;
            $namaKacab = $targetData ? $targetData->nama_kacab : '-';

            $actualData = $actuals->get($distCode);
            $sellOut = $actualData ? (float) $actualData->total_actual : 0;

            $displayCabang = $cabang;
            if (isset($parentToChildrenMap[$cabang])) {
                $displayCabang .= ', ' . implode(', ', $parentToChildrenMap[$cabang]);
            }

            $percentage = $target > 0 ? ($sellOut / $target) * 100 : 0;
            
            $nilaiInsentif = $percentage >= 100 ? $insentif : 0;
            $pph = $nilaiInsentif * 0.05;
            $trf = $nilaiInsentif - $pph;

            $kacabData[] = [
                'area_name' => $md->area_name,
                'distributor_name' => $md->distributor_name,
                'cabang' => $displayCabang,
                'nama_kacab' => $namaKacab,
                'target' => $target,
                'insentif' => $insentif,
                'sell_out' => $sellOut,
                'percentage' => $percentage,
                'nilai_insentif' => $nilaiInsentif,
                'pph' => $pph,
                'trf' => $trf,
            ];
        }

        return $kacabData;
    }

    /**
     * Get SPV Insentif Data
     */
    public function calculateSpv($bulan, $region = null, $area = null, $search = null)
    {
        $headers = [];
        $spvData = [];
        
        $queryCabang = InsentifMasterDistributor::where('bulan', $bulan);
        if ($region) $queryCabang->where('region_name', $region);
        if ($area) {
            if (is_array($area)) $queryCabang->whereIn('area_name', $area);
            else $queryCabang->where('area_name', $area);
        }
        $distributorDataRaw = $queryCabang->get()->groupBy('cabang');
        $cabangList = $distributorDataRaw->keys()->toArray();

        if (empty($cabangList)) {
            return ['headers' => [], 'spvData' => []];
        }

        $headers = InsentifHeaderGrup::whereHas('regions', function($q) use ($region) {
            if ($region) {
                $q->where('region_name', $region);
            }
        })->with('details')->orderBy('nama_header')->get();

        $querySpv = InsentifMasterSpv::where('bulan', $bulan)
            ->whereIn('cabang', $cabangList);
        
        if ($search) {
            $querySpv->where(function($q) use ($search) {
                $q->where('supervisor_code', 'ilike', '%' . $search . '%')
                  ->orWhere('supervisor_name', 'ilike', '%' . $search . '%')
                  ->orWhere('cabang', 'ilike', '%' . $search . '%');
            });
        }
        $spvMasterData = $querySpv->get();

        // Target diambil dari target_per_depo dengan format bulan YYYY-MM-01
        $targetMonthFilter = $bulan . '-01';
        $targets = DB::table('target_per_depo')
            ->where('bulan', $targetMonthFilter)
            ->whereIn('cabang', $cabangList)
            ->get()
            ->keyBy('cabang');

        $actuals = DB::table('insentif_mingguan_value_per_salesmans')
            ->select('distributor_code', DB::raw('SUM(actual) as total_actual'))
            ->where('bulan', $bulan)
            ->groupBy('distributor_code')
            ->get()
            ->keyBy('distributor_code');

        // VTKP Targets SPV dari tabel target_spv_vtkps, key: cabang_produkGrup
        $vtkpTargetsRaw = DB::table('target_spv_vtkps')
            ->where('bulan', $bulan)
            ->get();
        $vtkpTargets = [];
        foreach ($vtkpTargetsRaw as $t) {
            $key = strtoupper(trim($t->cabang) . '_' . trim($t->produk_grup));
            $vtkpTargets[$key] = (float)$t->target;
        }

        // VTKP Actuals dari qty_ctn (bukan actual)
        $qtyActuals = DB::table('insentif_mingguan_qty_per_ses')
            ->select('distributor_code', 'product_group_3', DB::raw('SUM(qty_ctn) as total_qty'))
            ->where('bulan', $bulan)
            ->groupBy('distributor_code', 'product_group_3')
            ->get()
            ->mapWithKeys(function($item) {
                return [strtoupper(trim($item->distributor_code) . '_' . trim($item->product_group_3)) => (float)$item->total_qty];
            })->toArray();

        $rwoData = DB::table('insentif_spv_rwo')
            ->where('bulan', $bulan)
            ->get()
            ->keyBy('distributor_code');

        $iptDataRaw = DB::table('insentif_mingguan_se_ipts')
            ->where('bulan', $bulan)
            ->get();
            
        $iptData = [];
        foreach ($iptDataRaw as $iptItem) {
            $dc = strtoupper(trim($iptItem->distributor_code));
            if (!isset($iptData[$dc])) {
                $iptData[$dc] = ['sku' => 0, 'ec' => 0];
            }
            $iptData[$dc]['sku'] += (float)$iptItem->sku;
            $iptData[$dc]['ec'] += (float)$iptItem->ec;
        }

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
            }

            $target = isset($targets[$cabang]) ? (float)$targets[$cabang]->target : 0;
            $groupedBySpv[$spvKey]['total_target_reguler'] += $target;

            $dists = $distributorDataRaw->get($cabang, []);

            if (count($dists) == 0) {
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

        foreach ($groupedBySpv as $spvCode => $spv) {
            $pencapaian = 0;
            $targetTotal = $spv['total_target_reguler'];
            
            if ($targetTotal > 0) {
                $pencapaian = ($spv['total_aktual_so'] / $targetTotal) * 100;
            }
            
            $groupedBySpv[$spvCode]['pencapaian_persen'] = $pencapaian;
            
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

            $totalAllInsentif = $groupedBySpv[$spvCode]['ins_so'] 
                                + $groupedBySpv[$spvCode]['total_insentif_vtkp'] 
                                + $groupedBySpv[$spvCode]['insentif_rwo'] 
                                + $groupedBySpv[$spvCode]['insentif_ipt'];
            
            $groupedBySpv[$spvCode]['total_all_insentif'] = $totalAllInsentif;
            $groupedBySpv[$spvCode]['tabungan_30'] = $totalAllInsentif * 0.3;
            $groupedBySpv[$spvCode]['transfer_70'] = $totalAllInsentif * 0.7;
        }

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

        return ['headers' => $headers, 'spvData' => $groupedBySpv];
    }

    /**
     * Get SE Insentif Data
     */
    public function calculateSe($bulan, $region = null, $area = null, $search = null)
    {
        $headers = [];
        $salesmenData = [];

        $headersQuery = InsentifHeaderGrup::query();
        if ($region) {
            $headersQuery->whereHas('regions', function($q) use ($region) {
                $q->where('region_name', $region);
            });
        }
        $headers = $headersQuery->with('details')->orderBy('nama_header')->get();

        $salesmenQuery = DB::table('insentif_mingguan_master_salesmans as ims')
            ->join('insentif_mingguan_master_distributors as imd', function($join) {
                $join->on('ims.bulan', '=', 'imd.bulan')
                     ->on('ims.distributor_code', '=', 'imd.distributor_code');
            })
            ->where('ims.bulan', $bulan)
            ->where('ims.jenis_se', 'se');

        if ($region) {
            $salesmenQuery->where('imd.region_name', $region);
        }

        if (!empty($area)) {
            if (is_array($area)) {
                $salesmenQuery->whereIn('imd.area_name', $area);
            } else {
                $salesmenQuery->where('imd.area_name', $area);
            }
        }

        if ($search) {
            $term = '%' . trim($search) . '%';
            $salesmenQuery->where(function($query) use ($term) {
                $query->where('ims.sales_name', 'ilike', $term)
                      ->orWhere('ims.sales_code', 'ilike', $term)
                      ->orWhere('imd.distributor_name', 'ilike', $term)
                      ->orWhere('imd.cabang', 'ilike', $term);
            });
        }

        $salesmen = $salesmenQuery->select(
                'imd.region_name as region',
                'imd.area_name as area_name',
                'imd.distributor_code as kd_dist',
                'imd.distributor_name as distributor',
                'imd.cabang',
                'ims.sales_code as kode_se',
                'ims.sales_name as nama_se'
            )
            ->orderBy('imd.area_name')
            ->orderBy('imd.cabang')
            ->orderBy('ims.sales_name')
            ->get();

        // 3. Pre-load Target SE (VTKPS)
        $targetsRaw = DB::table('target_se_vtkps')->where('bulan', $bulan)->get();
        $targets = [];
        foreach ($targetsRaw as $t) {
            $key = strtoupper(trim($t->distributor_code) . '_' . trim($t->salesman_code) . '_' . trim($t->produk_grup));
            $targets[$key] = (float)$t->target;
        }

        // 4. Pre-load Actual Qty (CTN)
        $actualsRaw = DB::table('insentif_mingguan_qty_per_ses')->where('bulan', $bulan)->get();
        $actuals = [];
        foreach ($actualsRaw as $a) {
            $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->sales_code) . '_' . trim($a->product_group_3));
            $actuals[$key] = (float)$a->qty_ctn;
        }

        // 4a. Pre-load Value Targets
        $valueTargetsRaw = DB::table('target_se_values')->where('bulan', $bulan)->get();
        $valueTargets = [];
        foreach ($valueTargetsRaw as $t) {
            $key = strtoupper(trim($t->distributor_code) . '_' . trim($t->salesman_code));
            $valueTargets[$key] = (float)$t->target;
        }

        // 4b. Pre-load Value Actuals
        $valueActualsRaw = DB::table('insentif_mingguan_value_per_salesmans')->where('bulan', $bulan)->get();
        $valueActuals = [];
        foreach ($valueActualsRaw as $a) {
            $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->sales_code));
            $valueActuals[$key] = (float)$a->actual;
        }

        // 4c. Pre-load RO
        $roRaw = DB::table('insentif_se_ro')->where('bulan', $bulan)->get();
        $roData = [];
        foreach ($roRaw as $r) {
            $key = strtoupper(trim($r->kodecabang) . '_' . trim($r->slsno));
            $roData[$key] = $r->total_customer;
        }

        // 4d. Pre-load Visits
        $visitsRaw = DB::table('insentif_se_visits')->where('bulan', $bulan)->get();
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
        $iptRaw = DB::table('insentif_mingguan_se_ipts')->where('bulan', $bulan)->get();
        $iptData = [];
        foreach ($iptRaw as $i) {
            $key = strtoupper(trim($i->distributor_code) . '_' . trim($i->sales_code));
            $iptData[$key] = [
                'sku' => $i->sku,
                'ec'  => $i->ec
            ];
        }

        foreach ($salesmen as $sm) {
            $valKey = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se));

            // --- Value (SO) ---
            $valTarget = $valueTargets[$valKey] ?? 0;
            $valReal   = $valueActuals[$valKey] ?? 0;
            $valAch    = 0;
            if ($valTarget > 0) {
                $valAch = ($valReal / $valTarget) * 100;
            } elseif ($valReal > 0) {
                $valAch = 100;
            }

            // hitungInsentifValue — sesuai tabel asli InsentifSe.php
            $valInsentif = 0;
            if ($valTarget >= 450000000) {
                if ($valAch >= 125)     $valInsentif = 1200000;
                elseif ($valAch >= 100) $valInsentif = 1000000;
                elseif ($valAch >= 90)  $valInsentif = 300000;
            } elseif ($valTarget >= 350000000) {
                if ($valAch >= 125)     $valInsentif = 1000000;
                elseif ($valAch >= 100) $valInsentif = 800000;
                elseif ($valAch >= 90)  $valInsentif = 150000;
            } elseif ($valTarget >= 250000000) {
                if ($valAch >= 125)     $valInsentif = 800000;
                elseif ($valAch >= 100) $valInsentif = 500000;
                // ach >= 90 = 0
            }

            // --- VTKP ---
            $totalInsentifVtkp = 0;
            foreach ($headers as $h) {
                $targetVal = 0;
                $realVal   = 0;
                foreach ($h->details as $d) {
                    $k = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se) . '_' . trim($d->product_group_3));
                    $targetVal += ($targets[$k] ?? 0);
                    $realVal   += ($actuals[$k] ?? 0);
                }

                $growth = 0;
                if ($targetVal > 0) {
                    $growth = (($realVal - $targetVal) / $targetVal) * 100;
                } elseif ($realVal > 0) {
                    $growth = 100;
                }

                // hitungInsentifVtkp — lookup berdasarkan real qty, bukan (real-target)*600
                $insVtkp = 0;
                if ($targetVal > 0 && $valAch >= 60) {
                    $g = round($growth);
                    $r = round($realVal);
                    if ($g >= 30) {
                        if ($r >= 1500)      $insVtkp = 500000;
                        elseif ($r >= 1000)  $insVtkp = 350000;
                        elseif ($r >= 500)   $insVtkp = 250000;
                        elseif ($r >= 200)   $insVtkp = 150000;
                        elseif ($r >= 50)    $insVtkp = 50000;
                    } elseif ($g >= 20) {
                        if ($r >= 1500)      $insVtkp = 350000;
                        elseif ($r >= 1000)  $insVtkp = 250000;
                        elseif ($r >= 500)   $insVtkp = 150000;
                        elseif ($r >= 200)   $insVtkp = 100000;
                        elseif ($r >= 50)    $insVtkp = 35000;
                    } elseif ($g >= 10) {
                        if ($r >= 1500)      $insVtkp = 200000;
                        elseif ($r >= 1000)  $insVtkp = 150000;
                        elseif ($r >= 500)   $insVtkp = 100000;
                        elseif ($r >= 200)   $insVtkp = 75000;
                        elseif ($r >= 50)    $insVtkp = 25000;
                    }
                }
                $totalInsentifVtkp += $insVtkp;
            }

            // --- EC ---
            $roRow  = $roData[$valKey]     ?? ['frekuensi' => '-', 'total_customer' => 0];
            $visit  = $visitsData[$valKey] ?? ['pc' => 0, 'ac' => 0, 'ec' => 0];
            $ro     = $roRow['total_customer'] ?? 0;
            $ac     = $visit['ac'];
            $ec     = $visit['ec'];

            $persen_ec  = ($ac > 0) ? round(($ec / $ac) * 100) : 0;
            $ec_harian  = ($ec > 0) ? round($ec / 25) : 0;

            $insentif_ec = 0;
            if ($valAch >= 60) {
                if ($persen_ec >= 80 && $ec_harian >= 16)      $insentif_ec = 800000;
                elseif ($persen_ec >= 70 && $ec_harian >= 14)  $insentif_ec = 500000;
                elseif ($persen_ec >= 60 && $ec_harian >= 12)  $insentif_ec = 300000;
                elseif ($persen_ec >= 50 && $ec_harian >= 10)  $insentif_ec = 50000;
            }

            // --- IPT ---
            $iptRow   = $iptData[$valKey] ?? ['sku' => 0, 'ec' => 0];
            $iptVal   = ($iptRow['ec'] > 0) ? floor($iptRow['sku'] / $iptRow['ec']) : 0;

            $insentif_ipt = 0;
            if ($ro >= 250) {
                if ($iptVal >= 12)     $insentif_ipt = 600000;
                elseif ($iptVal >= 8)  $insentif_ipt = 500000;
                elseif ($iptVal >= 7)  $insentif_ipt = 250000;
                elseif ($iptVal >= 5)  $insentif_ipt = 150000;
            }

            // --- SFA Penalty ---
            $sfa_pc = $visit['pc'] ?? 0;
            $sfa_ac = $visit['ac'] ?? 0;
            if ($sfa_pc == 0 && $sfa_ac == 0) {
                $sfa_persen = 100; // device error → dianggap 100%
            } elseif ($sfa_pc > 0) {
                $sfa_persen = round(($sfa_ac / $sfa_pc) * 100);
            } else {
                $sfa_persen = 0;
            }

            // --- Grand Total ---
            $sum_insentif = $valInsentif + $totalInsentifVtkp + $insentif_ec + $insentif_ipt;

            // Jika SFA < 95% → hanya dapat 25% dari total insentif
            $total_insentif = ($sfa_persen < 95) ? 0.25 * $sum_insentif : $sum_insentif;

            $pph = $total_insentif * 0.05;
            $thp = $total_insentif - $pph;

            $salesmenData[] = [
                'area_name'     => $sm->area_name,
                'cabang'        => $sm->cabang,
                'salesman_code' => $sm->kode_se,
                'salesman_name' => $sm->nama_se,
                'thp'           => $thp
            ];
        }

        return ['headers' => $headers, 'salesmenData' => $salesmenData];
    }
}
