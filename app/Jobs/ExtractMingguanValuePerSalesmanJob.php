<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ImportBatch;

class ExtractMingguanValuePerSalesmanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bulan; // Format: YYYY-MM
    public $batchId;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct($bulan, $batchId = null)
    {
        $this->bulan = $bulan;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $carbonBulan = Carbon::createFromFormat('Y-m', $this->bulan);
        $startDate = $carbonBulan->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $carbonBulan->copy()->endOfMonth()->format('Y-m-d');

        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', '[3/X] Memulai perhitungan Actual Value per Salesman Mingguan...');
                $batch->addLog('warning', '[3/X] Membersihkan data lama Value per Salesman Mingguan untuk bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini
        DB::table('insentif_mingguan_value_per_salesmans')->where('bulan', $this->bulan)->delete();

        if (isset($batch)) {
            $batch->addLog('info', '[3/X] Mengeksekusi kueri agregasi (SUM) Actual Value Mingguan...');
        }

        // 2. Eksekusi Raw Query
        $query = "
            INSERT INTO insentif_mingguan_value_per_salesmans (
                bulan, 
                distributor_code, 
                sales_code, 
                actual, 
                created_at, 
                updated_at
            )
            SELECT 
                ? as bulan,
                ts.\"KDDIST\" as distributor_code,
                ts.\"SLSNO_PRC\" as sales_code,
                SUM(ts.\"NETT\") as actual,
                NOW() as created_at,
                NOW() as updated_at
            FROM t_sellingout ts 
            WHERE ts.\"INVOICE_DATE\" BETWEEN ? AND ?
              AND ts.\"KDDIST\" IS NOT NULL
              AND ts.\"SLSNO_PRC\" IS NOT NULL
              AND ts.\"REG_FEST\" = 'REG'
            GROUP BY ts.\"KDDIST\", ts.\"SLSNO_PRC\"
        ";

        DB::statement($query, [$this->bulan, $startDate, $endDate]);

        if (isset($batch)) {
            $batch->addLog('success', '[3/X] Sukses menghitung Actual Value per Salesman Mingguan!');
        }
    }
}
