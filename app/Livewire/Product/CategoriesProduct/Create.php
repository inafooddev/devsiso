<?php

namespace App\Livewire\Product\CategoriesProduct;

use Livewire\Component;
use App\Models\Category;

class Create extends Component
{
    public $category_id;
    public $category_name;

    protected $rules = [
        'category_id' => 'required|string|max:15|unique:categories,category_id',
        'category_name' => 'required|string|max:150',
    ];

    public function save()
    {
        $validatedData = $this->validate();

        Category::create($validatedData);

        session()->flash('message', 'Category berhasil ditambahkan.');

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.master-product.categories.create')->layout('layouts.app');
    }
}
