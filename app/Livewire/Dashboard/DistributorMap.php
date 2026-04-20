<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DistributorMap extends Component
{
    // Properti untuk filter
    public $regionFilter = '';
    public $areaFilter = '';
    public $statusFilter = '1'; // Default ke 'Aktif'

    // Properti untuk data dropdown
    public $regions = [];
    public $areas = [];

    // Properti KPI
    public $kpiCount = 0;
    public $kpiLabel = 'Distributor Aktif';

    /**
     * Inisialisasi komponen.
     */
    public function mount()
    {
        $this->regions = MasterRegion::orderBy('region_name')->get();
        $this->areas = collect();
    }

    /**
     * Dipanggil saat $regionFilter berubah.
     */
    public function updatedRegionFilter($value)
    {
        $this->reset('areaFilter');
        $this->areas = $value ? MasterArea::where('region_code', $value)->orderBy('area_name')->get() : collect();
        $this->loadDashboardData(); // Perbarui data
    }

    /**
     * Dipanggil saat $areaFilter berubah.
     */
    public function updatedAreaFilter()
    {
        $this->loadDashboardData(); // Perbarui data
    }

    /**
     * Dipanggil saat $statusFilter berubah.
     */
    public function updatedStatusFilter()
    {
        $this->loadDashboardData(); // Perbarui data
    }

    /**
     * Mengambil data KPI dan Peta, lalu mengirimkannya ke frontend.
     */
    #[On('map:ready')]
    public function loadDashboardData()
    {
        // 1. Buat Query Dasar
        $query = MasterDistributor::query();

        if ($this->regionFilter) {
            $query->where('region_code', $this->regionFilter);
        }
        if ($this->areaFilter) {
            $query->where('area_code', $this->areaFilter);
        }
        
        // [PERUBAHAN] Terapkan filter status di query utama
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        // 2. Ambil data mentah
        // [PERUBAHAN] Tambahkan region_code ke select
        $filteredDistributors = $query->select('is_active', 'latitude', 'longitude', 'distributor_name', 'region_code')
                                      ->get();
        
        // 3. Hitung KPI
        $this->kpiCount = $filteredDistributors->count();

        if ($this->statusFilter === '1') {
            $this->kpiLabel = 'Total Distributor Aktif';
        } elseif ($this->statusFilter === '0') {
            $this->kpiLabel = 'Total Distributor Tdk Aktif';
        } else {
            $this->kpiLabel = 'Semua Distributor';
        }

        // 4. Siapkan Data Peta
        $locations = $filteredDistributors
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->map(function ($dist) {
                return [
                    'lat' => (float)$dist->latitude,
                    'lng' => (float)$dist->longitude,
                    'name' => $dist->distributor_name,
                    'status' => $dist->is_active ? 'Aktif' : 'Tidak Aktif',
                    'region_code' => $dist->region_code, // [DITAMBAHKAN]
                ];
            })
            ->values();

        // 5. Kirim data ke JavaScript
        $this->dispatch('dataUpdated', [
            'kpi' => [
                'count' => number_format($this->kpiCount),
                'label' => $this->kpiLabel,
            ],
            'locations' => $locations
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.distributor-map')
               ->layout('layouts.app');
    }
}