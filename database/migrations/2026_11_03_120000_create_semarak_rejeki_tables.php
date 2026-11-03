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
        // 1. Master Tier & Hadiah Promo
        Schema::create('semarak_rejeki_reward_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('kategori_wilayah', 20)->index(); // 'jawa' | 'pulau'
            $table->string('kode_paket', 10);                // 'A', 'B', 'C', ... 'V'
            $table->integer('target_karton');               // Minimal Qty Karton (cth: 400, 700)
            $table->decimal('harga_per_karton', 12, 2)->default(220500);
            $table->decimal('nilai_transaksi', 15, 2);      // target_karton * harga_per_karton
            $table->text('nama_hadiah');                    // Deskripsi unit hadiah
            $table->decimal('nilai_hadiah', 15, 2);         // Cost hadiah
            $table->decimal('cost_ratio', 5, 2)->default(1.0); // 1.0%
            $table->integer('urutan')->default(0);          // Urutan sorting
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kategori_wilayah', 'kode_paket']);
        });

        // 2. Master Target SPV (Bisa diatur ulang oleh admin)
        Schema::create('semarak_rejeki_spv_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->default(2026)->index();
            $table->string('supervisor_code', 50)->index(); // Kode akun SPV / Team Elite (cth: SPVINAJABO1)
            $table->string('siso_code', 50)->index();       // Kode SPV SISO di master_distributors (cth: JI011)
            $table->string('kategori_wilayah', 20)->index();// 'jawa' | 'luar_jawa'
            
            // Relasi ke Paket Awal yang disepakati
            $table->foreignId('tier_id')->nullable()->constrained('semarak_rejeki_reward_tiers')->nullOnDelete();
            $table->string('kode_paket_awal', 10)->nullable();
            
            // Target Volume
            $table->decimal('target_karton_3bln', 12, 2);   // Target total 3 bulan
            $table->decimal('target_m1_okt', 12, 2);        // Target Bulan 1 (Oktober)
            $table->decimal('target_m2_nov', 12, 2);        // Target Bulan 2 (November)
            $table->decimal('target_m3_des', 12, 2);        // Target Bulan 3 (Desember)
            
            $table->boolean('is_active')->default(true);
            $table->text('catatan')->nullable();            // Catatan revisi/mutasi
            $table->timestamps();

            $table->unique(['tahun', 'supervisor_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semarak_rejeki_spv_targets');
        Schema::dropIfExists('semarak_rejeki_reward_tiers');
    }
};
