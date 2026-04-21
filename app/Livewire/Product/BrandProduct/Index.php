<?php

namespace App\Livewire\Product\BrandProduct;

use Livewire\Component;
use App\Models\ProductBrand;
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
    public $brandIdToDelete;

    // Form Fields
    public $brand_id;
    public $brand_name;
    public $old_brand_id;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'brand_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_brands')->ignore($this->old_brand_id, 'brand_id')
                    : Rule::unique('product_brands', 'brand_id'),
            ],
            'brand_name' => 'required|string|max:150',
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

    public function openEditModal($brandId)
    {
        $this->resetValidation();
        $brand = ProductBrand::findOrFail($brandId);
        
        $this->old_brand_id = $brand->brand_id;
        $this->brand_id = $brand->brand_id;
        $this->brand_name = $brand->brand_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->brand_id = null;
        $this->brand_name = null;
        $this->old_brand_id = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $brand = ProductBrand::where('brand_id', $this->old_brand_id)->first();
            $brand->update([
                'brand_id' => $this->brand_id,
                'brand_name' => $this->brand_name,
            ]);
            session()->flash('message', 'Product Brand berhasil diperbarui.');
        } else {
            ProductBrand::create([
                'brand_id' => $this->brand_id,
                'brand_name' => $this->brand_name,
            ]);
            session()->flash('message', 'Product Brand berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $brands = ProductBrand::where('brand_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('brand_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('brand_id')
            ->paginate(10);

        return view('livewire.master-product.brand.index', [
            'brands' => $brands,
        ])->layout('layouts.app');
    }

    public function confirmDelete($brandId)
    {
        $this->brandIdToDelete = $brandId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $brand = ProductBrand::where('brand_id', $this->brandIdToDelete)->first();
        if ($brand) {
            $brand->delete();
            session()->flash('message', 'Product Brand berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }
}

