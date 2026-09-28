<?php

namespace App\Livewire\Report\FundamentalSales;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithFileUploads;

class Index extends Component
{
    use EnforcesMenuPermissions;
    use WithFileUploads;

    #[Title('Individual Performance Management')]
    protected string $menuRoute = 'report.fundamental-sales.index';

    // Filters
    public $selectedYear;
    public $selectedRegion = '';
    public $selectedArea = '';

    public $regions = [];
    public $areas = [];

    public $appliedYear;
    public $appliedRegion = '';
    public $appliedArea = '';
    
    public $search = '';
    public $appliedSearch = '';

    // Export/Import Raw Data
    public $exportImportMonth;
    public $importFile;

    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->appliedYear = $this->selectedYear;
        $this->exportImportMonth = date('Y-m'); // Default current month
        $this->loadRegions();
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
            $this->updatedSelectedRegion($this->selectedRegion);
        }
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->areas = [];

        if ($value) {
            $user = auth()->user();
            $accessLevel = $user->getAccessLevel();

            $query = DB::table('team_elite_code_mappings as t')
                ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
                ->where('t.region_code', $value)
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

    public function applyFilter()
    {
        $this->appliedYear = $this->selectedYear;
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedSearch = $this->search;
    }

    #[Computed]
    public function reportData()
    {
        if (empty($this->appliedYear)) {
            return [];
        }

        $query = DB::table('ipm_fundamental_sales as ifs')
            ->leftJoin('master_branches as mb', 'mb.branch_name', '=', 'ifs.cabang')
            ->leftJoin('master_supervisors as ms', 'ms.supervisor_code', '=', 'mb.supervisor_code')
            ->leftJoin('master_areas as ma', 'ma.area_code', '=', 'ms.area_code')
            ->leftJoin('master_regions as mr', 'mr.region_code', '=', 'ma.region_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'ms.supervisor_code')
            ->whereYear('ifs.bulan', $this->appliedYear)
            ->whereNotNull('te.team_elite_code');

        if (!empty($this->appliedRegion)) {
            $query->where('mr.region_code', $this->appliedRegion);
        }
        if (!empty($this->appliedArea)) {
            $query->where('ma.area_code', $this->appliedArea);
        }
        if (!empty($this->appliedSearch)) {
            $query->where(function($q) {
                $q->where('ms.description', 'ilike', '%' . $this->appliedSearch . '%')
                  ->orWhere('te.team_elite_code', 'ilike', '%' . $this->appliedSearch . '%');
            });
        }

        // Apply access level restrictions if region/area is not selected
        $user = auth()->user();
        $accessLevel = $user->getAccessLevel();
        if ($accessLevel === 'region' && empty($this->appliedRegion)) {
            $query->whereIn('mr.region_code', (array) $user->region_code);
        } elseif ($accessLevel === 'area' && empty($this->appliedArea)) {
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

        $processedData = [];
        $emptyRaw = [
            'target' => 0, 'selling_out' => 0, 'pc' => 0, 'ac' => 0, 'ec' => 0,
            'se' => 0, 'ro' => 0, 'ao' => 0, 'sku' => 0, 'nota' => 0,
            'potensi_rwo' => 0, 'capai_rwo' => 0,
        ];

        foreach ($rawData as $row) {
            $spvCode = $row->supervisor_code;
            $spvName = $row->supervisor_name ?: $spvCode;
            $cabangName = $row->cabang_name ?: 'Unknown';
            $m = (int)$row->month_num;

            if (!isset($processedData[$spvCode])) {
                $processedData[$spvCode] = [
                    'code' => $spvCode,
                    'name' => $spvName,
                    'region' => $row->region_name ?: 'Unknown',
                    'raw' => array_fill(1, 12, $emptyRaw),
                    'months' => [],
                    'children' => [],
                    'branches_string' => ''
                ];
            }

            if (!isset($processedData[$spvCode]['children'][$cabangName])) {
                $processedData[$spvCode]['children'][$cabangName] = [
                    'name' => $cabangName,
                    'raw' => array_fill(1, 12, $emptyRaw),
                    'months' => []
                ];
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

            // Add to Cabang and Supervisor
            foreach ($rawValues as $k => $v) {
                $processedData[$spvCode]['children'][$cabangName]['raw'][$m][$k] += $v;
                $processedData[$spvCode]['raw'][$m][$k] += $v;
            }
        }

        // Calculate KPIs and branch strings
        foreach ($processedData as &$spv) {
            $spv['branches_string'] = implode(', ', array_keys($spv['children'] ?? []));
        }

        $this->calculateKPIsForNode($processedData);

        return $processedData;
    }

    private function calculateKPIsForNode(&$nodes)
    {
        foreach ($nodes as &$node) {
            if (isset($node['children'])) {
                $this->calculateKPIsForNode($node['children']);
            }

            for ($m = 1; $m <= 12; $m++) {
                $raw = $node['raw'][$m];

                // 1. SO
                $so_ach = $raw['target'] > 0 ? ($raw['selling_out'] / $raw['target']) : 0;
                $so_score = min($so_ach * 35, 35);

                // 2. JKS
                $jks_ach = $raw['pc'] > 0 ? ($raw['ac'] / $raw['pc']) : 0;
                $jks_score = min($jks_ach * 5, 5);

                // 3. EC
                $ec_ach = $raw['ac'] > 0 ? ($raw['ec'] / $raw['ac']) : 0;
                $ec_score = min($ec_ach * 10, 10);

                // 4. Standpro
                $standpro_target = $raw['se'] * 300;
                $standpro_ach = $standpro_target > 0 ? ($raw['ro'] / $standpro_target) : 0;
                $standpro_score = min($standpro_ach * 10, 10);

                // 5. AO
                $ao_ach = $raw['ro'] > 0 ? ($raw['ao'] / $raw['ro']) : 0;
                $ao_score = min($ao_ach * 10, 10);

                // 6. IPT
                $ipt_actual = $raw['nota'] > 0 ? ($raw['sku'] / $raw['nota']) : 0;
                $ipt_target = 8;
                $ipt_ach = $ipt_target > 0 ? ($ipt_actual / $ipt_target) : 0;
                $ipt_score = min($ipt_ach * 15, 15);

                // 7. RWO
                $rwo_ach = $raw['potensi_rwo'] > 0 ? ($raw['capai_rwo'] / $raw['potensi_rwo']) : 0;
                $rwo_score = min($rwo_ach * 15, 15);

                $node['months'][$m] = [
                    'so' => ['target' => $raw['target'], 'actual' => $raw['selling_out'], 'ach' => $so_ach * 100, 'score' => $so_score],
                    'jks' => ['target' => $raw['pc'], 'actual' => $raw['ac'], 'ach' => $jks_ach * 100, 'score' => $jks_score],
                    'ec' => ['target' => $raw['ac'], 'actual' => $raw['ec'], 'ach' => $ec_ach * 100, 'score' => $ec_score],
                    'standpro' => ['target' => $standpro_target, 'actual' => $raw['ro'], 'ach' => $standpro_ach * 100, 'score' => $standpro_score],
                    'ao' => ['target' => $raw['ro'], 'actual' => $raw['ao'], 'ach' => $ao_ach * 100, 'score' => $ao_score],
                    'ipt' => ['target' => $ipt_target, 'actual' => $ipt_actual, 'ach' => $ipt_ach * 100, 'score' => $ipt_score],
                    'rwo' => ['target' => $raw['potensi_rwo'], 'actual' => $raw['capai_rwo'], 'ach' => $rwo_ach * 100, 'score' => $rwo_score],
                ];
            }
        }
    }

    private function getEmptyKPIs()
    {
        $empty = ['target' => 0, 'actual' => 0, 'ach' => 0, 'score' => 0];
        return [
            'so' => $empty,
            'jks' => $empty,
            'ec' => $empty,
            'standpro' => $empty,
            'ao' => $empty,
            'ipt' => $empty,
            'rwo' => $empty,
        ];
    }

    public function exportRawData()
    {
        if (empty($this->exportImportMonth)) {
            $this->addError('exportImportMonth', 'Bulan wajib dipilih untuk export.');
            return;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Data\FundamentalSalesRawExport($this->exportImportMonth),
            'Fundamental_Sales_Raw_' . $this->exportImportMonth . '.xlsx'
        );
    }

    public function importRawData()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls'
        ], [
            'importFile.required' => 'File Excel wajib diunggah.',
            'importFile.mimes' => 'Format file harus .xlsx atau .xls'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\Data\FundamentalSalesRawImport,
                $this->importFile
            );

            $this->reset('importFile');
            $this->dispatch('close-modal', id: 'export_import_modal');
            $this->dispatch('toast', type: 'success', message: 'Data berhasil diimport.');
            
            // Reload data
            $this->applyFilter();
        } catch (\Exception $e) {
            $this->addError('importFile', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.report.fundamental-sales.index')->layout('layouts.app');
    }
}
