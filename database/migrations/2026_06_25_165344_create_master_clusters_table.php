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
        Schema::create('master_clusters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('team_sales');
            $table->unsignedBigInteger('center_store_id');
            $table->decimal('total_distance', 10, 2)->nullable();
            $table->integer('total_duration_minutes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_clusters');
    }
};
