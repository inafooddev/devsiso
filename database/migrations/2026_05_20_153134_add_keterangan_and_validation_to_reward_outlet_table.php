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
        Schema::table('reward_outlet', function (Blueprint $table) {
            $table->text('keterangan')->nullable();
            $table->boolean('is_valid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_outlet', function (Blueprint $table) {
            $table->dropColumn(['keterangan', 'is_valid']);
        });
    }
};
