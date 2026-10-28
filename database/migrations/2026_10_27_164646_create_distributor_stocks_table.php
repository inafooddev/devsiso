<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('distributor_stocks')) {
            Schema::create('distributor_stocks', function (Blueprint $table) {
                $table->id();

                // 1. Dimensi Periode Waktu
                $table->integer('bulan')->comment('Bulan 1 s/d 12 (sinkron ke t_sellingout.BLN)');
                $table->integer('tahun')->comment('Tahun (sinkron ke t_sellingout.THN)');
                $table->date('stock_date')->comment('Tanggal posisi stok (sinkron ke selling_ins.invoice_date)');

                // 2. Kunci Entitas
                $table->string('distributor_code', 50)->comment('Kode Distributor (master_distributors.distributor_code)');
                $table->string('product_id', 50)->comment('Kode Produk Principal (product_masters.product_id)');

                // 3. Rekonsiliasi Pergerakan (Karton)
                $table->decimal('stock_awal_ktn', 15, 2)->default(0)->comment('Stok awal bulan (dari stok akhir bulan lalu)');
                $table->decimal('sellin_ktn', 15, 2)->default(0)->comment('Total pembelian masuk dari principal');
                $table->decimal('sellout_ktn', 15, 2)->default(0)->comment('Total penjualan keluar ke toko');
                $table->decimal('stock_teoritis_ktn', 15, 2)->default(0)->comment('Stok buku: awal + in - out');
                
                // 4. Stok Fisik Hasil Upload
                $table->decimal('stock_fisik_upload_ktn', 15, 2)->default(0)->comment('Stok fisik yang di-upload distributor (Karton)');
                $table->decimal('stock_fisik_upload_pcs', 15, 2)->default(0)->comment('Pecahan Pcs fisik jika ada');
                
                // 5. Analisa Selisih & Anomali
                $table->decimal('selisih_ktn', 15, 2)->default(0)->comment('Selisih: fisik - teoritis');
                $table->string('status_anomali', 50)->default('NORMAL')->comment('NORMAL, MINUS_STOCK, FISIK_LEBIH, FISIK_KURANG');
                
                // 6. Nilai Moneter & Audit Trail
                $table->decimal('total_value', 18, 2)->default(0)->comment('Estimasi nilai rupiah stok');
                $table->string('file_name')->nullable()->comment('Nama file Excel saat di-upload');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                // Unique Constraint per distributor, produk, bulan & tahun
                $table->unique(['distributor_code', 'product_id', 'bulan', 'tahun'], 'uq_dist_stock_period');

                // Indeks Performa
                $table->index(['distributor_code', 'tahun', 'bulan'], 'idx_dist_stocks_lookup');
                $table->index('stock_date', 'idx_dist_stocks_date');
                $table->index('status_anomali', 'idx_dist_stocks_anomali');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_stocks');
    }
};
