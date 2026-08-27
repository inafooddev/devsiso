<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JksSyncWilayahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 jam maksimal
    protected $jobId;

    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }

    public function handle(): void
    {
        $cacheKey = "sync_wilayah_progress_{$this->jobId}";
        
        Cache::put($cacheKey, [
            'status' => 'processing',
            'processed' => 0,
            'total' => 0,
            'updated' => 0,
            'message' => 'Menghitung total data toko dengan koordinat...',
        ], 3600);

        try {
            // Kita hitung dulu berapa data yang punya koordinat
            $totalData = DB::table('list_toko_pareto_team_elite')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->count();
            
            if ($totalData == 0) {
                Cache::put($cacheKey, [
                    'status' => 'completed',
                    'processed' => 0,
                    'total' => 0,
                    'updated' => 0,
                    'message' => 'Tidak ada data toko yang memiliki koordinat valid.',
                ], 3600);
                return;
            }

            Cache::put($cacheKey, [
                'status' => 'processing',
                'processed' => 0,
                'total' => $totalData,
                'updated' => 0,
                'message' => "Memproses {$totalData} data koordinat... (Query Spasial Sedang Berjalan, Mohon Tunggu)",
            ], 3600);

            // Menjalankan bulk update secara bertahap (Chunking) agar progress bisa terlihat live
            $processed = 0;
            $updatedRows = 0;
            
            DB::table('list_toko_pareto_team_elite')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->orderBy('id')
                ->chunk(100, function ($rows) use (&$processed, &$updatedRows, $totalData, $cacheKey) {
                    $ids = $rows->pluck('id')->toArray();
                    $idString = implode(',', $ids);
                    
                    $updated = DB::update("
                        UPDATE list_toko_pareto_team_elite t
                        SET 
                            kabupaten = b.wadmkk,
                            kecamatan = b.wadmkc,
                            desa      = b.wadmkd
                        FROM batas_wilayah b
                        WHERE t.id IN ($idString)
                          AND ST_Contains(b.geom, ST_SetSRID(ST_Point(CAST(t.longitude AS float), CAST(t.latitude AS float)), 4326))
                    ");
                    
                    $processed += count($ids);
                    $updatedRows += $updated;
                    
                    Cache::put($cacheKey, [
                        'status' => 'processing',
                        'processed' => $processed,
                        'total' => $totalData,
                        'updated' => $updatedRows,
                        'message' => "Memproses {$processed} dari {$totalData} data koordinat...",
                    ], 3600);
                });

            Cache::put($cacheKey, [
                'status' => 'completed',
                'processed' => $totalData,
                'total' => $totalData,
                'updated' => $updatedRows,
                'message' => "Sukses! {$updatedRows} toko berhasil disinkronkan wilayahnya.",
            ], 3600);

            Log::info("JksSyncWilayahJob ID {$this->jobId} completed successfully. Updated {$updatedRows} rows.");

        } catch (\Exception $e) {
            Log::error("JksSyncWilayahJob Error: " . $e->getMessage());
            
            Cache::put($cacheKey, [
                'status' => 'error',
                'processed' => 0,
                'total' => 0,
                'updated' => 0,
                'message' => "Error: " . $e->getMessage(),
            ], 3600);
        }
    }
}
