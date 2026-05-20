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
        Schema::table('reward_outlet', function (Blueprint $table) {
            // Rename columns if they exist
            if (Schema::hasColumn('reward_outlet', 'region') && !Schema::hasColumn('reward_outlet', 'region_name')) {
                $table->renameColumn('region', 'region_name');
            }
            if (Schema::hasColumn('reward_outlet', 'area') && !Schema::hasColumn('reward_outlet', 'area_name')) {
                $table->renameColumn('area', 'area_name');
            }
            if (Schema::hasColumn('reward_outlet', 'cabang') && !Schema::hasColumn('reward_outlet', 'branch_name')) {
                $table->renameColumn('cabang', 'branch_name');
            }
        });

        Schema::table('reward_outlet', function (Blueprint $table) {
            // Add new columns
            if (!Schema::hasColumn('reward_outlet', 'region_code')) {
                $table->string('region_code')->nullable()->after('id');
                $table->index('region_code');
            }
            if (!Schema::hasColumn('reward_outlet', 'area_code')) {
                $table->string('area_code')->nullable()->after('region_name');
                $table->index('area_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_outlet', function (Blueprint $table) {
            if (Schema::hasColumn('reward_outlet', 'region_name') && !Schema::hasColumn('reward_outlet', 'region')) {
                $table->renameColumn('region_name', 'region');
            }
            if (Schema::hasColumn('reward_outlet', 'area_name') && !Schema::hasColumn('reward_outlet', 'area')) {
                $table->renameColumn('area_name', 'area');
            }
            if (Schema::hasColumn('reward_outlet', 'branch_name') && !Schema::hasColumn('reward_outlet', 'cabang')) {
                $table->renameColumn('branch_name', 'cabang');
            }
            
            if (Schema::hasColumn('reward_outlet', 'region_code')) {
                $table->dropColumn('region_code');
            }
            if (Schema::hasColumn('reward_outlet', 'area_code')) {
                $table->dropColumn('area_code');
            }
        });
    }
};
