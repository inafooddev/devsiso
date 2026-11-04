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
        // 1. Master Target Region & Reward RSM
        Schema::create('semarak_rejeki_rsm_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->default(2026)->index();
            $table->string('region_code', 50)->index();      // JAWA_1, JAWA_2, PULAU, SUMATERA_1, SUMATERA_2
            $table->string('region_name', 100);              // JAWA 1, JAWA 2, PULAU, SUMATERA 1, SUMATERA 2
            $table->string('rsm_db_pattern', 100);           // Pattern di selling_in.rsm (cth: INA JAWA 1)
            $table->string('nomor_proposal', 100)->nullable();
            $table->integer('max_ly')->default(0);           // MAX baseline penjualan tahun lalu
            
            // Layer 1
            $table->integer('target_l1_monthly');            // Target karton per bulan
            $table->decimal('growth_l1_pct', 5, 2)->default(20.00);
            $table->decimal('reward_rsm_l1', 15, 2)->default(0); // Reward RSM jika Region tembus Layer 1
            
            // Layer 2
            $table->integer('target_l2_monthly');            // Target karton per bulan
            $table->decimal('growth_l2_pct', 5, 2)->default(30.00);
            $table->decimal('reward_rsm_l2', 15, 2)->default(0); // Reward RSM jika Region tembus Layer 2
            
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tahun', 'region_code']);
        });

        // 2. Master Target Divisi & Reward ASM
        Schema::create('semarak_rejeki_asm_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsm_target_id')->constrained('semarak_rejeki_rsm_targets')->cascadeOnDelete();
            $table->integer('tahun')->default(2026)->index();
            $table->string('area_code', 50)->index();        // JABODETABEK, JATIM_1, dll.
            $table->string('area_name', 100);                // JABODETABEK, JATIM 1, dll.
            $table->string('asm_db_name', 100)->index();     // Sesuai nilai di selling_in.asm (cth: ASM JABODETABEK)
            $table->integer('max_ly')->default(0);           // MAX baseline penjualan
            
            // Layer 1
            $table->integer('target_l1_monthly');            // Target karton per bulan
            $table->decimal('growth_l1_pct', 5, 2)->default(20.00);
            $table->decimal('reward_asm_l1', 15, 2)->default(0); // Reward ASM jika tembus Layer 1
            
            // Layer 2
            $table->integer('target_l2_monthly');            // Target karton per bulan
            $table->decimal('growth_l2_pct', 5, 2)->default(30.00);
            $table->decimal('reward_asm_l2', 15, 2)->default(0); // Reward ASM jika tembus Layer 2
            
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tahun', 'area_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semarak_rejeki_asm_targets');
        Schema::dropIfExists('semarak_rejeki_rsm_targets');
    }
};
