<?php

namespace App\Livewire\Product\ProductCategories;

use Livewire\Component;
use App\Models\ProductCategory;
use App\Models\ProductMaster;
use App\Models\Category;
use Illuminate\Validation\Rule;

class Create extends Component
{
    // [DIHAPUS] ID tidak lagi diinput manual
    // public $mapping_id; 
    public $product_id;
    public $category_id;

    public $products = [];
    public $categories = [];

    public function mount()
    {
        $this->products = ProductMaster::orderBy('product_name')->get(['product_id', 'product_name']);
        $this->categories = Category::orderBy('category_name')->get(['category_id', 'category_name']);
    }

    protected function rules()
    {
        return [
            // [DIHAPUS] Validasi untuk ID tidak diperlukan lagi
            // 'mapping_id' => 'required|string|max:15|unique:product_categories,id',
            'product_id' => [
                'required',
                'string',
                'exists:product_masters,product_id',
                Rule::unique('product_categories')->where(function ($query) {
                    return $query->where('category_id', $this->category_id);
                }),
            ],
            'category_id' => 'required|string|exists:categories,category_id',
        ];
    }

    protected $messages = [
        'product_id.unique' => 'Kombinasi Produk dan Kategori ini sudah ada.',
    ];


    public function save()
    {
        $validatedData = $this->validate();
        
        // [DIHAPUS] Tidak perlu lagi rename key 'id'

        ProductCategory::create($validatedData);

        session()->flash('message', 'Pemetaan Kategori Produk berhasil ditambahkan.');
        return redirect()->route('product-categories.index');
    }

    public function render()
    {
        return view('livewire.master-product.map-categories.create')->layout('layouts.app');
    }
}

