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
        if (!Schema::hasTable('validasi_geotag')) {
            Schema::create('validasi_geotag', function (Blueprint $table) {
                $table->id();
                $table->string('kodecabang', 50)->comment('Eskalink distributor code (frute.kodecabang)');
                $table->string('custno', 50)->comment('Customer code toko');
                $table->decimal('la', 11, 8)->nullable()->comment('Latitude sah hasil keputusan verifikator');
                $table->decimal('lg', 11, 8)->nullable()->comment('Longitude sah hasil keputusan verifikator');
                $table->string('status', 20)->comment('VALID atau TIDAK_VALID');
                $table->text('catatan')->nullable()->comment('Catatan/alasan verifikator');
                $table->unsignedBigInteger('validated_by')->nullable()->comment('User ID verifikator');
                $table->string('validator_name', 150)->nullable()->comment('Nama verifikator saat validasi');
                $table->timestamp('validated_at')->nullable()->comment('Waktu keputusan validasi');
                $table->timestamps();

                // Composite unique: 1 toko di 1 cabang hanya punya 1 status sah
                $table->unique(['kodecabang', 'custno'], 'uq_validasi_geotag_toko');

                // Performance Indexes
                $table->index('status', 'idx_validasi_geotag_status');
                $table->index('kodecabang', 'idx_validasi_geotag_kodecabang');
                $table->index('validated_at', 'idx_validasi_geotag_validated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validasi_geotag');
    }
};
