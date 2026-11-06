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
        // 1. Composite Index on list_toko_pareto_team_elite for outlet code + distributor coordinate joins
        DB::statement('CREATE INDEX IF NOT EXISTS idx_ltpte_dist_uniq ON public.list_toko_pareto_team_elite USING btree (distributor_code, uniq_kd)');

        // 2. Composite Index on jks_salesmans for salesman monthly lookup
        DB::statement('CREATE INDEX IF NOT EXISTS idx_jks_salesmans_dist_sls_bln ON public.jks_salesmans USING btree (distributor_code, salesman_code, bulan)');

        // 3. Composite Index on jks_salesmans for double cover aggregation
        DB::statement('CREATE INDEX IF NOT EXISTS idx_jks_salesmans_dc ON public.jks_salesmans USING btree (bulan, customer_code, distributor_code, salesman_code)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS public.idx_ltpte_dist_uniq');
        DB::statement('DROP INDEX IF EXISTS public.idx_jks_salesmans_dist_sls_bln');
        DB::statement('DROP INDEX IF EXISTS public.idx_jks_salesmans_dc');
    }
};
