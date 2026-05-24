<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAdminAccessGroup extends Command
{
    protected $signature = 'fix:admin-access-group';
    protected $description = 'Create full-view access group and assign to all admin users';

    public function handle()
    {
        // Show all users
        $this->info('=== USERS ===');
        $users = DB::select('SELECT id, email, access_group_id FROM users');
        foreach ($users as $u) {
            $this->line("  id={$u->id} | email={$u->email} | access_group_id={$u->access_group_id}");
        }

        // Show groups
        $this->info(PHP_EOL . '=== ACCESS GROUPS ===');
        $groups = DB::select('SELECT id, name FROM access_groups');
        foreach ($groups as $g) {
            $this->line("  id={$g->id} | name={$g->name}");
        }

        $pivotCount = count(DB::select('SELECT * FROM access_group_menu'));
        $this->line("access_group_menu rows: {$pivotCount}");

        // Create/update full-view group
        $this->info(PHP_EOL . '=== FIXING ===');
        $allMenuIds = DB::table('menus')->pluck('id')->toArray();
        $this->line("Total menus: " . count($allMenuIds));

        $groupId = DB::table('access_groups')->where('name', 'admin_full_view')->value('id');
        if (!$groupId) {
            $groupId = DB::table('access_groups')->insertGetId([
                'name' => 'admin_full_view',
                'description' => 'Full view access - all menus visible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->line("Created access group id={$groupId}");
        } else {
            $this->line("Found existing access group id={$groupId}");
        }

        // Sync all menus
        DB::table('access_group_menu')->where('access_group_id', $groupId)->delete();
        $inserts = array_map(fn($id) => [
            'access_group_id' => $groupId,
            'menu_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ], $allMenuIds);
        if (count($inserts) > 0) {
            DB::table('access_group_menu')->insert($inserts);
        }
        $this->line("Synced " . count($inserts) . " menus to group");

        // Assign to all admin users
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $this->line("Admin role id: {$adminRoleId}");

        if ($adminRoleId) {
            $adminUserIds = DB::table('model_has_roles')
                ->where('role_id', $adminRoleId)
                ->where('model_type', 'App\Models\User')
                ->pluck('model_id')
                ->toArray();
            $this->line("Admin user ids: " . implode(', ', $adminUserIds));

            if (count($adminUserIds) > 0) {
                DB::table('users')
                    ->whereIn('id', $adminUserIds)
                    ->update(['access_group_id' => $groupId]);
                $this->info("Updated " . count($adminUserIds) . " admin users with access_group_id={$groupId}");
            }
        }

        $this->info('DONE!');
    }
}
