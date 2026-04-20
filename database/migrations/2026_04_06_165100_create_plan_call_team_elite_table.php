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
        Schema::create('plan_call_team_elite', function (Blueprint $table) {
            $table->id(); // Secara otomatis membuat kolom 'id' bertipe bigserial (auto-incrementing BIGINT) dan menjadikannya primary key
            $table->date('tanggal')->nullable();
            $table->string('minggu', 10)->nullable();
            $table->string('level', 10)->nullable();
            $table->string('kode_sales', 150)->nullable();
            $table->string('cabang', 50)->nullable();
            $table->string('kode_toko', 50)->nullable();
            $table->string('nama_toko', 255)->nullable();
            $table->string('pilar', 10)->nullable();
            $table->decimal('target', 18, 6)->nullable();
            $table->timestamps(); // Secara otomatis membuat kolom 'created_at' dan 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_call_team_elite');
    }
};