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
            // Tambahkan unique constraint agar batch upsert (ON CONFLICT) di PostgreSQL bisa berjalan
            $table->unique(['customer_code_prc', 'distributor_code'], 'unique_toko_pareto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->dropUnique('unique_toko_pareto');
        });
    }
};
