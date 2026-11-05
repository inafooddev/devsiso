<?php

namespace App\Livewire\CallPlan\JksSalesmans;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Traits\WithCallPlanFilters;
use App\Services\JksSalesmanService;

class Index extends Component
{
    use WithPagination;
    use WithCallPlanFilters {
        applyFilters as traitApplyFilters;
    }

    #[Title('JKS Salesman')]
    #[Layout('layouts.app')]

    protected $listeners = ['refreshTable' => '$refresh'];

    public $search = '';
    public $headerSalesman = '';
    public $selectedHari = 'h1';
    public $selectedMinggu = 'ganjil';

    // Visit Realisasi & Detail Modal State
    public $showVisitDetailModal = false;
    public $visitModalHeader = null;
    public $visitModalSummary = null;
    public $visitModalHistory = [];

    public $canAdd = false;
    public $canEdit = false;
    public $canDelete = false;
    public $canExport = false;
    public $canImport = false;

    #[Computed]
    public function isCurrentRunningMonth(): bool
    {
        if (empty($this->appliedBulan)) {
            return false;
        }
        return substr($this->appliedBulan, 0, 7) === now()->format('Y-m');
    }

    #[Computed]
    public function canManipulate(): bool
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return true;
        }
        return $this->isCurrentRunningMonth;
    }

    public function checkCanManipulate(bool $abort = false): bool
    {
        if (!$this->canManipulate) {
            $currentMonthName = now()->translatedFormat('F Y');
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Akses Ditolak: Manipulasi data hanya diizinkan untuk periode bulan berjalan (' . $currentMonthName . ').'
            ]);
            if ($abort) {
                abort(403, 'Akses Ditolak: Manipulasi data hanya diizinkan untuk periode bulan berjalan (' . $currentMonthName . ').');
            }
            return false;
        }
        return true;
    }

    public function mount()
    {
        if (session()->has('jks_salesmans_state')) {
            $state = session()->get('jks_salesmans_state');
            $this->appliedRegion = $state['appliedRegion'] ?? '';
            $this->appliedArea = $state['appliedArea'] ?? '';
            $this->appliedSupervisor = $state['appliedSupervisor'] ?? '';
            $this->appliedDistributor = $state['appliedDistributor'] ?? '';
            $this->appliedBulan = $state['appliedBulan'] ?? date('Y-m-01');
            
            $this->selectedRegion = $state['selectedRegion'] ?? '';
            $this->selectedArea = $state['selectedArea'] ?? '';
            $this->selectedSupervisor = $state['selectedSupervisor'] ?? '';
            $this->selectedDistributor = $state['selectedDistributor'] ?? '';
            $this->selectedBulan = $state['selectedBulan'] ?? date('Y-m-01');

            $this->search = $state['search'] ?? '';
            $this->headerSalesman = $state['headerSalesman'] ?? '';
            $this->selectedHari = $state['selectedHari'] ?? 'h1';
            $this->selectedMinggu = $state['selectedMinggu'] ?? 'ganjil';
            
            if (isset($state['page'])) {
                $this->setPage($state['page']);
            }
        }
        $this->applyDefaultFiltersForRestrictedUser();

        $user = auth()->user();
        if ($user) {
            $routeName = 'call-plan.jks-salesmans';
            $this->canAdd = $user->hasMenuAccess($routeName, 'can_add');
            $this->canEdit = $user->hasMenuAccess($routeName, 'can_edit');
            $this->canDelete = $user->hasMenuAccess($routeName, 'can_delete');
            $this->canExport = $user->hasMenuAccess($routeName, 'can_export');
            $this->canImport = $user->hasMenuAccess($routeName, 'can_import');
        }
    }

    protected function applyDefaultFiltersForRestrictedUser()
    {
        $user = auth()->user();
        if ($user && !$user->hasRole(['admin', 'spm'])) {
            $accessLevel = $user->getAccessLevel();
            
            if (in_array($accessLevel, ['region', 'area', 'supervisor'])) {
                if (!empty($user->region_code) && count((array)$user->region_code) == 1 && empty($this->selectedRegion)) {
                    $this->selectedRegion = ((array)$user->region_code)[0];
                    $this->appliedRegion = $this->selectedRegion;
                }
            }
            if (in_array($accessLevel, ['area', 'supervisor'])) {
                if (!empty($user->area_code) && count((array)$user->area_code) == 1 && empty($this->selectedArea)) {
                    $this->selectedArea = ((array)$user->area_code)[0];
                    $this->appliedArea = $this->selectedArea;
                    
                    if ($accessLevel === 'area' && empty($this->selectedRegion)) {
                        $areaData = \Illuminate\Support\Facades\DB::table('master_distributors')
                            ->where('area_code', $this->selectedArea)
                            ->select('region_code')
                            ->first();
                        if ($areaData) {
                            $this->selectedRegion = $areaData->region_code;
                            $this->appliedRegion = $this->selectedRegion;
                        }
                    }
                }
            }
            if ($accessLevel === 'supervisor') {
                if (!empty($user->supervisor_code) && empty($this->selectedSupervisor)) {
                    $this->selectedSupervisor = $user->supervisor_code;
                    $this->appliedSupervisor = $this->selectedSupervisor;
                    
                    $spvData = \Illuminate\Support\Facades\DB::table('master_distributors as md')
                        ->join('team_elite_code_mappings as te', 'md.supervisor_code', '=', 'te.siso_code')
                        ->where('te.team_elite_code', $user->supervisor_code)
                        ->select('md.region_code', 'md.area_code')
                        ->first();
                        
                    if ($spvData) {
                        if (empty($this->selectedRegion)) {
                            $this->selectedRegion = $spvData->region_code;
                            $this->appliedRegion = $this->selectedRegion;
                        }
                        if (empty($this->selectedArea)) {
                            $this->selectedArea = $spvData->area_code;
                            $this->appliedArea = $this->selectedArea;
                        }
                    }
                }
            }
        }
    }

    public function dehydrate()
    {
        session()->put('jks_salesmans_state', [
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedSupervisor' => $this->appliedSupervisor,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedBulan' => $this->appliedBulan,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedSupervisor' => $this->selectedSupervisor,
            'selectedDistributor' => $this->selectedDistributor,
            'selectedBulan' => $this->selectedBulan,
            'search' => $this->search,
            'headerSalesman' => $this->headerSalesman,
            'selectedHari' => $this->selectedHari,
            'selectedMinggu' => $this->selectedMinggu,
            'page' => $this->paginators['page'] ?? $this->getPage(),
        ]);
    }

    // Collision Detection
    public $collisionCustomerName = '';
    public $collisionDetails = [];
    public $showCollisionModal = false;
    public $onlyDobelCover = false;

    public function toggleDobelCover()
    {
        $this->onlyDobelCover = !$this->onlyDobelCover;
        if ($this->onlyDobelCover) {
            $this->selectedHari = '';
            $this->selectedMinggu = '';
        } else {
            $this->selectedHari = 'h1';
            $this->selectedMinggu = 'ganjil';
        }
        $this->resetPage();
    }

    public function toggleHari($hari)
    {
        $this->onlyDobelCover = false;
        if ($this->selectedHari === $hari) {
            $this->selectedHari = '';
        } else {
            $this->selectedHari = $hari;

            if ($hari === 'non_rute') {
                // Non Rute: reset filter minggu karena tidak relevan
                $this->selectedMinggu = '';
            } else {
                // Jika memilih hari spesifik dan minggu belum dipilih, default ke ganjil
                if (empty($this->selectedMinggu)) {
                    $this->selectedMinggu = 'ganjil';
                }
            }
        }
        $this->resetPage();
    }

    public function toggleMinggu($minggu)
    {
        $this->onlyDobelCover = false;
        if ($this->selectedMinggu === $minggu) {
            $this->selectedMinggu = '';
        } else {
            $this->selectedMinggu = $minggu;

            // Jika minggu dipilih dan hari sedang di non_rute atau kosong, default ke Senin
            if (empty($this->selectedHari) || $this->selectedHari === 'non_rute') {
                $this->selectedHari = 'h1';
            }
        }
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingHeaderSalesman()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        // Panggil trait method untuk reset global filters
        $this->reset([
            'selectedRegion', 'selectedArea', 'selectedSupervisor', 'selectedDistributor',
            'appliedRegion', 'appliedArea', 'appliedSupervisor', 'appliedDistributor'
        ]);
        
        // Reset bulan ke default
        $this->selectedBulan = date('Y-m-01');
        $this->appliedBulan = date('Y-m-01');
        
        // Reset internal JksSalesmans state
        $this->reset(['search', 'headerSalesman', 'selectedHari', 'selectedMinggu', 'onlyDobelCover']);
        
        $this->selectedHari = 'h1';
        $this->selectedMinggu = 'ganjil';

        $this->resetBdSelection();

        if (session()->has('jks_salesmans_state')) {
            session()->forget('jks_salesmans_state');
        }

        $this->applyDefaultFiltersForRestrictedUser();

        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->traitApplyFilters();
        $this->resetBdSelection();
    }

    protected function applyHierarchyAccess($query)
    {
        $user = auth()->user();
        if (!$user) return $query;
        
        if ($user->hasRole(['admin', 'spm'])) {
            return $query;
        }
        
        $accessLevel = $user->getAccessLevel();
        
        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            // Map the team_elite_code (from users table) to siso_code (used in master_distributors)
            $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
                           ->where('team_elite_code', $user->supervisor_code)
                           ->pluck('siso_code');
            return $query->whereIn('md.supervisor_code', $sisoCodes);
        }
        
        if ($accessLevel === 'area' && !empty($user->area_code) && count((array) $user->area_code) > 0) {
            return $query->whereIn('md.area_code', (array) $user->area_code);
        }
        
        if ($accessLevel === 'region' && !empty($user->region_code) && count((array) $user->region_code) > 0) {
            return $query->whereIn('md.region_code', (array) $user->region_code);
        }

        return $query;
    }

    protected function getAllowedDistributorCodes()
    {
        $user = auth()->user();
        if (!$user || $user->hasRole(['admin', 'spm'])) {
            return true;
        }
        
        $query = \Illuminate\Support\Facades\DB::table('master_distributors as md')->select('md.distributor_code');
        $query = $this->applyHierarchyAccess($query);
        return $query->pluck('distributor_code')->toArray();
    }

    protected function abortIfUnauthorizedDistributor($distributorCode)
    {
        $allowed = $this->getAllowedDistributorCodes();
        if ($allowed === true) return;
        
        if (!in_array($distributorCode, $allowed)) {
            abort(403, 'Akses ditolak. Wilayah ini di luar kewenangan Anda.');
        }
    }

    protected function abortIfUnauthorizedJksIds($ids)
    {
        $allowed = $this->getAllowedDistributorCodes();
        if ($allowed === true) return;
        
        $unauthorized = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->whereIn('id', (array) $ids)
            ->whereNotIn('distributor_code', $allowed)
            ->exists();
            
        if ($unauthorized) {
            abort(403, 'Akses ditolak. Sebagian data berada di luar kewenangan Anda.');
        }
    }

    #[\Livewire\Attributes\Computed]
    public function filterRegions()
    {
        $query = \Illuminate\Support\Facades\DB::table('master_regions as mr')
            ->select('mr.region_code', 'mr.region_name')
            ->whereExists(function($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('master_distributors as md')
                  ->whereColumn('md.region_code', 'mr.region_code')
                  ->where('md.is_active', true);
                $this->applyHierarchyAccess($q);
            });
            
        return $query->orderBy('mr.region_name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function filterAreas()
    {
        $query = \Illuminate\Support\Facades\DB::table('master_areas as ma')
            ->select('ma.area_code', 'ma.area_name');
            
        if ($this->selectedRegion) {
            $query->where('ma.region_code', $this->selectedRegion);
        }
        
        $query->whereExists(function($q) {
            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('master_distributors as md')
                  ->whereColumn('md.area_code', 'ma.area_code')
                  ->where('md.is_active', true);
            $this->applyHierarchyAccess($q);
        });
            
        return $query->orderBy('ma.area_name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function filterSupervisors()
    {
        $query = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings as te')
            ->join('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->select('te.team_elite_code as supervisor_code', 'f.SLSNAME as description')
            ->whereExists(function($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('master_distributors as md')
                  ->whereColumn('md.supervisor_code', 'te.siso_code')
                  ->where('md.is_active', true);
                  
                if ($this->selectedRegion) $q->where('md.region_code', $this->selectedRegion);
                if ($this->selectedArea) $q->where('md.area_code', $this->selectedArea);
                
                $this->applyHierarchyAccess($q);
            });

        return $query->orderBy('f.SLSNAME')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function filterDistributors()
    {
        $query = \Illuminate\Support\Facades\DB::table('master_distributors as md')
            ->select('md.distributor_code', 'md.distributor_name')
            ->where('md.is_active', true);
            
        if ($this->selectedRegion) $query->where('md.region_code', $this->selectedRegion);
        if ($this->selectedArea) $query->where('md.area_code', $this->selectedArea);
        if ($this->selectedSupervisor) {
            $query->whereExists(function($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('team_elite_code_mappings as te')
                  ->whereColumn('te.siso_code', 'md.supervisor_code')
                  ->where('te.team_elite_code', $this->selectedSupervisor);
            });
        }
        
        $query = $this->applyHierarchyAccess($query);
        
        return $query->orderBy('md.distributor_name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function headerSalesmans()
    {
        if (empty($this->appliedDistributor)) {
            return collect([]);
        }

        return \Illuminate\Support\Facades\DB::table('salesmans')
            ->select('salesman_code', 'salesman_name')
            ->where('distributor_code', $this->appliedDistributor)
            ->where('is_active', true)
            ->orderBy('salesman_name')
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function affectedStores()
    {
        if (!$this->showSwapModal || empty($this->swapSalesmanCode) || empty($this->swapHariAsal) || empty($this->swapHariTujuan) || $this->swapHariAsal === $this->swapHariTujuan) {
            return collect([]);
        }

        $selectedWeeks = [];
        if ($this->swapMinggu['w1']) $selectedWeeks[] = 'w1';
        if ($this->swapMinggu['w2']) $selectedWeeks[] = 'w2';
        if ($this->swapMinggu['w3']) $selectedWeeks[] = 'w3';
        if ($this->swapMinggu['w4']) $selectedWeeks[] = 'w4';

        if (empty($selectedWeeks)) return collect([]);

        return \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.id', 'js.customer_code', 'js.' . $this->swapHariAsal . ' as asal', 'js.' . $this->swapHariTujuan . ' as tujuan', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.salesman_code', $this->swapSalesmanCode)
            ->where('js.distributor_code', $this->appliedDistributor)
            ->where(function($query) use ($selectedWeeks) {
                foreach ($selectedWeeks as $week) {
                    $query->where('js.' . $week, 'Y');
                }
            })
            ->where(function($query) {
                $query->where('js.' . $this->swapHariAsal, 'Y')
                      ->orWhere('js.' . $this->swapHariTujuan, 'Y');
            })
            ->when($this->appliedBulan, function($query) {
                $query->where('js.bulan', 'like', $this->appliedBulan . '%');
            })
            ->orderByRaw("
                CASE 
                    WHEN js.{$this->swapHariAsal} = 'Y' AND (js.{$this->swapHariTujuan} = 'T' OR js.{$this->swapHariTujuan} IS NULL) THEN 1
                    WHEN js.{$this->swapHariTujuan} = 'Y' AND (js.{$this->swapHariAsal} = 'T' OR js.{$this->swapHariAsal} IS NULL) THEN 2
                    ELSE 3
                END ASC
            ")
            ->orderBy('js.customer_code', 'asc')
            ->get();
    }

    public $showCalendarModal = false;
    public $selectedWeekForCalendar = null;

    // State Edit
    public $showEditModal = false;
    public $editId = null;
    public $editCustomerName = '';
    public $editSalesmanCode = '';
    public $editHari = ['h1' => false, 'h2' => false, 'h3' => false, 'h4' => false, 'h5' => false, 'h6' => false, 'h7' => false];
    public $editMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
    public $editReason = '';

    // State Delete
    public $showDeleteModal = false;
    public $deleteId = null;
    public $deleteReason = '';
    public $deleteMethod = '';
    public $deletePreviewData = null;
    public $showDeleteMethodModal = false;

    // State Swap (Tukar Jadwal)
    public $showSwapModal = false;
    public $swapTab = 'hari';
    public $swapSalesmanCode = '';
    public $swapHariAsal = '';
    public $swapHariTujuan = '';
    public $swapMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
    
    public $swapMingguAsal = '';
    public $swapMingguTujuan = '';
    
    public $swapSalesmanAsal = '';
    public $swapSalesmanTujuan = '';

    // State Bulk Delete
    public $showBulkDeleteModal = false;
    public $bdSalesman = '';
    public $bdHari = '';
    public $bdMinggu = '';
    public $bdSelectedIds = [];
    public $bdSelectAll = false;
    public $bdReason = '';
    public $bdDeleteMethod = '';

    // State Cleansing Duplicate
    public $showCleansingModal = false;
    public $cleansingResults = [];
    public $cleansingTotalGroups = 0;
    public $cleansingTotalDuplicates = 0;
    
    // Modals: Copy
    public $showCopyModal = false;
    public $copyRegion = '';
    public $copyArea = '';
    public $copyDistributor = '';
    public $copySalesmanCode = '';
    public $copySourceMonth = '';
    public $copyTargetMonth = '';

    // Export Eskalink
    public $showExportEskalinkModal = false;
    public $eskalinkFlagDelete = 'N';
    public $exportEskalinkSalesman = '';

    public function updatedCopySourceMonth()
    {
        if (!empty($this->copyDistributor)) {
            $hasJks = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('distributor_code', $this->copyDistributor)
                ->where('bulan', 'like', substr($this->copySourceMonth, 0, 7) . '%')
                ->exists();
            if (!$hasJks) {
                $this->copyDistributor = '';
                $this->copySalesmanCode = '';
            }
        }

        if (!empty($this->copySalesmanCode)) {
            $hasJks = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $this->copySalesmanCode)
                ->where('bulan', 'like', substr($this->copySourceMonth, 0, 7) . '%')
                ->exists();
            if (!$hasJks) {
                $this->copySalesmanCode = '';
            }
        }
    }

    public function updatedCopyRegion()
    {
        $this->copyArea = '';
        $this->copyDistributor = '';
        $this->copySalesmanCode = '';
    }

    public function updatedCopyArea()
    {
        $this->copyDistributor = '';
        $this->copySalesmanCode = '';
    }

    public function updatedCopyDistributor()
    {
        $this->copySalesmanCode = '';
        if (!empty($this->copyDistributor)) {
            $dist = \Illuminate\Support\Facades\DB::table('master_distributors')
                ->where('distributor_code', $this->copyDistributor)
                ->first(['region_code', 'area_code']);
            if ($dist) {
                if (empty($this->copyRegion)) {
                    $this->copyRegion = $dist->region_code ?? '';
                }
                if (empty($this->copyArea)) {
                    $this->copyArea = $dist->area_code ?? '';
                }
            }
        }
    }

    #[\Livewire\Attributes\Computed]
    public function copyFilterAreas()
    {
        if (!$this->copyRegion) return collect([]);
        $query = \Illuminate\Support\Facades\DB::table('master_areas as ma')
            ->select('ma.area_code', 'ma.area_name')
            ->join('master_distributors as md', 'ma.area_code', '=', 'md.area_code')
            ->join('jks_salesmans as js', 'md.distributor_code', '=', 'js.distributor_code')
            ->where('ma.region_code', $this->copyRegion);

        if (!empty($this->copySourceMonth)) {
            $query->where('js.bulan', 'like', substr($this->copySourceMonth, 0, 7) . '%');
        }

        return $query->distinct()->orderBy('ma.area_name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function copyFilterDistributors()
    {
        $query = \Illuminate\Support\Facades\DB::table('master_distributors as md')
            ->select('md.distributor_code', 'md.distributor_name')
            ->join('jks_salesmans as js', 'md.distributor_code', '=', 'js.distributor_code');

        if (!empty($this->copySourceMonth)) {
            $query->where('js.bulan', 'like', substr($this->copySourceMonth, 0, 7) . '%');
        }

        if ($this->copyRegion) $query->where('md.region_code', $this->copyRegion);
        if ($this->copyArea) $query->where('md.area_code', $this->copyArea);

        $user = auth()->user();
        if ($user && !$user->hasRole(['admin', 'spm'])) {
            $accessLevel = $user->getAccessLevel();
            if ($accessLevel === 'region' && !empty($user->region_code)) {
                $query->whereIn('md.region_code', (array) $user->region_code);
            } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
                $query->whereIn('md.area_code', (array) $user->area_code);
            } elseif ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
                $query->where('md.supervisor_code', $user->supervisor_code);
            }
        }

        return $query->distinct()->orderBy('md.distributor_name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function copyFilterSalesmans()
    {
        if (!$this->copyDistributor) return collect([]);

        $query = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->leftJoin('salesmans as s', function($join) {
                $join->on('js.salesman_code', '=', 's.salesman_code')
                     ->on('js.distributor_code', '=', 's.distributor_code');
            })
            ->where('js.distributor_code', $this->copyDistributor)
            ->select(
                'js.salesman_code',
                \Illuminate\Support\Facades\DB::raw("COALESCE(s.salesman_name, js.salesman_code) as salesman_name")
            );

        if (!empty($this->copySourceMonth)) {
            $query->where('js.bulan', 'like', substr($this->copySourceMonth, 0, 7) . '%');
        }

        return $query->distinct()->orderBy('salesman_name')->get();
    }

    // Batching State
    public $isCopying = false;
    public $copyQueue = [];
    public $copyTotal = 0;
    public $copyProgress = 0;
    public $currentCopySalesman = '';
    public $currentCopyDistributor = '';

    public function openCopyModal()
    {
        $this->copyRegion = $this->appliedRegion ?? '';
        $this->copyArea = $this->appliedArea ?? '';
        $this->copyDistributor = $this->appliedDistributor ?? '';
        $this->copySalesmanCode = $this->headerSalesman ?? '';
        
        $this->copySourceMonth = $this->appliedBulan ?: date('Y-m-01');
        $this->copyTargetMonth = date('Y-m', strtotime('+1 month', strtotime($this->copySourceMonth . '-01')));

        // Validasi apakah distributor aktif di halaman utama ada jadwalnya di copySourceMonth
        if (!empty($this->copyDistributor)) {
            $hasJks = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('distributor_code', $this->copyDistributor)
                ->where('bulan', 'like', substr($this->copySourceMonth, 0, 7) . '%')
                ->exists();
            if (!$hasJks) {
                $this->copyDistributor = '';
                $this->copySalesmanCode = '';
            } else {
                $dist = \Illuminate\Support\Facades\DB::table('master_distributors')
                    ->where('distributor_code', $this->copyDistributor)
                    ->first(['region_code', 'area_code']);
                if ($dist) {
                    if (empty($this->copyRegion)) $this->copyRegion = $dist->region_code ?? '';
                    if (empty($this->copyArea)) $this->copyArea = $dist->area_code ?? '';
                }
            }
        }

        $this->isCopying = false;
        $this->copyProgress = 0;
        $this->copyTotal = 0;
        $this->showCopyModal = true;
    }

    public function closeCopyModal()
    {
        $this->showCopyModal = false;
        $this->isCopying = false;
    }

    #[Computed]
    public function copyPendingApprovalsCount(): int
    {
        if (empty($this->copySourceMonth)) {
            return 0;
        }

        $sourceMonth = substr($this->copySourceMonth, 0, 7);

        $distributorCodes = [];
        if (!empty($this->copyDistributor)) {
            $distributorCodes = [$this->copyDistributor];
        } elseif (!empty($this->copyArea)) {
            $distributorCodes = \Illuminate\Support\Facades\DB::table('master_distributors')
                ->where('area_code', $this->copyArea)
                ->pluck('distributor_code')
                ->toArray();
        } elseif (!empty($this->copyRegion)) {
            $distributorCodes = \Illuminate\Support\Facades\DB::table('master_distributors')
                ->where('region_code', $this->copyRegion)
                ->pluck('distributor_code')
                ->toArray();
        } else {
            $user = auth()->user();
            if ($user && !$user->hasRole(['admin', 'spm'])) {
                $accessLevel = $user->getAccessLevel();
                $distQuery = \Illuminate\Support\Facades\DB::table('master_distributors');
                if ($accessLevel === 'region' && !empty($user->region_code)) {
                    $distQuery->whereIn('region_code', (array) $user->region_code);
                } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
                    $distQuery->whereIn('area_code', (array) $user->area_code);
                } elseif ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
                    $distQuery->where('supervisor_code', $user->supervisor_code);
                }
                $distributorCodes = $distQuery->pluck('distributor_code')->toArray();
            }
        }

        $query = \Illuminate\Support\Facades\DB::table('jks_approvals as ja')
            ->where('ja.status', 'PENDING');

        if (!empty($distributorCodes)) {
            $query->whereIn('ja.distributor_code', $distributorCodes);
        }

        $query->leftJoin('jks_salesmans as js', function($join) {
            $join->on(\Illuminate\Support\Facades\DB::raw("CAST(NULLIF(ja.payload->>'id', '') AS BIGINT)"), '=', 'js.id');
        });

        $query->where(function($q) use ($sourceMonth) {
            $q->where(\Illuminate\Support\Facades\DB::raw("ja.payload->>'bulan'"), 'like', $sourceMonth . '%')
              ->orWhere('js.bulan', 'like', $sourceMonth . '%')
              ->orWhere(function($sub) use ($sourceMonth) {
                  $sub->whereNull(\Illuminate\Support\Facades\DB::raw("ja.payload->>'bulan'"))
                      ->whereNull('js.id')
                      ->where('ja.created_at', 'like', $sourceMonth . '%');
              });
        });

        if (!empty($this->copySalesmanCode)) {
            $salesman = $this->copySalesmanCode;
            $query->where(function($q) use ($salesman) {
                $q->where(\Illuminate\Support\Facades\DB::raw("ja.payload->>'salesman_code'"), $salesman)
                  ->orWhere(\Illuminate\Support\Facades\DB::raw("ja.payload->>'salesman_asal'"), $salesman)
                  ->orWhere(\Illuminate\Support\Facades\DB::raw("ja.payload->>'salesman_tujuan'"), $salesman)
                  ->orWhere('js.salesman_code', $salesman);
            });
        }

        return $query->count();
    }

    public function startCopyPeriod()
    {
        abort_if(!$this->canEdit || !auth()->user()->hasRole(['admin', 'user', 'spm']), 403, 'Akses ditolak.');

        if (empty($this->copySourceMonth) || empty($this->copyTargetMonth)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Periode Asal dan Periode Tujuan tidak boleh kosong.']);
            return;
        }

        if ($this->copySourceMonth === $this->copyTargetMonth) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Periode Asal dan Periode Tujuan tidak boleh sama.']);
            return;
        }

        $pendingCount = $this->copyPendingApprovalsCount();
        if ($pendingCount > 0) {
            $sourceMonthName = \Carbon\Carbon::parse($this->copySourceMonth . '-01')->translatedFormat('F Y');
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Salin Periode Ditolak: Masih terdapat {$pendingCount} pengajuan perubahan jadwal berstatus PENDING pada periode {$sourceMonthName}. Harap selesaikan seluruh persetujuan terlebih dahulu!"
            ]);
            return;
        }

        // Cari semua salesman yang memiliki jadwal di Periode Asal (memenuhi filter)
        $query = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.salesman_code', 'js.distributor_code')
            ->where('js.bulan', 'like', $this->copySourceMonth . '%')
            ->when($this->copySalesmanCode, fn($q) => $q->where('js.salesman_code', $this->copySalesmanCode))
            ->when($this->copyDistributor, fn($q) => $q->where('js.distributor_code', $this->copyDistributor));

        // Jika distributor tidak dipilih, filter berdasarkan region/area
        if (empty($this->copyDistributor)) {
            if ($this->copyArea) {
                $distributors = \Illuminate\Support\Facades\DB::table('master_distributors')->where('area_code', $this->copyArea)->pluck('distributor_code');
                $query->whereIn('js.distributor_code', $distributors);
            } elseif ($this->copyRegion) {
                $distributors = \Illuminate\Support\Facades\DB::table('master_distributors')->where('region_code', $this->copyRegion)->pluck('distributor_code');
                $query->whereIn('js.distributor_code', $distributors);
            }
        }

        $salesmen = $query->groupBy('js.salesman_code', 'js.distributor_code')->get();

        if ($salesmen->isEmpty()) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Tidak ada jadwal yang ditemukan di Periode Asal untuk filter yang dipilih.']);
            return;
        }

        $this->copyQueue = json_decode(json_encode($salesmen->toArray()), true);
        $this->copyTotal = count($this->copyQueue);
        $this->copyProgress = 0;
        $this->isCopying = true;

        $this->dispatch('start-copy-batch');
    }

    #[On('process-next-copy-batch')]
    public function processNextCopyBatch()
    {
        if (empty($this->copyQueue)) {
            $this->isCopying = false;
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Proses Salin Jadwal selesai!']);
            $this->closeCopyModal();
            $this->resetPage();
            return;
        }

        // Pop 1 element
        $current = array_shift($this->copyQueue);
        $this->currentCopySalesman = $current['salesman_code'];
        $this->currentCopyDistributor = $current['distributor_code'];

        // Format bulan
        $sourceMonthPrefix = $this->copySourceMonth;
        $targetMonthFormatted = $this->copyTargetMonth . '-01'; // Target always uses -01 as standard date

        \Illuminate\Support\Facades\DB::transaction(function () use ($current, $sourceMonthPrefix, $targetMonthFormatted) {
            // 1. Delete target data for this salesman & distributor (Overwrite mode)
            \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $current['salesman_code'])
                ->where('distributor_code', $current['distributor_code'])
                ->where('bulan', 'like', $this->copyTargetMonth . '%')
                ->delete();

            // 2. Get source data
            $sourceRows = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $current['salesman_code'])
                ->where('distributor_code', $current['distributor_code'])
                ->where('bulan', 'like', $sourceMonthPrefix . '%')
                ->get();

            // 3. Insert target data
            if ($sourceRows->isNotEmpty()) {
                $inserts = [];
                $username = auth()->user()->username ?? 'system';
                
                foreach ($sourceRows as $row) {
                    $newRow = (array) $row;
                    unset($newRow['id']);
                    $newRow['bulan'] = $targetMonthFormatted;
                    $newRow['created_at'] = now();
                    $newRow['updated_at'] = now();
                    $newRow['update_by'] = $username;
                    $inserts[] = $newRow;
                }

                foreach (array_chunk($inserts, 500) as $chunk) {
                    \Illuminate\Support\Facades\DB::table('jks_salesmans')->insert($chunk);
                }
            }
        });

        $this->copyProgress++;

        // Trigger next batch
        $this->dispatch('continue-copy-batch');
    }



    protected function handleJksAction($actionType, $payload, $reason, callable $executeCallback, $successMessage)
    {
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        \Illuminate\Support\Facades\DB::table('jks_approvals')->insert([
            'maker_id' => auth()->id(),
            'checker_id' => null,
            'distributor_code' => $this->appliedDistributor,
            'action_type' => $actionType,
            'delete_type' => $payload['delete_type'] ?? null,
            'payload' => json_encode($payload),
            'reason' => $reason,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan.']);
    }

    public function openSwapModal()
    {
        if (!$this->checkCanManipulate()) {
            return;
        }

        $this->swapSalesmanCode = '';
        $this->swapHariAsal = '';
        $this->swapHariTujuan = '';
        $this->swapMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
        $this->swapMingguAsal = '';
        $this->swapMingguTujuan = '';
        $this->swapSalesmanAsal = '';
        $this->swapSalesmanTujuan = '';
        $this->swapTab = 'hari';
        $this->showSwapModal = true;
    }

    public function closeSwapModal()
    {
        $this->showSwapModal = false;
    }

    public function executeSwapDay()
    {
        abort_if(!$this->canEdit, 403, 'Akses ditolak.');
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        $this->abortIfUnauthorizedDistributor($this->appliedDistributor);

        if (empty($this->swapSalesmanCode) || empty($this->swapHariAsal) || empty($this->swapHariTujuan) || $this->swapHariAsal === $this->swapHariTujuan) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Input tidak valid untuk pertukaran jadwal.']);
            return;
        }

        $selectedWeeks = [];
        if ($this->swapMinggu['w1']) $selectedWeeks[] = 'w1';
        if ($this->swapMinggu['w2']) $selectedWeeks[] = 'w2';
        if ($this->swapMinggu['w3']) $selectedWeeks[] = 'w3';
        if ($this->swapMinggu['w4']) $selectedWeeks[] = 'w4';

        if (empty($selectedWeeks)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih setidaknya satu minggu (M1-M4).']);
            return;
        }

        $payload = [
            'bulan' => $this->appliedBulan,
            'salesman_code' => $this->swapSalesmanCode,
            'hari_asal' => $this->swapHariAsal,
            'hari_tujuan' => $this->swapHariTujuan,
            'minggu' => $selectedWeeks,
        ];

        $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
        $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
        
        $hariAsalName = $hariNames[$this->swapHariAsal] ?? $this->swapHariAsal;
        $hariTujuanName = $hariNames[$this->swapHariTujuan] ?? $this->swapHariTujuan;
        $seName = $this->headerSalesmans->firstWhere('salesman_code', $this->swapSalesmanCode)->salesman_name ?? $this->swapSalesmanCode;
        $weeksStr = collect($selectedWeeks)->map(fn($w) => $weekNames[$w] ?? $w)->implode(', ');

        $reason = "Tukar Jadwal Hari: {$hariAsalName} ditukar dengan {$hariTujuanName} | SE: {$seName} | Minggu: [{$weeksStr}]";

        try {
            $this->handleJksAction('TUKAR_HARI', $payload, $reason, function() use ($payload) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
                    $rows = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_code'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->where(function($query) use ($payload) {
                            foreach ($payload['minggu'] as $week) {
                                $query->orWhere($week, 'Y');
                            }
                        })
                        ->where(function($query) use ($payload) {
                            $query->where($payload['hari_asal'], 'Y')
                                  ->orWhere($payload['hari_tujuan'], 'Y');
                        })
                        ->when($this->appliedBulan, function($query) {
                            $query->where('bulan', 'like', $this->appliedBulan . '%');
                        })
                        ->get();

                    foreach ($rows as $row) {
                        $unselectedWeeks = array_diff(['w1', 'w2', 'w3', 'w4'], $payload['minggu']);
                        $hasUnselected = false;
                        foreach ($unselectedWeeks as $uw) {
                            if ($row->$uw === 'Y') {
                                $hasUnselected = true;
                                break;
                            }
                        }

                        if ($hasUnselected) {
                            $updateOriginal = ['updated_at' => now()];
                            foreach ($payload['minggu'] as $sw) {
                                $updateOriginal[$sw] = 'T';
                            }
                            \Illuminate\Support\Facades\DB::table('jks_salesmans')
                                ->where('id', $row->id)
                                ->update($updateOriginal);

                            $newRow = (array) $row;
                            unset($newRow['id']);
                            $newRow['created_at'] = now();
                            $newRow['updated_at'] = now();
                            
                            foreach ($unselectedWeeks as $uw) {
                                $newRow[$uw] = 'T';
                            }
                            
                            $temp = $newRow[$payload['hari_asal']];
                            $newRow[$payload['hari_asal']] = $newRow[$payload['hari_tujuan']];
                            $newRow[$payload['hari_tujuan']] = $temp;
                            
                            \Illuminate\Support\Facades\DB::table('jks_salesmans')->insert($newRow);

                        } else {
                            \Illuminate\Support\Facades\DB::table('jks_salesmans')
                                ->where('id', $row->id)
                                ->update([
                                    $payload['hari_asal'] => $row->{$payload['hari_tujuan']},
                                    $payload['hari_tujuan'] => $row->{$payload['hari_asal']},
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                });
            }, 'Pertukaran jadwal massal berhasil!');

            $this->closeSwapModal();
            
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pertukaran: ' . $e->getMessage()]);
        }
    }

    #[\Livewire\Attributes\Computed]
    public function affectedMingguSummary()
    {
        if (empty($this->swapSalesmanCode) || empty($this->swapMingguAsal) || empty($this->swapMingguTujuan) || $this->swapMingguAsal === $this->swapMingguTujuan) {
            return collect([]);
        }

        $days = [
            'h1' => 'Senin',
            'h2' => 'Selasa',
            'h3' => 'Rabu',
            'h4' => 'Kamis',
            'h5' => 'Jumat',
            'h6' => 'Sabtu',
            'h7' => 'Minggu'
        ];

        $summary = [];

        foreach ($days as $col => $label) {
            $countAsal = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $this->swapSalesmanCode)
                ->where('distributor_code', $this->appliedDistributor)
                ->when($this->appliedBulan, function($query) {
                    $query->where('bulan', 'like', $this->appliedBulan . '%');
                })
                ->where($col, 'Y')
                ->where($this->swapMingguAsal, 'Y')
                ->count();

            $countTujuan = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $this->swapSalesmanCode)
                ->where('distributor_code', $this->appliedDistributor)
                ->when($this->appliedBulan, function($query) {
                    $query->where('bulan', 'like', $this->appliedBulan . '%');
                })
                ->where($col, 'Y')
                ->where($this->swapMingguTujuan, 'Y')
                ->count();

            if ($countAsal > 0 || $countTujuan > 0) {
                $summary[] = (object) [
                    'hari' => $label,
                    'asal' => $countAsal,
                    'tujuan' => $countTujuan
                ];
            }
        }

        return collect($summary);
    }

    public function executeSwapMinggu()
    {
        abort_if(!$this->canEdit, 403, 'Akses ditolak.');
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        $this->abortIfUnauthorizedDistributor($this->appliedDistributor);

        if (empty($this->swapSalesmanCode) || empty($this->swapMingguAsal) || empty($this->swapMingguTujuan)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Lengkapi form sebelum mengeksekusi!']);
            return;
        }

        if ($this->swapMingguAsal === $this->swapMingguTujuan) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Minggu asal dan tujuan tidak boleh sama!']);
            return;
        }

        $summary = $this->affectedMingguSummary;
        if ($summary->isEmpty()) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Tidak ada toko yang terdampak!']);
            return;
        }

        $payload = [
            'bulan' => $this->appliedBulan,
            'salesman_code' => $this->swapSalesmanCode,
            'minggu_asal' => $this->swapMingguAsal,
            'minggu_tujuan' => $this->swapMingguTujuan,
        ];

        $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
        $mingguAsalName = $weekNames[$this->swapMingguAsal] ?? strtoupper($this->swapMingguAsal);
        $mingguTujuanName = $weekNames[$this->swapMingguTujuan] ?? strtoupper($this->swapMingguTujuan);
        $seName = $this->headerSalesmans->firstWhere('salesman_code', $this->swapSalesmanCode)->salesman_name ?? $this->swapSalesmanCode;

        $reason = "Tukar Jadwal Minggu: {$mingguAsalName} ditukar dengan {$mingguTujuanName} | SE: {$seName}";

        try {
            $this->handleJksAction('TUKAR_MINGGU', $payload, $reason, function() use ($payload) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
                    $rows = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_code'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->where(function($query) use ($payload) {
                            $query->where($payload['minggu_asal'], 'Y')
                                  ->orWhere($payload['minggu_tujuan'], 'Y');
                        })
                        ->when($this->appliedBulan, function($query) {
                            $query->where('bulan', 'like', $this->appliedBulan . '%');
                        })
                        ->get();

                    foreach ($rows as $row) {
                        $newAsalVal = $row->{$payload['minggu_tujuan']};
                        $newTujuanVal = $row->{$payload['minggu_asal']};

                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->where('id', $row->id)
                            ->update([
                                $payload['minggu_asal'] => $newAsalVal,
                                $payload['minggu_tujuan'] => $newTujuanVal,
                                'updated_at' => now(),
                            ]);
                    }
                });
            }, "Jadwal " . strtoupper($this->swapMingguAsal) . " berhasil ditukar dengan " . strtoupper($this->swapMingguTujuan) . "!");

            $this->closeSwapModal();
            $this->resetPage();

        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal menukar jadwal minggu: ' . $e->getMessage()]);
        }
    }

    #[\Livewire\Attributes\Computed]
    public function affectedSalesmanSummary()
    {
        if (empty($this->swapSalesmanAsal) || empty($this->swapSalesmanTujuan) || $this->swapSalesmanAsal === $this->swapSalesmanTujuan) {
            return collect([]);
        }

        $countAsal = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('salesman_code', $this->swapSalesmanAsal)
            ->where('distributor_code', $this->appliedDistributor)
            ->when($this->appliedBulan, function($query) {
                $query->where('bulan', 'like', $this->appliedBulan . '%');
            })
            ->count();

        $countTujuan = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('salesman_code', $this->swapSalesmanTujuan)
            ->where('distributor_code', $this->appliedDistributor)
            ->when($this->appliedBulan, function($query) {
                $query->where('bulan', 'like', $this->appliedBulan . '%');
            })
            ->count();

        $asalName = collect($this->headerSalesmans)->firstWhere('salesman_code', $this->swapSalesmanAsal)->salesman_name ?? $this->swapSalesmanAsal;
        $tujuanName = collect($this->headerSalesmans)->firstWhere('salesman_code', $this->swapSalesmanTujuan)->salesman_name ?? $this->swapSalesmanTujuan;

        return collect([
            'asal' => [
                'code' => $this->swapSalesmanAsal,
                'name' => $asalName,
                'count' => $countAsal
            ],
            'tujuan' => [
                'code' => $this->swapSalesmanTujuan,
                'name' => $tujuanName,
                'count' => $countTujuan
            ]
        ]);
    }

    public function executeSwapSalesman()
    {
        abort_if(!$this->canEdit, 403, 'Akses ditolak.');
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        $this->abortIfUnauthorizedDistributor($this->appliedDistributor);

        if (empty($this->swapSalesmanAsal) || empty($this->swapSalesmanTujuan)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Lengkapi form sebelum mengeksekusi!']);
            return;
        }

        if ($this->swapSalesmanAsal === $this->swapSalesmanTujuan) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Salesman asal dan tujuan tidak boleh sama!']);
            return;
        }

        $summary = $this->affectedSalesmanSummary;
        if ($summary['asal']['count'] == 0 && $summary['tujuan']['count'] == 0) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Kedua salesman tidak memiliki toko untuk ditukar!']);
            return;
        }

        $payload = [
            'bulan' => $this->appliedBulan,
            'salesman_asal' => $this->swapSalesmanAsal,
            'salesman_tujuan' => $this->swapSalesmanTujuan,
        ];

        $asalName = $summary['asal']['name'] ?? $this->swapSalesmanAsal;
        $tujuanName = $summary['tujuan']['name'] ?? $this->swapSalesmanTujuan;
        $reason = "Tukar Salesman: {$asalName} ditukar dengan {$tujuanName}";

        try {
            $this->handleJksAction('TUKAR_SALESMAN', $payload, $reason, function() use ($payload) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
                    $asalIds = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_asal'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->when($this->appliedBulan, function($query) {
                            $query->where('bulan', 'like', $this->appliedBulan . '%');
                        })
                        ->pluck('id');

                    $tujuanIds = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_tujuan'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->when($this->appliedBulan, function($query) {
                            $query->where('bulan', 'like', $this->appliedBulan . '%');
                        })
                        ->pluck('id');

                    if ($asalIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->whereIn('id', $asalIds)
                            ->update([
                                'salesman_code' => $payload['salesman_tujuan'],
                                'updated_at' => now(),
                            ]);
                    }

                    if ($tujuanIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->whereIn('id', $tujuanIds)
                            ->update([
                                'salesman_code' => $payload['salesman_asal'],
                                'updated_at' => now(),
                            ]);
                    }
                });
            }, "Semua toko dari salesman {$this->swapSalesmanAsal} berhasil ditukar dengan {$this->swapSalesmanTujuan}!");

            $this->closeSwapModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal menukar salesman: ' . $e->getMessage()]);
        }
    }

    // --- BULK DELETE METHODS ---
    public function openBulkDeleteModal()
    {
        if (!$this->checkCanManipulate()) {
            return;
        }

        $this->bdSalesman = '';
        $this->bdHari = '';
        $this->bdMinggu = '';
        $this->bdSelectedIds = [];
        $this->bdSelectAll = false;
        $this->bdReason = '';
        $this->bdDeleteMethod = '';
        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal()
    {
        $this->showBulkDeleteModal = false;
        $this->bdSelectedIds = [];
        $this->bdSelectAll = false;
        $this->bdReason = '';
        $this->bdDeleteMethod = '';
    }

    public function selectBdDeleteMethod($method)
    {
        $this->bdDeleteMethod = $method;
    }

    public function updatedBdSalesman() { $this->resetBdSelection(); }
    public function updatedBdHari() { $this->resetBdSelection(); }
    public function updatedBdMinggu() { $this->resetBdSelection(); }

    private function resetBdSelection()
    {
        $this->bdSelectedIds = [];
        $this->bdSelectAll = false;
    }

    public function updatedBdSelectAll($value)
    {
        if ($value) {
            $this->bdSelectedIds = $this->bdPreviewStores->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->bdSelectedIds = [];
        }
    }

    public function updatedBdSelectedIds()
    {
        $previewCount = $this->bdPreviewStores->count();
        $this->bdSelectAll = ($previewCount > 0 && count($this->bdSelectedIds) === $previewCount);
    }

    #[\Livewire\Attributes\Computed]
    public function bdPreviewStores()
    {
        if (empty($this->bdSalesman)) {
            return collect([]);
        }

        $query = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.distributor_code', $this->appliedDistributor)
            ->where('js.salesman_code', $this->bdSalesman);

        if (!empty($this->appliedBulan)) {
            $query->where('js.bulan', 'like', $this->appliedBulan . '%');
        }

        if (!empty($this->bdHari)) {
            $query->where('js.' . $this->bdHari, 'Y');
        }

        if (!empty($this->bdMinggu)) {
            if ($this->bdMinggu === 'ganjil') {
                $query->where(function($q) {
                    $q->where('js.w1', 'Y')->orWhere('js.w3', 'Y');
                });
            } elseif ($this->bdMinggu === 'genap') {
                $query->where(function($q) {
                    $q->where('js.w2', 'Y')->orWhere('js.w4', 'Y');
                });
            } else {
                $query->where('js.' . $this->bdMinggu, 'Y');
            }
        }

        return $query->orderBy('js.customer_code', 'asc')->get();
    }

    public function executeBulkDelete()
    {
        abort_if(!$this->canDelete, 403, 'Akses ditolak.');
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        $this->abortIfUnauthorizedJksIds($this->bdSelectedIds);

        if (empty($this->bdSelectedIds)) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pilih minimal satu toko untuk dihapus!']);
            return;
        }

        if (strlen(trim($this->bdReason)) < 5) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Alasan reset harus diisi (minimal 5 karakter)!']);
            return;
        }

        if (empty($this->bdDeleteMethod)) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pilih metode hapus terlebih dahulu!']);
            return;
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $count = count($this->bdSelectedIds);
            
            $records = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
                ->whereIn('js.id', $this->bdSelectedIds)
                ->where('js.distributor_code', $this->appliedDistributor)
                ->where('js.bulan', 'like', $this->appliedBulan . '%')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                    $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                         ->on('js.customer_code', '=', 'ltpte.uniq_kd');
                })
                ->get(['js.id', 'js.bulan', 'js.salesman_code', 'js.distributor_code', 'js.customer_code', 'ltpte.customer_name'])
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'bulan' => $row->bulan,
                        'salesman_code' => $row->salesman_code,
                        'distributor_code' => $row->distributor_code,
                        'customer_code' => $row->customer_code,
                        'customer_name' => $row->customer_name ?? $row->customer_code ?? '-',
                    ];
                })->toArray();
            
            $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
            $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
            $hariFilter = $this->bdHari ? ($hariNames[$this->bdHari] ?? $this->bdHari) : 'Semua Hari';
            $mingguFilter = $this->bdMinggu ? ($weekNames[$this->bdMinggu] ?? $this->bdMinggu) : 'Semua Minggu';
            $seFilter = collect($this->headerSalesmans)->firstWhere('salesman_code', $this->bdSalesman)->salesman_name ?? $this->bdSalesman ?: 'Semua Salesman';

            $payload = [
                'ids' => $this->bdSelectedIds,
                'delete_type' => strtoupper($this->bdDeleteMethod),
                'bulan' => $this->appliedBulan,
                'records' => $records,
                'salesman_code' => $seFilter,
                'hari' => [$hariFilter],
                'minggu' => [$mingguFilter],
                'update' => [
                    'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                    'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                    'reason' => $this->bdReason,
                ]
            ];

            $methodName = $this->bdDeleteMethod === 'hard' ? 'Hapus Permanen' : 'Soft Delete';
            $reasonFull = "{$methodName} Massal {$count} Jadwal Toko | Filter -> SE: {$seFilter}, Hari: {$hariFilter}, Minggu: {$mingguFilter} | Alasan: " . $this->bdReason;

            $this->handleJksAction('DELETE_MASSAL', $payload, $reasonFull, function() use ($payload) {
                if (($payload['delete_type'] ?? 'SOFT') === 'HARD') {
                    \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->whereIn('id', $payload['ids'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->where('bulan', 'like', $this->appliedBulan . '%')
                        ->delete();
                } else {
                    \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->whereIn('id', $payload['ids'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->where('bulan', 'like', $this->appliedBulan . '%')
                        ->update(array_merge($payload['update'], ['updated_at' => now()]));
                }
            }, "Berhasil memproses {$count} jadwal toko!");

            \Illuminate\Support\Facades\DB::commit();
            
            $this->closeBulkDeleteModal();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mereset massal: ' . $e->getMessage()]);
        }
    }

    public function openCleansingModal()
    {
        if (!$this->checkCanManipulate()) {
            return;
        }

        if (!auth()->user()->hasRole(['admin', 'user'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk fitur ini!']);
            return;
        }

        $this->showCleansingModal = true;
        $this->cleansingResults = [];
        $this->cleansingTotalGroups = 0;
        $this->cleansingTotalDuplicates = 0;
        
        $this->scanningDuplicates();
    }
    
    public function closeCleansingModal()
    {
        $this->showCleansingModal = false;
        $this->cleansingResults = [];
    }

    public function scanningDuplicates()
    {
        if (empty($this->appliedDistributor)) {
            return;
        }

        // 1. Get potential duplicates (customer + salesman + bulan having > 1 record)
        $potentialDuplicates = \Illuminate\Support\Facades\DB::select("
            SELECT customer_code, salesman_code, bulan
            FROM jks_salesmans
            WHERE distributor_code = ? AND bulan LIKE ?
            GROUP BY customer_code, salesman_code, bulan
            HAVING COUNT(*) > 1
        ", [$this->appliedDistributor, $this->appliedBulan . '%']);

        if (empty($potentialDuplicates)) {
            $this->cleansingTotalGroups = 0;
            $this->cleansingTotalDuplicates = 0;
            $this->cleansingResults = [];
            return;
        }

        $results = [];
        $totalGroups = 0;
        $totalDuplicates = 0;

        foreach ($potentialDuplicates as $dup) {
            $records = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
                ->select('js.*', 'ltpte.customer_name')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                    $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                         ->on('js.customer_code', '=', 'ltpte.uniq_kd');
                })
                ->where('js.distributor_code', $this->appliedDistributor)
                ->where('js.bulan', $dup->bulan)
                ->where('js.customer_code', $dup->customer_code)
                ->where('js.salesman_code', $dup->salesman_code)
                ->get();

            // Sub-group by Identical Weeks
            $weekGroups = [];
            $dayGroups = [];
            $processedIds = [];

            foreach ($records as $r) {
                $weekKey = ($r->w1==='Y'?'Y':'T') . ($r->w2==='Y'?'Y':'T') . ($r->w3==='Y'?'Y':'T') . ($r->w4==='Y'?'Y':'T');
                $weekGroups[$weekKey][] = $r;
            }

            foreach ($weekGroups as $weekKey => $groupRecords) {
                if (count($groupRecords) > 1) {
                    $processedGroup = $this->processDuplicateGroup(collect($groupRecords), 'Identik Minggu', $dup->customer_code, $dup->salesman_code);
                    $results[] = $processedGroup;
                    $totalGroups++;
                    $totalDuplicates += (count($groupRecords) - 1);
                    foreach ($groupRecords as $r) {
                        $processedIds[] = $r->id;
                    }
                }
            }

            // Now check remaining records for Identical Days
            $remainingRecords = $records->filter(function($r) use ($processedIds) {
                return !in_array($r->id, $processedIds);
            });

            foreach ($remainingRecords as $r) {
                $dayKey = ($r->h1==='Y'?'Y':'T') . ($r->h2==='Y'?'Y':'T') . ($r->h3==='Y'?'Y':'T') . 
                          ($r->h4==='Y'?'Y':'T') . ($r->h5==='Y'?'Y':'T') . ($r->h6==='Y'?'Y':'T') . ($r->h7==='Y'?'Y':'T');
                $dayGroups[$dayKey][] = $r;
            }

            foreach ($dayGroups as $dayKey => $groupRecords) {
                if (count($groupRecords) > 1) {
                    $processedGroup = $this->processDuplicateGroup(collect($groupRecords), 'Identik Hari', $dup->customer_code, $dup->salesman_code);
                    $results[] = $processedGroup;
                    $totalGroups++;
                    $totalDuplicates += (count($groupRecords) - 1);
                    foreach ($groupRecords as $r) {
                        $processedIds[] = $r->id;
                    }
                }
            }
        }

        $this->cleansingTotalGroups = $totalGroups;
        $this->cleansingTotalDuplicates = $totalDuplicates;
        $this->cleansingResults = $results;
    }

    private function processDuplicateGroup($records, $dupType, $customerCode, $salesmanCode)
    {
        $processedRecords = [];
        foreach ($records as $rec) {
            $yCount = 0;
            foreach (['h1','h2','h3','h4','h5','h6','h7','w1','w2','w3','w4'] as $col) {
                if (isset($rec->$col) && $rec->$col === 'Y') $yCount++;
            }
            $processedRecords[] = [
                'id' => $rec->id,
                'customer_name' => $rec->customer_name ?? $customerCode,
                'salesman_code' => $salesmanCode,
                'yCount' => $yCount,
                'updated_at' => $rec->updated_at,
                'isPrimary' => false,
                'raw' => (array) $rec
            ];
        }

        // Sort by yCount desc, then updated_at desc
        usort($processedRecords, function($a, $b) {
            if ($a['yCount'] === $b['yCount']) {
                return strtotime($b['updated_at'] ?? '0') - strtotime($a['updated_at'] ?? '0');
            }
            return $b['yCount'] - $a['yCount'];
        });

        // Mark first as primary
        if (count($processedRecords) > 0) {
            $processedRecords[0]['isPrimary'] = true;
        }

        return [
            'customer_code' => $customerCode,
            'salesman_code' => $salesmanCode,
            'dup_type' => $dupType,
            'records' => $processedRecords
        ];
    }

    public function executeCleansing()
    {
        $this->abortIfUnauthorizedDistributor($this->appliedDistributor);
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm'])) { abort(403, 'Akses ditolak. Anda tidak memiliki role yang diizinkan.'); }

        if (!auth()->user()->hasRole(['admin', 'user'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk mengeksekusi fitur ini!']);
            return;
        }

        if ($this->cleansingTotalDuplicates === 0) {
            return;
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $deletedCount = 0;

            foreach ($this->cleansingResults as $group) {
                $primaryId = null;
                $duplicateIds = [];
                $mergedH = [];
                $mergedW = [];

                foreach ($group['records'] as $rec) {
                    if ($rec['isPrimary']) {
                        $primaryId = $rec['id'];
                        // Initialize merge with primary values
                        foreach (['h1','h2','h3','h4','h5','h6','h7'] as $col) $mergedH[$col] = $rec['raw'][$col] ?? 'T';
                        foreach (['w1','w2','w3','w4'] as $col) $mergedW[$col] = $rec['raw'][$col] ?? 'T';
                    } else {
                        $duplicateIds[] = $rec['id'];
                    }
                }

                // If no primary found or no duplicates, skip
                if (!$primaryId || empty($duplicateIds)) continue;

                // Merge Y values from duplicates
                foreach ($group['records'] as $rec) {
                    if (!$rec['isPrimary']) {
                        foreach (['h1','h2','h3','h4','h5','h6','h7'] as $col) {
                            if (($rec['raw'][$col] ?? 'T') === 'Y') $mergedH[$col] = 'Y';
                        }
                        foreach (['w1','w2','w3','w4'] as $col) {
                            if (($rec['raw'][$col] ?? 'T') === 'Y') $mergedW[$col] = 'Y';
                        }
                    }
                }

                // Update primary record
                \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->where('id', $primaryId)
                    ->update(array_merge($mergedH, $mergedW, ['updated_at' => now(), 'reason' => 'System Cleansing Merge']));

                // Hard Delete duplicate records
                \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->whereIn('id', $duplicateIds)
                    ->delete();
                    
                $deletedCount += count($duplicateIds);
            }

            \Illuminate\Support\Facades\DB::commit();
            
            $this->closeCleansingModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => "Berhasil membersihkan {$deletedCount} duplicate route!"]);
            $this->dispatch('refreshTable');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal melakukan cleansing: ' . $e->getMessage()]);
        }
    }
    public function exportExcel()
    {
        abort_if(!$this->canExport, 403, 'Akses ditolak.');

        $filters = $this->getAppliedFilters();
        $filters['salesman'] = $this->headerSalesman;
        
        $fileName = 'JKS_Export_' . date('Ymd_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JksSalesmanExport($this->search, $filters), 
            $fileName
        );
    }

    public function editJadwal($id)
    {
        if (!$this->checkCanManipulate()) {
            return;
        }

        $jks = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.id', $id)
            ->first();

        if ($jks) {
            $this->editId = $jks->id;
            $this->editCustomerName = $jks->customer_name ?? $jks->customer_code;
            $this->editSalesmanCode = $jks->salesman_code;
            
            $this->editHari = [
                'h1' => $jks->h1 === 'Y',
                'h2' => $jks->h2 === 'Y',
                'h3' => $jks->h3 === 'Y',
                'h4' => $jks->h4 === 'Y',
                'h5' => $jks->h5 === 'Y',
                'h6' => $jks->h6 === 'Y',
                'h7' => $jks->h7 === 'Y',
            ];

            $this->editMinggu = [
                'w1' => $jks->w1 === 'Y',
                'w2' => $jks->w2 === 'Y',
                'w3' => $jks->w3 === 'Y',
                'w4' => $jks->w4 === 'Y',
            ];

            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editId = null;
        $this->editReason = '';
    }

    public function saveJadwal()
    {
        abort_if(!$this->canEdit, 403, 'Akses ditolak.');
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        if (!$this->editId) return;

        $this->abortIfUnauthorizedJksIds([$this->editId]);

        if (empty(trim($this->editSalesmanCode))) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Validasi Gagal: Pilihan Salesman tidak boleh kosong!']);
            return;
        }
        
        if (strlen(trim($this->editReason)) < 5) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Alasan edit harus diisi (minimal 5 karakter)!']);
            return;
        }

        try {
            $payload = [
                'id' => $this->editId,
                'bulan' => $this->appliedBulan,
                'update' => [
                    'salesman_code' => $this->editSalesmanCode,
                    'h1' => $this->editHari['h1'] ? 'Y' : 'T',
                    'h2' => $this->editHari['h2'] ? 'Y' : 'T',
                    'h3' => $this->editHari['h3'] ? 'Y' : 'T',
                    'h4' => $this->editHari['h4'] ? 'Y' : 'T',
                    'h5' => $this->editHari['h5'] ? 'Y' : 'T',
                    'h6' => $this->editHari['h6'] ? 'Y' : 'T',
                    'h7' => $this->editHari['h7'] ? 'Y' : 'T',
                    'w1' => $this->editMinggu['w1'] ? 'Y' : 'T',
                    'w2' => $this->editMinggu['w2'] ? 'Y' : 'T',
                    'w3' => $this->editMinggu['w3'] ? 'Y' : 'T',
                    'w4' => $this->editMinggu['w4'] ? 'Y' : 'T',
                    'reason' => $this->editReason,
                ]
            ];

            $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
            $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];

            $activeHari = [];
            foreach ($this->editHari as $key => $val) { if ($val) $activeHari[] = $hariNames[$key] ?? strtoupper($key); }
            $activeMinggu = [];
            foreach ($this->editMinggu as $key => $val) { if ($val) $activeMinggu[] = $weekNames[$key] ?? strtoupper($key); }
            
            $seName = $this->headerSalesmans->firstWhere('salesman_code', $this->editSalesmanCode)->salesman_name ?? $this->editSalesmanCode;

            $reasonFull = "Edit Jadwal Toko: {$this->editCustomerName} (SE: {$seName}) | Hari: [" . implode(', ', $activeHari) . "] | Minggu: [" . implode(', ', $activeMinggu) . "] | Alasan: " . $this->editReason;

            $this->handleJksAction('EDIT_INDIVIDU', $payload, $reasonFull, function() use ($payload) {
                \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->where('id', $payload['id'])
                    ->update(array_merge($payload['update'], ['updated_at' => now()]));
            }, 'Jadwal berhasil diperbarui!');

            $this->closeEditModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage()]);
        }
    }

    public function confirmDelete($id)
    {
        if (!$this->checkCanManipulate()) {
            return;
        }

        $jks = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.id', $id)
            ->first();

        if ($jks) {
            $this->deleteId = $id;
            $this->deletePreviewData = $jks;
            $this->deleteReason = '';
            $this->deleteMethod = '';
            $this->showDeleteMethodModal = true;
            $this->showDeleteModal = false;
        }
    }

    public function selectDeleteMethod($method)
    {
        $this->deleteMethod = $method;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteMethodModal = false;
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteReason = '';
        $this->deleteMethod = '';
        $this->deletePreviewData = null;
    }

    public function executeDelete()
    {
        abort_if(!$this->canDelete, 403, 'Akses ditolak.');
        if (!$this->checkCanManipulate(true)) {
            return;
        }

        if (!$this->deleteId) return;

        $this->abortIfUnauthorizedJksIds([$this->deleteId]);

        if (strlen(trim($this->deleteReason)) < 5) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Alasan reset harus diisi (minimal 5 karakter)!']);
            return;
        }

        if (empty($this->deleteMethod)) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pilih metode hapus terlebih dahulu!']);
            return;
        }

        try {
            $jks = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
                ->select('js.*', 'ltpte.customer_name')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                    $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                         ->on('js.customer_code', '=', 'ltpte.uniq_kd');
                })
                ->where('js.id', $this->deleteId)
                ->first();

            $payload = [
                'id' => $this->deleteId,
                'delete_type' => strtoupper($this->deleteMethod),
                'bulan' => $this->appliedBulan,
                'records' => $jks ? [
                    [
                        'id' => $jks->id,
                        'bulan' => $jks->bulan,
                        'salesman_code' => $jks->salesman_code,
                        'distributor_code' => $jks->distributor_code,
                        'customer_code' => $jks->customer_code,
                        'customer_name' => $jks->customer_name ?? $jks->customer_code ?? '-',
                    ]
                ] : [],
                'update' => [
                    'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                    'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                    'reason' => $this->deleteReason,
                ]
            ];

            $customerName = $jks->customer_name ?? $jks->customer_code ?? '-';
            $salesmanCode = $jks->salesman_code ?? '-';
            $seName = $this->headerSalesmans->firstWhere('salesman_code', $salesmanCode)->salesman_name ?? $salesmanCode;
            
            $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
            $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
            
            $activeHari = [];
            foreach (['h1','h2','h3','h4','h5','h6','h7'] as $h) { if (isset($jks->$h) && $jks->$h === 'Y') $activeHari[] = $hariNames[$h]; }
            $activeMinggu = [];
            foreach (['w1','w2','w3','w4'] as $w) { if (isset($jks->$w) && $jks->$w === 'Y') $activeMinggu[] = $weekNames[$w]; }

            $methodName = $this->deleteMethod === 'hard' ? 'Hard Delete' : 'Soft Delete';
            $reasonFull = "{$methodName} Jadwal Toko: {$customerName} | SE: {$seName} | Hari: [" . implode(', ', $activeHari) . "] | Minggu: [" . implode(', ', $activeMinggu) . "] | Alasan: " . $this->deleteReason;

            $this->handleJksAction('DELETE_INDIVIDU', $payload, $reasonFull, function() use ($payload) {
                if (($payload['delete_type'] ?? 'SOFT') === 'HARD') {
                    \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('id', $payload['id'])
                        ->delete();
                } else {
                    \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('id', $payload['id'])
                        ->update(array_merge($payload['update'], ['updated_at' => now()]));
                }
            }, 'Jadwal berhasil diproses!');

            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal menghapus jadwal: ' . $e->getMessage()]);
        }
    }



    public function openCalendarModal($week)
    {
        $this->selectedWeekForCalendar = $week;
        $this->showCalendarModal = true;
    }

    public function closeCalendarModal()
    {
        $this->showCalendarModal = false;
        $this->selectedWeekForCalendar = null;
    }

    #[\Livewire\Attributes\Computed]
    public function calendarDays()
    {
        if (!$this->showCalendarModal) return collect([]);
        
        $bulan = $this->appliedBulan ?: date('Y-m-01');
        $yearMonth = substr($bulan, 0, 7); // Gets "YYYY-MM"
        
        return \Illuminate\Support\Facades\DB::table('master_calender')
            ->where('date', 'like', $yearMonth . '%')
            ->orderBy('date')
            ->get();
    }

    protected function calculateApprovalsSummary($kpiSummary, $bulan)
    {
        if (!$kpiSummary || $kpiSummary->isEmpty()) return;

        $salesmanCodes = $kpiSummary->pluck('salesman_code')->filter()->unique()->toArray();
        if (empty($salesmanCodes)) return;

        $rawApprovals = \Illuminate\Support\Facades\DB::table('jks_approvals')
            ->where('status', 'APPROVED')
            ->where('payload->bulan', $bulan)
            ->orderBy('created_at', 'asc')
            ->get();
            
        $storeStates = [];
        $rawCounts = [];
        
        foreach ($salesmanCodes as $sCode) {
            $storeStates[$sCode] = [];
            $rawCounts[$sCode] = ['penambahan' => 0, 'perubahan' => 0, 'deleted' => 0];
        }

        foreach ($rawApprovals as $app) {
            $payload = json_decode($app->payload, true);
            if (!$payload) continue;
            
            $action = $app->action_type;
            $tokoCount = isset($payload['tokos']) && is_array($payload['tokos']) ? count($payload['tokos']) : 1;

            if ($action === 'TAMBAH_JADWAL') {
                $sCode = $payload['salesman_code'] ?? null;
                if ($sCode && in_array($sCode, $salesmanCodes)) {
                    if (isset($payload['tokos']) && is_array($payload['tokos'])) {
                        foreach ($payload['tokos'] as $toko) {
                            $cCode = $toko['code'] ?? $toko['customer_code'] ?? null;
                            if ($cCode) {
                                $currentState = $storeStates[$sCode][$cCode] ?? null;
                                if ($currentState === 'DELETED') {
                                    $storeStates[$sCode][$cCode] = 'PERUBAHAN'; // Input kembali
                                } else {
                                    $storeStates[$sCode][$cCode] = 'PENAMBAHAN';
                                }
                            } else {
                                $rawCounts[$sCode]['penambahan'] += 1;
                            }
                        }
                    } else {
                        $rawCounts[$sCode]['penambahan'] += $tokoCount;
                    }
                }
            } elseif (in_array($action, ['HAPUS_JADWAL', 'DELETE_MASSAL', 'DELETE_INDIVIDU'])) {
                if (isset($payload['records']) && is_array($payload['records'])) {
                    foreach ($payload['records'] as $record) {
                        $sCode = $record['salesman_code'] ?? null;
                        if ($sCode && in_array($sCode, $salesmanCodes)) {
                            $cCode = $record['customer_code'] ?? null;
                            if ($cCode) {
                                $currentState = $storeStates[$sCode][$cCode] ?? null;
                                if ($currentState === 'PENAMBAHAN') {
                                    unset($storeStates[$sCode][$cCode]); // Cancel out
                                } else {
                                    $storeStates[$sCode][$cCode] = 'DELETED';
                                }
                            } else {
                                $rawCounts[$sCode]['deleted'] += 1;
                            }
                        }
                    }
                } else {
                    $sCodes = [];
                    if (isset($payload['salesman_code'])) $sCodes[] = $payload['salesman_code'];
                    foreach ($sCodes as $sCode) {
                        if (in_array($sCode, $salesmanCodes)) {
                            $rawCounts[$sCode]['deleted'] += $tokoCount;
                        }
                    }
                }
            } elseif (in_array($action, ['UBAH_JADWAL', 'TUKAR_JADWAL', 'TUKAR_HARI', 'TUKAR_MINGGU', 'TUKAR_SALESMAN'])) {
                $sCodes = [];
                if (isset($payload['salesman_code'])) $sCodes[] = $payload['salesman_code'];
                if (isset($payload['salesman_asal'])) $sCodes[] = $payload['salesman_asal'];
                if (isset($payload['salesman_tujuan'])) $sCodes[] = $payload['salesman_tujuan'];
                if (isset($payload['records']) && is_array($payload['records'])) {
                    foreach ($payload['records'] as $record) {
                        if (isset($record['salesman_code'])) $sCodes[] = $record['salesman_code'];
                    }
                }
                foreach (array_unique($sCodes) as $sCode) {
                    if (in_array($sCode, $salesmanCodes)) {
                        $rawCounts[$sCode]['perubahan'] += $tokoCount;
                    }
                }
            }
        }

        $appCounts = collect();
        foreach ($salesmanCodes as $sCode) {
            $penambahan = $rawCounts[$sCode]['penambahan'];
            $perubahan = $rawCounts[$sCode]['perubahan'];
            $deleted = $rawCounts[$sCode]['deleted'];

            foreach ($storeStates[$sCode] as $cCode => $state) {
                if ($state === 'PENAMBAHAN') $penambahan++;
                elseif ($state === 'PERUBAHAN') $perubahan++;
                elseif ($state === 'DELETED') $deleted++;
            }

            $appCounts->put($sCode, [
                'penambahan' => $penambahan,
                'perubahan' => $perubahan,
                'deleted' => $deleted,
            ]);
        }

        foreach ($kpiSummary as $item) {
            $app = $appCounts->get($item->salesman_code);
            $item->penambahan = $app['penambahan'] ?? 0;
            $item->perubahan = $app['perubahan'] ?? 0;
            $item->deleted = $app['deleted'] ?? 0;
        }
    }

    public function render(JksSalesmanService $service)
    {
        $filters = $this->getAppliedFilters();
        $filters['salesman'] = $this->headerSalesman;
        $filters['hari'] = $this->selectedHari;
        $filters['minggu'] = $this->selectedMinggu;
        $filters['dobel_cover'] = $this->onlyDobelCover;
        
        $mingguDates = $service->getMingguDateRanges($filters['bulan'] ?? null);
        $visitSummary = [];
        if (empty($filters['distributor'])) {
            $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 100, $this->getPage());
            $kpiSummary = null;
            $collidingCustomerCodes = [];
        } else {
            $data = $service->getFilteredPaginatedList($this->search, $filters, 100);
            
            $kpiSummary = $service->getKpiSummary($filters);
            
            // Populate penambahan, perubahan, deleted
            $this->calculateApprovalsSummary($kpiSummary, $filters['bulan'] ?? null);
            
            // Find collisions for the current page
            $collidingCustomerCodes = [];
            if (!empty($filters['bulan']) && count($data->items()) > 0) {
                $customerCodesOnPage = collect($data->items())->pluck('uniq_kode')->unique()->toArray();
                
                $collisions = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->select('customer_code')
                    ->where('bulan', 'like', $filters['bulan'] . '%')
                    ->whereIn('customer_code', $customerCodesOnPage)
                    ->groupBy('customer_code')
                    ->havingRaw('COUNT(DISTINCT salesman_code) > 1')
                    ->pluck('customer_code')
                    ->toArray();
                    
                $collidingCustomerCodes = $collisions;
            }

            // Ambil data agregasi visit bulanan untuk toko pada halaman aktif
            if (!empty($filters['bulan']) && count($data->items()) > 0) {
                $visitSummary = $this->fetchVisitSummaryForPage($filters['distributor'], $filters['bulan'], $data->items());
            }
        }

        return view('livewire.call-plan.jks-salesmans.index', [
            'jksData' => $data,
            'kpiSummary' => $kpiSummary,
            'mingguDates' => $mingguDates,
            'collidingCustomerCodes' => $collidingCustomerCodes,
            'visitSummary' => $visitSummary,
        ]);
    }

    /**
     * Ambil data ringkasan realisasi visit bulanan dari rpt_visit_an_h & rpt_visit_an_r
     * khusus untuk toko-toko yang sedang tampil di halaman pagination aktif.
     */
    protected function fetchVisitSummaryForPage(string $distributorCode, string $bulan, array $pageItems): array
    {
        if (empty($distributorCode) || empty($bulan) || empty($pageItems)) {
            return [];
        }

        $eskalinkCode = \Illuminate\Support\Facades\DB::table('distributor_implementasi_eskalink')
            ->where('distributor_code', $distributorCode)
            ->value('eskalink_code') ?? $distributorCode;

        // Petakan customer_code target
        $custMapping = []; // [custno_rpt => [uniq_kode1, ...]]
        foreach ($pageItems as $item) {
            $targetCode = !empty($item->customer_code) ? $item->customer_code : $item->uniq_kode;
            $custMapping[$targetCode][] = $item->uniq_kode;
        }

        $targetCustnos = array_keys($custMapping);
        if (empty($targetCustnos)) {
            return [];
        }

        $bulanPrefix = substr($bulan, 0, 7) . '%';

        // 1. Agregasi data dari rpt_visit_an_h (Khusus kunjungan rute: FLAG_PJP = 'R')
        $aggregates = \Illuminate\Support\Facades\DB::table('rpt_visit_an_h')
            ->where('BID', $eskalinkCode)
            ->where('TANGGAL', 'like', $bulanPrefix)
            ->whereIn('CUSTNO', $targetCustnos)
            ->select(
                'CUSTNO',
                \Illuminate\Support\Facades\DB::raw('COUNT(CASE WHEN "FLAG_VISIT" = \'Y\' AND "FLAG_PJP" = \'R\' THEN 1 END) as total_visit'),
                \Illuminate\Support\Facades\DB::raw('COUNT(CASE WHEN "FLAG_EC" = \'Y\' AND "FLAG_PJP" = \'R\' THEN 1 END) as total_ec'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(CASE WHEN "FLAG_PJP" = \'R\' THEN "ORDER_VAL" ELSE 0 END), 0) as total_order_val'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(CASE WHEN "FLAG_PJP" = \'R\' THEN "ORDER_QTY" ELSE 0 END), 0) as total_order_qty'),
                \Illuminate\Support\Facades\DB::raw('MAX(CASE WHEN "FLAG_PJP" = \'R\' THEN "TANGGAL" END) as latest_visit_date')
            )
            ->groupBy('CUSTNO')
            ->get()
            ->keyBy('CUSTNO');

        // 2. Cari alasan dari rpt_visit_an_r untuk kunjungan terakhir toko
        $lookupPairs = [];
        foreach ($aggregates as $custno => $agg) {
            if (!empty($agg->latest_visit_date)) {
                $lookupPairs[] = ['custno' => $custno, 'date' => (string)$agg->latest_visit_date];
            }
        }

        $latestReasons = [];
        if (!empty($lookupPairs)) {
            $reasonsQuery = \Illuminate\Support\Facades\DB::table('rpt_visit_an_r')
                ->where('BID', $eskalinkCode)
                ->where(function($q) use ($lookupPairs) {
                    foreach ($lookupPairs as $pair) {
                        $q->orWhere(function($sub) use ($pair) {
                            $sub->where('CUSTNO', $pair['custno'])
                                ->where('TANGGAL', $pair['date']);
                        });
                    }
                })
                ->select('CUSTNO', 'TANGGAL', 'REASON_TYPE', 'REASON_ID', 'REASON_DESC', 'REASON_NOTE')
                ->get();

            foreach ($reasonsQuery as $rq) {
                $latestReasons[$rq->CUSTNO] = [
                    'type' => $rq->REASON_TYPE,
                    'code' => $rq->REASON_ID,
                    'desc' => $rq->REASON_DESC,
                    'note' => $rq->REASON_NOTE,
                ];
            }
        }

        // 3. Susun hasil akhir ber-index uniq_kode
        $result = [];
        foreach ($custMapping as $custno => $uniqCodes) {
            $agg = $aggregates[$custno] ?? null;
            $reason = $latestReasons[$custno] ?? null;

            $summaryItem = [
                'target_custno' => $custno,
                'total_visit' => $agg ? (int)$agg->total_visit : 0,
                'total_ec' => $agg ? (int)$agg->total_ec : 0,
                'total_order_val' => $agg ? (float)$agg->total_order_val : 0,
                'total_order_qty' => $agg ? (int)$agg->total_order_qty : 0,
                'latest_visit_date' => $agg ? $agg->latest_visit_date : null,
                'latest_reason' => $reason,
                'has_history' => $agg !== null,
            ];

            foreach ($uniqCodes as $uCode) {
                $result[$uCode] = $summaryItem;
            }
        }

        return $result;
    }

    /**
     * Buka modal rincian riwayat kunjungan & alasan untuk toko yang dipilih.
     */
    public function openVisitDetailModal($uniqKode, $customerCode = null, $customerName = null)
    {
        $filters = $this->getAppliedFilters();
        if (empty($filters['distributor']) || empty($filters['bulan'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih distributor dan bulan terlebih dahulu.']);
            return;
        }

        $this->abortIfUnauthorizedDistributor($filters['distributor']);

        $eskalinkCode = \Illuminate\Support\Facades\DB::table('distributor_implementasi_eskalink')
            ->where('distributor_code', $filters['distributor'])
            ->value('eskalink_code') ?? $filters['distributor'];

        $targetCustNo = !empty($customerCode) ? $customerCode : $uniqKode;
        $bulanPrefix = substr($filters['bulan'], 0, 7) . '%';

        // Ambil info toko dari JKS & Pareto
        $storeInfo = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->leftJoin('salesmans as s', function($join) {
                $join->on('js.distributor_code', '=', 's.distributor_code')
                     ->on('js.salesman_code', '=', 's.salesman_code');
            })
            ->where('js.distributor_code', $filters['distributor'])
            ->where('js.customer_code', $uniqKode)
            ->where('js.bulan', 'like', $bulanPrefix)
            ->select(
                'js.*',
                'ltpte.customer_code_prc',
                'ltpte.customer_name',
                'ltpte.customer_address',
                'ltpte.kecamatan',
                'ltpte.desa',
                'ltpte.pilar',
                'ltpte.target',
                's.salesman_name'
            )
            ->first();

        $this->visitModalHeader = [
            'uniq_kode' => $uniqKode,
            'customer_code' => $customerCode ?: ($storeInfo->customer_code_prc ?? '-'),
            'customer_name' => $customerName ?: ($storeInfo->customer_name ?? $uniqKode),
            'alamat' => $storeInfo->customer_address ?? '-',
            'kecamatan' => $storeInfo->kecamatan ?? '-',
            'desa' => $storeInfo->desa ?? '-',
            'salesman_name' => $storeInfo->salesman_name ?? ($storeInfo->salesman_code ?? '-'),
            'salesman_code' => $storeInfo->salesman_code ?? '-',
            'pilar' => $storeInfo->pilar ?? '-',
            'target' => $storeInfo->target ?? 0,
            'bulan' => $filters['bulan'],
        ];

        // Query riwayat kunjungan lengkap dari rpt_visit_an_h leftJoin rpt_visit_an_r
        $history = \Illuminate\Support\Facades\DB::table('rpt_visit_an_h as h')
            ->leftJoin('rpt_visit_an_r as r', function($join) {
                $join->on('h.ID', '=', 'r.HID')
                     ->on('h.BID', '=', 'r.BID')
                     ->on('h.CUSTNO', '=', 'r.CUSTNO')
                     ->on('h.TANGGAL', '=', \Illuminate\Support\Facades\DB::raw('r."TANGGAL"::text'));
            })
            ->where('h.BID', $eskalinkCode)
            ->where('h.CUSTNO', $targetCustNo)
            ->where('h.TANGGAL', 'like', $bulanPrefix)
            ->select(
                'h.ID',
                'h.TANGGAL',
                'h.MUID',
                'h.MUNAME',
                'h.FLAG_PJP',
                'h.FLAG_VISIT',
                'h.FLAG_EC',
                'h.ORDER_VAL',
                'h.ORDER_QTY',
                'h.TOTAL_ITEM',
                'h.TIME_IN',
                'h.TIME_OUT',
                'h.TIME_CONSUME',
                'h.NOTES',
                'r.REASON_TYPE',
                'r.REASON_ID',
                'r.REASON_DESC',
                'r.REASON_NOTE'
            )
            ->orderByDesc('h.TANGGAL')
            ->orderByDesc('h.TIME_IN')
            ->get();

        $this->visitModalHistory = $history->toArray();

        // Hitung total ringkasan (Pisahkan Rute PJP vs Non-Rute)
        $totalVisitRute = 0;
        $totalVisitNonRute = 0;
        $totalEcRute = 0;
        $totalEcNonRute = 0;
        $totalOrderValRute = 0;
        $totalOrderValAll = 0;
        $totalOrderQtyRute = 0;
        $totalOrderQtyAll = 0;

        foreach ($this->visitModalHistory as $h) {
            $isRute = ($h->FLAG_PJP === 'R');
            if ($h->FLAG_VISIT === 'Y') {
                if ($isRute) {
                    $totalVisitRute++;
                } else {
                    $totalVisitNonRute++;
                }
            }
            if ($h->FLAG_EC === 'Y') {
                if ($isRute) {
                    $totalEcRute++;
                } else {
                    $totalEcNonRute++;
                }
            }
            $val = (float) ($h->ORDER_VAL ?? 0);
            $qty = (int) ($h->ORDER_QTY ?? 0);

            if ($isRute) {
                $totalOrderValRute += $val;
                $totalOrderQtyRute += $qty;
            }
            $totalOrderValAll += $val;
            $totalOrderQtyAll += $qty;
        }

        $this->visitModalSummary = [
            'total_visit_route' => $totalVisitRute,
            'total_visit_non_route' => $totalVisitNonRute,
            'total_visit' => $totalVisitRute,
            'total_visit_all' => $totalVisitRute + $totalVisitNonRute,
            'total_ec' => $totalEcRute,
            'total_ec_all' => $totalEcRute + $totalEcNonRute,
            'total_order_val' => $totalOrderValRute,
            'total_order_val_all' => $totalOrderValAll,
            'total_order_qty' => $totalOrderQtyRute,
            'total_order_qty_all' => $totalOrderQtyAll,
            'strike_rate' => $totalVisitRute > 0 ? round(($totalEcRute / $totalVisitRute) * 100, 1) : 0,
        ];

        $this->showVisitDetailModal = true;
    }

    /**
     * Tutup modal rincian riwayat kunjungan.
     */
    public function closeVisitDetailModal()
    {
        $this->showVisitDetailModal = false;
        $this->visitModalHeader = null;
        $this->visitModalSummary = null;
        $this->visitModalHistory = [];
    }

    // ---------------------------------------------------------
    // COLLISION DETECTION
    // ---------------------------------------------------------
    public function showCollisionDetails($customerCode, $customerName)
    {
        $filters = $this->getAppliedFilters();
        if (empty($filters['bulan'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih bulan terlebih dahulu.']);
            return;
        }

        $this->collisionCustomerName = $customerName;
        
        // Fetch all schedules for this customer in this month
        $schedules = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('bulan', 'like', $filters['bulan'] . '%')
            ->where('customer_code', $customerCode)
            ->leftJoin('salesmans', function ($join) {
                $join->on('jks_salesmans.salesman_code', '=', 'salesmans.salesman_code')
                     ->on('jks_salesmans.distributor_code', '=', 'salesmans.distributor_code');
            })
            ->select('jks_salesmans.*', 'salesmans.salesman_name')
            ->get();

        $this->collisionDetails = $schedules->map(function($item) { return (array) $item; })->toArray();
        $this->showCollisionModal = true;
    }

    public function closeCollisionModal()
    {
        $this->showCollisionModal = false;
        $this->collisionDetails = [];
    }

    public function confirmDeleteFromCollision($id)
    {
        if (!$this->checkCanManipulate()) {
            return;
        }
        $this->showCollisionModal = false;
        $this->confirmDelete($id);
    }

    // Export Eskalink Methods
    public function openExportEskalinkModal()
    {
        if (empty($this->appliedBulan)) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pilih bulan terlebih dahulu!']);
            return;
        }
        $this->eskalinkFlagDelete = 'N';
        $this->exportEskalinkSalesman = '';
        $this->showExportEskalinkModal = true;
    }

    public function closeExportEskalinkModal()
    {
        $this->showExportEskalinkModal = false;
    }

    public function executeExportEskalink()
    {
        abort_if(!$this->canExport || !auth()->user()->hasRole(['admin', 'user', 'spm']), 403, 'Akses ditolak. Anda tidak memiliki role yang diizinkan.');

        if (empty($this->appliedBulan)) return;

        if ($this->appliedDistributor) {
            $this->abortIfUnauthorizedDistributor($this->appliedDistributor);
        }

        $fileName = 'JKS_Eskalink_' . $this->appliedBulan;
        if ($this->appliedDistributor) {
            $fileName .= '_' . $this->appliedDistributor;
        }
        if ($this->exportEskalinkSalesman) {
            $fileName .= '_' . $this->exportEskalinkSalesman;
        }
        $fileName .= '.xlsx';

        $this->closeExportEskalinkModal();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JksEskalinkExport($this->appliedBulan, $this->appliedDistributor, $this->eskalinkFlagDelete, $this->exportEskalinkSalesman), 
            $fileName
        );
    }
}
