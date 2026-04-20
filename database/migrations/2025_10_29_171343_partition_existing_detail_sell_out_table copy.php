<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Mulai transaksi
        DB::beginTransaction();

        try {
            // 2. Buat tabel baru yang dipartisi (identik dengan yang lama)
            // Perhatikan: PARTITION BY RANGE (invoice_date)
            // Primary key harus menyertakan kolom partisi
            DB::statement("
                CREATE TABLE detail_sell_out_new (
                    LIKE detail_sell_out INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING IDENTITY,
                    PRIMARY KEY (id, invoice_date) 
                ) PARTITION BY RANGE (invoice_date);
            ");

            // 3. Buat partisi untuk tahun-tahun (Contoh: 2024, 2025, 2026)
            // Sesuaikan rentang ini dengan data Anda
            DB::statement("
                CREATE TABLE detail_sell_out_y2024 PARTITION OF detail_sell_out_new
                FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');
            ");
            DB::statement("
                CREATE TABLE detail_sell_out_y2025 PARTITION OF detail_sell_out_new
                FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
            ");
            DB::statement("
                CREATE TABLE detail_sell_out_y2026 PARTITION OF detail_sell_out_new
                FOR VALUES FROM ('2026-01-01') TO ('2027-01-01');
            ");
            // Buat partisi default untuk data di luar rentang (opsional tapi disarankan)
            DB::statement("
                CREATE TABLE detail_sell_out_default PARTITION OF detail_sell_out_new DEFAULT;
            ");


            // 4. Buat INDEXES pada tabel induk (new). Ini akan otomatis diterapkan ke semua partisi.
            // Ini adalah index yang kita diskusikan untuk mempercepat query filter.
            DB::statement("CREATE INDEX ON detail_sell_out_new (region_code);");
            DB::statement("CREATE INDEX ON detail_sell_out_new (area_code);");
            DB::statement("CREATE INDEX ON detail_sell_out_new (entity_code);");
            DB::statement("CREATE INDEX ON detail_sell_out_new (branch_code);");
            DB::statement("CREATE INDEX ON detail_sell_out_new (sales_code);");
            DB::statement("CREATE INDEX ON detail_sell_out_new (prd_code);");
            
            // Index composite terpenting untuk filter Anda
            DB::statement("CREATE INDEX ON detail_sell_out_new (entity_code, invoice_date);");
            DB::statement("CREATE INDEX ON detail_sell_out_new (region_code, area_code, entity_code, invoice_date);");


            // 5. Salin data dari tabel lama ke tabel baru
            // INI ADALAH PROSES YANG LAMA DAN AKAN MENGUNCI TABEL
            DB::statement("INSERT INTO detail_sell_out_new SELECT * FROM detail_sell_out;");


            // 6. Ganti nama tabel (Swap)
            DB::statement("ALTER TABLE detail_sell_out RENAME TO detail_sell_out_old;");
            DB::statement("ALTER TABLE detail_sell_out_new RENAME TO detail_sell_out;");

            // 7. Selesaikan transaksi
            DB::commit();

        } catch (\Exception $e) {
            // Batalkan jika terjadi error
            DB::rollBack();
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
        // 1. Ganti nama tabel kembali
        DB::statement("ALTER TABLE detail_sell_out RENAME TO detail_sell_out_new;");
        DB::statement("ALTER TABLE detail_sell_out_old RENAME TO detail_sell_out;");

        // 2. Hapus tabel partisi yang baru
        DB::statement("DROP TABLE detail_sell_out_new;");
    }
};
