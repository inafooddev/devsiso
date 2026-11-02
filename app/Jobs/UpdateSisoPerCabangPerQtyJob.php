<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;
use Carbon\Carbon;

class UpdateSisoPerCabangPerQtyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $selectedMonth;
    public $timeout = 3600;

    /**
     * @param int|null $batchId
     * @param string|null $selectedMonth Format: Y-m-d atau Y-m (contoh: '2026-10-01' atau '2026-10'). Jika null / 'ALL', memproses seluruh data historis.
     */
    public function __construct($batchId = null, $selectedMonth = null)
    {
        $this->batchId = $batchId;
        $this->selectedMonth = $selectedMonth;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
        }

        // Tentukan apakah filter bulan spesifik aktif
        $targetMonth = null;
        if (!empty($this->selectedMonth) && strtoupper($this->selectedMonth) !== 'ALL') {
            $targetMonth = Carbon::parse($this->selectedMonth)->format('Y-m-01');
        }

        $overallStart = microtime(true);
        $periodLabel = $targetMonth ? "Periode {$targetMonth}" : "Seluruh Data Historis";

        try {
            // ==========================================
            // BAGIAN 1: PROSES SELL-IN PER CABANG PER QTY
            // ==========================================
            if ($batch) {
                $batch->addLog('info', "[1/2] Memulai agregasi Sell-In Per Cabang Per Qty ({$periodLabel})...");
            }

            $siStart = microtime(true);

            if ($targetMonth) {
                if ($batch) {
                    $batch->addLog('warning', "Menghapus data lama bulan {$targetMonth} di sellin_per_cabang_per_qty...");
                }
                DB::connection('pgsql')->table('sellin_per_cabang_per_qty')->where('bulan', $targetMonth)->delete();

                $querySellin = <<<SQL
INSERT INTO sellin_per_cabang_per_qty (
    bulan,
    region,
    area,
    cabang,
    kd_distributor,
    nama_produk_mapping,
    qty_ctn,
    value_net,
    created_at,
    updated_at
)
SELECT 
    si.bulan,
    si.region,
    si.area,
    si.cabang,
    si.kd_distributor,
    si.nama_produk_mapping,
    SUM(si.ktn_net) AS qty_ctn,
    SUM(si.value_net) AS value_net,
    NOW(),
    NOW()
FROM selling_in si 
WHERE si.bulan = '{$targetMonth}'
GROUP BY 
    si.bulan,
    si.region,
    si.area,
    si.cabang,
    si.kd_distributor,
    si.nama_produk_mapping;
SQL;
            } else {
                if ($batch) {
                    $batch->addLog('warning', 'Membersihkan seluruh data lama di sellin_per_cabang_per_qty (TRUNCATE)...');
                }
                $querySellin = <<<'SQL'
TRUNCATE TABLE sellin_per_cabang_per_qty;

INSERT INTO sellin_per_cabang_per_qty (
    bulan,
    region,
    area,
    cabang,
    kd_distributor,
    nama_produk_mapping,
    qty_ctn,
    value_net,
    created_at,
    updated_at
)
SELECT 
    si.bulan,
    si.region,
    si.area,
    si.cabang,
    si.kd_distributor,
    si.nama_produk_mapping,
    SUM(si.ktn_net) AS qty_ctn,
    SUM(si.value_net) AS value_net,
    NOW(),
    NOW()
FROM selling_in si 
GROUP BY 
    si.bulan,
    si.region,
    si.area,
    si.cabang,
    si.kd_distributor,
    si.nama_produk_mapping;
SQL;
            }

            DB::connection('pgsql')->unprepared($querySellin);

            $siDuration = round(microtime(true) - $siStart, 2);
            $siCount = $targetMonth 
                ? DB::connection('pgsql')->table('sellin_per_cabang_per_qty')->where('bulan', $targetMonth)->count()
                : DB::connection('pgsql')->table('sellin_per_cabang_per_qty')->count();

            if ($batch) {
                $batch->addLog('success', "Sukses Sell-In ({$periodLabel})! Total " . number_format($siCount, 0, ',', '.') . " baris tersimpan dalam {$siDuration} detik.");
            }

            // ==========================================
            // BAGIAN 2: PROSES SELL-OUT PER CABANG PER QTY (DARI SQL SERVER)
            // ==========================================
            if ($batch) {
                $batch->addLog('info', "[2/2] Memulai penarikan data Sell-Out dari SQL Server ({$periodLabel})...");
            }

            $soStart = microtime(true);

            if ($targetMonth) {
                if ($batch) {
                    $batch->addLog('warning', "Menghapus data lama bulan {$targetMonth} di sellout_per_cabang_per_qty...");
                }
                DB::connection('pgsql')->table('sellout_per_cabang_per_qty')->where('bulan', $targetMonth)->delete();

                $whereClauseSqlSrv = "WHERE so.BLN = '{$targetMonth}'";
            } else {
                if ($batch) {
                    $batch->addLog('warning', 'Membersihkan seluruh data lama di sellout_per_cabang_per_qty (TRUNCATE)...');
                }
                DB::connection('pgsql')->table('sellout_per_cabang_per_qty')->truncate();
                $whereClauseSqlSrv = "";
            }

            if ($batch) {
                $batch->addLog('info', 'Mengeksekusi agregasi langsung pada engine SQL Server...');
            }

            $querySellout = <<<SQL
SELECT 
    so.BLN AS bulan,
    so.REGION AS region,
    so.AREA AS area,
    so.CABANG AS cabang,
    so.KD_DISTRIBUTOR AS kd_distributor,
    so.MAPING_PRODUK AS nama_produk_mapping,
    so.REG_FEST AS reg_fest,
    SUM(so.[QTY_(KTN)]) AS qty_ctn,
    SUM(so.[VALUE_(NETTO)]) AS value_net
FROM selling_out so
{$whereClauseSqlSrv}
GROUP BY
    so.BLN,
    so.REGION,
    so.AREA,
    so.CABANG,
    so.KD_DISTRIBUTOR,
    so.MAPING_PRODUK,
    so.REG_FEST
SQL;

            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare($querySellout);
            $stmt->execute();

            if ($batch) {
                $batch->addLog('info', 'Streaming data & batch inserting ke PostgreSQL (per 2.000 baris)...');
            }

            $batchData = [];
            $batchSize = 2000;
            $totalSoInserted = 0;
            $now = date('Y-m-d H:i:s');

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $cabang = $row['cabang'];
                if ($cabang === 'METRO') {
                    $cabang = 'KOTA METRO';
                }

                $regFest = $row['reg_fest'];
                if (strcasecmp($regFest, 'fest') === 0) {
                    $regFest = 'FEST';
                } elseif (strcasecmp($regFest, 'reg') === 0) {
                    $regFest = 'REG';
                }

                $batchData[] = [
                    'bulan' => $row['bulan'] ? substr($row['bulan'], 0, 10) : null,
                    'region' => $row['region'],
                    'area' => $row['area'],
                    'cabang' => $cabang,
                    'kd_distributor' => $row['kd_distributor'],
                    'nama_produk_mapping' => $row['nama_produk_mapping'],
                    'reg_fest' => $regFest,
                    'qty_ctn' => $row['qty_ctn'] ?? 0,
                    'value_net' => $row['value_net'] ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batchData) >= $batchSize) {
                    DB::connection('pgsql')->table('sellout_per_cabang_per_qty')->insert($batchData);
                    $totalSoInserted += count($batchData);
                    $batchData = [];
                }
            }

            if (!empty($batchData)) {
                DB::connection('pgsql')->table('sellout_per_cabang_per_qty')->insert($batchData);
                $totalSoInserted += count($batchData);
            }

            $soDuration = round(microtime(true) - $soStart, 2);

            if ($batch) {
                $batch->addLog('success', "Sukses Sell-Out ({$periodLabel})! Total " . number_format($totalSoInserted, 0, ',', '.') . " baris tersimpan dalam {$soDuration} detik.");
            }

            // ==========================================
            // FINALISASI
            // ==========================================
            $totalDuration = round(microtime(true) - $overallStart, 2);

            if ($batch) {
                $batch->addLog('success', "SELURUH PROSES SISO PER CABANG PER QTY ({$periodLabel}) SELESAI! Total durasi: {$totalDuration} detik.");
                $batch->update(['status' => 'completed']);
            }

        } catch (\Throwable $e) {
            if ($batch) {
                $batch->addLog('error', 'Terjadi kesalahan: ' . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            throw $e;
        }
    }
}
