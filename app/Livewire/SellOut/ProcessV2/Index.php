<?php

namespace App\Livewire\SellOut\ProcessV2;

use Livewire\Component;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ImportBatch; 
use App\Jobs\ValidateSellOutJobV2; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    // Filter
    public $regionFilter;
    public $areaFilter;
    public $distributorFilter;
    public $monthFilter;
    public $yearFilter;

    // Dropdown data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    // State
    public $isFilterModalOpen = false;
    public $hasAppliedFilters = false;

    // Log Process
    public $batchId;
    public $logLines = [];
    public $batchStatus;
    public $totalRows = 0;
    public $processedRows = 0;

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
        // Filter Region yang muncul di dropdown awal berdasarkan hak akses
        $this->regions = $this->applyRegionAccess(MasterRegion::query())
            ->where ('region_name', '!=', 'HOINA')
            ->orderBy('region_name')            
            ->get();

        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributorFilter']);
        
        if ($value) {
            $query = MasterArea::where('region_code', $value);
            // Keamanan tambahan: pastikan region yang dipilih memang dalam scope user
            $this->applyRegionAccess($query);
            
            $this->areas = $query->orderBy('area_name')->get();
        } else {
            $this->areas = collect();
        }
    }

    public function updatedAreaFilter($value)
    {
        $this->reset('distributorFilter');
        
        if ($value) {
            $query = MasterDistributor::where('area_code', $value);
            // Tetap kawal dengan region access untuk memastikan integritas data
            $this->applyRegionAccess($query);
            
            $this->distributors = $query->orderBy('distributor_name')->get();
        } else {
            $this->distributors = collect();
        }
    }

    public function applyFilters()
    {
        $this->hasAppliedFilters = true;
        $this->isFilterModalOpen = false;
        $this->reset(['batchId', 'logLines', 'batchStatus', 'totalRows', 'processedRows']);
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'monthFilter', 'yearFilter']);
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
        $this->areas = collect();
        $this->distributors = collect();
        $this->hasAppliedFilters = false;
        $this->reset(['batchId', 'logLines', 'batchStatus', 'totalRows', 'processedRows']);
    }

    /**
     * Memulai proses ETL V2
     */
    public function startProcess()
    {
        if (!$this->hasAppliedFilters) {
            session()->flash('error', 'Silakan terapkan filter terlebih dahulu.');
            return;
        }

        // Final Security Check sebelum dispatch Job
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->regionFilter, $user->region_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk memproses region ini.');
                return;
            }
        }

        $batch = ImportBatch::create([
            'file_name' => 'Proses Sell Out V2 - ' . Carbon::create($this->yearFilter, $this->monthFilter)->format('F Y'),
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog();

        $filters = [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'monthFilter' => $this->monthFilter,
            'yearFilter' => $this->yearFilter,
        ];

        // Kirim job validasi V2
        ValidateSellOutJobV2::dispatch($batch->id, $filters);
    }

    /**
     * Sinkronisasi log dari database.
     */
    public function syncLog()
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $this->logLines = $batch->log_lines ?? [];
                $this->batchStatus = $batch->status;
                $this->totalRows = $batch->total_rows;
                $this->processedRows = $batch->processed_rows;
            }
        }
    }

    public function render()
    {
        return view('livewire.sell-out.process-v2.index')->layout('layouts.app');
    }
}