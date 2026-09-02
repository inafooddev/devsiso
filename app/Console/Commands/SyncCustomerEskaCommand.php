<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncCustomerEskaJob;

class SyncCustomerEskaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eskalink:sync-customer {--region= : Region khusus yang ingin disync, contoh: CSTINAJWA1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Customer Eska dari API per region atau semua region';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $regions = [
            'CSTINAJWA1',
            'CSTINAJWA2',
            'CSTINAPUL1',
            'CSTINASUM1',
            'CSTINASUM2',
        ];

        $regionOpt = $this->option('region');

        if ($regionOpt) {
            if (!in_array($regionOpt, $regions)) {
                $this->error("Region {$regionOpt} tidak valid!");
                return;
            }
            $this->info("Dispatching job untuk region: {$regionOpt}");
            SyncCustomerEskaJob::dispatch($regionOpt);
        } else {
            $this->info("Dispatching job untuk semua region...");
            foreach ($regions as $region) {
                SyncCustomerEskaJob::dispatch($region);
            }
        }

        $this->info("Semua job telah didispatch ke queue!");
    }
}
