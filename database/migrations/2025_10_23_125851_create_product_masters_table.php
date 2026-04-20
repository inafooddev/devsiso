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
        Schema::create('product_masters', function (Blueprint $table) {
            $table->string('product_id', 15)->primary();
            
            // Foreign Keys
            $table->string('line_id', 15);
            $table->string('brand_id', 15);
            $table->string('product_group_id', 15);
            $table->string('sub_brand_id', 15)->nullable(); // Nullable jika sub-brand opsional

            // Denormalized Names (Redundant Data)
            $table->string('line_name', 150);
            $table->string('brand_name', 150);
            $table->string('brand_unit_name', 150);
            $table->string('sub_brand_name', 150)->nullable(); // Nullable jika sub-brand opsional
            
            // Product Specific Data
            $table->string('product_name', 255);
            $table->boolean('is_active')->default(true);
            $table->string('base_unit', 20);
            $table->string('uom1', 20)->nullable();
            $table->string('uom2', 20)->nullable();
            $table->string('uom3', 20)->nullable();
            $table->decimal('conv_unit1', 10, 2)->nullable();
            $table->decimal('conv_unit2', 10, 2)->nullable();
            $table->decimal('conv_unit3', 10, 2)->nullable();
            $table->decimal('price_zone1', 18, 2)->nullable();
            $table->decimal('price_zone2', 18, 2)->nullable();
            $table->decimal('price_zone3', 18, 2)->nullable();
            $table->decimal('price_zone4', 18, 2)->nullable();
            $table->decimal('price_zone5', 18, 2)->nullable();
            
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('line_id')->references('line_id')->on('product_lines')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('brand_id')->references('brand_id')->on('product_brands')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('product_group_id')->references('product_group_id')->on('product_groups')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('sub_brand_id')->references('sub_brand_id')->on('product_sub_brands')->onUpdate('cascade')->onDelete('set null'); // Set null jika sub-brand dihapus
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_masters');
    }
};
