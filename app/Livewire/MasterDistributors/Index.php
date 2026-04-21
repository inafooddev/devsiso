<?php

namespace App\Livewire\MasterDistributors;

use Livewire\Component;
use App\Models\MasterDistributor;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterBranch;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\MasterDistributorsExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $statusFilter = '';
    public $regionFilter = '';
    public $areaFilter = '';

    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $distributorIdToDelete;

    // Form Fields
    public $distributor_code;
    public $distributor_name;
    public $join_date;
    public $resign_date;
    public $latitude;
    public $longitude;
    public $is_active = true;
    public $branch_code = '';
    
    // UI Helpers
    public $branchSearch = '';
    public $selectedBranchName = '';
    public $region_name = 'N/A';
    public $area_name = 'N/A';
    public $supervisor_name = 'N/A';

    // Map Modal States
    public $isMapModalOpen = false;
    public $mapLatitude;
    public $mapLongitude;
    public $mapDistributorName;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'regionFilter' => ['except' => ''],
        'areaFilter' => ['except' => ''],
    ];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'distributor_code' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('master_distributors')->ignore($this->distributor_code, 'distributor_code')
                    : Rule::unique('master_distributors', 'distributor_code'),
            ],
            'distributor_name' => 'required|string|max:100',
            'branch_code'      => 'required|exists:master_branches,branch_code',
            'join_date'        => 'nullable|date',
            'resign_date'      => 'nullable|date',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'is_active'        => 'required|boolean',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }

        return $query;
    }

    public function updatingRegionFilter()
    {
        $this->reset('areaFilter');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Search Branches for the form.
     */
    #[Computed]
    public function branchesSearch()
    {
        if (strlen($this->branchSearch) < 2) return collect();
        
        $query = MasterBranch::query();
        $user = auth()->user();

        // Filter branches by region access
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('supervisor.area', function ($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }

        return $query->where(function($q) {
                $q->where('branch_name', 'ilike', '%' . $this->branchSearch . '%')
                  ->orWhere('branch_code', 'ilike', '%' . $this->branchSearch . '%');
            })
            ->take(5)
            ->get();
    }

    /**
     * Select a branch from search results.
     */
    public function selectBranch($branchCode, $branchName)
    {
        $this->branch_code = $branchCode;
        $this->selectedBranchName = $branchName;
        $this->branchSearch = '';
        
        $branch = MasterBranch::with(['supervisor.area.region'])->find($branchCode);
        if ($branch) {
            $this->region_name = $branch->supervisor->area->region->region_name ?? 'N/A';
            $this->area_name = $branch->supervisor->area->area_name ?? 'N/A';
            $this->supervisor_name = $branch->supervisor->supervisor_name ?? 'N/A';
        }
    }

    /**
     * CRUD Modal Operations.
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($distributorCode)
    {
        $this->resetValidation();
        $distributor = MasterDistributor::findOrFail($distributorCode);
        
        $this->distributor_code = $distributor->distributor_code;
        $this->distributor_name = $distributor->distributor_name;
        $this->join_date = $distributor->join_date ? $distributor->join_date->format('Y-m-d') : null;
        $this->resign_date = $distributor->resign_date ? $distributor->resign_date->format('Y-m-d') : null;
        $this->latitude = $distributor->latitude;
        $this->longitude = $distributor->longitude;
        $this->is_active = $distributor->is_active;

        $this->branch_code = $distributor->branch_code;
        $this->selectedBranchName = $distributor->branch_name;
        $this->region_name = $distributor->region_name;
        $this->area_name = $distributor->area_name;
        $this->supervisor_name = $distributor->supervisor_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->distributor_code = null;
        $this->distributor_name = null;
        $this->join_date = null;
        $this->resign_date = null;
        $this->latitude = null;
        $this->longitude = null;
        $this->is_active = true;
        $this->branch_code = '';
        $this->branchSearch = '';
        $this->selectedBranchName = '';
        $this->region_name = 'N/A';
        $this->area_name = 'N/A';
        $this->supervisor_name = 'N/A';
    }

    public function save()
    {
        $this->validate();

        $branch = MasterBranch::with(['supervisor.area.region'])->find($this->branch_code);
        
        $data = [
            'distributor_name' => $this->distributor_name,
            'join_date'        => $this->join_date,
            'resign_date'      => $this->resign_date,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'is_active'        => $this->is_active,
            'branch_code'      => $this->branch_code,
            'branch_name'      => $branch->branch_name ?? 'N/A',
            'supervisor_code'  => $branch->supervisor->supervisor_code ?? 'N/A',
            'supervisor_name'  => $branch->supervisor->supervisor_name ?? 'N/A',
            'area_code'        => $branch->supervisor->area->area_code ?? 'N/A',
            'area_name'        => $branch->supervisor->area->area_name ?? 'N/A',
            'region_code'      => $branch->supervisor->area->region->region_code ?? 'N/A',
            'region_name'      => $branch->supervisor->area->region->region_name ?? 'N/A',
        ];

        if ($this->isEditing) {
            MasterDistributor::where('distributor_code', $this->distributor_code)->update($data);
            session()->flash('message', 'Data distributor berhasil diperbarui.');
        } else {
            $data['distributor_code'] = $this->distributor_code;
            MasterDistributor::create($data);
            session()->flash('message', 'Distributor baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterDistributor::with('supervisor')
            ->where('distributor_code', '!=', 'HOINA');

        $this->applyRegionAccess($query);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('distributor_code', 'ilike', '%' . $search . '%')
                  ->orWhere('distributor_name', 'ilike', '%' . $search . '%')
                  ->orWhere('branch_name', 'ilike', '%' . $search . '%')
                  ->orWhere('supervisor_name', 'ilike', '%' . $search . '%')
                  ->orWhereHas('supervisor', function ($qs) use ($search) {
                      $qs->where('description', 'ilike', "%$search%");
                  });
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        if ($this->regionFilter) {
            $query->where('region_code', $this->regionFilter)->where('region_code', '!=', 'HOINA');
        }

        if ($this->areaFilter) {
            $query->where('area_code', $this->areaFilter);
        }

        $distributors = $query->latest()->paginate(10);
        
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA');
        $this->applyRegionAccess($regionQuery);
        $regions = $regionQuery->orderBy('region_name')->get();

        $areas = $this->regionFilter ? MasterArea::where('region_code', $this->regionFilter)->where('region_code', '!=', 'HOINA')->orderBy('area_name')->get() : collect();

        return view('livewire.master-distributors.index', [
            'distributors' => $distributors,
            'regions' => $regions,
            'areas' => $areas
        ])->layout('layouts.app');
    }

    public function synchronize()
    {
        if (!auth()->user()->hasRole('admin')) {
            session()->flash('error', 'Akses ditolak: Hanya Administrator yang dapat melakukan sinkronisasi data massal.');
            return;
        }

        try {
            DB::statement("
                UPDATE master_distributors md
                SET
                    branch_name = mb.branch_name,
                    supervisor_name = ms.supervisor_name,
                    area_name = ma.area_name,
                    region_name = mr.region_name,
                    supervisor_code = ms.supervisor_code
                FROM master_branches mb
                JOIN master_supervisors ms ON mb.supervisor_code = ms.supervisor_code
                JOIN master_areas ma ON ms.area_code = ma.area_code
                JOIN master_regions mr ON ma.region_code = mr.region_code
                WHERE md.branch_code = mb.branch_code
            ");

            session()->flash('message', 'Sinkronisasi data distributor berhasil diselesaikan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
        }
    }

    public function confirmDelete($distributorCode)
    {
        $this->distributorIdToDelete = $distributorCode;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $query = MasterDistributor::query();
        $this->applyRegionAccess($query);

        $distributor = $query->where('distributor_code', $this->distributorIdToDelete)->first();

        if ($distributor) {
            $distributor->delete();
            session()->flash('message', 'Distributor berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus distributor ini.');
        }

        $this->isDeleteModalOpen = false;
    }

    public function showMap($distributorCode)
    {
        $query = MasterDistributor::query();
        $this->applyRegionAccess($query);

        $distributor = $query->where('distributor_code', $distributorCode)->first();
        
        if (!$distributor) {
            session()->flash('error', 'Distributor tidak ditemukan atau berada di luar otoritas Anda.');
            return;
        }
        
        if ($distributor->latitude && $distributor->longitude) {
            $this->mapLatitude = $distributor->latitude;
            $this->mapLongitude = $distributor->longitude;
            $this->mapDistributorName = $distributor->distributor_name;
            $this->isMapModalOpen = true;
            $this->dispatch('map-opened');
        } else {
            session()->flash('error', 'Koordinat lokasi tidak tersedia untuk distributor ini.');
        }
    }

    public function export()
    {
        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!empty($finalRegionFilter) && !in_array($finalRegionFilter, $user->region_code)) {
                $finalRegionFilter = ''; 
            }
        }

        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'regionFilter' => $finalRegionFilter,
            'areaFilter' => $this->areaFilter,
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [], 
        ];

        return Excel::download(new MasterDistributorsExport($filters), 'master_distributors.xlsx');
    }
}