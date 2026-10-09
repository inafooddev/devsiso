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
            $table->boolean('manager_is_approved')->nullable()->after('ho_notes');
            $table->text('manager_notes')->nullable()->after('manager_is_approved');
            $table->string('manager_approved_by')->nullable()->after('manager_notes');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_kesepakatan_bersama_rwo', function (Blueprint $table) {
            $table->dropColumn([
                'manager_is_approved',
                'manager_notes',
                'manager_approved_by',
                'manager_approved_at'
            ]);
        });
    }
};
