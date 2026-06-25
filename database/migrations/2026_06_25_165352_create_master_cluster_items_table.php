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
        Schema::create('master_cluster_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_cluster_id');
            $table->unsignedBigInteger('store_id');
            $table->integer('routing_order')->default(0);
            $table->timestamps();
            
            $table->foreign('master_cluster_id')->references('id')->on('master_clusters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_cluster_items');
    }
};
