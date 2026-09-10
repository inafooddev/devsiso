<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ZvSummaryTeamEliteJob;

class CronZvSummaryTeamEliteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:zv-summary-team-elite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses ETL ZV Summary Team Elite via Cronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ZvSummaryTeamEliteJob::dispatchSync();
    }
}
