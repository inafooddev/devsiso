<?php

namespace App\Livewire\Product\LineProduct;

use Livewire\Component;
use App\Models\ProductLine;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $lineId; // Untuk menyimpan ID asli
    public $line_id;
    public $line_name;

    public function mount($id)
    {
        $line = ProductLine::findOrFail($id);
        $this->lineId = $line->line_id; // Simpan ID asli
        $this->line_id = $line->line_id;
        $this->line_name = $line->line_name;
    }

    protected function rules()
    {
        return [
            'line_id' => [
                'required',
                'string',
                'max:15',
                Rule::unique('product_lines')->ignore($this->lineId, 'line_id') // Abaikan ID asli saat cek unique
            ],
            'line_name' => 'required|string|max:100',
        ];
    }

    public function update()
    {
        $validatedData = $this->validate();

        $line = ProductLine::findOrFail($this->lineId);
        $line->update($validatedData);

        session()->flash('message', 'Product Line berhasil diperbarui.');

        return redirect()->route('product-lines.index');
    }

    public function render()
    {
        return view('livewire.master-product.line.edit')->layout('layouts.app');
    }
}
