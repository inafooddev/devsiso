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
        if (!Schema::hasTable('perbaikan_tikor_toko')) {
            Schema::create('perbaikan_tikor_toko', function (Blueprint $table) {
                $table->id();
                $table->string('region_code', 15)->nullable();
                $table->string('area_code', 15)->nullable();
                $table->string('distributor_code', 15)->nullable();
                $table->string('sales_code', 15)->nullable();
                $table->string('customer_code', 25)->nullable();
                $table->decimal('latitude', 11, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('status', 50)->nullable();
                $table->timestamp('timestamp')->useCurrent();
                $table->timestamps();

                // Single Column Indexes
                $table->index('region_code', 'idx_ptt_region_code');
                $table->index('area_code', 'idx_ptt_area_code');
                $table->index('distributor_code', 'idx_ptt_distributor_code');
                $table->index('sales_code', 'idx_ptt_sales_code');
                $table->index('customer_code', 'idx_ptt_customer_code');
                $table->index('status', 'idx_ptt_status');

                // Composite Indexes for Join/Query Optimization
                // 1. distributor_implementasi_eskalink (die.eskalink_code = distributor_code)
                // 2. fsalesman (fs.SLSNO = sales_code and fs.KD = distributor_code)
                $table->index(['distributor_code', 'sales_code'], 'idx_ptt_dist_sales');
                // 3. customer_prc_eska (cpe.kodecabang = distributor_code and cpe.custno = customer_code)
                $table->index(['distributor_code', 'customer_code'], 'idx_ptt_dist_cust');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perbaikan_tikor_toko');
    }
};
