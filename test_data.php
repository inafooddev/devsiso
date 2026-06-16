<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mappings = DB::table('team_elite_code_mappings')->take(5)->get();
$distributors = DB::table('master_distributors')->select('supervisor_code')->whereNotNull('supervisor_code')->take(5)->get();

$out = [
  'mappings' => $mappings,
  'distributors' => $distributors
];

file_put_contents('c:/Users/User/Desktop/dev/devsiso/test_output.json', json_encode($out));
echo "Done";
