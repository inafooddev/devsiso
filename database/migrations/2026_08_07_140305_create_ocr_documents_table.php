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
        Schema::create('ocr_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_name');
            $table->string('distributor_code')->nullable();
            $table->date('tanggal');
            $table->text('raw_text')->nullable();
            $table->decimal('nominal_extracted', 15, 2)->default(0);
            $table->string('status')->default('verified'); // e.g. pending, verified
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_documents');
    }
};
