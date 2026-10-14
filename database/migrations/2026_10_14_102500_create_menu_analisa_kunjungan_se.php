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
        $existing = DB::table('menus')->where('route', 'report.analisa-kunjungan-se.index')->first();
        if (!$existing) {
            $menuId = DB::table('menus')->insertGetId([
                'name' => 'Analisa Kunjungan SE',
                'route' => 'report.analisa-kunjungan-se.index',
                'icon' => '',
                'parent_id' => 76,
                'order_number' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Copy permissions from menu 86 (Analisa Kunjungan TE)
            $tePermissions = DB::table('menu_role')->where('menu_id', 86)->get();
            foreach ($tePermissions as $perm) {
                DB::table('menu_role')->insert([
                    'menu_id' => $menuId,
                    'role_id' => $perm->role_id,
                    'can_edit' => $perm->can_edit,
                    'can_import' => $perm->can_import,
                    'can_export' => $perm->can_export,
                    'can_add' => $perm->can_add,
                    'can_delete' => $perm->can_delete,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menu = DB::table('menus')->where('route', 'report.analisa-kunjungan-se.index')->first();
        if ($menu) {
            DB::table('menu_role')->where('menu_id', $menu->id)->delete();
            DB::table('menus')->where('id', $menu->id)->delete();
        }
    }
};
