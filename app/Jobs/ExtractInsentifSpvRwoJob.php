<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ExtractInsentifSpvRwoJob implements ShouldQueue
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
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', '[X/X] Memulai rekap QTD Potensi RWO untuk bulan ' . $this->bulan);
                $batch->addLog('warning', '[X/X] Membersihkan data lama RWO bulan ' . $this->bulan);
            }
        }

        // 1. Bersihkan data bulan ini (agar aman jika job dire-run)
        DB::table('insentif_spv_rwo')->where('bulan', $this->bulan)->delete();

        // 2. Parsial Tahun dan Bulan
        // $this->bulan = '2026-08' -> tahun: 2026, bulan: 08
        $parts = explode('-', $this->bulan);
        $tahun = (int)$parts[0];
        $bulanAngka = $parts[1];
        
        // 3. Menghitung Kuartal dan Bulan Akumulasi
        $kuartal = ceil((int)$bulanAngka / 3);
        $awalBulanKuartal = (($kuartal - 1) * 3) + 1;
        
        $listBulanKuartal = [
            str_pad($awalBulanKuartal, 2, '0', STR_PAD_LEFT),
            str_pad($awalBulanKuartal + 1, 2, '0', STR_PAD_LEFT),
            str_pad($awalBulanKuartal + 2, 2, '0', STR_PAD_LEFT),
        ];

        $bulanAkumulasi = [];
        $datesAkumulasi = []; // Karena kolom bulan di tabel SO formatnya '2026-08-01'
        
        foreach ($listBulanKuartal as $b) {
            $bulanAkumulasi[] = $b;
            $datesAkumulasi[] = "{$tahun}-{$b}-01";
            if ($b === $bulanAngka) break;
        }
        
        $multiplier = count($bulanAkumulasi); // Pengali Prorata Target

        if (isset($batch)) {
            $batch->addLog('info', "[X/X] Akumulasi QTD (Multiplier: $multiplier) untuk daftar tanggal: " . implode(', ', $datesAkumulasi));
        }

        // 4. Subquery Sales QTD (Akumulasi penjualan sampai bulan filter)
        $subquerySales = DB::table('zv_so_per_toko_2026')
            ->whereIn('bulan', $datesAkumulasi)
            ->select('uniq_kd', DB::raw('SUM(neto) as total_neto_akumulasi'))
            ->groupBy('uniq_kd');

        // 5. Eksekusi Perhitungan
        $rekapPerCabang = DB::table('list_potensi_rwo as p')
            ->where('p.tahun', $tahun)
            ->where('p.kuartal', $kuartal)
            ->join('master_distributors as md', function($join) {
                $join->on(DB::raw('SUBSTRING(p.distributor_code, 3, 3)'), '=', 'md.branch_code');
            })
            ->leftJoinSub($subquerySales, 'so', function ($join) {
                $join->on('p.customer_code', '=', 'so.uniq_kd');
            })
            ->groupBy('p.distributor_code', 'md.branch_name')
            ->select([
                DB::raw("{$tahun} as tahun"),
                DB::raw("{$kuartal} as kuartal"),
                DB::raw("'{$this->bulan}' as bulan"),
                'p.distributor_code',
                'md.branch_name as cabang',
                DB::raw('COUNT(DISTINCT p.customer_code) as total_potensi'),
                DB::raw("SUM(
                    CASE 
                        WHEN COALESCE(so.total_neto_akumulasi, 0) >= ((p.total_target / 3) * {$multiplier}) 
                        THEN 1 
                        ELSE 0 
                    END
                ) as capai_target"),
                DB::raw("NOW() as created_at"),
                DB::raw("NOW() as updated_at")
            ])
            ->get();

        // 6. Insert ke tabel insentif_spv_rwo
        $dataToInsert = json_decode(json_encode($rekapPerCabang), true);
        
        if (!empty($dataToInsert)) {
            DB::table('insentif_spv_rwo')->insert($dataToInsert);
        }

        if (isset($batch)) {
            $batch->addLog('success', '[X/X] Sukses menghitung rekap QTD Potensi RWO!');
        }
    }
}
