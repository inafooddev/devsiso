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
        Schema::create('top_item_master_customer', function (Blueprint $table) {
            $table->id();
            $table->string('distributor_code')->index();
            $table->string('uniq_code')->index();
            $table->string('custno')->index();
            $table->string('customer_name')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicates
            $table->unique(['distributor_code', 'uniq_code', 'custno'], 'uk_top_item_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_item_master_customer');
    }
};
