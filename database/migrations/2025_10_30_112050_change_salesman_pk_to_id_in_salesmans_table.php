<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Hapus foreign key yang tergantung ke salesman_code
        DB::statement('ALTER TABLE salesman_mappings DROP CONSTRAINT IF EXISTS salesman_mappings_salesman_code_prc_foreign;');

        // 2️⃣ Hapus primary key lama
        DB::statement('ALTER TABLE salesmans DROP CONSTRAINT IF EXISTS salesmans_pkey CASCADE;');

        // 3️⃣ Tambahkan kolom id auto increment (jika belum ada)
        Schema::table('salesmans', function (Blueprint $table) {
            if (!Schema::hasColumn('salesmans', 'id')) {
                $table->bigIncrements('id')->first();
            }
        });

        // 4️⃣ Buat index baru di salesman_code (biar tetap searchable)
        Schema::table('salesmans', function (Blueprint $table) {
            $table->index('salesman_code', 'salesmans_salesman_code_idx');
        });
    }

    public function down(): void
    {
        Schema::table('salesmans', function (Blueprint $table) {
            $table->dropIndex('salesmans_salesman_code_idx');
            $table->dropColumn('id');
            $table->primary('salesman_code');
        });
    }
};
