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
        Schema::create('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->id(); // Membuat kolom 'id' bertipe bigserial, auto_increment, primary_key
            $table->string('distributor_code', 15)->nullable();
            $table->string('customer_code_prc', 50)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_address', 255)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('desa', 100)->nullable();
            $table->decimal('latitude', 11, 8)->nullable(); // Latitude (max 11 digit, 8 di belakang koma)
            $table->decimal('longitude', 11, 8)->nullable(); // Longitude (max 11 digit, 8 di belakang koma)
            $table->string('pilar', 15)->nullable();
            $table->decimal('target', 18, 2)->nullable(); // Sesuai instruksi Anda (18,2)
            $table->timestamps(); // Membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_toko_pareto_team_elite');
    }
};