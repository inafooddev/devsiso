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
        Schema::create('ao_percabang_perbulan', function (Blueprint $table) {
            $table->id();
            $table->date('bulan')->nullable();
            $table->string('region')->nullable();
            $table->string('area')->nullable();
            $table->string('cabang')->nullable();
            $table->integer('ao')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ao_percabang_perbulan');
    }
};
