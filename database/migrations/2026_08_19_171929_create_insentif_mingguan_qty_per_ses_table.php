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
        Schema::create('insentif_mingguan_qty_per_ses', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->index(); // Format: YYYY-MM
            $table->string('distributor_code')->nullable();
            $table->string('sales_code')->nullable();
            $table->string('product_group_3')->nullable(); // Kita tetap menamakannya product_group_3 agar strukturnya konsisten, meski isinya dari SUBBRAND
            $table->decimal('qty_ctn', 16, 2)->default(0); // SUM dari TTL_QTY_KTN
            
            // Unik kombinasi agar tidak ada data dobel per bulan per distributor per sales per pg3
            $table->unique(['bulan', 'distributor_code', 'sales_code', 'product_group_3'], 'unq_ins_mgg_qty_se');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_mingguan_qty_per_ses');
    }
};
