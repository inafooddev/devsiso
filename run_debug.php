<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $spv = 'SPVINAJABO1';
    
    // Check what siso_code is for this SPV
    $mappings = DB::table('team_elite_code_mappings')
        ->whereRaw("TRIM(team_elite_code) = ?", [$spv])
        ->get();
        
    $sisoCodes = $mappings->pluck('siso_code')->toArray();
    
    // Check if these siso_codes exist in master_distributors
    $distributors = DB::table('master_distributors')
        ->whereIn('supervisor_code', $sisoCodes)
        ->get();
        
    $distCodes = $distributors->pluck('distributor_code')->toArray();
    
    // Check if these distributors exist in list_toko_pareto_team_elite
    $toko = DB::table('list_toko_pareto_team_elite')
        ->whereIn('distributor_code', $distCodes)
        ->count();
        
    $tokoWithPilar = DB::table('list_toko_pareto_team_elite')
        ->whereIn('distributor_code', $distCodes)
        ->whereIn('pilar', ['1. RWO','2. PNR','3. NGVO'])
        ->count();

    $output = [
        'spv' => $spv,
        'mappings_found' => $mappings->count(),
        'siso_codes' => $sisoCodes,
        'distributors_found' => $distributors->count(),
        'distributor_codes' => $distCodes,
        'toko_count' => $toko,
        'toko_with_pilar_count' => $tokoWithPilar
    ];
    
    file_put_contents(__DIR__.'/debug_output.json', json_encode($output, JSON_PRETTY_PRINT));
    echo "SUCCESS\n";
} catch (\Exception $e) {
    file_put_contents(__DIR__.'/debug_error.txt', $e->getMessage());
    echo "ERROR\n";
}
