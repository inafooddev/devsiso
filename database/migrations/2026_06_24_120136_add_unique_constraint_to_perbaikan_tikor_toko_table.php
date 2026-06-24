<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('perbaikan_tikor_toko', function (Blueprint $table) {
            $table->dropIndex('idx_ptt_dist_cust');
            $table->unique(['distributor_code', 'customer_code'], 'uq_ptt_dist_cust');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perbaikan_tikor_toko', function (Blueprint $table) {
            $table->dropUnique('uq_ptt_dist_cust');
            $table->index(['distributor_code', 'customer_code'], 'idx_ptt_dist_cust');
        });
    }
};
