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
        Schema::table('surat_kesepakatan_bersama_rwo', function (Blueprint $table) {
            $table->boolean('ho_is_valid')->nullable()->after('is_approved');
            $table->text('ho_notes')->nullable()->after('ho_is_valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_kesepakatan_bersama_rwo', function (Blueprint $table) {
            $table->dropColumn(['ho_is_valid', 'ho_notes']);
        });
    }
};
