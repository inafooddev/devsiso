<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateLongLatMasterCustomerJob;

class UpdateLongLatMasterCustomerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:longlat-customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan update longitude dan latitude master customer dari eska ke pareto team elite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Menyiapkan Job Update LongLat...");
        UpdateLongLatMasterCustomerJob::dispatchSync();
        $this->info("Proses selesai dieksekusi!");
    }
}
