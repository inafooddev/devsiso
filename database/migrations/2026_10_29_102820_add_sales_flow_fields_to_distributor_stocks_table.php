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
        Schema::table('distributor_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_stocks', 'doi_days')) {
                $table->decimal('doi_days', 10, 2)->default(0)->after('selisih_ktn')->comment('Days of Inventory berdasarkan run-rate harian');
            }
            if (!Schema::hasColumn('distributor_stocks', 'sales_flow_status')) {
                $table->string('sales_flow_status', 50)->default('NORMAL')->after('status_anomali')->comment('PHANTOM_SALES, CHANNEL_STUFFING, DEAD_SKU, STOCKOUT_RISK, HEALTHY, VARIANCE_PLUS, VARIANCE_MINUS');
            }
            if (!Schema::hasColumn('distributor_stocks', 'action_recommendation')) {
                $table->string('action_recommendation', 255)->nullable()->after('sales_flow_status')->comment('Rekomendasi tindakan terarah dari sudut pandang sales');
            }

            $table->index('sales_flow_status', 'idx_dist_stocks_flow_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_stocks', function (Blueprint $table) {
            $table->dropIndex('idx_dist_stocks_flow_status');
            $table->dropColumn(['doi_days', 'sales_flow_status', 'action_recommendation']);
        });
    }
};
