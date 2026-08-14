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
        Schema::create('target_se_vtkps', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->comment('Format: YYYY-MM');
            $table->string('distributor_code');
            $table->string('salesman_code');
            $table->string('produk_grup');
            $table->decimal('target', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['bulan', 'distributor_code', 'salesman_code', 'produk_grup'], 'target_se_vtkps_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_se_vtkps');
    }
};
