<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncFruteJob;

class CronSyncFruteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-frute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data Frute dari API ke database via Cronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SyncFruteJob::dispatchSync();
    }
}
