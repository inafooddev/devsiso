<?php

namespace App\Livewire\Product\LineProduct;

use Livewire\Component;
use App\Models\ProductLine;

class Create extends Component
{
    public $line_id;
    public $line_name;

    protected $rules = [
        'line_id' => 'required|string|max:15|unique:product_lines,line_id',
        'line_name' => 'required|string|max:100',
    ];

    public function save()
    {
        $validatedData = $this->validate();

        ProductLine::create($validatedData);

        session()->flash('message', 'Product Line berhasil ditambahkan.');

        return redirect()->route('product-lines.index');
    }

    public function render()
    {
        return view('livewire.master-product.line.create')->layout('layouts.app');
    }
}
