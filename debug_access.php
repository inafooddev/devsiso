<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== USERS ===" . PHP_EOL;
$users = DB::select('select id, email, access_group_id from users');
foreach ($users as $u) {
    echo "  id={$u->id} | {$u->email} | access_group_id={$u->access_group_id}" . PHP_EOL;
}

echo PHP_EOL . "=== ACCESS GROUPS ===" . PHP_EOL;
$groups = DB::select('select * from access_groups');
foreach ($groups as $g) {
    echo "  id={$g->id} | name={$g->name}" . PHP_EOL;
}

echo PHP_EOL . "=== ACCESS GROUP MENU PIVOT ===" . PHP_EOL;
echo "  Total rows: " . count(DB::select('select * from access_group_menu')) . PHP_EOL;

echo PHP_EOL . "=== MENUS ===" . PHP_EOL;
$menus = DB::select('select id, name, parent_id from menus');
foreach ($menus as $m) {
    echo "  id={$m->id} | {$m->name} | parent_id={$m->parent_id}" . PHP_EOL;
}
