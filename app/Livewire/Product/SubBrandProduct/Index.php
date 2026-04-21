<?php

namespace App\Livewire\Product\SubBrandProduct;

use Livewire\Component;
use App\Models\ProductSubBrand;
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
    public $subBrandIdToDelete;

    // Form Fields
    public $sub_brand_id;
    public $sub_brand_name;
    public $old_sub_brand_id;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'sub_brand_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_sub_brands')->ignore($this->old_sub_brand_id, 'sub_brand_id')
                    : Rule::unique('product_sub_brands', 'sub_brand_id'),
            ],
            'sub_brand_name' => 'required|string|max:150',
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

    public function openEditModal($subBrandId)
    {
        $this->resetValidation();
        $subBrand = ProductSubBrand::findOrFail($subBrandId);
        
        $this->old_sub_brand_id = $subBrand->sub_brand_id;
        $this->sub_brand_id = $subBrand->sub_brand_id;
        $this->sub_brand_name = $subBrand->sub_brand_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->sub_brand_id = null;
        $this->sub_brand_name = null;
        $this->old_sub_brand_id = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $subBrand = ProductSubBrand::where('sub_brand_id', $this->old_sub_brand_id)->first();
            $subBrand->update([
                'sub_brand_id' => $this->sub_brand_id,
                'sub_brand_name' => $this->sub_brand_name,
            ]);
            session()->flash('message', 'Product Sub-Brand berhasil diperbarui.');
        } else {
            ProductSubBrand::create([
                'sub_brand_id' => $this->sub_brand_id,
                'sub_brand_name' => $this->sub_brand_name,
            ]);
            session()->flash('message', 'Product Sub-Brand berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $subBrands = ProductSubBrand::where('sub_brand_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('sub_brand_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('sub_brand_id')
            ->paginate(10);

        return view('livewire.master-product.sub-brand.index', [
            'subBrands' => $subBrands,
        ])->layout('layouts.app');
    }

    public function confirmDelete($subBrandId)
    {
        $this->subBrandIdToDelete = $subBrandId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $subBrand = ProductSubBrand::where('sub_brand_id', $this->subBrandIdToDelete)->first();
        if ($subBrand) {
            $subBrand->delete();
            session()->flash('message', 'Product Sub-Brand berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }
}
