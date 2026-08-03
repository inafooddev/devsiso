<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use App\Traits\RwoQueryBuilder;
use App\Models\RemarkListPotensiRwo;

class Pencapaianrwo extends Component
{
    use WithPagination, EnforcesMenuPermissions, RwoQueryBuilder;

    protected string $menuRoute = 'rwo.listpotensirwo';

    // Filters
    public $search = '';
    // Applied Filters (used for actual queries)
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
    public $statusProgress = 'Semua';
    public $statusSkb = 'Semua';
    public $statusData = 'Semua';
    public $statusReward = 'Semua';

    protected $listeners = ['apply-rwo-filter' => 'applyModalFilter'];

    public $sortField = 'wilayah';
    public $sortDirection = 'desc';

    // Modals
    public $selectedCustomerCode = null;
    public $selectedDistributorCode = null;
    public $isDetailModalOpen = false;
    public $remarkKhusus = '';

    public function getSelectedStoreProperty()
    {
        if (!$this->selectedCustomerCode || !$this->selectedDistributorCode) {
            return null;
        }

        return $this->getStoreQuery()
            ->where('l.customer_code', $this->selectedCustomerCode)
            ->where('l.distributor_code', $this->selectedDistributorCode)
            ->where('l.kuartal', $this->appliedKuartal)
            ->first();
    }

    public function mount()
    {
        $currentMonth = (int) date('n');
        $currentQuarter = (string) ceil($currentMonth / 3);
        $this->appliedKuartal = $currentQuarter;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSortField()
    {
        $this->resetPage();
    }

    #[Livewire\Attributes\On('apply-rwo-filter')]
    public function applyModalFilter($filters)
    {
        $this->appliedKuartal = $filters['kuartal'] ?? '';
        $this->appliedRegion = $filters['region'] ?? '';
        $this->appliedArea = $filters['area'] ?? '';
        $this->appliedSupervisor = $filters['supervisor'] ?? '';
        $this->appliedDistributor = $filters['distributor'] ?? '';
        
        $this->resetPage();
    }

    public function updatedStatusProgress()
    {
        $this->appliedStatusProgress = $this->statusProgress;
        $this->resetPage();
    }

    public function updatedStatusSkb()
    {
        $this->appliedStatusSkb = $this->statusSkb;
        $this->resetPage();
    }

    public function updatedStatusData()
    {
        $this->appliedStatusData = $this->statusData;
        $this->resetPage();
    }

    public function updatedStatusReward()
    {
        $this->appliedStatusReward = $this->statusReward;
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->search = '';
        $currentMonth = (int) date('n');
        $this->appliedKuartal = (string) ceil($currentMonth / 3);
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedSupervisor = '';
        $this->appliedDistributor = '';
        
        $this->statusProgress = 'Semua';
        $this->statusSkb = 'Semua';
        $this->statusData = 'Semua';
        $this->statusReward = 'Semua';

        $this->appliedStatusProgress = 'Semua';
        $this->appliedStatusSkb = 'Semua';
        $this->appliedStatusData = 'Semua';
        $this->appliedStatusReward = 'Semua';
        
        $this->resetPage();
        
        // Broadcast to the filter component so it resets its UI as well
        $this->dispatch('rwo-filter-reset');
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


    public function showStoreDetail($customerCode, $distributorCode)
    {
        $this->selectedCustomerCode = $customerCode;
        $this->selectedDistributorCode = $distributorCode;

        if ($this->selectedStore) {
            $this->isDetailModalOpen = true;

            $remarkData = RemarkListPotensiRwo::where('kuartal', (string)$this->appliedKuartal)
                ->where('distributor_code', $distributorCode)
                ->where('customer_code', $customerCode)
                ->first();
            
            $this->remarkKhusus = $remarkData ? $remarkData->remark : '';
        }
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedCustomerCode = null;
        $this->selectedDistributorCode = null;
        $this->remarkKhusus = '';
    }

    public function saveRemarkKhusus()
    {
        if ($this->selectedCustomerCode && $this->selectedDistributorCode) {
            RemarkListPotensiRwo::updateOrCreate(
                [
                    'kuartal' => (string)$this->appliedKuartal,
                    'distributor_code' => $this->selectedDistributorCode,
                    'customer_code' => $this->selectedCustomerCode,
                ],
                [
                    'remark' => $this->remarkKhusus
                ]
            );
            
            session()->flash('success', 'Remark khusus berhasil disimpan!');
        }
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
        } elseif ($this->sortField === 'wilayah') {
            $query->orderBy('md.region_name', 'asc')
                  ->orderBy('md.area_name', 'asc')
                  ->orderBy('md.supervisor_name', 'asc')
                  ->orderBy('md.distributor_name', 'asc')
                  ->orderBy('l.customer_name', 'asc');
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

    public function loadMapData()
    {
        $filters = [
            'appliedKuartal' => $this->appliedKuartal,
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedSupervisor' => $this->appliedSupervisor,
            'appliedDistributor' => $this->appliedDistributor,
            'search' => $this->search,
            'appliedStatusSkb' => $this->appliedStatusSkb,
            'appliedStatusData' => $this->appliedStatusData,
            'appliedStatusReward' => $this->appliedStatusReward,
            'appliedStatusProgress' => $this->appliedStatusProgress,
        ];
        
        $this->dispatch('load-map-data', filters: $filters)->to(PencapaianrwoMap::class);
}
}
