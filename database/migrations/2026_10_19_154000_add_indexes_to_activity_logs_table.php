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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('created_at', 'idx_activity_logs_created_at');
            $table->index('user_id', 'idx_activity_logs_user_id');
            $table->index('action', 'idx_activity_logs_action');
            $table->index(['user_id', 'created_at'], 'idx_activity_logs_user_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_logs_created_at');
            $table->dropIndex('idx_activity_logs_user_id');
            $table->dropIndex('idx_activity_logs_action');
            $table->dropIndex('idx_activity_logs_user_created');
        });
    }
};
