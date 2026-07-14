<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perbaikan_tikor_toko', function (Blueprint $table) {
            $table->dropUnique('uq_ptt_dist_cust');
            $table->unique(['distributor_code', 'customer_code', 'sales_code'], 'uq_ptt_dist_cust_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perbaikan_tikor_toko', function (Blueprint $table) {
            $table->dropUnique('uq_ptt_dist_cust_sales');
            $table->unique(['distributor_code', 'customer_code'], 'uq_ptt_dist_cust');
        });
    }
};
