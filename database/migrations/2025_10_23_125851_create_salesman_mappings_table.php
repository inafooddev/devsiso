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
        Schema::create('salesman_mappings', function (Blueprint $table) {
            $table->id(); // bigint auto inc /pk
            $table->string('distributor_code', 15);
            $table->string('salesman_code_dist', 255)->nullable();
            $table->string('salesman_name_dist', 255)->nullable();
            $table->string('salesman_code_prc', 15)->nullable(); // Dibuat nullable untuk fleksibilitas
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('distributor_code')
                  ->references('distributor_code')
                  ->on('master_distributors')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Foreign Key ke tabel salesmans (principal)
            $table->foreign('salesman_code_prc')
                  ->references('salesman_code')
                  ->on('salesmans')
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // Set null jika salesman principal dihapus

            // Mencegah duplikat pemetaan per distributor
            $table->unique(['distributor_code', 'salesman_code_dist']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salesman_mappings');
    }
};
