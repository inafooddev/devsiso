<?php

namespace App\Livewire\MasterBranches;

use Livewire\Component;
use App\Models\MasterBranch;
use App\Models\MasterSupervisor;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    
    // Form Fields
    public $branchId;
    public $branch_code;
    public $branch_name;
    public $supervisor_code = '';
    public $selectedRegion = '';
    public $selectedArea = '';
    
    public $branchIdToDelete;

    protected $queryString = ['search'];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'branch_code' => [
                'required',
                'string',
                'max:15',
                $this->isEditing 
                    ? Rule::unique('master_branches')->ignore($this->branchId, 'branch_code')
                    : Rule::unique('master_branches', 'branch_code'),
            ],
            'branch_name'     => 'required|string|max:50',
            'selectedRegion'  => 'required',
            'selectedArea'    => 'required',
            'supervisor_code' => 'required|exists:master_supervisors,supervisor_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'supervisor_code.required' => 'Silakan pilih salah satu supervisor.',
            'selectedRegion.required'  => 'Silakan pilih region terlebih dahulu.',
            'selectedArea.required'    => 'Silakan pilih area terlebih dahulu.',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $type = 'branch')
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if ($type === 'branch') {
                $query->whereHas('supervisor.area', function ($areaQuery) use ($user) {
                    $areaQuery->whereIn('region_code', $user->region_code);
                });
            } elseif ($type === 'supervisor') {
                $query->whereHas('area', function ($areaQuery) use ($user) {
                    $areaQuery->whereIn('region_code', $user->region_code);
                });
            } elseif ($type === 'area') {
                $query->whereIn('region_code', $user->region_code);
            } elseif ($type === 'region') {
                $query->whereIn('region_code', $user->region_code);
            }
        }

        return $query;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Dependent dropdown logic.
     */
    public function updatedSelectedRegion()
    {
        $this->selectedArea = '';
        $this->supervisor_code = '';
    }

    public function updatedSelectedArea()
    {
        $this->supervisor_code = '';
    }

    /**
     * Computed properties for dropdowns.
     */
    #[Computed]
    public function regions()
    {
        $query = MasterRegion::orderBy('region_name');
        $this->applyRegionAccess($query, 'region');
        return $query->get();
    }

    #[Computed]
    public function areas()
    {
        if (!$this->selectedRegion) return collect();
        $query = MasterArea::where('region_code', $this->selectedRegion)->orderBy('area_name');
        $this->applyRegionAccess($query, 'area');
        return $query->get();
    }

    #[Computed]
    public function supervisors()
    {
        if (!$this->selectedArea) return collect();
        $query = MasterSupervisor::where('area_code', $this->selectedArea)->orderBy('supervisor_name');
        $this->applyRegionAccess($query, 'supervisor');
        return $query->get();
    }

    /**
     * Membuka modal untuk tambah data.
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    /**
     * Membuka modal untuk edit data.
     */
    public function openEditModal($branchCode)
    {
        $this->resetValidation();
        $branch = MasterBranch::with(['supervisor.area'])->findOrFail($branchCode);
        
        $this->branchId        = $branch->branch_code;
        $this->branch_code     = $branch->branch_code;
        $this->branch_name     = $branch->branch_name;
        $this->supervisor_code = $branch->supervisor_code;

        if ($branch->supervisor) {
            $this->selectedArea = $branch->supervisor->area_code;
            if ($branch->supervisor->area) {
                $this->selectedRegion = $branch->supervisor->area->region_code;
            }
        }
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->branchId = null;
        $this->branch_code = null;
        $this->branch_name = null;
        $this->supervisor_code = '';
        $this->selectedRegion = '';
        $this->selectedArea = '';
    }

    /**
     * Menyimpan atau memperbarui data cabang.
     */
    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $branch = MasterBranch::find($this->branchId);
            $branch->update([
                'branch_code'     => $this->branch_code,
                'branch_name'     => $this->branch_name,
                'supervisor_code' => $this->supervisor_code,
            ]);
            session()->flash('message', 'Data cabang berhasil diperbarui.');
        } else {
            MasterBranch::create([
                'branch_code'     => $this->branch_code,
                'branch_name'     => $this->branch_name,
                'supervisor_code' => $this->supervisor_code,
            ]);
            session()->flash('message', 'Cabang baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterBranch::with(['supervisor.area.region'])
            ->select('master_branches.*')
            ->leftJoin('master_supervisors', 'master_branches.supervisor_code', '=', 'master_supervisors.supervisor_code')
            ->leftJoin('master_areas', 'master_supervisors.area_code', '=', 'master_areas.area_code')
            ->where('master_branches.branch_code', '!=', 'HOINA');

        $this->applyRegionAccess($query, 'branch');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('master_branches.branch_code', 'ilike', '%' . $this->search . '%')
                    ->orWhere('master_branches.branch_name', 'ilike', '%' . $this->search . '%')
                    ->orWhereHas('supervisor', function ($supervisorQuery) {
                        $supervisorQuery
                            ->where('supervisor_name', 'ilike', '%' . $this->search . '%')
                            ->orWhere('description', 'ilike', '%' . $this->search . '%')
                            ->orWhereHas('area', function ($areaQuery) {
                                $areaQuery
                                    ->where('area_name', 'ilike', '%' . $this->search . '%')
                                    ->orWhereHas('region', function ($regionQuery) {
                                        $regionQuery->where('region_name', 'ilike', '%' . $this->search . '%');
                                    });
                            });
                    });
            });
        }

        $branches = $query->orderBy('master_areas.region_code', 'asc')
                          ->orderBy('master_areas.area_name', 'asc')
                          ->orderBy('master_supervisors.supervisor_name', 'asc')
                          ->paginate(10);

        return view('livewire.master-branches.index', [
            'branches' => $branches,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($branchId)
    {
        $this->branchIdToDelete = $branchId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data cabang.
     */
    public function delete()
    {
        $query = MasterBranch::query();
        $this->applyRegionAccess($query, 'branch');
        
        $branch = $query->where('branch_code', $this->branchIdToDelete)->first();

        if ($branch) {
            $branch->delete();
            session()->flash('message', 'Cabang berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus cabang ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}