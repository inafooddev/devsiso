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
            $this->appliedBulan = $state['appliedBulan'] ?? date('Y-m-01');
            
            $this->selectedRegion = $state['selectedRegion'] ?? '';
            $this->selectedArea = $state['selectedArea'] ?? '';
            $this->selectedSupervisor = $state['selectedSupervisor'] ?? '';
            $this->selectedDistributor = $state['selectedDistributor'] ?? '';
            $this->selectedBulan = $state['selectedBulan'] ?? date('Y-m-01');

            $this->search = $state['search'] ?? '';
            $this->selected = $state['selected'] ?? [];
            $this->selectAll = $state['selectAll'] ?? false;
            
            if (isset($state['page'])) {
                $this->setPage($state['page']);
            }
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
            
        if ($this->selectedRegion) {
            $query->where('region_code', $this->selectedRegion);
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
            
        if ($this->selectedArea) {
            $query->where('area_code', $this->selectedArea);
        } elseif ($this->selectedRegion) {
            $query->where('region_code', $this->selectedRegion);
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
            
        if ($this->selectedRegion) $query->where('region_code', $this->selectedRegion);
        if ($this->selectedArea) $query->where('area_code', $this->selectedArea);
        if ($this->selectedSupervisor) $query->where('supervisor_code', $this->selectedSupervisor);
        
        $query = $this->applyHierarchyAccess($query);
        
        return $query->distinct()->orderBy('distributor_name')->get();
    }

    protected function applyHierarchyAccess($query, $prefix = '')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole(['admin', 'spm'])) return $query;
        
        $accessLevel = $user->getAccessLevel();
        
        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            // jks_se_master_toko_ool stores team_elite_code in supervisor_code, 
            // and $user->supervisor_code is also team_elite_code.
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
            if (isset($filters['bulan'])) unset($filters['bulan']);
            
            $data = $service->getFilteredPaginatedList($this->search, $filters, 100);
            
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
        $this->editingDistributorCode = $distCode;
        $this->editingCustomerCode = $custCode;
        $this->editingCustomerName = $custName;
        $this->editingRemark = $existingRemark;
        $this->remarkModalOpen = true;
    }

    public function saveRemark()
    {
        abort_if(!$this->canEdit, 403, 'Akses ditolak.');

        \Illuminate\Support\Facades\DB::table('jks_se_toko_ool_remarks')->updateOrInsert(
            [
                'distributor_code' => $this->editingDistributorCode,
                'customer_code' => $this->editingCustomerCode,
            ],
            [
                'remark' => $this->editingRemark,
                'created_by' => auth()->user()->nik ?? 'system',
                'updated_at' => now(),
            ]
        );

        $this->remarkModalOpen = false;
        $this->dispatch('show-toast', type: 'success', message: 'Remark berhasil disimpan!');
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

        $outlet = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')->where('id', $outletId)->first();
        if (!$outlet) return;

        $this->formOutlets = [(array)$outlet];
        $this->bulkDistributorCode = $outlet->distributor_code;

        
        $this->formSalesman = '';
        $this->formBulan = date('Y-m-01'); // Default current month
        $this->formH = [];
        $this->formW = [];

        $this->addJksModalOpen = true;
    }

    public function openBulkAddJksModal()
    {
        abort_if(!$this->canAdd, 403, 'Akses ditolak.');

        if (empty($this->selected)) {
            $this->dispatch('show-toast', type: 'error', message: 'Pilih minimal satu outlet.');
            return;
        }

        $outlets = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->whereIn('id', $this->selected)
            ->get();

        if ($outlets->isEmpty()) return;

        $distributorCodes = $outlets->pluck('distributor_code')->unique();

        if ($distributorCodes->count() > 1) {
            $this->dispatch('show-toast', type: 'error', message: 'Semua outlet yang dipilih harus dari distributor yang sama!');
            return;
        }

        $this->formOutlets = $outlets->map(fn($o) => (array)$o)->toArray();
        $this->bulkDistributorCode = $distributorCodes->first();
        
        $this->formSalesman = '';
        $this->formBulan = date('Y-m-01');
        $this->formH = [];
        $this->formW = [];

        $this->addJksModalOpen = true;
    }

    #[Computed]
    public function getJksSalesmanOptionsProperty()
    {
        if (empty($this->bulkDistributorCode)) return [];

        return \Illuminate\Support\Facades\DB::table('salesmans')
            ->select('salesman_code', 'salesman_name')
            ->where('distributor_code', $this->bulkDistributorCode)
            ->orderBy('salesman_name')
            ->get();
    }

    #[Computed]
    public function getCalendarDaysProperty()
    {
        if (empty($this->formBulan)) return [];
        
        $parts = explode('-', $this->formBulan);
        if (count($parts) !== 2) return [];
        
        $year = (int)$parts[0];
        $month = (int)$parts[1];
        
        $days = \Illuminate\Support\Facades\DB::table('master_calender')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('date', 'asc')
            ->get();
            
        if ($days->isEmpty()) return ['days' => collect(), 'offset' => 0];

        // day_number: 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu
        // We want Sunday as first column, so Sunday=0 offset, Senin=1, etc.
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

        // Construct payload
        $hariArray = array_map(function($h) { return 'h' . $h; }, $this->formH);
        $mingguArray = array_map(function($w) { return 'w' . $w; }, $this->formW);

        // Construct tokos array
        $tokos = [];
        foreach ($this->formOutlets as $outlet) {
            $tokos[] = [
                'code' => $outlet['customer_code'],
                'name' => $outlet['customer_name']
            ];
        }

        $payload = [
            'distributor_code' => $this->bulkDistributorCode,
            'customer_code' => count($tokos) === 1 ? $tokos[0]['code'] : null, // Retained for backward compatibility
            'salesman_code' => $this->formSalesman,
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
        $this->dispatch('show-toast', type: 'success', message: 'Pengajuan Tambah ke JKS berhasil dikirim (Status: Menunggu).');
    }

    public function exportExcel()
    {
        abort_if(!$this->canExport, 403, 'Akses ditolak.');

        if (empty($this->appliedArea) && empty($this->appliedSupervisor) && empty($this->appliedDistributor)) {
            $this->dispatch('show-toast', type: 'warning', message: 'Silakan pilih filter minimal setingkat Area untuk melakukan export.');
            return;
        }

        $filters = $this->getAppliedFilters();
        if (isset($filters['bulan'])) {
            unset($filters['bulan']);
        }

        $fileName = 'Outlet_Non_JKS_' . date('Ymd_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JksNonRouteExport($this->search, $filters), 
            $fileName
        );
    }

    public function render(JksNonRouteService $service)
    {
        $distributorOptions = $this->filterDistributors;
        
        if (empty($this->appliedDistributor) && $distributorOptions->count() > 0) {
            $this->appliedDistributor = $distributorOptions->first()->distributor_code;
            $this->selectedDistributor = $this->appliedDistributor;
        }

        // Get applied filters from the trait
        $filters = $this->getAppliedFilters();
        
        // Remove 'bulan' filter since user requested it shouldn't exist
        if (isset($filters['bulan'])) {
            unset($filters['bulan']);
        }
        
        $data = $service->getFilteredPaginatedList($this->search, $filters, 100);

        return view('livewire.call-plan.jks-non-route.index', [
            'outlets' => $data,
            'regionOptions' => $this->filterRegions,
            'areaOptions' => $this->filterAreas,
            'supervisorOptions' => $this->filterSupervisors,
            'distributorOptions' => $distributorOptions,
        ]);
    }
}
