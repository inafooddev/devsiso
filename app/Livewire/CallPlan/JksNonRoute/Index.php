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

    public function updatingSearch()
    {
        $this->resetPage();
        $this->resetSelection();
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
        $outlet = \Illuminate\Support\Facades\DB::table('jks_se_master_toko_ool')->where('id', $outletId)->first();
        if (!$outlet) return;

        $this->formOutlets = [(array)$outlet];
        $this->bulkDistributorCode = $outlet->distributor_code;

        
        $this->formSalesman = '';
        $this->formBulan = date('Y-m'); // Default current month
        $this->formH = [];
        $this->formW = [];

        $this->addJksModalOpen = true;
    }

    public function openBulkAddJksModal()
    {
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
        $this->formBulan = date('Y-m');
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
