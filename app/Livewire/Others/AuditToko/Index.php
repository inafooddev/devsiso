<?php

namespace App\Livewire\Others\AuditToko;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    #[Url]
    public $selectedRegion = '';

    #[Url]
    public $selectedArea = '';

    #[Url]
    public $selectedDistributor = '';

    public $perPage = 15;
    public $selectedId = null;
    public $catatanManager = '';

    // Export Modal Filter properties
    public $dateStart = '';
    public $dateEnd = '';
    public $exportDateStart = '';
    public $exportDateEnd = '';
    public $exportStatusFilter = '';
    public $exportDistributors = [];
    public $selectAllExportDistributors = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSelectedRegion()
    {
        $this->selectedArea = '';
        $this->selectedDistributor = '';
        $this->resetPage();
    }

    public function updatingSelectedArea()
    {
        $this->selectedDistributor = '';
        $this->resetPage();
    }

    public function updatingSelectedDistributor()
    {
        $this->resetPage();
    }

    public function updatingDateStart()
    {
        $this->resetPage();
    }

    public function updatingDateEnd()
    {
        $this->resetPage();
    }

    public function openExportModal()
    {
        $this->exportDateStart = $this->dateStart;
        $this->exportDateEnd = $this->dateEnd;
        $this->exportStatusFilter = $this->statusFilter;
        $this->exportDistributors = !empty($this->selectedDistributor) ? [$this->selectedDistributor] : [];
        $this->selectAllExportDistributors = false;
        
        $this->dispatch('open-export-modal');
    }

    public function updatedSelectAllExportDistributors($value)
    {
        $allDistributors = DB::table('master_distributors')
            ->when(!empty($this->selectedRegion), fn($q) => $q->where('region_name', $this->selectedRegion))
            ->when(!empty($this->selectedArea), fn($q) => $q->where('area_name', $this->selectedArea))
            ->distinct()->pluck('distributor_name')->filter()->sort()->values()->toArray();

        if ($value) {
            $this->exportDistributors = $allDistributors;
        } else {
            $this->exportDistributors = [];
        }
    }

    public function getFilteredQueryProperty()
    {
        $user = Auth::user();
        $userRegionCodes = !empty($user->region_code) ? (array) $user->region_code : [];
        $userAreaCodes = !empty($user->area_code) ? (array) $user->area_code : [];

        $query = DB::table('hasil_audit_toko as hat')
            ->selectRaw('
                hat.id,
                hat.customer_code,
                hat.customer_name,
                hat.customer_address,
                hat.distributor_code,
                md.distributor_name,
                md.branch_name AS cabang,
                md.region_name,
                md.area_name,
                hat.auditor,
                hat.status_approval,
                hat.alasan_reject,
                hat.approved_by,
                hat.approved_at,
                hat.keterangan_hasil_audit,
                hat.is_toko_fisik,
                hat.is_nama_pemilik,
                hat.is_nama_ktp,
                hat.is_nik_ktp,
                hat.is_no_hp,
                hat.is_no_rekening,
                hat.is_an_rekening,
                hat.is_titik_koordinat,
                hat.foto_audit1,
                hat.foto_audit2,
                hat.foto_audit3,
                hat.foto_audit4,
                hat.foto_audit5,
                hat.foto_audit6,
                hat.foto_audit7,
                hat.foto_audit8,
                hat.latitude AS audit_latitude,
                hat.longitude AS audit_longitude,
                l.latitude AS master_latitude,
                l.longitude AS master_longitude,
                hat.created_at
            ')
            ->leftJoin('master_distributors as md', 'hat.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('list_outlet_audit as l', 'hat.customer_code', '=', 'l.customer_code');

        if (!empty($userAreaCodes)) {
            $query->whereIn('md.area_code', $userAreaCodes);
        } elseif (!empty($userRegionCodes)) {
            $query->whereIn('md.region_code', $userRegionCodes);
        }

        if (!empty($this->statusFilter)) {
            $query->where('hat.status_approval', $this->statusFilter);
        }

        if (!empty($this->selectedRegion)) {
            $query->where('md.region_name', $this->selectedRegion);
        }

        if (!empty($this->selectedArea)) {
            $query->where('md.area_name', $this->selectedArea);
        }

        if (!empty($this->selectedDistributor)) {
            $query->where('md.distributor_name', $this->selectedDistributor);
        }

        if (!empty($this->dateStart) && !empty($this->dateEnd)) {
            $query->whereBetween('hat.created_at', [$this->dateStart . ' 00:00:00', $this->dateEnd . ' 23:59:59']);
        } elseif (!empty($this->dateStart)) {
            $query->where('hat.created_at', '>=', $this->dateStart . ' 00:00:00');
        } elseif (!empty($this->dateEnd)) {
            $query->where('hat.created_at', '<=', $this->dateEnd . ' 23:59:59');
        }

        if (!empty($this->search)) {
            $q = '%' . trim($this->search) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('hat.customer_name', 'like', $q)
                    ->orWhere('hat.customer_code', 'like', $q)
                    ->orWhere('hat.auditor', 'like', $q)
                    ->orWhere('md.distributor_name', 'like', $q)
                    ->orWhere('md.branch_name', 'like', $q);
            });
        }

        return $query;
    }

    #[Computed]
    public function kpiData()
    {
        $baseQuery = DB::table('hasil_audit_toko as hat')
            ->leftJoin('master_distributors as md', 'hat.distributor_code', '=', 'md.distributor_code');

        $user = Auth::user();
        $userRegionCodes = !empty($user->region_code) ? (array) $user->region_code : [];
        $userAreaCodes = !empty($user->area_code) ? (array) $user->area_code : [];

        if (!empty($userAreaCodes)) {
            $baseQuery->whereIn('md.area_code', $userAreaCodes);
        } elseif (!empty($userRegionCodes)) {
            $baseQuery->whereIn('md.region_code', $userRegionCodes);
        }

        $total = (clone $baseQuery)->count();
        $pending = (clone $baseQuery)->where(function ($q) {
            $q->where('hat.status_approval', 'Pending')
              ->orWhereNull('hat.status_approval');
        })->count();

        $approved = (clone $baseQuery)->where('hat.status_approval', 'Approved')->count();
        $rejected = (clone $baseQuery)->where('hat.status_approval', 'Rejected')->count();
        $rate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        return compact('total', 'pending', 'approved', 'rejected', 'rate');
    }

    public function openApproveModal($id)
    {
        $this->selectedId = $id;
        $this->catatanManager = '';
        $this->dispatch('open-approve-modal');
    }

    public function approve()
    {
        if ($this->selectedId) {
            $audit = DB::table('hasil_audit_toko')->where('id', $this->selectedId)->first();
            if ($audit) {
                DB::table('hasil_audit_toko')->where('id', $this->selectedId)->update([
                    'status_approval' => 'Approved',
                    'alasan_reject' => empty($this->catatanManager) ? null : $this->catatanManager,
                    'approved_by' => Auth::user()->name,
                    'approved_at' => now(),
                ]);
                $this->dispatch('show-toast', type: 'success', message: "Audit toko {$audit->customer_name} berhasil disetujui (Approved).");
            }
        }
        $this->selectedId = null;
        $this->catatanManager = '';
        $this->dispatch('close-approve-modal');
    }

    public function openRejectModal($id)
    {
        $this->selectedId = $id;
        $this->catatanManager = '';
        $this->dispatch('open-reject-modal');
    }

    public function reject()
    {
        $this->validate([
            'catatanManager' => 'required|string|min:3',
        ], [
            'catatanManager.required' => 'Catatan penolakan wajib diisi.',
            'catatanManager.min' => 'Catatan penolakan minimal 3 karakter.',
        ]);

        if ($this->selectedId) {
            $audit = DB::table('hasil_audit_toko')->where('id', $this->selectedId)->first();
            if ($audit) {
                DB::table('hasil_audit_toko')->where('id', $this->selectedId)->update([
                    'status_approval' => 'Rejected',
                    'alasan_reject' => $this->catatanManager,
                    'approved_by' => Auth::user()->name,
                    'approved_at' => now(),
                ]);
                $this->dispatch('show-toast', type: 'warning', message: "Audit toko {$audit->customer_name} ditolak (Rejected).");
            }
        }

        $this->selectedId = null;
        $this->catatanManager = '';
        $this->dispatch('close-reject-modal');
    }

    public function delete($id)
    {
        $audit = DB::table('hasil_audit_toko')->where('id', $id)->first();
        if ($audit) {
            // Delete associated photos
            for ($i = 1; $i <= 8; $i++) {
                $field = "foto_audit{$i}";
                if (!empty($audit->$field) && \Illuminate\Support\Facades\Storage::disk('public')->exists($audit->$field)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($audit->$field);
                }
            }
            // Delete record
            DB::table('hasil_audit_toko')->where('id', $id)->delete();
            $this->dispatch('show-toast', type: 'success', message: "Data audit toko {$audit->customer_name} berhasil dihapus permanen.");
        }
    }

    public function edit($id)
    {
        // Placeholder for edit action
        $this->dispatch('show-toast', type: 'info', message: 'Fitur form edit sedang dalam pengembangan.');
    }

    public function exportExcel()
    {
        return Excel::download(
            new \App\Exports\AuditTokoExport(
                $this->search,
                $this->exportStatusFilter ?: $this->statusFilter,
                $this->selectedRegion,
                $this->selectedArea,
                $this->exportDistributors,
                $this->exportDateStart,
                $this->exportDateEnd
            ),
            'Audit_Toko_Report_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        $reports = $this->filteredQuery
            ->orderBy('hat.created_at', 'desc')
            ->paginate($this->perPage);

        $regions = DB::table('master_distributors')->distinct()->pluck('region_name')->filter()->sort()->values();
        $areas = DB::table('master_distributors')
            ->when(!empty($this->selectedRegion), fn($q) => $q->where('region_name', $this->selectedRegion))
            ->distinct()->pluck('area_name')->filter()->sort()->values();
        $distributors = DB::table('master_distributors')
            ->when(!empty($this->selectedRegion), fn($q) => $q->where('region_name', $this->selectedRegion))
            ->when(!empty($this->selectedArea), fn($q) => $q->where('area_name', $this->selectedArea))
            ->distinct()->pluck('distributor_name')->filter()->sort()->values();

        return view('livewire.others.audittoko.index', [
            'reports' => $reports,
            'regions' => $regions,
            'areas' => $areas,
            'distributors' => $distributors,
        ])->layout('layouts.app');
    }
}
