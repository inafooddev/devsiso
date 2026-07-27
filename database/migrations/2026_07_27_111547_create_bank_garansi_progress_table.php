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
        // Menambahkan kolom progress_status di tabel bank_garansis
        Schema::table('bank_garansis', function (Blueprint $table) {
            $table->string('progress_status')->default('Belum'); // Belum, Sudah di-Follow Up, Close
        });

        // Tabel riwayat follow up
        Schema::create('bank_garansi_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_garansi_id')->constrained('bank_garansis')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Siapa yang mencatat
            $table->string('status_progress'); // Status yang di-update saat mencatat
            $table->text('catatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_garansi_follow_ups');
        
        Schema::table('bank_garansis', function (Blueprint $table) {
            $table->dropColumn('progress_status');
        });
    }
};
