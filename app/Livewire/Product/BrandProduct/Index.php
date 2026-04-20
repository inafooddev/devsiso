<?php

namespace App\Livewire\Product\BrandProduct;

use Livewire\Component;
use App\Models\ProductBrand;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $brandIdToDelete;

    protected $queryString = ['search'];

    public function render()
    {
        // Menggunakan ILIKE untuk PostgreSQL case-insensitive search
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
        ProductBrand::find($this->brandIdToDelete)->delete();
        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Product Brand berhasil dihapus.');
    }
}

