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
            $table->string('foto_toko2')->nullable()->after('foto_toko');
            $table->string('foto_toko3')->nullable()->after('foto_toko2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_outlet', function (Blueprint $table) {
            $table->dropColumn(['foto_toko2', 'foto_toko3']);
        });
    }
};
