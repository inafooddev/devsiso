<?php

namespace App\Livewire\Others\ProdukLama;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 50;

    // Modals state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    // Form fields
    public $original_nama_produk = ''; // Used for editing/deleting

    public $nama_produk = '';
    public $top_6 = '';
    public $grup_top_6 = '';
    public $top_6_item = '';
    public $reg_fest = '';
    public $pak = 0;
    public $pcs = 0;
    public $kategory = '';
    public $top_item = '';
    public $brand = '';
    public $subbrand = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetFields();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetFields();
    }

    public function store()
    {
        $this->validate([
            'nama_produk' => 'required|string|max:255|unique:produk_lama,nama_produk',
            'top_6' => 'nullable|string|max:255',
            'grup_top_6' => 'nullable|string|max:255',
            'top_6_item' => 'nullable|string|max:255',
            'reg_fest' => 'nullable|string|max:255',
            'pak' => 'nullable|integer',
            'pcs' => 'nullable|integer',
            'kategory' => 'nullable|string|max:255',
            'top_item' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'subbrand' => 'nullable|string|max:255',
        ]);

        DB::table('produk_lama')->insert([
            'nama_produk' => $this->nama_produk,
            'top_6' => $this->top_6,
            'grup_top_6' => $this->grup_top_6,
            'top_6_item' => $this->top_6_item,
            'reg_fest' => $this->reg_fest,
            'pak' => $this->pak ?: null,
            'pcs' => $this->pcs ?: null,
            'kategory' => $this->kategory,
            'top_item' => $this->top_item,
            'brand' => $this->brand,
            'subbrand' => $this->subbrand,
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Produk lama berhasil ditambahkan.');
        $this->closeCreateModal();
    }

    public function edit($nama_produk)
    {
        $this->resetFields();
        $produk = DB::table('produk_lama')->where('nama_produk', $nama_produk)->first();
        
        if ($produk) {
            $this->original_nama_produk = $produk->nama_produk;
            $this->nama_produk = $produk->nama_produk;
            $this->top_6 = $produk->top_6;
            $this->grup_top_6 = $produk->grup_top_6;
            $this->top_6_item = $produk->top_6_item;
            $this->reg_fest = $produk->reg_fest;
            $this->pak = $produk->pak;
            $this->pcs = $produk->pcs;
            $this->kategory = $produk->kategory;
            $this->top_item = $produk->top_item;
            $this->brand = $produk->brand;
            $this->subbrand = $produk->subbrand;

            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetFields();
    }

    public function update()
    {
        $this->validate([
            'nama_produk' => 'required|string|max:255|unique:produk_lama,nama_produk,' . $this->original_nama_produk . ',nama_produk',
            'top_6' => 'nullable|string|max:255',
            'grup_top_6' => 'nullable|string|max:255',
            'top_6_item' => 'nullable|string|max:255',
            'reg_fest' => 'nullable|string|max:255',
            'pak' => 'nullable|integer',
            'pcs' => 'nullable|integer',
            'kategory' => 'nullable|string|max:255',
            'top_item' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'subbrand' => 'nullable|string|max:255',
        ]);

        DB::table('produk_lama')->where('nama_produk', $this->original_nama_produk)->update([
            'nama_produk' => $this->nama_produk,
            'top_6' => $this->top_6,
            'grup_top_6' => $this->grup_top_6,
            'top_6_item' => $this->top_6_item,
            'reg_fest' => $this->reg_fest,
            'pak' => $this->pak ?: null,
            'pcs' => $this->pcs ?: null,
            'kategory' => $this->kategory,
            'top_item' => $this->top_item,
            'brand' => $this->brand,
            'subbrand' => $this->subbrand,
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Data produk lama berhasil diperbarui.');
        $this->closeEditModal();
    }

    public function confirmDelete($nama_produk)
    {
        $this->original_nama_produk = $nama_produk;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->original_nama_produk = '';
    }

    public function delete()
    {
        if ($this->original_nama_produk) {
            DB::table('produk_lama')->where('nama_produk', $this->original_nama_produk)->delete();
            $this->dispatch('show-toast', type: 'success', message: 'Produk lama berhasil dihapus.');
        }
        $this->closeDeleteModal();
    }

    private function resetFields()
    {
        $this->original_nama_produk = '';
        $this->nama_produk = '';
        $this->top_6 = '';
        $this->grup_top_6 = '';
        $this->top_6_item = '';
        $this->reg_fest = '';
        $this->pak = 0;
        $this->pcs = 0;
        $this->kategory = '';
        $this->top_item = '';
        $this->brand = '';
        $this->subbrand = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = DB::table('produk_lama');

        if (!empty($this->search)) {
            $q = '%' . trim($this->search) . '%';
            $query->where(function($sub) use ($q) {
                $sub->where('nama_produk', 'ilike', $q)
                    ->orWhere('brand', 'ilike', $q)
                    ->orWhere('subbrand', 'ilike', $q)
                    ->orWhere('kategory', 'ilike', $q)
                    ->orWhere('top_6', 'ilike', $q)
                    ->orWhere('grup_top_6', 'ilike', $q);
            });
        }

        $produks = $query->orderBy('nama_produk', 'asc')->paginate($this->perPage);

        return view('livewire.others.produklama.index', [
            'produks' => $produks
        ])->layout('layouts.app');
    }
}
