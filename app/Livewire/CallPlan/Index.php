<?php

namespace App\Livewire\CallPlan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    #[Title('Call Plan Maps')]

    // ============================================================
    // PROPERTI / STATE
    // ============================================================

    /** Properti Filter Utama (Sidebar & Map) */
    public $regions = [], $areas = [], $distributors = [], $salesmen = [];
    public $selectedRegion, $selectedArea, $selectedDistributor, $selectedSalesman;
    public $searchStore = '';
    public $isFilterApplied = false;
    public $showFilterModal = false;
    public $selectedWeeks = [];
    public $selectedDays = [];

    /** Properti Edit Jadwal Store */
    public $editingStore = null;
    public $showEditScheduleModal = false;
    public $selectedSalesmanInModal = null;

    /** Properti Filter Export (Excel) */
    public $showExportModal = false;
    public $exportEntities = [], $exportBranches = [], $exportSalesmen = [];
    public $selectedExpRegion, $selectedExpEntity, $selectedExpBranch;
    public $selectedExpSls = [];

    /** Properti Tambah Rute Baru (Modal Add) */
    public $showAddModal = false;
    public $searchCustomer = '';
    public $selectedCustomers = []; // Menampung banyak outlet terpilih
    public $newRouteSalesman, $newRouteDay, $newRouteWeeks = [];
    public $newRouteRegion, $newRouteArea, $newRouteDistributor;
    

    /** Properti Tampilan & Konfigurasi */
    public $showNonRute = false;
    public $options = [
        'weeks' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        'days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
    ];

    /** Konfigurasi Warna Marker/UI berdasarkan Hari */
    public $dayColors = [
        'Senin' => ['ganjil' => '#DC2626', 'genap' => '#FCA5A5'],
        'Selasa' => ['ganjil' => '#EA580C', 'genap' => '#FDBA74'],
        'Rabu' => ['ganjil' => '#CA8A04', 'genap' => '#FDE68A'],
        'Kamis' => ['ganjil' => '#16A34A', 'genap' => '#86EFAC'],
        'Jumat' => ['ganjil' => '#2563EB', 'genap' => '#93C5FD'],
        'Sabtu' => ['ganjil' => '#7C3AED', 'genap' => '#C4B5FD'],
        'Minggu' => ['ganjil' => '#0D9488', 'genap' => '#5EEAD4'],
        'Non-Rute' => ['ganjil' => '#4B5563', 'genap' => '#9CA3AF'],
    ];

    public $salesmanPalette = [
        '#3B82F6', // Blue
        '#10B981', // Emerald
        '#F59E0B', // Amber
        '#EF4444', // Red
        '#8B5CF6', // Violet
        '#06B6D4', // Cyan
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
     * Helper Keamanan: Memastikan ID Frute yang diubah/dihapus ada di wilayah user.
     */
    private function checkFruteAccess($fruteId)
    {
        $query = DB::table('frute')
            ->join('master_distributors', 'frute.kodecabang', '=', 'master_distributors.distributor_code')
            ->where('frute.id', $fruteId);
            
        $this->applyRegionAccess($query, 'master_distributors.region_code');
        
        return $query->exists();
    }

    // ============================================================
    // SIKLUS HIDUP (LIFECYCLE)
    // ============================================================

    public function mount()
    {
        $this->loadRegions();
        $this->showFilterModal = true;
    }

    public function render()
    {
        return view('livewire.call-plan.index', [
            'filteredStores' => $this->filteredStores
        ])->layout('layouts.app');
    }

    // ============================================================
    // LOGIKA FILTER PETA (SIDEBAR)
    // ============================================================

    /** Memuat data Region awal */
    public function loadRegions()
    {
        $query = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct();

        $this->applyRegionAccess($query);
        $this->regions = $query->orderBy('region_name')->get()->toArray();

        // Auto-select jika user biasa hanya memiliki 1 Region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->selectedRegion = $this->regions[0]->region_code;
            $this->updatedSelectedRegion();
        }
    }

    /** Trigger saat Region dipilih: Load Area */
    public function updatedSelectedRegion()
    {
        $this->loadAreas();
        $this->selectedArea = $this->selectedDistributor = $this->selectedSalesman = null;
    }

    /** Memuat data Area berdasarkan Region */
    public function loadAreas()
    {
        if ($this->selectedRegion) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->select('area_code', 'area_name')
                ->distinct();

            $this->applyRegionAccess($query);
            $this->areas = $query->orderBy('area_name')->get()->toArray();
        }
    }

    /** Trigger saat Area dipilih: Load Distributor */
    public function updatedSelectedArea()
    {
        $this->loadDistributors();
        $this->selectedDistributor = $this->selectedSalesman = null;
    }

    /** Memuat data Distributor berdasarkan Area */
    public function loadDistributors()
    {
        if ($this->selectedArea) {
            $query = DB::table('master_distributors')
                ->where('region_code', $this->selectedRegion)
                ->where('area_code', $this->selectedArea)
                ->where('is_active', true)
                ->select('distributor_code', 'distributor_name')
                ->distinct();

            $this->applyRegionAccess($query);
            $this->distributors = $query->orderBy('distributor_name')->get()->toArray();
        }
    }

    /** Trigger saat Distributor dipilih: Load Salesman */
    public function updatedSelectedDistributor()
    {
        $this->loadSalesmen();
        $this->selectedSalesman = null;
    }

    /** Memuat daftar Salesman yang aktif di rute distributor tersebut */
    public function loadSalesmen()
    {
        if (!$this->selectedDistributor) return;

        $this->salesmen = DB::table('frute as f')
            ->join('distributor_implementasi_eskalink as diel', 'f.kodecabang', '=', 'diel.eskalink_code')
            ->join('fsalesman as ff', function ($j) {
                $j->on('f.kodecabang', '=', DB::raw('ff."KD"'))
                  ->on('f.slsno', '=', DB::raw('ff."SLSNO"'));
            })
            ->where('diel.distributor_code', $this->selectedDistributor)
            ->select(
                'f.slsno',
                DB::raw('ff."SLSNAME" as slsname')
            )
            ->distinct()
            ->orderBy('slsname')
            ->get()
            ->toArray();
    }

    // ============================================================
    // LOGIKA FILTER & EXPORT EXCEL
    // ============================================================

    /** Cascading Filter untuk Modal Export */
    public function updatedSelectedExpRegion()
    {
        $query = DB::table('master_distributors')
            ->where('region_code', $this->selectedExpRegion)
            ->select('area_code', 'area_name')->distinct();
        
        $this->applyRegionAccess($query);
        
        $this->exportEntities = $query->orderBy('area_name')->get()->toArray();
        $this->selectedExpEntity = $this->selectedExpBranch = null;
        $this->exportBranches = $this->exportSalesmen = $this->selectedExpSls = [];
    }

    public function updatedSelectedExpEntity()
    {
        $query = DB::table('master_distributors')
            ->where('region_code', $this->selectedExpRegion)
            ->where('area_code', $this->selectedExpEntity)
            ->where('is_active', true)
            ->select('distributor_code', 'distributor_name')->distinct();

        $this->applyRegionAccess($query);

        $this->exportBranches = $query->orderBy('distributor_name')->get()->toArray();
        $this->selectedExpBranch = null;
        $this->exportSalesmen = $this->selectedExpSls = [];
    }

    public function updatedSelectedExpBranch()
    {
        $this->exportSalesmen = DB::table('frute as f')
            ->join('fsalesman as ff', function ($j) {
                $j->on('f.kodecabang', '=', DB::raw('ff."KD"'))
                ->on('f.slsno', '=', DB::raw('ff."SLSNO"'));
            })
            ->where('f.kodecabang', $this->selectedExpBranch)
            ->select(
                'f.slsno',
                DB::raw('ff."SLSNAME" as slsname')
            )
            ->distinct()
            ->orderBy('slsname')
            ->get()
            ->toArray();

        $this->selectedExpSls = [];
    }

    /** Pilih semua salesman untuk export */
    public function selectAllExportSls()
    {
        if (count($this->selectedExpSls) === count($this->exportSalesmen) && count($this->exportSalesmen) > 0) {
            $this->selectedExpSls = [];
        } else {
            $this->selectedExpSls = array_column($this->exportSalesmen, 'slsno');
        }
    }

    /** Menjalankan proses download Excel */
    public function exportExcel()
    {
        $this->validate([
            'selectedExpRegion' => 'required',
            'selectedExpEntity' => 'required',
            'selectedExpBranch' => 'required',
            'selectedExpSls' => 'required|array|min:1',
        ]);

        // Security check Ekspor
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->selectedExpRegion, $user->region_code)) {
                $this->dispatch('alert', message: 'Anda tidak memiliki otoritas mengekspor region ini.');
                return;
            }
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CallPlanExport(
                $this->selectedExpRegion,
                $this->selectedExpEntity,
                $this->selectedExpBranch,
                $this->selectedExpSls
            ),
            'CallPlan_' . date('YmdHis') . '.xlsx'
        );
    }

    // ============================================================
    // AKSI & MANIPULASI DATA (CRUD)
    // ============================================================

    /** Menerapkan filter sidebar ke peta */
    public function applyAdvancedFilter()
    {
        if (!$this->selectedDistributor) {
            $this->dispatch('alert', message: 'Pilih Distributor!');
            return;
        }

        // Security check Tampilkan Peta
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->selectedRegion, $user->region_code)) {
                $this->dispatch('alert', message: 'Wilayah yang dipilih di luar otoritas Anda.');
                return;
            }
        }

        $this->isFilterApplied = true;
        $this->showFilterModal = false;
        $this->dispatch('filters-updated', stores: $this->filteredStores);
    }

    /** Membuka modal edit rute untuk toko tertentu */
    public function editSchedule($store)
    {
        $this->editingStore = $store;
        $this->selectedSalesmanInModal = $store['slsno'] ?? null;
        $this->showEditScheduleModal = true;
    }

    /** Menyimpan pembaruan rute (Hari, Minggu, Salesman) */
    public function saveStore($fruteId, $weeks, $day, $newSlsNo = null)
    {
        // Security Check Update Data
        if (!$this->checkFruteAccess($fruteId)) {
            $this->dispatch('alert', message: 'Akses Ditolak: Anda tidak memiliki otoritas di distributor ini.');
            return;
        }

        try {
            $daysMap = ['Senin' => 'h1', 'Selasa' => 'h2', 'Rabu' => 'h3', 'Kamis' => 'h4', 'Jumat' => 'h5', 'Sabtu' => 'h6', 'Minggu' => 'h7'];
            $updateData = [];

            foreach ($daysMap as $label => $col) $updateData[$col] = ($day === $label) ? 'Y' : 'T';
            for ($i = 1; $i <= 4; $i++) $updateData["m$i"] = in_array("Week $i", $weeks) ? 'Y' : 'T';
            if ($newSlsNo) $updateData['slsno'] = $newSlsNo;

            DB::table('frute')->where('id', $fruteId)->update($updateData);

            $this->dispatch('alert', message: 'Data Berhasil Diperbarui!');
            $this->dispatch('filters-updated', stores: $this->filteredStores);
            $this->showEditScheduleModal = false;
        } catch (\Exception $e) {
            $this->dispatch('alert', message: 'Gagal Update.');
        }
    }

    /** Menghapus data rute toko */
    public function deleteStore($id)
    {
        // Security Check Hapus Data
        if (!$this->checkFruteAccess($id)) {
            $this->dispatch('alert', message: 'Akses Ditolak: Anda tidak dapat menghapus rute di distributor ini.');
            return;
        }

        if (DB::table('frute')->where('id', $id)->delete()) {
            $this->dispatch('alert', message: "Data Dihapus!");
            $this->dispatch('filters-updated', stores: $this->filteredStores);
        }
    }

    // ============================================================
    // LOGIKA MODAL TAMBAH CUSTOMER BARU
    // ============================================================

    /** Cascading Filter untuk Modal Add */
    public function updatedNewRouteRegion($value)
    {
        $query = DB::table('master_distributors')
            ->where('region_code', $value)
            ->select('area_code', 'area_name')->distinct();

        $this->applyRegionAccess($query);

        $this->exportEntities = $query->orderBy('area_name')->get()->toArray();
        $this->newRouteArea = $this->newRouteDistributor = null;
    }

    public function updatedNewRouteArea($value)
    {
        $query = DB::table('master_distributors')
            ->where('region_code', $this->newRouteRegion)
            ->where('area_code', $value)
            ->where('is_active', true)
            ->select('distributor_code', 'distributor_name')->distinct();

        $this->applyRegionAccess($query);

        $this->exportBranches = $query->orderBy('distributor_name')->get()->toArray();
        $this->newRouteDistributor = null;
    }

    public function updatedNewRouteDistributor($value)
    {
        if (!$value) {
            $this->exportSalesmen = [];
            return;
        }

        $this->exportSalesmen = DB::table('frute as f')
            ->join('fsalesman as ff', function ($j) {
                $j->on('f.kodecabang', '=', DB::raw('ff."KD"'))
                ->on('f.slsno', '=', DB::raw('ff."SLSNO"'));
            })
            ->where('f.kodecabang', $value)
            ->select(
                'f.slsno',
                DB::raw('ff."SLSNAME" as slsname')
            )
            ->distinct()
            ->orderBy('slsname')
            ->get()
            ->toArray();
    }

    /** Pencarian Master Customer di Modal Add */
    #[Computed]
    public function masterCustomers()
    {
        if (strlen($this->searchCustomer) < 3) return [];

        return DB::table('customer_prc_eska')
            ->where('kodecabang', $this->newRouteDistributor)
            ->where(function ($q) {
                $q->where('custno', 'ilike', '%' . $this->searchCustomer . '%')
                    ->orWhere('custname', 'ilike', '%' . $this->searchCustomer . '%');
            })
            ->limit(10)
            ->get();
    }

    /** Menambah Customer ke daftar pilihan sementara di modal */
    public function addCustomerToSelection($custno, $custname)
    {
        if (!collect($this->selectedCustomers)->contains('custno', $custno)) {
            $this->selectedCustomers[] = ['custno' => $custno, 'name' => $custname];
        }
        $this->searchCustomer = '';
    }

    /** Menghapus customer dari daftar pilihan sementara di modal */
    public function removeCustomerFromSelection($index)
    {
        unset($this->selectedCustomers[$index]);
        $this->selectedCustomers = array_values($this->selectedCustomers);
    }

    /** Simpan rute-rute baru yang telah dipilih di modal */
    public function storeCustomRoute()
    {
        $this->validate([
            'newRouteDistributor' => 'required',
            'newRouteSalesman' => 'required',
            'newRouteDay' => 'required',
            'newRouteWeeks' => 'required|array|min:1',
            'selectedCustomers' => 'required|array|min:1',
        ], [
            'newRouteDistributor.required' => 'Pilih Distributor dahulu.',
            'newRouteSalesman.required' => 'Pilih Salesman dahulu.',
            'newRouteDay.required' => 'Pilih Hari dahulu.',
            'newRouteWeeks.min' => 'Pilih minimal satu Minggu.',
            'selectedCustomers.min' => 'Pilih minimal satu Customer.',
        ]);

        // Security Check Add Route
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->newRouteRegion, $user->region_code)) {
                $this->dispatch('alert', message: 'Akses Ditolak: Anda tidak memiliki otoritas di region ini.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            foreach ($this->selectedCustomers as $customer) {
                $count = DB::table('frute')
                    ->where('custno', $customer['custno'])
                    ->where('slsno', $this->newRouteSalesman)
                    ->where('kodecabang', $this->newRouteDistributor)
                    ->count();

                $norute = $count + 1;
                $daysMap = ['Senin' => 'h1', 'Selasa' => 'h2', 'Rabu' => 'h3', 'Kamis' => 'h4', 'Jumat' => 'h5', 'Sabtu' => 'h6', 'Minggu' => 'h7'];

                $insertData = [
                    'region'      => $this->newRouteRegion,
                    'kodecabang'  => $this->newRouteDistributor,
                    'cabang'      => $this->newRouteArea,
                    'slsno'       => $this->newRouteSalesman,
                    'norute'      => $norute,
                    'custno'      => $customer['custno'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                foreach ($daysMap as $lbl => $col) {
                    $insertData[$col] = ($this->newRouteDay === $lbl) ? 'Y' : 'T';
                }

                for ($i = 1; $i <= 4; $i++) {
                    $insertData["m$i"] = in_array("Week $i", $this->newRouteWeeks) ? 'Y' : 'T';
                }

                DB::table('frute')->insert($insertData);
            }

            DB::commit();

            $this->reset(['showAddModal', 'selectedCustomers', 'searchCustomer', 'newRouteWeeks']);
            $this->dispatch('alert', message: 'Customer Berhasil Ditambahkan ke Rute!');
            $this->dispatch('filters-updated', stores: $this->filteredStores);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', message: 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // ============================================================
    // HELPER & TOGGLE UTILITIES
    // ============================================================

    /** Toggle filter khusus data non-rute */
    public function toggleNonRute()
    {
        $this->showNonRute = !$this->showNonRute;
        if ($this->showNonRute) {
            $this->selectedDays = [];
            $this->selectedWeeks = [];
        }
    }

    /** Toggle filter Minggu di Sidebar */
    public function toggleWeek($week)
    {
        $this->selectedWeeks = in_array($week, $this->selectedWeeks)
            ? array_values(array_filter($this->selectedWeeks, fn($w) => $w !== $week))
            : array_merge($this->selectedWeeks, [$week]);

        $this->dispatch('filters-updated', stores: $this->filteredStores);
    }

    /** Toggle filter Hari di Sidebar */
    public function toggleDay($day)
    {
        $this->selectedDays = in_array($day, $this->selectedDays)
            ? array_values(array_filter($this->selectedDays, fn($d) => $d !== $day))
            : array_merge($this->selectedDays, [$day]);

        $this->dispatch('filters-updated', stores: $this->filteredStores);
    }

    /** Trigger update map saat pilihan salesman berubah */
    public function updatedSelectedSalesman()
    {
        $this->dispatch('filters-updated', stores: $this->filteredStores);
    }

    /** Pilih semua hari dan minggu sekaligus */
    public function selectAllFilters()
    {
        $this->selectedWeeks = $this->options['weeks'];
        $this->selectedDays = $this->options['days'];
        $this->dispatch('filters-updated', stores: $this->filteredStores);
    }

    /** Reset filter pencarian dan checkbox ke awal */
    public function resetFilters()
    {
        $this->selectedWeeks = [];
        $this->selectedDays = [];
        $this->searchStore = '';
        $this->dispatch('filters-updated', stores: $this->filteredStores);
    }

    // ============================================================
    // LOGIKA PENGAMBILAN DATA (COMPUTED)
    // ============================================================

    /** Mengambil data toko yang akan ditampilkan di peta berdasarkan filter */
    #[Computed]
    public function filteredStores() 
    {
        // Cek apakah filter utama sudah diterapkan dan salesman sudah dipilih
        if (!$this->isFilterApplied || !$this->selectedSalesman) {
            return [];
        }

        // Security Check Tambahan pada level Raw SQL
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if (!in_array($this->selectedRegion, $user->region_code)) {
                return []; // Block query jika region terlarang
            }
        }

        $isAll = $this->selectedSalesman === 'all';
        $daysMap = [
            'Senin' => 'h1', 'Selasa' => 'h2', 'Rabu' => 'h3', 'Kamis' => 'h4', 
            'Jumat' => 'h5', 'Sabtu' => 'h6', 'Minggu' => 'h7'
        ];
        
        // 1. Logika Filter SQL untuk Jadwal Kunjungan atau Non-Rute
        if ($this->showNonRute) {
            // Filter Non-Rute: Mencari data yang tidak memiliki jadwal di Hari ATAU Minggu (Semua 'T')
            $scheduleSql = " AND (
                (f.h1='T' AND f.h2='T' AND f.h3='T' AND f.h4='T' AND f.h5='T' AND f.h6='T' AND f.h7='T')
                OR 
                (f.m1='T' AND f.m2='T' AND f.m3='T' AND f.m4='T')
            )";
        } else {
            // Filter Normal: Berdasarkan pilihan Minggu dan Hari
            $weekSql = !empty($this->selectedWeeks) 
                ? " AND (" . implode(" OR ", array_map(fn($w) => "f.m".filter_var($w, FILTER_SANITIZE_NUMBER_INT)."='Y'", $this->selectedWeeks)) . ")" 
                : " AND 1=0";
                
            $daySql = !empty($this->selectedDays) 
                ? " AND (" . implode(" OR ", array_map(fn($d) => "f.".$daysMap[$d]."='Y'", $this->selectedDays)) . ")" 
                : " AND 1=0";
            
            $scheduleSql = $weekSql . $daySql;
        }

        // 2. Query Data dari Database
        $sql = "SELECT f.id as frute_id, f.slsno, ff.\"SLSNAME\" as slsname, f.custno, cpe.custname, cpe.custadd1, 
                    f.h1, f.h2, f.h3, f.h4, f.h5, f.h6, f.h7, f.m1, f.m2, f.m3, f.m4, cpe.la, cpe.lg 
                FROM frute f 
                LEFT JOIN fsalesman ff ON f.kodecabang = ff.\"KODECABANG\" AND f.slsno = ff.\"SLSNO\" 
                LEFT JOIN customer_prc_eska cpe ON f.kodecabang = cpe.kodecabang AND f.custno = cpe.custno 
                INNER JOIN master_distributors md ON f.kodecabang = md.distributor_code 
                WHERE md.region_code = ? AND md.area_code = ? AND md.distributor_code = ? {$scheduleSql}";
        
        $params = [$this->selectedRegion, $this->selectedArea, $this->selectedDistributor];
        
        if (!$isAll) { 
            $sql .= " AND f.slsno = ?"; 
            $params[] = $this->selectedSalesman; 
        }

        if ($this->searchStore) { 
            $sql .= " AND (cpe.custname ILIKE ? OR f.custno ILIKE ? OR cpe.custadd1 ILIKE ?)";
            $t = "%" . strtolower($this->searchStore) . "%";
            $params[] = $t; 
            $params[] = $t; 
            $params[] = $t; 
        }

        // 3. Persiapan Penentuan Warna Salesman
        $distributorSalesmen = array_column($this->salesmen, 'slsno');

        // 4. Transformasi Data untuk Frontend
        return array_map(function($item) use ($daysMap, $distributorSalesmen) {
            $r = (array)$item;
            
            // Cari hari pertama yang 'Y' untuk label tampilan
            $d = 'Non-Rute'; 
                foreach($daysMap as $label => $col) { 
                    if (($r[$col] ?? 'T') === 'Y') { 
                        $d = $label; 
                        break; 
                    } 
                }

            // Penentuan warna SE berdasarkan index di palet (max 6)
            $seIndex = array_search($r['slsno'], $distributorSalesmen);
            $seColor = ($seIndex !== false) ? $this->salesmanPalette[$seIndex % 6] : '#9CA3AF';

            return [
                'frute_id' => $r['frute_id'],
                'code'     => $r['custno'],
                'name'     => $r['custname'],
                'address'  => $r['custadd1'],
                'weeks'    => array_keys(array_filter([
                    'Week 1' => $r['m1'] == 'Y', 
                    'Week 2' => $r['m2'] == 'Y', 
                    'Week 3' => $r['m3'] == 'Y', 
                    'Week 4' => $r['m4'] == 'Y'
                ])),
                'day'      => $d,
                'salesman' => $r['slsname'] ?? 'Unknown',
                'lat'      => (float)$r['la'],
                'lng'      => (float)$r['lg'],
                'slsno'    => $r['slsno'],
                'se_color' => $seColor 
            ];
        }, DB::select($sql, $params));
    }

    /** Menghitung jumlah toko yang belum memiliki koordinat (Lat/Lng) */
    #[Computed]
    public function untaggedCount()
    {
        if (!$this->isFilterApplied) return 0;

        return collect($this->filteredStores)->filter(function ($store) {
            return empty($store['lat']) || $store['lat'] == 0;
        })->count();
    }
}