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
use App\Traits\EnforcesMenuPermissions;

class ListTokoPareto extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'plan-call-team-elite.toko-pareto';
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
    public $isAddToJksModalOpen = false; // Modal Add to JKS

    // Properti Import
    public $importFile;

    // Properti Form Edit & Create
    public $editId;
    public $distributor_code, $customer_code_prc, $customer_name, $customer_address;
    public $kecamatan, $desa, $latitude, $longitude, $pilar, $target, $keterangan, $uniq_kd;

    // Properti Hapus
    public $deleteId;

    // Properti Add to JKS
    public $selectedJksId, $jksTanggal, $jksKodeTeam;

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
                'l.customer_code_prc', 'l.customer_name', 'l.uniq_kd', 'l.customer_address',
                'l.kecamatan', 'l.desa', 'l.latitude', 'l.longitude', 'l.pilar', 'l.target', 'l.keterangan',
                DB::raw("CASE WHEN EXISTS (SELECT 1 FROM jks_team_elite as j WHERE l.distributor_code = j.distributor_code AND l.customer_code_prc = j.custno) THEN 'Y' ELSE 'T' END as on_jks")
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

        // --- KPI Calculation ---
        $kpiQuery = clone $this->getBaseQuery();
        $kpiQuery->orders = null;
        
        $kpi = DB::table(DB::raw("({$kpiQuery->toSql()}) as sub"))
            ->mergeBindings($kpiQuery)
            ->selectRaw("
                COUNT(id) as total_toko,
                SUM(CASE WHEN on_jks = 'Y' THEN 1 ELSE 0 END) as total_toko_jks_y,
                SUM(COALESCE(CAST(NULLIF(CAST(target AS TEXT), '') AS NUMERIC), 0)) as total_target,
                SUM(CASE WHEN on_jks = 'Y' THEN COALESCE(CAST(NULLIF(CAST(target AS TEXT), '') AS NUMERIC), 0) ELSE 0 END) as total_target_jks_y,
                SUM(CASE WHEN pilar = '1. RWO' THEN 1 ELSE 0 END) as total_rwo,
                SUM(CASE WHEN pilar = '1. RWO' AND on_jks = 'Y' THEN 1 ELSE 0 END) as total_rwo_jks_y,
                SUM(CASE WHEN pilar = '2. PNR' THEN 1 ELSE 0 END) as total_pnr,
                SUM(CASE WHEN pilar = '2. PNR' AND on_jks = 'Y' THEN 1 ELSE 0 END) as total_pnr_jks_y,
                SUM(CASE WHEN pilar = '3. NGVO' THEN 1 ELSE 0 END) as total_ngvo,
                SUM(CASE WHEN pilar = '3. NGVO' AND on_jks = 'Y' THEN 1 ELSE 0 END) as total_ngvo_jks_y
            ")->first();

        $teams = DB::table('fsalesman')
            ->where('TEAM', 'SPI')
            ->select('SLSNO as kode_team', 'SLSNAME as nama_team')
            ->orderBy('SLSNAME')
            ->get();

        return view('livewire.plan-call-team-elite.list-toko-pareto', [
            'data' => $data,
            'regions' => $regions,
            'areas' => $areas,
            'supervisors' => $supervisors,
            'teams' => $teams,
            'kpi' => $kpi,
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
        $this->reset(['distributor_code', 'customer_code_prc', 'customer_name', 'uniq_kd', 'customer_address', 'kecamatan', 'desa', 'latitude', 'longitude', 'pilar', 'target', 'keterangan']);
        $this->isCreateModalOpen = true;
    }

    public function store()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'distributor_code' => 'required|string|max:15',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'uniq_kd' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'nullable|numeric',
            'keterangan' => 'nullable|string',
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
                'uniq_kd' => $this->uniq_kd,
                'customer_address' => $this->customer_address,
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'pilar' => $this->pilar,
                'target' => $this->target ?? 0,
                'keterangan' => $this->keterangan,
            ]
        );

        $this->isCreateModalOpen = false;
        \App\Helpers\ActivityLogger::log('Create Toko Pareto', "Menambahkan Toko Pareto baru: {$this->customer_code_prc} - {$this->customer_name}");
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
        $this->uniq_kd = $toko->uniq_kd;
        $this->customer_address = $toko->customer_address;
        $this->kecamatan = $toko->kecamatan;
        $this->desa = $toko->desa;
        $this->latitude = $toko->latitude;
        $this->longitude = $toko->longitude;
        $this->pilar = $toko->pilar;
        $this->target = $toko->target;
        $this->keterangan = $toko->keterangan;

        $this->isEditModalOpen = true;
    }

    public function update()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'distributor_code' => 'required|string|max:15',
            'customer_code_prc' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'uniq_kd' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'target' => 'nullable|numeric',
            'keterangan' => 'nullable|string',
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
            'uniq_kd' => $this->uniq_kd,
            'customer_address' => $this->customer_address,
            'kecamatan' => $this->kecamatan,
            'desa' => $this->desa,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'pilar' => $this->pilar,
            'target' => $this->target,
            'keterangan' => $this->keterangan,
        ]);

        $this->isEditModalOpen = false;
        \App\Helpers\ActivityLogger::log('Update Toko Pareto', "Memperbarui Toko Pareto: {$this->customer_code_prc} - {$this->customer_name}");
        session()->flash('message', 'Data berhasil diperbarui.');
    }

    // --- FITUR HAPUS ---
    public function confirmDelete($id) { 
        $toko = ListTokoParetoTeamElite::find($id);
        if ($toko) {
            $existsOnJks = DB::table('jks_team_elite')
                ->where('distributor_code', $toko->distributor_code)
                ->where('custno', $toko->customer_code_prc)
                ->exists();
            if ($existsOnJks) {
                session()->flash('error', 'Tidak dapat menghapus toko ini karena statusnya terdaftar di JKS.');
                return;
            }
        }
        
        $this->deleteId = $id; 
        $this->isDeleteModalOpen = true; 
    }
    
    public function delete()
    {
        $this->authorizeAction('can_edit');

        $toko = ListTokoParetoTeamElite::find($this->deleteId);
        
        if ($toko) {
            // Security Check: Pastikan data yang dihapus ada di wilayah otoritas user
            if (!$this->checkDistributorAccess($toko->distributor_code)) {
                session()->flash('error', 'Akses ditolak: Anda tidak dapat menghapus data di luar wilayah Anda.');
                $this->isDeleteModalOpen = false;
                return;
            }

            // Pastikan toko tidak terdaftar di JKS sebelum dihapus
            $existsOnJks = DB::table('jks_team_elite')
                ->where('distributor_code', $toko->distributor_code)
                ->where('custno', $toko->customer_code_prc)
                ->exists();
            if ($existsOnJks) {
                session()->flash('error', 'Gagal menghapus: Toko ini terdaftar di JKS.');
                $this->isDeleteModalOpen = false;
                return;
            }

            \App\Helpers\ActivityLogger::log('Delete Toko Pareto', "Menghapus Toko Pareto: {$toko->customer_code_prc} - {$toko->customer_name}");
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
        $this->authorizeAction('can_import');

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

            $import = new ListTokoParetoImport;
            Excel::import($import, $fullPath);
            
            if (file_exists($fullPath)) unlink($fullPath);

            // Jika ada data duplikat, buat file TXT dan download
            if (count($import->duplicates) > 0) {
                $fileName = 'Duplikat_Import_Toko_' . time() . '.txt';
                $txtPath = storage_path('app/private/' . $fileName);
                
                $content = "Daftar Data Ganda yang Dilewati Saat Import:\n";
                $content .= "Total Data Dilewati: " . count($import->duplicates) . " toko\n\n";
                $content .= implode("\n", $import->duplicates);
                
                file_put_contents($txtPath, $content);

                $this->isImportModalOpen = false;
                $this->resetPage();
                session()->flash('error', 'Import selesai, namun terdapat ' . count($import->duplicates) . ' data ganda yang gagal diunggah. Silakan cek file teks (TXT) yang ter-download otomatis.');
                
                \App\Helpers\ActivityLogger::log('Import Toko Pareto', "Mengimpor data dengan " . count($import->duplicates) . " duplikat yang terdeteksi.");
                return response()->download($txtPath)->deleteFileAfterSend(true);
            }

            $this->isImportModalOpen = false;
            \App\Helpers\ActivityLogger::log('Import Toko Pareto', "Mengimpor data List Toko Pareto secara massal dengan sukses seluruhnya.");
            session()->flash('message', 'Proses Import Selesai. Seluruh data berhasil disinkronkan ke database tanpa ada duplikat.');
            $this->resetPage(); 
            
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23505') { // 23505 adalah kode unik PostgreSQL untuk Duplicate Key
                session()->flash('error', 'Gagal Import: Terdapat data ganda yang tertangkap di level database.');
            } else {
                session()->flash('error', 'Gagal Import Database: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    public function syncGeotag()
    {
        $this->authorizeAction('can_edit');

        try {
            $tokos = ListTokoParetoTeamElite::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'latitude', 'longitude', 'kecamatan', 'desa']);

            if ($tokos->isEmpty()) {
                session()->flash('message', 'Tidak ada data toko yang memiliki koordinat latitude & longitude untuk disinkronisasi.');
                return;
            }

            // Simpan ke CSV
            $fileName = 'temp_geotag_' . time() . '.csv';
            $filePath = storage_path('app/private/' . $fileName);
            
            $file = fopen($filePath, 'w');
            fputcsv($file, ['id', 'latitude', 'longitude', 'kecamatan', 'desa']);
            foreach ($tokos as $toko) {
                fputcsv($file, [
                    $toko->id,
                    $toko->latitude,
                    $toko->longitude,
                    $toko->kecamatan,
                    $toko->desa
                ]);
            }
            fclose($file);

            // Eksekusi Python
            $pythonScript = base_path('scripts/fill_polygon.py');
            $command = escapeshellcmd("python3") . " " . escapeshellarg($pythonScript) . " " . escapeshellarg($filePath) . " 2>&1";
            exec($command, $outputArray, $resultCode);
            $output = implode("\n", $outputArray);

            if ($resultCode !== 0) {
                if (file_exists($filePath)) unlink($filePath);
                throw new \Exception("Eksekusi Python gagal: " . $output);
            }

            // Baca hasil CSV dan Update DB
            $updatedFile = fopen($filePath, 'r');
            $header = fgetcsv($updatedFile); // Skip header

            DB::beginTransaction();
            try {
                while (($row = fgetcsv($updatedFile)) !== false) {
                    if (count($row) >= 5) {
                        $id = $row[0];
                        $kec = $row[3];
                        $desa = $row[4];
                        
                        ListTokoParetoTeamElite::where('id', $id)->update([
                            'kecamatan' => $kec,
                            'desa' => $desa,
                        ]);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($updatedFile);
                if (file_exists($filePath)) unlink($filePath);
                throw $e;
            }

            fclose($updatedFile);
            if (file_exists($filePath)) unlink($filePath);

            \App\Helpers\ActivityLogger::log('Sync Geotag', "Mensinkronisasi Geotag Toko Pareto secara massal.");
            session()->flash('message', 'Sinkronisasi Geotag (Kecamatan & Desa) berhasil diselesaikan.');
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Sync Geotag: ' . $e->getMessage());
        }
    }

    // --- FITUR EXPORT ---
    public function export()
    {
        $this->authorizeAction('can_export');

        \App\Helpers\ActivityLogger::log('Export Toko Pareto', "Mengekspor data List Toko Pareto.");
        return Excel::download(new ListTokoParetoExport($this->getBaseQuery()), 'List_Toko_Pareto_Team_Elite.xlsx');
    }

    public function addToJks($id)
    {
        $this->selectedJksId = $id;
        $this->jksTanggal = date('Y-m-d');
        $this->jksKodeTeam = '';
        $this->isAddToJksModalOpen = true;
    }

    public function storeToJks()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'jksTanggal' => 'required|date',
            'jksKodeTeam' => 'required|string',
        ]);

        $toko = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as m', 'l.distributor_code', '=', 'm.distributor_code')
            ->leftJoin('mapping_spv_code as msc', 'm.branch_code', '=', 'msc.branch_code')
            ->leftJoin('master_supervisors as ms', 'm.supervisor_code', '=', 'ms.supervisor_code')
            ->select(
                'm.region_code', 'm.region_name',
                'm.area_code', 'm.area_name',
                'msc.supervisor_code',
                'l.distributor_code', 'm.distributor_name',
                'l.customer_code_prc', 'l.customer_name', 'l.customer_address'
            )
            ->where('l.id', $this->selectedJksId)
            ->first();

        if ($toko) {
            $team = DB::table('fsalesman')->where('SLSNO', $this->jksKodeTeam)->first();
            $namaTeam = $team ? $team->SLSNAME : '-';

            \App\Models\JksTeamElite::create([
                'distributor_code' => $toko->distributor_code,
                'custno' => $toko->customer_code_prc,
                'tanggal' => $this->jksTanggal,
                'kode_team' => $this->jksKodeTeam,
                'nama_team' => $namaTeam,
                'kode_region' => $toko->region_code ?? '-',
                'nama_region' => $toko->region_name ?? '-',
                'kode_area' => $toko->area_code ?? '-',
                'nama_area' => $toko->area_name ?? '-',
                'distributor_name' => $toko->distributor_name ?? '-',
                'custname' => $toko->customer_name ?? '-',
                'addres' => $toko->customer_address ?? '-',
            ]);

            $this->isAddToJksModalOpen = false;
            \App\Helpers\ActivityLogger::log('Add Toko to JKS', "Menambahkan Toko Pareto {$toko->customer_code_prc} ke JKS Team {$this->jksKodeTeam} ({$this->jksTanggal})");
            session()->flash('message', 'Toko berhasil ditambahkan ke JKS Team Elite.');
        }
    }
}