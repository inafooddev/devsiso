<?php

namespace App\Livewire\Product\MasterProduct;

use Livewire\Component;
use App\Models\ProductMaster;
use App\Models\ProductLine;
use App\Models\ProductBrand;
use App\Models\ProductGroup;
use App\Models\ProductSubBrand;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Untuk logging

class Edit extends Component
{
    public ProductMaster $product; 

    // Properti Form 
    public $product_id;
    public $line_id;
    public $brand_id;
    public $product_group_id;
    public $sub_brand_id;
    public $product_name;
    public $is_active;
    public $base_unit;
    public $uom1, $uom2, $uom3;
    public $conv_unit1, $conv_unit2, $conv_unit3;
    public $price_zone1, $price_zone2, $price_zone3, $price_zone4, $price_zone5;
    public $selectedCategories = [];

    // Properti Dropdown
    public $productLines = [];
    public $productBrands = [];
    public $productGroups = [];
    public $productSubBrands = [];
    public $allCategories = [];

    public function mount(ProductMaster $product)
    {
        Log::info('Mounting Edit Component for Product ID: ' . $product->product_id); // Logging
        $this->product = $product;
        
        // Isi properti form secara manual
        $this->product_id = $product->product_id;
        $this->line_id = $product->line_id;
        $this->brand_id = $product->brand_id;
        $this->product_group_id = $product->product_group_id;
        $this->sub_brand_id = $product->sub_brand_id;
        $this->product_name = $product->product_name;
        $this->is_active = $product->is_active ? '1' : '0'; // Konversi ke string '1' atau '0'
        $this->base_unit = $product->base_unit;
        $this->uom1 = $product->uom1;
        $this->uom2 = $product->uom2;
        $this->uom3 = $product->uom3;
        $this->conv_unit1 = $product->conv_unit1 ?? null;
        $this->conv_unit2 = $product->conv_unit2 ?? null;
        $this->conv_unit3 = $product->conv_unit3 ?? null;
        $this->price_zone1 = $product->price_zone1 ?? null;
        $this->price_zone2 = $product->price_zone2 ?? null;
        $this->price_zone3 = $product->price_zone3 ?? null;
        $this->price_zone4 = $product->price_zone4 ?? null;
        $this->price_zone5 = $product->price_zone5 ?? null;

        // Muat kategori yang sudah terpilih
        $this->selectedCategories = $product->categories->pluck('category_id')->toArray();

        // Load data dropdown
        $this->productLines = ProductLine::orderBy('line_name')->get();
        $this->productBrands = ProductBrand::orderBy('brand_name')->get();
        $this->productGroups = ProductGroup::orderBy('brand_unit_name')->get();
        $this->productSubBrands = ProductSubBrand::orderBy('sub_brand_name')->get();
        $this->allCategories = Category::orderBy('category_name')->get();
        
        Log::info('Finished mounting. Product Name in component: ' . $this->product_name); // Logging
    }

    protected function rules()
    {
        return [
            'product_id' => [
                'required', 'string', 'max:15',
                Rule::unique('product_masters')->ignore($this->product->product_id, 'product_id') 
            ],
            'line_id' => 'required|string|exists:product_lines,line_id',
            'brand_id' => 'required|string|exists:product_brands,brand_id',
            'product_group_id' => 'required|string|exists:product_groups,product_group_id',
            'sub_brand_id' => 'nullable|string|exists:product_sub_brands,sub_brand_id',
            'product_name' => 'required|string|max:255',
            'is_active' => 'required|in:0,1',
            'base_unit' => 'required|string|max:20',
            'uom1' => 'nullable|string|max:20',
            'uom2' => 'nullable|string|max:20',
            'uom3' => 'nullable|string|max:20',
            'conv_unit1' => 'nullable|numeric|min:0|required_with:uom1',
            'conv_unit2' => 'nullable|numeric|min:0|required_with:uom2',
            'conv_unit3' => 'nullable|numeric|min:0|required_with:uom3',
            'price_zone1' => 'nullable|numeric|min:0',
            'price_zone2' => 'nullable|numeric|min:0',
            'price_zone3' => 'nullable|numeric|min:0',
            'price_zone4' => 'nullable|numeric|min:0',
            'price_zone5' => 'nullable|numeric|min:0',
            'selectedCategories' => 'nullable|array',
            'selectedCategories.*' => 'string|exists:categories,category_id',
        ];
    }

     protected $messages = [
        'conv_unit1.required_with' => 'Konversi Unit 1 harus diisi jika UOM 1 diisi.',
        'conv_unit2.required_with' => 'Konversi Unit 2 harus diisi jika UOM 2 diisi.',
        'conv_unit3.required_with' => 'Konversi Unit 3 harus diisi jika UOM 3 diisi.',
    ];

    public function update()
    {
        $validatedData = $this->validate();
        $validatedData['is_active'] = (bool)$validatedData['is_active']; 

        $line = ProductLine::find($validatedData['line_id']);
        $brand = ProductBrand::find($validatedData['brand_id']);
        $group = ProductGroup::find($validatedData['product_group_id']);
        $subBrand = !empty($validatedData['sub_brand_id']) ? ProductSubBrand::find($validatedData['sub_brand_id']) : null;

        $validatedData['line_name'] = $line->line_name ?? $this->product->line_name; 
        $validatedData['brand_name'] = $brand->brand_name ?? $this->product->brand_name;
        $validatedData['brand_unit_name'] = $group->brand_unit_name ?? $this->product->brand_unit_name;
        $validatedData['sub_brand_name'] = $subBrand->sub_brand_name ?? null;

        $categoryIds = $validatedData['selectedCategories'] ?? [];
        unset($validatedData['selectedCategories']);

        $this->product->update($validatedData);
        $this->product->categories()->sync($categoryIds);

        session()->flash('message', 'Master Product berhasil diperbarui.');
        return redirect()->route('product-masters.index');
    }

    public function render()
    {
        return view('livewire.master-product.master.edit')->layout('layouts.app');
    }
}

