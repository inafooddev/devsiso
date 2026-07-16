<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ListPotensiRwoImport;
use Illuminate\Support\Facades\Storage;
use App\Traits\EnforcesMenuPermissions;

class Listpotensirwo extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';

    // Permissions
    public $canAdd = true;
    public $canEdit = true;
    public $canDelete = true;
    public $canImport = true;
    public $canExport = true;

    public $search = '';

    public $kuartal = '';
    public $region = '';
    public $area = '';
    public $supervisor = '';
    public $distributor = '';

    public $appliedKuartal = '';
    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSupervisor = '';
    public $appliedDistributor = '';

    public $statusSkb = '';

    public $kuartals = [];
    public $regions = [];
    public $areas = [];
    public $supervisors = [];
    public $distributors = [];

    public $isImportModalOpen = false;
    public $importFile;

    public $isDeleteModalOpen = false;
    public $deleteCustomerCode = '';
    public $deleteKuartal = '';
    public $deleteCustomerName = '';

    public $isEditModalOpen = false;
    public $editCustomerCode = '';
    public $editKuartal = '';
    public $editCustomerName = '';
    public $editAlamat = '';
    public $editTotalTarget = 0;
    public $editDistributorName = '';

    public $isApprovalModalOpen = false;
    public $approvalCustomerCode = '';
    public $approvalKuartal = '';
    public $approvalDistributorCode = '';
    public $approvalCustomerName = '';
    public $approvalStatus = ''; // 'approve' or 'reject'
    public $rejectReason = '';
    public $fotoSkb;
    public $existingFotoSkb = null;
    public $approvalError = '';

    public $filterTargetMin = null;
    public $filterTargetMax = null;
    
    // State for Status Data Modal
    public $isStatusModalOpen = false;
    public $activeStatusData = null;

    public $isDetailModalOpen = false;
    public $detailData = [];

    public function mount()
    {
        $user = auth()->user();
        if ($user) {
            $this->canAdd = $user->hasMenuAccess($this->menuRoute, 'can_add');
            $this->canEdit = $user->hasMenuAccess($this->menuRoute, 'can_edit');
            $this->canDelete = $user->hasMenuAccess($this->menuRoute, 'can_delete');
            $this->canImport = $user->hasMenuAccess($this->menuRoute, 'can_import');
            $this->canExport = $user->hasMenuAccess($this->menuRoute, 'can_export');
        }

        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $this->regions = $regionQuery->get();

        $currentMonth = (int) date('n');
        $currentQuarter = (string) ceil($currentMonth / 3);
        $this->kuartal = $currentQuarter;
        $this->appliedKuartal = $currentQuarter;
    }

    public function updatedKuartal($value)
    {
        $this->resetPage();
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->areas = empty($value) ? [] : DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name')
            ->get();
            
        $this->supervisors = [];
        $this->distributors = [];
        $this->resetPage();
    }

    public function updatedArea($value)
    {
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->supervisors = empty($value) ? [] : DB::table('master_distributors')
            ->where('area_code', $value)
            ->select('supervisor_code', 'supervisor_name')
            ->whereNotNull('supervisor_code')
            ->where('supervisor_code', '!=', '')
            ->distinct()
            ->orderBy('supervisor_name')
            ->get();
            
        $this->distributors = [];
        $this->resetPage();
    }

    public function updatedSupervisor($value)
    {
        $this->distributor = '';
        
        $this->distributors = empty($value) ? [] : DB::table('master_distributors')
            ->where('supervisor_code', $value)
            ->when($this->area, function ($q) {
                return $q->where('area_code', $this->area);
            })
            ->select('distributor_code', 'distributor_name')
            ->orderBy('distributor_name')
            ->get();
            
        $this->resetPage();
    }

    public function updatedDistributor($value)
    {
        // Don't reset page here anymore, done in applyFilter
    }

    public function applyFilter()
    {
        $this->appliedKuartal = $this->kuartal;
        $this->appliedRegion = $this->region;
        $this->appliedArea = $this->area;
        $this->appliedSupervisor = $this->supervisor;
        $this->appliedDistributor = $this->distributor;
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->kuartal = '';
        $this->region = '';
        $this->area = '';
        $this->supervisor = '';
        $this->distributor = '';
        
        $this->areas = [];
        $this->supervisors = [];
        $this->distributors = [];

        $this->appliedKuartal = '';
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedSupervisor = '';
        $this->appliedDistributor = '';
        
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusSkb()
    {
        $this->resetPage();
    }

    public function openImportModal()
    {
        $this->reset('importFile');
        $this->isImportModalOpen = true;
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ListPotensiRwoTemplateExport(), 'Template_Import_Potensi_RWO_' . date('Ymd_His') . '.xlsx');
    }

    public function importData()
    {
        $this->authorizeAction('can_import');
        
        $this->validate(['importFile' => 'required|mimes:xlsx,xls,csv|max:10240']);

        try {
            $import = new ListPotensiRwoImport();
            Excel::import($import, $this->importFile);

            $fileName = 'Log_Import_Potensi_RWO_' . date('Ymd_His') . '.txt';
            $content = "=== LOG IMPORT POTENSI RWO ===\n";
            $content .= "Waktu Import: " . now()->format('Y-m-d H:i:s') . "\n\n";

            $message = "Berhasil mengimpor " . $import->successCount . " data toko baru.";

            if (count($import->updatedList) > 0 || count($import->errorList) > 0) {
                if (count($import->errorList) > 0) {
                    $content .= "=== DAFTAR TOKO GAGAL/ERROR ===\n";
                    $content .= "Total: " . count($import->errorList) . " toko\n";
                    foreach ($import->errorList as $err) {
                        $content .= "{$err}\n";
                    }
                    $content .= "\n";
                }

                if (count($import->updatedList) > 0) {
                    $content .= "=== DAFTAR TOKO UPDATE (Sudah ada, diperbarui) ===\n";
                    $content .= "Total: " . count($import->updatedList) . " toko\n";
                    foreach ($import->updatedList as $upd) {
                        $content .= "{$upd}\n";
                    }
                }
                $message .= " Terdapat " . count($import->updatedList) . " data diperbarui dan " . count($import->errorList) . " data error.";
            } else {
                $content .= "=== STATUS: SUKSES 100% ===\n";
                $content .= "Semua data berhasil diimpor tanpa ada error atau update.\n";
                $content .= "Total data masuk: {$import->successCount} toko.\n";
            }

            // Instead of saving to disk, pass content to frontend to generate blob
            $this->importFile = null;
            $this->isImportModalOpen = false;
            $this->dispatch('close-import-modal');
            $this->dispatch('download-log', content: $content, filename: $fileName);
            $this->dispatch('notify', type: 'success', message: 'Proses import selesai.');

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function getBaseQuery()
    {
        $baseQuery = DB::table('list_potensi_rwo as l')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'te.team_elite_code', '=', 'f.SLSNO')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            });

        $this->applyAccessScope($baseQuery, 'md');

        if (!empty($this->search)) {
            $baseQuery->where(function ($q) {
                $q->where('l.customer_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('l.customer_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('md.distributor_name', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->statusSkb === 'Sudah') {
            $baseQuery->whereNotNull('skb.customer_code');
        } elseif ($this->statusSkb === 'Belum') {
            $baseQuery->whereNull('skb.customer_code');
        }

        if (!empty($this->appliedKuartal)) {
            $baseQuery->where('l.kuartal', $this->appliedKuartal);
        }

        if (!empty($this->appliedRegion)) {
            $baseQuery->where('md.region_code', $this->appliedRegion);
        }
        if (!empty($this->appliedArea)) {
            $baseQuery->where('md.area_code', $this->appliedArea);
        }
        if (!empty($this->appliedSupervisor)) {
            $baseQuery->where('md.supervisor_code', $this->appliedSupervisor);
        }
        if (!empty($this->appliedDistributor)) {
            $baseQuery->where('md.distributor_code', $this->appliedDistributor);
        }

        return $baseQuery;
    }

    /**
     * Filter Query berdasarkan hak akses (Regional, Area, Supervisor) user.
     */
    private function applyAccessScope($query, $distributorAlias = 'md')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $query->where("$distributorAlias.supervisor_code", $user->supervisor_code);
        } elseif (!empty($user->area_code)) {
            $query->whereIn("$distributorAlias.area_code", (array) $user->area_code);
        } elseif (!empty($user->region_code)) {
            $query->whereIn("$distributorAlias.region_code", (array) $user->region_code);
        }

        return $query;
    }

    public function exportData()
    {
        $this->authorizeAction('can_export');
        
        $query = $this->getBaseQuery()->select([
            'l.kuartal',
            'md.region_code',
            'md.region_name',
            'md.area_code',
            'md.area_name',
            'te.team_elite_code as supervisor_code',
            'f.SLSNAME as supervisor_name',
            'md.distributor_code',
            'md.distributor_name',
            'r.eskalink_code as customer_prc',
            'l.customer_code',
            'l.customer_name',
            'l.alamat',
            'l.total_target',
            DB::raw("CASE
                WHEN l.total_target >= 90000000 THEN 0.025
                WHEN l.total_target >= 30000000 THEN 0.020
                ELSE 0.015
            END AS reward_percent"),
            DB::raw("CASE
                WHEN l.total_target >= 90000000 THEN 'RSM'
                WHEN l.total_target >= 30000000 THEN 'ASM'
                ELSE 'SPV'
            END AS pic"),
            DB::raw("CASE
                WHEN skb.customer_code IS NOT NULL THEN 'Sudah'
                ELSE 'Belum'
            END AS status_skb"),
            'skb.is_approved'
        ])
        ->orderBy('md.region_name', 'asc')
        ->orderBy('md.area_name', 'asc')
        ->orderBy('f.SLSNAME', 'asc')
        ->orderBy('md.distributor_name', 'asc');
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ListPotensiRwoExport($query), 'List_Potensi_RWO.xlsx');
    }

    public function showDetail($customerCode, $kuartal)
    {
        $item = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'te.team_elite_code', '=', 'f.SLSNO')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->where('l.customer_code', $customerCode)
            ->where('l.kuartal', $kuartal)
            ->select(
                'l.*',
                'md.distributor_name', 'md.region_name', 'md.area_name',
                'f.SLSNAME as supervisor_name',
                'r.eskalink_code as customer_prc',
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3',
                DB::raw("CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END AS status_skb"),
                'skb.is_approved',
                'skb.foto_skb'
            )
            ->first();

        if ($item) {
            $this->detailData = (array) $item;
            $this->isDetailModalOpen = true;
        }
    }

    public function showStatusData($customerCode)
    {
        $item = DB::table('reward_outlet as r')
            ->where('r.customer_code', $customerCode)
            ->first();
            
        if ($item) {
            $this->activeStatusData = (array) $item;
            $this->isStatusModalOpen = true;
        } else {
            // Handle if not found in reward_outlet (should not happen if joined, but just in case)
            $this->activeStatusData = [];
            $this->isStatusModalOpen = true;
        }
    }

    public function editData($customerCode, $kuartal)
    {
        $item = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->where('l.customer_code', $customerCode)
            ->where('l.kuartal', $kuartal)
            ->select('l.*', 'md.distributor_name')
            ->first();

        if ($item) {
            $this->resetValidation();
            $this->editCustomerCode = $item->customer_code;
            $this->editKuartal = $item->kuartal;
            $this->editCustomerName = $item->customer_name;
            $this->editAlamat = $item->alamat;
            $this->editTotalTarget = $item->total_target;
            $this->editDistributorName = $item->distributor_name;
            
            $this->isEditModalOpen = true;
        }
    }

    public function updateData()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'editCustomerName' => 'required|string|max:255',
            'editAlamat' => 'nullable|string',
            'editTotalTarget' => 'required|numeric|min:0',
        ]);

        DB::table('list_potensi_rwo')
            ->where('customer_code', $this->editCustomerCode)
            ->where('kuartal', $this->editKuartal)
            ->update([
                'customer_name' => $this->editCustomerName,
                'alamat' => $this->editAlamat,
                'total_target' => $this->editTotalTarget,
                'updated_at' => now(),
            ]);

        $this->isEditModalOpen = false;
        $this->dispatch('notify', type: 'success', message: 'Data toko berhasil diperbarui.');
    }

    public function deleteData($customerCode, $kuartal)
    {
        $this->deleteCustomerCode = $customerCode;
        $this->deleteKuartal = $kuartal;
        
        $item = DB::table('list_potensi_rwo')
            ->where('customer_code', $customerCode)
            ->where('kuartal', $kuartal)
            ->first();
            
        if ($item) {
            $this->deleteCustomerName = $item->customer_name;
            $this->isDeleteModalOpen = true;
        }
    }

    public function destroyData()
    {
        $this->authorizeAction('can_delete');
        
        if ($this->deleteCustomerCode && $this->deleteKuartal) {
            DB::table('list_potensi_rwo')
                ->where('customer_code', $this->deleteCustomerCode)
                ->where('kuartal', $this->deleteKuartal)
                ->delete();

            $this->isDeleteModalOpen = false;
            $this->reset(['deleteCustomerCode', 'deleteKuartal', 'deleteCustomerName']);
            $this->dispatch('notify', type: 'success', message: 'Data berhasil dihapus.');
        } else {
            $this->isDeleteModalOpen = false;
            $this->dispatch('notify', type: 'error', message: 'Gagal menghapus. Data tidak ditemukan.');
        }
    }

    public function openApprovalModal($customerCode, $kuartal, $distributorCode, $customerName)
    {
        // Require can_edit permission for approval? Let's use canEdit for now, or assume if they see it they can.
        // Actually, we'll guard in submitApproval.
        $this->resetValidation();
        $this->approvalCustomerCode = $customerCode;
        $this->approvalKuartal = $kuartal;
        $this->approvalDistributorCode = $distributorCode;
        $this->approvalCustomerName = $customerName;
        
        // Fetch existing SKB data if exists
        $existing = \App\Models\SuratKesepakatanBersamaRwo::where('customer_code', $customerCode)
                        ->where('distributor_code', $distributorCode)
                        ->where('kuartal', $kuartal === '' ? null : $kuartal)
                        ->first();
                        
        if ($existing) {
            $this->approvalStatus = $existing->is_approved === true ? 'approve' : ($existing->is_approved === false ? 'reject' : '');
            $this->rejectReason = $existing->reason;
            $this->existingFotoSkb = $existing->foto_skb;
        } else {
            $this->approvalStatus = '';
            $this->rejectReason = '';
            $this->existingFotoSkb = null;
        }
        
        $this->fotoSkb = null;
        $this->approvalError = '';
        $this->isApprovalModalOpen = true;
    }

    public function submitApproval()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'approvalStatus' => 'required|in:approve,reject',
            'fotoSkb' => 'nullable|image|max:2048', // optional if already exists, but let's make it optional and handle it
            'rejectReason' => 'required_if:approvalStatus,reject|max:500'
        ], [
            'approvalStatus.required' => 'Pilih status approval (Approve/Reject).',
            'rejectReason.required_if' => 'Alasan wajib diisi jika status Reject.',
            'fotoSkb.image' => 'File harus berupa gambar.',
            'fotoSkb.max' => 'Ukuran gambar maksimal 2MB.'
        ]);

        $this->approvalError = '';

        try {
            $skb = \App\Models\SuratKesepakatanBersamaRwo::firstOrNew([
                'customer_code' => $this->approvalCustomerCode,
                'distributor_code' => $this->approvalDistributorCode,
                'kuartal' => $this->approvalKuartal === '' ? null : $this->approvalKuartal,
            ]);

            $skb->is_approved = ($this->approvalStatus === 'approve');
            
            if ($this->approvalStatus === 'reject') {
                $skb->reason = $this->rejectReason;
            } else {
                $skb->reason = null;
            }

            if ($this->fotoSkb) {
                $path = $this->fotoSkb->store('skb_photos', 'public');
                $skb->foto_skb = $path;
            } elseif (!$skb->exists) {
                // If it's a new record and no photo uploaded, we should probably require it, 
                // but based on instruction "inputannya nanti dari tabel list , lalu foto lalu approve sama reject"
                // Let's enforce photo if it's new.
                $this->validate(['fotoSkb' => 'required|image|max:2048'], ['fotoSkb.required' => 'Foto SKB wajib diunggah.']);
                $path = $this->fotoSkb->store('skb_photos', 'public');
                $skb->foto_skb = $path;
            }

            $skb->save();

            $this->isApprovalModalOpen = false;
            $this->dispatch('notify', type: 'success', message: 'Data approval berhasil disimpan.');

        } catch (\Exception $e) {
            $this->approvalError = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $baseQuery = $this->getBaseQuery();

        $kpiQuery = clone $baseQuery;
        $kpi = $kpiQuery->select(
            DB::raw('COUNT(*) as total_toko'),
            DB::raw('SUM(l.total_target) as total_target'),
            DB::raw('COUNT(skb.customer_code) as sudah_skb')
        )->first();

        $kpiData = [
            'total_toko' => $kpi->total_toko ?? 0,
            'total_target' => $kpi->total_target ?? 0,
            'sudah_skb' => $kpi->sudah_skb ?? 0,
            'belum_skb' => ($kpi->total_toko ?? 0) - ($kpi->sudah_skb ?? 0),
        ];

        $query = $baseQuery->select([
            'l.kuartal',
            'md.region_code',
            'md.region_name',
            'md.area_code',
            'md.area_name',
            'te.team_elite_code as supervisor_code',
            'f.SLSNAME as supervisor_name',
            'md.distributor_code',
            'md.distributor_name',
            'r.eskalink_code as customer_prc',
            'l.customer_code',
            'l.customer_name',
            'l.alamat',
            'l.total_target',
            DB::raw("CASE
                WHEN l.total_target >= 90000000 THEN 0.025
                WHEN l.total_target >= 30000000 THEN 0.020
                ELSE 0.015
            END AS reward_percent"),
            DB::raw("CASE
                WHEN l.total_target >= 90000000 THEN 'RSM'
                WHEN l.total_target >= 30000000 THEN 'ASM'
                ELSE 'SPV'
            END AS pic"),
            DB::raw("CASE
                WHEN skb.customer_code IS NOT NULL THEN 'Sudah'
                ELSE 'Belum'
            END AS status_skb"),
            'skb.is_approved',
            'r.no_hp',
            'r.nama_pemilik_toko',
            'r.nik_ktp',
            'r.nama_ktp',
            'r.foto_ktp',
            'r.nama_bank',
            'r.no_rekening',
            'r.nama_pemilik_norek',
            'r.latitude',
            'r.longitude',
            'r.foto_toko2',
            'r.foto_toko3',
            DB::raw("CASE WHEN 
                NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap")
        ])
        ->orderBy('md.region_name', 'asc')
        ->orderBy('md.area_name', 'asc')
        ->orderBy('f.SLSNAME', 'asc')
        ->orderBy('md.distributor_name', 'asc');

        $data = $query->paginate(100);

        return view('livewire.rwo.listpotensirwo', [
            'data' => $data,
            'kpi' => $kpiData
        ])->layout('layouts.app');
    }
}
