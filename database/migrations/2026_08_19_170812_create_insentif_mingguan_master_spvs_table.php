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
        Schema::create('insentif_mingguan_master_spvs', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->index(); // YYYY-MM
            $table->string('region_name')->nullable();
            $table->string('area_name')->nullable();
            $table->string('cabang')->nullable();
            $table->string('supervisor_code')->nullable();
            $table->string('supervisor_code_hak_akses_login')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_mingguan_master_spvs');
    }
};
