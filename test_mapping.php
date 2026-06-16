<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
    require 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $mappings = DB::table('team_elite_code_mappings')->take(5)->get();
    echo json_encode($mappings);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
