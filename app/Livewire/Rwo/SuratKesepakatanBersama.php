<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\EnforcesMenuPermissions;

class SuratKesepakatanBersama extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';

    // Permissions
    public $canEdit = true;
    public $canDelete = true;

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

    public $statusApproval = ''; // '', 'approve', 'reject', 'pending'

    public $kuartals = [];
    public $regions = [];
    public $areas = [];
    public $supervisors = [];
    public $distributors = [];

    public $isEditModalOpen = false;
    public $editId = null;
    public $approvalCustomerCode = '';
    public $approvalKuartal = '';
    public $approvalDistributorCode = '';
    public $approvalCustomerName = '';
    public $approvalStatus = ''; // 'approve' or 'reject'
    public $rejectReason = '';
    public $fotoSkb;
    public $existingFotoSkb = null;
    public $approvalError = '';

    // HO Validation fields
    public $hoIsValid = ''; // 'valid', 'invalid', ''
    public $hoNotes = '';

    // Master data reference & edit properties
    public $masterData = null;
    public $masterId = null;
    public $masterNamaPemilik = '';
    public $masterNoHp = '';
    public $masterNikKtp = '';
    public $masterNamaKtp = '';
    public $masterNamaBank = '';
    public $masterNoRekening = '';
    public $masterPemilikRekening = '';
    public $masterFotoKtp;
    public $existingMasterFotoKtp = null;

    public $isDeleteModalOpen = false;
    public $deleteId = null;
    public $deleteCustomerName = '';
    public $deleteCustomerCode = '';

    public $isPreviewModalOpen = false;
    public $previewPhotoUrl = '';

    public $fileImport;
    public $isImportModalOpen = false;
    public $importResult = null;

    public $canImport = true;
    public $canExport = true;

    public function authorizeAction($action = 'can_edit')
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return;
        }
        
        // Khusus INATM hanya otomatis bisa Edit
        if ($user && $user->hasRole('inatm') && $action === 'can_edit') {
            return;
        }
        
        $routeName = property_exists($this, 'menuRoute') ? $this->menuRoute : null;
        if (!$user || !$user->hasMenuAccess($routeName, $action)) {
            abort(403, "Anda tidak memiliki akses untuk melakukan aksi ini ({$action}).");
        }
    }

    public function mount()
    {
        $user = auth()->user();
        if ($user) {
            $isAdmin = $user->hasRole('admin');
            $this->canEdit = $isAdmin || $user->hasRole('inatm') || $user->hasMenuAccess($this->menuRoute, 'can_edit');
            $this->canDelete = $isAdmin || $user->hasMenuAccess($this->menuRoute, 'can_delete');
            $this->canImport = $isAdmin || $user->hasMenuAccess($this->menuRoute, 'can_import');
            $this->canExport = $isAdmin || $user->hasMenuAccess($this->menuRoute, 'can_export');
        }

        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $this->regions = $regionQuery->get();
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
        
        $this->statusApproval = '';
        $this->search = '';

        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedStatusApproval()
    {
        $this->resetPage();
    }

    private function applyAccessScope($query, $distributorAlias = 'md')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $query->where(function($q) use ($user, $distributorAlias) {
                $q->where('te.team_elite_code', $user->supervisor_code)
                  ->orWhere("$distributorAlias.supervisor_code", $user->supervisor_code);
            });
        } elseif (!empty($user->area_code)) {
            $query->whereIn("$distributorAlias.area_code", (array) $user->area_code);
        } elseif (!empty($user->region_code)) {
            $query->whereIn("$distributorAlias.region_code", (array) $user->region_code);
        }

        return $query;
    }

    public function getBaseQuery()
    {
        // For Surat Kesepakatan Bersama, the primary table is skb.
        $baseQuery = DB::table('surat_kesepakatan_bersama_rwo as skb')
            ->leftJoin('list_potensi_rwo as l', function($join) {
                $join->on('l.customer_code', '=', 'skb.customer_code')
                     ->on('l.distributor_code', '=', 'skb.distributor_code')
                     ->on('l.kuartal', '=', 'skb.kuartal');
            })
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'skb.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code');

        $this->applyAccessScope($baseQuery, 'md');

        if (!empty($this->search)) {
            $baseQuery->where(function ($q) {
                $q->where('l.customer_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('skb.customer_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('md.distributor_name', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->statusApproval === 'approve') {
            $baseQuery->where('skb.is_approved', true);
        } elseif ($this->statusApproval === 'reject') {
            $baseQuery->where('skb.is_approved', false);
        } elseif ($this->statusApproval === 'pending') {
            $baseQuery->whereNull('skb.is_approved');
        }

        if (!empty($this->appliedKuartal)) {
            $baseQuery->where('skb.kuartal', $this->appliedKuartal);
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
            $baseQuery->where('skb.distributor_code', $this->appliedDistributor);
        }

        return $baseQuery;
    }

    public function previewPhoto($url)
    {
        $this->previewPhotoUrl = $url;
        $this->isPreviewModalOpen = true;
    }

    public function closePreview()
    {
        $this->isPreviewModalOpen = false;
        $this->previewPhotoUrl = '';
    }

    public function editData($id)
    {
        $this->authorizeAction('can_edit');
        
        $skb = \App\Models\SuratKesepakatanBersamaRwo::find($id);
        if ($skb) {
            $l = DB::table('list_potensi_rwo')->where('customer_code', $skb->customer_code)
                    ->where('distributor_code', $skb->distributor_code)
                    ->where('kuartal', $skb->kuartal)->first();
                    
            $this->editId = $skb->id;
            $this->approvalCustomerCode = $skb->customer_code;
            $this->approvalDistributorCode = $skb->distributor_code;
            $this->approvalKuartal = $skb->kuartal;
            $this->approvalCustomerName = $l ? $l->customer_name : '';
            
            $this->approvalStatus = $skb->is_approved === true ? 'approve' : ($skb->is_approved === false ? 'reject' : '');
            $this->rejectReason = $skb->reason;
            $this->existingFotoSkb = $skb->foto_skb;
            
            $this->hoIsValid = $skb->ho_is_valid === true ? 'valid' : ($skb->ho_is_valid === false ? 'invalid' : '');
            $this->hoNotes = $skb->ho_notes;
            
            // Fetch master data reference
            $rewardOutlet = \App\Models\RewardOutlet::where('customer_code', $skb->customer_code)->first();
            $this->masterData = $rewardOutlet ? $rewardOutlet->toArray() : null;
            
            if ($rewardOutlet) {
                $this->masterId = $rewardOutlet->id;
                $this->masterNamaPemilik = $rewardOutlet->nama_pemilik_toko ?? '';
                $this->masterNoHp = $rewardOutlet->no_hp ?? '';
                $this->masterNikKtp = $rewardOutlet->nik_ktp ?? '';
                $this->masterNamaKtp = $rewardOutlet->nama_ktp ?? '';
                $this->masterNamaBank = $rewardOutlet->nama_bank ?? '';
                $this->masterNoRekening = $rewardOutlet->no_rekening ?? '';
                $this->masterPemilikRekening = $rewardOutlet->nama_pemilik_norek ?? '';
                $this->existingMasterFotoKtp = $rewardOutlet->foto_ktp ?? null;
            } else {
                $this->masterId = null;
                $this->existingMasterFotoKtp = null;
            }
            
            $this->fotoSkb = null;
            $this->masterFotoKtp = null;
            $this->approvalError = '';
            $this->isEditModalOpen = true;
        }
    }

    public function submitApproval()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'approvalStatus' => 'required|in:approve,reject',
            'fotoSkb' => 'nullable|image|max:2048',
            'masterFotoKtp' => 'nullable|image|max:2048',
            'rejectReason' => 'required_if:approvalStatus,reject|max:500',
            'hoIsValid' => 'nullable|in:valid,invalid',
            'hoNotes' => 'nullable|string|max:1000'
        ], [
            'approvalStatus.required' => 'Pilih status approval (Approve/Reject).',
            'rejectReason.required_if' => 'Alasan wajib diisi jika status Reject.',
            'fotoSkb.image' => 'File harus berupa gambar.',
            'fotoSkb.max' => 'Ukuran gambar maksimal 2MB.'
        ]);

        $this->approvalError = '';

        try {
            $skb = \App\Models\SuratKesepakatanBersamaRwo::findOrFail($this->editId);

            $skb->is_approved = ($this->approvalStatus === 'approve');
            
            if ($this->approvalStatus === 'reject') {
                $skb->reason = $this->rejectReason;
            } else {
                $skb->reason = null;
            }

            if ($this->hoIsValid === 'valid') {
                $skb->ho_is_valid = true;
            } elseif ($this->hoIsValid === 'invalid') {
                $skb->ho_is_valid = false;
            } else {
                $skb->ho_is_valid = null;
            }
            $skb->ho_notes = $this->hoNotes;

            if ($this->fotoSkb) {
                $path = $this->fotoSkb->store('skb_photos', 'public');
                $skb->foto_skb = $path;
            }

            $skb->save();

            // Update Master Data if exists
            if ($this->masterId) {
                $ro = \App\Models\RewardOutlet::find($this->masterId);
                if ($ro) {
                    $ro->nama_pemilik_toko = $this->masterNamaPemilik;
                    $ro->no_hp = $this->masterNoHp;
                    $ro->nik_ktp = $this->masterNikKtp;
                    $ro->nama_ktp = $this->masterNamaKtp;
                    $ro->nama_bank = $this->masterNamaBank;
                    $ro->no_rekening = $this->masterNoRekening;
                    $ro->nama_pemilik_norek = $this->masterPemilikRekening;
                    
                    if ($this->masterFotoKtp) {
                        $ro->foto_ktp = $this->masterFotoKtp->store('ktp_photos', 'public');
                    }
                    
                    $ro->save();
                }
            }

            $this->isEditModalOpen = false;
            $this->dispatch('notify', type: 'success', message: 'Data SKB berhasil diperbarui.');

        } catch (\Exception $e) {
            $this->approvalError = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
    
    public function confirmDelete($id, $customerName, $customerCode)
    {
        $this->authorizeAction('can_delete');
        $this->deleteId = $id;
        $this->deleteCustomerName = $customerName ?: '-';
        $this->deleteCustomerCode = $customerCode;
        $this->isDeleteModalOpen = true;
    }

    public function exportData()
    {
        $this->authorizeAction('can_export');
        
        $query = $this->getBaseQuery()
            ->leftJoin('reward_outlet as ro', 'ro.customer_code', '=', 'skb.customer_code')
            ->select([
                'skb.kuartal',
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'md.supervisor_code',
                'md.distributor_code',
                'md.distributor_name',
                'skb.customer_code',
                'l.customer_name',
                'skb.is_approved',
                'skb.reason',
                'skb.ho_is_valid',
                'skb.ho_notes',
                'ro.nama_pemilik_toko',
                'ro.no_hp',
                'ro.nik_ktp',
                'ro.nama_ktp',
                'ro.nama_bank',
                'ro.no_rekening',
                'ro.nama_pemilik_norek'
            ])
            ->orderBy('md.region_name', 'asc')
            ->orderBy('md.area_name', 'asc')
            ->orderBy('md.distributor_name', 'asc');
        
        $timestamp = now()->format('Ymd_His');
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SuratKesepakatanBersamaExport($query), "SKB_RWO_{$timestamp}.xlsx");
    }

    public function downloadTemplate()
    {
        $this->authorizeAction('can_import');
        // We will just export the current filter as a template
        return $this->exportData();
    }

    public function importData()
    {
        $this->authorizeAction('can_import');
        
        $this->validate([
            'fileImport' => 'required|mimes:xlsx,xls|max:5120'
        ], [
            'fileImport.required' => 'Pilih file Excel terlebih dahulu.',
            'fileImport.mimes' => 'Format file harus berupa .xlsx atau .xls',
            'fileImport.max' => 'Ukuran file maksimal 5MB.'
        ]);

        try {
            $import = new \App\Imports\SuratKesepakatanBersamaImport;
            \Maatwebsite\Excel\Facades\Excel::import($import, $this->fileImport);
            
            $this->importResult = [
                'success' => true,
                'count' => $import->successCount,
                'errors' => $import->errorList,
                'updated' => $import->updatedList,
            ];

            if ($import->successCount > 0 || count($import->updatedList) > 0) {
                $this->dispatch('notify', type: 'success', message: 'Import SKB selesai. Periksa log detail.');
            }
            
            // Do not close modal automatically if there are errors/updates to show
            
        } catch (\Exception $e) {
            $this->importResult = [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    public function resetImport()
    {
        $this->fileImport = null;
        $this->importResult = null;
    }

    public function destroyData()
    {
        $this->authorizeAction('can_delete');
        if ($this->deleteId) {
            try {
                \App\Models\SuratKesepakatanBersamaRwo::where('id', $this->deleteId)->delete();
                $this->isDeleteModalOpen = false;
                $this->dispatch('notify', type: 'success', message: 'Data SKB berhasil dihapus.');
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $baseQuery = $this->getBaseQuery();
        
        // Calculate KPIs based on current filter
        $kpiQuery = clone $baseQuery;
        
        $totalToko = $kpiQuery->count();
        $totalApprove = (clone $baseQuery)->where('skb.is_approved', true)->count();
        $totalReject = (clone $baseQuery)->where('skb.is_approved', false)->count();

        $kpiData = [
            'total_toko' => $totalToko,
            'total_approve' => $totalApprove,
            'total_reject' => $totalReject,
        ];

        $query = $baseQuery->select([
            'skb.id',
            'skb.kuartal',
            'skb.customer_code',
            'skb.distributor_code',
            'skb.is_approved',
            'skb.reason',
            'skb.ho_is_valid',
            'skb.ho_notes',
            'skb.foto_skb',
            'l.customer_name',
            'md.distributor_name',
            'md.region_name',
            'md.area_name',
            'md.supervisor_code',
        ])
        ->orderBy('skb.created_at', 'desc');

        $data = $query->paginate(100);

        return view('livewire.rwo.surat-kesepakatan-bersama', [
            'data' => $data,
            'kpi' => $kpiData
        ])->layout('layouts.app');
    }
}
