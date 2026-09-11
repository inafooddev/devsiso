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
        Schema::create('jks_se_toko_ool_remarks', function (Blueprint $table) {
            $table->id();
            $table->string('distributor_code')->index();
            $table->string('customer_code')->index();
            $table->text('remark')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['distributor_code', 'customer_code'], 'uniq_ool_remark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jks_se_toko_ool_remarks');
    }
};
