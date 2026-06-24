<?php

namespace App\Livewire\Others\Perbaikantikor;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\PerbaikanTikorToko;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    #[Url]
    public $dateStart = '';

    #[Url]
    public $dateEnd = '';

    public $keteranganReject = '';
    public $selectedId = null;
    public $selectedIds = [];

    public $selectAll = false;

    #[Computed]
    public function selectedPendingCount()
    {
        if (empty($this->selectedIds)) return 0;
        return PerbaikanTikorToko::whereIn('id', $this->selectedIds)->where('status', 'Pending')->count();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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

    public function updatingSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->filteredQuery->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatingSelectedIds()
    {
        $this->selectAll = false;
    }

    public function approve($id)
    {
        $record = PerbaikanTikorToko::find($id);
        if ($record && $record->status == 'Pending') {
            $record->status = 'Approved';
            $record->keterangan = null;
            $record->save();
            $this->dispatch('show-toast', type: 'success', message: 'Tikor Toko berhasil disetujui');
        }
    }

    public function bulkApprove()
    {
        if (empty($this->selectedIds)) return;
        
        $count = PerbaikanTikorToko::whereIn('id', $this->selectedIds)
            ->where('status', 'Pending')
            ->update([
                'status' => 'Approved',
                'keterangan' => null
            ]);
            
        if ($count > 0) {
            $this->dispatch('show-toast', type: 'success', message: $count . ' Tikor Toko berhasil disetujui secara massal');
        }
        $this->selectedIds = [];
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PerbaikanTikorExport(
                $this->search, 
                $this->statusFilter, 
                $this->dateStart, 
                $this->dateEnd, 
                $this->selectedIds
            ), 
            'perbaikan_tikor_toko_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function promptReject($id)
    {
        $this->selectedId = $id;
        $this->keteranganReject = '';
        $this->dispatch('open-reject-modal');
    }

    public function reject()
    {
        $this->validate([
            'keteranganReject' => 'required|min:5'
        ], [
            'keteranganReject.required' => 'Keterangan penolakan wajib diisi',
            'keteranganReject.min' => 'Keterangan terlalu singkat'
        ]);

        $record = PerbaikanTikorToko::find($this->selectedId);
        if ($record) {
            $record->status = 'Rejected';
            $record->keterangan = $this->keteranganReject;
            $record->save();
            $this->dispatch('close-reject-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Tikor Toko berhasil ditolak');
        }
    }

    private function baseQuery()
    {
        $query = PerbaikanTikorToko::query();
        
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', is_array($user->region_code) ? $user->region_code : [$user->region_code]);
        }

        return $query;
    }

    #[Computed]
    public function filteredQuery()
    {
        $query = $this->baseQuery()
            ->with(['customerPrcEska', 'distributorImplementasiEskalink']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('customer_code', 'like', '%' . $this->search . '%')
                  ->orWhere('distributor_code', 'like', '%' . $this->search . '%')
                  ->orWhere('sales_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customerPrcEska', function($q2) {
                      $q2->where('custname', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter && $this->statusFilter !== 'Semua Kategori' && $this->statusFilter !== 'Semua Status') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->dateStart) {
            $query->whereDate('created_at', '>=', $this->dateStart);
        }

        if ($this->dateEnd) {
            $query->whereDate('created_at', '<=', $this->dateEnd);
        }

        $query->orderByRaw("
            CASE 
                WHEN status = 'Pending' THEN 1 
                WHEN status = 'Rejected' THEN 2 
                WHEN status = 'Approved' THEN 3 
                ELSE 4 
            END
        ")->orderBy('created_at', 'desc');

        return $query;
    }

    public function render()
    {
        $query = clone $this->filteredQuery;
        
        $data = $query->paginate(100);

        $kpi = [
            'total' => $this->baseQuery()->count(),
            'pending' => $this->baseQuery()->where('status', 'Pending')->count(),
            'approved' => $this->baseQuery()->where('status', 'Approved')->count(),
            'rejected' => $this->baseQuery()->where('status', 'Rejected')->count(),
        ];

        $duplicates = PerbaikanTikorToko::selectRaw('distributor_code, customer_code')
            ->groupBy('distributor_code', 'customer_code')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->map(function ($item) {
                return $item->distributor_code . '_' . $item->customer_code;
            })
            ->toArray();

        return view('livewire.others.perbaikantikor.index', [
            'data' => $data,
            'kpi' => $kpi,
            'duplicates' => $duplicates
        ])->layout('layouts.app');
    }
}
