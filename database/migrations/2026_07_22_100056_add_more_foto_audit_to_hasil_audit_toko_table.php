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
        Schema::table('hasil_audit_toko', function (Blueprint $table) {
            $table->string('foto_audit4')->nullable()->after('foto_audit3');
            $table->string('foto_audit5')->nullable()->after('foto_audit4');
            $table->string('foto_audit6')->nullable()->after('foto_audit5');
            $table->string('foto_audit7')->nullable()->after('foto_audit6');
            $table->string('foto_audit8')->nullable()->after('foto_audit7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_audit_toko', function (Blueprint $table) {
            $table->dropColumn(['foto_audit4', 'foto_audit5', 'foto_audit6', 'foto_audit7', 'foto_audit8']);
        });
    }
};
