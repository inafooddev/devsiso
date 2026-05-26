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
        Schema::table('salesmans', function (Blueprint $table) {
            $table->date('join_date')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->string('foto_npwp')->nullable();
            $table->string('bank')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_no')->nullable();
            $table->string('foto_bank')->nullable();
            $table->string('foto_skb')->nullable();
            $table->boolean('is_principle')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesmans', function (Blueprint $table) {
            $table->dropColumn([
                'join_date',
                'foto_ktp',
                'foto_npwp',
                'bank',
                'bank_name',
                'bank_no',
                'foto_bank',
                'foto_skb',
                'is_principle',
            ]);
        });
    }
};
