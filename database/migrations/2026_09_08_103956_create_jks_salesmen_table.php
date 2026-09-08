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
        Schema::create('jks_salesmans', function (Blueprint $table) {
            $table->id();
            $table->string('bulan')->nullable()->index();
            $table->string('distributor_code')->nullable()->index();
            $table->string('salesman_code')->nullable()->index();
            $table->string('customer_code')->nullable()->index();
            $table->string('h1')->nullable();
            $table->string('h2')->nullable();
            $table->string('h3')->nullable();
            $table->string('h4')->nullable();
            $table->string('h5')->nullable();
            $table->string('h6')->nullable();
            $table->string('h7')->nullable();
            $table->string('w1')->nullable();
            $table->string('w2')->nullable();
            $table->string('w3')->nullable();
            $table->string('w4')->nullable();
            $table->string('update_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jks_salesmans');
    }
};
