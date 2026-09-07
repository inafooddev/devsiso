<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateApplicationJob;

class CronUpdateApplicationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan proses Update Application (git pull, npm build, optimize clear)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Memulai eksekusi Cron Update Application...');
        
        try {
            UpdateApplicationJob::dispatchSync();
            $this->info('[' . now()->format('Y-m-d H:i:s') . '] Eksekusi selesai dengan sukses.');
        } catch (\Exception $e) {
            $this->error('[' . now()->format('Y-m-d H:i:s') . '] Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
