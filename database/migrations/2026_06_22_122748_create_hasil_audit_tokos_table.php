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
        Schema::create('hasil_audit_toko', function (Blueprint $table) {
            $table->id();
            $table->string('auditor')->index();
            $table->string('distributor_code')->index();
            $table->string('customer_code')->index();
            $table->string('customer_name');
            $table->text('customer_address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('foto_audit1')->nullable();
            $table->string('foto_audit2')->nullable();
            $table->string('foto_audit3')->nullable();
            $table->text('keterangan_hasil_audit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_audit_toko');
    }
};
