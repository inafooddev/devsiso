<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('frute', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('region', 20);
            $table->string('kodecabang', 20);
            $table->string('cabang', 20);
            $table->string('slsno', 20);
            $table->string('norute', 50);
            $table->string('custno', 25);

            // Hari (H1 - H7)
            $table->char('h1', 1)->nullable();
            $table->char('h2', 1)->nullable();
            $table->char('h3', 1)->nullable();
            $table->char('h4', 1)->nullable();
            $table->char('h5', 1)->nullable();
            $table->char('h6', 1)->nullable();
            $table->char('h7', 1)->nullable();

            // Minggu (M1 - M4)
            $table->char('m1', 1)->nullable();
            $table->char('m2', 1)->nullable();
            $table->char('m3', 1)->nullable();
            $table->char('m4', 1)->nullable();

            $table->timestamps();

            /* =========================
               INDEX SECTION
               ========================= */

            // Index umum filter
            $table->index('region');
            $table->index('kodecabang');
            $table->index('slsno');
            $table->index('custno');
            $table->index('norute');

            // Composite index (paling sering dipakai di filter)
            $table->index(
                ['region', 'kodecabang', 'slsno'],
                'frute_region_cabang_slsno_idx'
            );

            // Untuk pencarian customer per rute
            $table->index(
                ['norute', 'custno'],
                'frute_norute_custno_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frute');
    }
};
