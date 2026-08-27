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
            $table->string('channel_outlet')->nullable();
            $table->string('classification_outlet')->nullable();
            $table->string('segment_outlet')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->dropColumn([
                'channel_outlet',
                'classification_outlet',
                'segment_outlet',
            ]);
        });
    }
};
