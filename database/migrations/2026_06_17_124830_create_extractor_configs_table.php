<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extractor_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->jsonb('keywords')->comment('Array kata kunci file');
            $table->integer('header_row')->default(1);
            $table->jsonb('columns')->comment('Struktur mapping kolom Excel');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extractor_configs');
    }
};
