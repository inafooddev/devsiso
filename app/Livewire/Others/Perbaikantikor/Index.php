<?php

namespace App\Livewire\Others\Perbaikantikor;

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
            ->select('perbaikan_tikor_toko.distributor_code', \Illuminate\Support\Facades\DB::raw('COALESCE(die.distributor_name, perbaikan_tikor_toko.distributor_code) as distributor_name'))
            ->whereNotNull('perbaikan_tikor_toko.distributor_code')
            ->distinct()
            ->orderBy('distributor_name')
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
                $join->on('fs.SLSNO', '=', 'perbaikan_tikor_toko.sales_code')
                     ->on('fs.KD', '=', 'perbaikan_tikor_toko.distributor_code');
            })
            ->select('perbaikan_tikor_toko.sales_code', \Illuminate\Support\Facades\DB::raw('COALESCE(fs."SLSNAME", perbaikan_tikor_toko.sales_code) as sales_name'))
            ->whereNotNull('perbaikan_tikor_toko.sales_code')
            ->distinct()
            ->orderBy('sales_name')
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

    public function applyFilter()
    {
        if ($this->dateStart && $this->dateEnd) {
            $start = \Carbon\Carbon::parse($this->dateStart);
            $end = \Carbon\Carbon::parse($this->dateEnd);

            if ($start->gt($end)) {
                $this->dispatch('show-toast', type: 'error', message: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
                return;
            }
        }

        $this->resetPage();
        $this->selectAll = false;
        $this->selectedIds = [];
    }

    public function resetFilter()
    {
        $this->resetFilters();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'dateStart', 'dateEnd', 'distributorFilter', 'salesFilter']);
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedIds = [];
    }

    public function setDatePreset(string $preset): void
    {
        $today = \Carbon\Carbon::today();

        switch ($preset) {
            case 'today':
                $this->dateStart = $today->format('Y-m-d');
                $this->dateEnd = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay()->format('Y-m-d');
                $this->dateStart = $yesterday;
                $this->dateEnd = $yesterday;
                break;
            case 'last_7_days':
                $this->dateStart = $today->copy()->subDays(6)->format('Y-m-d');
                $this->dateEnd = $today->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateStart = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->dateEnd = $today->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->dateStart = $today->copy()->subDays(29)->format('Y-m-d');
                $this->dateEnd = $today->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $today->copy()->subMonthNoOverflow();
                $this->dateStart = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
                $this->dateEnd = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
                break;
        }
        $this->resetPage();
    }

    public function previousMonth(): void
    {
        $currentStart = !empty($this->dateStart) ? \Carbon\Carbon::parse($this->dateStart) : \Carbon\Carbon::today();
        $prevMonth = $currentStart->copy()->subMonthNoOverflow();
        $this->dateStart = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = $prevMonth->copy()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $currentStart = !empty($this->dateStart) ? \Carbon\Carbon::parse($this->dateStart) : \Carbon\Carbon::today();
        $nextMonth = $currentStart->copy()->addMonthNoOverflow();
        $this->dateStart = $nextMonth->copy()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = $nextMonth->copy()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
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

    public function updatingSummarySearch()
    {
        $this->loadSummary();
    }

    public function updatingSelectedIds()
    {
        $this->selectAll = false;
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
            $this->dispatch('show-toast', type: 'success', message: 'Tikor Toko berhasil disetujui');
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
        
        $count = $this->baseQuery()
            ->whereIn('perbaikan_tikor_toko.id', $this->selectedIds)
            ->where('perbaikan_tikor_toko.status', 'Pending')
            ->update([
                'status' => 'Approved',
                'keterangan' => null
            ]);
            
        if ($count > 0) {
            $this->dispatch('show-toast', type: 'success', message: $count . ' Tikor Toko berhasil disetujui secara massal');
        }
        $this->selectedIds = [];
    }

    public function loadSummary()
    {
        $query = $this->baseQuery()
            ->leftJoin('fsalesman as f', function($join) {
                $join->on('perbaikan_tikor_toko.sales_code', '=', 'f.SLSNO')
                     ->on('perbaikan_tikor_toko.distributor_code', '=', 'f.KD');
            })
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'perbaikan_tikor_toko.distributor_code')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->selectRaw('
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                md.distributor_name,
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
                'md.distributor_name',
                'perbaikan_tikor_toko.sales_code',
                'f.SLSNAME'
            );

        if ($this->summarySearch) {
            $query->where(function ($q) {
                $q->where('md.distributor_name', 'ilike', '%' . $this->summarySearch . '%')
                  ->orWhere('f.SLSNAME', 'ilike', '%' . $this->summarySearch . '%')
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

        $exportData = new \App\Exports\PerbaikanTikorExport(
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

    /**
     * Pengecekan Anomali Titik Koordinat:
     * 1. Cek apakah titik usulan di dekat kantor/gudang distributor (radius <= 100m).
     * 2. Cek apakah titik toko sama dengan toko lain di master customer_prc_eska.
     * 3. Cek apakah ada pengajuan perbaikan borongan di satu titik (perbaikan_tikor_toko).
     *
     * @param int $id
     * @return array|null
     */
    public function checkAnomalies($id)
    {
        $item = $this->baseQuery()
            ->leftJoin('distributor_implementasi_eskalink as die', function($join) {
                $join->on('die.eskalink_code', '=', 'perbaikan_tikor_toko.distributor_code')
                     ->orOn('die.distributor_code', '=', 'perbaikan_tikor_toko.distributor_code');
            })
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('customer_prc_eska as c', function($join) {
                $join->on('perbaikan_tikor_toko.distributor_code', '=', 'c.kodecabang')
                     ->on('perbaikan_tikor_toko.customer_code', '=', 'c.custno');
            })
            ->where('perbaikan_tikor_toko.id', $id)
            ->select(
                'perbaikan_tikor_toko.*',
                'c.la as master_la',
                'c.lg as master_lg',
                'c.custname as master_custname',
                'md.distributor_name as md_name',
                'die.distributor_name as die_name',
                'md.latitude as dist_lat',
                'md.longitude as dist_lon'
            )
            ->first();

        if (!$item) return null;

        $lat = (float) $item->latitude;
        $lon = (float) $item->longitude;

        // --- 1. CEK RADIUS DISTRIBUTOR (<= 100m) ---
        $distLat = $item->dist_lat;
        $distLon = $item->dist_lon;
        $distName = $item->md_name ?? $item->die_name ?? $item->distributor_code;

        if (!$distLat) {
            $directMd = \Illuminate\Support\Facades\DB::table('master_distributors')
                ->where('distributor_code', $item->distributor_code)
                ->select('distributor_name as md_name', 'latitude as dist_lat', 'longitude as dist_lon')
                ->first();
            if ($directMd) {
                $distLat = $directMd->dist_lat;
                $distLon = $directMd->dist_lon;
                $distName = $directMd->md_name;
            }
        }

        $distRadiusMeters = null;
        $distAnomaly = false;
        $distMessage = '';

        if ($distLat && $distLon && $lat && $lon) {
            $earthRadius = 6371000;
            $dLat = deg2rad((float)$distLat - $lat);
            $dLon = deg2rad((float)$distLon - $lon);
            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($lat)) * cos(deg2rad((float)$distLat)) *
                 sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distRadiusMeters = round($earthRadius * $c);

            if ($distRadiusMeters <= 100) {
                $distAnomaly = true;
                $distMessage = "Titik usulan sangat dekat distributor ({$distRadiusMeters} m, radius <= 100 m).";
            } else {
                $distFormatted = $distRadiusMeters >= 1000 ? round($distRadiusMeters / 1000, 1) . ' km' : "{$distRadiusMeters} m";
                $distMessage = "Jarak ke distributor: {$distFormatted} (Aman, di luar radius 100m).";
            }
        } else {
            $distMessage = "Koordinat distributor belum terdaftar di Master Distributor.";
        }

        $distributorResult = [
            'is_anomaly' => $distAnomaly,
            'distance_meters' => $distRadiusMeters,
            'distributor_name' => $distName,
            'distributor_lat' => $distLat ?? null,
            'distributor_lon' => $distLon ?? null,
            'message' => $distMessage,
        ];

        // --- 2. CEK TITIK TOKO DI MASTER CUSTOMER (customer_prc_eska) ---
        $masterAnomaly = false;
        $masterMessage = '';
        $sameMasterStores = [];
        $sameProposedStores = [];

        // 2a. Titik lama toko ini di master apakah sama dengan toko lain?
        if ($item->master_la && $item->master_lg && (float)$item->master_la != 0) {
            $sameMasterStores = \Illuminate\Support\Facades\DB::table('customer_prc_eska')
                ->where('kodecabang', $item->distributor_code)
                ->where('custno', '!=', $item->customer_code)
                ->where('la', (string) $item->master_la)
                ->where('lg', (string) $item->master_lg)
                ->select('custno', 'custname', 'custadd1')
                ->limit(10)
                ->get()
                ->toArray();
        }

        // 2b. Titik usulan baru apakah sama dengan toko lain yang sudah ada di master?
        if ($lat && $lon) {
            $sameProposedStores = \Illuminate\Support\Facades\DB::table('customer_prc_eska')
                ->where('kodecabang', $item->distributor_code)
                ->where('custno', '!=', $item->customer_code)
                ->where('la', (string) $lat)
                ->where('lg', (string) $lon)
                ->select('custno', 'custname', 'custadd1')
                ->limit(10)
                ->get()
                ->toArray();
        }

        if (count($sameMasterStores) > 0 || count($sameProposedStores) > 0) {
            $masterAnomaly = true;
            if (count($sameMasterStores) > 0 && count($sameProposedStores) > 0) {
                $masterMessage = "Titik master sama dgn " . count($sameMasterStores) . " toko lain, dan titik usulan juga sama dgn " . count($sameProposedStores) . " toko di master.";
            } elseif (count($sameMasterStores) > 0) {
                $masterMessage = "Titik master toko ini sama persis dengan " . count($sameMasterStores) . " toko lain di master cabang.";
            } else {
                $masterMessage = "Titik usulan ini sama persis dengan koordinat master " . count($sameProposedStores) . " toko lain.";
            }
        } else {
            $masterMessage = "Titik toko unik di master (tidak ada duplikasi koordinat dengan toko lain).";
        }

        $masterResult = [
            'is_anomaly' => $masterAnomaly,
            'same_master_count' => count($sameMasterStores),
            'same_master_stores' => $sameMasterStores,
            'same_proposed_count' => count($sameProposedStores),
            'same_proposed_stores' => $sameProposedStores,
            'message' => $masterMessage,
        ];

        // --- 3. CEK PENGAJUAN PERBAIKAN BORONGAN DI SATU TITIK (perbaikan_tikor_toko) ---
        $boronganAnomaly = false;
        $boronganMessage = '';
        $boronganStores = [];

        if ($lat && $lon) {
            $candidates = \Illuminate\Support\Facades\DB::table('perbaikan_tikor_toko as pt')
                ->leftJoin('customer_prc_eska as c', function($join) {
                    $join->on('pt.distributor_code', '=', 'c.kodecabang')
                         ->on('pt.customer_code', '=', 'c.custno');
                })
                ->where('pt.id', '!=', $item->id)
                ->where('pt.distributor_code', $item->distributor_code)
                ->whereBetween('pt.latitude', [$lat - 0.0003, $lat + 0.0003])
                ->whereBetween('pt.longitude', [$lon - 0.0003, $lon + 0.0003])
                ->select(
                    'pt.id',
                    'pt.customer_code',
                    'c.custname as customer_name',
                    'pt.sales_code',
                    'pt.status',
                    'pt.created_at',
                    'pt.latitude',
                    'pt.longitude'
                )
                ->limit(20)
                ->get();

            $earthRadius = 6371000;
            foreach ($candidates as $cand) {
                $candLat = (float) $cand->latitude;
                $candLon = (float) $cand->longitude;
                $dLat = deg2rad($candLat - $lat);
                $dLon = deg2rad($candLon - $lon);
                $a = sin($dLat/2) * sin($dLat/2) +
                     cos(deg2rad($lat)) * cos(deg2rad($candLat)) *
                     sin($dLon/2) * sin($dLon/2);
                $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                $distM = round($earthRadius * $c);

                if ($distM <= 25) { // Jarak <= 25m atau koordinat sama persis
                    $cand->distance_to_current = $distM;
                    $cand->created_at_formatted = \Carbon\Carbon::parse($cand->created_at)->format('d M H:i');
                    $boronganStores[] = $cand;
                }
            }

            if (count($boronganStores) > 0) {
                $boronganAnomaly = true;
                $boronganMessage = "Terdeteksi " . count($boronganStores) . " pengajuan toko lain di titik yang sama / berdekatan (<= 25m).";
            } else {
                $boronganMessage = "Titik usulan mandiri (tidak ada pengajuan borongan lain di titik ini).";
            }
        }

        $boronganResult = [
            'is_anomaly' => $boronganAnomaly,
            'borongan_count' => count($boronganStores),
            'borongan_stores' => $boronganStores,
            'message' => $boronganMessage,
        ];

        return [
            'has_any_anomaly' => $distAnomaly || $masterAnomaly || $boronganAnomaly,
            'distributor' => $distributorResult,
            'master' => $masterResult,
            'borongan' => $boronganResult,
        ];
    }

    private function baseQuery()
    {
        return PerbaikanTikorToko::query()->forUserAccess(auth()->user(), 'se', 'perbaikan_tikor_toko');
    }

    #[Computed]
    public function filteredQuery()
    {
        $query = $this->baseQuery()
            ->with(['distributorImplementasiEskalink'])
            ->leftJoin('customer_prc_eska as c', function ($join) {
                $join->on('perbaikan_tikor_toko.distributor_code', '=', 'c.kodecabang')
                     ->on('perbaikan_tikor_toko.customer_code', '=', 'c.custno');
            })
            ->select(
                'perbaikan_tikor_toko.*',
                'c.custname as exact_custname',
                'c.custadd1 as exact_custadd1',
                'c.ccity as exact_ccity',
                'c.la as exact_la',
                'c.lg as exact_lg'
            )
            ->addSelect([
                'sales_name' => \Illuminate\Support\Facades\DB::table('fsalesman')
                    ->select('SLSNAME')
                    ->whereColumn('SLSNO', 'perbaikan_tikor_toko.sales_code')
                    ->whereColumn('KD', 'perbaikan_tikor_toko.distributor_code')
                    ->limit(1)
            ]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('perbaikan_tikor_toko.customer_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('perbaikan_tikor_toko.distributor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('perbaikan_tikor_toko.sales_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('c.custname', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('distributorImplementasiEskalink', function($q3) {
                      $q3->where('distributor_name', 'ilike', '%' . $this->search . '%');
                  })
                  ->orWhereExists(function ($sub) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('fsalesman')
                          ->whereColumn('SLSNO', 'perbaikan_tikor_toko.sales_code')
                          ->whereColumn('KD', 'perbaikan_tikor_toko.distributor_code')
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

        $kpiRow = $this->baseQuery()
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN perbaikan_tikor_toko.status = 'Pending' THEN 1 END) as pending,
                COUNT(CASE WHEN perbaikan_tikor_toko.status = 'Approved' THEN 1 END) as approved,
                COUNT(CASE WHEN perbaikan_tikor_toko.status = 'Rejected' THEN 1 END) as rejected
            ")
            ->first();

        $kpi = [
            'total' => (int) ($kpiRow->total ?? 0),
            'pending' => (int) ($kpiRow->pending ?? 0),
            'approved' => (int) ($kpiRow->approved ?? 0),
            'rejected' => (int) ($kpiRow->rejected ?? 0),
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

        return view('livewire.others.perbaikantikor.index', [
            'data' => $data,
            'kpi' => $kpi,
            'duplicates' => $duplicates
        ])->layout('layouts.app');
    }
}
