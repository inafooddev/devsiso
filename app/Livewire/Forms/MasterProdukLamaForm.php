<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\MasterProdukLama;
use App\Models\ProductMaster;

class MasterProdukLamaForm extends Form
{
    #[Validate('required|string|max:255|exists:product_masters,product_id')]
    public $pcode_prc = '';

    #[Validate('nullable|string|max:255')]
    public $nama_produk = '';

    #[Validate('nullable|string|max:50')]
    public $status_product = '1';

    #[Validate('nullable|string|max:50')]
    public $uom1 = '';

    #[Validate('nullable|string|max:50')]
    public $uom2 = '';

    #[Validate('nullable|string|max:50')]
    public $uom3 = '';

    #[Validate('nullable|numeric')]
    public $crttopcs = '';

    #[Validate('nullable|numeric')]
    public $crttopack = '';

    #[Validate('nullable|numeric')]
    public $packtopcs = '';

    #[Validate('nullable|numeric')]
    public $pricehrt = '';

    #[Validate('nullable|string|max:100')]
    public $produk_line = '';

    #[Validate('nullable|string|max:100')]
    public $brand = '';

    #[Validate('nullable|string|max:100')]
    public $divisi = '';

    #[Validate('nullable|string|max:100')]
    public $kategory = '';

    #[Validate('nullable|string|max:100')]
    public $subbrand = '';

    #[Validate('nullable|string|max:100')]
    public $topitem = '';

    #[Validate('nullable|string|max:100')]
    public $promo_group = '';

    public $isEditing = false;

    public function setProduct(MasterProdukLama $product)
    {
        $this->isEditing = true;
        
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
    }

    public function fetchFromProductMaster($pcode_prc)
    {
        if (empty($pcode_prc)) return;

        $product = ProductMaster::where('product_id', $pcode_prc)->first();
        if ($product) {
            $this->nama_produk = $product->product_name;
            $this->brand = $product->brand_name;
            $this->subbrand = $product->sub_brand_name;
            $this->produk_line = $product->line_name;
            $this->uom1 = $product->uom1;
            $this->uom2 = $product->uom2;
            $this->uom3 = $product->uom3;
            $this->status_product = '1';
        } else {
            // Reset fields if not found
            $this->nama_produk = '';
            $this->brand = '';
            $this->subbrand = '';
            $this->produk_line = '';
            $this->uom1 = '';
            $this->uom2 = '';
            $this->uom3 = '';
        }
    }

    public function store()
    {
        $rules = [
            'pcode_prc' => 'required|string|max:255|unique:master_produk_lama,pcode_prc|exists:product_masters,product_id',
        ];
        
        $validatedData = $this->validate(array_merge($this->getRules(), $rules));

        $validatedData = $this->prepareForSaving($validatedData);
        
        MasterProdukLama::create($validatedData);
        
        $this->reset();
    }

    public function update()
    {
        $validatedData = $this->validate();
        
        $validatedData = $this->prepareForSaving($validatedData);

        $product = MasterProdukLama::find($this->pcode_prc);
        if ($product) {
            $product->update($validatedData);
        }

        $this->reset();
        $this->isEditing = false;
    }

    protected function prepareForSaving($validatedData)
    {
        // Convert empty strings to null for numeric casts in Postgres
        foreach ($validatedData as $key => $value) {
            if ($value === '') {
                $validatedData[$key] = null;
            }
        }

        if (!isset($validatedData['status_product'])) {
            $validatedData['status_product'] = '1';
        }

        return $validatedData;
    }
}
