<?php

namespace App\Livewire\SellingOutEskalink;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SellingOutEskalink;
use App\Exports\SellingOutEskalinkExport;
use App\Imports\SellingOutEskalinkImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Filter Variables
    public $selectedMonth;
    public $selectedRegions = [];
    public $selectedAreas = [];
    public $selectedDistributors = [];
    
    // UI Variables
    public $search = '';
    public $isFiltered = false;
    public $showFilterModal = false;
    public $showImportModal = false;

    // Import Variable
    public $importFile;

    // Dropdown Data
    public $regionsOption = [];
    public $areasOption = [];
    public $distributorsOption = [];

    public $totalRecords = 0;

    public function mount()
    {
        $this->selectedMonth = date('Y-m');
        $this->totalRecords = SellingOutEskalink::count();

        // Load Regions
        $this->regionsOption = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->distinct()
            ->orderBy('region_name')
            ->get();
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
            $this->areasOption = DB::table('master_distributors')
                ->whereIn('region_code', $this->selectedRegions)
                ->select('area_code', 'area_name')
                ->distinct()
                ->orderBy('area_name')
                ->get();
        }
    }

    public function updatedSelectedAreas()
    {
        $this->selectedDistributors = [];
        $this->distributorsOption = [];
        $this->isFiltered = false;

        if (!empty($this->selectedAreas)) {
            $this->distributorsOption = DB::table('master_distributors')
                ->whereIn('region_code', $this->selectedRegions)
                ->whereIn('area_code', $this->selectedAreas)
                ->select('distributor_code', 'distributor_name')
                ->distinct()
                ->orderBy('distributor_name')
                ->get();
        }
    }

    public function updatedSelectedDistributors()
    {
        $this->isFiltered = false;
    }

    // --- ACTIONS ---

    public function filter()
    {
        $this->validate([
            'selectedMonth' => 'required',
            'selectedRegions' => 'required|array|min:1',
            'selectedAreas' => 'required|array|min:1',
            'selectedDistributors' => 'required|array|min:1',
        ]);

        $this->isFiltered = true;
        $this->showFilterModal = false;
        $this->resetPage();
    }

    public function export()
    {
        $this->validate([
            'selectedMonth' => 'required',
            'selectedRegions' => 'required|array|min:1',
            'selectedAreas' => 'required|array|min:1',
            'selectedDistributors' => 'required|array|min:1',
        ]);

        $timestamp = Carbon::now()->format('Ymd_His');
        return Excel::download(
            new SellingOutEskalinkExport(
                $this->selectedRegions, 
                $this->selectedAreas, 
                $this->selectedDistributors,
                $this->selectedMonth
            ), 
            'SellingOut_Summary_' . $timestamp . '.xlsx'
        );
    }

    public function updatedImportFile()
    {
        $this->validateOnly('importFile', [
            'importFile' => 'file|max:102400|mimes:xlsx,xls'
        ]);
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'file|max:102400|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new SellingOutEskalinkImport, $this->importFile);
            session()->flash('success', 'Import berhasil!');
            $this->showImportModal = false;
            $this->importFile = null;
            $this->mount(); 
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $data = [];

        if ($this->isFiltered) {
            $startDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->endOfMonth()->format('Y-m-d');

            $query = DB::table('selling_out_eskalink as soe')
                ->select(
                    'soe.region_code',
                    'soe.region_name',
                    'soe.entity_code',
                    'soe.entity_name',
                    'soe.branch_code',
                    'soe.branch_name',
                    DB::raw('count(soe.area_code) as row_count'),
                    DB::raw('sum(soe.qty3_pcs) as qty_pcs'),
                    DB::raw('sum(soe.gross_amount) as gross'),
                    DB::raw('sum(soe.line_discount_4) as ld4'),
                    DB::raw('sum(soe.line_discount_8) as bb'),
                    DB::raw('sum(soe.dpp) as dpp'),
                    DB::raw('sum(soe.tax) as tax'),
                    DB::raw('sum(soe.nett_amount) as nett_amount')
                )
                ->leftJoin('distributor_implementasi_eskalink as die', 'soe.branch_code', '=', 'die.eskalink_code')
                ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
                ->whereBetween('soe.invoice_date', [$startDate, $endDate]);

            // Filters
            if (!empty($this->selectedRegions)) {
                $query->whereIn('md.region_code', $this->selectedRegions);
            }
            if (!empty($this->selectedAreas)) {
                $query->whereIn('md.area_code', $this->selectedAreas);
            }
            if (!empty($this->selectedDistributors)) {
                $query->whereIn('md.distributor_code', $this->selectedDistributors);
            }

            // Group By
            $query->groupBy(
                'soe.region_code',
                'soe.region_name',
                'soe.entity_code',
                'soe.entity_name',
                'soe.branch_code',
                'soe.branch_name'
            );

            // Search logic (Applied via HAVING because of Group By)
            if (!empty($this->search)) {
                $query->having(function($q) {
                    $q->where('soe.branch_name', 'ilike', '%'.$this->search.'%')
                      ->orWhere('soe.branch_code', 'ilike', '%'.$this->search.'%');
                });
            }

            $data = $query->paginate(20);
        }

        return view('livewire.selling-out-eskalink.index', [
            'sellouts' => $data
        ])->layout('layouts.app');
    }
}