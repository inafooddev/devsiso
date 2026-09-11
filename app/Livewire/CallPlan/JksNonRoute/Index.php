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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function filterRegions()
    {
        return \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')
            ->select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();
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
        
        return $query->distinct()->orderBy('distributor_name')->get();
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
    }

    public function updatedAppliedArea()
    {
        $this->selectedArea = $this->appliedArea;
        $this->appliedSupervisor = '';
        $this->selectedSupervisor = '';
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->resetPage();
    }

    public function updatedAppliedSupervisor()
    {
        $this->selectedSupervisor = $this->appliedSupervisor;
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->resetPage();
    }

    public function updatedAppliedDistributor()
    {
        $this->selectedDistributor = $this->appliedDistributor;
        $this->resetPage();
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
    public $formOutlet = []; // holds the selected outlet data
    public $formSalesman = '';
    public $formBulan = '';
    public $formH = [];
    public $formW = [];

    public function openAddJksModal($outletId)
    {
        $outlet = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')->where('id', $outletId)->first();
        if (!$outlet) return;

        $this->formOutlet = (array)$outlet;
        
        $this->formSalesman = '';
        $this->formBulan = date('Y-m'); // Default current month
        $this->formH = [];
        $this->formW = [];

        $this->addJksModalOpen = true;
    }

    #[Computed]
    public function getJksSalesmanOptionsProperty()
    {
        if (empty($this->formOutlet['distributor_code'])) return [];

        return \Illuminate\Support\Facades\DB::table('salesmans')
            ->select('salesman_code', 'salesman_name')
            ->where('distributor_code', $this->formOutlet['distributor_code'])
            ->orderBy('salesman_name')
            ->get();
    }

    public function submitAddJks()
    {
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

        $payload = [
            'distributor_code' => $this->formOutlet['distributor_code'],
            'customer_code' => $this->formOutlet['customer_code'], // Retained for pending join in JksNonRouteService
            'salesman_code' => $this->formSalesman,
            'bulan' => $this->formBulan,
            'hari' => $hariArray,
            'minggu' => $mingguArray,
            'tokos' => [
                [
                    'code' => $this->formOutlet['customer_code'],
                    'name' => $this->formOutlet['customer_name']
                ]
            ]
        ];

        // Insert to jks_approvals
        \Illuminate\Support\Facades\DB::table('jks_approvals')->insert([
            'distributor_code' => $this->formOutlet['distributor_code'],
            'action_type' => 'TAMBAH_JADWAL',
            'status' => 'PENDING',
            'payload' => json_encode($payload),
            'reason' => 'Pengajuan penambahan outlet Non JKS',
            'maker_id' => auth()->user()->id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addJksModalOpen = false;
        $this->dispatch('show-toast', type: 'success', message: 'Pengajuan Tambah ke JKS berhasil dikirim (Status: Menunggu).');
    }

    public function render(JksNonRouteService $service)
    {
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
            'distributorOptions' => $this->filterDistributors,
        ]);
    }
}
