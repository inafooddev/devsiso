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
        Schema::create('mapping_spv_code', function (Blueprint $table) {
            $table->id(); // Membuat kolom 'id' bertipe bigserial (auto_increment & primary_key)
            $table->string('branch_code', 15)->nullable();
            $table->string('supervisor_code', 15)->nullable();
            $table->timestamps(); // Membuat kolom 'created_at' dan 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping_spv_code');
    }
};