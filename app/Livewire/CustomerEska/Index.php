<?php

namespace App\Livewire\CustomerEska;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerEskaExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    // Filter Variables
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    
    // Search & UI Variables
    public $search = '';
    public $isFiltered = false;

    // Dropdown Data Lists
    public $regions = [];
    public $areas = [];
    public $distributors = [];

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

    public function mount()
    {
        // Load Region Awal dengan proteksi
        $query = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('region_code', '!=', 'HOINA')
            ->whereNotNull('region_code')
            ->distinct();

        $this->applyRegionAccess($query);
        $this->regions = $query->orderBy('region_name')->get();

        // Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->selectedRegion = $this->regions->first()->region_code;
            $this->updatedSelectedRegion($this->selectedRegion);
        }
    }

    // --- DEPENDENT DROPDOWN ---

    public function updatedSelectedRegion($value)
    {
        $this->reset(['selectedArea', 'selectedDistributor', 'areas', 'distributors', 'isFiltered', 'search']);
        
        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $value)
                ->select('area_code', 'area_name')
                ->distinct();
            
            // Amankan dropdown area
            $this->applyRegionAccess($query);

            $this->areas = $query->orderBy('area_name')->get();
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->reset(['selectedDistributor', 'distributors', 'isFiltered', 'search']);

        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('area_code', $value)
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct();

            // Amankan dropdown distributor
            $this->applyRegionAccess($query);

            $this->distributors = $query->orderBy('distributor_name')->get();
        }
    }

    public function updatedSelectedDistributor()
    {
        // Reset filter flag jika distributor berubah
        $this->isFiltered = false;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- ACTIONS ---

    public function filter()
    {
        $this->validate([
            'selectedRegion' => 'required',
            'selectedArea' => 'required',
            'selectedDistributor' => 'required',
        ]);

        // Security check: Pastikan region yang difilter valid sesuai akses login
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->selectedRegion, $user->region_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk memfilter wilayah ini.');
                return;
            }
        }

        $this->isFiltered = true;
        $this->resetPage();
    }

    public function export()
    {
        // Validasi sebelum export
        $this->validate([
            'selectedRegion' => 'required',
            'selectedArea' => 'required',
            'selectedDistributor' => 'required',
        ]);

        // Security check tambahan untuk memastikan manipulasi front-end tidak tembus
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->selectedRegion, $user->region_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk mengekspor data wilayah ini.');
                return;
            }
        }

        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = 'Customer_Eska_' . $timestamp . '.xlsx';

        return Excel::download(
            new CustomerEskaExport(
                $this->selectedRegion, 
                $this->selectedArea, 
                $this->selectedDistributor
            ), 
            $filename
        );
    }

    public function render()
    {
        $data = [];

        if ($this->isFiltered) {
            $query = DB::table('customer_prc_eska as cpe')
                ->select(
                    'md.region_name',
                    'md.area_name',
                    'cpe.kodecabang',
                    'md.distributor_name',
                    'cpe.custno',
                    'cpe.custname',
                    'cpe.custadd1'
                )
                ->leftJoin('distributor_implementasi_eskalink as die', 'cpe.kodecabang', '=', 'die.eskalink_code')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->where('md.region_code', $this->selectedRegion)
                ->where('md.area_code', $this->selectedArea)
                ->where('md.distributor_code', $this->selectedDistributor);

            // --- PROTEKSI KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'md.region_code');

            // Logic Search
            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('cpe.custname', 'ilike', '%'.$this->search.'%') // Gunakan 'ilike' untuk PostgreSQL
                      ->orWhere('cpe.custno', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cpe.custadd1', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->orderBy('cpe.custname')->paginate(10);
        }

        return view('livewire.customer-eska.index', [
            'customers' => $data
        ])->layout('layouts.app');
    }
}