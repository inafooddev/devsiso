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
        Schema::create('target_kacabs', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 4); // Format: YYYY
            $table->string('cabang');
            $table->string('nama_kacab')->nullable();
            $table->decimal('target', 20, 2)->default(0);
            $table->decimal('insentif', 20, 2)->default(0);
            $table->timestamps();

            // Constraint unik untuk upsert
            $table->unique(['tahun', 'cabang'], 'tgt_kacab_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_kacabs');
    }
};
