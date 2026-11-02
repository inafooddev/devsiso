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
        if (!Schema::hasTable('sellout_per_cabang_per_qty')) {
            Schema::create('sellout_per_cabang_per_qty', function (Blueprint $table) {
                $table->id();
                $table->date('bulan')->nullable();
                $table->string('region', 50)->nullable();
                $table->string('area', 50)->nullable();
                $table->string('cabang', 50)->nullable();
                $table->string('kd_distributor', 50)->nullable();
                $table->string('nama_produk_mapping', 100)->nullable();
                $table->string('reg_fest', 10)->nullable();
                $table->decimal('qty_ctn', 15, 2)->default(0);
                $table->decimal('value_net', 18, 6)->default(0);
                $table->timestamps();

                // Indexing for fast queries
                $table->index('bulan');
                $table->index('kd_distributor');
                $table->index(['region', 'area']);
                $table->index('cabang');
                $table->index('nama_produk_mapping');
                $table->index('reg_fest');
                $table->index(['bulan', 'kd_distributor', 'nama_produk_mapping'], 'idx_sopcpq_composite');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellout_per_cabang_per_qty');
    }
};
