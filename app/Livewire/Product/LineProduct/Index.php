<?php

namespace App\Livewire\Product\LineProduct;

use Livewire\Component;
use App\Models\ProductLine;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $lineIdToDelete;

    // Form Fields
    public $line_id;
    public $line_name;
    public $old_line_id;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'line_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_lines')->ignore($this->old_line_id, 'line_id')
                    : Rule::unique('product_lines', 'line_id'),
            ],
            'line_name' => 'required|string|max:100',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * CRUD Modal Operations.
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($lineId)
    {
        $this->resetValidation();
        $line = ProductLine::findOrFail($lineId);
        
        $this->old_line_id = $line->line_id;
        $this->line_id = $line->line_id;
        $this->line_name = $line->line_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->line_id = null;
        $this->line_name = null;
        $this->old_line_id = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $line = ProductLine::where('line_id', $this->old_line_id)->first();
            $line->update([
                'line_id' => $this->line_id,
                'line_name' => $this->line_name,
            ]);
            session()->flash('message', 'Product Line berhasil diperbarui.');
        } else {
            ProductLine::create([
                'line_id' => $this->line_id,
                'line_name' => $this->line_name,
            ]);
            session()->flash('message', 'Product Line berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $lines = ProductLine::where('line_id', 'ilike', '%' . $this->search . '%')
            ->orWhere('line_name', 'ilike', '%' . $this->search . '%')
            ->latest('line_id')
            ->paginate(10);

        return view('livewire.master-product.line.index', [
            'lines' => $lines,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($lineId)
    {
        $this->lineIdToDelete = $lineId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data product line.
     */
    public function delete()
    {
        $line = ProductLine::where('line_id', $this->lineIdToDelete)->first();
        if ($line) {
            $line->delete();
            session()->flash('message', 'Product Line berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
    }
}
