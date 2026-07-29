<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan ekstensi postgis aktif
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        Schema::create('batas_wilayah_kelurahan', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            
            // Kolom geometri MultiPolygon, menggunakan SRID 4326 (WGS 84 - GPS coordinates)
            $table->geometry('geom', 'multiPolygon', 4326);
            
            $table->timestamps();
        });

        // Membuat spatial index untuk query yang lebih cepat
        DB::statement('CREATE INDEX batas_wilayah_kelurahan_geom_idx ON batas_wilayah_kelurahan USING GIST (geom);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batas_wilayah_kelurahan');
    }
};
