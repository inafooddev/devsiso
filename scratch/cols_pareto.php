<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'list_toko_pareto_team_elite'");
echo json_encode(array_column($cols, 'column_name'));
