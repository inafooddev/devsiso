<?php

namespace App\Livewire\Product\LineProduct;

use Livewire\Component;
use App\Models\ProductLine;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $lineIdToDelete;

    protected $queryString = ['search'];

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
        ProductLine::find($this->lineIdToDelete)->delete();

        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Product Line berhasil dihapus.');
    }
}
