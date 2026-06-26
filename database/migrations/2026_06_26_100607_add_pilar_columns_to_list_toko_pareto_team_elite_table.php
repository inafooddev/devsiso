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
            $table->string('pilar_q1')->nullable();
            $table->string('pilar_q2')->nullable();
            $table->string('pilar_q3')->nullable();
            $table->string('pilar_q4')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->dropColumn(['pilar_q1', 'pilar_q2', 'pilar_q3', 'pilar_q4']);
        });
    }
};
