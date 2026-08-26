<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Membuat tabel selling_in_raws dengan partisi PostgreSQL RANGE per tahun (2023–2030).
     *
     * Catatan arsitektur:
     * - Tabel ini adalah RAW layer: menyimpan data persis dari file Excel, minimal transformasi.
     * - Kolom partisi (invoice_date) wajib NOT NULL dan menjadi bagian dari PRIMARY KEY composite.
     * - Semua index dibuat di tabel induk dan otomatis diwariskan ke seluruh partisi anak.
     * - Rollback (down) cukup DROP TABLE CASCADE karena menghapus partisi anak sekaligus.
     */
    public function up(): void
    {
        DB::statement('SET statement_timeout = 0');

        DB::beginTransaction();

        try {
            // ═══════════════════════════════════════════════════════
            // 1. BUAT TABEL INDUK (PARTITIONED BY RANGE invoice_date)
            // ═══════════════════════════════════════════════════════
            DB::statement("
                CREATE TABLE selling_in_raws (

                    -- Metadata pipeline
                    id                 BIGINT GENERATED ALWAYS AS IDENTITY,
                    import_batch_id    BIGINT          NOT NULL,
                    row_number         INTEGER         NOT NULL,

                    -- Data dari Excel: kolom WAJIB isi (NOT NULL)
                    invoice_date       DATE            NOT NULL,
                    divisi             VARCHAR(100)    NOT NULL,
                    wilayah            VARCHAR(100)    NOT NULL,
                    kode_distributor   VARCHAR(50)     NOT NULL,
                    distributor        VARCHAR(255)    NOT NULL,
                    kode_barang        VARCHAR(100)    NOT NULL,

                    -- Data dari Excel: kolom opsional (NULL)
                    kode               VARCHAR(50)     NULL,
                    invoice_no         VARCHAR(100)    NULL,
                    jenis_penjualan    VARCHAR(100)    NULL,
                    nama_barang        VARCHAR(255)    NULL,
                    qty                NUMERIC(15,4)   NULL,
                    satuan             VARCHAR(50)     NULL,
                    harga_satuan       NUMERIC(18,4)   NULL,
                    subtotal           NUMERIC(18,4)   NULL,
                    qty_bonus          NUMERIC(15,4)   NULL,
                    nilai_bonus        NUMERIC(18,4)   NULL,
                    diskon_1           NUMERIC(18,4)   NULL,
                    diskon_2           NUMERIC(18,4)   NULL,
                    diskon_3           NUMERIC(18,4)   NULL,
                    dpp                NUMERIC(18,4)   NULL,
                    ppn                NUMERIC(18,4)   NULL,
                    total              NUMERIC(18,4)   NULL,
                    total_idr          NUMERIC(18,4)   NULL,

                    -- Timestamps
                    created_at         TIMESTAMP       NULL,
                    updated_at         TIMESTAMP       NULL,

                    -- PK composite: id + invoice_date (syarat PostgreSQL partitioned table)
                    PRIMARY KEY (id, invoice_date)

                ) PARTITION BY RANGE (invoice_date);
            ");

            // ═══════════════════════════════════════════════════════
            // 2. BUAT PARTISI PER TAHUN (2023–2030)
            // ═══════════════════════════════════════════════════════
            $partitions = [
                ['2023-01-01', '2024-01-01'],
                ['2024-01-01', '2025-01-01'],
                ['2025-01-01', '2026-01-01'],
                ['2026-01-01', '2027-01-01'],
                ['2027-01-01', '2028-01-01'],
                ['2028-01-01', '2029-01-01'],
                ['2029-01-01', '2030-01-01'],
                ['2030-01-01', '2031-01-01'],
            ];

            foreach ($partitions as [$from, $to]) {
                $year = substr($from, 0, 4);
                DB::statement("
                    CREATE TABLE selling_in_raws_{$year}
                        PARTITION OF selling_in_raws
                        FOR VALUES FROM ('{$from}') TO ('{$to}');
                ");
            }

            // ═══════════════════════════════════════════════════════
            // 3. BUAT INDEX (di tabel induk → diwariskan ke partisi anak)
            // ═══════════════════════════════════════════════════════

            // Index pada kolom yang wajib ada isinya
            DB::statement('CREATE INDEX idx_sir_invoice_date     ON selling_in_raws (invoice_date);');
            DB::statement('CREATE INDEX idx_sir_divisi           ON selling_in_raws (divisi);');
            DB::statement('CREATE INDEX idx_sir_wilayah          ON selling_in_raws (wilayah);');
            DB::statement('CREATE INDEX idx_sir_kode_distributor ON selling_in_raws (kode_distributor);');
            DB::statement('CREATE INDEX idx_sir_distributor      ON selling_in_raws (distributor);');
            DB::statement('CREATE INDEX idx_sir_kode_barang      ON selling_in_raws (kode_barang);');

            // Index metadata pipeline
            DB::statement('CREATE INDEX idx_sir_batch            ON selling_in_raws (import_batch_id);');

            // Index composite: query paling umum (distributor + periode)
            DB::statement('CREATE INDEX idx_sir_dist_date        ON selling_in_raws (kode_distributor, invoice_date);');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rollback: drop tabel induk beserta semua partisi anak sekaligus (CASCADE).
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS selling_in_raws CASCADE;');
    }
};
