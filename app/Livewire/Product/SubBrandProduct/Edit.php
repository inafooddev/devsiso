<?php

namespace App\Livewire\Product\SubBrandProduct;

use Livewire\Component;
use App\Models\ProductSubBrand;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $subBrandId; // ID asli
    public $sub_brand_id;
    public $sub_brand_name;

    public function mount($id)
    {
        $subBrand = ProductSubBrand::findOrFail($id);
        $this->subBrandId = $subBrand->sub_brand_id;
        $this->sub_brand_id = $subBrand->sub_brand_id;
        $this->sub_brand_name = $subBrand->sub_brand_name;
    }

    protected function rules()
    {
        return [
            'sub_brand_id' => [
                'required',
                'string',
                'max:15',
                Rule::unique('product_sub_brands')->ignore($this->subBrandId, 'sub_brand_id')
            ],
            'sub_brand_name' => 'required|string|max:150',
        ];
    }

    public function update()
    {
        $validatedData = $this->validate();

        $subBrand = ProductSubBrand::findOrFail($this->subBrandId);
        $subBrand->update($validatedData);

        session()->flash('message', 'Product Sub-Brand berhasil diperbarui.');

        return redirect()->route('product-sub-brands.index');
    }

    public function render()
    {
        return view('livewire.master-product.sub-brand.edit')->layout('layouts.app');
    }
}
