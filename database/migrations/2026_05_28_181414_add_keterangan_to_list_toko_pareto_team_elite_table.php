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
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->string('keterangan')->nullable()->after('target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
