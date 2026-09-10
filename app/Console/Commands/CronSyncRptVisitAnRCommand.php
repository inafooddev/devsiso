<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncRptVisitAnRJob;

class CronSyncRptVisitAnRCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-rpt-visit-an-r';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data RPT Visit An R from API to database (via Cronicle)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SyncRptVisitAnRJob::dispatchSync();
    }
}
