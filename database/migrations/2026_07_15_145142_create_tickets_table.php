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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('bug')->index(); // 'bug', 'request'
            $table->string('priority')->default('medium')->index(); // 'low', 'medium', 'high', 'critical'
            $table->string('status')->default('open')->index(); // 'open', 'in_progress', 'resolved', 'closed'
            $table->string('screenshot')->nullable();
            $table->text('developer_response')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
