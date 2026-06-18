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
        Schema::create('remark_analisa_kunjungan', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('visit_id')->nullable();
            $table->string('muid')->nullable();
            $table->string('custno')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('remark')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            // Tambahkan index untuk mempercepat pencarian berdasarkan kombinasi ini
            $table->index(['visit_id', 'muid', 'custno', 'tanggal'], 'idx_remark_visit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remark_analisa_kunjungan');
    }
};
