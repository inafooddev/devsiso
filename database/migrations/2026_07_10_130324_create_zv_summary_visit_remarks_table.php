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
        Schema::create('zv_summary_visit_remarks', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('team_code', 50)->nullable();
            $table->string('custno', 50)->nullable();
            $table->text('remark')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
            
            // Optional: Indexing for faster lookups
            $table->index(['tanggal', 'team_code', 'custno']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zv_summary_visit_remarks');
    }
};
