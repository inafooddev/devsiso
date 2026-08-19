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
        Schema::create('insentif_mingguan_produk_grups', function (Blueprint $table) {
            $table->id();
            $table->string('product_group_3')->nullable();
            $table->string('prd_code')->unique();
            $table->string('prd_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_mingguan_produk_grups');
    }
};
