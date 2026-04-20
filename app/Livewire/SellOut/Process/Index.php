<?php

namespace App\Livewire\SellOut\Process;

use Livewire\Component;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use App\Models\ImportBatch; 
use App\Jobs\ValidateSellOutJob; 
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
            ->where('region_name', '!=', 'HOINA')
            ->orderBy('region_name')
            ->get();

        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;

        // Auto-select jika user bukan admin dan hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            // Panggil fungsi update area secara manual
            $this->updatedRegionFilter($this->regionFilter);
        }
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
        // Reset log saat filter baru diterapkan
        $this->reset(['batchId', 'logLines', 'batchStatus', 'totalRows', 'processedRows']);
    }

    public function resetFilters()
    {
        $this->reset(['regionFilter', 'areaFilter', 'distributorFilter', 'monthFilter', 'yearFilter']);
        $this->monthFilter = now()->month;
        $this->yearFilter = now()->year;
        
        // Kembalikan daftar region sesuai hak akses
        $this->regions = $this->applyRegionAccess(MasterRegion::query())
            ->orderBy('region_name')
            ->get();
            
        $this->areas = collect();
        $this->distributors = collect();
        $this->hasAppliedFilters = false;
        $this->reset(['batchId', 'logLines', 'batchStatus', 'totalRows', 'processedRows']);

        // Auto-select ulang jika direset
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    /**
     * Memulai proses ETL
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
            // Jika memilih "Semua Region" (kosong) padahal bukan admin
            if (empty($this->regionFilter)) {
                 session()->flash('error', 'Pilih Region yang spesifik sebelum memproses.');
                 return;
            }
            // Jika memilih region di luar hak aksesnya
            if (!in_array($this->regionFilter, $user->region_code)) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk memproses region ini.');
                return;
            }
        }

        // 1. Buat batch log baru
        $batch = ImportBatch::create([
            'file_name' => 'Proses Sell Out - ' . Carbon::create($this->yearFilter, $this->monthFilter)->format('F Y'),
            'status' => 'pending',
            'log_lines' => [['type' => 'info', 'message' => 'Proses ditambahkan ke antrian...']]
        ]);

        $this->batchId = $batch->id;
        $this->syncLog(); // Tampilkan pesan "pending"

        // 2. Siapkan filter
        $filters = [
            'regionFilter' => $this->regionFilter,
            'areaFilter' => $this->areaFilter,
            'distributorFilter' => $this->distributorFilter,
            'monthFilter' => $this->monthFilter,
            'yearFilter' => $this->yearFilter,
        ];

        // 3. Kirim job validasi pertama
        ValidateSellOutJob::dispatch($batch->id, $filters);
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
        return view('livewire.sell-out.process.index')->layout('layouts.app');
    }
}