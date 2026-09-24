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
        Schema::create('store_remark_chats', function (Blueprint $table) {
            $table->id();
            $table->string('kuartal');
            $table->string('distributor_code');
            $table->string('customer_code');
            $table->unsignedBigInteger('user_id');
            $table->text('message');
            $table->timestamps();

            // Foreign key to users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_remark_chats');
    }
};
