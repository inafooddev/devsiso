<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('userid', 'test')->first();
echo "hasRole('admin') ? " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
echo "getAccessLevel() ? " . $user->getAccessLevel() . "\n";
