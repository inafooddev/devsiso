<?php

namespace App\Livewire\MasterData\Product\MasterProdukLama;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MasterProdukLama;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $kategoriFilter = '';
    public $topItemFilter = '';
    public $subbrandFilter = '';
    public $divisiFilter = '';

    // Modal states
    public $isFormModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isDetailModalOpen = false;
    public $isEditing = false;
    
    // Product Detail Data
    public $productDetail = null;
    
    // Form fields
    public $pcode_prc;
    public $nama_produk;
    public $status_product;
    public $uom1;
    public $uom2;
    public $uom3;
    public $crttopcs;
    public $crttopack;
    public $packtopcs;
    public $pricehrt;
    public $produk_line;
    public $brand;
    public $divisi;
    public $kategory;
    public $subbrand;
    public $topitem;
    public $promo_group;

    // Item to delete
    public $deleteId = null;

    protected $rules = [
        'pcode_prc' => 'required|string|max:255',
        'nama_produk' => 'required|string|max:255',
        'status_product' => 'nullable|string|max:255',
        'uom1' => 'nullable|string|max:255',
        'uom2' => 'nullable|string|max:255',
        'uom3' => 'nullable|string|max:255',
        'crttopcs' => 'nullable|numeric',
        'crttopack' => 'nullable|numeric',
        'packtopcs' => 'nullable|numeric',
        'pricehrt' => 'nullable|numeric',
        'produk_line' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'divisi' => 'nullable|string|max:255',
        'kategory' => 'nullable|string|max:255',
        'subbrand' => 'nullable|string|max:255',
        'topitem' => 'nullable|string|max:255',
        'promo_group' => 'nullable|string|max:255',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingKategoriFilter()
    {
        $this->resetPage();
    }

    public function updatingTopItemFilter()
    {
        $this->resetPage();
    }

    public function updatingSubbrandFilter()
    {
        $this->resetPage();
    }

    public function updatingDivisiFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->kategoriFilter = '';
        $this->topItemFilter = '';
        $this->subbrandFilter = '';
        $this->divisiFilter = '';
        $this->resetPage();
    }

    public function render()
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

        $products = $query->paginate(100);

        // Helper function for dropdown queries (Safely rebuilding the query)
        $buildOptionsQuery = function($excludeColumn) {
            $q = MasterProdukLama::query()->select($excludeColumn)->whereNotNull($excludeColumn)->where($excludeColumn, '!=', '');
            
            if ($excludeColumn !== 'status_product' && $this->statusFilter !== '') {
                $q->where('status_product', $this->statusFilter);
            }
            if ($excludeColumn !== 'kategory' && $this->kategoriFilter !== '') {
                $q->where('kategory', $this->kategoriFilter);
            }
            if ($excludeColumn !== 'topitem' && $this->topItemFilter !== '') {
                $q->where('topitem', $this->topItemFilter);
            }
            if ($excludeColumn !== 'subbrand' && $this->subbrandFilter !== '') {
                $q->where('subbrand', $this->subbrandFilter);
            }
            if ($excludeColumn !== 'divisi' && $this->divisiFilter !== '') {
                $q->where('divisi', $this->divisiFilter);
            }
            
            return $q->distinct()->orderBy($excludeColumn)->pluck($excludeColumn);
        };

        $kategories = $buildOptionsQuery('kategory');
        $topItems = $buildOptionsQuery('topitem');
        $subbrands = $buildOptionsQuery('subbrand');
        $divisis = $buildOptionsQuery('divisi');

        $availableProducts = [];
        if ($this->isFormModalOpen && !$this->isEditing) {
            $availableProducts = \App\Models\ProductMaster::whereNotIn('product_id', function($query) {
                $query->select('pcode_prc')->from('master_produk_lama');
            })->get();
        }

        return view('livewire.master-data.product.master-produk-lama.index', [
            'products' => $products,
            'kategories' => $kategories,
            'topItems' => $topItems,
            'subbrands' => $subbrands,
            'divisis' => $divisis,
            'availableProducts' => $availableProducts,
        ])->layout('layouts.app');
    }

    public function updatedPcodePrc($value)
    {
        if (!$this->isEditing && $value) {
            $productMaster = \App\Models\ProductMaster::find($value);
            if ($productMaster) {
                $this->nama_produk = $productMaster->product_name;
                $this->brand = $productMaster->brand_name;
                $this->subbrand = $productMaster->sub_brand_name;
                $this->produk_line = $productMaster->line_name;
                $this->uom1 = $productMaster->uom1;
                $this->uom2 = $productMaster->uom2;
                $this->uom3 = $productMaster->uom3;
            }
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $product = MasterProdukLama::find($id);
        
        if ($product) {
            $this->pcode_prc = $product->pcode_prc;
            $this->nama_produk = $product->nama_produk;
            $this->status_product = $product->status_product;
            $this->uom1 = $product->uom1;
            $this->uom2 = $product->uom2;
            $this->uom3 = $product->uom3;
            $this->crttopcs = $product->crttopcs;
            $this->crttopack = $product->crttopack;
            $this->packtopcs = $product->packtopcs;
            $this->pricehrt = $product->pricehrt;
            $this->produk_line = $product->produk_line;
            $this->brand = $product->brand;
            $this->divisi = $product->divisi;
            $this->kategory = $product->kategory;
            $this->subbrand = $product->subbrand;
            $this->topitem = $product->topitem;
            $this->promo_group = $product->promo_group;
            
            $this->isFormModalOpen = true;
        } else {
            session()->flash('error', 'Produk tidak ditemukan.');
        }
    }

    public function openDetailModal($id)
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
        if (!$this->isEditing) {
            $this->rules['pcode_prc'] = 'required|string|max:255|unique:master_produk_lama,pcode_prc|exists:product_masters,product_id';
        }
        
        $validatedData = $this->validate();

        // Convert empty strings to null to prevent Postgres integer/numeric cast errors
        foreach ($validatedData as $key => $value) {
            if ($value === '') {
                $validatedData[$key] = null;
            }
        }

        // Set default status if null
        if (!isset($validatedData['status_product'])) {
            $validatedData['status_product'] = '1';
        }

        try {
            if ($this->isEditing) {
                $product = MasterProdukLama::find($this->pcode_prc);
                if ($product) {
                    $product->update($validatedData);
                    session()->flash('message', 'Data produk berhasil diperbarui.');
                }
            } else {
                MasterProdukLama::create($validatedData);
                session()->flash('message', 'Data produk berhasil ditambahkan.');
            }
            
            $this->isFormModalOpen = false;
            $this->resetForm();
            
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
        $this->resetValidation();
        $this->pcode_prc = '';
        $this->nama_produk = '';
        $this->status_product = '1';
        $this->uom1 = '';
        $this->uom2 = '';
        $this->uom3 = '';
        $this->crttopcs = '';
        $this->crttopack = '';
        $this->packtopcs = '';
        $this->pricehrt = '';
        $this->produk_line = '';
        $this->brand = '';
        $this->divisi = '';
        $this->kategory = '';
        $this->subbrand = '';
        $this->topitem = '';
        $this->promo_group = '';
    }
}
