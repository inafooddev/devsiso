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
    public $detailDistributorCode = null;
    public $detailDistributorName = null;
    public $isDetailModalOpen = false;
    public $monthlyDetails = [];
    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';

    // Modals & Import
    public $isImportModalOpen = false;
    public $importMonth;
    public $importFile;

    // Filter Status Program
    public $filterStatus = 'ikut';

    // Settings Modal State
    public $settingDistributorCode = null;
    public $settingDistributorName = null;
    public $isSettingsModalOpen = false;
    public $isParticipating = true;
    public $selectedMonthsP1 = [];
    public $selectedMonthsP2 = [];

    public function mount()
    {
        // No global settings loaded on mount
    }

    public function openDetailModal($distributorCode, $distributorName)
    {
        $this->detailDistributorCode = $distributorCode;
        $this->detailDistributorName = $distributorName;
        $year = 2026; // Match render() year

        $this->monthlyDetails = $this->fetchMonthlyData($distributorCode, $year);
        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->monthlyDetails = [];
        $this->detailDistributorCode = null;
        $this->detailDistributorName = null;
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
                ->whereYear('bulan', $year)
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
            $arValueMap[$m] = max($arValueMap[$m] ?? 0, $t->ar);
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

        for ($i = 1; $i <= 12; $i++) {
            $details[] = [
                'month_name' => $monthNames[$i],
                'month_num' => $i,
                'target' => $targetMap[$i] ?? 0,
                'sell_in' => $sellInMap[$i] ?? 0,
                'sell_out' => $sellOutMap[$i] ?? 0,
                'stock_avg' => $stockMap[$i] ?? 0,
                'ar_value' => $arValueMap[$i] ?? 0,
                'ar_late_count' => $arLateMap[$i] ?? 0,
                'is_stock_achieved' => ($stockMap[$i] ?? 0) >= 80,
                'is_ar_achieved' => ($arLateMap[$i] ?? 0) <= 2,
            ];
        }
        return $details;
        });
    }

    public function saveSettings()
    {
        if (!auth()->user()->hasMenuAccess(request()->route()->getName(), 'can_edit')) {
            abort(403);
        }

        \App\Models\RewardDistributorSetting::updateOrCreate(
            ['distributor_code' => $this->settingDistributorCode],
            [
                'is_participating' => $this->isParticipating,
                'p1_months' => array_map('intval', $this->selectedMonthsP1),
                'p2_months' => array_map('intval', $this->selectedMonthsP2)
            ]
        );

        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $this->isSettingsModalOpen = false;
        
        $this->dispatch('page-reload');
    }

    public function openSettingsModal($distributorCode, $distributorName)
    {
        $this->settingDistributorCode = $distributorCode;
        $this->settingDistributorName = $distributorName;
        
        $setting = \App\Models\RewardDistributorSetting::where('distributor_code', $distributorCode)->first();
        $this->isParticipating = $setting ? (bool)$setting->is_participating : true;
        $this->selectedMonthsP1 = $setting && $setting->p1_months ? $setting->p1_months : [1,2,3,4,5,6];
        $this->selectedMonthsP2 = $setting && $setting->p2_months ? $setting->p2_months : [1,2,3,4,5,6,7,8,9,10,11,12];
        
        $this->isSettingsModalOpen = true;
    }

    public function closeSettingsModal()
    {
        $this->isSettingsModalOpen = false;
    }

    public function render()
    {
        $year = 2026; // Hardcode or get from request

        $user = auth()->user();
        $userId = $user ? $user->id : 'guest';
        
        // Settings are now per-distributor, so we don't pass global settings to the cache closure
        
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

            // Load all settings
            $distSettingsMap = \App\Models\RewardDistributorSetting::whereIn('distributor_code', $distributorCodes)->get()->keyBy('distributor_code');

            // We DO NOT filter by Status Program inside the cache closure anymore
            // so that the cached array has all distributors regardless of their participation status.
            $finalDistributors = collect($validDistributors);

            // Update array after filter
            $cabangs = $finalDistributors->pluck('branch_name')->unique()->toArray();
            $distributorCodes = $finalDistributors->pluck('distributor_code')->unique()->toArray();

            // --- BULK QUERIES 12 MONTHS ---
            // Target
            $targetRaw = DB::table('target_per_depo')->selectRaw('cabang, EXTRACT(MONTH FROM bulan) as m, sum(target) as total')->whereIn('cabang', $cabangs)->where('reg_fest', 'REG')->whereYear('bulan', $year)->groupBy('cabang', DB::raw('EXTRACT(MONTH FROM bulan)'))->get();
            $mTarget = []; foreach ($targetRaw as $r) { $mTarget[$r->cabang][$r->m] = $r->total; }
            
            // Sell In
            $sellInRaw = DB::table('selling_in')->selectRaw('kd_distributor, EXTRACT(MONTH FROM bulan) as m, sum(value_net) as total')->whereIn('kd_distributor', $distributorCodes)->where('reg_fes', 'REG')->whereYear('bulan', $year)->groupBy('kd_distributor', DB::raw('EXTRACT(MONTH FROM bulan)'))->get();
            $mSellIn = []; foreach ($sellInRaw as $r) { $mSellIn[$r->kd_distributor][$r->m] = $r->total; }

            // Sell Out
            $sellOutRaw = DB::table('t_sellingout')->selectRaw('"KDDIST", CAST("BLN" AS INTEGER) as m, sum("NETT") as total')->whereIn('KDDIST', $distributorCodes)->where('REG_FEST', 'REG')->whereRaw('CAST("THN" AS INTEGER) = ?', [$year])->groupBy('KDDIST', DB::raw('CAST("BLN" AS INTEGER)'))->get();
            $mSellOut = []; foreach ($sellOutRaw as $r) { $mSellOut[$r->KDDIST][$r->m] = $r->total; }

            // Stock & AR
            $arStockRaw = DB::table('reward_dist_ar_stock')->selectRaw('distributor_code, EXTRACT(MONTH FROM bulan) as m, stock, ar')->whereIn('distributor_code', $distributorCodes)->whereYear('bulan', $year)->get();
            $mStock = []; $mArCount = [];
            foreach ($arStockRaw as $r) {
                $mStock[$r->distributor_code][$r->m][] = $r->stock;
                if (!isset($mArCount[$r->distributor_code][$r->m])) $mArCount[$r->distributor_code][$r->m] = 0;
                if ($r->ar > 7) $mArCount[$r->distributor_code][$r->m]++;
            }

            $data = [];

            foreach ($finalDistributors as $master) {
                $cabang = $master->branch_name;
                $distCode = $master->distributor_code;
                
            // Get specific months for this distributor
            $distSettings = $distSettingsMap->get($distCode);
            $p1Months = $distSettings && $distSettings->p1_months ? $distSettings->p1_months : [1,2,3,4,5,6];
            $p2Months = $distSettings && $distSettings->p2_months ? $distSettings->p2_months : [1,2,3,4,5,6,7,8,9,10,11,12];
            $isParticipating = $distSettings ? (bool)$distSettings->is_participating : true;
            
            $row = [
                'distributor_code' => $distCode,
                'cabang' => $cabang,
                'distributor' => $master->distributor_name ?? '-',
                'region' => $master->region_name ?? '-',
                'area' => $master->area_name ?? '-',
                'is_participating' => $isParticipating
            ];

            // Aggregation helper
            $aggregate = function($months, $type) use ($cabang, $distCode, $mTarget, $mSellIn, $mSellOut, $mStock, $mArCount) {
                $target = 0; $sellIn = 0; $sellOut = 0; $stockSum = 0; $stockCount = 0; $arCount = 0;
                foreach ($months as $m) {
                    $target += $mTarget[$cabang][$m] ?? 0;
                    $sellIn += $mSellIn[$distCode][$m] ?? 0;
                    $sellOut += $mSellOut[$distCode][$m] ?? 0;
                    if (isset($mStock[$distCode][$m])) {
                        foreach ($mStock[$distCode][$m] as $stock) {
                            $stockSum += $stock;
                            $stockCount++;
                        }
                    }
                    $arCount += $mArCount[$distCode][$m] ?? 0;
                }
                return [
                    'target' => $target,
                    'sell_in' => $sellIn,
                    'sell_out' => $sellOut,
                    'stock' => $stockCount > 0 ? $stockSum / $stockCount : 0,
                    'ar_count' => $arCount
                ];
            };

            // --- PERIODE 1 ---
            $p1Agg = $aggregate($p1Months, 'p1');
            $targetP1 = $p1Agg['target'];
            $sellInP1 = $p1Agg['sell_in'];
            $sellOutP1 = $p1Agg['sell_out'];
            $stockP1 = $p1Agg['stock'];
            $arCountP1 = $p1Agg['ar_count'];

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

            // --- PERIODE 2 ---
            $p2Agg = $aggregate($p2Months, 'p2');
            $targetP2 = $p2Agg['target'];
            $sellInP2 = $p2Agg['sell_in'];
            $sellOutP2 = $p2Agg['sell_out'];
            $stockP2 = $p2Agg['stock'];
            $arCountP2 = $p2Agg['ar_count'];

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
                'base_reward' => $baseRewardP2,
                'final_reward' => $finalRewardP2,
            ];
            $row['total_reward'] = $finalRewardP1 + $finalRewardP2;

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

        // Extract areas dependent on the selected Region (Chained) - SEBELUM Filter Status
        $areas = collect($sortedData)->pluck('area')->unique()->filter(fn($val) => $val !== '-')->sort()->values()->all();
        
        // Auto-reset Area jika Area yang sedang dipilih tidak ada di dalam list Area Region yang baru
        if (!empty($this->filterArea) && !in_array($this->filterArea, $areas)) {
            $this->filterArea = '';
        }

        // Terapkan Filter Status Program
        if ($this->filterStatus === 'ikut') {
            $sortedData = collect($sortedData)->where('is_participating', true)->values()->all();
        } elseif ($this->filterStatus === 'tidak_ikut') {
            $sortedData = collect($sortedData)->where('is_participating', false)->values()->all();
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
        $user = auth()->user();
        $userId = $user ? $user->id : 'guest';
        $sortedData = Cache::get("monitoring_reward_main_{$year}_uid_{$userId}");
        if (empty($sortedData)) {
            // Rebuild if cache expired right before click
            $this->render(); // this populates cache
            $sortedData = Cache::get("monitoring_reward_main_{$year}_uid_{$userId}", []);
        }

        $result = $this->getFilteredData($sortedData);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonitoringRewardExport($result['data'], $year),
            'Monitoring_Reward_Distributor_' . $year . '.xlsx'
        );
    }
}
