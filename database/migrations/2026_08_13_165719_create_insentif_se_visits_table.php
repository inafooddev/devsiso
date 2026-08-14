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
        Schema::create('insentif_se_visits', function (Blueprint $table) {
            $table->id();
            $table->string('bulan', 7)->comment('Format: YYYY-MM');
            $table->string('distributor_code')->nullable();
            $table->string('salesman_code')->nullable();
            $table->integer('pc')->default(0)->comment('Planned Call');
            $table->integer('ac')->default(0)->comment('Actual Call');
            $table->integer('ec')->default(0)->comment('Effective Call');
            $table->timestamps();

            $table->index(['bulan', 'distributor_code', 'salesman_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insentif_se_visits');
    }
};
