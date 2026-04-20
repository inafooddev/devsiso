<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::dropIfExists('detail_sell_out_old');
    }
    public function down() {
        // Tidak ada rollback untuk drop table
    }
};
