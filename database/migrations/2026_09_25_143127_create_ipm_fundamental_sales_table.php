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
        Schema::create('ipm_fundamental_sales', function (Blueprint $table) {
            $table->id();
            $table->date('bulan')->nullable();
            $table->string('cabang', 50)->nullable();
            $table->decimal('target', 18, 2)->nullable();
            $table->decimal('selling_out', 18, 2)->nullable();
            $table->integer('jumlah_se')->nullable();
            $table->decimal('pc', 18, 2)->nullable();
            $table->decimal('ac', 18, 2)->nullable();
            $table->decimal('ec', 18, 2)->nullable();
            $table->decimal('ro', 18, 2)->nullable();
            $table->decimal('ao', 18, 2)->nullable();
            $table->decimal('sku', 18, 2)->nullable();
            $table->decimal('nota', 18, 2)->nullable();
            $table->decimal('potensi_rwo', 18, 2)->nullable();
            $table->decimal('capai_rwo', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipm_fundamental_sales');
    }
};
