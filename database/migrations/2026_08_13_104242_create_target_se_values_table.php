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
        Schema::create('target_se_values', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->comment('Format: YYYY-MM');
            $table->string('distributor_code', 50)->nullable();
            $table->string('salesman_code', 50)->nullable();
            $table->decimal('target', 15, 2)->default(0)->comment('Nilai/Nominal target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_se_values');
    }
};
