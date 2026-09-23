<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateIsDiscountEskalinkJob;

class CronUpdateIsDiscountEskalinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-is-discount-eskalink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update kolom is_discount (Y/N) pada tabel selling_out_eskalink';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateIsDiscountEskalinkJob::dispatchSync();
    }
}
