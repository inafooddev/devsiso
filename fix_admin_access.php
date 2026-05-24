<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\AccessGroup;
use App\Models\User;

file_put_contents('debug_output.txt', '');

function out($msg) {
    file_put_contents('debug_output.txt', $msg . PHP_EOL, FILE_APPEND);
}

out("=== USERS ===");
$users = DB::select('SELECT id, email, access_group_id FROM users');
foreach ($users as $u) {
    out("  id={$u->id} | email={$u->email} | access_group_id={$u->access_group_id}");
}

out(PHP_EOL . "=== ACCESS GROUPS ===");
$groups = DB::select('SELECT * FROM access_groups');
foreach ($groups as $g) {
    out("  id={$g->id} | name={$g->name}");
}

out(PHP_EOL . "=== ACCESS GROUP MENU ===");
$cnt = count(DB::select('SELECT * FROM access_group_menu'));
out("  Total rows: {$cnt}");

out(PHP_EOL . "=== MENUS ===");
$menus = DB::select('SELECT id, name, parent_id FROM menus');
foreach ($menus as $m) {
    out("  id={$m->id} | {$m->name} | parent_id={$m->parent_id}");
}

// Now fix: create full-view group and assign to all admin users
out(PHP_EOL . "=== FIXING ===");
$allMenuIds = DB::table('menus')->pluck('id')->toArray();
out("  Total menus to add: " . count($allMenuIds));

// Create or update full-view access group
$groupId = DB::table('access_groups')->where('name', 'admin_full_view')->value('id');
if (!$groupId) {
    $groupId = DB::table('access_groups')->insertGetId([
        'name' => 'admin_full_view',
        'description' => 'Full view access group - all menus visible',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    out("  Created access group id={$groupId}");
} else {
    out("  Found existing access group id={$groupId}");
}

// Sync all menus to this group
DB::table('access_group_menu')->where('access_group_id', $groupId)->delete();
$inserts = [];
foreach ($allMenuIds as $menuId) {
    $inserts[] = [
        'access_group_id' => $groupId,
        'menu_id' => $menuId,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}
DB::table('access_group_menu')->insert($inserts);
out("  Inserted " . count($inserts) . " menu rows to group");

// Find all admin users and assign group
$adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
out("  Admin role id: {$adminRoleId}");
if ($adminRoleId) {
    $adminUserIds = DB::table('model_has_roles')
        ->where('role_id', $adminRoleId)
        ->where('model_type', 'App\\Models\\User')
        ->pluck('model_id')
        ->toArray();
    out("  Admin user ids: " . implode(', ', $adminUserIds));
    if (count($adminUserIds) > 0) {
        DB::table('users')
            ->whereIn('id', $adminUserIds)
            ->update(['access_group_id' => $groupId]);
        out("  Updated " . count($adminUserIds) . " admin users with access_group_id={$groupId}");
    }
}

out(PHP_EOL . "=== DONE ===");
out("Check debug_output.txt for results");
