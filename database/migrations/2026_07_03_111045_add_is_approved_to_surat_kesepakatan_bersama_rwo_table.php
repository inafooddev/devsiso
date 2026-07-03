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
            $table->boolean('is_approved')->nullable()->comment('True: Approved, False: Rejected, Null: Pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_kesepakatan_bersama_rwo', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
