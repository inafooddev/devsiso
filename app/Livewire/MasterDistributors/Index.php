<?php

namespace App\Livewire\MasterDistributors;

use Livewire\Component;
use App\Models\MasterDistributor;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Exports\MasterDistributorsExport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $statusFilter = '';
    public $regionFilter = '';
    public $areaFilter = '';

    public $isDeleteModalOpen = false;
    public $distributorIdToDelete;

    // Tambahan untuk modal map
    public $isMapModalOpen = false;
    public $mapLatitude;
    public $mapLongitude;
    public $mapDistributorName;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'regionFilter' => ['except' => ''],
        'areaFilter' => ['except' => ''],
    ];

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

    /**
     * Reset area filter when region filter is changed.
     */
    public function updatingRegionFilter()
    {
        $this->reset('areaFilter');
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        $query = MasterDistributor::with('supervisor')
        ->where('distributor_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan distributor yang terkait dengan region 'national'

        // 1. Terapkan proteksi hak akses region terlebih dahulu
        $this->applyRegionAccess($query);

        // Apply search filter (Sudah aman dibungkus closure)
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('distributor_code', 'ilike', '%' . $search . '%')
                  ->orWhere('distributor_name', 'ilike', '%' . $search . '%')
                  ->orWhere('branch_name', 'ilike', '%' . $search . '%')
                  ->orWhere('supervisor_name', 'ilike', '%' . $search . '%')
                  ->orWhereHas('supervisor', function ($qs) use ($search) {
                      $qs->where('description', 'ilike', "%$search%");
                  });
            });
        }

        // Apply status filter
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        // Apply region filter
        if ($this->regionFilter) {
            $query->where('region_code', $this->regionFilter)
                  ->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national' dari filter
        }

        // Apply area filter
        if ($this->areaFilter) {
            $query->where('area_code', $this->areaFilter);
        }

        $distributors = $query->latest()->paginate(10);
        
        // Pilihan Region di dropdown juga harus difilter sesuai akses login
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national' dari dropdown
        $this->applyRegionAccess($regionQuery);
        $regions = $regionQuery->orderBy('region_name')->get();

        $areas = $this->regionFilter ? MasterArea::where('region_code', $this->regionFilter)->where('region_code', '!=', 'HOINA')->orderBy('area_name')->get() : collect();

        return view('livewire.master-distributors.index', [
            'distributors' => $distributors,
            'regions' => $regions,
            'areas' => $areas
        ])->layout('layouts.app');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Synchronize denormalized data from master tables.
     */
    public function synchronize()
    {
        // Security Check: Karena ini query massal (DB::statement), kita blokir user biasa.
        // Hanya Admin yang boleh melakukan Synchronize.
        if (!auth()->user()->hasRole('admin')) {
            session()->flash('error', 'Akses ditolak: Hanya Administrator yang dapat melakukan sinkronisasi data massal.');
            return;
        }

        try {
            DB::statement("
                UPDATE master_distributors md
                SET
                    branch_name = mb.branch_name,
                    supervisor_name = ms.supervisor_name,
                    area_name = ma.area_name,
                    region_name = mr.region_name,
                    supervisor_code = ms.supervisor_code
                FROM master_branches mb
                JOIN master_supervisors ms ON mb.supervisor_code = ms.supervisor_code
                JOIN master_areas ma ON ms.area_code = ma.area_code
                JOIN master_regions mr ON ma.region_code = mr.region_code
                WHERE md.branch_code = mb.branch_code
            ");

            session()->flash('message', 'Sinkronisasi data distributor berhasil diselesaikan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Open the delete confirmation modal.
     */
    public function confirmDelete($distributorCode)
    {
        $this->distributorIdToDelete = $distributorCode;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Delete the distributor data.
     */
    public function delete()
    {
        // Security Check: Pastikan data yang dihapus masih dalam region user
        $query = MasterDistributor::query();
        $this->applyRegionAccess($query);

        $distributor = $query->where('distributor_code', $this->distributorIdToDelete)
                             ->orWhere('id', $this->distributorIdToDelete)->first();

        if ($distributor) {
            $distributor->delete();
            session()->flash('message', 'Distributor berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus distributor ini.');
        }

        $this->isDeleteModalOpen = false;
    }

    /**
     * Open the map modal with location data.
     */
    public function showMap($distributorCode)
    {
        // Security Check: Pastikan map yang dibuka berada dalam region user
        $query = MasterDistributor::query();
        $this->applyRegionAccess($query);

        $distributor = $query->where('distributor_code', $distributorCode)->first();
        
        if (!$distributor) {
            session()->flash('error', 'Distributor tidak ditemukan atau berada di luar otoritas Anda.');
            return;
        }
        
        if ($distributor->latitude && $distributor->longitude) {
            // Log untuk debugging
            \Log::info('Map Data:', [
                'distributor_code' => $distributor->distributor_code,
                'name' => $distributor->distributor_name,
                'latitude' => $distributor->latitude,
                'longitude' => $distributor->longitude
            ]);
            
            $this->mapLatitude = $distributor->latitude;
            $this->mapLongitude = $distributor->longitude;
            $this->mapDistributorName = $distributor->distributor_name;
            $this->isMapModalOpen = true;
            
            // Dispatch event to initialize map
            $this->dispatch('map-opened');
        } else {
            session()->flash('error', 'Koordinat lokasi tidak tersedia untuk distributor ini.');
        }
    }

    // [DITAMBAHKAN] Metode untuk mengekspor data
    public function export()
    {
        $finalRegionFilter = $this->regionFilter;
        $user = auth()->user();

        // Validasi ekstra untuk Export (Mencegah manipulasi user biasa)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            // Jika user iseng menginjeksi region lain yang tidak dia miliki, reset ke kosong
            if (!empty($finalRegionFilter) && !in_array($finalRegionFilter, $user->region_code)) {
                $finalRegionFilter = ''; 
            }
        }

        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'regionFilter' => $finalRegionFilter, // Akan selalu berupa String murni (1 region atau kosong)
            'areaFilter' => $this->areaFilter,
            // Tambahkan parameter khusus untuk proteksi Array Scope
            'allowed_regions' => (!$user->hasRole('admin')) ? $user->region_code : [], 
        ];

        return Excel::download(new MasterDistributorsExport($filters), 'master_distributors.xlsx');
    }
}