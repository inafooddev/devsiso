<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateSisoPerCabangPerQtyJob;
use Carbon\Carbon;

class CronUpdateSisoPerCabangPerQtyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-siso-per-cabang-per-qty 
                            {--month= : Periode bulan spesifik (contoh: 2026-10 atau 2026-10-01). Default: bulan berjalan}
                            {--all : Update seluruh data historis (Full Refresh)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses ETL Pipeline SISO (Sell-In & Sell-Out) Per Cabang Per Qty';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $month = 'ALL';
            $this->info("Menjalankan update SISO untuk SELURUH DATA HISTORIS...");
        } else {
            $month = $this->option('month') ?: Carbon::now()->format('Y-m-01');
            $this->info("Menjalankan update SISO untuk PERIODE BULAN: {$month}...");
        }

        UpdateSisoPerCabangPerQtyJob::dispatchSync(null, $month);

        $this->info("Selesai! Data SISO per cabang per qty berhasil diperbarui.");
    }
}
