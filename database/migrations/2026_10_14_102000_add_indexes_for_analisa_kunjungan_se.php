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
        DB::statement('CREATE INDEX IF NOT EXISTS idx_rvah_se_search ON rpt_visit_an_h ("RID", "EID", "BID", "MUID", "TANGGAL")');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_rvah_id ON rpt_visit_an_h ("ID")');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_rvar_join ON rpt_visit_an_r ("HID", "MUID", "CUSTNO", "TANGGAL")');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_rvah_se_search');
        DB::statement('DROP INDEX IF EXISTS idx_rvah_id');
        DB::statement('DROP INDEX IF EXISTS idx_rvar_join');
    }
};
