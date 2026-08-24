<?php

namespace App\Livewire\MasterData\Product\MasterProdukLama;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Models\MasterProdukLama;
use Illuminate\Support\Facades\Log;
use App\Livewire\Forms\MasterProdukLamaForm;
use App\Livewire\MasterData\Product\MasterProdukLama\Traits\WithFilters;
use App\Livewire\MasterData\Product\MasterProdukLama\Traits\WithExportImport;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination, WithFileUploads;
    use WithFilters, WithExportImport;

    public MasterProdukLamaForm $form;

    // Modal states
    public $isFormModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isDetailModalOpen = false;
    
    // Product Detail Data
    public $productDetail = null;
    
    // Item to delete
    public $deleteId = null;

    #[Computed]
    public function products()
    {
        $query = MasterProdukLama::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama_produk', 'like', '%' . $this->search . '%')
                  ->orWhere('pcode_prc', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status_product', $this->statusFilter);
        }
        if ($this->kategoriFilter !== '') {
            $query->where('kategory', $this->kategoriFilter);
        }
        if ($this->topItemFilter !== '') {
            $query->where('topitem', $this->topItemFilter);
        }
        if ($this->subbrandFilter !== '') {
            $query->where('subbrand', $this->subbrandFilter);
        }
        if ($this->divisiFilter !== '') {
            $query->where('divisi', $this->divisiFilter);
        }

        return $query->orderBy('pcode_prc', 'asc')->paginate(100);
    }

    public function getFilterOptions($column)
    {
        $query = MasterProdukLama::query()
            ->whereNotNull($column)
            ->where($column, '!=', '');

        if ($this->statusFilter !== '') {
            $query->where('status_product', $this->statusFilter);
        }
        if ($this->kategoriFilter !== '' && $column !== 'kategory') {
            $query->where('kategory', $this->kategoriFilter);
        }
        if ($this->topItemFilter !== '' && $column !== 'topitem') {
            $query->where('topitem', $this->topItemFilter);
        }
        if ($this->subbrandFilter !== '' && $column !== 'subbrand') {
            $query->where('subbrand', $this->subbrandFilter);
        }
        if ($this->divisiFilter !== '' && $column !== 'divisi') {
            $query->where('divisi', $this->divisiFilter);
        }

        return $query->distinct()
            ->orderBy($column, 'asc')
            ->pluck($column);
    }

    #[Computed]
    public function kategories()
    {
        return $this->getFilterOptions('kategory');
    }

    #[Computed]
    public function topItems()
    {
        return $this->getFilterOptions('topitem');
    }

    #[Computed]
    public function subbrands()
    {
        return $this->getFilterOptions('subbrand');
    }

    #[Computed]
    public function divisis()
    {
        return $this->getFilterOptions('divisi');
    }

    public function updatedFormPcodePrc($value)
    {
        if (!$this->form->isEditing && $value) {
            $this->form->fetchFromProductMaster($value);
        }
    }

    public function openCreateModal()
    {
        $this->form->reset();
        $this->form->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function edit($id)
    {
        $product = MasterProdukLama::find($id);
        if ($product) {
            $this->form->setProduct($product);
            $this->isFormModalOpen = true;
        }
    }

    public function viewDetail($id)
    {
        $this->productDetail = MasterProdukLama::find($id);
        if ($this->productDetail) {
            $this->isDetailModalOpen = true;
        } else {
            session()->flash('error', 'Produk tidak ditemukan.');
        }
    }

    public function save()
    {
        try {
            if ($this->form->isEditing) {
                $this->form->update();
                session()->flash('message', 'Data produk berhasil diperbarui.');
            } else {
                $this->form->store();
                session()->flash('message', 'Data produk berhasil ditambahkan.');
            }
            
            $this->isFormModalOpen = false;
        } catch (\Exception $e) {
            Log::error('Error saving master produk lama: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            try {
                $product = MasterProdukLama::find($this->deleteId);
                if ($product) {
                    $product->delete();
                    session()->flash('message', 'Data produk berhasil dihapus.');
                }
                $this->isDeleteModalOpen = false;
                $this->deleteId = null;
            } catch (\Exception $e) {
                Log::error('Error deleting master produk lama: ' . $e->getMessage());
                session()->flash('error', 'Gagal menghapus data.');
            }
        }
    }

    public function resetForm()
    {
        $this->form->reset();
    }

    public function render()
    {
        return view('livewire.master-data.product.master-produk-lama.index');
    }
}
