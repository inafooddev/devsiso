<?php

namespace App\Livewire\Product\CategoriesProduct;

use Livewire\Component;
use App\Models\Category;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $categoryId; // ID asli
    public $category_id;
    public $category_name;

    public function mount($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->category_id;
        $this->category_id = $category->category_id;
        $this->category_name = $category->category_name;
    }

    protected function rules()
    {
        return [
            'category_id' => [
                'required',
                'string',
                'max:15',
                Rule::unique('categories')->ignore($this->categoryId, 'category_id')
            ],
            'category_name' => 'required|string|max:150',
        ];
    }

    public function update()
    {
        $validatedData = $this->validate();

        $category = Category::findOrFail($this->categoryId);
        $category->update($validatedData);

        session()->flash('message', 'Category berhasil diperbarui.');

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.master-product.categories.edit')->layout('layouts.app');
    }
}
