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
        if (!Schema::hasTable('team_elite_code_mappings')) {
            Schema::create('team_elite_code_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('region_code')->nullable();
                $table->string('area_code')->nullable();
                $table->string('team_elite_code')->nullable();
                $table->string('siso_code')->nullable();
                $table->string('level')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_elite_code_mappings');
    }
};
