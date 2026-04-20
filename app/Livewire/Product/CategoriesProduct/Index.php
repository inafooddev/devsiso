<?php

namespace App\Livewire\Product\CategoriesProduct;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $categoryIdToDelete;

    protected $queryString = ['search'];

    public function render()
    {
        // Menggunakan ILIKE untuk PostgreSQL case-insensitive search
        $categories = Category::where('category_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('category_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('category_id')
            ->paginate(10);

        return view('livewire.master-product.categories.index', [
            'categories' => $categories,
        ])->layout('layouts.app');
    }

    public function confirmDelete($categoryId)
    {
        $this->categoryIdToDelete = $categoryId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        Category::find($this->categoryIdToDelete)->delete();
        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Category berhasil dihapus.');
    }
}
