<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET statement_timeout = 0');

        // 1. sales_invoice_distributor (2027 & 2028)
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales_invoice_distributor_2027 PARTITION OF sales_invoice_distributor
                FOR VALUES FROM ('2027-01-01') TO ('2028-01-01');
        ");
        DB::statement("
            CREATE TABLE IF NOT EXISTS sales_invoice_distributor_2028 PARTITION OF sales_invoice_distributor
                FOR VALUES FROM ('2028-01-01') TO ('2029-01-01');
        ");

        // 2. detail_sell_out (2027 & 2028)
        DB::statement("
            CREATE TABLE IF NOT EXISTS detail_sell_out_y2027 PARTITION OF detail_sell_out
                FOR VALUES FROM ('2027-01-01') TO ('2028-01-01');
        ");
        DB::statement("
            CREATE TABLE IF NOT EXISTS detail_sell_out_y2028 PARTITION OF detail_sell_out
                FOR VALUES FROM ('2028-01-01') TO ('2029-01-01');
        ");

        // 3. t_sellingout (2027 & 2028)
        DB::statement("
            CREATE TABLE IF NOT EXISTS t_sellingout_y2027 PARTITION OF t_sellingout
                FOR VALUES FROM ('2027-01-01') TO ('2028-01-01');
        ");
        DB::statement("
            CREATE TABLE IF NOT EXISTS t_sellingout_y2028 PARTITION OF t_sellingout
                FOR VALUES FROM ('2028-01-01') TO ('2029-01-01');
        ");

        // 4. summary_selling_toko (2028)
        DB::statement("
            CREATE TABLE IF NOT EXISTS summary_selling_toko_2028 PARTITION OF summary_selling_toko
                FOR VALUES FROM ('2028-01-01') TO ('2029-01-01');
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS summary_selling_toko_2028 CASCADE');
        DB::statement('DROP TABLE IF EXISTS t_sellingout_y2028 CASCADE');
        DB::statement('DROP TABLE IF EXISTS t_sellingout_y2027 CASCADE');
        DB::statement('DROP TABLE IF EXISTS detail_sell_out_y2028 CASCADE');
        DB::statement('DROP TABLE IF EXISTS detail_sell_out_y2027 CASCADE');
        DB::statement('DROP TABLE IF EXISTS sales_invoice_distributor_2028 CASCADE');
        DB::statement('DROP TABLE IF EXISTS sales_invoice_distributor_2027 CASCADE');
    }
};
