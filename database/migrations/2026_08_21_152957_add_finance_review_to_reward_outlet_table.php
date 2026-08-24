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
            $table->unsignedBigInteger('finance_by')->nullable()->default(null);
            $table->text('finance_note')->nullable()->default(null);
            $table->timestamp('finance_noted_at')->nullable()->default(null);
            $table->timestamp('finalized_at')->nullable()->default(null);

            $table->foreign('finance_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_outlet', function (Blueprint $table) {
            $table->dropForeign(['finance_by']);
            $table->dropColumn(['finance_by', 'finance_note', 'finance_noted_at', 'finalized_at']);
        });
    }
};
