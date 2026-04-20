<?php

namespace App\Livewire\SellingIn;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Report extends Component
{
    // Filter State (Multi Select Arrays)
    public array $selectedRegions = [];
    public array $selectedAreas = [];
    public array $selectedDistributors = [];
    
    // Date Range (Format: YYYY-MM)
    public $startMonth = '';
    public $endMonth = '';

    // Options for Dropdowns
    public $regionOptions = [];
    public $areaOptions = [];
    public $distributorOptions = [];

    // Table Data & Summary
    public $data = [];
    public $totalQtyKtn = 0;
    public $totalValueNet = 0;

    // UI State
    public $hasSearched = false;
    public $isFilterModalOpen = false;

    /**
     * Helper untuk memfilter Query Builder berdasarkan akses user.
     * Menggunakan join ke master_distributors berdasarkan kd_distributor dari selling_in.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        // Jika bukan admin dan punya batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->join('master_distributors', 'selling_in.kd_distributor', '=', 'master_distributors.distributor_code')
                  ->whereIn('master_distributors.region_code', $user->region_code);
        }

        return $query;
    }

    public function mount()
    {
        // Load initial Region options
        $query = DB::table('selling_in')
            ->select('selling_in.region')
            ->whereNotNull('selling_in.region')
            ->where('selling_in.region', '!=', '')
            ->distinct();

        // Terapkan JOIN dan Filter Akses
        $this->applyRegionAccess($query);

        $this->regionOptions = $query->orderBy('selling_in.region')
            ->pluck('selling_in.region')
            ->toArray();
    }

    /**
     * Chain Filter: Saat Region diperbarui
     */
    public function updatedSelectedRegions()
    {
        $this->selectedAreas = [];
        $this->selectedDistributors = [];
        $this->distributorOptions = [];

        if (!empty($this->selectedRegions)) {
            $query = DB::table('selling_in')
                ->select('selling_in.area')
                ->whereIn('selling_in.region', $this->selectedRegions)
                ->whereNotNull('selling_in.area')
                ->where('selling_in.area', '!=', '')
                ->distinct();

            $this->applyRegionAccess($query);

            $this->areaOptions = $query->orderBy('selling_in.area')
                ->pluck('selling_in.area')
                ->toArray();
        } else {
            $this->areaOptions = [];
        }
    }

    /**
     * Chain Filter: Saat Area diperbarui
     */
    public function updatedSelectedAreas()
    {
        $this->selectedDistributors = [];

        if (!empty($this->selectedAreas)) {
            $query = DB::table('selling_in')
                ->select('selling_in.kd_distributor', 'selling_in.nama_distributor_fix', 'selling_in.cabang')
                ->whereIn('selling_in.region', $this->selectedRegions)
                ->whereIn('selling_in.area', $this->selectedAreas)
                ->whereNotNull('selling_in.kd_distributor')
                ->distinct();

            $this->applyRegionAccess($query);

            $this->distributorOptions = $query->orderBy('selling_in.nama_distributor_fix')
                ->get()
                ->toArray();
        } else {
            $this->distributorOptions = [];
        }
    }

    /**
     * Menjalankan query pencarian utama
     */
    public function search()
    {
        $this->validate([
            'selectedRegions' => 'required|array|min:1',
            'selectedAreas' => 'required|array|min:1',
            'selectedDistributors' => 'required|array|min:1',
            'startMonth' => 'required|date_format:Y-m',
            'endMonth' => 'required|date_format:Y-m|after_or_equal:startMonth',
        ], [
            'selectedRegions.required' => 'Pilih minimal 1 Region.',
            'selectedAreas.required' => 'Pilih minimal 1 Area.',
            'selectedDistributors.required' => 'Pilih minimal 1 Distributor.',
            'startMonth.required' => 'Bulan awal wajib diisi.',
            'endMonth.required' => 'Bulan akhir wajib diisi.',
            'endMonth.after_or_equal' => 'Bulan akhir tidak boleh lebih kecil dari bulan awal.'
        ]);

        $startDate = Carbon::createFromFormat('Y-m', $this->startMonth)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromFormat('Y-m', $this->endMonth)->endOfMonth()->format('Y-m-d');

        $query = DB::table('selling_in')
            ->select(
                'selling_in.region',
                'selling_in.area',
                'selling_in.cabang',
                'selling_in.kd_distributor',
                'selling_in.nama_distributor_fix',
                DB::raw('SUM(selling_in.ktn_net) as qty_ktn'),
                DB::raw('SUM(selling_in.value_net) as value_net')
            )
            ->whereIn('selling_in.region', $this->selectedRegions)
            ->whereIn('selling_in.area', $this->selectedAreas)
            ->whereIn('selling_in.kd_distributor', $this->selectedDistributors)
            ->whereBetween('selling_in.bulan', [$startDate, $endDate]);

        // Proteksi Data dengan JOIN
        $this->applyRegionAccess($query);

        $this->data = $query->groupBy(
                'selling_in.region', 
                'selling_in.area', 
                'selling_in.cabang', 
                'selling_in.kd_distributor', 
                'selling_in.nama_distributor_fix'
            )
            ->orderBy('selling_in.region')
            ->orderBy('selling_in.area')
            ->get()
            ->toArray();

        $this->totalQtyKtn = array_sum(array_column($this->data, 'qty_ktn'));
        $this->totalValueNet = array_sum(array_column($this->data, 'value_net'));

        $this->hasSearched = true;
        $this->isFilterModalOpen = false;
    }

    public function openFilterModal()
    {
        $this->isFilterModalOpen = true;
    }

    public function closeFilterModal()
    {
        $this->isFilterModalOpen = false;
    }

    public function getActiveFiltersProperty()
    {
        if (!$this->hasSearched) return null;

        $startStr = Carbon::createFromFormat('Y-m', $this->startMonth)->translatedFormat('M Y');
        $endStr = Carbon::createFromFormat('Y-m', $this->endMonth)->translatedFormat('M Y');
        $period = $startStr === $endStr ? $startStr : "$startStr s/d $endStr";

        return [
            'period' => $period,
            'regions' => count($this->selectedRegions),
            'areas' => count($this->selectedAreas),
            'distributors' => count($this->selectedDistributors),
        ];
    }

    public function render()
    {
        return view('livewire.selling-in.report')->layout('layouts.app');
    }
}