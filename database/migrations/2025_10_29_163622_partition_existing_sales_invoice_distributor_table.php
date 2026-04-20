<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catatan: Operasi ini SANGAT BERISIKO dan akan MENGUNCI tabel.
     * HARAP BACKUP DATABASE ANDA SEBELUM MENJALANKAN INI.
     * Pastikan aplikasi dalam mode maintenance.
     */
    public function up()
    {
        // Nonaktifkan timeout statement untuk query yang berjalan lama
        DB::statement('SET statement_timeout = 0');
        
        // Memulai transaksi, meskipun DDL di Postgres tidak selalu transaksional penuh
        DB::beginTransaction();
        
        try {
            // 1. Buat tabel partitioned "baru" (induk)
            // Strukturnya harus SAMA PERSIS dengan tabel lama, ditambah PRIMARY KEY
            DB::statement("
                CREATE TABLE sales_invoice_distributor_new (
                    id BIGINT NOT NULL, -- Asumsi ID dari tabel lama
                    distributor_code VARCHAR(25) NOT NULL,
                    invoice_type VARCHAR(50),
                    invoice_no VARCHAR(100),
                    invoice_date DATE NOT NULL,
                    order_no VARCHAR(100),
                    order_date DATE,
                    salesman_code VARCHAR(255),
                    salesman_name VARCHAR(255),
                    customer_code VARCHAR(255),
                    customer_name VARCHAR(500),
                    address VARCHAR(500),
                    product_code VARCHAR(255),
                    product_name VARCHAR(255),
                    carton_qty DECIMAL(18, 6) DEFAULT 0,
                    pack_qty DECIMAL(18, 6) DEFAULT 0,
                    pcs_qty DECIMAL(18, 6) DEFAULT 0,
                    quantity DECIMAL(18, 6) DEFAULT 0,
                    unit VARCHAR(50),
                    bonus DECIMAL(18, 6) DEFAULT 0,
                    is_bonus BOOLEAN DEFAULT false,
                    unit_price DECIMAL(18, 6) DEFAULT 0,
                    gross_amount DECIMAL(18, 6) DEFAULT 0,
                    discount1 DECIMAL(18, 6) DEFAULT 0,
                    discount2 DECIMAL(18, 6) DEFAULT 0,
                    discount3 DECIMAL(18, 6) DEFAULT 0,
                    discount4 DECIMAL(18, 6) DEFAULT 0,
                    discount5 DECIMAL(18, 6) DEFAULT 0,
                    discount6 DECIMAL(18, 6) DEFAULT 0,
                    discount7 DECIMAL(18, 6) DEFAULT 0,
                    discount8 DECIMAL(18, 6) DEFAULT 0,
                    total_discount DECIMAL(18, 6) DEFAULT 0,
                    dpp DECIMAL(18, 6) DEFAULT 0,
                    tax DECIMAL(18, 6) DEFAULT 0,
                    net_amount DECIMAL(18, 6) DEFAULT 0,
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    PRIMARY KEY (id, invoice_date) -- Kunci partisi HARUS bagian dari PK
                ) PARTITION BY RANGE (invoice_date);
            ");

            // 2. Buat partisi (sesuaikan rentang tahun sesuai data Anda)
            DB::statement("
                CREATE TABLE sales_invoice_distributor_2024 PARTITION OF sales_invoice_distributor_new
                    FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');
            ");
            DB::statement("
                CREATE TABLE sales_invoice_distributor_2025 PARTITION OF sales_invoice_distributor_new
                    FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
            ");
            DB::statement("
                CREATE TABLE sales_invoice_distributor_2026 PARTITION OF sales_invoice_distributor_new
                    FOR VALUES FROM ('2026-01-01') TO ('2027-01-01');
            ");

            // 3. Salin data dari tabel lama ke tabel baru (Ini akan MENGUNCI tabel dan butuh waktu lama)
            DB::statement("
                INSERT INTO sales_invoice_distributor_new SELECT * FROM sales_invoice_distributor;
            ");

            // 4. Buat ulang index di tabel baru
            DB::statement("CREATE INDEX ON sales_invoice_distributor_new (distributor_code);");
            DB::statement("CREATE INDEX ON sales_invoice_distributor_new (salesman_code);");
            DB::statement("CREATE INDEX ON sales_invoice_distributor_new (product_code);");
            DB::statement("CREATE INDEX ON sales_invoice_distributor_new (distributor_code, invoice_date);");
            // Catatan: Foreign keys harus dibuat ulang jika ada

            // 5. Ganti nama tabel (Tukar)
            DB::statement("ALTER TABLE sales_invoice_distributor RENAME TO sales_invoice_distributor_old;");
            DB::statement("ALTER TABLE sales_invoice_distributor_new RENAME TO sales_invoice_distributor;");
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // Tampilkan error
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
        try {
            // Tukar kembali
            DB::statement("ALTER TABLE sales_invoice_distributor RENAME TO sales_invoice_distributor_new;");
            DB::statement("ALTER TABLE sales_invoice_distributor_old RENAME TO sales_invoice_distributor;");
            
            // Hapus tabel partisi yang baru
            DB::statement("DROP TABLE sales_invoice_distributor_new;"); // Ini akan menghapus partisi anak juga

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
};
