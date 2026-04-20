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
        Schema::create('master_branches', function (Blueprint $table) {
            $table->string('branch_code', 15)->primary();
            $table->string('branch_name', 50);
            $table->string('supervisor_code', 15);
            $table->timestamps();

            // Definisi foreign key constraint
            $table->foreign('supervisor_code')
                  ->references('supervisor_code')
                  ->on('master_supervisors')
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
        Schema::dropIfExists('master_branches');
    }
};
