<?php

namespace App\Livewire\CallPlan\JksNonRoute;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Traits\WithCallPlanFilters;
use App\Services\JksNonRouteService;

class Index extends Component
{
    use WithPagination;
    use WithCallPlanFilters;

    #[Title('Outlet Non JKS')]
    #[Layout('layouts.app')]

    public $search = '';
    
    // Bulk action states
    public $selected = [];
    public $selectAll = false;
    
    // Permissions
    public $canExport = false;
    public $canAdd = false;
    public $canEdit = false;
    
    // Access Level for UI Logic
    public $accessLevel = 'nasional';

    
    public function mount()
    {
        $this->canExport = auth()->check() && auth()->user()->hasMenuAccess('call-plan.jks-salesmans', 'can_export');
        $this->canAdd = auth()->check() && auth()->user()->hasMenuAccess('call-plan.jks-salesmans', 'can_add');
        $this->canEdit = auth()->check() && auth()->user()->hasMenuAccess('call-plan.jks-salesmans', 'can_edit');
        
        $user = auth()->user();
        if ($user) {
            $this->accessLevel = $user->getAccessLevel();
        }

        if (session()->has('jks_non_route_state')) {
            $state = session()->get('jks_non_route_state');
            $this->appliedRegion = $state['appliedRegion'] ?? '';
            $this->appliedArea = $state['appliedArea'] ?? '';
            $this->appliedSupervisor = $state['appliedSupervisor'] ?? '';
            $this->appliedDistributor = $state['appliedDistributor'] ?? '';
            $this->appliedBulan = $state['appliedBulan'] ?? date('Y-m');
            
            $this->selectedRegion = $state['selectedRegion'] ?? '';
            $this->selectedArea = $state['selectedArea'] ?? '';
            $this->selectedSupervisor = $state['selectedSupervisor'] ?? '';
            $this->selectedDistributor = $state['selectedDistributor'] ?? '';
            $this->selectedBulan = $state['selectedBulan'] ?? date('Y-m');

            $this->search = $state['search'] ?? '';
            $this->selected = $state['selected'] ?? [];
            $this->selectAll = $state['selectAll'] ?? false;
            
            if (isset($state['page'])) {
                $this->setPage($state['page']);
            }
        }
        
        if (empty($this->appliedBulan)) {
            $this->appliedBulan = date('Y-m');
            $this->selectedBulan = date('Y-m');
        } else {
            $this->appliedBulan = substr($this->appliedBulan, 0, 7);
            $this->selectedBulan = $this->appliedBulan;
        }
        
        $this->applyDefaultFiltersForRestrictedUser();
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
                        $areaData = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
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
                    
                    $spvData = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
                        ->where('supervisor_code', $user->supervisor_code)
                        ->select('region_code', 'area_code')
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
        session()->put('jks_non_route_state', [
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
            'selected' => $this->selected,
            'selectAll' => $this->selectAll,
            'page' => $this->paginators['page'] ?? $this->getPage(),
        ]);
    }

    public function resetFilters()
    {
        $this->reset([
            'selectedRegion', 'selectedArea', 'selectedSupervisor', 'selectedDistributor',
            'appliedRegion', 'appliedArea', 'appliedSupervisor', 'appliedDistributor',
            'search', 'selected', 'selectAll'
        ]);

        if (session()->has('jks_non_route_state')) {
            session()->forget('jks_non_route_state');
        }

        $this->appliedBulan = date('Y-m');
        $this->selectedBulan = date('Y-m');

        $this->resetPage();
        $this->applyDefaultFiltersForRestrictedUser();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    #[Computed]
    public function filterRegions()
    {
        $query = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct();

        return $this->applyHierarchyAccess($query)->orderBy('region_name')->get();
    }

    #[Computed]
    public function filterAreas()
    {
        $query = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->select('area_code', 'area_name')
            ->whereNotNull('area_code');
            
        $region = $this->appliedRegion ?: $this->selectedRegion;
        if ($region) {
            $query->where('region_code', $region);
        }
        
        $query = $this->applyHierarchyAccess($query);
        
        return $query->distinct()->orderBy('area_name')->get();
    }

    #[Computed]
    public function filterSupervisors()
    {
        $query = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->select('supervisor_code', \Illuminate\Support\Facades\DB::raw('supervisor_name as description'))
            ->whereNotNull('supervisor_code');
            
        $area = $this->appliedArea ?: $this->selectedArea;
        $region = $this->appliedRegion ?: $this->selectedRegion;

        if ($area) {
            $query->where('area_code', $area);
        } elseif ($region) {
            $query->where('region_code', $region);
        }
        
        $query = $this->applyHierarchyAccess($query);
        
        return $query->distinct()->orderBy('description')->get();
    }
    

    #[Computed]
    public function filterDistributors()
    {
        $query = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->select('distributor_code', 'distributor_name')
            ->whereNotNull('distributor_code');
            
        $supervisor = $this->appliedSupervisor ?: $this->selectedSupervisor;
        $area = $this->appliedArea ?: $this->selectedArea;
        $region = $this->appliedRegion ?: $this->selectedRegion;

        if ($supervisor) {
            $query->where('supervisor_code', $supervisor);
        } elseif ($area) {
            $query->where('area_code', $area);
        } elseif ($region) {
            $query->where('region_code', $region);
        }
        
        $query = $this->applyHierarchyAccess($query);
        
        return $query->distinct()->orderBy('distributor_name')->get();
    }

    protected function applyHierarchyAccess($query, $prefix = '')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole(['admin', 'spm'])) return $query;
        
        $accessLevel = $user->getAccessLevel();
        
        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            return $query->whereIn($prefix . 'supervisor_code', (array) $user->supervisor_code);
        }
        
        if ($accessLevel === 'area' && !empty($user->area_code)) {
            return $query->whereIn($prefix . 'area_code', (array) $user->area_code);
        }
        
        if ($accessLevel === 'region' && !empty($user->region_code)) {
            return $query->whereIn($prefix . 'region_code', (array) $user->region_code);
        }
        
        return $query;
    }

    public function updatedAppliedRegion()
    {
        $this->selectedRegion = $this->appliedRegion;
        $this->appliedArea = '';
        $this->selectedArea = '';
        $this->appliedSupervisor = '';
        $this->selectedSupervisor = '';
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedAppliedArea()
    {
        $this->selectedArea = $this->appliedArea;
        $this->appliedSupervisor = '';
        $this->selectedSupervisor = '';
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedAppliedSupervisor()
    {
        $this->selectedSupervisor = $this->appliedSupervisor;
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedAppliedDistributor()
    {
        $this->selectedDistributor = $this->appliedDistributor;

        if (!empty($this->appliedDistributor)) {
            // Lookup hierarchy directly from jks_se_master_toko_ool to prevent code mismatch
            $distInfo = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
                ->where('distributor_code', $this->appliedDistributor)
                ->first();

            if ($distInfo) {
                if (!empty($distInfo->region_code)) {
                    $this->appliedRegion = $distInfo->region_code;
                    $this->selectedRegion = $this->appliedRegion;
                }
                if (!empty($distInfo->area_code)) {
                    $this->appliedArea = $distInfo->area_code;
                    $this->selectedArea = $this->appliedArea;
                }
                if (!empty($distInfo->supervisor_code)) {
                    $this->appliedSupervisor = $distInfo->supervisor_code;
                    $this->selectedSupervisor = $this->appliedSupervisor;
                } else {
                    $this->appliedSupervisor = '';
                    $this->selectedSupervisor = '';
                }
            }
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedAppliedBulan()
    {
        $this->selectedBulan = $this->appliedBulan;
        $this->resetPage();
        $this->resetSelection();
    }

    private function resetSelection()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $service = app(JksNonRouteService::class);
            $filters = $this->getAppliedFilters();
            $filters['bulan'] = $this->appliedBulan;
            
            // Explicitly pass current page so selectAll selects the items visible on the current page
            $data = $service->getFilteredPaginatedList($this->search, $filters, 100, $this->getPage());
            
            // Only select items that don't have pending approvals
            $this->selected = collect($data->items())
                ->whereNull('pending_approval_id')
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public $remarkModalOpen = false;
    public $editingDistributorCode = '';
    public $editingCustomerCode = '';
    public $editingCustomerName = '';
    public $editingRemark = '';

    public function openRemarkModal($distCode, $custCode, $custName, $existingRemark)
    {
        $this->editingDistributorCode = strtoupper(trim($distCode));
        $this->editingCustomerCode = strtoupper(preg_replace('/\s+/', ' ', trim($custCode)));
        $this->editingCustomerName = trim($custName);
        $this->editingRemark = trim($existingRemark);
        $this->remarkModalOpen = true;
    }

    public function saveRemark()
    {
        abort_if(!$this->canEdit, 403, 'Akses ditolak.');

        $distCode = strtoupper(trim($this->editingDistributorCode));
        $custCode = strtoupper(preg_replace('/\s+/', ' ', trim($this->editingCustomerCode)));

        \Illuminate\Support\Facades\DB::table('jks_se_toko_ool_remarks')->updateOrInsert(
            [
                'distributor_code' => $distCode,
                'customer_code' => $custCode,
            ],
            [
                'remark' => trim($this->editingRemark),
                'created_by' => auth()->user()->nik ?? 'system',
                'updated_at' => now(),
            ]
        );

        $this->remarkModalOpen = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Remark berhasil disimpan!']);
    }

    // --- TAMBAH KE JKS ---
    public $addJksModalOpen = false;
    public $formOutlets = []; // holds an array of selected outlet data
    public $bulkDistributorCode = '';
    public $formSalesman = '';
    public $formBulan = '';
    public $formH = [];
    public $formW = [];

    public function openAddJksModal($outletId)
    {
        abort_if(!$this->canAdd, 403, 'Akses ditolak.');
        $this->resetErrorBag();
        $this->resetValidation();

        $outlet = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')->where('id', $outletId)->first();
        if (!$outlet) return;

        $this->formOutlets = [(array)$outlet];
        $this->bulkDistributorCode = strtoupper(trim($outlet->distributor_code));

        $this->formSalesman = '';
        $this->formBulan = now()->format('Y-m'); // Default current running month
        $this->formH = [];
        $this->formW = [];

        $this->addJksModalOpen = true;
    }

    public function openBulkAddJksModal()
    {
        abort_if(!$this->canAdd, 403, 'Akses ditolak.');
        $this->resetErrorBag();
        $this->resetValidation();

        if (empty($this->selected)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih minimal satu outlet.']);
            return;
        }

        $outlets = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->whereIn('id', $this->selected)
            ->get();

        if ($outlets->isEmpty()) return;

        $distributorCodes = $outlets->pluck('distributor_code')->map(fn($d) => strtoupper(trim($d)))->unique();

        if ($distributorCodes->count() > 1) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Semua outlet yang dipilih harus dari distributor yang sama!']);
            return;
        }

        $this->formOutlets = $outlets->map(fn($o) => (array)$o)->toArray();
        $this->bulkDistributorCode = $distributorCodes->first();
        
        $this->formSalesman = '';
        $this->formBulan = now()->format('Y-m');
        $this->formH = [];
        $this->formW = [];

        $this->addJksModalOpen = true;
    }

    #[Computed]
    public function jksSalesmanOptions()
    {
        if (empty($this->bulkDistributorCode)) return [];

        return \Illuminate\Support\Facades\DB::table('salesmans')
            ->select('salesman_code', 'salesman_name')
            ->where('distributor_code', $this->bulkDistributorCode)
            ->orderBy('salesman_name')
            ->get();
    }

    #[Computed]
    public function calendarDays()
    {
        if (empty($this->formBulan)) return ['days' => collect(), 'offset' => 0];
        
        $parts = explode('-', $this->formBulan);
        if (count($parts) !== 2) return ['days' => collect(), 'offset' => 0];
        
        $year = (int)$parts[0];
        $month = (int)$parts[1];
        
        $days = \Illuminate\Support\Facades\DB::table('master_calender')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('date', 'asc')
            ->get();
            
        if ($days->isEmpty()) return ['days' => collect(), 'offset' => 0];

        $firstDayNumber = $days->first()->day_number;
        $offsetMap = [7 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6];
        $offset = $offsetMap[$firstDayNumber] ?? 0;
        
        return [
            'days' => $days,
            'offset' => $offset
        ];
    }

    public function submitAddJks()
    {
        abort_if(!$this->canAdd, 403, 'Akses ditolak.');

        $user = auth()->user();
        $currentMonth = now()->format('Y-m');
        $targetMonth = substr($this->formBulan, 0, 7);

        if (!$user || (!$user->hasRole('admin') && $targetMonth !== $currentMonth)) {
            $this->dispatch('toast', [
                'type' => 'error', 
                'message' => 'Akses Ditolak: Pengajuan penambahan jadwal hanya diizinkan untuk periode bulan berjalan (' . now()->translatedFormat('F Y') . ').'
            ]);
            return;
        }

        $this->validate([
            'formSalesman' => 'required',
            'formBulan' => 'required',
            'formH' => 'required|array|min:1',
            'formW' => 'required|array|min:1',
        ], [
            'formSalesman.required' => 'Salesman harus dipilih.',
            'formBulan.required' => 'Bulan target wajib diisi.',
            'formH.required' => 'Pilih minimal 1 hari kunjungan.',
            'formW.required' => 'Pilih minimal 1 minggu kunjungan.',
        ]);

        // Construct tokos array with strict sanitization
        $tokos = [];
        foreach ($this->formOutlets as $outlet) {
            $code = strtoupper(preg_replace('/\s+/', ' ', trim($outlet['customer_code'])));
            $tokos[] = [
                'code' => $code,
                'name' => trim($outlet['customer_name'])
            ];
        }

        $tokoCodes = array_column($tokos, 'code');

        // Backend Duplicate Check 1: Check if already present in jks_salesmans
        $existingInJks = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('distributor_code', $this->bulkDistributorCode)
            ->where('bulan', 'like', $targetMonth . '%')
            ->whereIn('customer_code', $tokoCodes)
            ->pluck('customer_code')
            ->toArray();

        if (!empty($existingInJks)) {
            $this->dispatch('toast', [
                'type' => 'error', 
                'message' => 'Gagal: Toko (' . implode(', ', $existingInJks) . ') sudah memiliki jadwal rute JKS aktif di bulan ini.'
            ]);
            return;
        }

        // Backend Duplicate Check 2: Check if any toko already has a PENDING approval
        $pendingApprovals = \Illuminate\Support\Facades\DB::table('jks_approvals')
            ->where('distributor_code', $this->bulkDistributorCode)
            ->where('status', 'PENDING')
            ->where('action_type', 'TAMBAH_JADWAL')
            ->where(function($q) use ($targetMonth) {
                $q->whereRaw("payload->>'bulan' LIKE ?", [$targetMonth . '%'])
                  ->orWhereRaw("payload->>'bulan' IS NULL");
            })
            ->get();

        foreach ($pendingApprovals as $p) {
            $payloadData = json_decode($p->payload, true);
            $pendingCodes = [];
            if (!empty($payloadData['customer_code'])) {
                $pendingCodes[] = strtoupper(trim($payloadData['customer_code']));
            }
            if (!empty($payloadData['tokos'])) {
                foreach ($payloadData['tokos'] as $pt) {
                    if (!empty($pt['code'])) {
                        $pendingCodes[] = strtoupper(trim($pt['code']));
                    }
                }
            }
            $intersect = array_intersect($tokoCodes, $pendingCodes);
            if (!empty($intersect)) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Gagal: Toko (' . implode(', ', $intersect) . ') sudah memiliki pengajuan penambahan yang berstatus PENDING.'
                ]);
                return;
            }
        }

        $hariArray = array_map(function($h) { return 'h' . $h; }, $this->formH);
        $mingguArray = array_map(function($w) { return 'w' . $w; }, $this->formW);

        $payload = [
            'distributor_code' => $this->bulkDistributorCode,
            'customer_code' => count($tokos) === 1 ? $tokos[0]['code'] : null,
            'salesman_code' => strtoupper(trim($this->formSalesman)),
            'bulan' => \Carbon\Carbon::parse($this->formBulan)->format('Y-m-01'),
            'hari' => $hariArray,
            'minggu' => $mingguArray,
            'tokos' => $tokos
        ];

        // Insert to jks_approvals
        \Illuminate\Support\Facades\DB::table('jks_approvals')->insert([
            'distributor_code' => $this->bulkDistributorCode,
            'action_type' => 'TAMBAH_JADWAL',
            'status' => 'PENDING',
            'payload' => json_encode($payload),
            'reason' => count($tokos) === 1 ? 'Pengajuan penambahan outlet Non JKS' : 'Pengajuan penambahan ' . count($tokos) . ' outlet Non JKS secara massal',
            'maker_id' => auth()->user()->id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->resetSelection();
        $this->addJksModalOpen = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan Tambah ke JKS berhasil dikirim (Status: Menunggu).']);
    }

    public function exportExcel()
    {
        abort_if(!$this->canExport, 403, 'Akses ditolak.');

        if (empty($this->appliedArea) && empty($this->appliedSupervisor) && empty($this->appliedDistributor)) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Silakan pilih filter minimal setingkat Area untuk melakukan export.']);
            return;
        }

        $filters = $this->getAppliedFilters();
        $filters['bulan'] = $this->appliedBulan;

        $fileName = 'Outlet_Non_JKS_' . date('Ymd_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JksNonRouteExport($this->search, $filters), 
            $fileName
        );
    }

    public function render(JksNonRouteService $service)
    {
        $distributorOptions = $this->filterDistributors;

        // Get applied filters from the trait
        $filters = $this->getAppliedFilters();
        $filters['bulan'] = $this->appliedBulan;
        
        $data = $service->getFilteredPaginatedList($this->search, $filters, 100, $this->getPage());
        
        $historyLabels = [];
        $baseBulan = $this->appliedBulan ?: date('Y-m-01');
        for ($i = 1; $i <= 6; $i++) {
            $historyLabels[] = \Carbon\Carbon::parse($baseBulan)->subMonths($i)->translatedFormat('M y');
        }

        $pendingApprovalsCount = \App\Livewire\CallPlan\JksApprovals\Index::getPendingCount();

        return view('livewire.call-plan.jks-non-route.index', [
            'outlets' => $data,
            'regionOptions' => $this->filterRegions,
            'areaOptions' => $this->filterAreas,
            'supervisorOptions' => $this->filterSupervisors,
            'distributorOptions' => $distributorOptions,
            'historyLabels' => $historyLabels,
            'pendingApprovalsCount' => $pendingApprovalsCount,
        ]);
    }
}
