<?php

namespace App\Services;

use App\Models\DistributorStock;
use App\Models\MasterDistributor;
use App\Models\ProductMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesDiagnosticService
{
    /**
     * Ambang batas default (Thresholds)
     */
    public const DOI_CRITICAL_MIN = 7.0;    // Di bawah 7 hari: Darurat Stockout
    public const DOI_OVERSTOCK_MAX = 60.0;  // Di atas 60 hari: Gudang Banjir (Channel Stuffing)
    public const MIN_OVERSTOCK_KTN = 100.0; // Minimal 100 karton sisa stok untuk dikategorikan Gudang Banjir
    public const SERAPAN_MIN_RATIO = 0.30;  // Di bawah 30% serapan: Over-push
    public const VARIANCE_TOLERANCE = 2.0;  // Toleransi selisih karton (misal pembulatan atau pecahan kecil)

    /**
     * Hitung diagnosa lengkap untuk satu pasang data distributor + produk
     */
    public function diagnose(
        float $stockAwalKtn,
        float $sellInKtn,
        float $sellOutKtn,
        float $stockFisikUploadKtn,
        int $daysInMonth = 30,
        float $totalValue = 0.0
    ): array {
        // 1. Hitung Stok Sistem (Teoritis)
        $ketersediaan = $stockAwalKtn + $sellInKtn;
        $stockTeoritis = $ketersediaan - $sellOutKtn;
        $selisih = $stockFisikUploadKtn - $stockTeoritis;

        // 2. Hitung Kecepatan Penjualan Harian & DOI
        $runRateHarian = $daysInMonth > 0 ? ($sellOutKtn / $daysInMonth) : 0.0;
        
        if ($runRateHarian > 0) {
            $doiDays = round($stockFisikUploadKtn / $runRateHarian, 1);
        } elseif ($stockFisikUploadKtn > 0) {
            // Ada stok tapi penjualan 0
            $doiDays = 999.0;
        } else {
            $doiDays = 0.0;
        }

        $serapanRatio = $ketersediaan > 0 ? ($sellOutKtn / $ketersediaan) : 0.0;

        // 3. Tentukan Status Aliran & Rekomendasi Sales
        $status = 'HEALTHY';
        $statusLabel = 'Aliran Sehat';
        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        $recommendation = 'ALIRAN SEHAT. Pertahankan ritme pasokan replenishment reguler.';

        // Hierarki Deteksi Masalah (POV Sales Prioritas):
        if ($sellOutKtn > $ketersediaan && $ketersediaan >= 0) {
            // Pola 1: Phantom Sales (Penjualan Melebihi Total Barang)
            $status = 'PHANTOM_SALES';
            $statusLabel = 'Penjualan Aneh (Melebihi Pasokan)';
            $badgeClass = 'bg-red-50 text-red-700 border-red-200';
            $recommendation = 'AUDIT NOTA TOKO! Penjualan melebihi ketersediaan barang. Verifikasi 5 faktur toko terbesar & tahan klaim reward/insentif.';
        } elseif ($sellOutKtn <= 0 && ($ketersediaan > 0 || $stockFisikUploadKtn > 0)) {
            // Pola 2: Dead / Zombie SKU
            $status = 'DEAD_SKU';
            $statusLabel = 'Dead SKU (0 Penjualan)';
            $badgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
            $recommendation = 'CEK DISPLAY TOKO! Ada stok tapi 0 penjualan. Periksa ketersediaan di rak toko atau siapkan retur/relokasi produk.';
        } elseif ($stockTeoritis >= self::MIN_OVERSTOCK_KTN && $doiDays > self::DOI_OVERSTOCK_MAX) {
            // Pola 3: Channel Stuffing (Gudang Banjir: Sisa Stok >= 100 ktn & DOI > 60 hari)
            $status = 'CHANNEL_STUFFING';
            $statusLabel = 'Over-Stock (Gudang Banjir)';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
            $recommendation = 'STOP SELL IN SEMENTARA! Gudang distributor kepenuhan (DOI ' . round($doiDays) . ' hari). Tahan PO baru & buat program dorong sell-out toko.';
        } elseif ($stockTeoritis >= 0 && $doiDays < self::DOI_CRITICAL_MIN && $sellOutKtn > 0) {
            // Pola 4: Stockout Risk (Kritis Mau Habis / Sudah Habis)
            $status = 'STOCKOUT_RISK';
            $statusLabel = 'Darurat Stok (Kritis Habis)';
            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
            $recommendation = 'PRIORITAS PO KILAT! Stok tersisa < 7 hari sedangkan serapan kencang. Segera terbitkan PO darurat dan jadwalkan pengiriman dari pabrik.';
        } elseif ($selisih < -self::VARIANCE_TOLERANCE) {
            // Pola 5A: Selisih Minus (Fisik Upload < Stok Sistem)
            $status = 'VARIANCE_MINUS';
            $statusLabel = 'Selisih Minus (Potensi Penjualan Gelap)';
            $badgeClass = 'bg-orange-50 text-orange-700 border-orange-200';
            $recommendation = 'CEK NOTA MANUAL! Stok fisik kurang dari hitungan sistem. Selidiki faktur manual yang belum ter-entry atau rembesan barang keluar tanpa nota.';
        } elseif ($selisih > self::VARIANCE_TOLERANCE) {
            // Pola 5B: Selisih Plus (Fisik Upload > Stok Sistem)
            $status = 'VARIANCE_PLUS';
            $statusLabel = 'Selisih Lebih (Potensi Pasokan Siluman)';
            $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
            $recommendation = 'CEK ASAL-USUL BARANG! Fisik upload melebihi sistem. Selidiki potensi pasokan lintas area (cross-border) atau retur fisik tanpa dokumen.';
        }

        return [
            'stock_awal_ktn'         => round($stockAwalKtn, 2),
            'sellin_ktn'             => round($sellInKtn, 2),
            'sellout_ktn'            => round($sellOutKtn, 2),
            'ketersediaan_ktn'       => round($ketersediaan, 2),
            'stock_teoritis_ktn'     => round($stockTeoritis, 2),
            'stock_fisik_upload_ktn' => round($stockFisikUploadKtn, 2),
            'selisih_ktn'            => round($selisih, 2),
            'runrate_harian'         => round($runRateHarian, 2),
            'doi_days'               => $doiDays,
            'sales_flow_status'      => $status,
            'status_label'           => $statusLabel,
            'badge_class'            => $badgeClass,
            'action_recommendation'  => $recommendation,
            'total_value'            => round($totalValue, 2),
        ];
    }

    /**
     * Proses batch upload data stok dari file Excel distributor
     *
     * @param array $rows Array associative: [['distributor_code' => '...', 'product_id' => '...', 'stock_ktn' => 10, 'stock_pcs' => 0], ...]
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (misal 2026)
     * @param int|null $uploadedBy ID user pengunggah
     * @param string|null $fileName Nama file Excel
     * @return array Ringkasan hasil proses
     */
    public function processBatchUpload(
        array $rows,
        int $bulan,
        int $tahun,
        ?int $uploadedBy = null,
        ?string $fileName = null
    ): array {
        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'Tidak ada baris data yang diproses.',
                'total_rows' => 0,
            ];
        }

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $stockDate = $endDate;

        // Ambil periode bulan sebelumnya untuk mencari Stok Awal
        $prevPeriod = Carbon::createFromDate($tahun, $bulan, 1)->subMonth();
        $prevBulan = $prevPeriod->month;
        $prevTahun = $prevPeriod->year;

        // Kumpulkan distributor_code & product_id unik
        $distCodes = collect($rows)->pluck('distributor_code')->unique()->filter()->values()->all();
        $prodCodes = collect($rows)->pluck('product_id')->unique()->filter()->values()->all();

        // 0. Validasi Master Distributor & Master Produk (Wajib Terdaftar)
        $existingDistributors = DB::table('master_distributors')
            ->whereIn('distributor_code', $distCodes)
            ->pluck('distributor_name', 'distributor_code');

        $missingDistributors = array_values(array_diff($distCodes, $existingDistributors->keys()->all()));

        $existingProducts = DB::table('product_masters')
            ->whereIn('product_id', $prodCodes)
            ->pluck('product_name', 'product_id');

        $missingProducts = array_values(array_diff($prodCodes, $existingProducts->keys()->all()));

        if (!empty($missingDistributors) || !empty($missingProducts)) {
            $errorLines = [];
            if (!empty($missingDistributors)) {
                $countD = count($missingDistributors);
                $sampleD = implode(', ', array_slice($missingDistributors, 0, 5));
                $moreD = $countD > 5 ? " dan " . ($countD - 5) . " lainnya" : "";
                $errorLines[] = "• {$countD} Kode Distributor belum terdaftar di Master Distributor: [{$sampleD}{$moreD}].";
            }

            if (!empty($missingProducts)) {
                $countP = count($missingProducts);
                $sampleP = implode(', ', array_slice($missingProducts, 0, 5));
                $moreP = $countP > 5 ? " dan " . ($countP - 5) . " lainnya" : "";
                $errorLines[] = "• {$countP} Kode Produk (SKU) belum terdaftar di Master Produk: [{$sampleP}{$moreP}].";
            }

            $errorMessage = "Upload Dibatalkan!\n" . implode("\n", $errorLines) . "\nSilakan daftarkan terlebih dahulu master data di atas atau perbaiki kode di file Excel sebelum mengunggah.";

            return [
                'success'              => false,
                'message'              => $errorMessage,
                'missing_distributors' => $missingDistributors,
                'missing_products'     => $missingProducts,
                'total_rows'           => 0,
            ];
        }

        // 1. Ambil Stok Awal dari closing bulan lalu
        $prevStocks = DB::table('distributor_stocks')
            ->where('bulan', $prevBulan)
            ->where('tahun', $prevTahun)
            ->whereIn('distributor_code', $distCodes)
            ->whereIn('product_id', $prodCodes)
            ->select('distributor_code', 'product_id', 'stock_fisik_upload_ktn')
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        // 2. Agregasi Sell In bulan ini
        $sellIns = DB::table('selling_ins')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('distributor_code', $distCodes)
            ->whereIn('kode_barang', $prodCodes)
            ->groupBy('distributor_code', 'kode_barang')
            ->select(
                'distributor_code',
                'kode_barang as product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(total) as total_val')
            )
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        // 3. Agregasi Sell Out bulan ini (PostgreSQL uppercase column names)
        $sellOuts = DB::table('t_sellingout')
            ->whereBetween(DB::raw('"INVOICE_DATE"'), [$startDate, $endDate])
            ->where(DB::raw('"THN"'), (string)$tahun)
            ->where(DB::raw('"BLN"'), (string)$bulan)
            ->whereIn(DB::raw('"KDDIST"'), $distCodes)
            ->whereIn(DB::raw('"KDITEMPRC"'), $prodCodes)
            ->groupBy(DB::raw('"KDDIST"'), DB::raw('"KDITEMPRC"'))
            ->select(
                DB::raw('"KDDIST" as distributor_code'),
                DB::raw('"KDITEMPRC" as product_id'),
                DB::raw('SUM("TTL_QTY_KTN") as total_qty'),
                DB::raw('SUM("NETT") as total_val')
            )
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        // 4. Ambil estimasi harga/karton dari product_masters (price_zone1)
        $productPrices = DB::table('product_masters')
            ->whereIn('product_id', $prodCodes)
            ->pluck('price_zone1', 'product_id');

        $summary = [
            'total_rows'       => 0,
            'phantom_sales'    => 0,
            'channel_stuffing' => 0,
            'dead_sku'         => 0,
            'stockout_risk'    => 0,
            'variance_cases'   => 0,
            'healthy_cases'    => 0,
            'total_stock_ktn'  => 0.0,
            'total_value_rp'   => 0.0,
        ];

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $distCode = trim((string)($row['distributor_code'] ?? ''));
                $prodId   = trim((string)($row['product_id'] ?? ''));
                $stockKtn = (float)($row['stock_ktn'] ?? 0);
                $stockPcs = (float)($row['stock_pcs'] ?? 0);

                if (!$distCode || !$prodId) {
                    continue;
                }

                $key = $distCode . '_' . $prodId;

                // Stok Awal: dari closing bulan lalu, atau fallback 0
                $stockAwal = isset($prevStocks[$key]) ? (float)$prevStocks[$key]->stock_fisik_upload_ktn : 0.0;
                
                // Sell In
                $sellInKtn = isset($sellIns[$key]) ? (float)$sellIns[$key]->total_qty : 0.0;
                
                // Sell Out
                $sellOutKtn = isset($sellOuts[$key]) ? (float)$sellOuts[$key]->total_qty : 0.0;

                // Estimasi Nilai
                $unitPrice = (float)($productPrices[$prodId] ?? 0);
                $totalVal = $stockKtn * $unitPrice;

                // Jalankan Diagnosa
                $diag = $this->diagnose(
                    $stockAwal,
                    $sellInKtn,
                    $sellOutKtn,
                    $stockKtn,
                    $daysInMonth,
                    $totalVal
                );

                // Update or Create di distributor_stocks
                DistributorStock::updateOrCreate(
                    [
                        'distributor_code' => $distCode,
                        'product_id'       => $prodId,
                        'bulan'            => $bulan,
                        'tahun'            => $tahun,
                    ],
                    [
                        'stock_date'             => $stockDate,
                        'stock_awal_ktn'         => $diag['stock_awal_ktn'],
                        'sellin_ktn'             => $diag['sellin_ktn'],
                        'sellout_ktn'            => $diag['sellout_ktn'],
                        'stock_teoritis_ktn'     => $diag['stock_teoritis_ktn'],
                        'stock_fisik_upload_ktn' => $diag['stock_fisik_upload_ktn'],
                        'stock_fisik_upload_pcs' => $stockPcs,
                        'selisih_ktn'            => $diag['selisih_ktn'],
                        'doi_days'               => $diag['doi_days'],
                        'status_anomali'         => $diag['selisih_ktn'] == 0 ? 'NORMAL' : ($diag['selisih_ktn'] > 0 ? 'FISIK_LEBIH' : 'FISIK_KURANG'),
                        'sales_flow_status'      => $diag['sales_flow_status'],
                        'action_recommendation'  => $diag['action_recommendation'],
                        'total_value'            => $diag['total_value'],
                        'file_name'              => $fileName,
                        'uploaded_by'            => $uploadedBy,
                    ]
                );

                // Agregasi Ringkasan
                $summary['total_rows']++;
                $summary['total_stock_ktn'] += $stockKtn;
                $summary['total_value_rp']  += $totalVal;

                match ($diag['sales_flow_status']) {
                    'PHANTOM_SALES'    => $summary['phantom_sales']++,
                    'CHANNEL_STUFFING' => $summary['channel_stuffing']++,
                    'DEAD_SKU'         => $summary['dead_sku']++,
                    'STOCKOUT_RISK'    => $summary['stockout_risk']++,
                    'VARIANCE_MINUS', 'VARIANCE_PLUS' => $summary['variance_cases']++,
                    default            => $summary['healthy_cases']++,
                };
            }

            DB::commit();
            $summary['success'] = true;
            $summary['message'] = "Berhasil memproses {$summary['total_rows']} data stok distributor.";
            return $summary;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SalesDiagnosticService processBatchUpload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses upload stok: ' . $e->getMessage(),
                'total_rows' => 0,
            ];
        }
    }

    /**
     * Sinkronisasi data monitoring untuk bulan berjalan secara otomatis tanpa menunggu upload closing.
     * Stok Awal otomatis ditarik dari closing fisik bulan sebelumnya.
     *
     * @param int $bulan Bulan yang dipantau (misal 10)
     * @param int $tahun Tahun yang dipantau (misal 2026)
     * @return int Jumlah baris yang disinkronkan
     */
    public function syncRunningMonth(int $bulan, int $tahun): int
    {
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        // Hitung hari berjalan (misal jika bulan ini sedang aktif berjalan)
        $now = Carbon::now();
        $isCurrentMonth = ($now->year === $tahun && $now->month === $bulan);
        $elapsedDays = $isCurrentMonth ? max(1, $now->day) : $daysInMonth;

        // Ambil periode bulan sebelumnya
        $prevPeriod = Carbon::createFromDate($tahun, $bulan, 1)->subMonth();
        $prevBulan = $prevPeriod->month;
        $prevTahun = $prevPeriod->year;

        // 1. Ambil Stok Closing bulan lalu (yang sudah di-upload)
        $prevStocks = DB::table('distributor_stocks')
            ->where('bulan', $prevBulan)
            ->where('tahun', $prevTahun)
            ->select('distributor_code', 'product_id', 'stock_fisik_upload_ktn')
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        // 2. Agregasi Sell In bulan ini
        $sellIns = DB::table('selling_ins')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy('distributor_code', 'kode_barang')
            ->select(
                'distributor_code',
                'kode_barang as product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(total) as total_val')
            )
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        // 3. Agregasi Sell Out bulan ini (PostgreSQL uppercase column names)
        $sellOuts = DB::table('t_sellingout')
            ->whereBetween(DB::raw('"INVOICE_DATE"'), [$startDate, $endDate])
            ->where(DB::raw('"THN"'), (string)$tahun)
            ->where(DB::raw('"BLN"'), (string)$bulan)
            ->groupBy(DB::raw('"KDDIST"'), DB::raw('"KDITEMPRC"'))
            ->select(
                DB::raw('"KDDIST" as distributor_code'),
                DB::raw('"KDITEMPRC" as product_id'),
                DB::raw('SUM("TTL_QTY_KTN") as total_qty'),
                DB::raw('SUM("NETT") as total_val')
            )
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        // Gabungkan seluruh kombinasi unik: yang punya stok lalu OR punya Sell In OR punya Sell Out
        $allKeys = collect(array_keys($prevStocks->all()))
            ->merge(array_keys($sellIns->all()))
            ->merge(array_keys($sellOuts->all()))
            ->unique();

        // Filter: Pastikan hanya distributor dan produk yang valid di master data
        $validDistSet = DB::table('master_distributors')->pluck('distributor_code')->flip();
        $validProdSet = DB::table('product_masters')->pluck('product_id')->flip();

        $allKeys = $allKeys->filter(function ($key) use ($validDistSet, $validProdSet) {
            $parts = explode('_', $key, 2);
            return count($parts) === 2 && isset($validDistSet[$parts[0]]) && isset($validProdSet[$parts[1]]);
        });

        if ($allKeys->isEmpty()) {
            return 0;
        }

        // Cek data existing bulan ini
        $existingThisMonth = DB::table('distributor_stocks')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->select('id', 'distributor_code', 'product_id', 'file_name', 'stock_fisik_upload_ktn')
            ->get()
            ->keyBy(fn($item) => $item->distributor_code . '_' . $item->product_id);

        $productPrices = DB::table('product_masters')->pluck('price_zone1', 'product_id');

        $syncedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($allKeys as $key) {
                [$distCode, $prodId] = explode('_', $key, 2);

                $stockAwal = isset($prevStocks[$key]) ? (float)$prevStocks[$key]->stock_fisik_upload_ktn : 0.0;
                $sellInKtn = isset($sellIns[$key]) ? (float)$sellIns[$key]->total_qty : 0.0;
                $sellOutKtn = isset($sellOuts[$key]) ? (float)$sellOuts[$key]->total_qty : 0.0;

                $ketersediaan = $stockAwal + $sellInKtn;
                $stockTeoritis = $ketersediaan - $sellOutKtn;

                $runRateHarian = $elapsedDays > 0 ? ($sellOutKtn / $elapsedDays) : 0.0;
                $doiDays = $runRateHarian > 0 ? round($stockTeoritis / $runRateHarian, 1) : ($stockTeoritis > 0 ? 999.0 : 0.0);

                // Jika sudah ada upload fisik resmi di bulan ini, jangan timpa fisik upload-nya
                $existing = $existingThisMonth[$key] ?? null;
                $hasOfficialUpload = ($existing && $existing->file_name && $existing->file_name !== 'RUNNING_ESTIMATE');

                if ($hasOfficialUpload) {
                    $stockFisik = (float)$existing->stock_fisik_upload_ktn;
                    $selisih = $stockFisik - $stockTeoritis;
                    $doiDaysOfficial = $runRateHarian > 0 ? round($stockFisik / $runRateHarian, 1) : ($stockFisik > 0 ? 999.0 : 0.0);
                    $diag = $this->diagnose($stockAwal, $sellInKtn, $sellOutKtn, $stockFisik, $daysInMonth);

                    DistributorStock::where('id', $existing->id)->update([
                        'stock_awal_ktn'        => $stockAwal,
                        'sellin_ktn'            => $sellInKtn,
                        'sellout_ktn'           => $sellOutKtn,
                        'stock_teoritis_ktn'    => $stockTeoritis,
                        'selisih_ktn'           => $selisih,
                        'doi_days'              => $doiDaysOfficial,
                        'sales_flow_status'     => $diag['sales_flow_status'],
                        'action_recommendation' => $diag['action_recommendation'],
                    ]);
                    $syncedCount++;
                    continue;
                }

                // Mode Bulan Berjalan (Belum Closing)
                $status = 'HEALTHY';
                $recommendation = 'ALIRAN SEHAT. Monitoring pasokan berjalan.';

                if ($sellOutKtn > $ketersediaan && $ketersediaan >= 0) {
                    $status = 'PHANTOM_SALES';
                    $recommendation = 'AUDIT NOTA TOKO! Penjualan berjalan melebihi ketersediaan barang (Stok Awal + In).';
                } elseif ($sellOutKtn <= 0 && $ketersediaan > 0) {
                    $status = 'DEAD_SKU';
                    $recommendation = 'CEK DISPLAY TOKO! Ada stok tapi 0 penjualan berjalan bulan ini.';
                } elseif ($doiDays > self::DOI_OVERSTOCK_MAX) {
                    $status = 'CHANNEL_STUFFING';
                    $recommendation = 'STOP SELL IN SEMENTARA! Gudang distributor kepenuhan (Running DOI ' . round($doiDays) . ' hari).';
                } elseif ($doiDays > 0 && $doiDays < self::DOI_CRITICAL_MIN && $sellOutKtn > 0) {
                    $status = 'STOCKOUT_RISK';
                    $recommendation = 'PRIORITAS PO KILAT! Sisa stok berjalan < 7 hari. Segera jadwalkan pengiriman dari pabrik.';
                }

                $unitPrice = (float)($productPrices[$prodId] ?? 0);
                $totalVal = $stockTeoritis * $unitPrice;

                DistributorStock::updateOrCreate(
                    [
                        'distributor_code' => $distCode,
                        'product_id'       => $prodId,
                        'bulan'            => $bulan,
                        'tahun'            => $tahun,
                    ],
                    [
                        'stock_date'             => $endDate,
                        'stock_awal_ktn'         => $stockAwal,
                        'sellin_ktn'             => $sellInKtn,
                        'sellout_ktn'            => $sellOutKtn,
                        'stock_teoritis_ktn'     => $stockTeoritis,
                        'stock_fisik_upload_ktn' => 0.0,
                        'stock_fisik_upload_pcs' => 0.0,
                        'selisih_ktn'            => 0.0,
                        'doi_days'               => $doiDays,
                        'status_anomali'         => 'RUNNING',
                        'sales_flow_status'      => $status,
                        'action_recommendation'  => $recommendation,
                        'total_value'            => $totalVal,
                        'file_name'              => 'RUNNING_ESTIMATE',
                    ]
                );
                $syncedCount++;
            }

            DB::commit();
            return $syncedCount;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('syncRunningMonth Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 0;
        }
    }

    /**
     * Dapatkan daftar status untuk filter pills
     */
    public static function getStatusPills(): array
    {
        return [
            'ALL' => [
                'label' => 'Semua Diagnosa',
                'color' => 'gray',
            ],
            'PHANTOM_SALES' => [
                'label' => 'Penjualan Aneh',
                'color' => 'red',
                'statuses' => ['PHANTOM_SALES'],
            ],
            'OVER_STOCK' => [
                'label' => 'Over Stock',
                'color' => 'amber',
                'statuses' => ['OVER_STOCK'],
            ],
            'UNDER_STOCK' => [
                'label' => 'Under Stock',
                'color' => 'sky',
                'statuses' => ['UNDER_STOCK'],
            ],
            'IDEAL' => [
                'label' => 'Ideal',
                'color' => 'emerald',
                'statuses' => ['IDEAL'],
            ],
        ];
    }
}
