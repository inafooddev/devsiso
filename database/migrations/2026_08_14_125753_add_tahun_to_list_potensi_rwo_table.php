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
        Schema::table('list_potensi_rwo', function (Blueprint $table) {
            $table->integer('tahun')->nullable()->after('kuartal');
        });

        // Set existing records to 2026
        DB::table('list_potensi_rwo')->update(['tahun' => 2026]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_potensi_rwo', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};
