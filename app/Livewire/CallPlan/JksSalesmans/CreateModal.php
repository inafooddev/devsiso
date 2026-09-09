<?php

namespace App\Livewire\CallPlan\JksSalesmans;

use Livewire\Component;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\DB;

class CreateModal extends Component
{
    #[Reactive]
    public $appliedBulan;

    #[Reactive]
    public $appliedDistributor;

    public $salesmanCode = '';
    
    public $searchToko = '';
    public $searchResults = [];
    public $selectedTokos = []; // Array of ['code' => '...', 'name' => '...']

    public $hari = [
        'h1' => false, 'h2' => false, 'h3' => false, 'h4' => false,
        'h5' => false, 'h6' => false, 'h7' => false
    ];

    public $minggu = [
        'w1' => false, 'w2' => false, 'w3' => false, 'w4' => false
    ];

    public $reason = '';
    public $errorMessage = '';
    public $successMessage = '';

    public function updatedSearchToko()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        if (empty($this->appliedDistributor)) {
            $this->searchResults = [];
            return;
        }

        if (strlen($this->searchToko) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = DB::table('list_toko_pareto_team_elite')
            ->where('distributor_code', $this->appliedDistributor)
            ->where(function($q) {
                $q->where('customer_name', 'ilike', '%' . $this->searchToko . '%')
                  ->orWhere('uniq_kd', 'ilike', '%' . $this->searchToko . '%');
            })
            ->limit(10)
            ->get(['uniq_kd as customer_code', 'customer_name', 'customer_address', 'kecamatan', 'desa'])
            ->toArray();
    }

    public function selectToko($code, $name)
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        // Check if already selected
        $exists = collect($this->selectedTokos)->contains('code', $code);
        if (!$exists) {
            $this->selectedTokos[] = ['code' => $code, 'name' => $name];
        }
        $this->searchToko = '';
        $this->searchResults = [];
    }

    public function removeToko($code)
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->selectedTokos = array_filter($this->selectedTokos, function($t) use ($code) {
            return $t['code'] !== $code;
        });
    }

    public function submit()
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (empty($this->appliedBulan) || empty($this->appliedDistributor)) {
            $this->errorMessage = 'Pilih Bulan dan Distributor terlebih dahulu di filter utama.';
            return;
        }

        if (empty($this->salesmanCode)) {
            $this->errorMessage = 'Pilih Salesman terlebih dahulu.';
            return;
        }

        if (empty($this->selectedTokos)) {
            $this->errorMessage = 'Pilih minimal satu toko.';
            return;
        }

        $selectedHari = array_keys(array_filter($this->hari));
        $selectedMinggu = array_keys(array_filter($this->minggu));

        if (empty($selectedHari)) {
            $this->errorMessage = 'Pilih minimal satu hari kunjungan.';
            return;
        }

        if (empty($selectedMinggu)) {
            $this->errorMessage = 'Pilih minimal satu minggu kunjungan.';
            return;
        }

        // Collision Check (Hari + Minggu)
        $customerCodes = array_column($this->selectedTokos, 'code');
        
        $existingSchedules = DB::table('jks_salesmans')
            ->where('bulan', 'like', $this->appliedBulan . '%')
            ->whereIn('customer_code', $customerCodes)
            ->get();

        foreach ($this->selectedTokos as $toko) {
            // Find existing schedules for this toko
            $tokoSchedules = $existingSchedules->where('customer_code', $toko['code']);
            
            foreach ($tokoSchedules as $sch) {
                // Check if any selected day overlaps
                $dayOverlap = false;
                foreach ($selectedHari as $h) {
                    if ($sch->$h === 'Y') {
                        $dayOverlap = true;
                        break;
                    }
                }

                // Check if any selected week overlaps
                $weekOverlap = false;
                foreach ($selectedMinggu as $w) {
                    if ($sch->$w === 'Y') {
                        $weekOverlap = true;
                        break;
                    }
                }

                if ($dayOverlap && $weekOverlap) {
                    // Get Salesman Name
                    $conflictSalesmanName = DB::table('salesmans')->where('salesman_code', $sch->salesman_code)->value('salesman_name');
                    $this->errorMessage = "Gagal! Toko {$toko['name']} sudah memiliki jadwal kunjungan pada Hari & Minggu yang sama oleh {$conflictSalesmanName}.";
                    return;
                }
            }
        }

        // Prepare Payload
        $payload = [
            'bulan' => $this->appliedBulan . '-01', // Standardize to YYYY-MM-DD
            'distributor_code' => $this->appliedDistributor,
            'salesman_code' => $this->salesmanCode,
            'tokos' => $this->selectedTokos,
            'hari' => $selectedHari,
            'minggu' => $selectedMinggu,
            'reason' => $this->reason
        ];

        // Insert into jks_approvals
        DB::table('jks_approvals')->insert([
            'distributor_code' => $this->appliedDistributor,
            'action_type' => 'TAMBAH_JADWAL',
            'status' => 'PENDING',
            'maker_id' => auth()->id(),
            'reason' => $this->reason,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->successMessage = 'Penambahan jadwal berhasil diajukan dan menunggu Approval Atasan.';
        $this->dispatch('refresh-jks-table');
        
        // Reset form
        $this->reset(['salesmanCode', 'searchToko', 'searchResults', 'selectedTokos', 'reason']);
        foreach($this->hari as $k => $v) $this->hari[$k] = false;
        foreach($this->minggu as $k => $v) $this->minggu[$k] = false;
        
    }

    public function resetModal()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->reset(['salesmanCode', 'searchToko', 'searchResults', 'selectedTokos', 'reason']);
        foreach($this->hari as $k => $v) $this->hari[$k] = false;
        foreach($this->minggu as $k => $v) $this->minggu[$k] = false;
    }

    public function render()
    {
        $salesmans = collect();
        if (!empty($this->appliedDistributor)) {
            $salesmans = DB::table('salesmans')
                ->where('distributor_code', $this->appliedDistributor)
                ->get();
        }

        return view('livewire.call-plan.jks-salesmans.create-modal', [
            'salesmans' => $salesmans
        ]);
    }
}
