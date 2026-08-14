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
        Schema::create('insentif_spv_rwo', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('kuartal');
            $table->string('bulan', 10);
            $table->string('distributor_code', 50)->nullable();
            $table->string('cabang', 100);
            $table->integer('total_potensi')->default(0);
            $table->integer('capai_target')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_spv_rwo');
    }
};
