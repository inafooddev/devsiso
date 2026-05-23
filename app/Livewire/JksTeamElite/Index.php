<?php

namespace App\Livewire\JksTeamElite;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\JksTeamElite;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JksTeamEliteExport;
use App\Exports\JksTeamEliteTemplateExport;
use App\Imports\JksTeamEliteImport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Filters
    public $search = '';
    public $searchTeamFilter = '';
    public $filterTeam = [];
    public $filterStartDate = '';
    public $filterEndDate = '';

    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    
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
    public $storeModalData = [];
    public $storeModalTitle = '';

    protected $queryString = ['filterTeam', 'filterStartDate', 'filterEndDate'];

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
            $this->distributorOptions = DB::table('master_distributors')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('distributor_code', 'ilike', '%' . $this->searchDistributor . '%')
                      ->orWhere('distributor_name', 'ilike', '%' . $this->searchDistributor . '%');
                })
                ->select('distributor_code', 'distributor_name')
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
            session()->flash('message', 'Grup customer berhasil diperbarui.');
        } else {
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
        if (!empty($this->dataIdToDelete)) {
            JksTeamElite::whereDate('tanggal', $this->dataIdToDelete['tanggal'])
                ->where('kode_team', $this->dataIdToDelete['kode_team'])
                ->where('kode_region', $this->dataIdToDelete['kode_region'])
                ->delete();
                
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
        return Excel::download(
            new JksTeamEliteExport($this->filterTeam, $this->filterStartDate, $this->filterEndDate), 
            'jks_team_elite.xlsx'
        );
    }

    /**
     * Download Template Import Excel
     */
    public function downloadTemplate()
    {
        return Excel::download(new JksTeamEliteTemplateExport, 'template_import_jks_team_elite.xlsx');
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

    public function showStoreDetails($tanggal, $kodeTeam)
    {
        $this->storeModalTitle = "Daftar Toko - " . \Carbon\Carbon::parse($tanggal)->format('d M Y') . " ($kodeTeam)";
        $this->storeModalData = JksTeamElite::query()
            ->select('jks_team_elite.custno', 'jks_team_elite.custname', 'jks_team_elite.distributor_name', 'l.pilar', 'l.target')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->where('jks_team_elite.tanggal', $tanggal)
            ->where('jks_team_elite.kode_team', $kodeTeam)
            ->orderBy('jks_team_elite.custname', 'asc')
            ->get()
            ->toArray();
            
        $this->isStoreModalOpen = true;
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

        if (!empty($this->filterTeam) && !empty($this->filterStartDate) && !empty($this->filterEndDate)) {
            $query = JksTeamElite::query()
                ->select(
                    'tanggal',
                    'kode_region',
                    'nama_region',
                    'kode_team',
                    'nama_team',
                    DB::raw('COUNT(*) as total_toko')
                )
                ->whereIn('kode_team', $this->filterTeam)
                ->whereBetween('tanggal', [$this->filterStartDate, $this->filterEndDate]);

            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('nama_region', 'ilike', '%' . $this->search . '%')
                      ->orWhere('kode_region', 'ilike', '%' . $this->search . '%')
                      ->orWhere('nama_team', 'ilike', '%' . $this->search . '%')
                      ->orWhere('kode_team', 'ilike', '%' . $this->search . '%');
                });
            }

            $query->groupBy(
                    'tanggal',
                    'kode_region',
                    'nama_region',
                    'kode_team',
                    'nama_team'
                )
                ->orderBy('tanggal', 'desc');

            $records = $query->paginate(10);
            
            // KPI Calculation
            $kpiData = JksTeamElite::query()
                ->selectRaw('
                    COUNT(jks_team_elite.custno) as total_toko,
                    SUM(l.target) as total_target,
                    SUM(CASE WHEN l.pilar = \'1. RWO\' THEN 1 ELSE 0 END) as total_rwo,
                    SUM(CASE WHEN l.pilar = \'2. PNR\' THEN 1 ELSE 0 END) as total_pnr,
                    SUM(CASE WHEN l.pilar = \'3. NGVO\' THEN 1 ELSE 0 END) as total_ngvo
                ')
                ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                    $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                         ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
                })
                ->whereIn('jks_team_elite.kode_team', $this->filterTeam)
                ->whereBetween('jks_team_elite.tanggal', [$this->filterStartDate, $this->filterEndDate]);

            if (!empty($this->search)) {
                $kpiData->where(function($q) {
                    $q->where('jks_team_elite.nama_region', 'ilike', '%' . $this->search . '%')
                      ->orWhere('jks_team_elite.kode_region', 'ilike', '%' . $this->search . '%')
                      ->orWhere('jks_team_elite.nama_team', 'ilike', '%' . $this->search . '%')
                      ->orWhere('jks_team_elite.kode_team', 'ilike', '%' . $this->search . '%');
                });
            }

            $kpiResult = $kpiData->first();
            if ($kpiResult) {
                $kpi = [
                    'total_toko' => $kpiResult->total_toko ?? 0,
                    'total_target' => $kpiResult->total_target ?? 0,
                    'total_rwo' => $kpiResult->total_rwo ?? 0,
                    'total_pnr' => $kpiResult->total_pnr ?? 0,
                    'total_ngvo' => $kpiResult->total_ngvo ?? 0,
                ];
            }
        }

        return view('livewire.jks-team-elite.index', [
            'records' => $records,
            'kpi' => $kpi,
        ])->layout('layouts.app');
    }
}
