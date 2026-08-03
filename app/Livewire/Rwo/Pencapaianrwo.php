<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use App\Models\RemarkListPotensiRwo;

class Pencapaianrwo extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';

    // Filters
    public $search = '';
    public $kuartal = '';
    public $region = '';
    public $area = '';
    public $supervisor = '';
    public $distributor = '';

    public $appliedKuartal = '';
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSupervisor = '';
    public $appliedDistributor = '';

    public $appliedStatusProgress = 'Semua';
    public $appliedStatusSkb = 'Semua';
    public $appliedStatusData = 'Semua';
    public $appliedStatusReward = 'Semua';

    // Status Filters
    public $statusProgress = 'Semua'; // Semua, Hijau, Kuning, Merah
    public $statusSkb = 'Semua';      // Semua, Sudah, Belum
    public $statusData = 'Semua';     // Semua, Lengkap, Belum
    public $statusReward = 'Semua';   // Semua, 2.5%, 2%, 1.5%

    public $sortField = 'gap';
    public $sortDirection = 'desc';

    // Select lists
    public $kuartals = [];
    public $regions = [];
    public $areas = [];
    public $supervisors = [];
    public $distributors = [];

    // Modals
    public $selectedStore = null;
    public $isDetailModalOpen = false;
    public $remarkKhusus = '';

    public function mount()
    {
        $user = auth()->user();
        
        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $this->regions = $regionQuery->get();

        $currentMonth = (int) date('n');
        $currentQuarter = (string) ceil($currentMonth / 3);
        $this->kuartal = $currentQuarter;
        $this->appliedKuartal = $currentQuarter;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->areas = empty($value) ? [] : DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name')
            ->get();
            
        $this->supervisors = [];
        $this->distributors = [];
        $this->resetPage();
    }

    public function updatedArea($value)
    {
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->supervisors = empty($value) ? [] : DB::table('master_distributors')
            ->where('area_code', $value)
            ->select('supervisor_code', 'supervisor_name')
            ->whereNotNull('supervisor_code')
            ->where('supervisor_code', '!=', '')
            ->distinct()
            ->orderBy('supervisor_name')
            ->get();
            
        $this->distributors = [];
        $this->resetPage();
    }

    public function updatedSupervisor($value)
    {
        $this->distributor = '';
        
        $this->distributors = empty($value) ? [] : DB::table('master_distributors')
            ->where('supervisor_code', $value)
            ->when($this->area, function ($q) {
                return $q->where('area_code', $this->area);
            })
            ->select('distributor_code', 'distributor_name')
            ->orderBy('distributor_name')
            ->get();
            
        $this->resetPage();
    }

    public function applyFilter()
    {
        $this->appliedKuartal = $this->kuartal;
        $this->appliedRegion = $this->region;
        $this->appliedArea = $this->area;
        $this->appliedSupervisor = $this->supervisor;
        $this->appliedDistributor = $this->distributor;
        $this->appliedStatusProgress = $this->statusProgress;
        $this->appliedStatusSkb = $this->statusSkb;
        $this->appliedStatusData = $this->statusData;
        $this->appliedStatusReward = $this->statusReward;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->search = '';
        $this->kuartal = (string) ceil((int)date('n') / 3);
        $this->region = '';
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        $this->statusProgress = 'Semua';
        $this->statusSkb = 'Semua';
        $this->statusData = 'Semua';
        $this->statusReward = 'Semua';
        $this->applyFilter();
    }

    public function showStoreDetail($customerCode, $distributorCode)
    {
        $this->selectedStore = $this->getStoreQuery()
            ->where('l.customer_code', $customerCode)
            ->where('l.distributor_code', $distributorCode)
            ->where('l.kuartal', $this->appliedKuartal)
            ->first();

        if ($this->selectedStore) {
            $this->isDetailModalOpen = true;

            $remarkData = RemarkListPotensiRwo::where('kuartal', $this->appliedKuartal)
                ->where('distributor_code', $distributorCode)
                ->where('customer_code', $customerCode)
                ->first();
            
            $this->remarkKhusus = $remarkData ? $remarkData->remark : '';
        }
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedStore = null;
        $this->remarkKhusus = '';
    }

    public function saveRemarkKhusus()
    {
        if ($this->selectedStore) {
            RemarkListPotensiRwo::updateOrCreate(
                [
                    'kuartal' => $this->appliedKuartal,
                    'distributor_code' => $this->selectedStore->distributor_code,
                    'customer_code' => $this->selectedStore->customer_code,
                ],
                [
                    'remark' => $this->remarkKhusus
                ]
            );
            
            session()->flash('success', 'Remark khusus berhasil disimpan!');
        }
    }

    private function applyAccessScope($query)
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $query->where(function($q) use ($user) {
                $q->where('te.team_elite_code', $user->supervisor_code)
                  ->orWhere('md.supervisor_code', $user->supervisor_code);
            });
        } elseif (!empty($user->area_code)) {
            $query->where('md.area_code', $user->area_code);
        } elseif (!empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('md.region_code', $regions);
            }
        }

        return $query;
    }

    private function getStoreQuery()
    {
        return DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoin('list_toko_pareto_team_elite as lt', 'lt.uniq_kd', '=', 'l.customer_code')
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist', 
                        'uniq_kd', 
                        DB::raw('EXTRACT(QUARTER FROM bulan) as kuartal'),
                        DB::raw('SUM(neto) as total_achievement'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                        DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value')
                    )
                    ->groupBy('kd_dist', 'uniq_kd', DB::raw('EXTRACT(QUARTER FROM bulan)')),
                'zv',
                function($join) {
                    $join->on('zv.kd_dist', '=', 'l.distributor_code')
                         ->on('zv.uniq_kd', '=', 'l.customer_code')
                         ->on('zv.kuartal', '=', 'l.kuartal');
                }
            )
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->select(
                        'kd_dist', 
                        'uniq_kd',
                        DB::raw('MAX(neto) as max_transaction'),
                        DB::raw('AVG(neto) as avg_transaction'),
                        DB::raw('SUM(neto) as total_transaction')
                    )
                    ->groupBy('kd_dist', 'uniq_kd'),
                'zv_stats',
                function($join) {
                    $join->on('zv_stats.kd_dist', '=', 'l.distributor_code')
                         ->on('zv_stats.uniq_kd', '=', 'l.customer_code');
                }
            )
            ->leftJoinSub(
                DB::table('zv_so_per_toko_2026')
                    ->selectRaw('DISTINCT ON (kd_dist, uniq_kd) kd_dist, uniq_kd, neto as last_transaction_value, bulan as last_transaction_date')
                    ->orderBy('kd_dist')
                    ->orderBy('uniq_kd')
                    ->orderBy('bulan', 'desc'),
                'zv_last',
                function($join) {
                    $join->on('zv_last.kd_dist', '=', 'l.distributor_code')
                         ->on('zv_last.uniq_kd', '=', 'l.customer_code');
                }
            )
            ->select(
                'l.*', 
                'lt.customer_code_prc as customer_prc',
                'md.region_name', 'md.area_name', 'md.supervisor_name', 'md.distributor_name',
                'l.alamat as address',
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3',
                'skb.is_approved', 'skb.foto_skb as skb_foto', 'skb.reason as skb_reason',
                DB::raw("CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END AS status_skb"),
                DB::raw("CASE WHEN 
                    NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                    NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                    NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                    THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap"),
                'zv.total_achievement',
                'zv.month_1_value',
                'zv.month_2_value',
                'zv.month_3_value',
                'zv_stats.max_transaction',
                'zv_stats.avg_transaction',
                'zv_stats.total_transaction',
                'zv_last.last_transaction_value',
                'zv_last.last_transaction_date'
            );
    }

    public function getProratedData($target, $row, $kuartalStr)
    {
        $currentMonth = (int)date('n'); // 1-12
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$kuartalStr;
        
        $m1 = (float)($row->month_1_value ?? 0);
        $m2 = (float)($row->month_2_value ?? 0);
        $m3 = (float)($row->month_3_value ?? 0);
        
        // Determine multiplier (1, 2, or 3 months)
        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1; // 1, 2, or 3
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1; // Future quarter, default to 1 month target comparison
        } else {
            $multiplier = 3; // Past quarter, full 3 months comparison
        }
        
        $proratedTarget = ($target / 3) * $multiplier;
        
        // Calculate achievement based on active months
        if ($multiplier === 1) {
            $activeAchievement = $m1;
        } elseif ($multiplier === 2) {
            $activeAchievement = $m1 + $m2;
        } else {
            $activeAchievement = $m1 + $m2 + $m3;
        }
        
        $percent = $proratedTarget > 0 ? ($activeAchievement / $proratedTarget) * 100 : 0;
        
        if ($percent >= 100) {
            $colorLabel = '1. HIJAU';
        } elseif ($percent >= 80) {
            $colorLabel = '2. KUNING';
        } else {
            $colorLabel = '3. MERAH';
        }
        
        return [
            'prorated_target' => $proratedTarget,
            'active_achievement' => $activeAchievement,
            'percent' => $percent,
            'color_label' => $colorLabel,
            'multiplier' => $multiplier
        ];
    }

    public function render()
    {
        $query = $this->getStoreQuery();
        $this->applyAccessScope($query);

        // Apply dynamic filters
        if ($this->appliedKuartal) {
            $query->where('l.kuartal', $this->appliedKuartal);
        }
        if ($this->appliedRegion) {
            $query->where('md.region_code', $this->appliedRegion);
        }
        if ($this->appliedArea) {
            $query->where('md.area_code', $this->appliedArea);
        }
        if ($this->appliedSupervisor) {
            $query->where('md.supervisor_code', $this->appliedSupervisor);
        }
        if ($this->appliedDistributor) {
            $query->where('l.distributor_code', $this->appliedDistributor);
        }

        if ($this->search) {
            $q = '%' . strtolower($this->search) . '%';
            $query->where(function($sub) use ($q) {
                $sub->whereRaw('LOWER(l.customer_name) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(l.customer_code) LIKE ?', [$q]);
            });
        }

        if ($this->appliedStatusSkb !== 'Semua') {
            if ($this->appliedStatusSkb === 'Sudah') {
                $query->whereNotNull('skb.customer_code');
            } elseif ($this->appliedStatusSkb === 'Belum') {
                $query->whereNull('skb.customer_code');
            } elseif ($this->appliedStatusSkb === 'Approve') {
                $query->whereNotNull('skb.customer_code')->where('skb.is_approved', true);
            } elseif ($this->appliedStatusSkb === 'Reject') {
                $query->whereNotNull('skb.customer_code')->where(function($q) {
                    $q->where('skb.is_approved', false)->orWhereNull('skb.is_approved');
                });
            }
        }

        if ($this->appliedStatusData !== 'Semua') {
            $fieldsCheck = [
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3'
            ];
            if ($this->appliedStatusData === 'Lengkap') {
                foreach ($fieldsCheck as $f) {
                    $query->whereNotNull($f)->where(DB::raw("TRIM($f)"), '!=', '');
                }
            } else {
                $query->where(function($sub) use ($fieldsCheck) {
                    foreach ($fieldsCheck as $f) {
                        $sub->orWhereNull($f)->orWhere(DB::raw("TRIM($f)"), '=', '');
                    }
                });
            }
        }

        if ($this->appliedStatusReward !== 'Semua') {
            if ($this->appliedStatusReward === '2.5%') {
                $query->where('l.total_target', '>=', 90000000);
            } elseif ($this->appliedStatusReward === '2%') {
                $query->where('l.total_target', '>=', 30000000)->where('l.total_target', '<', 90000000);
            } elseif ($this->appliedStatusReward === '1.5%') {
                $query->where(function($q) {
                    $q->whereNull('l.total_target')->orWhere('l.total_target', '<', 30000000);
                });
            }
        }

        // Prorated parameters
        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$this->appliedKuartal;
        
        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
        }
        
        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";
        $progressExpr = "($achievementSql / NULLIF($proratedTargetSql, 0)) * 100";

        if ($this->appliedStatusProgress !== 'Semua') {
            if ($this->appliedStatusProgress === '1. HIJAU') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 100");
            } elseif ($this->appliedStatusProgress === '2. KUNING') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100");
            } elseif ($this->appliedStatusProgress === '3. MERAH') {
                $query->whereRaw("COALESCE($progressExpr, 0) < 80");
            }
        }

        // KPI Card Stats (CLONE BEFORE PAGINATE!)
        $kpiQuery = clone $query;
        $kpiQuery->orders = null;

        // Sorting
        if ($this->sortField === 'gap') {
            $gapSql = "($proratedTargetSql - COALESCE($achievementSql, 0))";
            $query->orderByRaw("$gapSql {$this->sortDirection}");
        } else {
            // Default fallback
            $query->orderBy('l.customer_name', 'asc');
        }

        $records = $query->paginate(100);
        
        if ($multiplier === 1) {
            $kpiAchievementSql = "SUM(COALESCE(zv.month_1_value, 0))";
        } elseif ($multiplier === 2) {
            $kpiAchievementSql = "SUM(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $kpiAchievementSql = "SUM(COALESCE(zv.total_achievement, 0))";
        }

        $kpiQuery->select(
            DB::raw('COUNT(DISTINCT l.customer_code) as total_toko'),
            DB::raw("SUM((l.total_target / 3.0) * $multiplier) as total_target"),
            DB::raw("$kpiAchievementSql as total_achievement"),
            DB::raw("COUNT(DISTINCT CASE WHEN $achievementSql > 0 THEN l.customer_code END) as toko_transaksi"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 100 THEN l.customer_code END) as toko_hijau"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100 THEN l.customer_code END) as toko_kuning"),
            DB::raw("COUNT(DISTINCT CASE WHEN COALESCE($progressExpr, 0) < 80 THEN l.customer_code END) as toko_merah")
        );
        $kpi = $kpiQuery->first() ?? (object)[
            'total_toko' => 0, 
            'total_target' => 0, 
            'total_achievement' => 0,
            'toko_transaksi' => 0,
            'toko_hijau' => 0,
            'toko_kuning' => 0,
            'toko_merah' => 0
        ];

        // Month Labels
        $firstMonthOfSelectedQ = ($kuartal - 1) * 3 + 1;
        $monthLabels = [
            date('M', mktime(0, 0, 0, $firstMonthOfSelectedQ, 1)),
            date('M', mktime(0, 0, 0, $firstMonthOfSelectedQ + 1, 1)),
            date('M', mktime(0, 0, 0, $firstMonthOfSelectedQ + 2, 1)),
        ];
        
        // Active Quarter context
        $activeQuarterLabel = "Q{$kuartal} " . date('Y');
        if ($kuartal === $currentQuarter) {
             $activeQuarterLabel .= " • Bulan ke-{$multiplier} (" . date('M') . ")";
        }

        return view('livewire.rwo.pencapaianrwo', [
            'records' => $records,
            'kpi' => $kpi,
            'monthLabels' => $monthLabels,
            'activeQuarterLabel' => $activeQuarterLabel
        ])->layout('layouts.app');
    }

    public function export()
    {
        $query = $this->getStoreQuery();
        $this->applyAccessScope($query);

        if ($this->appliedKuartal) {
            $query->where('l.kuartal', $this->appliedKuartal);
        }
        if ($this->appliedRegion) {
            $query->where('md.region_code', $this->appliedRegion);
        }
        if ($this->appliedArea) {
            $query->where('md.area_code', $this->appliedArea);
        }
        if ($this->appliedSupervisor) {
            $query->where('md.supervisor_code', $this->appliedSupervisor);
        }
        if ($this->appliedDistributor) {
            $query->where('l.distributor_code', $this->appliedDistributor);
        }

        if ($this->search) {
            $q = '%' . strtolower($this->search) . '%';
            $query->where(function($sub) use ($q) {
                $sub->whereRaw('LOWER(l.customer_name) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(l.customer_code) LIKE ?', [$q]);
            });
        }

        if ($this->appliedStatusSkb !== 'Semua') {
            if ($this->appliedStatusSkb === 'Sudah') {
                $query->whereNotNull('skb.customer_code');
            } elseif ($this->appliedStatusSkb === 'Belum') {
                $query->whereNull('skb.customer_code');
            } elseif ($this->appliedStatusSkb === 'Approve') {
                $query->whereNotNull('skb.customer_code')->where('skb.is_approved', true);
            } elseif ($this->appliedStatusSkb === 'Reject') {
                $query->whereNotNull('skb.customer_code')->where(function($q) {
                    $q->where('skb.is_approved', false)->orWhereNull('skb.is_approved');
                });
            }
        }

        if ($this->appliedStatusData !== 'Semua') {
            $fieldsCheck = [
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3'
            ];
            if ($this->appliedStatusData === 'Lengkap') {
                foreach ($fieldsCheck as $f) {
                    $query->whereNotNull($f)->where(DB::raw("TRIM($f)"), '!=', '');
                }
            } else {
                $query->where(function($sub) use ($fieldsCheck) {
                    foreach ($fieldsCheck as $f) {
                        $sub->orWhereNull($f)->orWhere(DB::raw("TRIM($f)"), '=', '');
                    }
                });
            }
        }

        if ($this->appliedStatusReward !== 'Semua') {
            if ($this->appliedStatusReward === '2.5%') {
                $query->where('l.total_target', '>=', 90000000);
            } elseif ($this->appliedStatusReward === '2%') {
                $query->where('l.total_target', '>=', 30000000)->where('l.total_target', '<', 90000000);
            } elseif ($this->appliedStatusReward === '1.5%') {
                $query->where(function($q) {
                    $q->whereNull('l.total_target')->orWhere('l.total_target', '<', 30000000);
                });
            }
        }

        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$this->appliedKuartal;
        
        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
        }
        
        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";
        $progressExpr = "($achievementSql / NULLIF($proratedTargetSql, 0)) * 100";

        if ($this->appliedStatusProgress !== 'Semua') {
            if ($this->appliedStatusProgress === '1. HIJAU') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 100");
            } elseif ($this->appliedStatusProgress === '2. KUNING') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100");
            } elseif ($this->appliedStatusProgress === '3. MERAH') {
                $query->whereRaw("COALESCE($progressExpr, 0) < 80");
            }
        }

        $query->orderBy('md.region_name', 'asc')
              ->orderBy('md.area_name', 'asc')
              ->orderBy('md.supervisor_name', 'asc')
              ->orderBy('l.distributor_code', 'asc');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PencapaianRwoExport($query, $this->appliedKuartal),
            'Pencapaian_RWO_Q' . $this->appliedKuartal . '_' . date('Ymd_His') . '.xlsx'
        );
    }
}
