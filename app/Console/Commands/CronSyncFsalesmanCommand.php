<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncFsalesmanJob;

class CronSyncFsalesmanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-fsalesman';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data Fsalesman dari API ke database via Cronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SyncFsalesmanJob::dispatchSync();
    }
}
