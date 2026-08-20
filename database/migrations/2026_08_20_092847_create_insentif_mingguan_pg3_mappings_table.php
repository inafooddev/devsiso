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
        Schema::create('insentif_mingguan_pg3_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('pg3_mingguan')->unique();
            $table->string('pg3_bulanan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_mingguan_pg3_mappings');
    }
};
