<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $menus = \App\Models\Menu::all();
    echo "Total menus: " . $menus->count() . "\n";
    foreach ($menus as $m) {
        if (stripos($m->name, 'Eskalink') !== false) {
            echo "Found Eskalink menu: {$m->name} -> route: {$m->route}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
