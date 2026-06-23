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
        // Bersihkan data (0,0) yang tersimpan sebelumnya karena bug
        DB::table('hasil_audit_toko')
            ->where('latitude', '0')
            ->where('longitude', '0')
            ->update([
                'latitude' => null,
                'longitude' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_audit_toko', function (Blueprint $table) {
            //
        });
    }
};
