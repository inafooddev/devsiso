<?php

namespace App\Livewire\JksTeamElite;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\JksTeamElite;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JksTeamEliteExport;
use App\Exports\JksTeamEliteEskaExport;
use App\Exports\JksTeamEliteTemplateExport;
use App\Imports\JksTeamEliteImport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'jks-team-elite.index';
    protected $paginationTheme = 'tailwind';

    // Filters
    public $search = '';
    public $searchTeamFilter = '';
    public $filterTeam = [];
    public $filterStartDate = '';
    public $filterEndDate = '';

    // Sorting
    public $sortField = 'tanggal';
    public $sortDirection = 'asc';

    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $isExportEskaModalOpen = false;
    public $selectedFlagDelete = 'Y';
    
    // Common Fields
    public $tanggal;
    
    // Team Fields
    public $teams = [];
    public $selectedTeamCode = '';
    public $selectedTeamName = '';

    // Search Distributor
    public $searchDistributor = '';
    public $distributorOptions = [];
    public $selectedDistributorCode = '';
    
    // Search Customer
    public $searchCustomer = '';
    public $customerOptions = [];
    
    // Cart for multiple customers
    public $selectedCustomers = [];

    // Edit Target (Grouping)
    public $oldGroupParams = [];

    // Delete Target (Grouping)
    public $dataIdToDelete = [];

    // Import Field
    public $excel_file;
    public $importErrors = [];
    public $importStep = 1;
    public $importMethod = 'full_sync';
    public $importStartDate = '';
    public $importEndDate = '';

    // Preview Metrics
    public $previewTotalRows = 0;
    public $previewTotalTeams = 0;
    public $previewExistingRows = 0;

    // Detail Toko Modal
    public $isStoreModalOpen = false;
    public $storeModalTitle = '';
    public $storeModalData = [];

    public $isMapModalOpen = false;
    public $mapModalTitle = '';
    public $mapModalData = [];
    public $mapTanggal = '';
    public $mapKodeTeam = '';

    protected $queryString = ['filterTeam', 'filterStartDate', 'filterEndDate'];

    private function applyHierarchyAccess($query, $distributorCodeColumn = 'jks_team_elite.distributor_code')
    {
        $user = auth()->user();

        // Admin atau tidak ada batasan → tampil semua
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        // Level Supervisor
        if (!empty($user->supervisor_code)) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md')
                    ->whereColumn('md.distributor_code', $distributorCodeColumn)
                    ->where('md.supervisor_code', $user->supervisor_code);
            });
        }

        // Level Area (Array)
        if (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md')
                    ->whereColumn('md.distributor_code', $distributorCodeColumn)
                    ->whereIn('md.area_code', $user->area_code);
            });
        }

        // Level Region (Array)
        if (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
            return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md')
                    ->whereColumn('md.distributor_code', $distributorCodeColumn)
                    ->whereIn('md.region_code', $user->region_code);
            });
        }

        // Jika user bukan admin tapi tidak punya akses apa-apa (sup/area/region kosong)
        return $query->whereRaw('1 = 0');
    }

    public function mount()
    {
        try {
            $this->teams = DB::table('fsalesman')
                ->select('SLSNO as kode_team', 'SLSNAME as nama_team')
                ->where('TEAM', 'SPI')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->teams = [];
        }

        // Set default filter date if empty
        if (empty($this->filterStartDate)) {
            $this->filterStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->filterEndDate)) {
            $this->filterEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // Set default import dates
        $this->importStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->importEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedFilterTeam() { $this->resetPage(); }
    public function updatedFilterStartDate() { $this->resetPage(); }
    public function updatedFilterEndDate() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function selectAllTeams()
    {
        if (!empty($this->searchTeamFilter)) {
            $filtered = collect($this->teams)->filter(function($t) {
                return stripos($t->nama_team, $this->searchTeamFilter) !== false || 
                       stripos($t->kode_team, $this->searchTeamFilter) !== false;
            })->pluck('kode_team')->toArray();
            $this->filterTeam = array_values(array_unique(array_merge($this->filterTeam, $filtered)));
        } else {
            $this->filterTeam = collect($this->teams)->pluck('kode_team')->toArray();
        }
    }

    public function resetTeams()
    {
        $this->filterTeam = [];
    }

    /**
     * Membuka modal untuk tambah data.
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    /**
     * Membuka modal untuk edit grup customer.
     */
    public function openEditModal($tanggal, $kode_team, $kode_region)
    {
        $this->resetValidation();
        $this->resetForm();
        
        // Simpan param lama agar nanti bisa dihapus saat simpan
        $this->oldGroupParams = [
            'tanggal' => $tanggal,
            'kode_team' => $kode_team,
            'kode_region' => $kode_region
        ];
        
        $customers = JksTeamElite::whereDate('tanggal', $tanggal)
            ->where('kode_team', $kode_team)
            ->where('kode_region', $kode_region)
            ->get();
            
        if ($customers->count() > 0) {
            $first = $customers->first();
            $this->tanggal = $first->tanggal ? $first->tanggal->format('Y-m-d') : null;
            $this->selectedTeamCode = $first->kode_team;
            $this->selectedTeamName = $first->nama_team;
            
            foreach ($customers as $cust) {
                $this->selectedCustomers[] = [
                    'kode_region' => $cust->kode_region,
                    'nama_region' => $cust->nama_region,
                    'kode_area' => $cust->kode_area,
                    'nama_area' => $cust->nama_area,
                    'distributor_code' => $cust->distributor_code,
                    'distributor_name' => $cust->distributor_name,
                    'custno' => $cust->custno,
                    'custname' => $cust->custname,
                    'addres' => $cust->addres,
                ];
            }
        }
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Set Nama Team saat dropdown berubah
     */
    public function updatedSelectedTeamCode($value)
    {
        $team = collect($this->teams)->firstWhere('kode_team', $value);
        if ($team) {
            $this->selectedTeamName = $team->nama_team;
        } else {
            $this->selectedTeamName = '';
        }
    }

    /**
     * Pencarian Distributor (Real-time)
     */
    public function updatedSearchDistributor()
    {
        if (strlen($this->searchDistributor) >= 2) {
            $query = DB::table('master_distributors')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('distributor_code', 'ilike', '%' . $this->searchDistributor . '%')
                      ->orWhere('distributor_name', 'ilike', '%' . $this->searchDistributor . '%');
                });
            
            $this->applyHierarchyAccess($query, 'distributor_code');

            $this->distributorOptions = $query->select('distributor_code', 'distributor_name')
                ->limit(20)
                ->get()
                ->toArray();
        } else {
            $this->distributorOptions = [];
        }
    }

    /**
     * Pilih Distributor
     */
    public function selectDistributor($code, $name)
    {
        $this->selectedDistributorCode = $code;
        $this->searchDistributor = $code . ' - ' . $name;
        $this->distributorOptions = [];
        
        // Reset customer search
        $this->searchCustomer = '';
        $this->customerOptions = [];
    }

    /**
     * Kosongkan distributor yang dipilih
     */
    public function clearDistributor()
    {
        $this->selectedDistributorCode = '';
        $this->searchDistributor = '';
        $this->distributorOptions = [];
        $this->searchCustomer = '';
        $this->customerOptions = [];
    }

    /**
     * Pencarian Customer (Real-time) berdasarkan distributor yg dipilih
     */
    public function updatedSearchCustomer()
    {
        if (strlen($this->searchCustomer) >= 3) {
            $query = DB::table('list_toko_pareto_team_elite as l')
                ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
                ->where('md.is_active', true);
            
            $this->applyHierarchyAccess($query, 'l.distributor_code');

            // Jika ada distributor yang dipilih, batasi pencarian
            if (!empty($this->selectedDistributorCode)) {
                $query->where('md.distributor_code', $this->selectedDistributorCode);
            }

            $query->where(function($q) {
                $q->where('l.customer_code_prc', 'ilike', '%' . $this->searchCustomer . '%')
                  ->orWhere('l.customer_name', 'ilike', '%' . $this->searchCustomer . '%')
                  ->orWhere('l.customer_address', 'ilike', '%' . $this->searchCustomer . '%');
            });

            $this->customerOptions = $query->select(
                    'md.region_code as kode_region',
                    'md.region_name as nama_region',
                    'md.area_code as kode_area',
                    'md.area_name as nama_area',
                    'l.distributor_code',
                    'md.distributor_name',
                    'l.customer_code_prc as custno',
                    'l.customer_name as custname',
                    'l.customer_address as addres'
                )
                ->limit(20)
                ->get()
                ->toArray();
        } else {
            $this->customerOptions = [];
        }
    }

    /**
     * Menambahkan Customer ke "Cart" (Daftar Customer Terpilih)
     */
    public function addCustomerToCart($custno)
    {
        $customer = collect($this->customerOptions)->firstWhere('custno', $custno);
        
        if ($customer) {
            // Cek apakah sudah ada di cart
            $exists = collect($this->selectedCustomers)->contains('custno', $custno);
            
            if (!$exists) {
                $this->selectedCustomers[] = (array) $customer;
            }
        }
        
        // Reset input customer search
        $this->searchCustomer = '';
        $this->customerOptions = [];
    }

    /**
     * Menghapus Customer dari "Cart"
     */
    public function removeCustomerFromCart($custno)
    {
        $this->selectedCustomers = collect($this->selectedCustomers)
            ->filter(function ($item) use ($custno) {
                return $item['custno'] !== $custno;
            })
            ->values()
            ->toArray();
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->tanggal          = null;
        $this->selectedTeamCode = '';
        $this->selectedTeamName = '';
        
        $this->searchDistributor = '';
        $this->distributorOptions = [];
        $this->selectedDistributorCode = '';
        
        $this->searchCustomer = '';
        $this->customerOptions = [];
        
        $this->selectedCustomers = [];
        $this->oldGroupParams = [];
        $this->dataIdToDelete = [];
    }

    /**
     * Menyimpan data.
     */
    public function save()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'tanggal' => 'required|date',
            'selectedTeamCode' => 'required|string',
            'selectedTeamName' => 'required|string',
            'selectedCustomers' => 'required|array|min:1',
        ], [
            'tanggal.required' => 'Tanggal harus diisi.',
            'selectedTeamCode.required' => 'Team harus dipilih.',
            'selectedCustomers.required' => 'Minimal pilih 1 customer untuk disimpan.',
        ]);

        if ($this->isEditing && !empty($this->oldGroupParams)) {
            // Hapus data grup yang lama
            JksTeamElite::whereDate('tanggal', $this->oldGroupParams['tanggal'])
                ->where('kode_team', $this->oldGroupParams['kode_team'])
                ->where('kode_region', $this->oldGroupParams['kode_region'])
                ->delete();
        }

        // Tambah Multiple Customer sekaligus
        $inserts = [];
        foreach ($this->selectedCustomers as $cust) {
            $inserts[] = [
                'tanggal'          => $this->tanggal,
                'kode_team'        => $this->selectedTeamCode,
                'nama_team'        => $this->selectedTeamName,
                'kode_region'      => $cust['kode_region'],
                'nama_region'      => $cust['nama_region'],
                'kode_area'        => $cust['kode_area'],
                'nama_area'        => $cust['nama_area'],
                'distributor_code' => $cust['distributor_code'],
                'distributor_name' => $cust['distributor_name'],
                'custno'           => $cust['custno'],
                'custname'         => $cust['custname'],
                'addres'           => $cust['addres'],
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ];
        }
        
        JksTeamElite::insert($inserts);
        
        if ($this->isEditing) {
            \App\Helpers\ActivityLogger::log('Update JKS Team Elite', "Memperbarui grup customer JKS untuk team: {$this->selectedTeamName} ({$this->tanggal})");
            session()->flash('message', 'Grup customer berhasil diperbarui.');
        } else {
            \App\Helpers\ActivityLogger::log('Create JKS Team Elite', "Membuat grup customer JKS baru untuk team: {$this->selectedTeamName} ({$this->tanggal}) sejumlah " . count($inserts) . " data");
            session()->flash('message', count($inserts) . ' Data customer berhasil disimpan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    /**
     * Membuka modal konfirmasi hapus massal (per grup).
     */
    public function confirmDelete($tanggal, $kode_team, $kode_region)
    {
        $this->dataIdToDelete = [
            'tanggal' => $tanggal,
            'kode_team' => $kode_team,
            'kode_region' => $kode_region
        ];
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus grup data.
     */
    public function delete()
    {
        $this->authorizeAction('can_edit');

        if (!empty($this->dataIdToDelete)) {
            JksTeamElite::whereDate('tanggal', $this->dataIdToDelete['tanggal'])
                ->where('kode_team', $this->dataIdToDelete['kode_team'])
                ->where('kode_region', $this->dataIdToDelete['kode_region'])
                ->delete();
                
            \App\Helpers\ActivityLogger::log('Delete JKS Team Elite', "Menghapus grup customer JKS untuk team: {$this->dataIdToDelete['kode_team']} pada {$this->dataIdToDelete['tanggal']}");
            session()->flash('message', 'Grup data berhasil dihapus.');
        } else {
            session()->flash('error', 'Grup data tidak ditemukan.');
        }

        $this->isDeleteModalOpen = false;
        $this->dataIdToDelete = [];
    }

    /**
     * Ekspor ke Excel
     */
    public function export()
    {
        $this->authorizeAction('can_export');

        if (empty($this->filterTeam) || empty($this->filterStartDate) || empty($this->filterEndDate)) {
            session()->flash('error', 'Pilih Team dan rentang tanggal terlebih dahulu sebelum export.');
            return;
        }

        $teamsLog = is_array($this->filterTeam) ? implode(', ', $this->filterTeam) : $this->filterTeam;
        \App\Helpers\ActivityLogger::log('Export JKS Team Elite', "Mengekspor data JKS Team Elite. Team: {$teamsLog}");

        return Excel::download(
            new JksTeamEliteExport($this->filterTeam, $this->filterStartDate, $this->filterEndDate), 
            'jks_team_elite_' . date('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Membuka modal export eska
     */
    public function openExportEskaModal()
    {
        $this->authorizeAction('can_export');

        if (empty($this->filterTeam) || empty($this->filterStartDate) || empty($this->filterEndDate)) {
            session()->flash('error', 'Pilih Team dan rentang tanggal terlebih dahulu sebelum export.');
            return;
        }

        $this->selectedFlagDelete = 'Y';
        $this->isExportEskaModalOpen = true;
    }

    /**
     * Ekspor ke Excel format ESKA
     */
    public function exportEska()
    {
        $this->authorizeAction('can_export');

        if (empty($this->filterTeam) || empty($this->filterStartDate) || empty($this->filterEndDate)) {
            session()->flash('error', 'Pilih Team dan rentang tanggal terlebih dahulu sebelum export.');
            return;
        }

        $flagDelete = in_array($this->selectedFlagDelete, ['Y', 'N']) ? $this->selectedFlagDelete : 'Y';
        $this->isExportEskaModalOpen = false;

        $teamsLog = is_array($this->filterTeam) ? implode(', ', $this->filterTeam) : $this->filterTeam;
        \App\Helpers\ActivityLogger::log('Export ESKA JKS Team Elite', "Mengekspor data ESKA JKS Team Elite dengan Flag Delete {$flagDelete}. Team: {$teamsLog}");

        return Excel::download(
            new JksTeamEliteEskaExport($this->filterTeam, $this->filterStartDate, $this->filterEndDate, $flagDelete), 
            'jks_team_elite_eska_' . date('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Download Template Import Excel
     */
    public function downloadTemplate()
    {
        return Excel::download(new JksTeamEliteTemplateExport, 'template_import_jks_team_elite_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Buka modal import
     */
    public function openImportModal()
    {
        $this->resetValidation();
        $this->excel_file = null;
        $this->importErrors = [];
        $this->importStep = 1;
        $this->isImportModalOpen = true;
    }

    /**
     * Preview Import Excel (Step 1)
     */
    public function previewImport()
    {
        $this->authorizeAction('can_import');

        $this->validate([
            'excel_file' => 'required|mimes:xls,xlsx,csv|max:10240', // Maks 10MB
            'importStartDate' => 'required|date',
            'importEndDate' => 'required|date|after_or_equal:importStartDate',
            'importMethod' => 'required|in:full_sync,partial_update',
        ]);

        try {
            $import = new JksTeamEliteImport();
            Excel::import($import, $this->excel_file->getRealPath());

            if (count($import->errors) > 0) {
                $this->importErrors = $import->errors;
                session()->flash('error', 'Terdapat ' . count($import->errors) . ' baris data yang bermasalah. Silakan download Log Error untuk melihat detailnya.');
                // Jangan reset excel_file atau tutup modal, agar user bisa download error log
            } else {
                $this->importErrors = [];
                $this->previewTotalRows = $import->successCount;
                $this->previewTotalTeams = count($import->distinctTeams);
                
                $this->previewExistingRows = JksTeamElite::whereBetween('tanggal', [$this->importStartDate, $this->importEndDate])
                    ->whereIn('kode_team', $import->distinctTeams)
                    ->count();

                $this->importStep = 2;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Execute Import (Step 2)
     */
    public function executeImport()
    {
        $this->authorizeAction('can_import');

        $this->validate([
            'excel_file' => 'required|mimes:xls,xlsx,csv|max:10240',
        ]);

        try {
            $import = new JksTeamEliteImport();
            Excel::import($import, $this->excel_file->getRealPath());

            if (count($import->errors) > 0) {
                $this->importErrors = $import->errors;
                $this->importStep = 1;
                session()->flash('error', 'Terdapat error saat membaca ulang file.');
                return;
            }

            if ($this->importMethod === 'full_sync') {
                JksTeamElite::whereBetween('tanggal', [$this->importStartDate, $this->importEndDate])
                    ->whereIn('kode_team', $import->distinctTeams)
                    ->delete();
                
                foreach (array_chunk($import->validInserts, 500) as $chunk) {
                    JksTeamElite::insert($chunk);
                }
            } else {
                foreach (array_chunk($import->validInserts, 500) as $chunk) {
                    JksTeamElite::upsert(
                        $chunk, 
                        ['tanggal', 'kode_team', 'distributor_code', 'custno'], 
                        ['nama_team', 'kode_region', 'nama_region', 'kode_area', 'nama_area', 'distributor_name', 'custname', 'addres', 'updated_at']
                    );
                }
            }

            \App\Helpers\ActivityLogger::log('Import JKS Team Elite', "Mengimpor " . $import->successCount . " data JKS. Metode: {$this->importMethod}");

            session()->flash('message', $import->successCount . ' Data berhasil diimport (Metode: ' . strtoupper(str_replace('_', ' ', $this->importMethod)) . ').');
            $this->isImportModalOpen = false;
            $this->excel_file = null;
            $this->importStep = 1;
            $this->importErrors = [];
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengeksekusi import: ' . $e->getMessage());
        }
    }

    public function showStoreDetails($tanggal, $kodeTeam, $pilar = null)
    {
        $titlePilar = $pilar ? " ($pilar)" : "";
        $this->storeModalTitle = "Daftar Toko - " . \Carbon\Carbon::parse($tanggal)->format('d M Y') . " - {$kodeTeam}{$titlePilar}";
        
        $query = JksTeamElite::query()
            ->select('jks_team_elite.custno', 'jks_team_elite.custname', 'jks_team_elite.distributor_name', 'l.pilar', 'l.target')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->where('jks_team_elite.tanggal', $tanggal)
            ->where('jks_team_elite.kode_team', $kodeTeam);
            
        $this->applyHierarchyAccess($query, 'jks_team_elite.distributor_code');

        if ($pilar === 'RWO') {
            $query->where('l.pilar', '1. RWO');
        } elseif ($pilar === 'PNR') {
            $query->where('l.pilar', '2. PNR');
        } elseif ($pilar === 'NGVO') {
            $query->where('l.pilar', '3. NGVO');
        }

        $this->storeModalData = $query->orderBy('jks_team_elite.custname', 'asc')
            ->get()
            ->toArray();
            
        $this->isStoreModalOpen = true;
    }

    public function showMap($tanggal, $kodeTeam, $dispatchInit = true)
    {
        $this->mapTanggal = $tanggal;
        $this->mapKodeTeam = $kodeTeam;
        $this->mapModalTitle = "Peta Persebaran Toko - " . \Carbon\Carbon::parse($tanggal)->format('d M Y') . " ($kodeTeam)";
        
        $query = JksTeamElite::query()
            ->select('jks_team_elite.custno', 'jks_team_elite.custname', 'jks_team_elite.distributor_code', 'jks_team_elite.tanggal', 'jks_team_elite.kode_team', 'jks_team_elite.nama_team', 'l.latitude', 'l.longitude', 'l.customer_address', 'mc.week_month as minggu', 'mc.day as hari', 'l.pilar')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->leftJoin('master_calender as mc', 'jks_team_elite.tanggal', '=', 'mc.date')
            ->where('jks_team_elite.tanggal', $tanggal)
            ->where('jks_team_elite.kode_team', $kodeTeam)
            ->whereNotNull('l.latitude')
            ->whereNotNull('l.longitude');
            
        $this->applyHierarchyAccess($query, 'jks_team_elite.distributor_code');
            
        $jksStores = $query->get()
            ->map(function ($store) {
                $c = \Carbon\Carbon::parse($store->tanggal);
                $store->tgl_format = $c->format('d M Y');
                $store->tanggal_ymd = $c->format('Y-m-d');
                return $store;
            });

        $distributorCodes = $jksStores->pluck('distributor_code')->unique()->toArray();
        
        // Ambil semua toko yang SUDAH dijadwalkan oleh tim ini pada BULAN yang sama
        $month = \Carbon\Carbon::parse($tanggal)->format('m');
        $year = \Carbon\Carbon::parse($tanggal)->format('Y');
        
        $alreadyScheduledCustNos = JksTeamElite::where('kode_team', $kodeTeam)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->pluck('custno')
            ->toArray();

        $paretoStores = DB::table('list_toko_pareto_team_elite')
            ->whereIn('distributor_code', $distributorCodes)
            ->whereNotIn('customer_code_prc', $alreadyScheduledCustNos)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('customer_code_prc as custno', 'customer_name as custname', 'distributor_code', 'latitude', 'longitude', 'customer_address', 'pilar')
            ->get();
            
        // Limit Pareto stores to a 10km radius from ANY scheduled store
        $filteredPareto = $paretoStores->filter(function($pareto) use ($jksStores) {
            foreach ($jksStores as $jks) {
                $lat1 = (float) $pareto->latitude;
                $lon1 = (float) $pareto->longitude;
                $lat2 = (float) $jks->latitude;
                $lon2 = (float) $jks->longitude;
                
                // Haversine formula
                $earthRadius = 6371; // in kilometers
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);
                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $distance = $earthRadius * $c;
                
                if ($distance <= 8) { // 8 KM Radius
                    return true;
                }
            }
            return false;
        });

        $this->mapModalData = [
            'scheduled' => $jksStores->toArray(),
            'pareto' => $filteredPareto->values()->toArray()
        ];
            
        $this->isMapModalOpen = true;
        if ($dispatchInit) {
            $this->dispatch('init-map');
        } else {
            $this->dispatch('update-map-markers');
        }
    }

    public function showGlobalMap()
    {
        $jksQuery = JksTeamElite::query();
        
        if (!empty($this->filterTeam)) {
            $jksQuery->whereIn('kode_team', $this->filterTeam);
        }
        
        if ($this->filterStartDate && $this->filterEndDate) {
            $jksQuery->whereBetween('tanggal', [$this->filterStartDate, $this->filterEndDate]);
        }
        
        if (!empty($this->search)) {
            $jksQuery->where(function ($q) {
                $q->where('custno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('custname', 'ilike', '%' . $this->search . '%')
                  ->orWhere('addres', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('nama_team', 'ilike', '%' . $this->search . '%');
            });
        }
        
        $this->applyHierarchyAccess($jksQuery, 'jks_team_elite.distributor_code');
        
        $jksStores = $jksQuery
            ->select('jks_team_elite.custno', 'jks_team_elite.custname', 'jks_team_elite.distributor_code', 'jks_team_elite.tanggal', 'jks_team_elite.kode_team', 'jks_team_elite.nama_team', 'l.latitude', 'l.longitude', 'l.customer_address', 'mc.week_month as minggu', 'mc.day as hari', 'l.pilar')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->leftJoin('master_calender as mc', 'jks_team_elite.tanggal', '=', 'mc.date')
            ->whereNotNull('l.latitude')
            ->whereNotNull('l.longitude')
            ->get()
            ->map(function ($store) {
                $c = \Carbon\Carbon::parse($store->tanggal);
                $store->tgl_format = $c->format('d M Y');
                $store->tanggal_ymd = $c->format('Y-m-d');
                return $store;
            });
            
        $distributorCodes = $jksStores->pluck('distributor_code')->unique()->toArray();
        $jksCustNos = $jksStores->pluck('custno')->toArray(); 
        
        $paretoStores = DB::table('list_toko_pareto_team_elite')
            ->whereIn('distributor_code', $distributorCodes)
            ->whereNotIn('customer_code_prc', $jksCustNos)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('customer_code_prc as custno', 'customer_name as custname', 'distributor_code', 'latitude', 'longitude', 'customer_address', 'pilar')
            ->get();
            
        // Limit Pareto stores to a 10km radius from ANY scheduled store in the current global view
        $filteredPareto = $paretoStores->filter(function($pareto) use ($jksStores) {
            foreach ($jksStores as $jks) {
                $lat1 = (float) $pareto->latitude;
                $lon1 = (float) $pareto->longitude;
                $lat2 = (float) $jks->latitude;
                $lon2 = (float) $jks->longitude;
                
                $earthRadius = 6371;
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);
                $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
                $c = 2 * asin(sqrt($a));
                $distance = $earthRadius * $c;
                
                if ($distance <= 10) {
                    return true;
                }
            }
            return false;
        });

        $this->mapModalTitle = "Peta Global Persebaran Toko";
        
        $availableTeams = empty($this->filterTeam) 
            ? collect($this->teams)->map(fn($t) => ['kode_team' => $t->kode_team, 'nama_team' => $t->nama_team])->toArray()
            : collect($this->teams)->whereIn('kode_team', $this->filterTeam)->map(fn($t) => ['kode_team' => $t->kode_team, 'nama_team' => $t->nama_team])->values()->toArray();

        $this->mapModalData = [
            'isGlobal' => true,
            'availableTeams' => $availableTeams,
            'scheduled' => $jksStores->toArray(),
            'pareto' => $filteredPareto->values()->toArray()
        ];
            
        $this->isMapModalOpen = true;
        $this->dispatch('init-map');
    }

    public function addStoreFromGlobalMap($custno, $distributorCode, $tanggal, $kodeTeam)
    {
        if (empty($tanggal) || empty($kodeTeam)) {
            session()->flash('message', 'Tanggal dan Team harus diisi.');
            return;
        }

        $this->mapTanggal = $tanggal;
        $this->mapKodeTeam = $kodeTeam;
        
        $this->addStoreFromMap($custno, $distributorCode);
        
        $this->showGlobalMap();
    }

    public function updateStoreFromGlobalMap($custno, $distributorCode, $oldDate, $oldTeam, $newDate, $newTeam)
    {
        if (empty($newDate) || empty($newTeam)) {
            session()->flash('message', 'Tanggal dan Team harus diisi.');
            return;
        }

        $team = collect($this->teams)->firstWhere('kode_team', $newTeam);
        $namaTeam = $team ? $team->nama_team : $newTeam;

        JksTeamElite::where('custno', $custno)
            ->where('distributor_code', $distributorCode)
            ->where('tanggal', $oldDate)
            ->where('kode_team', $oldTeam)
            ->update([
                'tanggal' => $newDate,
                'kode_team' => $newTeam,
                'nama_team' => $namaTeam
            ]);

        \App\Helpers\ActivityLogger::log('Update JKS Team Elite', "Memindahkan jadwal toko ($custno) dari $oldDate ($oldTeam) ke $newDate ($newTeam)");
        session()->flash('message', "Jadwal toko berhasil dipindahkan!");

        $this->showGlobalMap();
    }

    public function deleteStoreFromGlobalMap($custno, $distributorCode, $tanggal, $kodeTeam)
    {
        JksTeamElite::where('custno', $custno)
            ->where('distributor_code', $distributorCode)
            ->where('tanggal', $tanggal)
            ->where('kode_team', $kodeTeam)
            ->delete();

        \App\Helpers\ActivityLogger::log('Delete JKS Team Elite', "Menghapus jadwal toko ($custno) pada $tanggal ($kodeTeam)");
        session()->flash('message', "Jadwal toko berhasil dihapus dari peta!");

        $this->showGlobalMap();
    }

    public function addStoreFromMap($custno, $distributorCode)
    {
        // Pastikan kita tahu tanggal & team mana yang dituju
        if (empty($this->mapTanggal) || empty($this->mapKodeTeam)) {
            return;
        }

        // Cek apakah sudah dijadwalkan di bulan yang sama
        $month = \Carbon\Carbon::parse($this->mapTanggal)->format('m');
        $year = \Carbon\Carbon::parse($this->mapTanggal)->format('Y');

        $exists = JksTeamElite::where('kode_team', $this->mapKodeTeam)
            ->where('custno', $custno)
            ->where('distributor_code', $distributorCode)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->exists();

        if ($exists) {
            session()->flash('message', 'Toko tersebut sudah ada di jadwal pada bulan ini.');
            return;
        }

        // Ambil nama team
        $team = DB::table('fsalesman')->where('SLSNO', $this->mapKodeTeam)->first();
        $namaTeam = $team ? $team->SLSNAME : $this->mapKodeTeam;

        // Ambil detail toko dari relasi yang ada (hanya perlu join ke master_distributors)
        $storeDetails = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->where('l.customer_code_prc', $custno)
            ->where('l.distributor_code', $distributorCode)
            ->select(
                'md.region_code as kode_region',
                'md.region_name as nama_region',
                'md.area_code as kode_area',
                'md.area_name as nama_area',
                'l.distributor_code',
                'md.distributor_name',
                'l.customer_code_prc as custno',
                'l.customer_name as custname',
                'l.customer_address as addres'
            )
            ->first();

        if ($storeDetails) {
            JksTeamElite::create([
                'tanggal'          => $this->mapTanggal,
                'kode_team'        => $this->mapKodeTeam,
                'nama_team'        => $namaTeam,
                'kode_region'      => $storeDetails->kode_region,
                'nama_region'      => $storeDetails->nama_region,
                'kode_area'        => $storeDetails->kode_area,
                'nama_area'        => $storeDetails->nama_area,
                'distributor_code' => $storeDetails->distributor_code,
                'distributor_name' => $storeDetails->distributor_name,
                'custno'           => $storeDetails->custno,
                'custname'         => $storeDetails->custname,
                'addres'           => $storeDetails->addres,
            ]);

            \App\Helpers\ActivityLogger::log('Update JKS Team Elite', "Menambahkan toko ({$storeDetails->custname}) dari Map ke jadwal team: {$namaTeam} ({$this->mapTanggal})");
            session()->flash('message', "Toko {$storeDetails->custname} berhasil ditambahkan ke jadwal!");

            // Refresh Map modal data smoothly without destroying Leaflet instance
            if (!empty($this->mapModalData['isGlobal'])) {
                // Do not refresh here, addStoreFromGlobalMap will handle it
            } else {
                $this->showMap($this->mapTanggal, $this->mapKodeTeam, false);
            }
        }
    }

    /**
     * Download Error Log (TXT)
     */
    public function downloadErrorLog()
    {
        $errorText = "LAPORAN ERROR IMPORT JKS TEAM ELITE\n";
        $errorText .= "Tanggal Cetak: " . now()->format('Y-m-d H:i:s') . "\n";
        $errorText .= str_repeat("=", 80) . "\n\n";

        foreach ($this->importErrors as $err) {
            $errorText .= "- " . $err . "\n";
        }

        $errorText .= "\n" . str_repeat("=", 80) . "\n";
        $errorText .= "Silakan perbaiki data pada Excel Anda lalu lakukan import ulang.\n";

        $fileName = 'Error_Import_JKS_Team_Elite_' . now()->format('Ymd_His') . '.txt';

        return response()->streamDownload(function () use ($errorText) {
            echo $errorText;
        }, $fileName);
    }

    public function render()
    {
        $records = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        $kpi = [
            'total_toko' => 0,
            'total_target' => 0,
            'total_rwo' => 0,
            'total_pnr' => 0,
            'total_ngvo' => 0,
        ];
        $paretoKpi = [
            'total_toko' => 0,
            'total_target' => 0,
            'total_rwo' => 0,
            'total_pnr' => 0,
            'total_ngvo' => 0,
        ];

        if (!empty($this->filterTeam) && !empty($this->filterStartDate) && !empty($this->filterEndDate)) {
            $query = JksTeamElite::query()
                ->leftJoin('master_calender as mc', 'jks_team_elite.tanggal', '=', 'mc.date')
                ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                    $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                         ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
                })
                ->select(
                    'jks_team_elite.tanggal',
                    'jks_team_elite.kode_region',
                    'jks_team_elite.nama_region',
                    'jks_team_elite.kode_team',
                    'jks_team_elite.nama_team',
                    'mc.week_month',
                    DB::raw('COUNT(jks_team_elite.custno) as total_toko'),
                    DB::raw('SUM(CASE WHEN l.pilar = \'1. RWO\' THEN 1 ELSE 0 END) as total_rwo'),
                    DB::raw('SUM(CASE WHEN l.pilar = \'2. PNR\' THEN 1 ELSE 0 END) as total_pnr'),
                    DB::raw('SUM(CASE WHEN l.pilar = \'3. NGVO\' THEN 1 ELSE 0 END) as total_ngvo')
                )
                ->whereIn('jks_team_elite.kode_team', $this->filterTeam)
                ->whereBetween('jks_team_elite.tanggal', [$this->filterStartDate, $this->filterEndDate]);

            $this->applyHierarchyAccess($query, 'jks_team_elite.distributor_code');

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('jks_team_elite.nama_region', 'ilike', '%' . $this->search . '%')
                      ->orWhere('jks_team_elite.kode_region', 'ilike', '%' . $this->search . '%')
                      ->orWhere('jks_team_elite.nama_team', 'ilike', '%' . $this->search . '%')
                      ->orWhere('jks_team_elite.kode_team', 'ilike', '%' . $this->search . '%');
                });
            }

            // Validasi order by agar qualified jika yang dipilih adalah kolom default
            $sortField = $this->sortField;
            if (in_array($sortField, ['tanggal', 'kode_region', 'nama_region', 'kode_team', 'nama_team'])) {
                $sortField = 'jks_team_elite.' . $sortField;
            }

            $query->groupBy(
                    'jks_team_elite.tanggal',
                    'jks_team_elite.kode_region',
                    'jks_team_elite.nama_region',
                    'jks_team_elite.kode_team',
                    'jks_team_elite.nama_team',
                    'mc.week_month'
                )
                ->orderBy($sortField, $this->sortDirection);

            $records = $query->paginate(100);
            
            // KPI Calculation (Single Outlet / Unique Stores)
            $filterTeam = $this->filterTeam;
            $filterStartDate = $this->filterStartDate;
            $filterEndDate = $this->filterEndDate;
            $search = $this->search;

            $kpiData = DB::table(function ($query) use ($filterTeam, $filterStartDate, $filterEndDate, $search) {
                $query->select(
                        'jks_team_elite.custno',
                        'jks_team_elite.distributor_code',
                        'l.target',
                        'l.pilar'
                    )
                    ->from('jks_team_elite')
                    ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                        $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                             ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
                    })
                    ->whereIn('jks_team_elite.kode_team', $filterTeam)
                    ->whereBetween('jks_team_elite.tanggal', [$filterStartDate, $filterEndDate]);

                $this->applyHierarchyAccess($query, 'jks_team_elite.distributor_code');

                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->where('jks_team_elite.nama_region', 'ilike', '%' . $search . '%')
                          ->orWhere('jks_team_elite.kode_region', 'ilike', '%' . $search . '%')
                          ->orWhere('jks_team_elite.nama_team', 'ilike', '%' . $search . '%')
                          ->orWhere('jks_team_elite.kode_team', 'ilike', '%' . $search . '%');
                    });
                }
                
                $query->distinct();
            }, 'unique_stores')
            ->selectRaw('
                COUNT(*) as total_toko_all,
                SUM(CASE WHEN pilar IN (\'1. RWO\', \'2. PNR\', \'3. NGVO\') THEN 1 ELSE 0 END) as total_toko_3pilar,
                SUM(target) as total_target,
                SUM(CASE WHEN pilar = \'1. RWO\' THEN 1 ELSE 0 END) as total_rwo,
                SUM(CASE WHEN pilar = \'2. PNR\' THEN 1 ELSE 0 END) as total_pnr,
                SUM(CASE WHEN pilar = \'3. NGVO\' THEN 1 ELSE 0 END) as total_ngvo
            ');

            $kpiResult = $kpiData->first();
            if ($kpiResult) {
                $kpi = [
                    'total_toko_all' => $kpiResult->total_toko_all ?? 0,
                    'total_toko' => $kpiResult->total_toko_3pilar ?? 0,
                    'total_target' => $kpiResult->total_target ?? 0,
                    'total_rwo' => $kpiResult->total_rwo ?? 0,
                    'total_pnr' => $kpiResult->total_pnr ?? 0,
                    'total_ngvo' => $kpiResult->total_ngvo ?? 0,
                ];
            }

            // Pareto KPI Base Calculation (For Distributors in current filter)
            $distributorsInFilterQuery = DB::table('jks_team_elite')
                ->whereIn('kode_team', $filterTeam)
                ->whereBetween('tanggal', [$filterStartDate, $filterEndDate])
                ->when(!empty($search), function($q) use ($search) {
                    $q->where(function($q) use ($search) {
                        $q->where('nama_region', 'ilike', '%' . $search . '%')
                          ->orWhere('kode_region', 'ilike', '%' . $search . '%')
                          ->orWhere('nama_team', 'ilike', '%' . $search . '%')
                          ->orWhere('kode_team', 'ilike', '%' . $search . '%');
                    });
                });
                
            $this->applyHierarchyAccess($distributorsInFilterQuery, 'jks_team_elite.distributor_code');
            
            $distributorsInFilter = $distributorsInFilterQuery->pluck('distributor_code')->unique();

            $paretoBaseData = DB::table('list_toko_pareto_team_elite as l')
                ->whereIn('distributor_code', $distributorsInFilter)
                ->selectRaw('
                    COUNT(l.customer_code_prc) as total_toko_all,
                    SUM(CASE WHEN l.pilar IN (\'1. RWO\', \'2. PNR\', \'3. NGVO\') THEN 1 ELSE 0 END) as total_toko_3pilar,
                    SUM(l.target) as total_target,
                    SUM(CASE WHEN l.pilar = \'1. RWO\' THEN 1 ELSE 0 END) as total_rwo,
                    SUM(CASE WHEN l.pilar = \'2. PNR\' THEN 1 ELSE 0 END) as total_pnr,
                    SUM(CASE WHEN l.pilar = \'3. NGVO\' THEN 1 ELSE 0 END) as total_ngvo
                ')
                ->first();

            if ($paretoBaseData) {
                $paretoKpi = [
                    'total_toko_all' => $paretoBaseData->total_toko_all ?? 0,
                    'total_toko' => $paretoBaseData->total_toko_3pilar ?? 0,
                    'total_target' => $paretoBaseData->total_target ?? 0,
                    'total_rwo' => $paretoBaseData->total_rwo ?? 0,
                    'total_pnr' => $paretoBaseData->total_pnr ?? 0,
                    'total_ngvo' => $paretoBaseData->total_ngvo ?? 0,
                ];
            }
        }

        return view('livewire.jks-team-elite.index', [
            'records' => $records,
            'kpi' => $kpi,
            'paretoKpi' => $paretoKpi,
        ])->layout('layouts.app');
    }
}
