<?php

namespace App\Livewire\Others\PerbaikantikorElite;

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

            \Illuminate\Support\Facades\DB::table('list_toko_pareto_team_elite')
                ->where('distributor_code', $record->distributor_code)
                ->where('customer_code_prc', $record->customer_code)
                ->update([
                    'latitude' => $record->latitude,
                    'longitude' => $record->longitude,
                ]);

            $this->dispatch('show-toast', type: 'success', message: 'Tikor Toko berhasil disetujui dan list toko pareto diupdate');
        }
    }

    public function bulkApprove()
    {
        if (empty($this->selectedIds)) return;
        
        $records = PerbaikanTikorToko::whereIn('id', $this->selectedIds)
            ->where('status', 'Pending')
            ->get();
            
        $count = 0;
        foreach ($records as $record) {
            $record->status = 'Approved';
            $record->keterangan = null;
            $record->save();
            
            \Illuminate\Support\Facades\DB::table('list_toko_pareto_team_elite')
                ->where('distributor_code', $record->distributor_code)
                ->where('customer_code_prc', $record->customer_code)
                ->update([
                    'latitude' => $record->latitude,
                    'longitude' => $record->longitude,
                ]);
                
            $count++;
        }
            
        if ($count > 0) {
            $this->dispatch('show-toast', type: 'success', message: $count . ' Tikor Toko berhasil disetujui secara massal dan list toko pareto diupdate');
        }
        $this->selectedIds = [];
    }

    public function export()
    {
        $exportData = new \App\Exports\PerbaikanTikorExport(
            $this->search, 
            $this->statusFilter, 
            $this->dateStart, 
            $this->dateEnd, 
            $this->selectedIds
        );

        if ($exportData->query()->count() === 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Toko belum ada yang di-approve untuk dilakukan perubahan');
            return;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            $exportData, 
            'perbaikan_tikor_toko_approved_' . now()->format('Ymd_His') . '.xlsx'
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
        $query = PerbaikanTikorToko::query()->where('perbaikan_tikor_toko.source', 'elite');
        
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('perbaikan_tikor_toko.region_code', is_array($user->region_code) ? $user->region_code : [$user->region_code]);
        }

        return $query;
    }

    #[Computed]
    public function filteredQuery()
    {
        $query = $this->baseQuery()
            ->with(['distributorImplementasiEskalink'])
            ->leftJoin('list_toko_pareto_team_elite as elite', function ($join) {
                $join->on('perbaikan_tikor_toko.distributor_code', '=', 'elite.distributor_code')
                     ->on('perbaikan_tikor_toko.customer_code', '=', 'elite.customer_code_prc');
            })
            ->select(
                'perbaikan_tikor_toko.*', 
                'elite.customer_name as elite_customer_name', 
                'elite.customer_address as elite_customer_address',
                'elite.latitude as elite_lat', 
                'elite.longitude as elite_long'
            )
            ->addSelect([
                'sales_name' => \Illuminate\Support\Facades\DB::table('fsalesman')
                    ->select('SLSNAME')
                    ->whereColumn('SLSNO', 'perbaikan_tikor_toko.sales_code')
                    ->limit(1)
            ]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('perbaikan_tikor_toko.customer_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('perbaikan_tikor_toko.distributor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('perbaikan_tikor_toko.sales_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('elite.customer_name', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('distributorImplementasiEskalink', function($q3) {
                      $q3->where('distributor_name', 'ilike', '%' . $this->search . '%');
                  })
                  ->orWhereExists(function ($sub) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('fsalesman')
                          ->whereColumn('SLSNO', 'perbaikan_tikor_toko.sales_code')
                          ->where('SLSNAME', 'ilike', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter && $this->statusFilter !== 'Semua Kategori' && $this->statusFilter !== 'Semua Status') {
            $query->where('perbaikan_tikor_toko.status', $this->statusFilter);
        }

        if ($this->dateStart) {
            $query->whereDate('perbaikan_tikor_toko.created_at', '>=', $this->dateStart);
        }

        if ($this->dateEnd) {
            $query->whereDate('perbaikan_tikor_toko.created_at', '<=', $this->dateEnd);
        }

        $query->orderByRaw("
            CASE 
                WHEN perbaikan_tikor_toko.status = 'Pending' THEN 1 
                WHEN perbaikan_tikor_toko.status = 'Rejected' THEN 2 
                WHEN perbaikan_tikor_toko.status = 'Approved' THEN 3 
                ELSE 4 
            END
        ")->orderBy('perbaikan_tikor_toko.created_at', 'desc');

        return $query;
    }

    public function render()
    {
        $query = clone $this->filteredQuery;
        
        $data = $query->paginate(100);

        $kpi = [
            'total' => $this->baseQuery()->count(),
            'pending' => $this->baseQuery()->where('perbaikan_tikor_toko.status', 'Pending')->count(),
            'approved' => $this->baseQuery()->where('perbaikan_tikor_toko.status', 'Approved')->count(),
            'rejected' => $this->baseQuery()->where('perbaikan_tikor_toko.status', 'Rejected')->count(),
        ];

        $duplicates = PerbaikanTikorToko::selectRaw('distributor_code, customer_code')
            ->groupBy('distributor_code', 'customer_code')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->map(function ($item) {
                return $item->distributor_code . '_' . $item->customer_code;
            })
            ->toArray();

        return view('livewire.others.perbaikantikorelite.index', [
            'data' => $data,
            'kpi' => $kpi,
            'duplicates' => $duplicates
        ])->layout('layouts.app');
    }
}
