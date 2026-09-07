<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateAoListTokoJob;

class CronUpdateAoListTokoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-ao-list-toko';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan Update AO List Toko secara synchronous untuk Cronicle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Memulai eksekusi Cron Update AO List Toko...');
        
        try {
            UpdateAoListTokoJob::dispatchSync();
            $this->info('[' . now()->format('Y-m-d H:i:s') . '] Eksekusi selesai dengan sukses.');
        } catch (\Exception $e) {
            $this->error('[' . now()->format('Y-m-d H:i:s') . '] Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
