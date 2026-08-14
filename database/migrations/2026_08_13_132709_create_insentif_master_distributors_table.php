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
        Schema::create('insentif_master_distributors', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->index(); // Format: YYYY-MM
            $table->string('region_code')->nullable();
            $table->string('region_name')->nullable();
            $table->string('area_code')->nullable();
            $table->string('area_name')->nullable();
            $table->string('distributor_code')->nullable();
            $table->string('distributor_name')->nullable();
            $table->string('cabang')->nullable();
            
            // Unik kombinasi bulan dan distributor code untuk mencegah duplikat
            $table->unique(['bulan', 'distributor_code'], 'unq_insentif_master_distributor');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_master_distributors');
    }
};
