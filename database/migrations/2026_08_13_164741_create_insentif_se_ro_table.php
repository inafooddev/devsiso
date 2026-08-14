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
        Schema::create('insentif_se_ro', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->comment('Format: YYYY-MM');
            $table->string('kodecabang')->nullable();
            $table->string('slsno')->nullable();
            $table->string('frekuensi', 5)->nullable()->comment('F2 or F4');
            $table->integer('total_customer')->default(0);
            $table->timestamps();

            $table->index(['bulan', 'kodecabang', 'slsno']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_se_ro');
    }
};
