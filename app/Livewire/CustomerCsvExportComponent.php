<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\CustomerCsvExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerCsvExportComponent extends Component
{
    use WithPagination;

    // Form Inputs
    public $selectedMonth;
    
    // Filter State
    public $selectedRegions = []; 
    public $selectedAreas = [];
    public $selectedDistributors = [];

    // Data Options
    public $regionsOption = [];
    public $areasOption = [];
    public $distributorsOption = [];

    // Search & UI State
    public $search = '';
    public $isFiltered = false; // Default false agar data tidak langsung dimuat

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

    // --- Dependent Dropdown Logic ---

    public function updatedSelectedRegions()
    {
        $this->selectedAreas = [];
        $this->selectedDistributors = [];
        $this->areasOption = [];
        $this->distributorsOption = [];
        $this->isFiltered = false; // Reset filter jika pilihan berubah (opsional)

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
        $this->isFiltered = false; // Reset filter jika pilihan berubah

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- Actions ---

    public function filter()
    {
        $this->validate([
            'selectedMonth' => 'required',
            'selectedRegions' => 'required|array|min:1', 
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

        $timestamp = Carbon::now();
        $filename = 'PDAMASTER_SAP_' . $timestamp->format('Ymd_His') . '.txt';

        return Excel::download(
            new CustomerCsvExport(
                $this->selectedMonth, 
                $this->selectedRegions, 
                $this->selectedAreas, 
                $this->selectedDistributors, 
                $timestamp
            ), 
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function render()
    {
        $customers = [];

        // Hanya jalankan query jika tombol filter sudah ditekan
        if ($this->isFiltered) {
            $startDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->endOfMonth()->format('Y-m-d');

            $query = DB::table('customer_prc_eska as cpe')
                ->select(
                    'md.region_name',
                    'md.area_name',
                    'cpe.kodecabang',
                    'md.distributor_name',
                    'cpe.custno',
                    'cpe.custname',
                    'cpe.custadd1',
                    'cpe.ccity',
                    'cpe.cterm',
                    'cpe.typeout',
                    'cpe.grupout',
                    'cpe.gharga',
                    'cpe.flagpay',
                    'cpe.flagout'
                )
                ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'cpe.kodecabang')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->whereBetween('cpe.bln', [$startDate, $endDate]);

            // --- PROTEKSI KEAMANAN DATA ---
            $this->applyRegionAccess($query, 'md.region_code');

            if (!empty($this->selectedRegions)) {
                $query->whereIn('md.region_code', $this->selectedRegions);
            }
            if (!empty($this->selectedAreas)) {
                $query->whereIn('md.area_code', $this->selectedAreas);
            }
            if (!empty($this->selectedDistributors)) {
                $query->whereIn('md.distributor_code', $this->selectedDistributors);
            }

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('cpe.custname', 'ilike', '%'.$this->search.'%')
                      ->orWhere('cpe.custno', 'ilike', '%'.$this->search.'%');
                });
            }

            $customers = $query->orderBy('cpe.custno')->paginate(10);
        }

        return view('livewire.customer-csv-export-component', [
            'customers' => $customers
        ])->layout('layouts.app');
    }
}