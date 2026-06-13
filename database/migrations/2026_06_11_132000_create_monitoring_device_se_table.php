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
        if (!Schema::hasTable('monitoring_device_se')) {
            Schema::create('monitoring_device_se', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal')->nullable();
                $table->string('distributor_code')->nullable();
                $table->string('sales_code')->nullable();
                $table->string('foto_tampak_depan')->nullable();
                $table->string('foto_tampak_belakang')->nullable();
                $table->string('kondisi_hp')->nullable();
                $table->string('kondisi_kartu')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_device_se');
    }
};
