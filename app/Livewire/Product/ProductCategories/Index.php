<?php

namespace App\Livewire\Product\ProductCategories;

use Livewire\Component;
use App\Models\ProductCategory;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $mappingIdToDelete; // Tetap gunakan ID integer

    protected $queryString = ['search'];

    public function render()
    {
        $mappings = ProductCategory::with(['productMaster', 'category'])
            ->whereHas('productMaster', function ($query) {
                $query->where('product_name', 'ILIKE', '%' . $this->search . '%');
            })
            ->orWhereHas('category', function ($query) {
                $query->where('category_name', 'ILIKE', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.master-product.map-categories.index', [
            'mappings' => $mappings,
        ])->layout('layouts.app');
    }

    public function confirmDelete($mappingId) // Terima ID integer
    {
        $this->mappingIdToDelete = $mappingId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        ProductCategory::find($this->mappingIdToDelete)->delete();
        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Pemetaan Kategori Produk berhasil dihapus.');
    }
}

