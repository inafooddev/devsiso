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
        // 1. Data Normalization Patch for Case & Whitespace
        // Clean customer_code in jks_salesmans (fixes 160 lowercase codes / 108 false Non-JKS outlets)
        DB::statement("
            UPDATE jks_salesmans 
            SET customer_code = UPPER(TRIM(customer_code)) 
            WHERE customer_code != UPPER(TRIM(customer_code))
        ");

        // Clean customer_code & distributor_code in jks_se_master_toko_ool (fixes double spaces & casing)
        DB::statement("
            UPDATE jks_se_master_toko_ool 
            SET customer_code = REGEXP_REPLACE(UPPER(TRIM(customer_code)), '\s+', ' ', 'g') 
            WHERE customer_code ~ '\s{2,}' OR customer_code != UPPER(TRIM(customer_code))
        ");

        DB::statement("
            UPDATE jks_se_master_toko_ool 
            SET distributor_code = UPPER(TRIM(distributor_code)) 
            WHERE distributor_code != UPPER(TRIM(distributor_code))
        ");

        DB::statement("
            UPDATE jks_se_master_toko_ool 
            SET customer_eska = UPPER(TRIM(customer_eska)) 
            WHERE customer_eska IS NOT NULL AND customer_eska != UPPER(TRIM(customer_eska))
        ");

        // Clean jks_se_toko_ool_remarks
        DB::statement("
            UPDATE jks_se_toko_ool_remarks 
            SET customer_code = UPPER(TRIM(customer_code)),
                distributor_code = UPPER(TRIM(distributor_code))
            WHERE customer_code != UPPER(TRIM(customer_code)) 
               OR distributor_code != UPPER(TRIM(distributor_code))
        ");

        // 2. Performance Indexes
        // Indexes on jks_se_master_toko_ool
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_jks_se_mto_dist_cust 
            ON jks_se_master_toko_ool (distributor_code, customer_code)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_jks_se_mto_hierarchy 
            ON jks_se_master_toko_ool (region_code, area_code, supervisor_code)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_jks_se_mto_customer_code 
            ON jks_se_master_toko_ool (customer_code)
        ");

        // Partial Index on jks_approvals for fast Non-Route lookup
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_jks_approvals_pending_tambah 
            ON jks_approvals (distributor_code) 
            WHERE status = 'PENDING' AND action_type = 'TAMBAH_JADWAL'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_jks_approvals_pending_tambah");
        DB::statement("DROP INDEX IF EXISTS idx_jks_se_mto_customer_code");
        DB::statement("DROP INDEX IF EXISTS idx_jks_se_mto_hierarchy");
        DB::statement("DROP INDEX IF EXISTS idx_jks_se_mto_dist_cust");
    }
};
