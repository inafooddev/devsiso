<?php

namespace App\Livewire\CallPlan\JksSalesmans;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Traits\WithCallPlanFilters;
use App\Services\JksSalesmanService;

class Index extends Component
{
    use WithPagination;
    use WithCallPlanFilters;

    #[Title('JKS Salesman')]
    #[Layout('layouts.app')]

    protected $listeners = ['refreshTable' => '$refresh'];

    public $search = '';
    public $headerSalesman = '';
    public $selectedHari = 'h1';
    public $selectedMinggu = 'ganjil';

    // Collision Detection
    public $collisionCustomerName = '';
    public $collisionDetails = [];
    public $showCollisionModal = false;

    public function toggleHari($hari)
    {
        if ($this->selectedHari === $hari) {
            $this->selectedHari = '';
        } else {
            $this->selectedHari = $hari;
        }
        $this->resetPage();
    }

    public function toggleMinggu($minggu)
    {
        if ($this->selectedMinggu === $minggu) {
            $this->selectedMinggu = '';
        } else {
            $this->selectedMinggu = $minggu;
        }
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingHeaderSalesman()
    {
        $this->resetPage();
    }

    #[\Livewire\Attributes\Computed]
    public function headerSalesmans()
    {
        if (empty($this->appliedDistributor)) {
            return collect([]);
        }

        return \Illuminate\Support\Facades\DB::table('salesmans')
            ->select('salesman_code', 'salesman_name')
            ->where('distributor_code', $this->appliedDistributor)
            ->orderBy('salesman_name')
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function affectedStores()
    {
        if (!$this->showSwapModal || empty($this->swapSalesmanCode) || empty($this->swapHariAsal) || empty($this->swapHariTujuan) || $this->swapHariAsal === $this->swapHariTujuan) {
            return collect([]);
        }

        $selectedWeeks = [];
        if ($this->swapMinggu['w1']) $selectedWeeks[] = 'w1';
        if ($this->swapMinggu['w2']) $selectedWeeks[] = 'w2';
        if ($this->swapMinggu['w3']) $selectedWeeks[] = 'w3';
        if ($this->swapMinggu['w4']) $selectedWeeks[] = 'w4';

        if (empty($selectedWeeks)) return collect([]);

        return \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.id', 'js.customer_code', 'js.' . $this->swapHariAsal . ' as asal', 'js.' . $this->swapHariTujuan . ' as tujuan', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.salesman_code', $this->swapSalesmanCode)
            ->where('js.distributor_code', $this->appliedDistributor)
            ->where(function($query) use ($selectedWeeks) {
                foreach ($selectedWeeks as $week) {
                    $query->where('js.' . $week, 'Y');
                }
            })
            ->where(function($query) {
                $query->where('js.' . $this->swapHariAsal, 'Y')
                      ->orWhere('js.' . $this->swapHariTujuan, 'Y');
            })
            ->orderByRaw("
                CASE 
                    WHEN js.{$this->swapHariAsal} = 'Y' AND (js.{$this->swapHariTujuan} = 'T' OR js.{$this->swapHariTujuan} IS NULL) THEN 1
                    WHEN js.{$this->swapHariTujuan} = 'Y' AND (js.{$this->swapHariAsal} = 'T' OR js.{$this->swapHariAsal} IS NULL) THEN 2
                    ELSE 3
                END ASC
            ")
            ->orderBy('js.customer_code', 'asc')
            ->get();
    }

    public $showCalendarModal = false;
    public $selectedWeekForCalendar = null;

    // State Edit
    public $showEditModal = false;
    public $editId = null;
    public $editCustomerName = '';
    public $editSalesmanCode = '';
    public $editHari = ['h1' => false, 'h2' => false, 'h3' => false, 'h4' => false, 'h5' => false, 'h6' => false, 'h7' => false];
    public $editMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
    public $editReason = '';

    // State Delete
    public $showDeleteModal = false;
    public $deleteId = null;
    public $deleteReason = '';

    // State Swap (Tukar Jadwal)
    public $showSwapModal = false;
    public $swapTab = 'hari';
    public $swapSalesmanCode = '';
    public $swapHariAsal = '';
    public $swapHariTujuan = '';
    public $swapMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
    
    public $swapMingguAsal = '';
    public $swapMingguTujuan = '';
    
    public $swapSalesmanAsal = '';
    public $swapSalesmanTujuan = '';

    // State Bulk Delete
    public $showBulkDeleteModal = false;
    public $bdSalesman = '';
    public $bdHari = '';
    public $bdMinggu = '';
    public $bdSelectedIds = [];
    public $bdSelectAll = false;
    public $bdReason = '';

    protected function handleJksAction($actionType, $payload, $reason, callable $executeCallback, $successMessage)
    {
        if (auth()->user()->hasRole('user')) {
            // Bagian Data (Auto-Approve)
            $executeCallback();
            
            \Illuminate\Support\Facades\DB::table('jks_approvals')->insert([
                'maker_id' => auth()->id(),
                'checker_id' => auth()->id(),
                'distributor_code' => $this->appliedDistributor,
                'action_type' => $actionType,
                'payload' => json_encode($payload),
                'reason' => $reason,
                'status' => 'AUTO_APPROVED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->dispatch('toast', ['type' => 'success', 'message' => $successMessage]);
        } else {
            // SPV atau Role Lain (Pending Approval)
            \Illuminate\Support\Facades\DB::table('jks_approvals')->insert([
                'maker_id' => auth()->id(),
                'checker_id' => null,
                'distributor_code' => $this->appliedDistributor,
                'action_type' => $actionType,
                'payload' => json_encode($payload),
                'reason' => $reason,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan Bagian Data.']);
        }
    }

    public function openSwapModal()
    {
        $this->swapSalesmanCode = '';
        $this->swapHariAsal = '';
        $this->swapHariTujuan = '';
        $this->swapMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
        $this->swapMingguAsal = '';
        $this->swapMingguTujuan = '';
        $this->swapSalesmanAsal = '';
        $this->swapSalesmanTujuan = '';
        $this->swapTab = 'hari';
        $this->showSwapModal = true;
    }

    public function closeSwapModal()
    {
        $this->showSwapModal = false;
    }

    public function executeSwapDay()
    {
        if (empty($this->swapSalesmanCode) || empty($this->swapHariAsal) || empty($this->swapHariTujuan) || $this->swapHariAsal === $this->swapHariTujuan) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Input tidak valid untuk pertukaran jadwal.']);
            return;
        }

        $selectedWeeks = [];
        if ($this->swapMinggu['w1']) $selectedWeeks[] = 'w1';
        if ($this->swapMinggu['w2']) $selectedWeeks[] = 'w2';
        if ($this->swapMinggu['w3']) $selectedWeeks[] = 'w3';
        if ($this->swapMinggu['w4']) $selectedWeeks[] = 'w4';

        if (empty($selectedWeeks)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih setidaknya satu minggu (M1-M4).']);
            return;
        }

        $payload = [
            'salesman_code' => $this->swapSalesmanCode,
            'hari_asal' => $this->swapHariAsal,
            'hari_tujuan' => $this->swapHariTujuan,
            'minggu' => $selectedWeeks,
        ];

        $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
        $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
        
        $hariAsalName = $hariNames[$this->swapHariAsal] ?? $this->swapHariAsal;
        $hariTujuanName = $hariNames[$this->swapHariTujuan] ?? $this->swapHariTujuan;
        $seName = $this->headerSalesmans->firstWhere('salesman_code', $this->swapSalesmanCode)->salesman_name ?? $this->swapSalesmanCode;
        $weeksStr = collect($selectedWeeks)->map(fn($w) => $weekNames[$w] ?? $w)->implode(', ');

        $reason = "Tukar Jadwal Hari: {$hariAsalName} ditukar dengan {$hariTujuanName} | SE: {$seName} | Minggu: [{$weeksStr}]";

        try {
            $this->handleJksAction('TUKAR_HARI', $payload, $reason, function() use ($payload) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
                    $rows = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_code'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->where(function($query) use ($payload) {
                            foreach ($payload['minggu'] as $week) {
                                $query->where($week, 'Y');
                            }
                        })
                        ->where(function($query) use ($payload) {
                            $query->where($payload['hari_asal'], 'Y')
                                  ->orWhere($payload['hari_tujuan'], 'Y');
                        })
                        ->get();

                    foreach ($rows as $row) {
                        $unselectedWeeks = array_diff(['w1', 'w2', 'w3', 'w4'], $payload['minggu']);
                        $hasUnselected = false;
                        foreach ($unselectedWeeks as $uw) {
                            if ($row->$uw === 'Y') {
                                $hasUnselected = true;
                                break;
                            }
                        }

                        if ($hasUnselected) {
                            $updateOriginal = ['updated_at' => now()];
                            foreach ($payload['minggu'] as $sw) {
                                $updateOriginal[$sw] = 'T';
                            }
                            \Illuminate\Support\Facades\DB::table('jks_salesmans')
                                ->where('id', $row->id)
                                ->update($updateOriginal);

                            $newRow = (array) $row;
                            unset($newRow['id']);
                            $newRow['created_at'] = now();
                            $newRow['updated_at'] = now();
                            
                            foreach ($unselectedWeeks as $uw) {
                                $newRow[$uw] = 'T';
                            }
                            
                            $temp = $newRow[$payload['hari_asal']];
                            $newRow[$payload['hari_asal']] = $newRow[$payload['hari_tujuan']];
                            $newRow[$payload['hari_tujuan']] = $temp;
                            
                            \Illuminate\Support\Facades\DB::table('jks_salesmans')->insert($newRow);

                        } else {
                            \Illuminate\Support\Facades\DB::table('jks_salesmans')
                                ->where('id', $row->id)
                                ->update([
                                    $payload['hari_asal'] => $row->{$payload['hari_tujuan']},
                                    $payload['hari_tujuan'] => $row->{$payload['hari_asal']},
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                });
            }, 'Pertukaran jadwal massal berhasil!');

            $this->closeSwapModal();
            
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pertukaran: ' . $e->getMessage()]);
        }
    }

    #[\Livewire\Attributes\Computed]
    public function affectedMingguSummary()
    {
        if (empty($this->swapSalesmanCode) || empty($this->swapMingguAsal) || empty($this->swapMingguTujuan) || $this->swapMingguAsal === $this->swapMingguTujuan) {
            return collect([]);
        }

        $days = [
            'h1' => 'Senin',
            'h2' => 'Selasa',
            'h3' => 'Rabu',
            'h4' => 'Kamis',
            'h5' => 'Jumat',
            'h6' => 'Sabtu',
            'h7' => 'Minggu'
        ];

        $summary = [];

        foreach ($days as $col => $label) {
            $countAsal = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $this->swapSalesmanCode)
                ->where('distributor_code', $this->appliedDistributor)
                ->where($col, 'Y')
                ->where($this->swapMingguAsal, 'Y')
                ->count();

            $countTujuan = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('salesman_code', $this->swapSalesmanCode)
                ->where('distributor_code', $this->appliedDistributor)
                ->where($col, 'Y')
                ->where($this->swapMingguTujuan, 'Y')
                ->count();

            if ($countAsal > 0 || $countTujuan > 0) {
                $summary[] = (object) [
                    'hari' => $label,
                    'asal' => $countAsal,
                    'tujuan' => $countTujuan
                ];
            }
        }

        return collect($summary);
    }

    public function executeSwapMinggu()
    {
        if (empty($this->swapSalesmanCode) || empty($this->swapMingguAsal) || empty($this->swapMingguTujuan)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Lengkapi form sebelum mengeksekusi!']);
            return;
        }

        if ($this->swapMingguAsal === $this->swapMingguTujuan) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Minggu asal dan tujuan tidak boleh sama!']);
            return;
        }

        $summary = $this->affectedMingguSummary;
        if ($summary->isEmpty()) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Tidak ada toko yang terdampak!']);
            return;
        }

        $payload = [
            'salesman_code' => $this->swapSalesmanCode,
            'minggu_asal' => $this->swapMingguAsal,
            'minggu_tujuan' => $this->swapMingguTujuan,
        ];

        $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
        $mingguAsalName = $weekNames[$this->swapMingguAsal] ?? strtoupper($this->swapMingguAsal);
        $mingguTujuanName = $weekNames[$this->swapMingguTujuan] ?? strtoupper($this->swapMingguTujuan);
        $seName = $this->headerSalesmans->firstWhere('salesman_code', $this->swapSalesmanCode)->salesman_name ?? $this->swapSalesmanCode;

        $reason = "Tukar Jadwal Minggu: {$mingguAsalName} ditukar dengan {$mingguTujuanName} | SE: {$seName}";

        try {
            $this->handleJksAction('TUKAR_MINGGU', $payload, $reason, function() use ($payload) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
                    $rows = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_code'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->where(function($query) use ($payload) {
                            $query->where($payload['minggu_asal'], 'Y')
                                  ->orWhere($payload['minggu_tujuan'], 'Y');
                        })
                        ->get();

                    foreach ($rows as $row) {
                        $newAsalVal = $row->{$payload['minggu_tujuan']};
                        $newTujuanVal = $row->{$payload['minggu_asal']};

                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->where('id', $row->id)
                            ->update([
                                $payload['minggu_asal'] => $newAsalVal,
                                $payload['minggu_tujuan'] => $newTujuanVal,
                                'updated_at' => now(),
                            ]);
                    }
                });
            }, "Jadwal " . strtoupper($this->swapMingguAsal) . " berhasil ditukar dengan " . strtoupper($this->swapMingguTujuan) . "!");

            $this->closeSwapModal();
            $this->resetPage();

        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal menukar jadwal minggu: ' . $e->getMessage()]);
        }
    }

    #[\Livewire\Attributes\Computed]
    public function affectedSalesmanSummary()
    {
        if (empty($this->swapSalesmanAsal) || empty($this->swapSalesmanTujuan) || $this->swapSalesmanAsal === $this->swapSalesmanTujuan) {
            return collect([]);
        }

        $countAsal = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('salesman_code', $this->swapSalesmanAsal)
            ->where('distributor_code', $this->appliedDistributor)
            ->count();

        $countTujuan = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('salesman_code', $this->swapSalesmanTujuan)
            ->where('distributor_code', $this->appliedDistributor)
            ->count();

        $asalName = collect($this->headerSalesmans)->firstWhere('salesman_code', $this->swapSalesmanAsal)->salesman_name ?? $this->swapSalesmanAsal;
        $tujuanName = collect($this->headerSalesmans)->firstWhere('salesman_code', $this->swapSalesmanTujuan)->salesman_name ?? $this->swapSalesmanTujuan;

        return collect([
            'asal' => [
                'code' => $this->swapSalesmanAsal,
                'name' => $asalName,
                'count' => $countAsal
            ],
            'tujuan' => [
                'code' => $this->swapSalesmanTujuan,
                'name' => $tujuanName,
                'count' => $countTujuan
            ]
        ]);
    }

    public function executeSwapSalesman()
    {
        if (empty($this->swapSalesmanAsal) || empty($this->swapSalesmanTujuan)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Lengkapi form sebelum mengeksekusi!']);
            return;
        }

        if ($this->swapSalesmanAsal === $this->swapSalesmanTujuan) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Salesman asal dan tujuan tidak boleh sama!']);
            return;
        }

        $summary = $this->affectedSalesmanSummary;
        if ($summary['asal']['count'] == 0 && $summary['tujuan']['count'] == 0) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Kedua salesman tidak memiliki toko untuk ditukar!']);
            return;
        }

        $payload = [
            'salesman_asal' => $this->swapSalesmanAsal,
            'salesman_tujuan' => $this->swapSalesmanTujuan,
        ];

        $asalName = $summary['asal']['name'] ?? $this->swapSalesmanAsal;
        $tujuanName = $summary['tujuan']['name'] ?? $this->swapSalesmanTujuan;
        $reason = "Tukar Salesman: {$asalName} ditukar dengan {$tujuanName}";

        try {
            $this->handleJksAction('TUKAR_SALESMAN', $payload, $reason, function() use ($payload) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($payload) {
                    $asalIds = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_asal'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->pluck('id');

                    $tujuanIds = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                        ->where('salesman_code', $payload['salesman_tujuan'])
                        ->where('distributor_code', $this->appliedDistributor)
                        ->pluck('id');

                    if ($asalIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->whereIn('id', $asalIds)
                            ->update([
                                'salesman_code' => $payload['salesman_tujuan'],
                                'updated_at' => now(),
                            ]);
                    }

                    if ($tujuanIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->whereIn('id', $tujuanIds)
                            ->update([
                                'salesman_code' => $payload['salesman_asal'],
                                'updated_at' => now(),
                            ]);
                    }
                });
            }, "Semua toko dari salesman {$this->swapSalesmanAsal} berhasil ditukar dengan {$this->swapSalesmanTujuan}!");

            $this->closeSwapModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal menukar salesman: ' . $e->getMessage()]);
        }
    }

    // --- BULK DELETE METHODS ---
    public function openBulkDeleteModal()
    {
        $this->bdSalesman = '';
        $this->bdHari = '';
        $this->bdMinggu = '';
        $this->bdSelectedIds = [];
        $this->bdSelectAll = false;
        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal()
    {
        $this->showBulkDeleteModal = false;
        $this->bdSelectedIds = [];
        $this->bdSelectAll = false;
    }

    public function updatedBdSalesman() { $this->resetBdSelection(); }
    public function updatedBdHari() { $this->resetBdSelection(); }
    public function updatedBdMinggu() { $this->resetBdSelection(); }

    private function resetBdSelection()
    {
        $this->bdSelectedIds = [];
        $this->bdSelectAll = false;
    }

    public function updatedBdSelectAll($value)
    {
        if ($value) {
            $this->bdSelectedIds = $this->bdPreviewStores->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->bdSelectedIds = [];
        }
    }

    public function updatedBdSelectedIds()
    {
        $previewCount = $this->bdPreviewStores->count();
        $this->bdSelectAll = ($previewCount > 0 && count($this->bdSelectedIds) === $previewCount);
    }

    #[\Livewire\Attributes\Computed]
    public function bdPreviewStores()
    {
        if (empty($this->bdSalesman)) {
            return collect([]);
        }

        $query = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.distributor_code', $this->appliedDistributor)
            ->where('js.salesman_code', $this->bdSalesman);

        if (!empty($this->bdHari)) {
            $query->where('js.' . $this->bdHari, 'Y');
        }

        if (!empty($this->bdMinggu)) {
            $query->where('js.' . $this->bdMinggu, 'Y');
        }

        return $query->orderBy('js.customer_code', 'asc')->get();
    }

    public function executeBulkDelete()
    {
        if (empty($this->bdSelectedIds)) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pilih minimal satu toko untuk dihapus!']);
            return;
        }

        if (strlen(trim($this->bdReason)) < 5) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Alasan reset harus diisi (minimal 5 karakter)!']);
            return;
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $count = count($this->bdSelectedIds);
            
            $payload = [
                'ids' => $this->bdSelectedIds,
                'update' => [
                    'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                    'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                    'reason' => $this->bdReason,
                ]
            ];

            $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
            $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
            $hariFilter = $this->bdHari ? ($hariNames[$this->bdHari] ?? $this->bdHari) : 'Semua Hari';
            $mingguFilter = $this->bdMinggu ? ($weekNames[$this->bdMinggu] ?? $this->bdMinggu) : 'Semua Minggu';
            $seFilter = collect($this->headerSalesmans)->firstWhere('salesman_code', $this->bdSalesman)->salesman_name ?? $this->bdSalesman ?: 'Semua Salesman';

            $reasonFull = "Hapus Massal {$count} Jadwal Toko | Filter -> SE: {$seFilter}, Hari: {$hariFilter}, Minggu: {$mingguFilter} | Alasan: " . $this->bdReason;

            $this->handleJksAction('DELETE_MASSAL', $payload, $reasonFull, function() use ($payload) {
                \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->whereIn('id', $payload['ids'])
                    ->where('distributor_code', $this->appliedDistributor)
                    ->update(array_merge($payload['update'], ['updated_at' => now()]));
            }, "Berhasil mereset {$count} jadwal toko menjadi kosong (T)!");

            \Illuminate\Support\Facades\DB::commit();
            
            $this->closeBulkDeleteModal();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mereset massal: ' . $e->getMessage()]);
        }
    }

    public function exportExcel()
    {
        $filters = $this->getAppliedFilters();
        $filters['salesman'] = $this->headerSalesman;
        
        $fileName = 'JKS_Export_' . date('Ymd_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JksSalesmanExport($this->search, $filters), 
            $fileName
        );
    }

    public function editJadwal($id)
    {
        $jks = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.id', $id)
            ->first();

        if ($jks) {
            $this->editId = $jks->id;
            $this->editCustomerName = $jks->customer_name ?? $jks->customer_code;
            $this->editSalesmanCode = $jks->salesman_code;
            
            $this->editHari = [
                'h1' => $jks->h1 === 'Y',
                'h2' => $jks->h2 === 'Y',
                'h3' => $jks->h3 === 'Y',
                'h4' => $jks->h4 === 'Y',
                'h5' => $jks->h5 === 'Y',
                'h6' => $jks->h6 === 'Y',
                'h7' => $jks->h7 === 'Y',
            ];

            $this->editMinggu = [
                'w1' => $jks->w1 === 'Y',
                'w2' => $jks->w2 === 'Y',
                'w3' => $jks->w3 === 'Y',
                'w4' => $jks->w4 === 'Y',
            ];

            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editId = null;
        $this->editReason = '';
    }

    public function saveJadwal()
    {
        if (!$this->editId) return;

        if (empty(trim($this->editSalesmanCode))) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Validasi Gagal: Pilihan Salesman tidak boleh kosong!']);
            return;
        }
        
        if (strlen(trim($this->editReason)) < 5) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Alasan edit harus diisi (minimal 5 karakter)!']);
            return;
        }

        try {
            $payload = [
                'id' => $this->editId,
                'update' => [
                    'salesman_code' => $this->editSalesmanCode,
                    'h1' => $this->editHari['h1'] ? 'Y' : 'T',
                    'h2' => $this->editHari['h2'] ? 'Y' : 'T',
                    'h3' => $this->editHari['h3'] ? 'Y' : 'T',
                    'h4' => $this->editHari['h4'] ? 'Y' : 'T',
                    'h5' => $this->editHari['h5'] ? 'Y' : 'T',
                    'h6' => $this->editHari['h6'] ? 'Y' : 'T',
                    'h7' => $this->editHari['h7'] ? 'Y' : 'T',
                    'w1' => $this->editMinggu['w1'] ? 'Y' : 'T',
                    'w2' => $this->editMinggu['w2'] ? 'Y' : 'T',
                    'w3' => $this->editMinggu['w3'] ? 'Y' : 'T',
                    'w4' => $this->editMinggu['w4'] ? 'Y' : 'T',
                    'reason' => $this->editReason,
                ]
            ];

            $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
            $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];

            $activeHari = [];
            foreach ($this->editHari as $key => $val) { if ($val) $activeHari[] = $hariNames[$key] ?? strtoupper($key); }
            $activeMinggu = [];
            foreach ($this->editMinggu as $key => $val) { if ($val) $activeMinggu[] = $weekNames[$key] ?? strtoupper($key); }
            
            $seName = $this->headerSalesmans->firstWhere('salesman_code', $this->editSalesmanCode)->salesman_name ?? $this->editSalesmanCode;

            $reasonFull = "Edit Jadwal Toko: {$this->editCustomerName} (SE: {$seName}) | Hari: [" . implode(', ', $activeHari) . "] | Minggu: [" . implode(', ', $activeMinggu) . "] | Alasan: " . $this->editReason;

            $this->handleJksAction('EDIT_INDIVIDU', $payload, $reasonFull, function() use ($payload) {
                \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->where('id', $payload['id'])
                    ->update(array_merge($payload['update'], ['updated_at' => now()]));
            }, 'Jadwal berhasil diperbarui!');

            $this->closeEditModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage()]);
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->deleteReason = '';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteReason = '';
    }

    public function executeDelete()
    {
        if (!$this->deleteId) return;

        if (strlen(trim($this->deleteReason)) < 5) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Alasan reset harus diisi (minimal 5 karakter)!']);
            return;
        }

        try {
            $payload = [
                'id' => $this->deleteId,
                'update' => [
                    'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                    'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                    'reason' => $this->deleteReason,
                ]
            ];

            $jks = \Illuminate\Support\Facades\DB::table('jks_salesmans as js')
                ->select('js.*', 'ltpte.customer_name')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                    $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                         ->on('js.customer_code', '=', 'ltpte.uniq_kd');
                })
                ->where('js.id', $this->deleteId)
                ->first();

            $customerName = $jks->customer_name ?? $jks->customer_code ?? '-';
            $salesmanCode = $jks->salesman_code ?? '-';
            $seName = $this->headerSalesmans->firstWhere('salesman_code', $salesmanCode)->salesman_name ?? $salesmanCode;
            
            $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
            $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];
            
            $activeHari = [];
            foreach (['h1','h2','h3','h4','h5','h6','h7'] as $h) { if (isset($jks->$h) && $jks->$h === 'Y') $activeHari[] = $hariNames[$h]; }
            $activeMinggu = [];
            foreach (['w1','w2','w3','w4'] as $w) { if (isset($jks->$w) && $jks->$w === 'Y') $activeMinggu[] = $weekNames[$w]; }

            $reasonFull = "Hapus Jadwal Toko: {$customerName} | SE: {$seName} | Hari: [" . implode(', ', $activeHari) . "] | Minggu: [" . implode(', ', $activeMinggu) . "] | Alasan: " . $this->deleteReason;

            $this->handleJksAction('DELETE_INDIVIDU', $payload, $reasonFull, function() use ($payload) {
                \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->where('id', $payload['id'])
                    ->update(array_merge($payload['update'], ['updated_at' => now()]));
            }, 'Jadwal berhasil dihapus (reset ke T)!');

            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal menghapus jadwal: ' . $e->getMessage()]);
        }
    }



    public function openCalendarModal($week)
    {
        $this->selectedWeekForCalendar = $week;
        $this->showCalendarModal = true;
    }

    public function closeCalendarModal()
    {
        $this->showCalendarModal = false;
        $this->selectedWeekForCalendar = null;
    }

    #[\Livewire\Attributes\Computed]
    public function calendarDays()
    {
        if (!$this->showCalendarModal) return collect([]);
        
        $bulan = $this->appliedBulan ?: date('Y-m');
        
        return \Illuminate\Support\Facades\DB::table('master_calender')
            ->where('date', 'like', $bulan . '%')
            ->orderBy('date')
            ->get();
    }

    public function render(JksSalesmanService $service)
    {
        $filters = $this->getAppliedFilters();
        $filters['salesman'] = $this->headerSalesman;
        $filters['hari'] = $this->selectedHari;
        $filters['minggu'] = $this->selectedMinggu;
        
        $data = $service->getFilteredPaginatedList($this->search, $filters, 100);
        
        $kpiSummary = $service->getKpiSummary($filters);
        $mingguDates = $service->getMingguDateRanges($filters['bulan'] ?? null);

        // Find collisions for the current page
        $collidingCustomerCodes = [];
        if (!empty($filters['bulan']) && count($data->items()) > 0) {
            $customerCodesOnPage = collect($data->items())->pluck('customer_code')->unique()->toArray();
            
            $collisions = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->select('customer_code')
                ->where('bulan', 'like', $filters['bulan'] . '%')
                ->whereIn('customer_code', $customerCodesOnPage)
                ->groupBy('customer_code')
                ->havingRaw('COUNT(DISTINCT salesman_code) > 1')
                ->pluck('customer_code')
                ->toArray();
                
            $collidingCustomerCodes = $collisions;
        }

        return view('livewire.call-plan.jks-salesmans.index', [
            'jksData' => $data,
            'kpiSummary' => $kpiSummary,
            'mingguDates' => $mingguDates,
            'collidingCustomerCodes' => $collidingCustomerCodes
        ]);
    }

    // ---------------------------------------------------------
    // COLLISION DETECTION
    // ---------------------------------------------------------
    public function showCollisionDetails($customerCode, $customerName)
    {
        $filters = $this->getAppliedFilters();
        if (empty($filters['bulan'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih bulan terlebih dahulu.']);
            return;
        }

        $this->collisionCustomerName = $customerName;
        
        // Fetch all schedules for this customer in this month
        $schedules = \Illuminate\Support\Facades\DB::table('jks_salesmans')
            ->where('bulan', 'like', $filters['bulan'] . '%')
            ->where('customer_code', $customerCode)
            ->join('salesmans', 'jks_salesmans.salesman_code', '=', 'salesmans.salesman_code')
            ->select('jks_salesmans.*', 'salesmans.salesman_name')
            ->get();

        $this->collisionDetails = $schedules->toArray();
        $this->showCollisionModal = true;
    }

    public function closeCollisionModal()
    {
        $this->showCollisionModal = false;
        $this->collisionDetails = [];
    }

}
