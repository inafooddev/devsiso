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
        if (!Schema::hasTable('nominal_qc_dist')) {
            Schema::create('nominal_qc_dist', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('distributor_code');
                $table->integer('qty');
                $table->decimal('discount_4', 15, 4);
                $table->decimal('discount_8', 15, 4);
                $table->decimal('neto', 15, 4);
                $table->decimal('nominal_surat', 15, 4);
                $table->string('file_surat')->nullable();
                $table->timestamp('timestamp')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominal_qc_dist');
    }
};
