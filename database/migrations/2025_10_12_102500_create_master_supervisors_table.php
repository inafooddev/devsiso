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
        Schema::create('master_supervisors', function (Blueprint $table) {
            $table->string('supervisor_code', 15)->primary();
            $table->string('supervisor_name', 50);
            $table->string('description', 100)->nullable();
            $table->string('area_code', 15);
            $table->timestamps();

            // Definisi foreign key constraint
            $table->foreign('area_code')
                  ->references('area_code')
                  ->on('master_areas')
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
        Schema::dropIfExists('master_supervisors');
    }
};
