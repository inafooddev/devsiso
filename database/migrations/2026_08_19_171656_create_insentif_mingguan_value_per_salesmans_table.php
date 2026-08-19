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
        Schema::create('insentif_mingguan_value_per_salesmans', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->index(); // Format: YYYY-MM
            $table->string('distributor_code')->nullable();
            $table->string('sales_code')->nullable();
            $table->decimal('actual', 16, 2)->default(0); // SUM dari nett_amount
            
            // Unik kombinasi agar tidak ada data dobel per bulan per distributor per sales
            $table->unique(['bulan', 'distributor_code', 'sales_code'], 'unq_ins_mgg_val_salesman');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_mingguan_value_per_salesmans');
    }
};
