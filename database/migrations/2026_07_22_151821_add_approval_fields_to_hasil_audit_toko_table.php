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
            $table->string('status_approval', 30)->default('Pending')->after('keterangan_hasil_audit');
            $table->text('alasan_reject')->nullable()->after('status_approval');
            $table->string('approved_by', 150)->nullable()->after('alasan_reject');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_audit_toko', function (Blueprint $table) {
            $table->dropColumn(['status_approval', 'alasan_reject', 'approved_by', 'approved_at']);
        });
    }
};
