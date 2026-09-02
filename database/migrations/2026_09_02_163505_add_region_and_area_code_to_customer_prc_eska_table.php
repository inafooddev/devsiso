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
        Schema::table('customer_prc_eska', function (Blueprint $table) {
            $table->string('region_code', 20)->nullable()->after('id');
            $table->string('area_code', 20)->nullable()->after('region_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_prc_eska', function (Blueprint $table) {
            $table->dropColumn(['region_code', 'area_code']);
        });
    }
};
