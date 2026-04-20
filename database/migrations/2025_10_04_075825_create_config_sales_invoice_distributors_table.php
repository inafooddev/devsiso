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
        Schema::create('config_sales_invoice_distributor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('distributor_code', 25)->nullable();
            $table->string('config_name', 100);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_sales_invoice_distributor');
    }
};
