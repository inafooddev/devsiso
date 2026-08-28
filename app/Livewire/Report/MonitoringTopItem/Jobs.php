<?php

namespace App\Livewire\Report\MonitoringTopItem;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\TopItemMasterCustomer;

class Jobs extends Component
{
    public $logLines = [];
    public $batchStatus = 'idle';
    public $progress = 0;
    public $currentTask = '';

    public function mount()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function syncLog()
    {
        // For UI polling, if we were using a real background job we'd fetch from DB.
        // For simple synchronous/semi-synchronous processing in Livewire, we just rely on component state updates.
    }

    public function startProcess()
    {
        if (!auth()->user()->hasRole('admin')) {
            $this->addLog('error', 'Akses ditolak.');
            return;
        }

        $this->logLines = [];
        $this->progress = 5;
        $this->batchStatus = 'processing';
        
        $this->addLog('info', 'Memulai proses sinkronisasi...');
        $this->currentTask = 'Menyiapkan terminal eksekusi...';
        
        // Dispatch event to frontend to trigger the next step, allowing UI to update
        $this->dispatch('run-job-1');
    }

    #[\Livewire\Attributes\On('run-job-1')]
    public function executeJob1()
    {
        if ($this->batchStatus === 'failed') return;

        try {
            $this->addLog('info', 'Memulai Job 1: Sinkronisasi Master Customer...');
            $this->currentTask = 'Truncate tabel top_item_master_customer...';
            $this->progress = 15;
            
            DB::statement('TRUNCATE TABLE top_item_master_customer');

            $this->addLog('info', 'Menjalankan query insert master customer...');
            $this->currentTask = 'Mengambil dan menyimpan data unik dari t_sellingout...';
            $this->progress = 30;
            
            $query = "
                INSERT INTO top_item_master_customer (distributor_code, uniq_code, custno, customer_name, address, created_at, updated_at)
                SELECT DISTINCT ON (ts.\"KDDIST\", ts.\"KDUNIQ\", ts.\"CUSTNO\")
                    ts.\"KDDIST\" as distributor_code,
                    ts.\"KDUNIQ\" as uniq_code,
                    ts.\"CUSTNO\" as custno,
                    ts.\"CUSTNAME\" as customer_name,
                    ts.\"ALAMAT\" as address,
                    NOW(),
                    NOW()
                FROM t_sellingout ts
                WHERE ts.\"KDDIST\" IS NOT NULL 
                  AND ts.\"KDUNIQ\" IS NOT NULL 
                  AND ts.\"CUSTNO\" IS NOT NULL
            ";

            DB::statement($query);

            $this->addLog('success', 'Job 1: Master Customer selesai.');
            $this->progress = 50;

            // Trigger next step
            $this->dispatch('run-job-2');

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    #[\Livewire\Attributes\On('run-job-2')]
    public function executeJob2()
    {
        if ($this->batchStatus === 'failed') return;

        try {
            $this->addLog('info', 'Memulai Job 2: Pemrosesan Data Achievement...');
            $this->currentTask = 'Truncate tabel top_item_achievement...';
            $this->progress = 65;
            
            DB::statement('TRUNCATE TABLE top_item_achievement');

            $this->addLog('info', 'Menjalankan query agregasi...');
            $this->currentTask = 'Menyimpan data agregasi ke tabel achievement...';
            $this->progress = 80;

            $query2 = "
                INSERT INTO top_item_achievement (period, distributor_code, uniq_code, pcode_prc, qty, value, created_at, updated_at)
                SELECT 
                    TO_DATE(ts.\"THN\" || '-' || ts.\"BLN\" || '-01', 'YYYY-MM-DD') as period,
                    ts.\"KDDIST\" as distributor_code,
                    ts.\"KDUNIQ\" as uniq_code,
                    ts.\"KDITEMPRC\" as pcode_prc,
                    SUM(ts.\"TTL_QTY_KTN\") as qty,
                    SUM(ts.\"NETT\") as value,
                    NOW(),
                    NOW()
                FROM t_sellingout ts 
                WHERE ts.\"KDDIST\" IS NOT NULL 
                  AND ts.\"KDUNIQ\" IS NOT NULL 
                  AND ts.\"KDITEMPRC\" IS NOT NULL
                  AND ts.\"THN\" IS NOT NULL
                  AND ts.\"BLN\" IS NOT NULL
                GROUP BY 
                    ts.\"THN\",
                    ts.\"BLN\",
                    ts.\"KDDIST\",
                    ts.\"KDUNIQ\",
                    ts.\"KDITEMPRC\"
            ";

            DB::statement($query2);

            $this->progress = 100;
            $this->batchStatus = 'success';
            $this->currentTask = 'Selesai';
            $this->addLog('success', 'Job 2: Data Achievement berhasil diselesaikan.');
            $this->addLog('success', 'Semua proses sinkronisasi telah selesai.');

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function handleError(\Exception $e)
    {
        $this->batchStatus = 'failed';
        $this->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
        $this->currentTask = 'Gagal';
    }

    private function addLog($type, $message)
    {
        $this->logLines[] = [
            'type' => $type,
            'message' => $message,
            'time' => now()->format('H:i:s')
        ];
    }

    public function render()
    {
        return view('livewire.report.monitoring-top-item.jobs');
    }
}
