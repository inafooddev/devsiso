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
        Schema::create('jks_import_temps', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id');
            $table->integer('row_number');
            $table->string('bulan')->nullable();
            $table->string('distributor_code')->nullable();
            $table->string('salesman_code')->nullable();
            $table->string('customer_code')->nullable();
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
            $table->string('status')->default('pending'); // pending, ready, failed, success
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['batch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jks_import_temps');
    }
};
