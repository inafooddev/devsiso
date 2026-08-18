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
        Schema::create('insentif_kacab_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('parent_cabang');
            $table->string('child_cabang')->unique(); // One child can only belong to one parent
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_kacab_mappings');
    }
};
