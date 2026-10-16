<?php

namespace App\Livewire\Others\PerbaikantikorElite;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\PerbaikanTikorToko;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    public $menuRoute = 'others.perbaikantikor';
    public $canManage = false;

    public function mount()
    {
        $this->authorizeAction('can_view');
        $user = auth()->user();
        $this->canManage = $user && $user->hasRole(['admin', 'user', 'spm']);
    }

    #[Url]
    public $search = '';

    #[Url]
    public $distributorFilter = '';

    #[Url]
    public $salesFilter = '';

    #[Url]
    public $statusFilter = '';

    #[Url]
    public $dateStart = '';

    #[Url]
    public $dateEnd = '';

    public $exportDateStart = '';
    public $exportDateEnd = '';

    public $keteranganReject = '';
    public $selectedId = null;
    public $selectedIds = [];
    public $summaryData = [];
    public $summarySearch = '';

    public $selectAll = false;
    // Fitur bulk approve dimatikan sementara agar reviewer memeriksa foto & titik koordinat di modal detail
    public bool $enableBulkApprove = false;

    #[Computed]
    public function canManage()
    {
        $user = auth()->user();
        return $user && $user->hasRole(['admin', 'user', 'spm']);
    }

    #[Computed]
    public function selectedPendingCount()
    {
        if (empty($this->selectedIds) || !$this->canManage) return 0;
        return $this->baseQuery()->whereIn('perbaikan_tikor_toko.id', $this->selectedIds)->where('perbaikan_tikor_toko.status', 'Pending')->count();
    }

    #[Computed]
    public function distributorOptions()
    {
        return (clone $this->baseQuery())
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'perbaikan_tikor_toko.distributor_code')
            ->select('perbaikan_tikor_toko.distributor_code', 'die.distributor_name')
            ->whereNotNull('perbaikan_tikor_toko.distributor_code')
            ->distinct()
            ->orderBy('die.distributor_name')
            ->orderBy('perbaikan_tikor_toko.distributor_code')
            ->get();
    }

    #[Computed]
    public function distributorSelectOptions()
    {
        $options = $this->distributorOptions()->map(function ($d) {
            return [
                'value' => (string) $d->distributor_code,
                'label' => $d->distributor_code . ($d->distributor_name ? ' - ' . $d->distributor_name : ''),
            ];
        })->values()->toArray();

        array_unshift($options, [
            'value' => '',
            'label' => 'Semua Distributor',
        ]);

        return $options;
    }

    #[Computed]
    public function salesOptions()
    {
        $query = (clone $this->baseQuery());

        if ($this->distributorFilter) {
            $query->where('perbaikan_tikor_toko.distributor_code', $this->distributorFilter);
        }

        return $query
            ->leftJoin('fsalesman as fs', function ($join) {
                $join->on('fs.SLSNO', '=', 'perbaikan_tikor_toko.sales_code');
            })
            ->select('perbaikan_tikor_toko.sales_code', 'fs.SLSNAME as sales_name')
            ->whereNotNull('perbaikan_tikor_toko.sales_code')
            ->distinct()
            ->orderBy('fs.SLSNAME')
            ->orderBy('perbaikan_tikor_toko.sales_code')
            ->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDistributorFilter()
    {
        $this->resetPage();
        $this->salesFilter = '';
        $this->selectAll = false;
        $this->selectedIds = [];
    }

    public function updatingSalesFilter()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedIds = [];
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

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'dateStart', 'dateEnd', 'distributorFilter', 'salesFilter']);
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedIds = [];
    }

    public function updatingSelectAll($value)
    {
        if (!$this->canManage) {
            $this->selectedIds = [];
            $this->selectAll = false;
            return;
        }

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

    public function updatingSummarySearch()
    {
        $this->loadSummary();
    }

    public function approve($id)
    {
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyetujui perbaikan koordinat.');
        }

        $record = $this->baseQuery()->where('perbaikan_tikor_toko.id', $id)->first();
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
        if (!$this->enableBulkApprove) {
            $this->dispatch('show-toast', type: 'error', message: 'Fitur persetujuan massal (bulk approve) saat ini dinonaktifkan.');
            return;
        }

        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyetujui perbaikan koordinat.');
        }

        if (empty($this->selectedIds)) return;
        
        $records = $this->baseQuery()
            ->whereIn('perbaikan_tikor_toko.id', $this->selectedIds)
            ->where('perbaikan_tikor_toko.status', 'Pending')
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

    public function loadSummary()
    {
        $query = $this->baseQuery()
            ->leftJoin('fsalesman as f', 'perbaikan_tikor_toko.sales_code', '=', 'f.SLSNO')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'perbaikan_tikor_toko.distributor_code')
            ->selectRaw('
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                perbaikan_tikor_toko.sales_code,
                UPPER(f."SLSNAME") AS sales_name,
                COUNT(DISTINCT perbaikan_tikor_toko.id) AS total_pengajuan,
                COUNT(DISTINCT CASE WHEN perbaikan_tikor_toko.status = \'Pending\' THEN perbaikan_tikor_toko.id END) AS pending,
                COUNT(DISTINCT CASE WHEN perbaikan_tikor_toko.status = \'Rejected\' THEN perbaikan_tikor_toko.id END) AS rejected,
                COUNT(DISTINCT CASE WHEN perbaikan_tikor_toko.status = \'Approved\' THEN perbaikan_tikor_toko.id END) AS approved
            ')
            ->groupBy(
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'perbaikan_tikor_toko.sales_code',
                'f.SLSNAME'
            );

        if ($this->summarySearch) {
            $query->where(function ($q) {
                $q->where('f.SLSNAME', 'ilike', '%' . $this->summarySearch . '%')
                  ->orWhere('perbaikan_tikor_toko.sales_code', 'ilike', '%' . $this->summarySearch . '%')
                  ->orWhere('md.region_name', 'ilike', '%' . $this->summarySearch . '%')
                  ->orWhere('md.area_name', 'ilike', '%' . $this->summarySearch . '%');
            });
        }

        $this->summaryData = $query->orderBy('md.region_code')
            ->orderBy('md.area_code')
            ->orderBy('perbaikan_tikor_toko.sales_code')
            ->get();
        
        $this->dispatch('open-summary-modal');
    }

    public function export()
    {
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengekspor data.');
        }

        $exportData = new \App\Exports\PerbaikanTikorEliteExport(
            $this->search, 
            $this->statusFilter, 
            $this->exportDateStart, 
            $this->exportDateEnd, 
            $this->selectedIds,
            $this->distributorFilter,
            $this->salesFilter
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
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak perbaikan koordinat.');
        }

        $this->selectedId = $id;
        $this->keteranganReject = '';
        $this->dispatch('open-reject-modal');
    }

    public function reject()
    {
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak perbaikan koordinat.');
        }

        $this->validate([
            'keteranganReject' => 'required|min:5'
        ], [
            'keteranganReject.required' => 'Keterangan penolakan wajib diisi',
            'keteranganReject.min' => 'Keterangan terlalu singkat'
        ]);

        $record = $this->baseQuery()->where('perbaikan_tikor_toko.id', $this->selectedId)->first();
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
        return PerbaikanTikorToko::query()->forUserAccess(auth()->user(), 'elite', 'perbaikan_tikor_toko');
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

        if ($this->distributorFilter) {
            $query->where('perbaikan_tikor_toko.distributor_code', $this->distributorFilter);
        }

        if ($this->salesFilter) {
            $query->where('perbaikan_tikor_toko.sales_code', $this->salesFilter);
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

        $duplicates = (clone $this->baseQuery())
            ->selectRaw('perbaikan_tikor_toko.distributor_code, perbaikan_tikor_toko.sales_code, perbaikan_tikor_toko.customer_code')
            ->groupBy('perbaikan_tikor_toko.distributor_code', 'perbaikan_tikor_toko.sales_code', 'perbaikan_tikor_toko.customer_code')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->map(function ($item) {
                return $item->distributor_code . '_' . $item->sales_code . '_' . $item->customer_code;
            })
            ->toArray();

        return view('livewire.others.perbaikantikorelite.index', [
            'data' => $data,
            'kpi' => $kpi,
            'duplicates' => $duplicates
        ])->layout('layouts.app');
    }
}
