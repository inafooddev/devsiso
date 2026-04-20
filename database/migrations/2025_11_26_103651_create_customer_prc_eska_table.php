<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_prc_eska', function (Blueprint $table) {
            $table->id(); // bigserial, auto_inc, primary key

            $table->date('bln')->nullable();
            $table->string('custno', 25)->nullable();
            $table->string('custname', 300)->nullable();
            $table->string('custadd1', 300)->nullable();
            $table->string('ccity', 50)->nullable();
            $table->string('cterm', 10)->nullable();
            $table->string('typeout', 10)->nullable();
            $table->string('grupout', 10)->nullable();
            $table->string('gharga', 10)->nullable();
            $table->string('flagpay', 10)->nullable();
            $table->string('flagout', 10)->nullable();
            $table->string('kodecabang', 15)->nullable();
            
            // Decimal untuk koordinat (presisi 11, skala 8 sesuai permintaan)
            $table->decimal('la', 11, 8)->nullable(); // Latitude
            $table->decimal('lg', 11, 8)->nullable(); // Longitude
            
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_prc_eska');
    }
};