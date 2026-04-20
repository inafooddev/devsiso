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
        Schema::create('customer_dist_eska', function (Blueprint $table) {
            // id;bigserial;y;y;;
            $table->id(); 
            
            // bln;date;;;;
            $table->date('bln')->nullable();
            
            // distid;varchar(100);;;;
            $table->string('distid', 100)->nullable();
            
            // branch;varchar(100);;;;
            $table->string('branch', 100)->nullable();
            
            // custno;varchar(300);;;;
            $table->string('custno', 300)->nullable();
            
            // custname;varchar(300);;;;
            $table->string('custname', 300)->nullable();
            
            // created_at & updated_at;timestamp;;;;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_dist_eska');
    }
};