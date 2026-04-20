<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('selling_out_eskalink', function (Blueprint $table) {
            // id;bigserial;y;y;;
            $table->id();

            // region_code;varchar(15);;;;
            $table->string('region_code', 50)->nullable();

            // region_name;varchar(50);;;;
            $table->string('region_name', 50)->nullable();

            // entity_code;varchar(15);;;;
            $table->string('entity_code', 50)->nullable();

            // entity_name;varchar(50);;;;
            $table->string('entity_name', 50)->nullable();

            // branch_code;varchar(15);;;;
            $table->string('branch_code', 50)->nullable();

            // branch_name;varchar(100);;;;
            $table->string('branch_name', 100)->nullable();

            // area_code;varchar(15);;;;
            $table->string('area_code', 50)->nullable();

            // area_name;varchar(50);;;;
            $table->string('area_name', 50)->nullable();

            // sales_code;varchar(255);;;;
            $table->string('sales_code', 255)->nullable();

            // sales_name;varchar(255);;;;
            $table->string('sales_name', 255)->nullable();

            // cust_code_prc;varchar(255);;;;
            $table->string('cust_code_prc', 255)->nullable();

            // cust_code_dist;varchar(255);;;;
            $table->string('cust_code_dist', 255)->nullable();

            // cust_name;varchar(500);;;;
            $table->string('cust_name', 500)->nullable();

            // cust_address;varchar(500);;;;
            $table->string('cust_address', 500)->nullable();

            // cust_city;varchar(50);;;;
            $table->string('cust_city', 50)->nullable();

            // sub_channel;varchar(50);;;;
            $table->string('sub_channel', 50)->nullable();

            // type_outlet;varchar(50);;;;
            $table->string('type_outlet', 50)->nullable();

            // ord_no;varchar(100);;;;
            $table->string('ord_no', 100)->nullable();

            // ord_date;date;;;;
            $table->date('ord_date')->nullable();

            // invoice_no;varchar(100);;;;
            $table->string('invoice_no', 100)->nullable();

            // invoice_type;varchar(50);;;;
            $table->string('invoice_type', 50)->nullable();

            // invoice_date;date;;;;
            $table->date('invoice_date')->nullable();

            // prd_brand;varchar(150);;;;
            $table->string('prd_brand', 150)->nullable();

            // product_group_1;varchar(150);;;;
            $table->string('product_group_1', 150)->nullable();

            // product_group_2;varchar(150);;;;
            $table->string('product_group_2', 150)->nullable();

            // product_group_3;varchar(150);;;;
            $table->string('product_group_3', 150)->nullable();

            // prd_code;varchar(15);;;;
            $table->string('prd_code', 15)->nullable();

            // prd_name;varchar(255);;;;
            $table->string('prd_name', 255)->nullable();

            // qty1_car;numeric(10,2);;;;
            $table->decimal('qty1_car', 10, 2)->nullable();

            // qty2_pck;numeric(10,2);;;;
            $table->decimal('qty2_pck', 10, 2)->nullable();

            // qty3_pcs;numeric(10,2);;;;
            $table->decimal('qty3_pcs', 10, 2)->nullable();

            // qty4_pcs;numeric(10,2);;;;
            $table->decimal('qty4_pcs', 10, 2)->nullable();

            // qty5_pcs;numeric(10,2);;;;
            $table->decimal('qty5_pcs', 10, 2)->nullable();

            // flag_bonus;varchar(50);;;;
            $table->string('flag_bonus', 50)->nullable();

            // gross_amount;numeric(18,6);;;;
            $table->decimal('gross_amount', 18, 6)->nullable();

            // line_discount_1;numeric(18,6);;;;
            $table->decimal('line_discount_1', 18, 6)->nullable();

            // line_discount_2;numeric(18,6);;;;
            $table->decimal('line_discount_2', 18, 6)->nullable();

            // line_discount_3;numeric(18,6);;;;
            $table->decimal('line_discount_3', 18, 6)->nullable();

            // line_discount_4;numeric(18,6);;;;
            $table->decimal('line_discount_4', 18, 6)->nullable();

            // line_discount_5;numeric(18,6);;;;
            $table->decimal('line_discount_5', 18, 6)->nullable();

            // line_discount_6;numeric(18,6);;;;
            $table->decimal('line_discount_6', 18, 6)->nullable();

            // line_discount_7;numeric(18,6);;;;
            $table->decimal('line_discount_7', 18, 6)->nullable();

            // line_discount_8;numeric(18,6);;;;
            $table->decimal('line_discount_8', 18, 6)->nullable();

            // total_line_discount;numeric(18,6);;;;
            $table->decimal('total_line_discount', 18, 6)->nullable();

            // dpp;numeric(18,6);;;;
            $table->decimal('dpp', 18, 6)->nullable();

            // tax;numeric(18,6);;;;
            $table->decimal('tax', 18, 6)->nullable();

            // nett_amount;numeric(18,6);;;;
            $table->decimal('nett_amount', 18, 6)->nullable();

            // category_item;varchar(150);;;;
            $table->string('category_item', 150)->nullable();

            // vtkp;varchar(150);;;;
            $table->string('vtkp', 150)->nullable();

            // npd;varchar(150);;;;
            $table->string('npd', 150)->nullable();

            // created_at & updated_at;timestamp;;;;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selling_out_eskalink');
    }
};