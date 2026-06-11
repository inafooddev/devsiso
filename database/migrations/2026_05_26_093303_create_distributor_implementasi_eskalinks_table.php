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
        if (!Schema::hasTable('distributor_implementasi_eskalink')) {
            Schema::create('distributor_implementasi_eskalink', function (Blueprint $table) {
                $table->id();
                $table->string('distributor_code', 15)->nullable();
                $table->string('distributor_name', 100)->nullable();
                $table->string('eskalink_code', 15)->nullable();
                $table->string('eskalink_code_dist', 100)->nullable();
                $table->string('implementasi', 15)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_implementasi_eskalink');
    }
};
