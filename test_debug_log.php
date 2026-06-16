<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $spv_code = \App\Models\User::orderBy('updated_at', 'desc')->first()->supervisor_code;
    
    // Check if team_elite_code exists in mapping
    $mapping = DB::table('team_elite_code_mappings')
        ->where('team_elite_code', 'LIKE', '%' . trim($spv_code) . '%')
        ->first();
        
    if ($mapping) {
        $distCount = DB::table('master_distributors')
            ->where('supervisor_code', $mapping->siso_code)
            ->count();
            
        $tokoCount = DB::table('list_toko_pareto_team_elite')
            ->join('master_distributors', 'list_toko_pareto_team_elite.distributor_code', '=', 'master_distributors.distributor_code')
            ->where('master_distributors.supervisor_code', $mapping->siso_code)
            ->count();
    } else {
        $distCount = 0;
        $tokoCount = 0;
    }

    $out = [
        'user_supervisor_code' => $spv_code,
        'mapping_found' => $mapping ? 'YES' : 'NO',
        'mapping_siso' => $mapping ? $mapping->siso_code : null,
        'distributor_count' => $distCount,
        'toko_count' => $tokoCount
    ];
    
    \Illuminate\Support\Facades\Log::info('DEBUG DATA:', $out);
    echo "Done";
} catch (\Exception $e) {
    \Illuminate\Support\Facades\Log::error('DEBUG ERROR: ' . $e->getMessage());
    echo "Error";
}
