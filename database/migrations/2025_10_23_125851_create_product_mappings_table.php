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
        Schema::create('product_mappings', function (Blueprint $table) {
            $table->id(); // bigint auto-increment / pk
            $table->string('distributor_code', 15);
            $table->string('product_code_dist', 255)->nullable();
            $table->string('product_name_dist', 255)->nullable();
            $table->string('product_code_prc', 255)->nullable();
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('distributor_code')
                  ->references('distributor_code')
                  ->on('master_distributors')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Hapus mapping jika distributor dihapus
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_mappings');
    }
};
