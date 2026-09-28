<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Report\FundamentalSalesHierarchy\FundamentalSalesMultiSheetExport;

class FundamentalSalesHierarchyExportController extends Controller
{
    public function export(Request $request, $type)
    {
        $code = $request->query('code');
        $year = $request->query('year', date('Y'));
        
        // 1. Fetch raw data matching the year and user access rights
        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();
        
        $query = DB::table('ipm_fundamental_sales as ifs')
            ->leftJoin('master_branches as mb', 'mb.branch_name', '=', 'ifs.cabang')
            ->leftJoin('master_supervisors as ms', 'ms.supervisor_code', '=', 'mb.supervisor_code')
            ->leftJoin('master_areas as ma', 'ma.area_code', '=', 'ms.area_code')
            ->leftJoin('master_regions as mr', 'mr.region_code', '=', 'ma.region_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'ms.supervisor_code')
            ->whereYear('ifs.bulan', $year)
            ->whereNotNull('te.team_elite_code');

        if ($accessLevel === 'region') {
            $query->whereIn('mr.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $query->whereIn('ma.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $query->where('te.team_elite_code', $user->supervisor_code);
        }

        // To optimize, if the export is for a specific node, we can filter the DB query directly
        if ($type === 'region') {
            $query->where('mr.region_name', $code); // assuming code is region_name
        } elseif ($type === 'area') {
            $query->where('ma.area_name', $code); // assuming code is area_name
        } elseif ($type === 'supervisor') {
            $query->where('te.team_elite_code', $code);
        }

        $rawData = $query->select(
            'mr.region_name',
            'ma.area_name',
            'te.team_elite_code as supervisor_code',
            'ms.description as supervisor_name',
            'ifs.cabang as cabang_name',
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
        ->groupBy('mr.region_name', 'ma.area_name', 'te.team_elite_code', 'ms.description', 'ifs.cabang', DB::raw('EXTRACT(MONTH FROM ifs.bulan)'))
        ->orderBy('mr.region_name')
        ->orderBy('ma.area_name')
        ->orderBy('ms.description')
        ->orderBy('ifs.cabang')
        ->get();

        $hierarchy = [];
        $emptyRaw = [
            'target' => 0, 'selling_out' => 0, 'pc' => 0, 'ac' => 0, 'ec' => 0,
            'se' => 0, 'ro' => 0, 'ao' => 0, 'sku' => 0, 'nota' => 0,
            'potensi_rwo' => 0, 'capai_rwo' => 0
        ];

        foreach ($rawData as $row) {
            $regionName = $row->region_name ?: 'Unknown Region';
            $areaName = $row->area_name ?: 'Unknown Area';
            $spvCode = $row->supervisor_code;
            $spvName = $row->supervisor_name ?: $spvCode;
            $cabangName = $row->cabang_name ?: 'Unknown Cabang';
            $m = (int)$row->month_num;
            
            if (!isset($hierarchy[$regionName])) {
                $hierarchy[$regionName] = ['type' => 'region', 'name' => $regionName, 'code' => $regionName, 'raw' => array_fill(1, 12, $emptyRaw), 'months' => [], 'children' => []];
            }
            if (!isset($hierarchy[$regionName]['children'][$areaName])) {
                $hierarchy[$regionName]['children'][$areaName] = ['type' => 'area', 'name' => $areaName, 'code' => $areaName, 'raw' => array_fill(1, 12, $emptyRaw), 'months' => [], 'children' => []];
            }
            if (!isset($hierarchy[$regionName]['children'][$areaName]['children'][$spvCode])) {
                $hierarchy[$regionName]['children'][$areaName]['children'][$spvCode] = ['type' => 'supervisor', 'name' => $spvName, 'code' => $spvCode, 'raw' => array_fill(1, 12, $emptyRaw), 'months' => [], 'children' => []];
            }
            if (!isset($hierarchy[$regionName]['children'][$areaName]['children'][$spvCode]['children'][$cabangName])) {
                $hierarchy[$regionName]['children'][$areaName]['children'][$spvCode]['children'][$cabangName] = ['type' => 'cabang', 'name' => $cabangName, 'code' => $cabangName, 'raw' => array_fill(1, 12, $emptyRaw), 'months' => []];
            }

            $rawValues = [
                'target' => (float)$row->total_target,
                'selling_out' => (float)$row->total_selling_out,
                'pc' => (float)$row->total_pc,
                'ac' => (float)$row->total_ac,
                'ec' => (float)$row->total_ec,
                'se' => (float)$row->total_se,
                'ro' => (float)$row->total_ro,
                'ao' => (float)$row->total_ao,
                'sku' => (float)$row->total_sku,
                'nota' => (float)$row->total_nota,
                'potensi_rwo' => (float)$row->total_potensi_rwo,
                'capai_rwo' => (float)$row->total_capai_rwo,
            ];

            foreach ($rawValues as $k => $v) {
                $hierarchy[$regionName]['children'][$areaName]['children'][$spvCode]['children'][$cabangName]['raw'][$m][$k] += $v;
                $hierarchy[$regionName]['children'][$areaName]['children'][$spvCode]['raw'][$m][$k] += $v;
                $hierarchy[$regionName]['children'][$areaName]['raw'][$m][$k] += $v;
                $hierarchy[$regionName]['raw'][$m][$k] += $v;
            }
        }

        $this->calculateKPIsForNode($hierarchy);

        // Find the requested node
        $targetNode = $this->findNode($hierarchy, $type, $code);

        if (!$targetNode) {
            abort(404, 'Data tidak ditemukan.');
        }

        // Format data for export classes
        $dashboardData = [
            'months' => $targetNode['months'],
            'branches' => []
        ];

        // Format branch data (which is actually child data)
        $branchData = [];
        if (isset($targetNode['children'])) {
            foreach ($targetNode['children'] as $childName => $childNode) {
                $branchData[$childName] = $childNode['months'];
                $dashboardData['branches'][$childName] = true;
            }
        }

        $supervisorInfo = new \stdClass();
        $supervisorInfo->code = $targetNode['code'];
        $supervisorInfo->name = $targetNode['name'];
        $supervisorInfo->type = $type;
        $supervisorInfo->region_name = $type == 'region' ? $targetNode['name'] : 'N/A';
        $supervisorInfo->area_name = $type == 'area' ? $targetNode['name'] : 'N/A';
        $supervisorInfo->branches_string = implode(', ', array_keys($dashboardData['branches']));

        $kpis = [
            'so' => ['label' => 'Penjualan Bersih (SO)', 'bobot' => 35],
            'ipt' => ['label' => 'Item Per Transaksi (IPT)', 'bobot' => 15],
            'jks' => ['label' => 'Kunjungan JKS SE (AC/PC)', 'bobot' => 5],
            'ec' => ['label' => 'Efektif Call (EC/AC)', 'bobot' => 10],
            'standpro' => ['label' => 'Standpro (SE * 300)', 'bobot' => 10],
            'ao' => ['label' => 'Active Outlet (AO/RO)', 'bobot' => 10],
            'rwo' => ['label' => 'Reward Outlet (RWO)', 'bobot' => 15],
        ];

        $filename = 'Laporan_KPI_' . ucfirst($type) . '_' . $code . '_' . $year . '.xlsx';
        $filename = str_replace(['/', '\\'], '_', $filename);

        return Excel::download(
            new FundamentalSalesMultiSheetExport($supervisorInfo, $year, $dashboardData, $branchData, $kpis), 
            $filename
        );
    }

    private function findNode($nodes, $type, $code)
    {
        foreach ($nodes as $node) {
            if ($node['type'] === $type && $node['code'] === $code) {
                return $node;
            }
            if (isset($node['children'])) {
                $found = $this->findNode($node['children'], $type, $code);
                if ($found) return $found;
            }
        }
        return null;
    }

    private function calculateKPIsForNode(&$nodes)
    {
        foreach ($nodes as &$node) {
            if (isset($node['children'])) {
                $this->calculateKPIsForNode($node['children']);
            }

            for ($m = 1; $m <= 12; $m++) {
                $raw = $node['raw'][$m];

                $so_ach = $raw['target'] > 0 ? ($raw['selling_out'] / $raw['target']) : 0;
                $so_score = min($so_ach * 35, 35);

                $jks_ach = $raw['pc'] > 0 ? ($raw['ac'] / $raw['pc']) : 0;
                $jks_score = min($jks_ach * 5, 5);

                $ec_ach = $raw['ac'] > 0 ? ($raw['ec'] / $raw['ac']) : 0;
                $ec_score = min($ec_ach * 10, 10);

                $standpro_target = $raw['se'] * 300;
                $standpro_ach = $standpro_target > 0 ? ($raw['ro'] / $standpro_target) : 0;
                $standpro_score = min($standpro_ach * 10, 10);

                $ao_ach = $raw['ro'] > 0 ? ($raw['ao'] / $raw['ro']) : 0;
                $ao_score = min($ao_ach * 10, 10);

                $ipt_actual = $raw['nota'] > 0 ? ($raw['sku'] / $raw['nota']) : 0;
                $ipt_target = 8;
                $ipt_ach = $ipt_target > 0 ? ($ipt_actual / $ipt_target) : 0;
                $ipt_score = min($ipt_ach * 15, 15);

                $rwo_ach = $raw['potensi_rwo'] > 0 ? ($raw['capai_rwo'] / $raw['potensi_rwo']) : 0;
                $rwo_score = min($rwo_ach * 15, 15);

                // Export classes expect 'ach' as decimal 0-1, NOT percentage 0-100!
                $node['months'][$m] = [
                    'so' => ['target' => $raw['target'], 'actual' => $raw['selling_out'], 'ach' => $so_ach, 'score' => $so_score],
                    'jks' => ['target' => $raw['pc'], 'actual' => $raw['ac'], 'ach' => $jks_ach, 'score' => $jks_score],
                    'ec' => ['target' => $raw['ac'], 'actual' => $raw['ec'], 'ach' => $ec_ach, 'score' => $ec_score],
                    'standpro' => ['target' => $standpro_target, 'actual' => $raw['ro'], 'ach' => $standpro_ach, 'score' => $standpro_score],
                    'ao' => ['target' => $raw['ro'], 'actual' => $raw['ao'], 'ach' => $ao_ach, 'score' => $ao_score],
                    'ipt' => ['target' => $ipt_target, 'actual' => $ipt_actual, 'ach' => $ipt_ach, 'score' => $ipt_score],
                    'rwo' => ['target' => $raw['potensi_rwo'], 'actual' => $raw['capai_rwo'], 'ach' => $rwo_ach, 'score' => $rwo_score],
                ];
            }
        }
    }
}
