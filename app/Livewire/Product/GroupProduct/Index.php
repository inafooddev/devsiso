<?php

namespace App\Livewire\Product\GroupProduct;

use Livewire\Component;
use App\Models\ProductGroup;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $groupIdToDelete;

    protected $queryString = ['search'];

    public function render()
    {
        // Menggunakan ILIKE untuk PostgreSQL case-insensitive search
        $groups = ProductGroup::where('product_group_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('brand_unit_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('product_group_id')
            ->paginate(10);

        return view('livewire.master-product.group.index', [
            'groups' => $groups,
        ])->layout('layouts.app');
    }

    public function confirmDelete($groupId)
    {
        $this->groupIdToDelete = $groupId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        ProductGroup::find($this->groupIdToDelete)->delete();
        $this->isDeleteModalOpen = false;
        session()->flash('message', 'Product Group berhasil dihapus.');
    }
}
