<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateSellinPerCabangPerQtyJob;

class CronUpdateSellinPerCabangPerQtyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-sellin-per-cabang-per-qty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses ETL Ringkasan Sell-In Per Cabang Per Qty';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai job update sellin_per_cabang_per_qty...');
        UpdateSellinPerCabangPerQtyJob::dispatchSync();
        $this->info('Job update sellin_per_cabang_per_qty selesai.');
    }
}
