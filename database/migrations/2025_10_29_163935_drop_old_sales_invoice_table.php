<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::dropIfExists('sales_invoice_distributor_old');
    }
    public function down() {
        // Tidak ada rollback untuk drop table
    }
};
