<?php

namespace App\Livewire\Mapping\Product;

use Livewire\Component;
use App\Models\ProductMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ProductMaster;
use Illuminate\Validation\Rule;

class Create extends Component
{
    // Properti Filter
    public $regionFilter;
    public $areaFilter;
    
    // Properti Form
    public $distributor_code;
    public $product_code_dist;
    public $product_name_dist;
    public $product_code_prc;
    public $productSearch = '';

    // Data Dropdown
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $principalProducts = [];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }

        return $query;
    }

    /**
     * Helper untuk memastikan distributor terkait berada di dalam wilayah user
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // 1. Terapkan akses region ke dropdown
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national'
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributor_code']);
        
        $query = MasterArea::query();
        if ($value) {
            $query->where('region_code', $value);
        }
        
        // Amankan dropdown area
        $this->applyRegionAccess($query);
        
        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributor_code');
        
        $query = MasterDistributor::query();
        if ($value) {
            $query->where('area_code', $value);
        }
        
        // Amankan dropdown distributor
        $this->applyRegionAccess($query);
        
        $this->distributors = $value ? $query->orderBy('distributor_name')->get() : collect();
    }

    public function updatedProductSearch($value)
    {
        if(strlen($value) < 2) {
            $this->principalProducts = collect();
            return;
        }
        // Produk Master bersifat global, jadi tidak perlu difilter per region
        $this->principalProducts = ProductMaster::where('product_name', 'ILIKE', '%' . $value . '%')
                                    ->orWhere('product_id', 'ILIKE', '%' . $value . '%')
                                    ->orderBy('is_active', 'desc')
                                    ->limit(50)
                                    ->get();
    }

    public function selectProduct($productCode)
    {
        $this->product_code_prc = $productCode;
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
                // Kombinasi distributor dan kode produk dist harus unik
                Rule::unique('product_mappings')->where(function ($query) {
                    return $query->where('distributor_code', $this->distributor_code);
                }),
            ],
            'product_name_dist' => 'nullable|string|max:255',
            'product_code_prc' => 'nullable|string|max:255|exists:product_masters,product_id',
        ];
    }

    protected $messages = [
        'product_code_dist.unique' => 'Kode Produk Distributor ini sudah dipetakan untuk distributor yang dipilih.',
    ];

    public function save()
    {
        $validatedData = $this->validate();

        // Security Check Ekstra:
        // Pastikan kode distributor yang disubmit benar-benar ada dalam wilayah otoritas user
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk memetakan produk ke distributor tersebut.');
            return;
        }

        ProductMapping::create($validatedData);

        session()->flash('message', 'Pemetaan Produk berhasil ditambahkan.');
        return redirect()->route('product-mappings.index');
    }

    public function render()
    {
        return view('livewire.mapping.product.create')->layout('layouts.app');
    }
}