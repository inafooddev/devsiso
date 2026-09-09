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
        Schema::create('jks_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maker_id');
            $table->unsignedBigInteger('checker_id')->nullable();
            $table->string('distributor_code')->nullable();
            $table->string('action_type'); // TUKAR_HARI, TUKAR_MINGGU, TUKAR_SALESMAN, RESET_MASSAL, RESET_INDIVIDU, EDIT_INDIVIDU
            $table->json('payload'); // Stores the exact data changes to be executed
            $table->text('reason')->nullable();
            $table->text('reject_reason')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'AUTO_APPROVED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jks_approvals');
    }
};
