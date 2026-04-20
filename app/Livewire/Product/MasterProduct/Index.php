<?php

namespace App\Livewire\Product\MasterProduct; // [PERBAIKAN] Menggunakan backslash

// [PERBAIKAN] Menggunakan backslash untuk semua use statement
use Livewire\Component;
use App\Models\ProductMaster;
use App\Exports\ProductMastersExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $statusFilter = '';
    public $isDeleteModalOpen = false;
    public $productIdToDelete;

    protected $queryString = ['search', 'statusFilter'];

    public function render()
    {
        // [PERBAIKAN] Menggunakan sintaks PHP yang benar ($this->)
        $products = ProductMaster::query()
            ->with(['productLine', 'productBrand', 'productGroup', 'productSubBrand'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('product_id', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('product_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('line_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('brand_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('brand_unit_name', 'ILIKE', '%' . $this->search . '%')
                      ->orWhere('sub_brand_name', 'ILIKE', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->latest('product_id')
            ->paginate(10);

        return view('livewire.master-product.master.index', [
            'products' => $products,
        ])->layout('layouts.app');
    }

    public function confirmDelete($productId)
    {
        $this->productIdToDelete = $productId; // [PERBAIKAN] Menggunakan sintaks PHP yang benar ($this->)
        $this->isDeleteModalOpen = true; // [PERBAIKAN] Menggunakan sintaks PHP yang benar ($this->)
    }

    public function delete()
    {
        ProductMaster::find($this->productIdToDelete)->delete(); // [PERBAIKAN] Menggunakan sintaks PHP yang benar ($this->)
        $this->isDeleteModalOpen = false; // [PERBAIKAN] Menggunakan sintaks PHP yang benar ($this->)
        session()->flash('message', 'Master Product berhasil dihapus.');
    }

    public function export()
    {
        // Panggil kelas export tanpa parameter filter
        return Excel::download(new ProductMastersExport(), 'master_products_all.xlsx');
    }
}

