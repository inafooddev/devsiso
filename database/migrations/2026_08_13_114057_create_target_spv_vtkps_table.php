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
        Schema::create('target_spv_vtkps', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7); // Format: YYYY-MM
            $table->string('cabang');
            $table->string('produk_grup');
            $table->decimal('target', 20, 2)->default(0);
            $table->timestamps();

            // Constraint unik untuk mencegah duplikasi (upsert key)
            $table->unique(['bulan', 'cabang', 'produk_grup'], 'tgt_spv_vtkp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_spv_vtkps');
    }
};
