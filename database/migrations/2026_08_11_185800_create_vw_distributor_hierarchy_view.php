<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * View Name: vw_distributor_hierarchy
     *
     * Tujuan:
     *   Menjadi jembatan antara tabel angka sales (yang hanya tahu distributor_code)
     *   dengan hierarki organisasi lengkap (Region → Area → Supervisor → Cabang → Distributor).
     *
     * Pola pemakaian:
     *   SELECT h.region_name, SUM(s.actual) AS total_sales
     *   FROM vw_distributor_hierarchy h
     *   JOIN <tabel_angka_sales> s ON s.distributor_code = h.distributor_code
     *   WHERE s.year = 2024
     *   GROUP BY h.region_code, h.region_name;
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW vw_distributor_hierarchy AS
            SELECT
                md.region_code,
                md.region_name,
                md.area_code,
                md.area_name,
                te.team_elite_code  AS supervisor_code,
                ms.description      AS supervisor_name,
                md.distributor_code,
                md.distributor_name,
                md.branch_name,
                md.supervisor_code  AS siso_code,
                md.latitude,
                md.longitude
            FROM master_distributors md
            LEFT JOIN team_elite_code_mappings te
                ON te.siso_code = md.supervisor_code
            LEFT JOIN master_supervisors ms
                ON ms.supervisor_code = md.supervisor_code
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_distributor_hierarchy');
    }
};
