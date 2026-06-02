<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan dua kolom pembatasan akses wilayah ke tabel users:
     * - area_code     : JSON array — 1 user bisa pegang beberapa area
     * - supervisor_code: string biasa — 1 user hanya 1 supervisor
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Level Area: bisa multi (JSON array), diletakkan setelah region_code
            $table->json('area_code')->nullable()->after('region_code');

            // Level Supervisor: hanya 1 (single string), diletakkan setelah area_code
            $table->string('supervisor_code', 20)->nullable()->after('area_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['area_code', 'supervisor_code']);
        });
    }
};
