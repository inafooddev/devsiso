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
        Schema::table('jks_salesmans', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('w4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jks_salesmans', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
