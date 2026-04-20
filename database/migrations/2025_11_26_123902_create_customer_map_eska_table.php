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
        Schema::create('customer_map_eska', function (Blueprint $table) {
            // id;bigserial;y;y;;
            $table->id();

            // bln;date;;;;
            $table->date('bln')->nullable();

            // distid;varchar(100);;;;
            $table->string('distid', 100)->nullable();

            // branch_dist;varchar(100);;;;
            $table->string('branch_dist', 100)->nullable();

            // custno_dist;varchar(300);;;;
            $table->string('custno_dist', 300)->nullable();

            // branch;varchar(15);;;;
            $table->string('branch', 15)->nullable();

            // custno;varchar(25);;;;
            $table->string('custno', 25)->nullable();

            // created_at & updated_at;timestamp;;;;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_map_eska');
    }
};