<?php

namespace App\Livewire\Mapping\Salesman;

use Livewire\Component;
use App\Models\SalesmanMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\Salesman;
use Illuminate\Validation\Rule;

class Create extends Component
{
    // Filter
    public $regionFilter;
    public $areaFilter;

    // Form
    public $distributor_code;
    public $salesman_code_dist;
    public $salesman_name_dist;
    public $salesman_code_prc;
    public $salesmanSearch = '';

    // Dropdown Data
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $principalSalesmans = [];

    public function mount()
    {
        $this->regions = MasterRegion::orderBy('region_name')->get();
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

    public function updatedSalesmanSearch($value)
    {
        if(strlen($value) < 2) {
            $this->principalSalesmans = collect();
            return;
        }
        $this->principalSalesmans = Salesman::where('salesman_name', 'ILIKE', '%' . $value . '%')
                                    ->orWhere('salesman_code', 'ILIKE', '%' . $value . '%')
                                    ->limit(10)
                                    ->get();
    }

    public function selectSalesman($salesmanCode)
    {
        $this->salesman_code_prc = $salesmanCode;
        $this->salesmanSearch = '';
        $this->principalSalesmans = collect();
    }

    protected function rules()
    {
        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'salesman_code_dist' => [
                'required', // Kode dist wajib diisi
                'string',
                'max:255',
                Rule::unique('salesman_mappings')->where(function ($query) {
                    return $query->where('distributor_code', $this->distributor_code);
                }),
            ],
            'salesman_name_dist' => 'nullable|string|max:255',
            'salesman_code_prc' => 'nullable|string|max:15|exists:salesmans,salesman_code',
        ];
    }

    protected $messages = [
        'salesman_code_dist.unique' => 'Kode Salesman Distributor ini sudah dipetakan untuk distributor yang dipilih.',
    ];

    public function save()
    {
        $validatedData = $this->validate();
        SalesmanMapping::create($validatedData);
        session()->flash('message', 'Pemetaan Salesman berhasil ditambahkan.');
        return redirect()->route('salesman-mappings.index');
    }

    public function render()
    {
        return view('livewire.mapping.salesman.create')->layout('layouts.app');
    }
}
