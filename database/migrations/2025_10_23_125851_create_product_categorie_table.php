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
        Schema::create('product_categories', function (Blueprint $table) {
            // [PERUBAHAN] ID menjadi auto-incrementing integer
            $table->id(); 
            $table->string('product_id', 15);
            $table->string('category_id', 15);
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('product_id')->references('product_id')->on('product_masters')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onUpdate('cascade')->onDelete('cascade');

            // Optional: Composite unique key to prevent duplicate mappings
            $table->unique(['product_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
};

