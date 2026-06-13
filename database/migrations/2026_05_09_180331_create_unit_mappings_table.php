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
        if (!Schema::hasTable('unit_mappings')) {
            Schema::create('unit_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('distributor_code');
                $table->string('raw_unit');
                $table->string('mapped_unit'); // CTN/PCK/PCS
                $table->timestamps();
    
                $table->unique(['distributor_code', 'raw_unit']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_mappings');
    }
};
