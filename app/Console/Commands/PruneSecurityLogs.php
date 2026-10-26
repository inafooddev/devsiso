<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecurityIncident;
use App\Models\BlockedIp;

class PruneSecurityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:prune-logs {--days=30 : Jumlah hari penyimpanan log sebelum dihapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan riwayat insiden keamanan lama dan hapus IP terblokir yang sudah kedaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Membersihkan log insiden keamanan yang lebih lama dari {$days} hari (sebelum {$cutoffDate->toDateTimeString()})...");
        $deletedIncidents = SecurityIncident::where('created_at', '<', $cutoffDate)->delete();
        $this->info("Berhasil menghapus {$deletedIncidents} catatan insiden lama.");

        // Bersihkan juga IP yang sudah expired
        $this->info("Membersihkan daftar IP terblokir yang masa berlakunya sudah habis...");
        $expiredIps = BlockedIp::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredIps as $blockedIp) {
            BlockedIp::unblockIp($blockedIp->ip_address);
        }

        $this->info("Berhasil membuka blokir {$expiredIps->count()} IP yang telah kedaluwarsa.");

        return Command::SUCCESS;
    }
}
