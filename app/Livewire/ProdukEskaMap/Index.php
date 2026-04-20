<?php

namespace App\Livewire\ProdukEskaMap;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\ProdukEskaMapExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    // Filter Utama
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedDistributor = '';
    
    // Filter Export (Produk Multi Select)
    public $selectedProducts = []; 

    // UI Variables
    public $search = '';
    public $isFiltered = false;
    public $showFilterModal = false;
    public $showExportModal = false;

    // Dropdown Data
    public $regions = [];
    public $areas = [];
    public $distributors = [];
    public $productOptions = []; // Opsi produk untuk modal export

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
        // Load Region Awal dengan Proteksi
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
        $this->reset(['selectedArea', 'selectedDistributor', 'areas', 'distributors', 'isFiltered', 'selectedProducts', 'productOptions']);
        
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
        $this->reset(['selectedDistributor', 'distributors', 'isFiltered', 'selectedProducts', 'productOptions']);

        if (!empty($value)) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('area_code', $value)
                ->select('distributor_code', 'distributor_name','is_active')
                ->distinct();

            // Amankan dropdown distributor
            $this->applyRegionAccess($query);

            $this->distributors = $query->orderBy('is_active', 'desc')
                ->orderBy('distributor_name')
                ->get();
        }
    }

    public function updatedSelectedDistributor()
    {
        $this->isFiltered = false;
        $this->selectedProducts = []; 
        $this->loadProducts(); // Load produk saat distributor dipilih
    }

    // Load list produk untuk filter export
    public function loadProducts()
    {
        if ($this->selectedDistributor) {
            $this->productOptions = DB::table('product_mappings')
                ->where('distributor_code', $this->selectedDistributor)
                ->select('product_code_dist', 'product_name_dist')
                ->distinct()
                ->orderBy('product_code_dist')
                ->get();
        } else {
            $this->productOptions = [];
        }
    }

    public function selectAllProducts()
    {
        $this->selectedProducts = $this->productOptions->pluck('product_code_dist')->toArray();
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
        $this->showFilterModal = false;
        $this->resetPage();
        
        $this->loadProducts(); // Pastikan opsi produk siap
    }

    public function openExportModal()
    {
        if (!$this->isFiltered) {
            $this->dispatch('notify', message: 'Silakan filter data terlebih dahulu.');
            return;
        }
        $this->showExportModal = true;
    }

    public function export()
    {
        $this->validate([
            'selectedRegion' => 'required',
            'selectedArea' => 'required',
            'selectedDistributor' => 'required',
        ]);

        // Security check tambahan untuk memastikan manipulasi front-end (export) tidak tembus
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->selectedRegion, $user->region_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk mengekspor data wilayah ini.');
                $this->showExportModal = false;
                return;
            }
        }

        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = 'Produk_Eska_Map_' . $timestamp . '.xlsx';

        $this->showExportModal = false;

        return Excel::download(
            new ProdukEskaMapExport(
                $this->selectedRegion, 
                $this->selectedArea, 
                $this->selectedDistributor,
                $this->selectedProducts
            ), 
            $filename
        );
    }

    public function render()
    {
        $data = [];

        if ($this->isFiltered) {
            $query = DB::table('product_mappings as pm')
                ->select(
                    'die.eskalink_code_dist',
                    'pm.product_code_dist',
                    'pm.product_name_dist',
                    'pm.product_code_prc',
                    'pmm.product_name'
                )
                ->leftJoin('distributor_implementasi_eskalink as die', 'pm.distributor_code', '=', 'die.distributor_code')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->leftJoin('product_masters as pmm', 'pm.product_code_prc', '=', 'pmm.product_id')
                ->where('md.region_code', $this->selectedRegion)
                ->where('md.area_code', $this->selectedArea)
                ->where('md.distributor_code', $this->selectedDistributor);

            // --- PROTEKSI KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'md.region_code');

            // Search Logic
            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('pm.product_name_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('pm.product_code_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('pmm.product_name', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->orderBy('pm.product_code_dist')->paginate(10);
        }

        return view('livewire.produk-eska-map.index', [
            'products' => $data
        ])->layout('layouts.app');
    }
}