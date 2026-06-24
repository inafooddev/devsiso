<?php

namespace App\Livewire\Others\Perbaikantikor;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\PerbaikanTikorToko;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    public $keteranganReject = '';
    public $selectedId = null;
    public $selectedIds = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
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

    public function render()
    {
        $query = PerbaikanTikorToko::query()
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

        $query->orderByRaw("
            CASE 
                WHEN status = 'Pending' THEN 1 
                WHEN status = 'Rejected' THEN 2 
                WHEN status = 'Approved' THEN 3 
                ELSE 4 
            END
        ")->orderBy('created_at', 'desc');

        $data = $query->paginate(30);

        $kpi = [
            'total' => PerbaikanTikorToko::count(),
            'pending' => PerbaikanTikorToko::where('status', 'Pending')->count(),
            'approved' => PerbaikanTikorToko::where('status', 'Approved')->count(),
            'rejected' => PerbaikanTikorToko::where('status', 'Rejected')->count(),
        ];

        return view('livewire.others.perbaikantikor.index', [
            'data' => $data,
            'kpi' => $kpi
        ])->layout('layouts.app');
    }
}
