<?php

namespace App\Livewire\Product\ProductCategories;

use Livewire\Component;
use App\Models\ProductCategory;
use App\Models\ProductMaster;
use App\Models\Category;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public ProductCategory $mapping; // Gunakan Route Model Binding Livewire 3

    public $product_id;
    public $category_id;

    public $products = [];
    public $categories = [];

    public function mount(ProductCategory $mapping) // Terima model langsung
    {
        $this->mapping = $mapping; // Simpan model
        $this->product_id = $mapping->product_id;
        $this->category_id = $mapping->category_id;

        $this->products = ProductMaster::orderBy('product_name')->get(['product_id', 'product_name']);
        $this->categories = Category::orderBy('category_name')->get(['category_id', 'category_name']);
    }

    protected function rules()
    {
        return [
            // [DIHAPUS] Validasi ID tidak diperlukan
            'product_id' => [
                'required', 'string', 'exists:product_masters,product_id',
                Rule::unique('product_categories')->where(function ($query) {
                    return $query->where('category_id', $this->category_id);
                })->ignore($this->mapping->id), // Gunakan ID dari model
            ],
            'category_id' => 'required|string|exists:categories,category_id',
        ];
    }
    
    protected $messages = [
        'product_id.unique' => 'Kombinasi Produk dan Kategori ini sudah ada.',
    ];

    public function update()
    {
        $validatedData = $this->validate();
        
        // [DIHAPUS] Tidak perlu rename key 'id'

        $this->mapping->update($validatedData); // Update langsung dari model

        session()->flash('message', 'Pemetaan Kategori Produk berhasil diperbarui.');
        return redirect()->route('product-categories.index');
    }

    public function render()
    {
        return view('livewire.master-product.map-categories.edit')->layout('layouts.app');
    }
}

