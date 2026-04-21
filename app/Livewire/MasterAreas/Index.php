<?php

namespace App\Livewire\MasterAreas;

use Livewire\Component;
use App\Models\MasterArea;
use App\Models\MasterRegion;
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
    public $areaId;
    public $area_code;
    public $area_name;
    public $region_code = '';
    public $areaIdToDelete;

    protected $queryString = ['search'];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'area_code' => [
                'required',
                'string',
                'max:15',
                $this->isEditing 
                    ? Rule::unique('master_areas')->ignore($this->areaId, 'area_code')
                    : Rule::unique('master_areas', 'area_code'),
            ],
            'area_name'   => 'required|string|max:50',
            'region_code' => 'required|exists:master_regions,region_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'region_code.required' => 'Silakan pilih salah satu region.',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
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
    public function openEditModal($areaCode)
    {
        $this->resetValidation();
        $area = MasterArea::findOrFail($areaCode);
        
        $this->areaId      = $area->area_code;
        $this->area_code   = $area->area_code;
        $this->area_name   = $area->area_name;
        $this->region_code = $area->region_code;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->areaId = null;
        $this->area_code = null;
        $this->area_name = null;
        $this->region_code = '';
    }

    /**
     * Menyimpan atau memperbarui data area.
     */
    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $area = MasterArea::find($this->areaId);
            $area->update([
                'area_code'   => $this->area_code,
                'area_name'   => $this->area_name,
                'region_code' => $this->region_code,
            ]);
            session()->flash('message', 'Data area berhasil diperbarui.');
        } else {
            MasterArea::create([
                'area_code'   => $this->area_code,
                'area_name'   => $this->area_name,
                'region_code' => $this->region_code,
            ]);
            session()->flash('message', 'Area baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterArea::with('region')
            ->where('region_code', '!=', 'HOINA')
            ->orderBy('region_code', 'asc');

        $this->applyRegionAccess($query);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('area_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('region', function($subQuery) {
                      $subQuery->where('region_name', 'ilike', '%' . $this->search . '%');
                  });
            });
        }

        $areas = $query->paginate(10);
        
        // Ambil data region untuk dropdown form
        $regionsQuery = MasterRegion::orderBy('region_name', 'asc');
        $this->applyRegionAccess($regionsQuery);
        $regions = $regionsQuery->get();

        return view('livewire.master-areas.index', [
            'areas' => $areas,
            'regions' => $regions,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($areaId)
    {
        $this->areaIdToDelete = $areaId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data area.
     */
    public function delete()
    {
        $query = MasterArea::query();
        $this->applyRegionAccess($query);
        
        $area = $query->where('area_code', $this->areaIdToDelete)->first();

        if ($area) {
            $area->delete();
            session()->flash('message', 'Area berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus area ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}