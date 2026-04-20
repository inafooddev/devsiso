<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CustomerData extends Component
{
    use WithPagination;

    // Filter Variables
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    
    // Search Variable
    public $search = '';

    // Dropdown Data Lists
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // State
    public $isFiltered = false;

    public function mount()
    {
        $this->regions = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->distinct()
            ->orderBy('region_name')
            ->get();
    }

    // --- DEPENDENT DROPDOWN LOGIC ---

    public function updatedSelectedRegion($value)
    {
        $this->reset(['selectedArea', 'selectedDistributor', 'areas', 'distributors', 'isFiltered', 'search']);
        
        if (!empty($value)) {
            $this->areas = DB::table('master_distributors')
                ->where('region_code', $value)
                ->select('area_code', 'area_name')
                ->distinct()
                ->orderBy('area_name')
                ->get();
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->reset(['selectedDistributor', 'distributors', 'isFiltered', 'search']);

        if (!empty($value)) {
            $this->distributors = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('area_code', $value)
                ->where('is_active', '1')
                ->select('distributor_code', 'distributor_name')
                ->distinct()
                ->orderBy('distributor_name')
                ->get();
        }
    }

    public function updatedSelectedDistributor()
    {
        $this->isFiltered = false;
    }

    // Reset pagination saat user mengetik search
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function filter()
    {
        $this->validate([
            'selectedRegion' => 'required',
            'selectedArea' => 'required',
            'selectedDistributor' => 'required',
        ]);

        $this->isFiltered = true;
        $this->resetPage();
    }

    public function render()
    {
        $data = [];

        if ($this->isFiltered) {
            $query = DB::table('customer_mappings as cm')
                ->select(
                    'md.region_name',
                    'md.area_name',
                    'md.distributor_code',
                    'md.distributor_name',
                    'cm.customer_code_dist',
                    DB::raw('min(rp.nama_toko) as nama_toko'),
                    DB::raw('min(rp.alamat) as alamat'),
                    'cm.customer_code_prc'
                )
                ->leftJoin('master_distributors as md', 'cm.distributor_code', '=', 'md.distributor_code')
                ->leftJoin('ro_penjualan as rp', function ($join) {
                    $join->on('cm.distributor_code', '=', 'rp.kd_distributor')
                         ->on('rp.kd_toko', '=', 'cm.customer_code_dist');
                })
                ->where('md.region_code', $this->selectedRegion)
                ->where('md.area_code', $this->selectedArea)
                ->where('md.distributor_code', $this->selectedDistributor);

            // LOGIKA PENCARIAN (SEARCH)
            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('rp.nama_toko', 'ilike', '%'.$this->search.'%') // Gunakan 'like' jika bukan Postgres
                      ->orWhere('rp.alamat', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cm.customer_code_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cm.customer_code_prc', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->groupBy(
                    'md.region_name',
                    'md.area_name',
                    'md.distributor_code',
                    'md.distributor_name',
                    'cm.customer_code_dist',
                    'cm.customer_code_prc'
                )
                ->paginate(10);
        }

        return view('livewire.customers.customer-data', [
            'customers' => $data
        ])->layout('layouts.app');
    }
}