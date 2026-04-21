<?php

namespace App\Livewire\Product\CategoriesProduct;

use Livewire\Component;
use App\Models\Category;
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
    public $categoryIdToDelete;

    // Form Fields
    public $category_id;
    public $category_name;
    public $old_category_id;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'category_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('categories')->ignore($this->old_category_id, 'category_id')
                    : Rule::unique('categories', 'category_id'),
            ],
            'category_name' => 'required|string|max:150',
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

    public function openEditModal($categoryId)
    {
        $this->resetValidation();
        $category = Category::findOrFail($categoryId);
        
        $this->old_category_id = $category->category_id;
        $this->category_id = $category->category_id;
        $this->category_name = $category->category_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->category_id = null;
        $this->category_name = null;
        $this->old_category_id = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $category = Category::where('category_id', $this->old_category_id)->first();
            $category->update([
                'category_id' => $this->category_id,
                'category_name' => $this->category_name,
            ]);
            session()->flash('message', 'Category berhasil diperbarui.');
        } else {
            Category::create([
                'category_id' => $this->category_id,
                'category_name' => $this->category_name,
            ]);
            session()->flash('message', 'Category berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
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
        $category = Category::where('category_id', $this->categoryIdToDelete)->first();
        if ($category) {
            $category->delete();
            session()->flash('message', 'Category berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }
}
