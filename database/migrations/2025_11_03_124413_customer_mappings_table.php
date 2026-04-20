<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('distributor_code', 15);
            $table->string('customer_code_prc', 15);
            $table->string('customer_code_dist', 255);
            $table->string('customer_name', 255)->nullable();

            // Untuk PostgreSQL, cukup gunakan timestamps() tanpa "ON UPDATE"
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_mappings');
    }
};
