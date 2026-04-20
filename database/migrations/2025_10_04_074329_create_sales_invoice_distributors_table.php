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
        Schema::create('sales_invoice_distributor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('distributor_code', 25);
            $table->string('invoice_type', 50)->nullable();
            $table->string('invoice_no', 100)->index();
            $table->date('invoice_date');
            $table->string('order_no', 100)->nullable()->index();
            $table->date('order_date')->nullable();
            $table->string('salesman_code', 255)->nullable();
            $table->string('salesman_name', 255)->nullable();
            $table->string('customer_code', 255)->nullable();
            $table->string('customer_name', 500)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('product_code', 255)->nullable();
            $table->string('product_name', 255)->nullable();
            $table->decimal('carton_qty', 18, 6)->default(0);
            $table->decimal('pack_qty', 18, 6)->default(0);
            $table->decimal('pcs_qty', 18, 6)->default(0);
            $table->decimal('quantity', 18, 6)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('bonus', 18, 6)->default(0);
            $table->boolean('is_bonus')->default(false);
            $table->decimal('unit_price', 18, 6)->default(0);
            $table->decimal('gross_amount', 18, 6)->default(0);
            $table->decimal('discount1', 18, 6)->default(0);
            $table->decimal('discount2', 18, 6)->default(0);
            $table->decimal('discount3', 18, 6)->default(0);
            $table->decimal('discount4', 18, 6)->default(0);
            $table->decimal('discount5', 18, 6)->default(0);
            $table->decimal('discount6', 18, 6)->default(0);
            $table->decimal('discount7', 18, 6)->default(0);
            $table->decimal('discount8', 18, 6)->default(0);
            $table->decimal('total_discount', 18, 6)->default(0);
            $table->decimal('dpp', 18, 6)->default(0);
            $table->decimal('tax', 18, 6)->default(0);
            $table->decimal('net_amount', 18, 6)->default(0);
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_distributor');
    }
};
