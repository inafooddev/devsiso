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
        Schema::create('jks_se_master_toko_ool', function (Blueprint $table) {
            $table->id();
            $table->string('region_code')->nullable();
            $table->string('region_name')->nullable();
            $table->string('area_code')->nullable();
            $table->string('area_name')->nullable();
            $table->string('supervisor_code')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('distributor_code')->nullable();
            $table->string('distributor_name')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('customer_eska')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('alamat')->nullable();
            $table->decimal('avg_value_net', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jks_se_master_toko_ool');
    }
};
