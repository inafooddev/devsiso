<?php

namespace App\Livewire\Report\FundamentalSales;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;

#[Layout('layouts.app')]
#[Title('Individual Performance Management - Ranking SPV')]
class Ranking extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'report.fundamental-sales.index';

    // Filters
    public $selectedYear;
    public $selectedMonth;
    public $selectedRegion = '';
    public $selectedArea = '';
    public $sortBy = 'score_desc';
    public $search = '';

    public $regions = [];
    public $areas = [];

    public $appliedYear;
    public $appliedMonth;
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSortBy = 'score_desc';
    public $appliedSearch = '';

    public function mount()
    {
        $this->selectedYear = (int) date('Y');

        // Cek bulan terakhir yang memiliki data pada tahun berjalan
        $latestRecord = DB::table('ipm_fundamental_sales')
            ->whereYear('bulan', $this->selectedYear)
            ->orderBy('bulan', 'desc')
            ->value('bulan');

        if ($latestRecord) {
            $this->selectedMonth = (int) date('n', strtotime($latestRecord));
        } else {
            $this->selectedMonth = (int) date('n');
        }

        // Load regions & auto-select if user only has 1 region/area
        $this->loadRegions();

        // Sync applied filters after region/area initialization
        $this->syncAppliedFilters();
    }

    public function loadRegions()
    {
        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();

        $query = DB::table('team_elite_code_mappings as t')
            ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
            ->select('mr.region_code', 'mr.region_name')
            ->whereNotNull('mr.region_code')
            ->distinct()
            ->orderBy('mr.region_name');

        if ($accessLevel === 'region') {
            $query->whereIn('t.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $query->whereIn('t.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $query->where('t.team_elite_code', $user->supervisor_code);
        }

        $this->regions = $query->get()->toArray();

        if (count($this->regions) === 1 && empty($this->selectedRegion)) {
            $this->selectedRegion = $this->regions[0]->region_code;
            $this->loadAreasForRegion($this->selectedRegion);
        }
    }

    protected function loadAreasForRegion($regionCode)
    {
        $this->selectedArea = '';
        $this->areas = [];

        if ($regionCode) {
            $user = auth()->user();
            $accessLevel = $user->getAccessLevel();

            $query = DB::table('team_elite_code_mappings as t')
                ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
                ->where('t.region_code', $regionCode)
                ->whereNotNull('ma.area_code')
                ->select('ma.area_code', 'ma.area_name')
                ->distinct()
                ->orderBy('ma.area_name');

            if ($accessLevel === 'area') {
                $query->whereIn('t.area_code', (array) $user->area_code);
            } elseif ($accessLevel === 'supervisor') {
                $query->where('t.team_elite_code', $user->supervisor_code);
            }

            $this->areas = $query->get()->toArray();

            if (count($this->areas) === 1 && empty($this->selectedArea)) {
                $this->selectedArea = $this->areas[0]->area_code;
            }
        }
    }

    public function updatedSelectedYear()
    {
        $this->syncAppliedFilters();
    }

    public function updatedSelectedMonth()
    {
        $this->syncAppliedFilters();
    }

    public function updatedSelectedRegion($value)
    {
        $this->loadAreasForRegion($value);
        $this->syncAppliedFilters();
    }

    public function updatedSelectedArea()
    {
        $this->syncAppliedFilters();
    }

    public function updatedSortBy()
    {
        $this->syncAppliedFilters();
    }

    public function updatedSearch()
    {
        $this->appliedSearch = trim($this->search);
    }

    public function applyFilter()
    {
        $this->syncAppliedFilters();
    }

    public function resetFilter()
    {
        $this->search = '';
        if (count($this->regions) > 1) {
            $this->selectedRegion = '';
            $this->areas = [];
            $this->selectedArea = '';
        } elseif (count($this->areas) > 1) {
            $this->selectedArea = '';
        }
        $this->sortBy = 'score_desc';
        $this->syncAppliedFilters();
    }

    protected function syncAppliedFilters()
    {
        $this->appliedYear = (int) $this->selectedYear;
        $this->appliedMonth = (int) $this->selectedMonth;
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedSortBy = $this->sortBy;
        $this->appliedSearch = trim($this->search);
    }

    public function buildRankingReport(): array
    {
        if (empty($this->appliedYear) || empty($this->appliedMonth)) {
            return [
                'data' => [],
                'stats' => [
                    'total_spv' => 0,
                    'avg_score' => 0,
                    'pass_count' => 0,
                    'top_spv' => null,
                ],
            ];
        }

        $query = DB::table('ipm_fundamental_sales as ifs')
            ->leftJoin('master_branches as mb', 'mb.branch_name', '=', 'ifs.cabang')
            ->leftJoin('master_supervisors as ms', 'ms.supervisor_code', '=', 'mb.supervisor_code')
            ->leftJoin('master_areas as ma', 'ma.area_code', '=', 'ms.area_code')
            ->leftJoin('master_regions as mr', 'mr.region_code', '=', 'ma.region_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'ms.supervisor_code')
            ->whereYear('ifs.bulan', $this->appliedYear)
            ->whereMonth('ifs.bulan', $this->appliedMonth)
            ->whereNotNull('te.team_elite_code');

        if (!empty($this->appliedRegion)) {
            $query->where('mr.region_code', $this->appliedRegion);
        }
        if (!empty($this->appliedArea)) {
            $query->where('ma.area_code', $this->appliedArea);
        }

        // Apply access level restrictions — ALWAYS enforced regardless of filter selection
        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();
        if ($accessLevel === 'region') {
            $query->whereIn('mr.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area') {
            $query->whereIn('ma.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'supervisor') {
            $query->where('te.team_elite_code', $user->supervisor_code);
        }

        $rawData = $query->select(
            'mr.region_name',
            'ma.area_name',
            'te.team_elite_code as supervisor_code',
            'ms.description as supervisor_name',
            'ifs.cabang as cabang_name',
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
        ->groupBy('mr.region_name', 'ma.area_name', 'te.team_elite_code', 'ms.description', 'ifs.cabang')
        ->orderBy('mr.region_name')
        ->orderBy('ma.area_name')
        ->orderBy('ms.description')
        ->get();

        $processed = [];
        $emptyRaw = [
            'target' => 0, 'selling_out' => 0, 'pc' => 0, 'ac' => 0, 'ec' => 0,
            'se' => 0, 'ro' => 0, 'ao' => 0, 'sku' => 0, 'nota' => 0,
            'potensi_rwo' => 0, 'capai_rwo' => 0,
        ];

        foreach ($rawData as $row) {
            $spvCode = $row->supervisor_code;
            $spvName = $row->supervisor_name ?: $spvCode;
            $cabangName = $row->cabang_name ?: 'Unknown';

            if (!isset($processed[$spvCode])) {
                $processed[$spvCode] = [
                    'code' => $spvCode,
                    'name' => $spvName,
                    'display_name' => $this->formatDisplayName($spvName, $spvCode),
                    'initials' => $this->extractInitials($spvName),
                    'region' => $row->region_name ?: 'Unknown',
                    'area' => $row->area_name ?: 'Unknown',
                    'raw' => $emptyRaw,
                    'branches' => [],
                    'children' => [],
                ];
            }

            if (!isset($processed[$spvCode]['children'][$cabangName])) {
                $processed[$spvCode]['children'][$cabangName] = [
                    'name' => $cabangName,
                    'raw' => $emptyRaw,
                ];
            }

            $processed[$spvCode]['branches'][] = $cabangName;

            $vals = [
                'target' => (float) $row->total_target,
                'selling_out' => (float) $row->total_selling_out,
                'pc' => (float) $row->total_pc,
                'ac' => (float) $row->total_ac,
                'ec' => (float) $row->total_ec,
                'se' => (float) $row->total_se,
                'ro' => (float) $row->total_ro,
                'ao' => (float) $row->total_ao,
                'sku' => (float) $row->total_sku,
                'nota' => (float) $row->total_nota,
                'potensi_rwo' => (float) $row->total_potensi_rwo,
                'capai_rwo' => (float) $row->total_capai_rwo,
            ];

            foreach ($vals as $k => $v) {
                $processed[$spvCode]['raw'][$k] += $v;
                $processed[$spvCode]['children'][$cabangName]['raw'][$k] += $v;
            }
        }

        $allSpv = [];
        foreach ($processed as $spvCode => $spv) {
            $kpiData = $this->calculateNodeKPIs($spv['raw']);
            $spv['kpis'] = $kpiData['kpis'];
            $spv['total_score'] = $kpiData['total_score'];
            $spv['se_count'] = (int) $spv['raw']['se'];
            $uniqueBranches = array_values(array_unique($spv['branches']));
            $spv['branches_list'] = $uniqueBranches;
            $spv['branches_string'] = implode(', ', $uniqueBranches);

            // Breakdown Cabang
            $childrenBreakdown = [];
            foreach ($spv['children'] as $cName => $cData) {
                $cKpi = $this->calculateNodeKPIs($cData['raw']);
                $childrenBreakdown[$cName] = [
                    'name' => $cName,
                    'kpis' => $cKpi['kpis'],
                    'total_score' => $cKpi['total_score'],
                    'se_count' => (int) $cData['raw']['se'],
                ];
            }
            // Urutkan cabang berdasarkan skor tertinggi
            uasort($childrenBreakdown, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
            $spv['children'] = $childrenBreakdown;

            $allSpv[] = $spv;
        }

        // 1. Selalu urutkan berdasarkan total_score tertinggi terlebih dahulu untuk menetapkan Rank asli
        usort($allSpv, function ($a, $b) {
            $cmp = $b['total_score'] <=> $a['total_score'];
            if ($cmp !== 0) {
                return $cmp;
            }
            // Tie-breaker: persentase pencapaian SO tertinggi
            return ($b['kpis']['so']['ach'] ?? 0) <=> ($a['kpis']['so']['ach'] ?? 0);
        });

        $totalInScope = count($allSpv);
        foreach ($allSpv as $index => &$item) {
            $item['rank'] = $index + 1;
            $item['total_spv_in_scope'] = $totalInScope;
        }
        unset($item);

        // 2. Hitung Summary Stats dari populasi scope (sebelum filter search teks)
        $topSpv = $allSpv[0] ?? null;
        $totalScores = array_sum(array_column($allSpv, 'total_score'));
        $avgScore = $totalInScope > 0 ? ($totalScores / $totalInScope) : 0;
        $passCount = count(array_filter($allSpv, fn($s) => $s['total_score'] >= 80));

        // 3. Terapkan filter pencarian nama/kode SPV (tanpa merusak nomor Rank asli!)
        $filtered = $allSpv;
        if (!empty($this->appliedSearch)) {
            $needle = mb_strtolower($this->appliedSearch);
            $filtered = array_values(array_filter($allSpv, function ($s) use ($needle) {
                return str_contains(mb_strtolower($s['name']), $needle)
                    || str_contains(mb_strtolower($s['code']), $needle)
                    || str_contains(mb_strtolower($s['branches_string']), $needle)
                    || str_contains(mb_strtolower($s['region']), $needle)
                    || str_contains(mb_strtolower($s['area']), $needle);
            }));
        }

        // 4. Terapkan urutan tampilan sesuai pilihan user
        if ($this->appliedSortBy === 'score_asc') {
            usort($filtered, fn($a, $b) => $a['total_score'] <=> $b['total_score']);
        } elseif ($this->appliedSortBy === 'name_asc') {
            usort($filtered, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        }

        return [
            'data' => $filtered,
            'stats' => [
                'total_spv' => $totalInScope,
                'filtered_spv' => count($filtered),
                'avg_score' => $avgScore,
                'pass_count' => $passCount,
                'top_spv' => $topSpv,
            ],
        ];
    }

    private function calculateNodeKPIs(array $raw): array
    {
        // 1. SO (35%)
        $so_ach = $raw['target'] > 0 ? ($raw['selling_out'] / $raw['target']) : 0;
        $so_score = min($so_ach * 35, 35);

        // 2. IPT (15%)
        $ipt_actual = $raw['nota'] > 0 ? ($raw['sku'] / $raw['nota']) : 0;
        $ipt_target = 8;
        $ipt_ach = $ipt_target > 0 ? ($ipt_actual / $ipt_target) : 0;
        $ipt_score = min($ipt_ach * 15, 15);

        // 3. JKS AC/PC (5%)
        $jks_ach = $raw['pc'] > 0 ? ($raw['ac'] / $raw['pc']) : 0;
        $jks_score = min($jks_ach * 5, 5);

        // 4. EC/AC (10%)
        $ec_ach = $raw['ac'] > 0 ? ($raw['ec'] / $raw['ac']) : 0;
        $ec_score = min($ec_ach * 10, 10);

        // 5. Standpro (10%)
        $standpro_target = $raw['se'] * 300;
        $standpro_ach = $standpro_target > 0 ? ($raw['ro'] / $standpro_target) : 0;
        $standpro_score = min($standpro_ach * 10, 10);

        // 6. AO (10%)
        $ao_ach = $raw['ro'] > 0 ? ($raw['ao'] / $raw['ro']) : 0;
        $ao_score = min($ao_ach * 10, 10);

        // 7. RWO (15%)
        $rwo_ach = $raw['potensi_rwo'] > 0 ? ($raw['capai_rwo'] / $raw['potensi_rwo']) : 0;
        $rwo_score = min($rwo_ach * 15, 15);

        $totalScore = $so_score + $ipt_score + $jks_score + $ec_score + $standpro_score + $ao_score + $rwo_score;

        return [
            'total_score' => $totalScore,
            'kpis' => [
                'so' => [
                    'label' => 'KPI 1 - SO (35%)',
                    'full_label' => 'Penjualan Bersih (Selling Out vs Target) • Bobot Maks 35%',
                    'bobot' => 35,
                    'actual' => $raw['selling_out'],
                    'target' => $raw['target'],
                    'ach' => $so_ach * 100,
                    'score' => $so_score,
                    'formatted_actual' => $this->formatRupiahShort($raw['selling_out']),
                    'formatted_target' => $this->formatRupiahShort($raw['target']),
                ],
                'ipt' => [
                    'label' => 'KPI 2 - IPT (15%)',
                    'full_label' => 'Item Per Transaksi (Total SKU / Nota vs Target 8 SKU) • Bobot Maks 15%',
                    'bobot' => 15,
                    'actual' => $ipt_actual,
                    'target' => $ipt_target,
                    'ach' => $ipt_ach * 100,
                    'score' => $ipt_score,
                    'formatted_actual' => number_format($ipt_actual, 1, ',', '.'),
                    'formatted_target' => '8 SKU',
                ],
                'jks' => [
                    'label' => 'KPI 3 - AC/PC (5%)',
                    'full_label' => 'Pemenuhan Kunjungan JKS SE (Actual Call / Plan Call) • Bobot Maks 5%',
                    'bobot' => 5,
                    'actual' => $raw['ac'],
                    'target' => $raw['pc'],
                    'ach' => $jks_ach * 100,
                    'score' => $jks_score,
                    'formatted_actual' => number_format($raw['ac'], 0, ',', '.'),
                    'formatted_target' => number_format($raw['pc'], 0, ',', '.'),
                ],
                'ec' => [
                    'label' => 'KPI 4 - EC (10%)',
                    'full_label' => 'Pencapaian Effective Call (Effective Call / Actual Call) • Bobot Maks 10%',
                    'bobot' => 10,
                    'actual' => $raw['ec'],
                    'target' => $raw['ac'],
                    'ach' => $ec_ach * 100,
                    'score' => $ec_score,
                    'formatted_actual' => number_format($raw['ec'], 0, ',', '.'),
                    'formatted_target' => number_format($raw['ac'], 0, ',', '.'),
                ],
                'standpro' => [
                    'label' => 'PI 5 - STANDPRO (10%)',
                    'full_label' => 'Standar Produktivitas RO (Registered Outlet vs Jumlah SE × 300) • Bobot Maks 10%',
                    'bobot' => 10,
                    'actual' => $raw['ro'],
                    'target' => $standpro_target,
                    'ach' => $standpro_ach * 100,
                    'score' => $standpro_score,
                    'formatted_actual' => number_format($raw['ro'], 0, ',', '.'),
                    'formatted_target' => number_format($standpro_target, 0, ',', '.'),
                ],
                'ao' => [
                    'label' => 'PI 6 - AO/RO (10%)',
                    'full_label' => 'Active Outlet (Active Outlet / Registered Outlet) • Bobot Maks 10%',
                    'bobot' => 10,
                    'actual' => $raw['ao'],
                    'target' => $raw['ro'],
                    'ach' => $ao_ach * 100,
                    'score' => $ao_score,
                    'formatted_actual' => number_format($raw['ao'], 0, ',', '.'),
                    'formatted_target' => number_format($raw['ro'], 0, ',', '.'),
                ],
                'rwo' => [
                    'label' => 'PI 7 - RWO (15%)',
                    'full_label' => 'Reward Outlet Capai Target (Capai RWO / Potensi RWO) • Bobot Maks 15%',
                    'bobot' => 15,
                    'actual' => $raw['capai_rwo'],
                    'target' => $raw['potensi_rwo'],
                    'ach' => $rwo_ach * 100,
                    'score' => $rwo_score,
                    'formatted_actual' => number_format($raw['capai_rwo'], 0, ',', '.'),
                    'formatted_target' => number_format($raw['potensi_rwo'], 0, ',', '.') . ' Toko',
                ],
            ]
        ];
    }

    protected function formatDisplayName(string $name, string $code = ''): string
    {
        $clean = trim($name);
        if (strcasecmp($clean, 'VACANT') === 0 && !empty($code)) {
            return 'Vacant (' . $code . ')';
        }
        return ucwords(mb_strtolower($clean));
    }

    protected function extractInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function formatRupiahShort(float $val): string
    {
        if ($val >= 1_000_000_000) {
            return 'Rp ' . number_format($val / 1_000_000_000, 1, ',', '.') . ' M';
        } elseif ($val >= 1_000_000) {
            return 'Rp ' . number_format($val / 1_000_000, 1, ',', '.') . ' Jt';
        } elseif ($val >= 1_000) {
            return 'Rp ' . number_format($val / 1_000, 1, ',', '.') . ' Rb';
        }
        return 'Rp ' . number_format($val, 0, ',', '.');
    }

    public function render()
    {
        $report = $this->buildRankingReport();

        return view('livewire.report.fundamental-sales.ranking', [
            'data' => $report['data'],
            'stats' => $report['stats'],
        ])->layout('layouts.app');
    }
}
