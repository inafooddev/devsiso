<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = DB::select("
    SELECT COUNT(*) as count 
    FROM list_toko_pareto_team_elite l
    LEFT JOIN master_distributors md ON l.distributor_code = md.distributor_code
    LEFT JOIN team_elite_code_mappings t ON t.siso_code = md.supervisor_code
    WHERE t.team_elite_code IS NOT NULL
");

$data2 = DB::select("
    SELECT COUNT(*) as count
    FROM team_elite_code_mappings
");

file_put_contents('test_db.txt', json_encode(['join_count' => $data, 'map_count' => $data2]));
echo "Done";
