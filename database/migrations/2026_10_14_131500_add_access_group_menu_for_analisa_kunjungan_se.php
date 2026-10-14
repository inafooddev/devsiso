<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menuSe = DB::table('menus')->where('route', 'report.analisa-kunjungan-se.index')->first();
        $menuTe = DB::table('menus')->where('route', 'report.analisa-kunjungan.index')->first();

        if ($menuSe && $menuTe) {
            $teGroups = DB::table('access_group_menu')->where('menu_id', $menuTe->id)->get();
            foreach ($teGroups as $tg) {
                $exists = DB::table('access_group_menu')
                    ->where('access_group_id', $tg->access_group_id)
                    ->where('menu_id', $menuSe->id)
                    ->exists();

                if (!$exists) {
                    DB::table('access_group_menu')->insert([
                        'access_group_id' => $tg->access_group_id,
                        'menu_id' => $menuSe->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuSe = DB::table('menus')->where('route', 'report.analisa-kunjungan-se.index')->first();
        if ($menuSe) {
            DB::table('access_group_menu')->where('menu_id', $menuSe->id)->delete();
        }
    }
};
