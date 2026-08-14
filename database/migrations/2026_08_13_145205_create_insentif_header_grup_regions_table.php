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
        Schema::create('insentif_header_grup_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insentif_header_grup_id')->constrained('insentif_header_grups')->onDelete('cascade');
            $table->string('region_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_header_grup_regions');
    }
};
