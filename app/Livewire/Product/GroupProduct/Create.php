<?php

namespace App\Livewire\Product\GroupProduct;

use Livewire\Component;
use App\Models\ProductGroup;

class Create extends Component
{
    public $product_group_id;
    public $brand_unit_name;

    protected $rules = [
        'product_group_id' => 'required|string|max:15|unique:product_groups,product_group_id',
        'brand_unit_name' => 'required|string|max:150',
    ];

    public function save()
    {
        $validatedData = $this->validate();

        ProductGroup::create($validatedData);

        session()->flash('message', 'Product Group berhasil ditambahkan.');

        return redirect()->route('product-groups.index');
    }

    public function render()
    {
        return view('livewire.master-product.group.create')->layout('layouts.app');
    }
}
