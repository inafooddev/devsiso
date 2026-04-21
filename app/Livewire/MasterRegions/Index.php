<?php

namespace App\Livewire\MasterRegions;

use Livewire\Component;
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
    public $regionId;
    public $region_code;
    public $region_name;
    public $regionIdToDelete;

    protected $queryString = ['search'];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'region_code' => [
                'required',
                'string',
                'max:15',
                $this->isEditing 
                    ? Rule::unique('master_regions')->ignore($this->regionId, 'region_code')
                    : Rule::unique('master_regions', 'region_code'),
            ],
            'region_name' => 'required|string|max:50',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'region_code.required' => 'Kode Region wajib diisi.',
            'region_code.unique'   => 'Kode Region ini sudah digunakan.',
            'region_name.required' => 'Nama Region wajib diisi.',
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
    public function openEditModal($regionCode)
    {
        $this->resetValidation();
        $region = MasterRegion::findOrFail($regionCode);
        
        $this->regionId = $region->region_code;
        $this->region_code = $region->region_code;
        $this->region_name = $region->region_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->regionId = null;
        $this->region_code = null;
        $this->region_name = null;
    }

    /**
     * Menyimpan atau memperbarui data region.
     */
    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $region = MasterRegion::find($this->regionId);
            $region->update([
                'region_code' => $this->region_code,
                'region_name' => $this->region_name,
            ]);
            session()->flash('message', 'Region berhasil diperbarui.');
        } else {
            MasterRegion::create([
                'region_code' => $this->region_code,
                'region_name' => $this->region_name,
            ]);
            session()->flash('message', 'Region baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterRegion::query()
            ->where('region_code', '!=', 'HOINA')
            ->orderBy('region_code', 'asc');

        $this->applyRegionAccess($query);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('region_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('region_name', 'ilike', '%' . $this->search . '%');
            });
        }

        $regions = $query->latest('region_code')->paginate(10);

        return view('livewire.master-regions.index', [
            'regions' => $regions,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($regionId)
    {
        $this->regionIdToDelete = $regionId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data region.
     */
    public function delete()
    {
        $query = MasterRegion::query();
        $this->applyRegionAccess($query);
        
        $region = $query->where('region_code', $this->regionIdToDelete)->first();

        if ($region) {
            $region->delete();
            session()->flash('message', 'Region berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus region ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}