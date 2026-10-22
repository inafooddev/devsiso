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
        $existing = DB::table('menus')->where('route', 'others.validasi-geotag')->first();
        if (!$existing) {
            // Parent menu 'Others' (id: 71)
            $parentId = DB::table('menus')->where('name', 'Others')->whereNull('parent_id')->value('id') ?? 71;

            $menuId = DB::table('menus')->insertGetId([
                'name' => 'Validasi Geotag Toko',
                'route' => 'others.validasi-geotag',
                'icon' => 'ri-map-pin-user-line',
                'parent_id' => $parentId,
                'order_number' => 11,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Copy permissions from menu 87 (Perbaikan Tikor) if exists
            $perbaikanTikorMenu = DB::table('menus')->where('route', 'others.perbaikantikor')->first();
            $sourceMenuId = $perbaikanTikorMenu ? $perbaikanTikorMenu->id : 87;

            $rolesPermissions = DB::table('menu_role')->where('menu_id', $sourceMenuId)->get();
            $processedRoleIds = [];

            foreach ($rolesPermissions as $perm) {
                DB::table('menu_role')->insert([
                    'menu_id' => $menuId,
                    'role_id' => $perm->role_id,
                    'can_edit' => $perm->can_edit,
                    'can_import' => $perm->can_import,
                    'can_export' => $perm->can_export,
                    'can_add' => $perm->can_add ?? false,
                    'can_delete' => $perm->can_delete ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $processedRoleIds[] = $perm->role_id;
            }

            // Pastikan role 'admin' & 'spm' memiliki akses penuh
            $adminRoles = DB::table('roles')->whereIn('name', ['admin', 'spm'])->get();
            foreach ($adminRoles as $ar) {
                if (!in_array($ar->id, $processedRoleIds)) {
                    DB::table('menu_role')->insert([
                        'menu_id' => $menuId,
                        'role_id' => $ar->id,
                        'can_edit' => true,
                        'can_import' => false,
                        'can_export' => true,
                        'can_add' => false,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Copy access groups dari source menu
            $groups = DB::table('access_group_menu')->where('menu_id', $sourceMenuId)->get();
            foreach ($groups as $grp) {
                $exists = DB::table('access_group_menu')
                    ->where('access_group_id', $grp->access_group_id)
                    ->where('menu_id', $menuId)
                    ->exists();

                if (!$exists) {
                    DB::table('access_group_menu')->insert([
                        'access_group_id' => $grp->access_group_id,
                        'menu_id' => $menuId,
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
        $menu = DB::table('menus')->where('route', 'others.validasi-geotag')->first();
        if ($menu) {
            DB::table('menu_role')->where('menu_id', $menu->id)->delete();
            DB::table('access_group_menu')->where('menu_id', $menu->id)->delete();
            DB::table('menus')->where('id', $menu->id)->delete();
        }
    }
};
