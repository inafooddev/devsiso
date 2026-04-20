<?php

namespace App\Livewire\SellOut\Export;

use Livewire\Component;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Exports\DetailSellOutExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class Index extends Component
{
    // Filter
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter = []; // Checkbox, jadi array
    public $monthFilter;
    public $yearFilter;

    // Dropdown data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    public function mount()
    {
        $this->regions = MasterRegion::orderBy('region_name')->get();
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
    }

    // [PERUBAHAN] Menerima satu nilai (dari radio)
    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        $this->areas = $value ? MasterArea::where('region_code', $value)->orderBy('area_name')->get() : collect();
        $this->distributors = collect();
    }

    // [PERUBAHAN] Menerima satu nilai (dari radio)
    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        $this->distributors = $value ? MasterDistributor::where('area_code', $value)->orderBy('distributor_name')->get() : collect();
    }

    // Helper untuk listbox distributor
    public function selectAllDistributors()
    {
        $this->distributorFilter = $this->distributors->pluck('distributor_code')->toArray();
    }

    public function clearDistributors()
    {
        $this->reset('distributorFilter');
    }

    // Validasi sebelum ekspor
    public function rules()
    {
        return [
            'regionFilter' => 'required',
            'areaFilter' => 'required',
            'distributorFilter' => 'required|array|min:1',
            'monthFilter' => 'required',
            'yearFilter' => 'required',
        ];
    }

    protected $messages = [
        'regionFilter.required' => 'Region wajib dipilih.',
        'areaFilter.required' => 'Area wajib dipilih.',
        'distributorFilter.required' => 'Minimal satu distributor wajib dipilih.',
        'distributorFilter.min' => 'Minimal satu distributor wajib dipilih.',
    ];

    /**
     * Memulai proses ekspor
     */
    public function export()
    {
        $validatedData = $this->validate();

        $filters = [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'monthFilter' => $this->monthFilter,
            'yearFilter' => $this->yearFilter,
        ];
        
        $fileName = 'detail_sell_out_' . Carbon::create($this->yearFilter, $this->monthFilter)->format('M_Y') . '.xlsx';

        session()->flash('message', 'Ekspor sedang diproses. Harap tunggu...');

        return Excel::download(new DetailSellOutExport($filters), $fileName);
    }

    public function render()
    {
        return view('livewire.sell-out.export.index')->layout('layouts.app');
    }
}
