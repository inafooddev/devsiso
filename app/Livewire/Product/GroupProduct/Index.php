<?php

namespace App\Livewire\Product\GroupProduct;

use Livewire\Component;
use App\Models\ProductGroup;
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
    public $groupIdToDelete;

    // Form Fields
    public $product_group_id;
    public $brand_unit_name;
    public $old_group_id;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'product_group_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_groups')->ignore($this->old_group_id, 'product_group_id')
                    : Rule::unique('product_groups', 'product_group_id'),
            ],
            'brand_unit_name' => 'required|string|max:150',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
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

    public function openEditModal($groupId)
    {
        $this->resetValidation();
        $group = ProductGroup::findOrFail($groupId);
        
        $this->old_group_id = $group->product_group_id;
        $this->product_group_id = $group->product_group_id;
        $this->brand_unit_name = $group->brand_unit_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->product_group_id = null;
        $this->brand_unit_name = null;
        $this->old_group_id = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $group = ProductGroup::where('product_group_id', $this->old_group_id)->first();
            $group->update([
                'product_group_id' => $this->product_group_id,
                'brand_unit_name' => $this->brand_unit_name,
            ]);
            session()->flash('message', 'Product Group berhasil diperbarui.');
        } else {
            ProductGroup::create([
                'product_group_id' => $this->product_group_id,
                'brand_unit_name' => $this->brand_unit_name,
            ]);
            session()->flash('message', 'Product Group berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $groups = ProductGroup::where('product_group_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('brand_unit_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('product_group_id')
            ->paginate(10);

        return view('livewire.master-product.group.index', [
            'groups' => $groups,
        ])->layout('layouts.app');
    }

    public function confirmDelete($groupId)
    {
        $this->groupIdToDelete = $groupId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $group = ProductGroup::where('product_group_id', $this->groupIdToDelete)->first();
        if ($group) {
            $group->delete();
            session()->flash('message', 'Product Group berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }
}
