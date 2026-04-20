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
        Schema::create('salesmans', function (Blueprint $table) {
            $table->string('salesman_code', 15)->primary();
            $table->string('distributor_code', 15);
            $table->string('salesman_name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('distributor_code')
                  ->references('distributor_code')
                  ->on('master_distributors')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salesmans');
    }
};
