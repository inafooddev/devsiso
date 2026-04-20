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
        Schema::create('master_areas', function (Blueprint $table) {
            $table->string('area_code', 15)->primary();
            $table->string('area_name', 50);
            $table->string('region_code', 15);
            $table->timestamps();

            // Definisi foreign key constraint
            $table->foreign('region_code')
                  ->references('region_code')
                  ->on('master_regions')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_areas');
    }
};
