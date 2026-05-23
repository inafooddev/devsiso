<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove exact duplicates to ensure the unique index can be created.
        // In PostgreSQL, removing duplicates keeping the minimum id:
        DB::statement('
            DELETE FROM jks_team_elite a
            USING jks_team_elite b
            WHERE a.id > b.id
              AND a.tanggal = b.tanggal
              AND a.kode_team = b.kode_team
              AND a.distributor_code = b.distributor_code
              AND a.custno = b.custno
        ');

        Schema::table('jks_team_elite', function (Blueprint $table) {
            $table->unique(['tanggal', 'kode_team', 'distributor_code', 'custno'], 'jks_unique_composite');
        });
    }

    public function down(): void
    {
        Schema::table('jks_team_elite', function (Blueprint $table) {
            $table->dropUnique('jks_unique_composite');
        });
    }
};
