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
            $table->boolean('can_view')->default(false)->after('role_id');
            $table->boolean('can_edit')->default(false)->after('can_view');
            $table->boolean('can_import')->default(false)->after('can_edit');
            $table->boolean('can_export')->default(false)->after('can_import');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_role', function (Blueprint $table) {
            $table->dropColumn(['can_view', 'can_edit', 'can_import', 'can_export']);
        });
    }
};
