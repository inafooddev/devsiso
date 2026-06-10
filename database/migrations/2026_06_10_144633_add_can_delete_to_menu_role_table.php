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
        Schema::table('menu_role', function (Blueprint $table) {
            $table->boolean('can_delete')->default(false)->after('can_add');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_role', function (Blueprint $table) {
            $table->dropColumn('can_delete');
        });
    }
};
