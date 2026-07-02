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
        Schema::create('list_potensi_rwo', function (Blueprint $table) {
            $table->id();
            $table->string('kuartal')->index();
            $table->string('distributor_code')->index();
            $table->string('customer_code')->index();
            $table->string('customer_name');
            $table->text('alamat')->nullable();
            $table->decimal('total_target', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_potensi_rwo');
    }
};
