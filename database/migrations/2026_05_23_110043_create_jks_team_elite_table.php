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
        if (!Schema::hasTable('jks_team_elite')) {
            Schema::create('jks_team_elite', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal')->nullable();
                $table->string('kode_team', 50)->nullable();
                $table->string('nama_team', 255)->nullable();
                $table->string('kode_region', 50)->nullable();
                $table->string('nama_region', 255)->nullable();
                $table->string('kode_area', 50)->nullable();
                $table->string('nama_area', 255)->nullable();
                $table->string('distributor_code', 50)->nullable();
                $table->string('distributor_name', 255)->nullable();
                $table->string('custno', 100)->nullable();
                $table->string('custname', 255)->nullable();
                $table->text('addres')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jks_team_elite');
    }
};
