<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\AccessGroup;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cari Parent Menu "Semarak Rejeki"
        $parentMenu = Menu::where('name', 'Semarak Rejeki')->whereNull('parent_id')->first();
        if (!$parentMenu) {
            $starIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>';
            $parentMenu = Menu::create([
                'name'         => 'Semarak Rejeki',
                'parent_id'    => null,
                'route'        => null,
                'icon'         => $starIcon,
                'order_number' => 18,
                'is_active'    => true,
            ]);
        }

        // 2. Buat Sub Menu (Child Menu): "Reward ASM & RSM"
        $childMenu = Menu::updateOrCreate(
            ['name' => 'Reward ASM & RSM', 'parent_id' => $parentMenu->id],
            [
                'route'        => 'semarak-rejeki.monitoring-asm-rsm',
                'icon'         => null,
                'order_number' => 2,
                'is_active'    => true,
            ]
        );

        // 3. Daftarkan hak akses ke Access Groups
        $allGroupIds = AccessGroup::pluck('id')->toArray();
        foreach ($allGroupIds as $groupId) {
            DB::table('access_group_menu')->updateOrInsert(
                ['access_group_id' => $groupId, 'menu_id' => $childMenu->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Daftarkan hak akses ke Role Permissions (menu_role)
        $roles = Role::whereIn('name', [
            'admin', 'admspm', 'spm', 'spvspm', 'spvlapangan', 'rsm', 'asm', 'it', 'itina', 'edp'
        ])->get();

        foreach ($roles as $role) {
            $isAdminOrSpm = in_array($role->name, ['admin', 'admspm', 'spvspm']);
            DB::table('menu_role')->updateOrInsert(
                ['role_id' => $role->id, 'menu_id' => $childMenu->id],
                [
                    'can_edit'   => $isAdminOrSpm,
                    'can_import' => $isAdminOrSpm,
                    'can_export' => true,
                    'can_add'    => $isAdminOrSpm,
                    'can_delete' => $isAdminOrSpm,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $parent = Menu::where('name', 'Semarak Rejeki')->whereNull('parent_id')->first();
        if ($parent) {
            $child = Menu::where('name', 'Reward ASM & RSM')->where('parent_id', $parent->id)->first();
            if ($child) {
                DB::table('access_group_menu')->where('menu_id', $child->id)->delete();
                DB::table('menu_role')->where('menu_id', $child->id)->delete();
                $child->delete();
            }
        }
    }
};
