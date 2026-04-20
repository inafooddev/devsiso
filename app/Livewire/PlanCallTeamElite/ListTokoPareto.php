<?php

namespace App\Livewire\PlanCallTeamElite;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ListTokoParetoTeamElite;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ListTokoParetoImport;
use App\Exports\ListTokoParetoExport;

class ListTokoPareto extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Pencarian & Filter
    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterSupervisor = '';

    // Sorting
    public $sortColumn = 'm.region_name';
    public $sortDirection = 'asc';

    // State Modal
    public $isFilterModalOpen = false;
    public $isImportModalOpen = false;
    public $isEditModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isCreateModalOpen = false; // Modal Tambah Customer

    // Properti Import
    public $importFile;

    // Properti Form Edit & Create
    public $editId;
    public $distributor_code, $customer_code_prc, $customer_name, $customer_address;
    public $kecamatan, $desa, $latitude, $longitude, $pilar, $target;

    // Properti Hapus
    public $deleteId;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterRegion' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterSupervisor' => ['except' => ''],
        'sortColumn' => ['except' => 'm.region_name'],
        'sortDirection' => ['except' => 'asc'],
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
     * Helper Keamanan: Memastikan kode distributor yang dimanipulasi valid dengan hak akses.
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = DB::table('master_distributors')->where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // Auto-select region jika user hanya memiliki akses ke 1 region
        $query = DB::table('master_distributors')->select('region_code')->whereNotNull('region_code')->distinct();
        $this->applyRegionAccess($query);
        $regions = $query->get();

        if (!auth()->user()->hasRole('admin') && $regions->count() === 1) {
            $this->filterRegion = $regions->first()->region_code;
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterRegion() { $this->reset(['filterArea', 'filterSupervisor']); $this->resetPage(); }
    public function updatingFilterArea() { $this->reset('filterSupervisor'); $this->resetPage(); }
    public function updatingFilterSupervisor() { $this->resetPage(); }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        $query = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as m', 'l.distributor_code', '=', 'm.distributor_code')
            ->leftJoin('mapping_spv_code as msc', 'm.branch_code', '=', 'msc.branch_code')
            ->leftJoin('master_supervisors as ms', 'm.supervisor_code', '=', 'ms.supervisor_code')
            ->select(
                'l.id',
                'm.region_code', 'm.region_name',
                'm.area_code', 'm.area_name',
                'msc.supervisor_code',
                'ms.description as supervisor_name',
                'l.distributor_code', 'm.distributor_name',
                'l.customer_code_prc', 'l.customer_name', 'l.customer_address',
                'l.kecamatan', 'l.desa', 'l.latitude', 'l.longitude', 'l.pilar', 'l.target'
            );

        // --- PROTEKSI KEAMANAN DATA UTAMA ---
        $this->applyRegionAccess($query, 'm.region_code');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('l.customer_code_prc', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_name', 'ilike', "%{$this->search}%")
                  ->orWhere('l.customer_address', 'ilike', "%{$this->search}%")
                  ->orWhere('l.kecamatan', 'ilike', "%{$this->search}%")
                  ->orWhere('l.desa', 'ilike', "%{$this->search}%")
                  ->orWhere('l.pilar', 'ilike', "%{$this->search}%")
                  ->orWhere('ms.description', 'ilike', "%{$this->search}%");
            });
        }

        if ($this->filterRegion) $query->where('m.region_code', $this->filterRegion);
        if ($this->filterArea) $query->where('m.area_code', $this->filterArea);
        if ($this->filterSupervisor) $query->where('m.supervisor_code', $this->filterSupervisor);

        if ($this->sortColumn) {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        } else {
            $query->orderBy('m.region_name')
                  ->orderBy('m.area_name')
                  ->orderBy('m.distributor_name')
                  ->orderBy('ms.description')
                  ->orderBy('l.pilar');
        }

        return $query;
    }

    public function render()
    {
        // Amankan List Dropdown Region
        $regionQuery = DB::table('master_distributors')->select('region_code', 'region_name')->whereNotNull('region_code')->distinct();
        $this->applyRegionAccess($regionQuery);
        $regions = $regionQuery->orderBy('region_name')->get();
        
        $areas = [];
        if ($this->filterRegion) {
            // Amankan List Dropdown Area
            $areaQuery = DB::table('master_distributors')->select('area_code', 'area_name')->where('region_code', $this->filterRegion)->whereNotNull('area_code')->distinct();
            $this->applyRegionAccess($areaQuery);
            $areas = $areaQuery->orderBy('area_name')->get();
        }

        $supervisors = [];
        if ($this->filterArea) {
            // Amankan List Dropdown Supervisor
            $spvQuery = DB::table('master_distributors as m')
                ->join('master_supervisors as ms', 'm.supervisor_code', '=', 'ms.supervisor_code')
                ->select('ms.supervisor_code', 'ms.description as supervisor_name')
                ->where('m.area_code', $this->filterArea)
                ->distinct();
            $this->applyRegionAccess($spvQuery, 'm.region_code');
            $supervisors = $spvQuery->orderBy('supervisor_name')->get();
        }

        $data = $this->getBaseQuery()->paginate(15);

        return view('livewire.plan-call-team-elite.list-toko-pareto', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
        ])->layout('layouts.app');
    }

    // --- FITUR FILTER ---
    public function openFilterModal() { $this->isFilterModalOpen = true; }
    public function closeFilterModal() { $this->isFilterModalOpen = false; }
    public function applyFilter() { $this->isFilterModalOpen = false; $this->resetPage(); }
    public function resetFilter() { 
        $this->reset(['filterRegion', 'filterArea', 'filterSupervisor']); 
        $this->isFilterModalOpen = false; 
        
        // Kembalikan auto-select region setelah reset jika user non-admin hanya 1 region
        $this->mount();
        $this->resetPage(); 
    }

    // --- FITUR TAMBAH CUSTOMER BARU ---
    public function openCreateModal()
    {
        $this->reset(['distributor_code', 'customer_code_prc', 'customer_name', 'customer_address', 'kecamatan', 'desa', 'latitude', 'longitude', 'pilar', 'target']);
        $this->isCreateModalOpen = true;
    }

    public function store()
    {
        $this->validate([
            'distributor_code' => 'required|string|max:15',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'nullable|numeric',
        ]);

        // Security Check: Pastikan user berhak menambah data di distributor ini
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Akses ditolak: Anda tidak memiliki otoritas di distributor ini.');
            return;
        }

        ListTokoParetoTeamElite::updateOrCreate(
            [
                'distributor_code' => $this->distributor_code,
                'customer_code_prc' => $this->customer_code_prc,
            ],
            [
                'customer_name' => $this->customer_name,
                'customer_address' => $this->customer_address,
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'pilar' => $this->pilar,
                'target' => $this->target ?? 0,
            ]
        );

        $this->isCreateModalOpen = false;
        session()->flash('message', 'Customer berhasil ditambahkan.');
    }

    // --- FITUR EDIT ---
    public function edit($id)
    {
        $toko = ListTokoParetoTeamElite::findOrFail($id);
        
        // Security Check: Pastikan data yang dibuka valid secara otoritas wilayah
        if (!$this->checkDistributorAccess($toko->distributor_code)) {
            session()->flash('error', 'Akses ditolak: Data ini berada di luar otoritas wilayah Anda.');
            return;
        }

        $this->editId = $toko->id;
        $this->distributor_code = $toko->distributor_code;
        $this->customer_code_prc = $toko->customer_code_prc;
        $this->customer_name = $toko->customer_name;
        $this->customer_address = $toko->customer_address;
        $this->kecamatan = $toko->kecamatan;
        $this->desa = $toko->desa;
        $this->latitude = $toko->latitude;
        $this->longitude = $toko->longitude;
        $this->pilar = $toko->pilar;
        $this->target = $toko->target;

        $this->isEditModalOpen = true;
    }

    public function update()
    {
        $this->validate([
            'distributor_code' => 'required|string|max:15',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'nullable|numeric',
        ]);

        // Security Check: Pastikan user tidak mengubah kode distributor ke wilayah orang lain
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Akses ditolak: Anda tidak memiliki otoritas di distributor ini.');
            return;
        }

        ListTokoParetoTeamElite::find($this->editId)->update([
            'distributor_code' => $this->distributor_code,
            'customer_code_prc' => $this->customer_code_prc,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'kecamatan' => $this->kecamatan,
            'desa' => $this->desa,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'pilar' => $this->pilar,
            'target' => $this->target,
        ]);

        $this->isEditModalOpen = false;
        session()->flash('message', 'Data berhasil diperbarui.');
    }

    // --- FITUR HAPUS ---
    public function confirmDelete($id) { 
        $this->deleteId = $id; 
        $this->isDeleteModalOpen = true; 
    }
    
    public function delete()
    {
        $toko = ListTokoParetoTeamElite::find($this->deleteId);
        
        if ($toko) {
            // Security Check: Pastikan data yang dihapus ada di wilayah otoritas user
            if (!$this->checkDistributorAccess($toko->distributor_code)) {
                session()->flash('error', 'Akses ditolak: Anda tidak dapat menghapus data di luar wilayah Anda.');
                $this->isDeleteModalOpen = false;
                return;
            }
            $toko->delete();
            session()->flash('message', 'Data berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
    }

    // --- FITUR IMPORT & DOWNLOAD TEMPLATE ---
    public function downloadTemplate()
    {
        $filePath = public_path('templates/Format_List_Toko_Pareto.xlsx');
        if (file_exists($filePath)) {
            return response()->download($filePath);
        }
        session()->flash('error', 'File template Format_List_Toko_Pareto.xlsx tidak ditemukan di folder public/templates.');
    }

    public function openImportModal() { 
        $this->reset('importFile'); 
        $this->isImportModalOpen = true; 
    }
    
    public function import()
    {
        // Security Check: Proses Import Massal + Python Geotag sangat rentan jika tidak dibatasi. 
        // Sebaiknya hanya admin yang bisa melakukan import.
        if (!auth()->user()->hasRole('admin')) {
            session()->flash('error', 'Akses Ditolak: Hanya Administrator yang diizinkan untuk mengimpor List Toko Pareto secara massal.');
            return;
        }

        $this->validate(['importFile' => 'required|mimes:xlsx,xls,csv|max:10240']);

        try {
            $filePath = $this->importFile->store('temp-imports');
            $fullPath = \Illuminate\Support\Facades\Storage::path($filePath);

            $pythonScript = base_path('scripts/fill_polygon.py');
            
            $command = escapeshellcmd("python3") . " " . escapeshellarg($pythonScript) . " " . escapeshellarg($fullPath) . " 2>&1";
            exec($command, $outputArray, $resultCode);
            $output = implode("\n", $outputArray);

            if ($resultCode !== 0) {
                if (file_exists($fullPath)) unlink($fullPath);
                throw new \Exception("Pre-proses Polygon gagal: " . $output);
            }

            Excel::import(new ListTokoParetoImport, $fullPath);
            
            if (file_exists($fullPath)) unlink($fullPath);

            $this->isImportModalOpen = false;
            session()->flash('message', 'Proses Import Selesai (Geotag Polygon & Full Sync berhasil).');
            $this->resetPage(); 
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    // --- FITUR EXPORT ---
    public function export()
    {
        // Data yang diekspor mengambil dari getBaseQuery() yang sudah diamankan dengan applyRegionAccess()
        return Excel::download(new ListTokoParetoExport($this->getBaseQuery()), 'List_Toko_Pareto_Team_Elite.xlsx');
    }
}