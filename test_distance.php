<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $results = \Illuminate\Support\Facades\DB::select("
        SELECT 
            rvah.\"V_LA\", rvah.\"V_LG\", rvah.\"M_LA\", rvah.\"M_LG\",
            (ACOS(LEAST(1.0, GREATEST(-1.0, 
                SIN(RADIANS(rvah.\"V_LA\")) * 
                SIN(RADIANS(rvah.\"M_LA\")) + 
                COS(RADIANS(rvah.\"V_LA\")) * 
                COS(RADIANS(rvah.\"M_LA\")) * 
                COS(RADIANS(rvah.\"V_LG\" - rvah.\"M_LG\"))
            ))) * 6371000) as distance
        FROM rpt_visit_an_h as rvah 
        WHERE rvah.\"V_LA\" IS NOT NULL AND rvah.\"V_LA\" != 0
          AND rvah.\"M_LA\" IS NOT NULL AND rvah.\"M_LA\" != 0
        LIMIT 5
    ");
    print_r($results);
} catch (\Exception $e) {
    echo $e->getMessage();
}
