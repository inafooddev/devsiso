<?php

namespace App\Livewire\Product\BrandProduct;

use Livewire\Component;
use App\Models\ProductBrand;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $brandId; // ID asli
    public $brand_id;
    public $brand_name;

    public function mount($id)
    {
        $brand = ProductBrand::findOrFail($id);
        $this->brandId = $brand->brand_id;
        $this->brand_id = $brand->brand_id;
        $this->brand_name = $brand->brand_name;
    }

    protected function rules()
    {
        return [
            'brand_id' => [
                'required',
                'string',
                'max:15',
                Rule::unique('product_brands')->ignore($this->brandId, 'brand_id')
            ],
            'brand_name' => 'required|string|max:150',
        ];
    }

    public function update()
    {
        $validatedData = $this->validate();

        $brand = ProductBrand::findOrFail($this->brandId);
        $brand->update($validatedData);

        session()->flash('message', 'Product Brand berhasil diperbarui.');

        return redirect()->route('product-brands.index');
    }

    public function render()
    {
        return view('livewire.master-product.brand.edit')->layout('layouts.app');
    }
}

