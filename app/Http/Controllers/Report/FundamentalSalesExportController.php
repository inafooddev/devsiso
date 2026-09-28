<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Report\FundamentalSales\FundamentalSalesMultiSheetExport;

class FundamentalSalesExportController extends Controller
{
    private function getEmptyKPIs()
    {
        return [
            'so' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
            'ipt' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
            'jks' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
            'ec' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
            'standpro' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
            'ao' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
            'rwo' => ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0],
        ];
    }

    public function export(Request $request, $supervisorCode)
    {
        $year = $request->query('year', date('Y'));
        
        // --- 1. Fetch Supervisor Details ---
        $supervisorInfo = DB::table('team_elite_code_mappings as te')
            ->leftJoin('master_supervisors as ms', 'te.siso_code', '=', 'ms.supervisor_code')
            ->leftJoin('master_areas as ma', 'ma.area_code', '=', 'ms.area_code')
            ->leftJoin('master_regions as mr', 'mr.region_code', '=', 'ma.region_code')
            ->where('te.team_elite_code', $supervisorCode)
            ->select('te.team_elite_code as code', 'ms.description as name', 'ma.area_name', 'mr.region_name')
            ->first();

        if (!$supervisorInfo) {
            abort(404, 'Supervisor tidak ditemukan.');
        }

        // --- 2. Dashboard Matrix ---
        $queryDashboard = DB::table('ipm_fundamental_sales as ifs')
            ->leftJoin('master_branches as mb', 'mb.branch_name', '=', 'ifs.cabang')
            ->leftJoin('master_supervisors as ms', 'ms.supervisor_code', '=', 'mb.supervisor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'ms.supervisor_code')
            ->whereYear('ifs.bulan', $year)
            ->where('te.team_elite_code', $supervisorCode);

        $rawDataDashboard = $queryDashboard->select(
            DB::raw("STRING_AGG(DISTINCT ifs.cabang, ', ') as cabang_list"),
            DB::raw('EXTRACT(MONTH FROM ifs.bulan) as month_num'),
            DB::raw('SUM(ifs.target) as total_target'),
            DB::raw('SUM(ifs.selling_out) as total_selling_out'),
            DB::raw('SUM(ifs.pc) as total_pc'),
            DB::raw('SUM(ifs.ac) as total_ac'),
            DB::raw('SUM(ifs.ec) as total_ec'),
            DB::raw('SUM(ifs.jumlah_se) as total_se'),
            DB::raw('SUM(ifs.ro) as total_ro'),
            DB::raw('SUM(ifs.ao) as total_ao'),
            DB::raw('SUM(ifs.sku) as total_sku'),
            DB::raw('SUM(ifs.nota) as total_nota'),
            DB::raw('SUM(ifs.potensi_rwo) as total_potensi_rwo'),
            DB::raw('SUM(ifs.capai_rwo) as total_capai_rwo')
        )
        ->groupBy(DB::raw('EXTRACT(MONTH FROM ifs.bulan)'))
        ->get();

        $dashboardData = [
            'months' => array_fill(1, 12, $this->getEmptyKPIs()),
            'branches' => []
        ];

        foreach ($rawDataDashboard as $row) {
            if ($row->cabang_list) {
                $branches = explode(', ', $row->cabang_list);
                foreach ($branches as $b) {
                    $dashboardData['branches'][$b] = true;
                }
            }

            $m = (int)$row->month_num;
            
            $so_target = (float)$row->total_target;
            $so_actual = (float)$row->total_selling_out;
            $so_ach = $so_target > 0 ? ($so_actual / $so_target) : 0;
            $so_score = min($so_ach * 35, 35);

            $pc = (float)$row->total_pc;
            $ac = (float)$row->total_ac;
            $jks_ach = $pc > 0 ? ($ac / $pc) : 0;
            $jks_score = min($jks_ach * 5, 5);

            $ec = (float)$row->total_ec;
            $ec_ach = $ac > 0 ? ($ec / $ac) : 0;
            $ec_score = min($ec_ach * 10, 10);

            $standpro_target = (float)$row->total_se * 300;
            $standpro_actual = (float)$row->total_ro;
            $standpro_ach = $standpro_target > 0 ? ($standpro_actual / $standpro_target) : 0;
            $standpro_score = min($standpro_ach * 10, 10);

            $ao = (float)$row->total_ao;
            $ro = (float)$row->total_ro;
            $ao_ach = $ro > 0 ? ($ao / $ro) : 0;
            $ao_score = min($ao_ach * 10, 10);

            $sku = (float)$row->total_sku;
            $nota = (float)$row->total_nota;
            $ipt_target = 8;
            $ipt_actual = $nota > 0 ? ($sku / $nota) : 0;
            $ipt_ach = $ipt_target > 0 ? ($ipt_actual / $ipt_target) : 0;
            $ipt_score = min($ipt_ach * 15, 15);

            $rwo_target = (float)$row->total_potensi_rwo;
            $rwo_actual = (float)$row->total_capai_rwo;
            $rwo_ach = $rwo_target > 0 ? ($rwo_actual / $rwo_target) : 0;
            $rwo_score = min($rwo_ach * 15, 15);

            $dashboardData['months'][$m] = [
                'so' => ['target' => $so_target, 'actual' => $so_actual, 'ach' => $so_ach, 'score' => $so_score],
                'ipt' => ['target' => $ipt_target, 'actual' => $ipt_actual, 'ach' => $ipt_ach, 'score' => $ipt_score],
                'jks' => ['target' => $pc, 'actual' => $ac, 'ach' => $jks_ach, 'score' => $jks_score],
                'ec' => ['target' => $ac, 'actual' => $ec, 'ach' => $ec_ach, 'score' => $ec_score],
                'standpro' => ['target' => $standpro_target, 'actual' => $standpro_actual, 'ach' => $standpro_ach, 'score' => $standpro_score],
                'ao' => ['target' => $ro, 'actual' => $ao, 'ach' => $ao_ach, 'score' => $ao_score],
                'rwo' => ['target' => $rwo_target, 'actual' => $rwo_actual, 'ach' => $rwo_ach, 'score' => $rwo_score],
            ];
        }
        
        ksort($dashboardData['branches']);
        $supervisorInfo->branches_string = implode(', ', array_keys($dashboardData['branches']));

        // --- 3. Branch Details ---
        $queryBranch = DB::table('ipm_fundamental_sales as ifs')
            ->leftJoin('master_branches as mb', 'mb.branch_name', '=', 'ifs.cabang')
            ->leftJoin('master_supervisors as ms', 'ms.supervisor_code', '=', 'mb.supervisor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'ms.supervisor_code')
            ->whereYear('ifs.bulan', $year)
            ->where('te.team_elite_code', $supervisorCode);
            
        $rawDataBranch = $queryBranch->select(
            'ifs.cabang',
            DB::raw('EXTRACT(MONTH FROM ifs.bulan) as month_num'),
            DB::raw('SUM(ifs.target) as total_target'),
            DB::raw('SUM(ifs.selling_out) as total_selling_out'),
            DB::raw('SUM(ifs.pc) as total_pc'),
            DB::raw('SUM(ifs.ac) as total_ac'),
            DB::raw('SUM(ifs.ec) as total_ec'),
            DB::raw('SUM(ifs.jumlah_se) as total_se'),
            DB::raw('SUM(ifs.ro) as total_ro'),
            DB::raw('SUM(ifs.ao) as total_ao'),
            DB::raw('SUM(ifs.sku) as total_sku'),
            DB::raw('SUM(ifs.nota) as total_nota'),
            DB::raw('SUM(ifs.potensi_rwo) as total_potensi_rwo'),
            DB::raw('SUM(ifs.capai_rwo) as total_capai_rwo')
        )
        ->groupBy('ifs.cabang', DB::raw('EXTRACT(MONTH FROM ifs.bulan)'))
        ->get();

        $branchData = [];
        $uniqueBranches = array_keys($dashboardData['branches']);
        foreach($uniqueBranches as $ub) {
            $branchData[$ub] = array_fill(1, 12, $this->getEmptyKPIs());
        }

        foreach($rawDataBranch as $row) {
            $cabang = $row->cabang;
            $m = (int)$row->month_num;
            if(!isset($branchData[$cabang])) continue;
            
            $so_target = (float)$row->total_target;
            $so_actual = (float)$row->total_selling_out;
            $so_ach = $so_target > 0 ? ($so_actual / $so_target) : 0;
            $so_score = min($so_ach * 35, 35);

            $pc = (float)$row->total_pc;
            $ac = (float)$row->total_ac;
            $jks_ach = $pc > 0 ? ($ac / $pc) : 0;
            $jks_score = min($jks_ach * 5, 5);

            $ec = (float)$row->total_ec;
            $ec_ach = $ac > 0 ? ($ec / $ac) : 0;
            $ec_score = min($ec_ach * 10, 10);

            $standpro_target = (float)$row->total_se * 300;
            $standpro_actual = (float)$row->total_ro;
            $standpro_ach = $standpro_target > 0 ? ($standpro_actual / $standpro_target) : 0;
            $standpro_score = min($standpro_ach * 10, 10);

            $ao = (float)$row->total_ao;
            $ro = (float)$row->total_ro;
            $ao_ach = $ro > 0 ? ($ao / $ro) : 0;
            $ao_score = min($ao_ach * 10, 10);

            $sku = (float)$row->total_sku;
            $nota = (float)$row->total_nota;
            $ipt_target = 8;
            $ipt_actual = $nota > 0 ? ($sku / $nota) : 0;
            $ipt_ach = $ipt_target > 0 ? ($ipt_actual / $ipt_target) : 0;
            $ipt_score = min($ipt_ach * 15, 15);

            $rwo_target = (float)$row->total_potensi_rwo;
            $rwo_actual = (float)$row->total_capai_rwo;
            $rwo_ach = $rwo_target > 0 ? ($rwo_actual / $rwo_target) : 0;
            $rwo_score = min($rwo_ach * 15, 15);

            $branchData[$cabang][$m] = [
                'so' => ['target' => $so_target, 'actual' => $so_actual, 'ach' => $so_ach, 'score' => $so_score],
                'ipt' => ['target' => $ipt_target, 'actual' => $ipt_actual, 'ach' => $ipt_ach, 'score' => $ipt_score],
                'jks' => ['target' => $pc, 'actual' => $ac, 'ach' => $jks_ach, 'score' => $jks_score],
                'ec' => ['target' => $ac, 'actual' => $ec, 'ach' => $ec_ach, 'score' => $ec_score],
                'standpro' => ['target' => $standpro_target, 'actual' => $standpro_actual, 'ach' => $standpro_ach, 'score' => $standpro_score],
                'ao' => ['target' => $ro, 'actual' => $ao, 'ach' => $ao_ach, 'score' => $ao_score],
                'rwo' => ['target' => $rwo_target, 'actual' => $rwo_actual, 'ach' => $rwo_ach, 'score' => $rwo_score],
            ];
        }

        $kpis = [
            'so' => ['label' => 'Penjualan Bersih (SO)', 'bobot' => 35],
            'ipt' => ['label' => 'Item Per Transaksi (IPT)', 'bobot' => 15],
            'jks' => ['label' => 'Kunjungan JKS SE (AC/PC)', 'bobot' => 5],
            'ec' => ['label' => 'Efektif Call (EC/AC)', 'bobot' => 10],
            'standpro' => ['label' => 'Standpro (SE * 300)', 'bobot' => 10],
            'ao' => ['label' => 'Active Outlet (AO/RO)', 'bobot' => 10],
            'rwo' => ['label' => 'Reward Outlet (RWO)', 'bobot' => 15],
        ];

        return Excel::download(
            new FundamentalSalesMultiSheetExport($supervisorInfo, $year, $dashboardData, $branchData, $kpis), 
            'Laporan_KPI_Supervisor_' . $supervisorCode . '_' . $year . '.xlsx'
        );
    }
}
