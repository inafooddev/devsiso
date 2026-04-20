<?php

namespace App\Livewire\Product\SubBrandProduct;

use Livewire\Component;
use App\Models\ProductSubBrand;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $subBrandIdToDelete;

    protected $queryString = ['search'];

    public function render()
    {
        // Menggunakan ILIKE untuk PostgreSQL case-insensitive search
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
        ProductSubBrand::find($this->subBrandIdToDelete)->delete();
        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Product Sub-Brand berhasil dihapus.');
    }
}
