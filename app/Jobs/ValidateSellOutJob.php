<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class ValidateSellOutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchId;
    protected $filters;

    public function __construct(int $batchId, array $filters)
    {
        $this->batchId = $batchId;
        $this->filters = $filters;
    }

    public function handle()
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        try {
            $batch->updateStatus('processing');
            $batch->addLog('info', 'Memulai validasi data...');

            // --- 1. Bangun Query Dasar (Hanya untuk validasi) ---
            $query = $this->buildBaseValidationQuery();

            // --- 2. Validasi Produk (Blocker) ---
            $batch->addLog('info', 'Mengecek Mapping produk...');
            $unmappedProducts = (clone $query)
                // 'd' adalah alias untuk product_masters, ini sudah benar
                ->whereNull('d.product_id') 
                ->whereNotNull('a.product_code')
                ->where('a.product_code', '!=', '')
                ->count(DB::raw('DISTINCT a.product_code'));

            if ($unmappedProducts > 0) {
                throw new \Exception("Ditemukan {$unmappedProducts} produk yang belum termaping. Proses dibatalkan.");
            }
            $batch->addLog('success', 'Semua produk sudah termapping.');

            // --- 3. Validasi Salesman (Blocker) ---
            $batch->addLog('info', 'Mengecek mapping salesman...');
            $unmappedSalesman = (clone $query)
                // [PERBAIKAN] Mengubah 'd.salesman_code' menjadi 'd_sales.salesman_code'
                ->whereNull('d_sales.salesman_code') 
                ->whereNotNull('a.salesman_code')
                ->where('a.salesman_code', '!=', '')
                ->count(DB::raw('DISTINCT a.salesman_code'));
            
            if ($unmappedSalesman > 0) {
                throw new \Exception("Ditemukan {$unmappedSalesman} salesman yang belum termaping. Proses dibatalkan.");
            }
            $batch->addLog('success', 'Semua salesman sudah termaping.');

            // [PERUBAHAN BARU] Hitung total baris yang akan dimasukkan
            $batch->addLog('info', 'Menghitung total baris yang akan diproses...');
            // Ambil query dasar (sebelum validasi whereNull) dan hitung jumlah barisnya
            $totalRowsToProcess = (clone $query)->count(); 
            
            $batch->update(['total_rows' => $totalRowsToProcess]); // Simpan ke batch
            $batch->addLog('info', "Ditemukan {$totalRowsToProcess} total baris data yang siap diproses.");


            // --- 4. Lolos Validasi, Kirim Job Insert ---
            $batch->addLog('info', 'Validasi selesai. Memulai proses pemindahan data...');
            ProcessSellOutJob::dispatch($this->batchId, $this->filters, $totalRowsToProcess); // Kirim total baris ke job berikutnya

        } catch (Throwable $e) {
            $batch->addLog('error', 'Validasi Gagal: ' . $e->getMessage());
            $batch->updateStatus('failed');
        }
    }

    /**
     * Query helper untuk validasi, join semua tabel terkait.
     */
    private function buildBaseValidationQuery()
    {
        $salesmanMappingsSub = DB::table('salesman_mappings')
            ->select('distributor_code', 'salesman_code_dist', DB::raw('MIN(salesman_code_prc) as salesman_code_prc'))
            ->groupBy('distributor_code', 'salesman_code_dist');
            
        $productMappingsSub = DB::table('product_mappings')
            ->select('distributor_code', 'product_code_dist', DB::raw('MIN(product_code_prc) as product_code_prc'))
            ->groupBy('distributor_code', 'product_code_dist');

        $query = DB::table('sales_invoice_distributor as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code')
            // Join untuk salesman dengan alias 'd_sales'
            ->leftJoinSub($salesmanMappingsSub, 'sm', function ($join) {
                $join->on('a.distributor_code', '=', 'sm.distributor_code')
                     ->on('a.salesman_code', '=', 'sm.salesman_code_dist');
            })
            ->leftJoin('salesmans as d_sales', 'sm.salesman_code_prc', '=', 'd_sales.salesman_code')
            // Join untuk produk dengan alias 'd'
            ->leftJoinSub($productMappingsSub, 'c', function ($join) {
                $join->on('a.distributor_code', '=', 'c.distributor_code')
                     ->on('a.product_code', '=', 'c.product_code_dist');
            })
            ->leftJoin('product_masters as d', 'c.product_code_prc', '=', 'd.product_id');

        // Terapkan Filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('b.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('b.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('a.distributor_code', $this->filters['distributorFilter']);
        }
        if (!empty($this->filters['monthFilter']) && !empty($this->filters['yearFilter'])) {
            $startDate = Carbon::create($this->filters['yearFilter'], $this->filters['monthFilter'], 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $query->whereBetween('a.invoice_date', [$startDate, $endDate]);
        }
        
        return $query;
    }
}

