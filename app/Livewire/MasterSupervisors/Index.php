<?php

namespace App\Livewire\MasterSupervisors;

use Livewire\Component;
use App\Models\MasterSupervisor;
use App\Models\MasterArea;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

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
    public $supervisorId;
    public $supervisor_code;
    public $supervisor_name;
    public $description;
    public $area_code = '';
    public $supervisorIdToDelete;

    protected $queryString = ['search'];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'supervisor_code' => [
                'required',
                'string',
                'max:15',
                $this->isEditing 
                    ? Rule::unique('master_supervisors')->ignore($this->supervisorId, 'supervisor_code')
                    : Rule::unique('master_supervisors', 'supervisor_code'),
            ],
            'supervisor_name' => 'required|string|max:50',
            'description'     => 'nullable|string|max:100',
            'area_code'       => 'required|exists:master_areas,area_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'area_code.required' => 'Silakan pilih salah satu area.',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('area', function ($areaQuery) use ($user) {
                $areaQuery->whereIn('region_code', $user->region_code);
            });
        }

        return $query;
    }

    public function updatingSearch()
    {
        $this->resetPage();
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
    public function openEditModal($supervisorCode)
    {
        $this->resetValidation();
        $supervisor = MasterSupervisor::findOrFail($supervisorCode);
        
        $this->supervisorId    = $supervisor->supervisor_code;
        $this->supervisor_code = $supervisor->supervisor_code;
        $this->supervisor_name = $supervisor->supervisor_name;
        $this->description     = $supervisor->description;
        $this->area_code       = $supervisor->area_code;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->supervisorId = null;
        $this->supervisor_code = null;
        $this->supervisor_name = null;
        $this->description = null;
        $this->area_code = '';
    }

    /**
     * Menyimpan atau memperbarui data supervisor.
     */
    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $supervisor = MasterSupervisor::find($this->supervisorId);
            $supervisor->update([
                'supervisor_code' => $this->supervisor_code,
                'supervisor_name' => $this->supervisor_name,
                'description'     => $this->description,
                'area_code'       => $this->area_code,
            ]);
            session()->flash('message', 'Data supervisor berhasil diperbarui.');
        } else {
            MasterSupervisor::create([
                'supervisor_code' => $this->supervisor_code,
                'supervisor_name' => $this->supervisor_name,
                'description'     => $this->description,
                'area_code'       => $this->area_code,
            ]);
            session()->flash('message', 'Supervisor baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterSupervisor::with(['area', 'area.region'])
            ->select('master_supervisors.*')
            ->leftJoin('master_areas', 'master_supervisors.area_code', '=', 'master_areas.area_code')
            ->where('master_supervisors.supervisor_code', '!=', 'HOINA');

        $this->applyRegionAccess($query);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('master_supervisors.supervisor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('master_supervisors.supervisor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('master_supervisors.description', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('area', function($areaQuery) {
                      $areaQuery->where('area_name', 'ilike', '%' . $this->search . '%')
                                ->orWhereHas('region', function($regionQuery) {
                                    $regionQuery->where('region_name', 'ilike', '%' . $this->search . '%');
                                });
                  });
            });
        }

        $supervisors = $query->orderBy('master_areas.region_code', 'asc')
                             ->orderBy('master_supervisors.supervisor_name', 'asc')
                             ->paginate(10);
                             
        // Ambil data area untuk dropdown form
        $areasQuery = MasterArea::orderBy('area_name', 'asc');
        $this->applyRegionAccess($areasQuery);
        $areas = $areasQuery->get();

        return view('livewire.master-supervisors.index', [
            'supervisors' => $supervisors,
            'areas' => $areas,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($supervisorId)
    {
        $this->supervisorIdToDelete = $supervisorId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data supervisor.
     */
    public function delete()
    {
        $query = MasterSupervisor::query();
        $this->applyRegionAccess($query);
        
        $supervisor = $query->where('supervisor_code', $this->supervisorIdToDelete)->first();

        if ($supervisor) {
            $supervisor->delete();
            session()->flash('message', 'Supervisor berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus supervisor ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}