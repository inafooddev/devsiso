<?php

namespace App\Livewire\Product\GroupProduct;

use Livewire\Component;
use App\Models\ProductGroup;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $groupId; // ID asli
    public $product_group_id;
    public $brand_unit_name;

    public function mount($id)
    {
        $group = ProductGroup::findOrFail($id);
        $this->groupId = $group->product_group_id;
        $this->product_group_id = $group->product_group_id;
        $this->brand_unit_name = $group->brand_unit_name;
    }

    protected function rules()
    {
        return [
            'product_group_id' => [
                'required',
                'string',
                'max:15',
                Rule::unique('product_groups')->ignore($this->groupId, 'product_group_id')
            ],
            'brand_unit_name' => 'required|string|max:150',
        ];
    }

    public function update()
    {
        $validatedData = $this->validate();

        $group = ProductGroup::findOrFail($this->groupId);
        $group->update($validatedData);

        session()->flash('message', 'Product Group berhasil diperbarui.');

        return redirect()->route('product-groups.index');
    }

    public function render()
    {
        return view('livewire.master-product.group.edit')->layout('layouts.app');
    }
}
