<?php

namespace App\Livewire\Mapping\Product;

use Livewire\Component;
use App\Models\ProductMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ProductMaster;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class Edit extends Component
{
    public ProductMapping $mapping;

    // Properti Filter
    public $regionFilter;
    public $areaFilter;
    
    // Properti Form
    public $distributor_code;
    public $product_code_dist;
    public $product_name_dist;
    public $product_code_prc;
    public $productSearch = '';
    public $selectedProductName = ''; // Untuk menampilkan nama produk PRC yg dipilih

    // Data Dropdown
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $principalProducts = [];

    public function mount($id)
    {
        try {
            $decryptedId = base64_decode($id);
            $this->mapping = ProductMapping::with('masterDistributor.area.region')->findOrFail($decryptedId);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memuat data mapping.');
            return redirect()->route('product-mappings.index');
        }

        // Isi properti form
        $this->distributor_code = $this->mapping->distributor_code;
        $this->product_code_dist = $this->mapping->product_code_dist;
        $this->product_name_dist = $this->mapping->product_name_dist;
        $this->product_code_prc = $this->mapping->product_code_prc;

        // Isi data filter untuk dropdown
        $this->regions = MasterRegion::orderBy('region_name')->get();
        if ($this->mapping->masterDistributor && $this->mapping->masterDistributor->area && $this->mapping->masterDistributor->area->region) {
            $this->regionFilter = $this->mapping->masterDistributor->area->region->region_code;
            $this->areas = MasterArea::where('region_code', $this->regionFilter)->orderBy('area_name')->get();
            $this->areaFilter = $this->mapping->masterDistributor->area_code;
            $this->distributors = MasterDistributor::where('area_code', $this->areaFilter)->orderBy('distributor_name')->get();
        }
        
        // Isi nama produk principal
        if($this->product_code_prc) {
            $product = ProductMaster::find($this->product_code_prc);
            $this->selectedProductName = $product ? $product->product_name : '';
        }
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributor_code']);
        $this->areas = $value ? MasterArea::where('region_code', $value)->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributor_code');
        $this->distributors = $value ? MasterDistributor::where('area_code', $value)->orderBy('distributor_name')->get() : collect();
    }
    
    public function updatedProductSearch($value)
    {
        if(strlen($value) < 2) {
            $this->principalProducts = collect();
            return;
        }
        $this->principalProducts = ProductMaster::where('product_name', 'ILIKE', '%' . $value . '%')
                                    ->orWhere('product_id', 'ILIKE', '%' . $value . '%')
                                    ->limit(10)
                                    ->get();
    }

    public function selectProduct($productCode, $productName)
    {
        $this->product_code_prc = $productCode;
        $this->selectedProductName = $productName;
        $this->productSearch = '';
        $this->principalProducts = collect();
    }

    protected function rules()
    {
        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'product_code_dist' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_mappings')->where(function ($query) {
                    return $query->where('distributor_code', $this->distributor_code);
                })->ignore($this->mapping->id),
            ],
            'product_name_dist' => 'nullable|string|max:255',
            'product_code_prc' => 'nullable|string|max:255|exists:product_masters,product_id',
        ];
    }

    protected $messages = [
        'product_code_dist.unique' => 'Kode Produk Distributor ini sudah dipetakan untuk distributor yang dipilih.',
    ];

    public function update()
    {
        $validatedData = $this->validate();
        $this->mapping->update($validatedData);

        session()->flash('message', 'Pemetaan Produk berhasil diperbarui.');
        return redirect()->route('product-mappings.index');
    }

    public function render()
    {
        return view('livewire.mapping.product.edit')->layout('layouts.app');
    }
}
