<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selling_in', function (Blueprint $table) {
            $table->id();
            $table->date('bulan');
            $table->integer('tahun');
            $table->string('rsm', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('kd_spv', 50)->nullable();
            $table->string('nama_spv', 100)->nullable();
            $table->string('cabang', 50)->nullable();
            $table->string('kd_distributor', 50)->nullable();
            $table->string('nama_distributor', 255)->nullable();
            $table->string('nama_distributor_fix', 50)->nullable();
            $table->string('nama_produk', 255)->nullable();
            $table->string('nama_produk_mapping', 100)->nullable();
            $table->string('jenis', 50)->nullable();
            $table->string('reg_fes', 50)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->string('top_item', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('sub_brand', 100)->nullable();
            
            // Numeric fields
            $table->decimal('ktn_jual', 10, 2)->default(0);
            $table->decimal('pcs_jual', 10, 2)->default(0);
            $table->decimal('value_jual', 18, 6)->default(0);
            
            $table->decimal('ktn_retur', 10, 2)->default(0);
            $table->decimal('pcs_retur', 10, 2)->default(0);
            $table->decimal('value_retur', 18, 6)->default(0);
            
            $table->decimal('ktn_net', 10, 2)->default(0);
            $table->decimal('pcs_net', 10, 2)->default(0);
            $table->decimal('value_net', 18, 6)->default(0);
            
            $table->timestamps();
            
            // Indexing for faster filtering
            $table->index(['bulan', 'tahun']);
            $table->index('kd_distributor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selling_in');
    }
};