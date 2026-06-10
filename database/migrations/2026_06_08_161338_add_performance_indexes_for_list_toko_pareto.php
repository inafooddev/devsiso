<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FIX #2 — Tambah index performa untuk mengatasi bottleneck query pada
     * halaman List Toko Pareto (Team Elite).
     *
     * Kolom yang diindex dipilih berdasarkan analisis query:
     * - JOIN condition antara list_toko_pareto_team_elite ↔ jks_team_elite
     * - Filter WHERE (distributor_code, pilar, latitude)
     * - ORDER BY yang sering dipakai (pilar)
     */
    public function up(): void
    {
        // --- Tabel: list_toko_pareto_team_elite ---
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            // distributor_code sering dipakai di JOIN & WHERE hierarchy
            // (UNIQUE index existing: customer_code_prc, distributor_code — tapi distributor_code bukan kolom pertama,
            //  sehingga query WHERE distributor_code saja tidak bisa memanfaatkannya secara optimal)
            if (!$this->indexExists('list_toko_pareto_team_elite', 'idx_ltpte_distributor_code')) {
                $table->index('distributor_code', 'idx_ltpte_distributor_code');
            }

            // pilar dipakai di WHERE (filterKpi) dan ORDER BY
            if (!$this->indexExists('list_toko_pareto_team_elite', 'idx_ltpte_pilar')) {
                $table->index('pilar', 'idx_ltpte_pilar');
            }

            // latitude dipakai di WHERE (filter no_geotag: IS NULL OR = 0)
            if (!$this->indexExists('list_toko_pareto_team_elite', 'idx_ltpte_latitude')) {
                $table->index('latitude', 'idx_ltpte_latitude');
            }
        });

        // --- Tabel: jks_team_elite ---
        Schema::table('jks_team_elite', function (Blueprint $table) {
            // (distributor_code, custno) dipakai sebagai LEFT JOIN condition dari getBaseQuery()
            // Ini adalah index terpenting — langsung menghilangkan full-scan per-baris
            if (!$this->indexExists('jks_team_elite', 'idx_jte_distributor_custno')) {
                $table->index(['distributor_code', 'custno'], 'idx_jte_distributor_custno');
            }

            // tanggal dipakai untuk filter/sort di halaman JKS lain
            if (!$this->indexExists('jks_team_elite', 'idx_jte_tanggal')) {
                $table->index('tanggal', 'idx_jte_tanggal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('list_toko_pareto_team_elite', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_ltpte_distributor_code');
            $table->dropIndexIfExists('idx_ltpte_pilar');
            $table->dropIndexIfExists('idx_ltpte_latitude');
        });

        Schema::table('jks_team_elite', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_jte_distributor_custno');
            $table->dropIndexIfExists('idx_jte_tanggal');
        });
    }

    /**
     * Helper: cek apakah index sudah ada (mencegah error jika migration dijalankan ulang).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        return collect(\Illuminate\Support\Facades\DB::select(
            "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
            [$table, $indexName]
        ))->isNotEmpty();
    }
};
