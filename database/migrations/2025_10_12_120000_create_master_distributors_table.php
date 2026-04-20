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
        Schema::create('master_distributors', function (Blueprint $table) {
            $table->string('distributor_code', 15)->primary();
            $table->string('distributor_name', 100);
            $table->date('resign_date')->nullable();
            $table->date('join_date')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);

            // Kolom redundan dari tabel lain
            $table->string('region_code', 15);
            $table->string('region_name', 50);

            $table->string('area_code', 15);
            $table->string('area_name', 50);

            $table->string('supervisor_code', 15);
            $table->string('supervisor_name', 50);

            $table->string('branch_code', 15);
            $table->string('branch_name', 50);
            
            $table->timestamps();

            // Definisi Foreign Keys
            $table->foreign('region_code')->references('region_code')->on('master_regions')->onUpdate('cascade');
            $table->foreign('area_code')->references('area_code')->on('master_areas')->onUpdate('cascade');
            $table->foreign('supervisor_code')->references('supervisor_code')->on('master_supervisors')->onUpdate('cascade');
            $table->foreign('branch_code')->references('branch_code')->on('master_branches')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_distributors');
    }
};
