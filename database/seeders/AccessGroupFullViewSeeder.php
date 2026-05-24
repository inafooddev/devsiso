<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessGroup;
use App\Models\Menu;
use App\Models\User;

class AccessGroupFullViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find admin user (by role admin or email admin@example.com)
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (! $adminUser) {
            // If admin user not found, try by role
            $adminUser = User::whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->first();
        }
        if (! $adminUser) {
            $this->command->info('Admin user not found, skipping AccessGroup full view seeder.');
            return;
        }

        // Create or fetch the full‑view Access Group
        $group = AccessGroup::firstOrCreate(
            ['name' => 'admin_full_view'],
            ['description' => 'Full view access group for admin (all menus visible)']
        );

        // Assign every menu to this group (visibility only)
        $menus = Menu::all();
        $group->menus()->sync($menus->pluck('id')->toArray());

        // Attach the group to the admin user
        $adminUser->access_group_id = $group->id;
        $adminUser->save();

        $this->command->info('AccessGroup "admin_full_view" seeded and attached to admin user.');
    }
}
