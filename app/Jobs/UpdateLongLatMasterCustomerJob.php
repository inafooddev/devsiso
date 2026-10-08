<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateLongLatMasterCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;

    public function __construct()
    {
        // Parameter jika diperlukan nantinya bisa ditambahkan di sini
    }

    public function handle(): void
    {
        try {
            $this->logMessage("INFO", "Memulai proses pembaruan Longitude & Latitude dari master eska ke list_toko_pareto_team_elite...");

            $updatedCount = DB::update("
                UPDATE list_toko_pareto_team_elite AS target
                SET latitude = src.la::numeric,
                    longitude = src.lg::numeric,
                    updated_at = NOW()
                FROM (
                    SELECT 
                        md.distributor_code,
                        cpe.custno,
                        cpe.la,
                        cpe.lg 
                    FROM customer_prc_eska cpe 
                    LEFT JOIN distributor_implementasi_eskalink die 
                        ON cpe.kodecabang = die.eskalink_code 
                    LEFT JOIN master_distributors md 
                        ON die.distributor_code = md.distributor_code 
                    WHERE md.is_active IS TRUE 
                      AND cpe.region_code <> 'HOINA'
                      AND cpe.la IS NOT NULL
                      AND cpe.la <> '0'
                      AND cpe.lg <> '0'
                ) AS src
                WHERE target.distributor_code = src.distributor_code
                  AND target.customer_code_prc = src.custno
            ");

            $this->logMessage("SUCCESS", "Pembaruan selesai! Sebanyak {$updatedCount} record berhasil di-update.");

        } catch (Exception $e) {
            $this->logMessage("ERROR", "Terjadi kesalahan saat pembaruan LongLat: " . $e->getMessage());
            throw $e;
        }
    }

    private function logMessage($type, $message)
    {
        if (app()->runningInConsole()) {
            $timestamp = now()->format('Y-m-d H:i:s');
            echo "[$timestamp] [$type] $message\n";
        } else {
            if ($type === "ERROR") {
                Log::error($message);
            } else {
                Log::info($message);
            }
        }
    }
}
