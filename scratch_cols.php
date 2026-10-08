<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('list_toko_pareto_team_elite');
print_r($columns);
