<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geotag_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('system_filename');
            $table->string('status')->default('pending'); // pending, processing, completed, error
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('geotag_jobs');
    }
};