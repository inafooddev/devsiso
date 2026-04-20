<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        try {
            DB::beginTransaction();

            // 1. Buat tabel baru terpartisi
            DB::statement("
                CREATE TABLE t_sellingout_new (
                    LIKE t_sellingout INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING IDENTITY,
                    PRIMARY KEY (id, \"INVOICE_DATE\")
                ) PARTITION BY RANGE (\"INVOICE_DATE\");
            ");

            // 2. Buat partisi tahun
            DB::statement("
                CREATE TABLE t_sellingout_y2024 PARTITION OF t_sellingout_new
                FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');
            ");
            DB::statement("
                CREATE TABLE t_sellingout_y2025 PARTITION OF t_sellingout_new
                FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
            ");
            DB::statement("
                CREATE TABLE t_sellingout_y2026 PARTITION OF t_sellingout_new
                FOR VALUES FROM ('2026-01-01') TO ('2027-01-01');
            ");
            DB::statement("
                CREATE TABLE t_sellingout_default PARTITION OF t_sellingout_new DEFAULT;
            ");

            // 3. Buat index di parent dan di partisi utama
            $indexes = [
                'REGION', 'AREA', 'KDDIST', 'KDSPV', 'SLSNO_PRC', 'KDITEMPRC'
            ];

            foreach ($indexes as $col) {
                DB::statement("CREATE INDEX idx_{$col}_new ON t_sellingout_new (\"$col\");");
                DB::statement("CREATE INDEX idx_{$col}_y2025 ON t_sellingout_y2025 (\"$col\");");
            }

            DB::statement("CREATE INDEX idx_composite_new ON t_sellingout_new (\"REGION\", \"AREA\", \"KDDIST\", \"INVOICE_DATE\");");

            // 4. Salin data lama
            DB::statement("
                INSERT INTO t_sellingout_new 
                OVERRIDING SYSTEM VALUE 
                SELECT * FROM t_sellingout;
            ");

            // 5. Ganti nama tabel
            DB::statement("ALTER TABLE t_sellingout RENAME TO t_sellingout_old;");
            DB::statement("ALTER TABLE t_sellingout_new RENAME TO t_sellingout;");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function down()
    {
        DB::statement("DROP TABLE IF EXISTS t_sellingout CASCADE;");
        DB::statement("ALTER TABLE IF EXISTS t_sellingout_old RENAME TO t_sellingout;");
    }
};
