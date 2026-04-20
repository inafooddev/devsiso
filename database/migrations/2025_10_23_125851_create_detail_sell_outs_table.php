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
        Schema::create('detail_sell_out', function (Blueprint $table) {
            $table->id(); // bigint auto inc/pk
            
            // Kolom Denormalisasi
            $table->string('region_code', 15);
            $table->string('region_name', 50);
            $table->string('entity_code', 15);
            $table->string('entity_name', 50);
            $table->string('branch_code', 15);
            $table->string('branch_name', 100);
            $table->string('area_code', 15);
            $table->string('area_name', 50);

            // Sales & Customer
            $table->string('sales_code', 255)->nullable();
            $table->string('sales_name', 255)->nullable();
            $table->string('cust_code_prc', 255)->nullable();
            $table->string('cust_code_dist', 255)->nullable();
            $table->string('cust_name', 500)->nullable();
            $table->string('cust_address', 500)->nullable();
            $table->string('cust_city', 50);
            $table->string('sub_channel', 50);
            $table->string('type_outlet', 50);

            // Transaksi
            $table->string('ord_no', 100)->nullable();
            $table->date('ord_date')->nullable();
            $table->string('invoice_no', 100)->nullable();
            $table->string('invoice_type', 50)->nullable();
            $table->date('invoice_date')->nullable();

            // Produk
            $table->string('prd_brand', 150); // prd.brand diubah menjadi prd_brand
            $table->string('product_group_1', 150);
            $table->string('product_group_2', 150);
            $table->string('product_group_3', 150);
            $table->string('prd_code', 15); // prd_code sebagai varchar(15), bukan PK
            $table->string('prd_name', 255);

            // Kuantitas (nama kolom disesuaikan)
            $table->decimal('qty1_car', 10, 2)->default(0); // qty1_(car)
            $table->decimal('qty2_pck', 10, 2)->default(0); // qty2_(pck)
            $table->decimal('qty3_pcs', 10, 2)->default(0); // qty3_(pcs)
            $table->decimal('qty4_pcs', 10, 2)->default(0); // qty4_(pcs)
            $table->decimal('qty5_pcs', 10, 2)->default(0); // qty5_(pcs)
            $table->string('flag_bonus', 50)->nullable();;

            // Finansial
            $table->decimal('gross_amount', 18, 6)->default(0);
            $table->decimal('line_discount_1', 18, 6)->default(0);
            $table->decimal('line_discount_2', 18, 6)->default(0);
            $table->decimal('line_discount_3', 18, 6)->default(0);
            $table->decimal('line_discount_4', 18, 6)->default(0);
            $table->decimal('line_discount_5', 18, 6)->default(0);
            $table->decimal('line_discount_6', 18, 6)->default(0);
            $table->decimal('line_discount_7', 18, 6)->default(0);
            $table->decimal('line_discount_8', 18, 6)->default(0);
            $table->decimal('total_line_discount', 18, 6)->default(0);
            $table->decimal('dpp', 18, 6)->default(0);
            $table->decimal('tax', 18, 6)->default(0);
            $table->decimal('nett_amount', 18, 6)->default(0);

            // Atribut Tambahan
            $table->string('category_item', 150);
            $table->string('vtkp', 150);
            $table->string('npd', 150);
            
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_sell_out');
    }
};
