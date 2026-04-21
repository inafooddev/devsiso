<?php

namespace App\Livewire\Product\ProductCategories;

use Livewire\Component;
use App\Models\ProductCategory;
use App\Models\ProductMaster;
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
    public $mappingIdToDelete;

    // Form Fields
    public $mapping_id;
    public $product_id;
    public $category_id;

    // Data Lists
    public $productList = [];
    public $categoryList = [];

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'product_id' => [
                'required', 'string', 'exists:product_masters,product_id',
                Rule::unique('product_categories')->where(function ($query) {
                    return $query->where('category_id', $this->category_id);
                })->ignore($this->mapping_id),
            ],
            'category_id' => 'required|string|exists:categories,category_id',
        ];
    }

    protected $messages = [
        'product_id.unique' => 'Kombinasi Produk dan Kategori ini sudah ada.',
    ];

    public function mount()
    {
        $this->productList = ProductMaster::orderBy('product_name')->get(['product_id', 'product_name']);
        $this->categoryList = Category::orderBy('category_name')->get(['category_id', 'category_name']);
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

    public function openEditModal($mappingId)
    {
        $this->resetValidation();
        $mapping = ProductCategory::findOrFail($mappingId);
        
        $this->mapping_id = $mapping->id;
        $this->product_id = $mapping->product_id;
        $this->category_id = $mapping->category_id;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->mapping_id = null;
        $this->product_id = null;
        $this->category_id = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $mapping = ProductCategory::findOrFail($this->mapping_id);
            $mapping->update([
                'product_id' => $this->product_id,
                'category_id' => $this->category_id,
            ]);
            session()->flash('message', 'Pemetaan Kategori Produk berhasil diperbarui.');
        } else {
            ProductCategory::create([
                'product_id' => $this->product_id,
                'category_id' => $this->category_id,
            ]);
            session()->flash('message', 'Pemetaan Kategori Produk berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $mappings = ProductCategory::with(['productMaster', 'category'])
            ->whereHas('productMaster', function ($query) {
                $query->where('product_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('product_id', 'ILIKE', '%' . $this->search . '%');
            })
            ->orWhereHas('category', function ($query) {
                $query->where('category_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('category_id', 'ILIKE', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.master-product.map-categories.index', [
            'mappings' => $mappings,
        ])->layout('layouts.app');
    }

    public function confirmDelete($mappingId)
    {
        $this->mappingIdToDelete = $mappingId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $mapping = ProductCategory::find($this->mappingIdToDelete);
        if ($mapping) {
            $mapping->delete();
            session()->flash('message', 'Pemetaan Kategori Produk berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }
}

