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
        Schema::create('bank_garansis', function (Blueprint $table) {
            $table->id();
            $table->string('distributor_code');
            $table->string('nama_bank');
            $table->string('nomor_jaminan');
            $table->decimal('nilai_jaminan', 15, 2);
            $table->date('tanggal_terbit');
            $table->date('tanggal_jatuh_tempo');
            $table->string('status')->default('Aktif');
            $table->text('keterangan')->nullable();
            $table->string('dokumen_lampiran')->nullable();
            $table->timestamps();
            
            $table->foreign('distributor_code')
                  ->references('distributor_code')
                  ->on('master_distributors')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_garansis');
    }
};
