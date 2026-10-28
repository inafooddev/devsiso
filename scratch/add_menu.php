<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use App\Models\AccessGroup;

$existing = Menu::where('route', 'siso-analytics.index')->first();
if (!$existing) {
    $menu = Menu::create([
        'name' => 'SISO Analytics',
        'route' => 'siso-analytics.index',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" /></svg>',
        'parent_id' => null,
        'order' => 15,
        'is_active' => true,
    ]);
    echo "Created Menu SISO Analytics with ID: {$menu->id}\n";

    // Grant to admin, it, edp, dev
    $groups = AccessGroup::whereIn('name', ['admin', 'it', 'edp', 'dev'])->get();
    foreach ($groups as $g) {
        $menu->accessGroups()->syncWithoutDetaching([$g->id]);
        echo "Attached to AccessGroup: {$g->name}\n";
    }
} else {
    echo "Menu SISO Analytics already exists with ID: {$existing->id}\n";
}
