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
        Schema::create('selling_in_distributor_mappings', function (Blueprint $table) {
            $table->id();
            
            // Kolom kombinasi pencarian dari RAW
            $table->string('divisi')->nullable();
            $table->string('wilayah')->nullable();
            $table->string('kode_distributor')->nullable();
            $table->string('distributor')->nullable();
            
            // Kolom tujuan mapping ke tabel master_distributors
            $table->string('distributor_code')->nullable();

            $table->timestamps();

            // Memastikan tidak ada duplikasi mapping untuk kombinasi 4 kolom tersebut
            // Karena nullable, kita biarkan unik, tapi MySQL kadang bermasalah dengan nullable unique
            // Kita asumsikan kolom-kolom ini akan diisi string kosong jika null dari excel, 
            // Namun index unique ini akan sangat menjaga integritas data.
            $table->unique(['divisi', 'wilayah', 'kode_distributor', 'distributor'], 'unique_raw_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selling_in_distributor_mappings');
    }
};
