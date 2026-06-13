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
        if (!Schema::hasTable('reward_outlet')) {
            Schema::create('reward_outlet', function (Blueprint $table) {
                $table->id();
                $table->string('region_code')->nullable();
                $table->string('region_name')->nullable();
                $table->string('area_code')->nullable();
                $table->string('area_name')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('eskalink_code')->nullable();
                $table->string('customer_code')->nullable();
                $table->string('customer_name')->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_hp')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('nama_pemilik_toko')->nullable();
                $table->string('nama_ktp')->nullable();
                $table->string('nik_ktp')->nullable();
                $table->string('foto_ktp')->nullable();
                $table->string('nama_bank')->nullable();
                $table->string('no_rekening')->nullable();
                $table->string('nama_pemilik_norek')->nullable();
                $table->string('foto_toko')->nullable();
                $table->timestamps();
    
                // Indexes
                $table->index('region_code');
                $table->index('region_name');
                $table->index('area_code');
                $table->index('area_name');
                $table->index('branch_name');
                $table->index('customer_code');
                $table->index('eskalink_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_outlet');
    }
};
