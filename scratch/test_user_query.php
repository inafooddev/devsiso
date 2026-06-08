<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $sql = '
    WITH visit_data AS (
        SELECT
            z.custno,
            LOWER(z."level") AS level
        FROM zv_summary_visit_team_elite z
        WHERE z.status_visit = \'Y\'
          AND z.tanggal BETWEEN \'2026-05-01\' and \'2026-05-31\'
    )

    SELECT
        md.region_code,
        md.region_name,
        md.area_code,
        md.area_name,
        t.team_elite_code AS supervisor_code,
        f."SLSNAME" AS supervisor_name,
        l.distributor_code,
        md.distributor_name,
        l.customer_code_prc AS customer_code,
        l.uniq_kd,
        l.customer_name,
        l.pilar,

        COUNT(CASE
            WHEN v.level = \'region\'
            THEN 1
        END) AS visit_region,

        COUNT(CASE
            WHEN v.level = \'area\'
            THEN 1
        END) AS visit_area,

        COUNT(CASE
            WHEN v.level = \'supervisor\'
            THEN 1
        END) AS visit_supervisor,

        CASE
            WHEN COUNT(CASE WHEN v.level = \'region\' THEN 1 END) > 0
              OR COUNT(CASE WHEN v.level = \'area\' THEN 1 END) > 0
              OR COUNT(CASE WHEN v.level = \'supervisor\' THEN 1 END) > 0
            THEN \'Y\'
            ELSE \'N\'
        END AS status_visit

    FROM list_toko_pareto_team_elite l

    LEFT JOIN master_distributors md
        ON l.distributor_code = md.distributor_code

    LEFT JOIN team_elite_code_mappings t
        ON t.siso_code = md.supervisor_code

    LEFT JOIN fsalesman f
        ON t.team_elite_code = f."SLSNO"

    LEFT JOIN visit_data v
        ON l.customer_code_prc = v.custno

    WHERE l.pilar IN (\'1. RWO\',\'2. PNR\',\'3. NGVO\')

    GROUP BY
        md.region_code,
        md.region_name,
        md.area_code,
        md.area_name,
        t.team_elite_code,
        f."SLSNAME",
        l.distributor_code,
        md.distributor_name,
        l.customer_code_prc,
        l.uniq_kd,
        l.customer_name,
        l.pilar
    LIMIT 5;
    ';

    $results = DB::select($sql);
    echo "SUCCESS: Found " . count($results) . " rows.\n";
    print_r($results);

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
