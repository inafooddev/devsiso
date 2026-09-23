<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MonitoringRewardDistributor extends Component
{
    use WithFileUploads;
    public $expandedDistributor = null;
    public $monthlyDetails = [];
    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';

    // Modals & Import
    public $isImportModalOpen = false;
    public $importMonth;
    public $importFile;

    public function toggleExpand($distributorCode)
    {
        if ($this->expandedDistributor === $distributorCode) {
            $this->expandedDistributor = null;
            $this->monthlyDetails = [];
            return;
        }

        $this->expandedDistributor = $distributorCode;
        $year = 2026; // Match render() year

        $this->monthlyDetails = $this->fetchMonthlyData($distributorCode, $year);
    }

    private function fetchMonthlyData($distributorCode, $year)
    {
        return Cache::remember("monthly_detail_{$distributorCode}_{$year}", 300, function () use ($distributorCode, $year) {
            $master = DB::table('master_distributors')->where('distributor_code', $distributorCode)->first();
            if (!$master) return [];
            $cabang = $master->branch_name;
            $details = [];
        
        // Target
        $targets = DB::table('target_per_depo')
            ->where('cabang', $cabang)->where('reg_fest', 'REG')->whereYear('bulan', $year)
            ->get(['bulan', 'target']);
        $targetMap = [];
        foreach ($targets as $t) {
            $m = (int) date('n', strtotime($t->bulan));
            $targetMap[$m] = ($targetMap[$m] ?? 0) + $t->target;
        }

        // Sell In
        $sellInMap = [];
        if ($distributorCode) {
            $sellIns = DB::table('selling_in')
                ->where('kd_distributor', $distributorCode)
                ->where('reg_fes', 'REG')
                ->where('tahun', $year)
                ->get(['bulan', 'value_net']);
            foreach ($sellIns as $t) {
                $m = (int) date('n', strtotime($t->bulan));
                $sellInMap[$m] = ($sellInMap[$m] ?? 0) + $t->value_net;
            }
        }

        // Sell Out
        $sellOutMap = [];
        if ($distributorCode) {
            $sellOuts = DB::table('t_sellingout')
                ->where('KDDIST', $distributorCode)
                ->where('REG_FEST', 'REG')
                ->whereRaw('CAST("THN" AS INTEGER) = ?', [$year])
                ->get(['BLN', 'NETT']);
            foreach ($sellOuts as $t) {
                $m = (int) $t->BLN;
                $sellOutMap[$m] = ($sellOutMap[$m] ?? 0) + $t->NETT;
            }
        }

        // AR & Stock
        $arStocks = DB::table('reward_dist_ar_stock')
            ->where('distributor_code', $distributorCode)->whereYear('bulan', $year)
            ->get(['bulan', 'ar', 'stock']);
        $stockMap = [];
        $arLateMap = [];
        $arValueMap = [];
        $stockCount = [];
        foreach ($arStocks as $t) {
            $m = (int) date('n', strtotime($t->bulan));
            $stockMap[$m] = ($stockMap[$m] ?? 0) + $t->stock;
            $stockCount[$m] = ($stockCount[$m] ?? 0) + 1;
            $arValueMap[$m] = $t->ar;
            if ($t->ar > 7) {
                $arLateMap[$m] = ($arLateMap[$m] ?? 0) + 1;
            }
        }
        foreach ($stockMap as $m => $val) {
            $stockMap[$m] = $stockCount[$m] > 0 ? $val / $stockCount[$m] : 0;
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        for ($i = 1; $i <= 6; $i++) {
            $m1 = $i;
            $m2 = $i + 6;
            
            $details[] = [
                'p1_month' => $monthNames[$m1],
                'p2_month' => $monthNames[$m2],
                'p1' => [
                    'target' => $targetMap[$m1] ?? 0,
                    'sell_in' => $sellInMap[$m1] ?? 0,
                    'sell_out' => $sellOutMap[$m1] ?? 0,
                    'stock_avg' => $stockMap[$m1] ?? 0,
                    'ar_value' => $arValueMap[$m1] ?? 0,
                    'ar_late_count' => $arLateMap[$m1] ?? 0,
                    'is_stock_achieved' => ($stockMap[$m1] ?? 0) >= 80,
                    'is_ar_achieved' => ($arLateMap[$m1] ?? 0) <= 2,
                ],
                'p2' => [
                    'target' => $targetMap[$m2] ?? 0,
                    'sell_in' => $sellInMap[$m2] ?? 0,
                    'sell_out' => $sellOutMap[$m2] ?? 0,
                    'stock_avg' => $stockMap[$m2] ?? 0,
                    'ar_value' => $arValueMap[$m2] ?? 0,
                    'ar_late_count' => $arLateMap[$m2] ?? 0,
                    'is_stock_achieved' => ($stockMap[$m2] ?? 0) >= 80,
                    'is_ar_achieved' => ($arLateMap[$m2] ?? 0) <= 2,
                ],
            ];
            }
            return $details;
        });
    }

    public function render()
    {
        $year = 2026; // Hardcode or get from request

        $user = auth()->user();
        $userId = $user ? $user->id : 'guest';
        
        $sortedData = Cache::remember("monitoring_reward_main_{$year}_uid_{$userId}", 300, function () use ($year, $user) {
            // Get all active distributors with Role Based Access Control
            $masterQuery = DB::table('master_distributors')
                ->where('is_active', true);

            if ($user && method_exists($user, 'hasRole') && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    // Supervisor: Join dengan team_elite_code_mappings berdasarkan siso_code
                    $masterQuery->join('team_elite_code_mappings as tecm', 'tecm.siso_code', '=', 'master_distributors.supervisor_code')
                                ->whereRaw("TRIM(tecm.team_elite_code) = TRIM(?)", [$user->supervisor_code])
                                ->select('master_distributors.*'); // Hindari ambiguous columns
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    // Area Manager
                    $masterQuery->whereIn('master_distributors.area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    // Region Manager
                    $masterQuery->whereIn('master_distributors.region_code', $user->region_code);
                } else {
                    // No access
                    $masterQuery->whereRaw('1 = 0');
                }
            }
            
            $activeDistributors = $masterQuery->get();
                
            // Get cabangs that have targets
            $cabangsWithTargets = DB::table('target_per_depo')
                ->where('reg_fest', 'REG')
                ->whereYear('bulan', $year)
                ->distinct()
                ->pluck('cabang')
                ->toArray();
                
            // Filter active distributors that belong to a branch with a target
            $validDistributors = collect($activeDistributors)->filter(function($dist) use ($cabangsWithTargets) {
                return in_array($dist->branch_name, $cabangsWithTargets);
            });
            
            $cabangs = $validDistributors->pluck('branch_name')->unique()->toArray();
            $distributorCodes = $validDistributors->pluck('distributor_code')->unique()->toArray();

            $startP1 = "{$year}-01-01";
            $endP1 = "{$year}-06-30";
            $startP2 = "{$year}-01-01";
            $endP2 = "{$year}-12-31";

            // --- BULK QUERIES P1 ---
            $targetP1Map = DB::table('target_per_depo')->selectRaw('cabang, sum(target) as total')->whereIn('cabang', $cabangs)->where('reg_fest', 'REG')->whereBetween('bulan', [$startP1, $endP1])->groupBy('cabang')->pluck('total', 'cabang');
            $sellInP1Map = DB::table('selling_in')->selectRaw('kd_distributor, sum(value_net) as total')->whereIn('kd_distributor', $distributorCodes)->where('reg_fes', 'REG')->whereBetween('bulan', [$startP1, $endP1])->groupBy('kd_distributor')->pluck('total', 'kd_distributor');
            $sellOutP1Map = DB::table('t_sellingout')->selectRaw('"KDDIST", sum("NETT") as total')->whereIn('KDDIST', $distributorCodes)->where('REG_FEST', 'REG')->whereRaw('CAST("THN" AS INTEGER) = ?', [$year])->whereRaw('CAST("BLN" AS INTEGER) BETWEEN 1 AND 6')->groupBy('KDDIST')->pluck('total', 'KDDIST');
            $stockP1Map = DB::table('reward_dist_ar_stock')->selectRaw('distributor_code, avg(stock) as average')->whereIn('distributor_code', $distributorCodes)->whereBetween('bulan', [$startP1, $endP1])->groupBy('distributor_code')->pluck('average', 'distributor_code');
            $arCountP1Map = DB::table('reward_dist_ar_stock')->selectRaw('distributor_code, count(*) as total')->whereIn('distributor_code', $distributorCodes)->whereBetween('bulan', [$startP1, $endP1])->where('ar', '>', 7)->groupBy('distributor_code')->pluck('total', 'distributor_code');

            // --- BULK QUERIES P2 ---
            $targetP2Map = DB::table('target_per_depo')->selectRaw('cabang, sum(target) as total')->whereIn('cabang', $cabangs)->where('reg_fest', 'REG')->whereBetween('bulan', [$startP2, $endP2])->groupBy('cabang')->pluck('total', 'cabang');
            $sellInP2Map = DB::table('selling_in')->selectRaw('kd_distributor, sum(value_net) as total')->whereIn('kd_distributor', $distributorCodes)->where('reg_fes', 'REG')->whereBetween('bulan', [$startP2, $endP2])->groupBy('kd_distributor')->pluck('total', 'kd_distributor');
            $sellOutP2Map = DB::table('t_sellingout')->selectRaw('"KDDIST", sum("NETT") as total')->whereIn('KDDIST', $distributorCodes)->where('REG_FEST', 'REG')->whereRaw('CAST("THN" AS INTEGER) = ?', [$year])->whereRaw('CAST("BLN" AS INTEGER) BETWEEN 1 AND 12')->groupBy('KDDIST')->pluck('total', 'KDDIST');
            $stockP2Map = DB::table('reward_dist_ar_stock')->selectRaw('distributor_code, avg(stock) as average')->whereIn('distributor_code', $distributorCodes)->whereBetween('bulan', [$startP2, $endP2])->groupBy('distributor_code')->pluck('average', 'distributor_code');
            $arCountP2Map = DB::table('reward_dist_ar_stock')->selectRaw('distributor_code, count(*) as total')->whereIn('distributor_code', $distributorCodes)->whereBetween('bulan', [$startP2, $endP2])->where('ar', '>', 7)->groupBy('distributor_code')->pluck('total', 'distributor_code');

            $data = [];

            foreach ($validDistributors as $master) {
                $cabang = $master->branch_name;
                $distCode = $master->distributor_code;
                
                $row = [
                    'distributor_code' => $distCode,
                    'cabang' => $cabang,
                    'distributor' => $master->distributor_name ?? '-',
                    'region' => $master->region_name ?? '-',
                    'area' => $master->area_name ?? '-'
                ];

            // --- PERIODE 1 (Jan - Jun) ---
            $targetP1 = $targetP1Map[$cabang] ?? 0;
            $sellInP1 = $sellInP1Map[$distCode] ?? 0;
            $sellOutP1 = $sellOutP1Map[$distCode] ?? 0;
            $stockP1 = $stockP1Map[$distCode] ?? 0;
            $arCountP1 = $arCountP1Map[$distCode] ?? 0;

            // Validations P1
            // Menghindari target 0 (prevent division by zero / logic issue)
            $isTargetAchievedP1 = $targetP1 > 0 && ($sellInP1 >= $targetP1) && ($sellOutP1 >= $targetP1);
            $isStockAchievedP1 = $stockP1 >= 80;
            $isArAchievedP1 = $arCountP1 <= 2;

            $baseRewardP1 = 0;
            if ($isTargetAchievedP1) {
                $baseRewardP1 = 0.005 * $sellInP1; // 0.5%
            }

            $finalRewardP1 = $baseRewardP1;
            if ($baseRewardP1 > 0 && (!$isStockAchievedP1 || !$isArAchievedP1)) {
                $finalRewardP1 = $baseRewardP1 * 0.25; // Penalty 25% if stock or ar failed
            }

            $row['p1'] = [
                'target' => $targetP1,
                'sell_in' => $sellInP1,
                'sell_out' => $sellOutP1,
                'stock_avg' => $stockP1,
                'ar_late_count' => $arCountP1,
                'is_target_achieved' => $isTargetAchievedP1,
                'is_stock_achieved' => $isStockAchievedP1,
                'is_ar_achieved' => $isArAchievedP1,
                'base_reward' => $baseRewardP1,
                'final_reward' => $finalRewardP1,
            ];

            // --- PERIODE 2 (Jul - Dec) ---
            $targetP2 = $targetP2Map[$cabang] ?? 0;
            $sellInP2 = $sellInP2Map[$distCode] ?? 0;
            $sellOutP2 = $sellOutP2Map[$distCode] ?? 0;
            $stockP2 = $stockP2Map[$distCode] ?? 0;
            $arCountP2 = $arCountP2Map[$distCode] ?? 0;

            // Validations P2
            $isTargetAchievedP2 = $targetP2 > 0 && ($sellInP2 >= $targetP2) && ($sellOutP2 >= $targetP2);
            $isStockAchievedP2 = $stockP2 >= 80;
            $isArAchievedP2 = $arCountP2 <= 2;

            $baseRewardP2 = 0;
            if ($isTargetAchievedP2) {
                $baseRewardP2 = 0.010 * $sellInP2; // 1%
            }

            $finalRewardP2 = $baseRewardP2;
            if ($baseRewardP2 > 0 && (!$isStockAchievedP2 || !$isArAchievedP2)) {
                $finalRewardP2 = $baseRewardP2 * 0.25; // Penalty
            }

            $row['p2'] = [
                'target' => $targetP2,
                'sell_in' => $sellInP2,
                'sell_out' => $sellOutP2,
                'stock_avg' => $stockP2,
                'ar_late_count' => $arCountP2,
                'is_target_achieved' => $isTargetAchievedP2,
                'is_stock_achieved' => $isStockAchievedP2,
                'is_ar_achieved' => $isArAchievedP2,
                // Reward properties omitted since they are not displayed
            ];

            $data[] = $row;
        }

            // Sort by Region -> Area -> Distributor Name
            return collect($data)->sortBy([
                ['region', 'asc'],
                ['area', 'asc'],
                ['distributor', 'asc']
            ])->values()->all();
        });

        // Filter and get options
        $result = $this->getFilteredData($sortedData);

        return view('livewire.monitoring-reward-distributor', [
            'data' => $result['data'],
            'year' => $year,
            'regions' => $result['regions'],
            'areas' => $result['areas'],
        ]);
    }

    private function getFilteredData($sortedData)
    {
        // Extract filter options from the FULL cached data
        $regions = collect($sortedData)->pluck('region')->unique()->filter(fn($val) => $val !== '-')->sort()->values()->all();

        // Terapkan Filter Region (First)
        if (!empty($this->filterRegion)) {
            $sortedData = collect($sortedData)->where('region', $this->filterRegion)->values()->all();
        }

        // Extract areas dependent on the selected Region (Chained)
        $areas = collect($sortedData)->pluck('area')->unique()->filter(fn($val) => $val !== '-')->sort()->values()->all();
        
        // Auto-reset Area jika Area yang sedang dipilih tidak ada di dalam list Area Region yang baru
        if (!empty($this->filterArea) && !in_array($this->filterArea, $areas)) {
            $this->filterArea = '';
        }

        // Terapkan Filter Area (Second)
        if (!empty($this->filterArea)) {
            $sortedData = collect($sortedData)->where('area', $this->filterArea)->values()->all();
        }

        // Terapkan fitur search dari data memory (sangat cepat)
        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $sortedData = collect($sortedData)->filter(function ($item) use ($searchTerm) {
                return str_contains(strtolower($item['cabang'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['distributor'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['region'] ?? ''), $searchTerm) ||
                       str_contains(strtolower($item['area'] ?? ''), $searchTerm);
            })->values()->all();
        }

        return [
            'data' => $sortedData,
            'regions' => $regions,
            'areas' => $areas,
        ];
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ArStockTemplateExport(),
            'Template_Import_AR_Stock.xlsx'
        );
    }

    public function openImportModal()
    {
        $this->importMonth = date('Y-m'); // Default bulan saat ini
        $this->importFile = null;
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->importFile = null;
    }

    public function processImport()
    {
        $this->validate([
            'importMonth' => 'required|date_format:Y-m',
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Maks 10MB
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\ArStockImport($this->importMonth),
                $this->importFile->getRealPath()
            );

            // Bersihkan SELURUH cache agar data utama & rincian (expanded row) langsung muncul
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            
            $this->closeImportModal();
            session()->flash('success', 'Data AR dan Stock berhasil diimport!');
            
            // Perintahkan browser untuk mereload halaman penuh
            $this->dispatch('page-reload');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $year = 2026;
        $sortedData = Cache::get("monitoring_reward_main_{$year}");
        if (empty($sortedData)) {
            // Rebuild if cache expired right before click
            $this->render(); // this populates cache
            $sortedData = Cache::get("monitoring_reward_main_{$year}", []);
        }

        $result = $this->getFilteredData($sortedData);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonitoringRewardExport($result['data'], $year),
            'Monitoring_Reward_Distributor_' . $year . '.xlsx'
        );
    }
}
