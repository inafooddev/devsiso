<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $res = DB::table('list_toko_pareto_team_elite')->selectRaw('SUM(target) as total')->first();
    echo "SUCCESS: " . json_encode($res);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
