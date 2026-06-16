<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $spv = 'SPVINAJABO1';
    
    // 1. Raw Query Without SPV Filter (Like Admin)
    $sqlAdmin = "
            WITH visit_data AS (
                SELECT
                    z.custno,
                    LOWER(z.\"level\") AS level
                FROM zv_summary_visit_team_elite z
                WHERE z.status_visit = 'Y'
                  AND z.tanggal BETWEEN '2026-06-01' and '2026-06-30'
            )
            SELECT t.team_elite_code, COUNT(*) as count
            FROM list_toko_pareto_team_elite l
            LEFT JOIN master_distributors md ON l.distributor_code = md.distributor_code
            LEFT JOIN team_elite_code_mappings t ON TRIM(t.siso_code) = TRIM(md.supervisor_code)
            LEFT JOIN fsalesman f ON TRIM(t.team_elite_code) = TRIM(f.\"SLSNO\")
            LEFT JOIN visit_data v ON l.customer_code_prc = v.custno
            WHERE l.pilar IN ('1. RWO','2. PNR','3. NGVO')
            GROUP BY t.team_elite_code
    ";
    
    $adminResults = DB::select($sqlAdmin);
    
    // 2. Raw Query WITH SPV Filter (Like Supervisor)
    $sqlSpv = "
            WITH visit_data AS (
                SELECT
                    z.custno,
                    LOWER(z.\"level\") AS level
                FROM zv_summary_visit_team_elite z
                WHERE z.status_visit = 'Y'
                  AND z.tanggal BETWEEN '2026-06-01' and '2026-06-30'
            )
            SELECT t.team_elite_code, COUNT(*) as count
            FROM list_toko_pareto_team_elite l
            LEFT JOIN master_distributors md ON l.distributor_code = md.distributor_code
            LEFT JOIN team_elite_code_mappings t ON TRIM(t.siso_code) = TRIM(md.supervisor_code)
            LEFT JOIN fsalesman f ON TRIM(t.team_elite_code) = TRIM(f.\"SLSNO\")
            LEFT JOIN visit_data v ON l.customer_code_prc = v.custno
            WHERE l.pilar IN ('1. RWO','2. PNR','3. NGVO')
              AND TRIM(t.team_elite_code) LIKE ?
            GROUP BY t.team_elite_code
    ";
    
    $spvResults = DB::select($sqlSpv, ["%SPVINAJABO1%"]);
    
    // 3. User object
    $user = DB::table('users')->where('userid', 'test')->first();

    $output = [
        'admin_summary' => $adminResults,
        'spv_summary' => $spvResults,
        'user_test' => $user
    ];
    
    file_put_contents(__DIR__.'/debug_query.json', json_encode($output, JSON_PRETTY_PRINT));
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
