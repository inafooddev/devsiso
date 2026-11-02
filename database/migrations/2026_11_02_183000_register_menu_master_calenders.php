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
        $existing = DB::table('menus')->where('route', 'master-calenders.index')->first();
        if (!$existing) {
            // Parent menu 'Master' (id: 15) di bawah 'Master Data' (id: 14)
            $parentId = DB::table('menus')->where('id', 15)->value('id') 
                     ?? DB::table('menus')->where('name', 'Master')->whereNotNull('parent_id')->value('id');

            $menuId = DB::table('menus')->insertGetId([
                'name' => 'Kalender',
                'route' => 'master-calenders.index',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" /></svg>',
                'parent_id' => $parentId,
                'order_number' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Salin permission dari master-areas.index (menu_id: 18) atau salesmans.index (menu_id: 31)
            $sourceMenu = DB::table('menus')->where('route', 'master-areas.index')->first()
                       ?? DB::table('menus')->where('route', 'salesmans.index')->first();
            $sourceMenuId = $sourceMenu ? $sourceMenu->id : 18;

            $rolesPermissions = DB::table('menu_role')->where('menu_id', $sourceMenuId)->get();
            $processedRoleIds = [];

            foreach ($rolesPermissions as $perm) {
                DB::table('menu_role')->insert([
                    'menu_id' => $menuId,
                    'role_id' => $perm->role_id,
                    'can_edit' => $perm->can_edit ?? true,
                    'can_import' => $perm->can_import ?? true,
                    'can_export' => $perm->can_export ?? true,
                    'can_add' => $perm->can_add ?? true,
                    'can_delete' => $perm->can_delete ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $processedRoleIds[] = $perm->role_id;
            }

            // Pastikan role admin & edp memiliki akses penuh
            $adminRoles = DB::table('roles')->whereIn('name', ['admin', 'edp', 'it', 'dev'])->get();
            foreach ($adminRoles as $ar) {
                if (!in_array($ar->id, $processedRoleIds)) {
                    DB::table('menu_role')->insert([
                        'menu_id' => $menuId,
                        'role_id' => $ar->id,
                        'can_edit' => true,
                        'can_import' => true,
                        'can_export' => true,
                        'can_add' => true,
                        'can_delete' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Salin access groups dari source menu
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
        $menu = DB::table('menus')->where('route', 'master-calenders.index')->first();
        if ($menu) {
            DB::table('menu_role')->where('menu_id', $menu->id)->delete();
            DB::table('access_group_menu')->where('menu_id', $menu->id)->delete();
            DB::table('menus')->where('id', $menu->id)->delete();
        }
    }
};
