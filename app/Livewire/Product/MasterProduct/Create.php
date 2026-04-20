<?php

namespace App\Livewire\Product\MasterProduct;

use Livewire\Component;
use App\Models\ProductMaster;
use App\Models\ProductLine;
use App\Models\ProductBrand;
use App\Models\ProductGroup;
use App\Models\ProductSubBrand;
use App\Models\Category; // [DITAMBAHKAN]

class Create extends Component
{
    // Properti untuk form
    public $product_id;
    public $line_id;
    public $brand_id;
    public $product_group_id;
    public $sub_brand_id;
    public $product_name;
    public $is_active = true;
    public $base_unit;
    public $uom1, $uom2, $uom3;
    public $conv_unit1, $conv_unit2, $conv_unit3;
    public $price_zone1, $price_zone2, $price_zone3, $price_zone4, $price_zone5;

    // [DITAMBAHKAN] Properti untuk menyimpan kategori yang dipilih
    public $selectedCategories = []; 

    // Properti untuk data dropdown
    public $productLines = [];
    public $productBrands = [];
    public $productGroups = [];
    public $productSubBrands = [];
    public $allCategories = []; // [DITAMBAHKAN]

    public function mount()
    {
        // Load data untuk dropdown
        $this->productLines = ProductLine::orderBy('line_name')->get();
        $this->productBrands = ProductBrand::orderBy('brand_name')->get();
        $this->productGroups = ProductGroup::orderBy('brand_unit_name')->get();
        $this->productSubBrands = ProductSubBrand::orderBy('sub_brand_name')->get();
        $this->allCategories = Category::orderBy('category_name')->get(); // [DITAMBAHKAN]
    }

    protected function rules()
    {
        return [
            'product_id' => 'required|string|max:15|unique:product_masters,product_id',
            'line_id' => 'required|string|exists:product_lines,line_id',
            'brand_id' => 'required|string|exists:product_brands,brand_id',
            'product_group_id' => 'required|string|exists:product_groups,product_group_id',
            'sub_brand_id' => 'nullable|string|exists:product_sub_brands,sub_brand_id', 
            'product_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'base_unit' => 'required|string|max:20',
            'uom1' => 'nullable|string|max:20',
            'uom2' => 'nullable|string|max:20',
            'uom3' => 'nullable|string|max:20',
            'conv_unit1' => 'nullable|numeric|min:0',
            'conv_unit2' => 'nullable|numeric|min:0',
            'conv_unit3' => 'nullable|numeric|min:0',
            'price_zone1' => 'nullable|numeric|min:0',
            'price_zone2' => 'nullable|numeric|min:0',
            'price_zone3' => 'nullable|numeric|min:0',
            'price_zone4' => 'nullable|numeric|min:0',
            'price_zone5' => 'nullable|numeric|min:0',
            // [DITAMBAHKAN] Validasi untuk kategori
            'selectedCategories' => 'nullable|array', 
            'selectedCategories.*' => 'string|exists:categories,category_id', // Pastikan ID kategori valid
        ];
    }

    public function save()
    {
        $validatedData = $this->validate();

        $line = ProductLine::find($validatedData['line_id']);
        $brand = ProductBrand::find($validatedData['brand_id']);
        $group = ProductGroup::find($validatedData['product_group_id']);
        $subBrand = !empty($validatedData['sub_brand_id']) ? ProductSubBrand::find($validatedData['sub_brand_id']) : null;

        $validatedData['line_name'] = $line->line_name ?? 'N/A';
        $validatedData['brand_name'] = $brand->brand_name ?? 'N/A';
        $validatedData['brand_unit_name'] = $group->brand_unit_name ?? 'N/A';
        $validatedData['sub_brand_name'] = $subBrand->sub_brand_name ?? null;

        // Pisahkan data kategori sebelum membuat produk
        $categoryIds = $validatedData['selectedCategories'] ?? [];
        unset($validatedData['selectedCategories']); 

        // Buat produk master
        $product = ProductMaster::create($validatedData);

        // [DITAMBAHKAN] Sinkronkan kategori ke tabel pivot
        $product->categories()->sync($categoryIds); 

        session()->flash('message', 'Master Product berhasil ditambahkan.');
        return redirect()->route('product-masters.index');
    }

    public function render()
    {
        return view('livewire.master-product.master.create')->layout('layouts.app');
    }
}

