<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Menu;
use App\Models\AccessGroup;

class AdminFullAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure admin role exists
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );

        // Ensure admin user exists (you can adjust the email/username as needed)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'userid' => 'admin',
                'name' => 'Administrator',
                'password' => bcrypt('password'), // change after seeding
                'region_code' => null,
            ]
        );
        // Assign admin role to user
        $adminUser->assignRole($adminRole);

        // Grant all menu permissions (view, edit, import, export) to admin role
        $menus = Menu::all();
        $pivotData = [];
        foreach ($menus as $menu) {
            $pivotData[$menu->id] = [
                'can_view' => 1,
                'can_edit' => 1,
                'can_import' => 1,
                'can_export' => 1,
            ];
        }
        // Sync pivot table (role_menu)
        $adminRole->menus()->sync($pivotData);

        // Create a full‑access Access Group (optional, for view‑only grouping)
        $fullGroup = AccessGroup::firstOrCreate(
            ['name' => 'full_access'],
            ['description' => 'Group that can view all menus (no edit actions)']
        );
        // Assign every menu to the group (visibility only)
        $fullGroup->menus()->sync($menus->pluck('id')->toArray());

        // Attach admin user to the full‑access group (so sidebar shows everything)
        $adminUser->access_group_id = $fullGroup->id;
        $adminUser->save();

        $this->command->info('Admin user with full role & access group seeded successfully.');
    }
}
