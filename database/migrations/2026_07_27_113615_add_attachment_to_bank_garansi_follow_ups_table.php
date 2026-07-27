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
        Schema::table('bank_garansi_follow_ups', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_garansi_follow_ups', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
