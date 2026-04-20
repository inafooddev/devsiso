<?php

namespace App\Livewire\Mapping\Salesman;

use Livewire\Component;
use App\Models\SalesmanMapping;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\Salesman;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public SalesmanMapping $mapping;

    // Filter
    public $regionFilter;
    public $areaFilter;

    // Form
    public $distributor_code;
    public $salesman_code_dist;
    public $salesman_name_dist;
    public $salesman_code_prc;

    // Dropdown Data
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $principalSalesmans = [];

    public function mount($id)
    {
        try {
            $decryptedId = base64_decode($id);
            $this->mapping = SalesmanMapping::with('masterDistributor.area.region', 'principalSalesman')->findOrFail($decryptedId);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memuat data mapping.');
            return redirect()->route('salesman-mappings.index');
        }

        // Isi form
        $this->distributor_code = $this->mapping->distributor_code;
        $this->salesman_code_dist = $this->mapping->salesman_code_dist;
        $this->salesman_name_dist = $this->mapping->salesman_name_dist;
        $this->salesman_code_prc = $this->mapping->salesman_code_prc;

        // Isi filter & data awal
        $this->regions = MasterRegion::orderBy('region_name')->get();
        if ($this->mapping->masterDistributor && $this->mapping->masterDistributor->area && $this->mapping->masterDistributor->area->region) {
            $this->regionFilter = $this->mapping->masterDistributor->area->region->region_code;
            $this->areas = MasterArea::where('region_code', $this->regionFilter)->orderBy('area_name')->get();
            $this->areaFilter = $this->mapping->masterDistributor->area_code;
            $this->distributors = MasterDistributor::where('area_code', $this->areaFilter)->orderBy('distributor_name')->get();
            
            // Muat daftar salesman principal berdasarkan distributor awal
            $this->loadPrincipalSalesmans();
        }
    }
    
    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributor_code', 'salesman_code_prc', 'principalSalesmans']);
        $this->areas = $value ? MasterArea::where('region_code', $value)->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset(['distributor_code', 'salesman_code_prc', 'principalSalesmans']);
        $this->distributors = $value ? MasterDistributor::where('area_code', $value)->orderBy('distributor_name')->get() : collect();
    }

    public function updatedDistributorCode($value)
    {
        $this->reset('salesman_code_prc');
        $this->loadPrincipalSalesmans();
    }

    public function loadPrincipalSalesmans()
    {
        if ($this->distributor_code) {
            $this->principalSalesmans = Salesman::where('distributor_code', $this->distributor_code)
                                        ->orderBy('salesman_name')
                                        ->get();
        } else {
            $this->principalSalesmans = collect();
        }
    }

    protected function rules()
    {
        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'salesman_code_dist' => [
                'required',
                'string',
                'max:255',
                Rule::unique('salesman_mappings')->where(function ($query) {
                    return $query->where('distributor_code', $this->distributor_code);
                })->ignore($this->mapping->id),
            ],
            'salesman_name_dist' => 'nullable|string|max:255',
            'salesman_code_prc' => [
                'nullable', 
                'string', 
                'max:15', 
                Rule::exists('salesmans', 'salesman_code')->where(function ($query) {
                    return $query->where('distributor_code', $this->distributor_code);
                })
            ],
        ];
    }

    protected $messages = [
        'salesman_code_dist.unique' => 'Kode Salesman Distributor ini sudah dipetakan untuk distributor yang dipilih.',
        'salesman_code_prc.exists' => 'Salesman Principal tidak valid untuk distributor ini.',
    ];

    public function update()
    {
        $validatedData = $this->validate();
        $this->mapping->update($validatedData);
        session()->flash('message', 'Pemetaan Salesman berhasil diperbarui.');
        return redirect()->route('salesman-mappings.index');
    }

    public function render()
    {
        return view('livewire.mapping.salesman.edit')->layout('layouts.app');
    }
}