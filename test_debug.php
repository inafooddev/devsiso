<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $spv_code = 'SPVINAJABO1'; // Example team elite code
    
    // Check if team_elite_code exists in mapping
    $mapping = DB::table('team_elite_code_mappings')
        ->where('team_elite_code', 'LIKE', '%' . trim($spv_code) . '%')
        ->first();
        
    // Check if md.supervisor_code matches siso_code
    if ($mapping) {
        $dist = DB::table('master_distributors')
            ->where('supervisor_code', $mapping->siso_code)
            ->first();
            
        $toko = DB::table('list_toko_pareto_team_elite')
            ->where('distributor_code', $dist ? $dist->distributor_code : 'N/A')
            ->count();
    } else {
        $dist = null;
        $toko = 0;
    }

    $out = [
        'mapping_found' => $mapping,
        'distributor_found' => $dist ? $dist->distributor_code : null,
        'toko_count' => $toko
    ];
    
    file_put_contents('c:/Users/User/Desktop/dev/devsiso/debug_data.txt', print_r($out, true));
    echo "Done";
} catch (\Exception $e) {
    file_put_contents('c:/Users/User/Desktop/dev/devsiso/debug_data.txt', $e->getMessage());
    echo "Error";
}
