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
        // 1. Buat Tabel Utama (Partitioned)
        DB::statement('
            CREATE TABLE selling_ins (
                id BIGSERIAL,
                invoice_date DATE NOT NULL,
                
                -- Hierarki Sales & Distributor
                region_code VARCHAR(255) NULL,
                region_name VARCHAR(255) NULL,
                area_code VARCHAR(255) NULL,
                area_name VARCHAR(255) NULL,
                supervisor_code VARCHAR(255) NULL,
                supervisor_name VARCHAR(255) NULL,
                distributor_code VARCHAR(255) NULL,
                distributor_name VARCHAR(255) NULL,
                branch_name VARCHAR(255) NULL,
                
                -- Info Transaksi Mentah
                kode VARCHAR(255) NULL,
                invoice_no VARCHAR(255) NULL,
                jenis_penjualan VARCHAR(255) NULL,
                kode_barang VARCHAR(255) NULL,
                nama_barang VARCHAR(255) NULL,
                
                -- Hierarki Produk
                produk_grup VARCHAR(255) NULL,
                subbrand VARCHAR(255) NULL,
                reg_fes VARCHAR(255) NULL,
                kategory VARCHAR(255) NULL,
                topitem VARCHAR(255) NULL,
                
                -- Metrik
                qty NUMERIC(15,2) NULL,
                satuan VARCHAR(255) NULL,
                harga_satuan NUMERIC(15,2) NULL,
                subtotal NUMERIC(15,2) NULL,
                qty_bonus NUMERIC(15,2) NULL,
                nilai_bonus NUMERIC(15,2) NULL,
                diskon_1 NUMERIC(15,2) NULL,
                diskon_2 NUMERIC(15,2) NULL,
                diskon_3 NUMERIC(15,2) NULL,
                dpp NUMERIC(15,2) NULL,
                ppn NUMERIC(15,2) NULL,
                total NUMERIC(15,2) NULL,
                total_idr NUMERIC(15,2) NULL,
                
                -- Timestamps
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                
                PRIMARY KEY (id, invoice_date)
            ) PARTITION BY RANGE (invoice_date);
        ');

        // 2. Buat Index
        DB::statement('CREATE INDEX idx_selling_ins_distributor_code ON selling_ins(distributor_code)');
        DB::statement('CREATE INDEX idx_selling_ins_supervisor_code ON selling_ins(supervisor_code)');
        DB::statement('CREATE INDEX idx_selling_ins_produk_grup ON selling_ins(produk_grup)');
        DB::statement('CREATE INDEX idx_selling_ins_reg_fes ON selling_ins(reg_fes)');
        DB::statement('CREATE INDEX idx_selling_ins_region_area ON selling_ins(region_code, area_code)');

        // 3. Buat partisi awal (Tahun-tahun umum)
        $years = [2022, 2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030];
        foreach ($years as $year) {
            $nextYear = $year + 1;
            DB::statement("
                CREATE TABLE selling_ins_y{$year} 
                PARTITION OF selling_ins 
                FOR VALUES FROM ('{$year}-01-01') TO ('{$nextYear}-01-01');
            ");
        }
    }

    /**
     * Run the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS selling_ins CASCADE');
    }
};
