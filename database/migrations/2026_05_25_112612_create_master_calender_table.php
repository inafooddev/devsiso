<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_calender', function (Blueprint $table) {
            $table->date('date')->primary();
            $table->string('day', 15);
            $table->integer('day_number');
            $table->integer('week_year');
            $table->integer('week_month');
            $table->integer('month');
            $table->integer('quarter');
            $table->integer('semester');
            $table->integer('year');
            $table->timestamps();
        });

        DB::table('master_calender')->insert([
            ['date' => '2025-12-28', 'day' => 'Minggu', 'day_number' => 7, 'week_year' => 1, 'week_month' => 1, 'month' => 12, 'quarter' => 4, 'semester' => 2, 'year' => 2025, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-12-29', 'day' => 'Senin', 'day_number' => 1, 'week_year' => 1, 'week_month' => 1, 'month' => 12, 'quarter' => 4, 'semester' => 2, 'year' => 2025, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-12-30', 'day' => 'Selasa', 'day_number' => 2, 'week_year' => 1, 'week_month' => 1, 'month' => 12, 'quarter' => 4, 'semester' => 2, 'year' => 2025, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-12-31', 'day' => 'Rabu', 'day_number' => 3, 'week_year' => 1, 'week_month' => 1, 'month' => 12, 'quarter' => 4, 'semester' => 2, 'year' => 2025, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2026-01-01', 'day' => 'Kamis', 'day_number' => 4, 'week_year' => 1, 'week_month' => 1, 'month' => 1, 'quarter' => 1, 'semester' => 1, 'year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2026-01-02', 'day' => 'Jumat', 'day_number' => 5, 'week_year' => 1, 'week_month' => 1, 'month' => 1, 'quarter' => 1, 'semester' => 1, 'year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2026-01-03', 'day' => 'Sabtu', 'day_number' => 6, 'week_year' => 1, 'week_month' => 1, 'month' => 1, 'quarter' => 1, 'semester' => 1, 'year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2026-01-04', 'day' => 'Minggu', 'day_number' => 7, 'week_year' => 2, 'week_month' => 2, 'month' => 1, 'quarter' => 1, 'semester' => 1, 'year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2026-01-05', 'day' => 'Senin', 'day_number' => 1, 'week_year' => 2, 'week_month' => 2, 'month' => 1, 'quarter' => 1, 'semester' => 1, 'year' => 2026, 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2026-01-06', 'day' => 'Selasa', 'day_number' => 2, 'week_year' => 2, 'week_month' => 2, 'month' => 1, 'quarter' => 1, 'semester' => 1, 'year' => 2026, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_calender');
    }
};
