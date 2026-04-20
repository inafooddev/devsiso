<?php

namespace App\Livewire\CustomerEskaUnmap;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerEskaUnmapExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    // Filter Variables (Array untuk Multi Select)
    public $selectedMonth;
    public $selectedRegions = [];
    public $selectedAreas = [];
    public $selectedDistributors = [];
    
    // UI Variables
    public $search = '';
    public $isFiltered = false;

    // Data Dropdown Options
    public $regionsOption = [];
    public $areasOption = [];
    public $distributorsOption = [];

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
        $this->selectedMonth = date('Y-m');

        // Load Region Awal dengan Proteksi
        $query = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('region_code', '!=', 'HOINA')
            ->whereNotNull('region_code')
            ->distinct();

        $this->applyRegionAccess($query);
        $this->regionsOption = $query->orderBy('region_name')->get();

        // Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regionsOption) === 1) {
            $this->selectedRegions = [$this->regionsOption->first()->region_code];
            $this->updatedSelectedRegions();
        }
    }

    // --- SELECT ALL LOGIC ---

    public function selectAllRegions()
    {
        $this->selectedRegions = $this->regionsOption->pluck('region_code')->toArray();
        $this->updatedSelectedRegions();
    }

    public function selectAllAreas()
    {
        $this->selectedAreas = $this->areasOption->pluck('area_code')->toArray();
        $this->updatedSelectedAreas();
    }

    public function selectAllDistributors()
    {
        $this->selectedDistributors = $this->distributorsOption->pluck('distributor_code')->toArray();
    }

    // --- DEPENDENT DROPDOWN ---

    public function updatedSelectedRegions()
    {
        $this->selectedAreas = [];
        $this->selectedDistributors = [];
        $this->areasOption = [];
        $this->distributorsOption = [];
        $this->isFiltered = false;

        if (!empty($this->selectedRegions)) {
            $query = DB::table('master_distributors')
                ->whereIn('region_code', $this->selectedRegions)
                ->select('area_code', 'area_name')
                ->distinct();

            // Amankan dropdown area
            $this->applyRegionAccess($query);

            $this->areasOption = $query->orderBy('area_name')->get();
        }
    }

    public function updatedSelectedAreas()
    {
        $this->selectedDistributors = [];
        $this->distributorsOption = [];
        $this->isFiltered = false;

        if (!empty($this->selectedAreas)) {
            $query = DB::table('master_distributors')
                ->whereIn('region_code', $this->selectedRegions)
                ->whereIn('area_code', $this->selectedAreas)
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct();

            // Amankan dropdown distributor
            $this->applyRegionAccess($query);

            $this->distributorsOption = $query->orderBy('distributor_name')->get();
        }
    }

    public function updatedSelectedDistributors()
    {
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
            'selectedMonth' => 'required',
            'selectedRegions' => 'required|array|min:1',
            'selectedAreas' => 'required|array|min:1',
        ]);

        // Security check: Pastikan SEMUA region yang difilter (array) valid sesuai akses login
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $unauthorizedRegions = array_diff($this->selectedRegions, $user->region_code);
            if (!empty($unauthorizedRegions)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk memfilter beberapa wilayah yang dipilih.');
                return;
            }
        }

        $this->isFiltered = true;
        $this->resetPage();
    }

    public function export()
    {
        $this->validate([
            'selectedMonth' => 'required',
            'selectedRegions' => 'required|array|min:1',
        ]);

        // Security check tambahan untuk ekspor (Mencegah manipulasi user biasa di front-end)
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $unauthorizedRegions = array_diff($this->selectedRegions, $user->region_code);
            if (!empty($unauthorizedRegions)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk mengekspor data wilayah yang dipilih.');
                return;
            }
        }

        $dateParam = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth()->format('Y-m-d');
        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = 'Unmapped_Customer_' . $timestamp . '.xlsx';

        return Excel::download(
            new CustomerEskaUnmapExport(
                $dateParam,
                $this->selectedRegions, 
                $this->selectedAreas, 
                $this->selectedDistributors,
                $this->search
            ), 
            $filename
        );
    }

    public function render()
    {
        $data = [];

        if ($this->isFiltered) {
            $dateParam = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth()->format('Y-m-d');

            $query = DB::table('customer_map_eska as cme')
                ->select(
                    'cme.bln',
                    'md.region_name',
                    'md.area_name',
                    'cme.distid',
                    'cme.branch_dist',
                    'cme.custno_dist',
                    'cde.custname as dist_cust_name',
                    'cme.branch',
                    'cme.custno',
                    'cpe.custname as prc_cust_name'
                )
                ->leftJoin('customer_dist_eska as cde', function ($join) {
                    $join->on('cme.distid', '=', 'cde.distid')
                         ->on('cme.branch_dist', '=', 'cde.branch')
                         ->on('cme.custno_dist', '=', 'cde.custno');
                })
                ->leftJoin('customer_prc_eska as cpe', function ($join) {
                    $join->on('cme.branch', '=', 'cpe.kodecabang')
                         ->on('cme.custno', '=', 'cpe.custno');
                })
                ->leftJoin('distributor_implementasi_eskalink as die', function ($join) {
                    $join->on('cme.distid', '=', 'die.eskalink_code_dist')
                         ->on('cme.branch_dist', '=', 'die.eskalink_code_dist')
                         ->on('cme.branch', '=', 'die.eskalink_code');
                })
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->where('cme.bln', $dateParam);

            // --- PROTEKSI KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'md.region_code');

            // Filter Where In
            if (!empty($this->selectedRegions)) {
                $query->whereIn('md.region_code', $this->selectedRegions);
            }
            if (!empty($this->selectedAreas)) {
                $query->whereIn('md.area_code', $this->selectedAreas);
            }
            if (!empty($this->selectedDistributors)) {
                $query->whereIn('md.distributor_code', $this->selectedDistributors);
            }

            // Search
            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('cme.custno_dist', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cme.custno', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cde.custname', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->orderBy('cme.distid', 'asc')->paginate(20);
        }

        return view('livewire.customer-eska-unmap.index', [
            'customers' => $data
        ])->layout('layouts.app');
    }
}