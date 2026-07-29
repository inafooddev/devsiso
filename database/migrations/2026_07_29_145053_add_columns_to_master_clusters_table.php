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
        Schema::table('master_clusters', function (Blueprint $table) {
            $table->string('distributor_code')->nullable();
            $table->integer('items_count')->default(0);
            $table->string('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_clusters', function (Blueprint $table) {
            $table->dropColumn(['distributor_code', 'items_count', 'created_by']);
        });
    }
};
