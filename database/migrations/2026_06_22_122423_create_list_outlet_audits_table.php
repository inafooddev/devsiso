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
        Schema::create('list_outlet_audit', function (Blueprint $table) {
            $table->id();
            $table->string('distributor_code')->index();
            $table->string('customer_code')->index();
            $table->string('customer_name');
            $table->text('customer_address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_outlet_audit');
    }
};
