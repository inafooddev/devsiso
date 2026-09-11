<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncJksSeMasterTokoOolJob;

class CronSyncJksSeMasterTokoOolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-jks-se-master-toko-ool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data JKS SE Master Toko OOL dari tabel lokal via Cronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SyncJksSeMasterTokoOolJob::dispatchSync();
    }
}
