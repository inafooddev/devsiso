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
        if (!Schema::hasTable('unmapped_units')) {
            Schema::create('unmapped_units', function (Blueprint $table) {
                $table->id();
                $table->string('distributor_code');
                $table->string('raw_unit');
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
        Schema::dropIfExists('unmapped_units');
    }
};
