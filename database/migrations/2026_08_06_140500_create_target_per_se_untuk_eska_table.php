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
        if (!Schema::hasTable('target_per_se_untuk_eska')) {
            Schema::create('target_per_se_untuk_eska', function (Blueprint $table) {
                $table->id();

                $table->string('tahun', 4)->comment('Tahun target, contoh: 2026');
                $table->string('bulan', 2)->comment('Bulan target, contoh: 08');
                $table->string('region', 50)->nullable();
                $table->string('branch', 50)->nullable();
                $table->string('sellingpoint', 50)->nullable();
                $table->string('salesman', 50)->nullable();
                $table->string('outlet', 100)->nullable();
                $table->decimal('value', 15, 2)->default(0)->comment('Nilai/Nominal target');

                $table->timestamps();

                // Index untuk optimalisasi pencarian / penyaringan data
                $table->index(['tahun', 'bulan', 'salesman'], 'idx_target_eska_periode_salesman');
                $table->index(['tahun', 'bulan', 'outlet'], 'idx_target_eska_periode_outlet');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_per_se_untuk_eska');
    }
};
