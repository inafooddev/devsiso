<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $spv = 'SPVINAJABO1';
    
    $whereSql = " AND TRIM(t.team_elite_code) LIKE ?";
    $bindings = ['2026-06-01', '2026-06-30', '%' . $spv . '%'];

    $sql = "
            WITH visit_data AS (
                SELECT
                    z.custno,
                    LOWER(z.\"level\") AS level
                FROM zv_summary_visit_team_elite z
                WHERE z.status_visit = 'Y'
                  AND z.tanggal BETWEEN ? and ?
            )
            SELECT
                md.region_code,
                t.team_elite_code AS supervisor_code,
                f.\"SLSNAME\" AS supervisor_name,
                l.distributor_code
            FROM list_toko_pareto_team_elite l
            LEFT JOIN master_distributors md ON l.distributor_code = md.distributor_code
            LEFT JOIN team_elite_code_mappings t ON TRIM(t.siso_code) = TRIM(md.supervisor_code)
            LEFT JOIN fsalesman f ON TRIM(t.team_elite_code) = TRIM(f.\"SLSNO\")
            LEFT JOIN visit_data v ON l.customer_code_prc = v.custno
            WHERE l.pilar IN ('1. RWO','2. PNR','3. NGVO')
              $whereSql
            GROUP BY
                md.region_code,
                t.team_elite_code,
                f.\"SLSNAME\",
                l.distributor_code
            LIMIT 10
    ";
    
    $results = DB::select($sql, $bindings);
    
    file_put_contents(__DIR__.'/debug_final.json', json_encode($results, JSON_PRETTY_PRINT));
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
