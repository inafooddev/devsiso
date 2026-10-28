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
        // 1. Index tanggal & dimensi utama pada t_sellingout untuk Partition Pruning + Index Scan bulanan
        DB::statement('CREATE INDEX IF NOT EXISTS idx_t_sellingout_invdate_dist ON public.t_sellingout USING btree ("INVOICE_DATE", "KDDIST", "REG_FEST")');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_t_sellingout_thn_bln_dist ON public.t_sellingout USING btree ("THN", "BLN", "KDDIST")');

        // 2. Index tanggal & dimensi utama pada selling_ins
        DB::statement('CREATE INDEX IF NOT EXISTS idx_selling_ins_invdate_dist ON public.selling_ins USING btree (invoice_date, distributor_code, reg_fes)');

        // 3. Index bulan & cabang pada target_per_depo
        DB::statement('CREATE INDEX IF NOT EXISTS idx_target_per_depo_bulan_cabang ON public.target_per_depo USING btree (bulan, cabang, reg_fest)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS public.idx_t_sellingout_invdate_dist');
        DB::statement('DROP INDEX IF EXISTS public.idx_t_sellingout_thn_bln_dist');
        DB::statement('DROP INDEX IF EXISTS public.idx_selling_ins_invdate_dist');
        DB::statement('DROP INDEX IF EXISTS public.idx_target_per_depo_bulan_cabang');
    }
};
