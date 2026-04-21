<?php

namespace App\Livewire\Product\MasterProduct;

use Livewire\Component;
use App\Models\ProductMaster;
use App\Models\ProductLine;
use App\Models\ProductBrand;
use App\Models\ProductGroup;
use App\Models\ProductSubBrand;
use App\Models\Category;
use App\Exports\ProductMastersExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filter & Pagination
    public $search = '';
    public $statusFilter = '';

    // Modal States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $productIdToDelete;

    // Form Fields
    public $product_id;
    public $line_id;
    public $brand_id;
    public $product_group_id;
    public $sub_brand_id;
    public $product_name;
    public $is_active = 1;
    public $base_unit;
    public $uom1, $uom2, $uom3;
    public $conv_unit1, $conv_unit2, $conv_unit3;
    public $price_zone1, $price_zone2, $price_zone3, $price_zone4, $price_zone5;
    public $selectedCategories = [];

    // Dropdown Data
    public $productLines = [];
    public $productBrands = [];
    public $productGroups = [];
    public $productSubBrands = [];
    public $allCategories = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'product_id' => [
                'required', 'string', 'max:15',
                $this->isEditing
                    ? Rule::unique('product_masters')->ignore($this->product_id, 'product_id')
                    : Rule::unique('product_masters', 'product_id'),
            ],
            'line_id'          => 'required|string|exists:product_lines,line_id',
            'brand_id'         => 'required|string|exists:product_brands,brand_id',
            'product_group_id' => 'required|string|exists:product_groups,product_group_id',
            'sub_brand_id'     => 'nullable|string|exists:product_sub_brands,sub_brand_id',
            'product_name'     => 'required|string|max:255',
            'is_active'        => 'required|in:0,1',
            'base_unit'        => 'required|string|max:20',
            'uom1'             => 'nullable|string|max:20',
            'uom2'             => 'nullable|string|max:20',
            'uom3'             => 'nullable|string|max:20',
            'conv_unit1'       => 'nullable|numeric|min:0|required_with:uom1',
            'conv_unit2'       => 'nullable|numeric|min:0|required_with:uom2',
            'conv_unit3'       => 'nullable|numeric|min:0|required_with:uom3',
            'price_zone1'      => 'nullable|numeric|min:0',
            'price_zone2'      => 'nullable|numeric|min:0',
            'price_zone3'      => 'nullable|numeric|min:0',
            'price_zone4'      => 'nullable|numeric|min:0',
            'price_zone5'      => 'nullable|numeric|min:0',
            'selectedCategories'   => 'nullable|array',
            'selectedCategories.*' => 'string|exists:categories,category_id',
        ];
    }

    protected $messages = [
        'conv_unit1.required_with' => 'Konversi Unit 1 harus diisi jika UOM 1 diisi.',
        'conv_unit2.required_with' => 'Konversi Unit 2 harus diisi jika UOM 2 diisi.',
        'conv_unit3.required_with' => 'Konversi Unit 3 harus diisi jika UOM 3 diisi.',
    ];

    public function mount()
    {
        $this->productLines    = ProductLine::orderBy('line_name')->get();
        $this->productBrands   = ProductBrand::orderBy('brand_name')->get();
        $this->productGroups   = ProductGroup::orderBy('brand_unit_name')->get();
        $this->productSubBrands = ProductSubBrand::orderBy('sub_brand_name')->get();
        $this->allCategories   = Category::orderBy('category_name')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // --- Modal Operations ---

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($productId)
    {
        $this->resetValidation();
        $product = ProductMaster::findOrFail($productId);

        $this->product_id        = $product->product_id;
        $this->line_id           = $product->line_id;
        $this->brand_id          = $product->brand_id;
        $this->product_group_id  = $product->product_group_id;
        $this->sub_brand_id      = $product->sub_brand_id;
        $this->product_name      = $product->product_name;
        $this->is_active         = $product->is_active ? 1 : 0;
        $this->base_unit         = $product->base_unit;
        $this->uom1              = $product->uom1;
        $this->uom2              = $product->uom2;
        $this->uom3              = $product->uom3;
        $this->conv_unit1        = $product->conv_unit1;
        $this->conv_unit2        = $product->conv_unit2;
        $this->conv_unit3        = $product->conv_unit3;
        $this->price_zone1       = $product->price_zone1;
        $this->price_zone2       = $product->price_zone2;
        $this->price_zone3       = $product->price_zone3;
        $this->price_zone4       = $product->price_zone4;
        $this->price_zone5       = $product->price_zone5;
        $this->selectedCategories = $product->categories->pluck('category_id')->toArray();

        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->product_id = $this->line_id = $this->brand_id = null;
        $this->product_group_id = $this->sub_brand_id = $this->product_name = null;
        $this->base_unit = $this->uom1 = $this->uom2 = $this->uom3 = null;
        $this->conv_unit1 = $this->conv_unit2 = $this->conv_unit3 = null;
        $this->price_zone1 = $this->price_zone2 = $this->price_zone3 = null;
        $this->price_zone4 = $this->price_zone5 = null;
        $this->is_active = 1;
        $this->selectedCategories = [];
    }

    public function save()
    {
        $validatedData = $this->validate();
        $validatedData['is_active'] = (bool) $validatedData['is_active'];

        $line     = ProductLine::find($this->line_id);
        $brand    = ProductBrand::find($this->brand_id);
        $group    = ProductGroup::find($this->product_group_id);
        $subBrand = $this->sub_brand_id ? ProductSubBrand::find($this->sub_brand_id) : null;

        $validatedData['line_name']      = $line->line_name ?? null;
        $validatedData['brand_name']     = $brand->brand_name ?? null;
        $validatedData['brand_unit_name']= $group->brand_unit_name ?? null;
        $validatedData['sub_brand_name'] = $subBrand->sub_brand_name ?? null;

        $categoryIds = $validatedData['selectedCategories'] ?? [];
        unset($validatedData['selectedCategories']);

        if ($this->isEditing) {
            $product = ProductMaster::findOrFail($this->product_id);
            $product->update($validatedData);
            $product->categories()->sync($categoryIds);
            session()->flash('message', 'Master Product berhasil diperbarui.');
        } else {
            $product = ProductMaster::create($validatedData);
            if (!empty($categoryIds)) {
                $product->categories()->attach($categoryIds);
            }
            session()->flash('message', 'Master Product berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    // --- Render ---

    public function render()
    {
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

    // --- Delete ---

    public function confirmDelete($productId)
    {
        $this->productIdToDelete = $productId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $product = ProductMaster::find($this->productIdToDelete);
        if ($product) {
            $product->delete();
            session()->flash('message', 'Master Product berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }

    public function export()
    {
        return Excel::download(new ProductMastersExport(), 'master_products_all.xlsx');
    }
}
