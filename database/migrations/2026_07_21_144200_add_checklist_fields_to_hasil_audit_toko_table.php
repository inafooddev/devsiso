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
            $table->boolean('is_toko_fisik')->default(false)->after('keterangan_hasil_audit');
            $table->boolean('is_nama_pemilik')->default(false)->after('is_toko_fisik');
            $table->boolean('is_nama_ktp')->default(false)->after('is_nama_pemilik');
            $table->boolean('is_nik_ktp')->default(false)->after('is_nama_ktp');
            $table->boolean('is_no_hp')->default(false)->after('is_nik_ktp');
            $table->boolean('is_no_rekening')->default(false)->after('is_no_hp');
            $table->boolean('is_an_rekening')->default(false)->after('is_no_rekening');
            $table->boolean('is_titik_koordinat')->default(false)->after('is_an_rekening');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_audit_toko', function (Blueprint $table) {
            $table->dropColumn([
                'is_toko_fisik',
                'is_nama_pemilik',
                'is_nama_ktp',
                'is_nik_ktp',
                'is_no_hp',
                'is_no_rekening',
                'is_an_rekening',
                'is_titik_koordinat',
            ]);
        });
    }
};
