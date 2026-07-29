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
        Schema::table('master_cluster_items', function (Blueprint $table) {
            $table->index('store_id', 'idx_mci_store_id');
        });

        Schema::table('batas_wilayah', function (Blueprint $table) {
            $table->index('wadmkc', 'idx_bw_wadmkc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_cluster_items', function (Blueprint $table) {
            $table->dropIndex('idx_mci_store_id');
        });

        Schema::table('batas_wilayah', function (Blueprint $table) {
            $table->dropIndex('idx_bw_wadmkc');
        });
    }
};
