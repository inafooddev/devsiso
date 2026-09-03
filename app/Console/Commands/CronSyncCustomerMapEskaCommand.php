<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncCustomerMapEskaJob;

class CronSyncCustomerMapEskaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-customer-map-eska';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan Sync Customer Map Eska secara synchronous untuk keperluan Scheduler/Chronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Memulai eksekusi Cron Sync Customer Map Eska...');
        
        try {
            SyncCustomerMapEskaJob::dispatchSync();
            $this->info('[' . now()->format('Y-m-d H:i:s') . '] Eksekusi selesai dengan sukses.');
        } catch (\Exception $e) {
            $this->error('[' . now()->format('Y-m-d H:i:s') . '] Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
