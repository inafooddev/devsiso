<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_report_reaktivasi_toko AS
            SELECT 
                m.region_name AS region,
                m.area_name AS area,
                m.supervisor_name AS supervisor,
                m.distributor_name AS distributor,
                t.kd_dist,
                t.uniq_kd,
                t.custno,
                t.custname,
                t.alamat,
                t.bulan,
                t.neto
            FROM ao_list_toko t
            LEFT JOIN md_report_ao m ON t.kd_dist = m.distributor_code
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_report_reaktivasi_toko");
    }
};
