<?php

namespace App\Livewire\Rwo\MasterCustomer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RewardOutlet;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterBranch;
use App\Traits\EnforcesMenuPermissions;
use App\Livewire\Rwo\MasterCustomer\Concerns\HasHierarchyAccess;
use App\Livewire\Rwo\MasterCustomer\Forms\RewardOutletForm;
use App\Livewire\Rwo\MasterCustomer\Actions\SaveRewardOutlet;
use App\Livewire\Rwo\MasterCustomer\Actions\DeleteRewardOutlet;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RewardOutletExport;
use App\Imports\RewardOutletImport;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use EnforcesMenuPermissions;
    use HasHierarchyAccess;

    protected string $menuRoute = 'rwo.index';

    // State Modals
    public $isFormModalOpen = false;
    public $isDetailModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $isExportModalOpen = false;
    public $isFilterModalOpen = false;
    public $isStatusModalOpen = false;

    // Finance State
    public $financeOutletId = null;
    public $financeNote = '';

    // Filter & Search
    public $search = '';
    public $filter_finance_status = '';
    public $filter_check_spm = '';
    public $filter_status_data = '';
    public $filter_ktp = '';
    public $filter_rekening = '';
    public $filter_geotag = '';
    

    
    // Form Object
    public RewardOutletForm $form;

    // View / Delete selection
    public $selectedOutlet;
    public $statusOutlet = null;
    public $outletIdToDelete;
    
    // Upload files for import
    public $importFile;

    // Export Flags
    public $export_foto_ktp = false;
    public $export_foto_toko = false;
    public $export_foto_toko2 = false;
    public $export_foto_toko3 = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_region_code' => ['except' => ''],
        'filter_area_code' => ['except' => ''],
        'filter_branch_name' => ['except' => ''],
        'filter_finance_status' => ['except' => ''],
        'filter_check_spm' => ['except' => ''],
        'filter_status_data' => ['except' => ''],
        'filter_ktp' => ['except' => ''],
        'filter_rekening' => ['except' => ''],
        'filter_geotag' => ['except' => ''],
    ];

    public $temp_filter_region_code = '';
    public $temp_filter_area_code = '';
    public $temp_filter_branch_name = '';

    public function updating($property, $value)
    {
        if (in_array($property, [
            'search', 
            'filter_finance_status', 
            'filter_check_spm', 
            'filter_status_data', 
            'filter_ktp', 
            'filter_rekening', 
            'filter_geotag'
        ])) {
            $this->resetPage();
        }
    }
    public function setFilterFinance($status) { $this->filter_finance_status = $status; $this->resetPage(); }

    public function openCreateModal()
    {
        $this->form->reset();
        $this->isFormModalOpen = true;
    }

    public function openEditModal($id)
    {
        $outlet = RewardOutlet::findOrFail($id);
        
        if ($outlet->isFinalized()) {
            $this->dispatch('notify', type: 'error', message: 'Data ini telah difinalisasi dan tidak dapat diedit.');
            return;
        }

        $this->form->setOutlet($outlet);
        $this->isFormModalOpen = true;
    }

    public function openDetailModal($id)
    {
        $this->selectedOutlet = RewardOutlet::findOrFail($id);
        $this->financeOutletId = $this->selectedOutlet->id;
        $this->financeNote = $this->selectedOutlet->finance_note ?? '';
        $this->isDetailModalOpen = true;
    }

    public function openStatusModal($id)
    {
        $this->statusOutlet = RewardOutlet::findOrFail($id);
        $this->isStatusModalOpen = true;
    }

    public function closeStatusModal()
    {
        $this->isStatusModalOpen = false;
        $this->statusOutlet = null;
    }

    public function closeFormModal()
    {
        $this->isFormModalOpen = false;
        $this->form->reset();
    }

    public function removePhoto($field)
    {
        $field = str_replace('form.', '', $field);
        $validFields = ['foto_ktp', 'foto_toko', 'foto_toko2', 'foto_toko3'];
        if (!in_array($field, $validFields)) return;

        $this->form->$field = null;
        $existingField = 'existing_' . $field;
        
        if ($this->form->outletId && $this->form->$existingField) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->form->$existingField);
            
            $outlet = RewardOutlet::findOrFail($this->form->outletId);
            $outlet->update([$field => null]);
            
            \App\Helpers\ActivityLogger::log('Update RWO', "Menghapus foto {$field} dari data RWO: {$this->form->customer_code}");
        }
        
        $this->form->$existingField = null;
        $this->dispatch('notify', type: 'success', message: 'Foto berhasil dihapus.');
    }

    public function save()
    {
        $this->authorizeAction('can_edit');
        
        if ($this->form->outletId) {
            $outlet = RewardOutlet::find($this->form->outletId);
            if ($outlet && $outlet->isFinalized()) {
                $this->dispatch('notify', type: 'error', message: 'Data ini telah difinalisasi dan tidak dapat diedit.');
                return;
            }
        }

        // Auto-fill region_name and area_name based on codes
        if (!empty($this->form->region_code)) {
            $region = MasterRegion::where('region_code', $this->form->region_code)->first();
            $this->form->region_name = $region ? $region->region_name : '';
        }
        
        if (!empty($this->form->area_code)) {
            $area = MasterArea::where('area_code', $this->form->area_code)->first();
            $this->form->area_name = $area ? $area->area_name : '';
        }

        try {
            $this->form->validate();
            
            app(SaveRewardOutlet::class)->execute($this->form);
            
            $this->dispatch('notify', type: 'success', message: 'Data RWO berhasil disimpan.');
            $this->closeFormModal();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', type: 'error', message: 'Terdapat isian yang tidak valid, silakan periksa form kembali.');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('RWO Database Save Error: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Gagal menyimpan ke database (mungkin duplikat atau data tidak sesuai format).');
        } catch (\Exception $e) {
            \Log::error('RWO Save Error: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $outlet = RewardOutlet::findOrFail($id);
        if ($outlet->isFinalized()) {
            $this->dispatch('notify', type: 'error', message: 'Data ini telah difinalisasi dan tidak dapat dihapus.');
            return;
        }

        $this->outletIdToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_delete');
        
        if ($this->outletIdToDelete) {
            $outlet = RewardOutlet::findOrFail($this->outletIdToDelete);
            if ($outlet->isFinalized()) {
                $this->dispatch('notify', type: 'error', message: 'Data ini telah difinalisasi dan tidak dapat dihapus.');
                $this->isDeleteModalOpen = false;
                $this->outletIdToDelete = null;
                return;
            }

            app(DeleteRewardOutlet::class)->execute($this->outletIdToDelete);
            $this->isDeleteModalOpen = false;
            $this->outletIdToDelete = null;
            $this->dispatch('notify', type: 'success', message: 'Data RWO berhasil dihapus.');
        }
    }

    // Dependencies for forms
    public function getRegions()
    {
        $user = auth()->user();
        $query = MasterRegion::query();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }
        return $query->orderBy('region_name')->get();
    }

    public function getAreas()
    {
        if (empty($this->form->region_code)) return collect();
        return MasterArea::where('region_code', $this->form->region_code)->orderBy('area_name')->get();
    }

    public function getBranches()
    {
        return MasterBranch::orderBy('branch_name')->get();
    }

    public function getBanksList()
    {
        return [
            'Bank Central Asia (BCA)',
            'Bank Mandiri',
            'Bank Rakyat Indonesia (BRI)',
            'Bank Negara Indonesia (BNI)',
            'Bank Syariah Indonesia (BSI)',
            'Bank Tabungan Negara (BTN)',
            'Bank Danamon',
            'Bank CIMB Niaga',
            'Bank Permata',
            'OCBC NISP',
            'Bank Mega',
            'Bank Bukopin',
            'Bank Panin',
            'Bank Jago',
            'Allo Bank',
            'Bank BJB',
            'Bank DKI',
            'Bank Jatim',
            'Bank Jateng',
            'Bank Nobu',
            'SeaBank',
            'Bank Neo Commerce',
        ];
    }

    public function getFotoKtpPreview()
    {
        if ($this->form->foto_ktp && method_exists($this->form->foto_ktp, 'temporaryUrl')) {
            try { return $this->form->foto_ktp->temporaryUrl(); } catch (\Exception $e) { return null; }
        }
        return null;
    }

    public function getFotoTokoPreview()
    {
        if ($this->form->foto_toko && method_exists($this->form->foto_toko, 'temporaryUrl')) {
            try { return $this->form->foto_toko->temporaryUrl(); } catch (\Exception $e) { return null; }
        }
        return null;
    }

    public function getFotoToko2Preview()
    {
        if ($this->form->foto_toko2 && method_exists($this->form->foto_toko2, 'temporaryUrl')) {
            try { return $this->form->foto_toko2->temporaryUrl(); } catch (\Exception $e) { return null; }
        }
        return null;
    }

    public function getFotoToko3Preview()
    {
        if ($this->form->foto_toko3 && method_exists($this->form->foto_toko3, 'temporaryUrl')) {
            try { return $this->form->foto_toko3->temporaryUrl(); } catch (\Exception $e) { return null; }
        }
        return null;
    }

    public function openFilterModal()
    {
        $this->temp_filter_region_code = $this->filter_region_code;
        $this->temp_filter_area_code = $this->filter_area_code;
        $this->temp_filter_branch_name = $this->filter_branch_name;
        $this->isFilterModalOpen = true;
    }

    public function closeFilterModal()
    {
        $this->isFilterModalOpen = false;
    }

    public function applyFilters()
    {
        $this->filter_region_code = $this->temp_filter_region_code;
        $this->filter_area_code = $this->temp_filter_area_code;
        $this->filter_branch_name = $this->temp_filter_branch_name;
        $this->resetPage();
        $this->isFilterModalOpen = false;
    }

    public function resetFilters()
    {
        $this->temp_filter_region_code = '';
        $this->temp_filter_area_code = '';
        $this->temp_filter_branch_name = '';
        
        $this->filter_region_code = '';
        $this->filter_area_code = '';
        $this->filter_branch_name = '';
        
        $this->filter_finance_status = '';
        $this->filter_check_spm = '';
        $this->filter_status_data = '';
        $this->filter_ktp = '';
        $this->filter_rekening = '';
        $this->filter_geotag = '';
        $this->resetPage();
    }

    public function openExportModal()
    {
        $this->export_foto_ktp = false;
        $this->export_foto_toko = false;
        $this->export_foto_toko2 = false;
        $this->export_foto_toko3 = false;
        $this->isExportModalOpen = true;
    }

    public function closeExportModal()
    {
        $this->isExportModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_export');

        $filters = [
            'search' => $this->search,
            'filter_region_code' => $this->filter_region_code,
            'filter_area_code' => $this->filter_area_code,
            'filter_branch_name' => $this->filter_branch_name,
            'filter_finance_status' => $this->filter_finance_status,
            'export_foto_ktp' => $this->export_foto_ktp,
            'export_foto_toko' => $this->export_foto_toko,
            'export_foto_toko2' => $this->export_foto_toko2,
            'export_foto_toko3' => $this->export_foto_toko3,
        ];
        
        $this->isExportModalOpen = false;
        
        \App\Helpers\ActivityLogger::log('Export RWO', "Mengekspor data RWO.");
        $filename = 'reward_outlet_export_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new RewardOutletExport($filters), $filename);
    }

    public function openImportModal()
    {
        $this->importFile = null;
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
    }

    public function downloadTemplate()
    {
        $filename = 'template_import_rwo_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new \App\Exports\RewardOutletTemplateExport, $filename);
    }

    public function import()
    {
        $this->authorizeAction('can_import');

        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB Max
        ]);

        try {
            $importer = new RewardOutletImport();
            Excel::import($importer, $this->importFile->getRealPath());

            \App\Helpers\ActivityLogger::log('Import RWO', "Mengimpor {$importer->importedCount} data RWO secara massal.");

            $this->dispatch('notify', type: 'success', message: "Berhasil mengimpor {$importer->importedCount} data RWO. (Lewat: {$importer->skippedCount} baris).");
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Gagal mengimpor file: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
    }

    public function syncTikorPareto()
    {
        try {
            $updatedRows = \Illuminate\Support\Facades\DB::select("
                UPDATE reward_outlet AS ro
                SET 
                    latitude = l.latitude,
                    longitude = l.longitude
                FROM list_toko_pareto_team_elite AS l
                WHERE ro.customer_code = l.uniq_kd
                  AND l.latitude IS NOT NULL
                  AND l.longitude IS NOT NULL
                  AND l.latitude::text != ''
                  AND l.longitude::text != ''
                  AND l.latitude::numeric != 0
                  AND l.longitude::numeric != 0
                  AND (ro.latitude IS DISTINCT FROM l.latitude::text OR ro.longitude IS DISTINCT FROM l.longitude::text)
                RETURNING ro.customer_code, ro.customer_name, ro.latitude, ro.longitude
            ");
            
            $count = count($updatedRows);

            if ($count > 0) {
                $logContent = "LOG SYNC TIKOR PARETO\n";
                $logContent .= "Tanggal: " . now()->format('Y-m-d H:i:s') . "\n";
                $logContent .= "Total Diupdate: " . $count . " Outlet\n";
                $logContent .= str_repeat("-", 80) . "\n";
                $logContent .= str_pad("KODE CUSTOMER", 15) . " | " . str_pad("NAMA TOKO", 30) . " | " . str_pad("LATITUDE", 15) . " | " . "LONGITUDE\n";
                $logContent .= str_repeat("-", 80) . "\n";
                
                foreach ($updatedRows as $row) {
                    $logContent .= str_pad($row->customer_code, 15) . " | " . str_pad(substr($row->customer_name ?? '-', 0, 30), 30) . " | " . str_pad($row->latitude, 15) . " | " . $row->longitude . "\n";
                }
                
                $filename = 'sync_tikor_pareto_' . now()->format('Ymd_His') . '.txt';
                
                $this->dispatch('show-toast', ['type' => 'success', 'message' => $count . ' Titik Koordinat berhasil disinkronisasi dari Pareto!']);
                
                return response()->streamDownload(function () use ($logContent) {
                    echo $logContent;
                }, $filename);
            } else {
                $this->dispatch('show-toast', ['type' => 'info', 'message' => 'Tidak ada koordinat baru yang perlu disinkronisasi.']);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Gagal sync koordinat: ' . $e->getMessage()]);
        }
    }


    public function render()
    {
        $query = RewardOutlet::query();
        
        // 1. Hierarchy Access via Trait
        $this->applyHierarchyAccess($query);
        
        // 2. Filters
        if ($this->filter_region_code) {
            $query->where('region_code', $this->filter_region_code);
        }
        if ($this->filter_area_code) {
            $query->where('area_code', $this->filter_area_code);
        }
        if ($this->filter_branch_name) {
            $query->where('branch_name', $this->filter_branch_name);
        }
        
        // 3. Search
        $query->search($this->search);

        // 4. Finance Status Filter
        if ($this->filter_finance_status === 'belum') {
            $query->whereNull('finance_note')->whereNull('finalized_at');
        } elseif ($this->filter_finance_status === 'revisi') {
            $query->whereNotNull('finance_note')->whereNull('finalized_at');
        } elseif ($this->filter_finance_status === 'final') {
            $query->whereNotNull('finalized_at');
        }

        // 5. Check SPM Filter (menggunakan is_valid)
        if ($this->filter_check_spm === 'sudah') {
            $query->where('is_valid', true);
        } elseif ($this->filter_check_spm === 'belum') {
            $query->where(fn($q) => $q->where('is_valid', false)->orWhereNull('is_valid'));
        }

        // 6. Status Data Filter (menggunakan scope lengkap / belum lengkap)
        if ($this->filter_status_data === 'complete') {
            $query->filterType('lengkap');
        } elseif ($this->filter_status_data === 'not_complete') {
            $query->filterType('belum_lengkap');
        }

        // 7. KTP Filter
        if ($this->filter_ktp === 'lengkap') {
            $query->whereRaw("TRIM(COALESCE(nama_ktp, '')) NOT IN ('', '0')")
                  ->whereRaw("TRIM(COALESCE(nik_ktp, '')) NOT IN ('', '0')")
                  ->whereRaw("TRIM(COALESCE(foto_ktp, '')) NOT IN ('', '0')");
        } elseif ($this->filter_ktp === 'belum_lengkap') {
            $query->where(fn($q) => $q->whereRaw("TRIM(COALESCE(nama_ktp, '')) IN ('', '0')")
                                      ->orWhereRaw("TRIM(COALESCE(nik_ktp, '')) IN ('', '0')")
                                      ->orWhereRaw("TRIM(COALESCE(foto_ktp, '')) IN ('', '0')"));
        }

        // 8. Rekening Filter
        if ($this->filter_rekening === 'lengkap') {
            $query->whereRaw("TRIM(COALESCE(no_rekening, '')) NOT IN ('', '0')")
                  ->whereRaw("TRIM(COALESCE(nama_bank, '')) NOT IN ('', '0')")
                  ->whereRaw("TRIM(COALESCE(nama_pemilik_norek, '')) NOT IN ('', '0')");
        } elseif ($this->filter_rekening === 'belum_lengkap') {
            $query->where(fn($q) => $q->whereRaw("TRIM(COALESCE(no_rekening, '')) IN ('', '0')")
                                      ->orWhereRaw("TRIM(COALESCE(nama_bank, '')) IN ('', '0')")
                                      ->orWhereRaw("TRIM(COALESCE(nama_pemilik_norek, '')) IN ('', '0')"));
        }

        // 9. Geotag Filter
        if ($this->filter_geotag === 'lengkap') {
            $query->whereRaw("TRIM(COALESCE(latitude, '')) NOT IN ('', '0')")
                  ->whereRaw("TRIM(COALESCE(longitude, '')) NOT IN ('', '0')");
        } elseif ($this->filter_geotag === 'belum_lengkap') {
            $query->where(fn($q) => $q->whereRaw("TRIM(COALESCE(latitude, '')) IN ('', '0')")
                                      ->orWhereRaw("TRIM(COALESCE(longitude, '')) IN ('', '0')"));
        }
        
        // Count for KPIs (without search and filter, but with hierarchy and region filters)
        $kpiQuery = RewardOutlet::query();
        $this->applyHierarchyAccess($kpiQuery);
        if ($this->filter_region_code) $kpiQuery->where('region_code', $this->filter_region_code);
        if ($this->filter_area_code) $kpiQuery->where('area_code', $this->filter_area_code);
        if ($this->filter_branch_name) $kpiQuery->where('branch_name', $this->filter_branch_name);
        
        // Specific KPI for Index page via single SQL query
        $kpiData = (clone $kpiQuery)->selectRaw("
            COUNT(*) as total_toko,
            COUNT(*) FILTER (WHERE finalized_at IS NOT NULL) as sudah_finalisasi,
            COUNT(*) FILTER (WHERE nik_ktp IS NULL OR nik_ktp = '') as tanpa_ktp,
            COUNT(*) FILTER (WHERE foto_ktp IS NULL OR foto_ktp = '') as tanpa_foto_ktp,
            COUNT(*) FILTER (WHERE nik_ktp IS NULL OR nik_ktp = '' OR foto_ktp IS NULL OR foto_ktp = '' OR nama_ktp IS NULL OR nama_ktp = '') as tanpa_data_ktp,
            COUNT(*) FILTER (WHERE validasi_rekening = false OR validasi_rekening IS NULL) as tanpa_rekening,
            COUNT(*) FILTER (WHERE no_rekening IS NULL OR no_rekening = '' OR nama_pemilik_norek IS NULL OR nama_pemilik_norek = '' OR nama_bank IS NULL OR nama_bank = '') as tanpa_data_rekening,
            COUNT(*) FILTER (WHERE foto_toko IS NULL OR foto_toko = '') as tanpa_foto_toko,
            COUNT(*) FILTER (WHERE (latitude IS NULL OR latitude = '') OR (longitude IS NULL OR longitude = '')) as tanpa_tikor,
            COUNT(*) FILTER (WHERE is_valid = false OR is_valid IS NULL) as tidak_valid,
            COUNT(*) FILTER (WHERE
                NULLIF(no_hp,'') IS NOT NULL AND
                NULLIF(nama_pemilik_toko,'') IS NOT NULL AND
                NULLIF(nama_ktp,'') IS NOT NULL AND
                NULLIF(nik_ktp,'') IS NOT NULL AND
                NULLIF(foto_ktp,'') IS NOT NULL AND
                NULLIF(no_rekening,'') IS NOT NULL AND
                NULLIF(nama_bank,'') IS NOT NULL AND
                NULLIF(nama_pemilik_norek,'') IS NOT NULL AND
                NULLIF(foto_toko2,'') IS NOT NULL AND
                NULLIF(foto_toko3,'') IS NOT NULL AND
                NULLIF(latitude,'') IS NOT NULL AND
                NULLIF(longitude,'') IS NOT NULL
            ) as total_lengkap
        ")->first();

        $outlets = $query->orderBy('id', 'desc')->paginate(100);

        return view('livewire.rwo.master-customer.index', [
            'outlets' => $outlets,
            'kpis' => [
                'total_toko' => $kpiData->total_toko ?? 0,
                'sudah_finalisasi' => $kpiData->sudah_finalisasi ?? 0,
                'total_lengkap' => $kpiData->total_lengkap ?? 0,
                'total_belum_lengkap' => ($kpiData->total_toko ?? 0) - ($kpiData->total_lengkap ?? 0),
                'tanpa_ktp' => $kpiData->tanpa_ktp ?? 0,
                'tanpa_foto_ktp' => $kpiData->tanpa_foto_ktp ?? 0,
                'tanpa_data_ktp' => $kpiData->tanpa_data_ktp ?? 0,
                'tanpa_rekening' => $kpiData->tanpa_rekening ?? 0,
                'tanpa_data_rekening' => $kpiData->tanpa_data_rekening ?? 0,
                'tanpa_foto_toko' => $kpiData->tanpa_foto_toko ?? 0,
                'tanpa_tikor' => $kpiData->tanpa_tikor ?? 0,
                'tidak_valid' => $kpiData->tidak_valid ?? 0,
            ]
        ])->layout('layouts.app');
    }

    // --- Finance Lock & Review Methods ---

    public function submitFinanceRevisi()
    {
        if (!auth()->user()->hasRole('finance') && !auth()->user()->hasRole('admin')) {
            abort(403);
        }
        
        $outlet = RewardOutlet::findOrFail($this->financeOutletId);
        $outlet->update([
            'finance_by' => auth()->id(),
            'finance_note' => $this->financeNote,
            'finance_noted_at' => now(),
        ]);

        $this->isDetailModalOpen = false;
        $this->dispatch('notify', type: 'warning', message: 'Catatan revisi berhasil disimpan.');
    }

    public function submitFinanceFinalize()
    {
        if (!auth()->user()->hasRole('finance') && !auth()->user()->hasRole('admin')) {
            abort(403);
        }
        
        $outlet = RewardOutlet::findOrFail($this->financeOutletId);
        $outlet->update([
            'finance_by' => auth()->id(),
            'finance_note' => null,
            'finance_noted_at' => now(),
            'finalized_at' => now(),
        ]);

        $this->isDetailModalOpen = false;
        $this->dispatch('notify', type: 'success', message: 'Data berhasil difinalisasi (Terkunci).');
    }

    public function unfinalizeOutlet($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }
        
        $outlet = RewardOutlet::findOrFail($id);
        $outlet->update([
            'finalized_at' => null,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Kunci finalisasi berhasil dibuka.');
    }
}
