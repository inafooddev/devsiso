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
        Schema::create('top_item_achievement', function (Blueprint $table) {
            $table->id();
            $table->date('period')->index();
            $table->string('distributor_code')->index();
            $table->string('uniq_code')->index();
            $table->string('pcode_prc')->index();
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('value', 15, 2)->default(0);
            $table->timestamps();

            // Optional unique constraint, though not strictly required, good for data integrity
            $table->unique(['period', 'distributor_code', 'uniq_code', 'pcode_prc'], 'uk_top_item_achievement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_item_achievement');
    }
};
