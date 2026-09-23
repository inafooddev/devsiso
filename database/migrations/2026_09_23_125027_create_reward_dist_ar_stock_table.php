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
        Schema::create('reward_dist_ar_stock', function (Blueprint $table) {
            $table->id();
            $table->date('bulan')->comment('Bulan pencatatan');
            $table->string('cabang')->comment('Kode atau Nama Cabang');
            $table->integer('ar')->comment('Nilai keterlambatan AR dalam hari (bisa minus atau plus)');
            $table->decimal('stock', 5, 2)->comment('Persentase pencapaian stock (contoh: 85.50 untuk 85.5%)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_dist_ar_stock');
    }
};
