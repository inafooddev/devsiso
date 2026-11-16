<?php

namespace App\Http\Controllers\Mobile\Insentif;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\InsentifMingguanMasterDistributor;
use App\Models\InsentifHeaderGrup;
use App\Services\MingguanInsentifCalculatorService;
use Carbon\Carbon;

class IndexController extends Controller
{
    protected MingguanInsentifCalculatorService $calculatorService;

    public function __construct(MingguanInsentifCalculatorService $calculatorService)
    {
        $this->calculatorService = $calculatorService;
    }

    /**
     * Display Mobile Weekly Incentive Monitoring Dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('mobile.login');
        }

        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';

        // 1. Daftar Bulan dari data mingguan
        $listBulan = DB::table('insentif_mingguan_master_distributors')
            ->select('bulan')
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan');

        if ($listBulan->isEmpty()) {
            $listBulan = collect([date('Y-m')]);
        }

        $bulan = $request->input('bulan', $listBulan->first());

        // 2. Hak Akses Region & Area
        $lockedRegions = null;
        $lockedAreas = null;
        $sisoCodes = null;

        if ($accessLevel === 'region') {
            $regionCodes = (array) $user->region_code;
            $lockedRegions = InsentifMingguanMasterDistributor::whereIn('region_code', $regionCodes)
                ->whereNotNull('region_name')
                ->distinct()
                ->pluck('region_name')
                ->toArray();
        } elseif ($accessLevel === 'area') {
            $areaCodes = (array) $user->area_code;
            $rows = InsentifMingguanMasterDistributor::whereIn('area_code', $areaCodes)
                ->whereNotNull('area_name')
                ->distinct()
                ->get(['region_name', 'area_name']);

            $lockedAreas = $rows->pluck('area_name')->unique()->values()->toArray();
            $lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();
        } elseif ($accessLevel === 'supervisor') {
            $sisoCodes = !empty($user->supervisor_code) ? [$user->supervisor_code] : null;
            if ($sisoCodes) {
                $rows = InsentifMingguanMasterDistributor::whereIn('supervisor_code', $sisoCodes)
                    ->whereNotNull('region_name')
                    ->distinct()
                    ->get(['region_name', 'area_name']);

                $lockedAreas = $rows->pluck('area_name')->unique()->values()->toArray();
                $lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();
            }
        }

        // Daftar Region untuk filter dropdown
        $regionQuery = InsentifMingguanMasterDistributor::select('region_name')
            ->whereNotNull('region_name')
            ->distinct()
            ->orderBy('region_name');

        if ($lockedRegions !== null) {
            $regionQuery->whereIn('region_name', $lockedRegions);
        }
        if ($accessLevel === 'supervisor' && $sisoCodes) {
            $regionQuery->whereIn('supervisor_code', $sisoCodes);
        }
        $listRegions = $regionQuery->pluck('region_name');

        $filterRegion = $request->input('region', '');
        $filterArea = $request->input('area', '');

        // Auto-detect territory for logged-in user if region not specified
        if (empty($filterRegion)) {
            // 1. Check if user has locked regions from accessLevel
            if ($lockedRegions !== null && count($lockedRegions) >= 1) {
                $filterRegion = $lockedRegions[0];
            } else {
                // 2. Check if user is a salesman in master table
                $rawUserCode = trim($user->userid ?? '');
                if (!empty($rawUserCode)) {
                    $userSalesman = DB::table('insentif_mingguan_master_salesmans as ims')
                        ->join('insentif_mingguan_master_distributors as imd', function ($join) {
                            $join->on('ims.bulan', '=', 'imd.bulan')
                                 ->on('ims.distributor_code', '=', 'imd.distributor_code');
                        })
                        ->where('ims.bulan', $bulan)
                        ->where(function ($q) use ($rawUserCode) {
                            $q->where('ims.sales_code', $rawUserCode)
                              ->orWhere('ims.sales_code', 'ILIKE', $rawUserCode);
                        })
                        ->select('imd.region_name', 'imd.area_name')
                        ->first();

                    if ($userSalesman && !empty($userSalesman->region_name)) {
                        $filterRegion = $userSalesman->region_name;
                        if (empty($filterArea) && !empty($userSalesman->area_name)) {
                            $filterArea = $userSalesman->area_name;
                        }
                    }
                }

                // 3. Check if user is SPV in master table
                if (empty($filterRegion) && (!empty($user->supervisor_code) || !empty($user->userid))) {
                    $userSpv = DB::table('insentif_mingguan_master_spvs')
                        ->where('bulan', $bulan)
                        ->where(function ($q) use ($user) {
                            if (!empty($user->supervisor_code)) {
                                $q->where('supervisor_code_hak_akses_login', $user->supervisor_code)
                                  ->orWhere('supervisor_code', $user->supervisor_code);
                            }
                            if (!empty($user->userid)) {
                                $q->orWhere('supervisor_code', $user->userid);
                            }
                        })
                        ->whereNotNull('region_name')
                        ->select('region_name', 'area_name')
                        ->first();

                    if ($userSpv && !empty($userSpv->region_name)) {
                        $filterRegion = $userSpv->region_name;
                        if (empty($filterArea) && !empty($userSpv->area_name)) {
                            $filterArea = $userSpv->area_name;
                        }
                    }
                }

                // 4. Default fallback to first available region
                if (empty($filterRegion) && $listRegions->isNotEmpty()) {
                    $filterRegion = $listRegions->first();
                }
            }
        }

        // Daftar Area untuk filter dropdown
        $listAreas = collect();
        if ($filterRegion) {
            $areaQuery = InsentifMingguanMasterDistributor::select('area_name')
                ->where('region_name', $filterRegion)
                ->whereNotNull('area_name')
                ->distinct()
                ->orderBy('area_name');

            if ($lockedAreas !== null) {
                $areaQuery->whereIn('area_name', $lockedAreas);
            }
            if ($accessLevel === 'supervisor' && $sisoCodes) {
                $areaQuery->whereIn('supervisor_code', $sisoCodes);
            }
            $listAreas = $areaQuery->pluck('area_name');
        }

        if (!$request->has('area')) {
            if (empty($filterArea) && $lockedAreas !== null && count($lockedAreas) === 1) {
                $filterArea = $lockedAreas[0];
            }
        } else {
            $filterArea = $request->input('area', '');
        }

        // 3. Header Grup VTKP yang aktif di region ini
        $headersQuery = InsentifHeaderGrup::query();
        if ($filterRegion) {
            $headersQuery->whereHas('regions', function ($q) use ($filterRegion) {
                $q->where('region_name', $filterRegion);
            });
        }
        $headers = $headersQuery->with('details')->orderBy('nama_header')->get();

        // 4. Hitung Data Salesmen (SE)
        $salesmenQuery = DB::table('insentif_mingguan_master_salesmans as ims')
            ->join('insentif_mingguan_master_distributors as imd', function ($join) {
                $join->on('ims.bulan', '=', 'imd.bulan')
                     ->on('ims.distributor_code', '=', 'imd.distributor_code');
            })
            ->where('ims.bulan', $bulan)
            ->whereRaw("LOWER(TRIM(ims.jenis_se)) = 'se'");

        if ($filterRegion) {
            $salesmenQuery->where('imd.region_name', $filterRegion);
        }
        if ($filterArea) {
            $salesmenQuery->where('imd.area_name', $filterArea);
        } elseif ($lockedAreas !== null) {
            $salesmenQuery->whereIn('imd.area_name', $lockedAreas);
        }
        if ($sisoCodes !== null) {
            $salesmenQuery->whereIn('imd.supervisor_code', $sisoCodes);
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
            ->orderBy('imd.area_name')
            ->orderBy('imd.distributor_name')
            ->orderBy('ims.sales_name')
            ->get();

        // Preload VTKP targets
        $targetsRaw = DB::table('target_se_vtkps')->where('bulan', $bulan)->get();
        $targets = [];
        foreach ($targetsRaw as $t) {
            $key = strtoupper(trim($t->distributor_code) . '_' . trim($t->salesman_code) . '_' . trim($t->produk_grup));
            $targets[$key] = (float)$t->target;
        }

        // Preload PG3 mappings & Actuals
        $mappingsRaw = DB::table('insentif_mingguan_pg3_mappings')->get();
        $mappingMingguanToBulanan = [];
        foreach ($mappingsRaw as $m) {
            $mappingMingguanToBulanan[trim($m->pg3_mingguan)] = trim($m->pg3_bulanan);
        }

        $actualsRaw = DB::table('insentif_mingguan_qty_per_ses')->where('bulan', $bulan)->get();
        $actuals = [];
        foreach ($actualsRaw as $a) {
            $pg3 = trim($a->product_group_3);
            $mappedPg3 = $mappingMingguanToBulanan[$pg3] ?? $pg3;
            $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->sales_code) . '_' . $mappedPg3);
            if (!isset($actuals[$key])) {
                $actuals[$key] = 0;
            }
            $actuals[$key] += (float)$a->qty_ctn;
        }

        // Preload Value targets & actuals
        $valueTargetsRaw = DB::table('target_se_values')->where('bulan', $bulan)->get();
        $valueTargets = [];
        foreach ($valueTargetsRaw as $t) {
            $key = strtoupper(trim($t->distributor_code) . '_' . trim($t->salesman_code));
            $valueTargets[$key] = (float)$t->target;
        }

        $valueActualsRaw = DB::table('insentif_mingguan_value_per_salesmans')->where('bulan', $bulan)->get();
        $valueActuals = [];
        foreach ($valueActualsRaw as $a) {
            $key = strtoupper(trim($a->distributor_code) . '_' . trim($a->sales_code));
            if (!isset($valueActuals[$key])) {
                $valueActuals[$key] = 0;
            }
            $valueActuals[$key] += (float)$a->actual;
        }

        // Preload RO & Visits & IPT
        $roRaw = DB::table('insentif_se_ro')->where('bulan', $bulan)->get();
        $roData = [];
        foreach ($roRaw as $r) {
            $key = strtoupper(trim($r->kodecabang) . '_' . trim($r->slsno));
            $roData[$key] = [
                'frekuensi' => $r->frekuensi ?? '-',
                'total_customer' => (int) ($r->total_customer ?? 0),
            ];
        }

        $visitsRaw = DB::table('insentif_se_visits')->where('bulan', $bulan)->get();
        $visitsData = [];
        foreach ($visitsRaw as $v) {
            $key = strtoupper(trim($v->distributor_code) . '_' . trim($v->salesman_code));
            $visitsData[$key] = [
                'pc' => (int) ($v->pc ?? 0),
                'ac' => (int) ($v->ac ?? 0),
                'ec' => (int) ($v->ec ?? 0),
            ];
        }

        $iptRaw = DB::table('insentif_mingguan_se_ipts')->where('bulan', $bulan)->get();
        $iptData = [];
        foreach ($iptRaw as $i) {
            $key = strtoupper(trim($i->distributor_code) . '_' . trim($i->sales_code));
            if (!isset($iptData[$key])) {
                $iptData[$key] = ['sku' => 0, 'ec' => 0];
            }
            $iptData[$key]['sku'] += (float) ($i->sku ?? 0);
            $iptData[$key]['ec']  += (float) ($i->ec ?? 0);
        }

        $salesmenList = [];
        $totalThpSemua = 0;
        $totalKotorSemua = 0;
        $totalEstimasiSemua = 0;
        $totalPphSemua = 0;
        $totalSalesmen = count($salesmen);
        $totalAchieved = 0;
        $totalPenaltySfa = 0;

        foreach ($salesmen as $sm) {
            $valKey = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se));

            // 1. Value SO
            $valTarget = $valueTargets[$valKey] ?? 0;
            $valReal = $valueActuals[$valKey] ?? 0;
            $valAch = $valTarget > 0 ? ($valReal / $valTarget) * 100 : ($valReal > 0 ? 100 : 0);
            $valInsentif = $this->hitungInsentifValue($valTarget, $valAch);

            // 2. VTKP
            $totalInsentifVtkp = 0;
            $vtkpDetails = [];
            foreach ($headers as $h) {
                $targetVal = 0;
                $realVal = 0;
                foreach ($h->details as $d) {
                    $tk = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se) . '_' . trim($d->product_group_3));
                    $targetVal += ($targets[$tk] ?? 0);

                    $ak = strtoupper(trim($sm->kd_dist) . '_' . trim($sm->kode_se) . '_' . trim($d->product_group_3));
                    $realVal += ($actuals[$ak] ?? 0);
                }

                $growth = $targetVal > 0 ? (($realVal - $targetVal) / $targetVal) * 100 : ($realVal > 0 ? 100 : 0);
                $insentif = ($targetVal > 0 && $valAch >= 60) ? $this->hitungInsentifVtkp($growth, $realVal) : 0;
                $totalInsentifVtkp += $insentif;

                $vtkpDetails[] = [
                    'nama_header' => $h->nama_header,
                    'target' => $targetVal,
                    'real' => $realVal,
                    'growth' => round($growth, 1),
                    'insentif' => $insentif,
                ];
            }

            // 3. EC
            $roRow = $roData[$valKey] ?? ['frekuensi' => '-', 'total_customer' => 0];
            $visit = $visitsData[$valKey] ?? ['pc' => 0, 'ac' => 0, 'ec' => 0];
            $ro = $roRow['total_customer'];
            $frekuensi = $roRow['frekuensi'];
            $ac = $visit['ac'];
            $ec = $visit['ec'];

            $persenEc = $ac > 0 ? round(($ec / $ac) * 100) : 0;
            $ecHarian = round($ec / 25, 1);
            $insentifEc = 0;

            if ($valAch >= 60 && $ro >= 250) {
                if ($persenEc >= 80 && $ecHarian >= 16)      $insentifEc = 800000;
                elseif ($persenEc >= 70 && $ecHarian >= 14)  $insentifEc = 500000;
                elseif ($persenEc >= 60 && $ecHarian >= 12)  $insentifEc = 300000;
                elseif ($persenEc >= 50 && $ecHarian >= 10)  $insentifEc = 50000;
            }

            // 4. IPT
            $iptRow = $iptData[$valKey] ?? ['sku' => 0, 'ec' => 0];
            $iptVal = $iptRow['ec'] > 0 ? floor($iptRow['sku'] / $iptRow['ec']) : 0;
            $insentifIpt = 0;
            if ($ro >= 250) {
                if ($iptVal >= 12)     $insentifIpt = 600000;
                elseif ($iptVal >= 8)  $insentifIpt = 500000;
                elseif ($iptVal >= 7)  $insentifIpt = 250000;
                elseif ($iptVal >= 5)  $insentifIpt = 150000;
            }

            // 5. SFA
            $sfaPc = $visit['pc'];
            $sfaAc = $visit['ac'];
            if ($sfaPc == 0 && $sfaAc == 0) {
                $sfaPersen = 100.0;
            } elseif ($sfaPc > 0) {
                $sfaPersen = round(($sfaAc / $sfaPc) * 100, 1);
            } else {
                $sfaPersen = 0.0;
            }

            $totalKotor = $valInsentif + $totalInsentifVtkp + $insentifEc + $insentifIpt;
            $sfaPenaltyApplied = $sfaPersen < 95.0;
            if ($sfaPenaltyApplied) {
                $totalPenaltySfa++;
                $totalEstimasi = 0.25 * $totalKotor;
            } else {
                $totalEstimasi = $totalKotor;
            }

            $pph = $totalEstimasi * 0.05;
            $thp = $totalEstimasi - $pph;

            $totalKotorSemua += $totalKotor;
            $totalEstimasiSemua += $totalEstimasi;
            $totalPphSemua += $pph;
            $totalThpSemua += $thp;

            if ($thp > 0) {
                $totalAchieved++;
            }

            $salesmenList[] = [
                'area_name' => $sm->area,
                'cabang' => $sm->cabang,
                'distributor' => $sm->distributor,
                'kd_dist' => $sm->kd_dist,
                'salesman_code' => $sm->kode_se,
                'salesman_name' => $sm->nama_se,
                'value' => [
                    'target' => $valTarget,
                    'actual' => $valReal,
                    'ach_pct' => round($valAch, 1),
                    'insentif' => $valInsentif,
                ],
                'vtkp' => [
                    'total_insentif' => $totalInsentifVtkp,
                    'details' => $vtkpDetails,
                ],
                'ec' => [
                    'ro' => $ro,
                    'frekuensi' => $frekuensi,
                    'ac' => $ac,
                    'ec' => $ec,
                    'persen_ec' => $persenEc,
                    'ec_harian' => $ecHarian,
                    'insentif' => $insentifEc,
                ],
                'ipt' => [
                    'sku' => $iptRow['sku'],
                    'ec' => $iptRow['ec'],
                    'ipt_val' => $iptVal,
                    'insentif' => $insentifIpt,
                ],
                'sfa' => [
                    'pc' => $sfaPc,
                    'ac' => $sfaAc,
                    'sfa_pct' => $sfaPersen,
                    'penalty_applied' => $sfaPenaltyApplied,
                ],
                'totals' => [
                    'kotor' => $totalKotor,
                    'estimasi' => $totalEstimasi,
                    'pph' => $pph,
                    'thp' => $thp,
                ],
            ];
        }

        // 5. Cek apakah user adalah Salesman, SPV, atau Kacab untuk My Incentive Card
        $myIncentive = null;
        $userCode = strtoupper(trim($user->userid ?? ''));
        $userSpvCode = strtoupper(trim($user->supervisor_code ?? ''));
        $userName = strtoupper(trim($user->name ?? ''));

        // A. Cek jika user login adalah Salesman
        foreach ($salesmenList as $item) {
            if (strtoupper(trim($item['salesman_code'])) === $userCode) {
                $myIncentive = [
                    'role_type' => 'SE',
                    'title' => 'Insentif Mingguan Saya (SE)',
                    'data' => $item,
                ];
                break;
            }
        }

        // 6. SPV Data (Jika user SPV atau level atas)
        $spvDataRaw = $this->calculatorService->calculateSpv($bulan, $filterRegion, $filterArea, null, $sisoCodes);
        $spvList = $spvDataRaw['spvData'] ?? [];

        // B. Cek jika user login adalah SPV
        if (!$myIncentive && !empty($spvList)) {
            $matchedSpv = null;
            foreach ($spvList as $spvItem) {
                $sc = strtoupper(trim($spvItem['supervisor_code'] ?? ''));
                $sn = strtoupper(trim($spvItem['supervisor_name'] ?? ''));
                if ($sc === $userSpvCode || $sc === $userCode || ($userName !== '' && $sn === $userName)) {
                    $matchedSpv = $spvItem;
                    break;
                }
            }
            if (!$matchedSpv && $accessLevel === 'supervisor') {
                $matchedSpv = reset($spvList);
            }
            if ($matchedSpv) {
                $myIncentive = [
                    'role_type' => 'SPV',
                    'title' => 'Insentif Mingguan Saya (SPV)',
                    'data' => $matchedSpv,
                ];
            }
        }

        // 7. Kacab Data
        $kacabList = $this->calculatorService->calculateKacab($bulan, $filterRegion, $filterArea, null, $sisoCodes);

        // C. Cek jika user login adalah Kacab
        if (!$myIncentive && !empty($kacabList)) {
            foreach ($kacabList as $kacabItem) {
                $kn = strtoupper(trim($kacabItem['nama_kacab'] ?? ''));
                if ($kn !== '-' && $kn !== '' && $userName !== '' && ($kn === $userName || str_contains($userName, $kn) || str_contains($kn, $userName))) {
                    $myIncentive = [
                        'role_type' => 'KACAB',
                        'title' => 'Insentif Mingguan Saya (Kacab)',
                        'data' => $kacabItem,
                    ];
                    break;
                }
            }
        }

        $summaryTotals = [
            'total_thp' => $totalThpSemua,
            'total_kotor' => $totalKotorSemua,
            'total_estimasi' => $totalEstimasiSemua,
            'total_pph' => $totalPphSemua,
            'total_salesmen' => $totalSalesmen,
            'total_achieved' => $totalAchieved,
            'total_penalty_sfa' => $totalPenaltySfa,
            'total_spv' => count($spvList),
            'total_kacab' => count($kacabList),
        ];

        return Inertia::render('mobile/Pages/InsentifMingguan/Index', [
            'user' => [
                'name' => $user->name,
                'userid' => $user->userid,
                'supervisor_code' => $user->supervisor_code,
                'access_level' => $accessLevel,
                'role' => $user->getRoleNames()->first() ?? 'User',
            ],
            'currentBulan' => $bulan,
            'listBulan' => $listBulan,
            'filterRegion' => $filterRegion,
            'filterArea' => $filterArea,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'myIncentive' => $myIncentive,
            'salesmenList' => $salesmenList,
            'spvList' => $spvList,
            'kacabList' => $kacabList,
            'headers' => $headers,
            'summaryTotals' => $summaryTotals,
        ]);
    }

    private function hitungInsentifValue(float $target, float $ach): float
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
        }
        return 0;
    }

    private function hitungInsentifVtkp(float $growth, float $real): float
    {
        $g = round($growth);
        $r = round($real);
        if ($g >= 30) {
            if ($r >= 1500) return 500000;
            if ($r >= 1000) return 350000;
            if ($r >= 500)  return 250000;
            if ($r >= 200)  return 150000;
            if ($r >= 50)   return 50000;
        } elseif ($g >= 20) {
            if ($r >= 1500) return 350000;
            if ($r >= 1000) return 250000;
            if ($r >= 500)  return 150000;
            if ($r >= 200)  return 100000;
            if ($r >= 50)   return 35000;
        } elseif ($g >= 10) {
            if ($r >= 1500) return 200000;
            if ($r >= 1000) return 150000;
            if ($r >= 500)  return 100000;
            if ($r >= 200)  return 75000;
            if ($r >= 50)   return 25000;
        }
        return 0;
    }
}
