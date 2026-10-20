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
        DB::statement("
            CREATE OR REPLACE VIEW md_report_ao AS
            SELECT 
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                tec.team_elite_code AS supervisor_code,
                ms.description AS supervisor_name,
                md.distributor_code,
                md.distributor_name
            FROM master_distributors md
            LEFT JOIN team_elite_code_mappings tec ON md.supervisor_code::text = tec.siso_code::text
            LEFT JOIN master_supervisors ms ON ms.supervisor_code::text = md.supervisor_code::text
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_report_reaktivasi_toko AS
            SELECT 
                m.region_code,
                m.area_code,
                m.supervisor_code,
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
        DB::statement("
            CREATE OR REPLACE VIEW md_report_ao AS
            SELECT 
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                tec.team_elite_code AS supervisor_code,
                ms.description AS supervisor_name,
                md.distributor_code,
                md.distributor_name
            FROM master_distributors md
            LEFT JOIN team_elite_code_mappings tec ON md.supervisor_code::text = tec.siso_code::text
            LEFT JOIN master_supervisors ms ON ms.supervisor_code::text = md.supervisor_code::text
            WHERE (md.is_active IS TRUE)
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_report_reaktivasi_toko AS
            SELECT 
                m.region_code,
                m.area_code,
                m.supervisor_code,
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
};
