<?php

namespace App\Livewire\Product\BrandProduct;

use Livewire\Component;
use App\Models\ProductBrand;

class Create extends Component
{
    public $brand_id;
    public $brand_name;

    protected $rules = [
        'brand_id' => 'required|string|max:15|unique:product_brands,brand_id',
        'brand_name' => 'required|string|max:150',
    ];

    public function save()
    {
        $validatedData = $this->validate();

        ProductBrand::create($validatedData);

        session()->flash('message', 'Product Brand berhasil ditambahkan.');

        return redirect()->route('product-brands.index');
    }

    public function render()
    {
        return view('livewire.master-product.brand.create')->layout('layouts.app');
    }
}

