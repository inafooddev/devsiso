<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncCustomerEskaJob;

class CronSyncCustomerEskaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-customer-eska {--region=all : Pilih spesifik region jika perlu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan Sync Customer Eska secara synchronous untuk keperluan Scheduler/Chronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Memulai eksekusi Cron Sync Customer Eska...');
        
        $regionOpt = $this->option('region') ?: 'all';
        $this->info("Target region: {$regionOpt}");

        // Eksekusi job secara SYNCHRONOUS
        // Sehingga proses Chronicle akan menahan log/terminal hingga proses benar-benar selesai
        try {
            SyncCustomerEskaJob::dispatchSync($regionOpt);
            $this->info('[' . now()->format('Y-m-d H:i:s') . '] Eksekusi selesai dengan sukses.');
        } catch (\Exception $e) {
            $this->error('[' . now()->format('Y-m-d H:i:s') . '] Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
