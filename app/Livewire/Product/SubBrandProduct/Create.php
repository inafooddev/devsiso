<?php

namespace App\Livewire\Product\SubBrandProduct;

use Livewire\Component;
use App\Models\ProductSubBrand;

class Create extends Component
{
    public $sub_brand_id;
    public $sub_brand_name;

    protected $rules = [
        'sub_brand_id' => 'required|string|max:15|unique:product_sub_brands,sub_brand_id',
        'sub_brand_name' => 'required|string|max:150',
    ];

    public function save()
    {
        $validatedData = $this->validate();

        ProductSubBrand::create($validatedData);

        session()->flash('message', 'Product Sub-Brand berhasil ditambahkan.');

        return redirect()->route('product-sub-brands.index');
    }

    public function render()
    {
        return view('livewire.master-product.sub-brand.create')->layout('layouts.app');
    }
}
