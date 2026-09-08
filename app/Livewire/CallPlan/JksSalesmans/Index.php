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

    public $search = '';
    public $headerSalesman = '';
    public $selectedHari = 'h1';
    public $selectedMinggu = 'ganjil';

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

    // State Delete
    public $showDeleteModal = false;
    public $deleteId = null;

    // State Swap (Tukar Jadwal)
    public $showSwapModal = false;
    public $swapTab = 'hari';
    public $swapSalesmanCode = '';
    public $swapHariAsal = '';
    public $swapHariTujuan = '';
    public $swapMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];

    public function openSwapModal()
    {
        $this->swapSalesmanCode = '';
        $this->swapHariAsal = '';
        $this->swapHariTujuan = '';
        $this->swapMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
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

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($selectedWeeks) {
                // Ambil baris yang relevan dengan hari asal ATAU tujuan, DAN mengandung minggu yang dipilih
                $rows = \Illuminate\Support\Facades\DB::table('jks_salesmans')
                    ->where('salesman_code', $this->swapSalesmanCode)
                    ->where('distributor_code', $this->appliedDistributor)
                    ->where(function($query) use ($selectedWeeks) {
                        foreach ($selectedWeeks as $week) {
                            $query->where($week, 'Y');
                        }
                    })
                    ->where(function($query) {
                        $query->where($this->swapHariAsal, 'Y')
                              ->orWhere($this->swapHariTujuan, 'Y');
                    })
                    ->get();

                foreach ($rows as $row) {
                    // Cek apakah ada minggu lain (selain yang dipilih) yang aktif (Y)
                    $unselectedWeeks = array_diff(['w1', 'w2', 'w3', 'w4'], $selectedWeeks);
                    $hasUnselected = false;
                    foreach ($unselectedWeeks as $uw) {
                        if ($row->$uw === 'Y') {
                            $hasUnselected = true;
                            break;
                        }
                    }

                    if ($hasUnselected) {
                        // ROW SPLITTING (Pecah Baris)
                        
                        // 1. Update baris lama: Matikan minggu-minggu yang dipilih (biarkan jadwal aslinya utuh untuk minggu lainnya)
                        $updateOriginal = ['updated_at' => now()];
                        foreach ($selectedWeeks as $sw) {
                            $updateOriginal[$sw] = 'T';
                        }
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->where('id', $row->id)
                            ->update($updateOriginal);

                        // 2. Buat baris baru: Khusus untuk minggu yang dipilih, dengan hari yang DITUKAR
                        $newRow = (array) $row;
                        unset($newRow['id']);
                        $newRow['created_at'] = now();
                        $newRow['updated_at'] = now();
                        
                        // Matikan minggu yang tidak dipilih di baris baru
                        foreach ($unselectedWeeks as $uw) {
                            $newRow[$uw] = 'T';
                        }
                        
                        // Eksekusi penukaran hari
                        $temp = $newRow[$this->swapHariAsal];
                        $newRow[$this->swapHariAsal] = $newRow[$this->swapHariTujuan];
                        $newRow[$this->swapHariTujuan] = $temp;
                        
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')->insert($newRow);

                    } else {
                        // NO SPLIT NEEDED (Semua minggu aktif adalah minggu yang dipilih)
                        \Illuminate\Support\Facades\DB::table('jks_salesmans')
                            ->where('id', $row->id)
                            ->update([
                                $this->swapHariAsal => $row->{$this->swapHariTujuan},
                                $this->swapHariTujuan => $row->{$this->swapHariAsal},
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

            $this->closeSwapModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pertukaran jadwal massal berhasil!']);
            
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pertukaran: ' . $e->getMessage()]);
        }
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
    }

    public function saveJadwal()
    {
        if (!$this->editId) return;

        if (empty(trim($this->editSalesmanCode))) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Validasi Gagal: Pilihan Salesman tidak boleh kosong!']);
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::table('jks_salesmans')
                ->where('id', $this->editId)
                ->update([
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
                    'updated_at' => now(),
                ]);

            $this->closeEditModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Jadwal berhasil diperbarui!']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage()]);
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function executeDelete()
    {
        if (!$this->deleteId) return;

        try {
            $deleted = \Illuminate\Support\Facades\DB::table('jks_salesmans')->where('id', $this->deleteId)->delete();
            
            if (!$deleted) {
                $this->closeDeleteModal();
                $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal: Data jadwal tidak ditemukan atau sudah dihapus.']);
                return;
            }

            $this->closeDeleteModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Jadwal berhasil dihapus!']);
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

        return view('livewire.call-plan.jks-salesmans.index', [
            'jksData' => $data,
            'kpiSummary' => $kpiSummary,
            'mingguDates' => $mingguDates
        ]);
    }
}
