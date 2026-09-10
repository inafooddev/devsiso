<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncRptVisitAnHJob;

class CronSyncRptVisitAnHCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-rpt-visit-an-h';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan proses sinkronisasi API RPT_ANH ke database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Memulai eksekusi Cron Sync RPT Visit An H...');
        
        try {
            SyncRptVisitAnHJob::dispatchSync();
            $this->info('[' . now()->format('Y-m-d H:i:s') . '] Eksekusi selesai dengan sukses.');
        } catch (\Exception $e) {
            $this->error('[' . now()->format('Y-m-d H:i:s') . '] Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
