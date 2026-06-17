<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extractor_temp_results', function (Blueprint $table) {
            $table->id();
            
            // Kolom untuk melacak sesi/batch proses (agar mudah dihapus nantinya)
            $table->uuid('batch_id')->index(); 
            
            // Kolom bawaan (selalu ada)
            $table->string('nama_file');
            $table->string('kode_dist')->nullable();
            $table->string('group_name');
            $table->decimal('nominal_surat', 15, 2)->nullable();
            
            // Tipe data: 'rekap' (1 baris per file) atau 'rincian' (semua baris detail)
            $table->string('mode')->default('rekap'); 
            
            // Data Dinamis (Hasil ekstraksi seperti QTY, DISC 4, NETT, dll disimpan di sini)
            // Di Postgres, JSONB sangat cepat untuk di-query.
            $table->jsonb('extracted_data'); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extractor_temp_results');
    }
};
