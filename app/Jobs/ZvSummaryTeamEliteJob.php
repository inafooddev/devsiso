<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\ImportBatch;

class ZvSummaryTeamEliteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->addLog('info', 'Memulai penarikan data ZV Summary Team Elite...');
                $batch->addLog('warning', 'Membersihkan data lama ZV Summary Team Elite');
            }
        }

        if (isset($batch)) {
            $batch->addLog('info', 'Mengeksekusi kueri ZV Summary Team Elite...');
        }

        $query = <<<'SQL'
TRUNCATE TABLE zv_summary_visit_team_elite;

INSERT INTO zv_summary_visit_team_elite (
    tanggal,
    region_code,
    region_name,
    area_code,
    area_name,
    "level",
    team_code,
    team_name,
    custno,
    uniq_id,
    custname,
    address,
    keterangan,
    status_visit,
    order_val
)

WITH lt_clean AS (
    SELECT
        customer_code_prc,
        uniq_kd
    FROM (
        SELECT
            customer_code_prc,
            uniq_kd,
            ROW_NUMBER() OVER (
                PARTITION BY customer_code_prc
                ORDER BY uniq_kd
            ) AS rn
        FROM list_toko_pareto_team_elite
    ) x
    WHERE rn = 1
),

team_clean AS (
    SELECT
        team_elite_code,
        region_code,
        area_code,
        "level"
    FROM (
        SELECT
            team_elite_code,
            region_code,
            area_code,
            "level",
            ROW_NUMBER() OVER (
                PARTITION BY team_elite_code
                ORDER BY region_code
            ) AS rn
        FROM team_elite_code_mappings
    ) x
    WHERE rn = 1
),

sales_clean AS (
    SELECT
        "SLSNO",
        "SLSNAME"
    FROM (
        SELECT
            "SLSNO",
            "SLSNAME",
            ROW_NUMBER() OVER (
                PARTITION BY "SLSNO"
                ORDER BY "SLSNAME"
            ) AS rn
        FROM fsalesman
    ) x
    WHERE rn = 1
),

base AS (
    SELECT
        TO_DATE(r."TANGGAL", 'YYYY-MM-DD') AS tanggal,
        r."MUID"                           AS team_code,
        r."CUSTNO"                         AS custno,
        r."CUSTNAME"                       AS custname,
        r."CUSTADD1"                       AS address,
        r."FLAG_VISIT",
        r."FLAG_BUY",
        COALESCE(r."ORDER_VAL", 0)         AS order_val
    FROM rpt_visit_an_h r
    WHERE
        r."RID" = 'HOINA'
        AND r."FLAG_PAUSE" = 'N'
        AND r."CUSTNO" NOT LIKE 'BR%'
        AND r."CUSTNO" NOT LIKE 'EV%'
        AND r."CUSTNO" NOT LIKE 'KTR%'
        AND r."CUSTNO" NOT LIKE 'BL%'
        AND r."CUSTNO" NOT LIKE 'DS%'
        AND r."CUSTNO" NOT LIKE 'TR%'
),

master_data AS (
    SELECT *
    FROM (
        SELECT
            b.tanggal,

            tc.region_code,
            mr.region_name,

            tc.area_code,
            ma.area_name,

            tc."level",

            b.team_code,

            sc."SLSNAME" AS team_name,

            b.custno,

            lc.uniq_kd AS uniq_id,

            b.custname,

            b.address,

            CASE
                WHEN b.custno ILIKE 'D202%' THEN 'NOO'
                ELSE 'RO'
            END AS keterangan,

            ROW_NUMBER() OVER (
                PARTITION BY
                    b.tanggal,
                    b.team_code,
                    b.custno
                ORDER BY
                    lc.uniq_kd NULLS LAST
            ) AS rn

        FROM base b

        LEFT JOIN team_clean tc
            ON b.team_code = tc.team_elite_code

        LEFT JOIN master_regions mr
            ON tc.region_code = mr.region_code

        LEFT JOIN master_areas ma
            ON tc.area_code = ma.area_code

        LEFT JOIN sales_clean sc
            ON b.team_code = sc."SLSNO"

        LEFT JOIN lt_clean lc
            ON b.custno = lc.customer_code_prc
    ) x
    WHERE rn = 1
),

visit_data AS (
    SELECT
        tanggal,
        team_code,
        custno,
        'Y' AS status_visit
    FROM base
    WHERE "FLAG_VISIT" = 'Y'
    GROUP BY
        tanggal,
        team_code,
        custno
),

order_data AS (
    SELECT
        tanggal,
        team_code,
        custno,
        SUM(order_val) AS order_val
    FROM base
    WHERE "FLAG_BUY" = 'Y'
    GROUP BY
        tanggal,
        team_code,
        custno
)

SELECT
    md.tanggal,
    md.region_code,
    md.region_name,
    md.area_code,
    md.area_name,
    md."level",
    md.team_code,
    md.team_name,
    md.custno,
    md.uniq_id,
    md.custname,
    md.address,
    md.keterangan,

    COALESCE(vd.status_visit, 'N') AS status_visit,

    COALESCE(od.order_val, 0) AS order_val

FROM master_data md

LEFT JOIN visit_data vd
    ON md.tanggal = vd.tanggal
    AND md.team_code = vd.team_code
    AND md.custno = vd.custno

LEFT JOIN order_data od
    ON md.tanggal = od.tanggal
    AND md.team_code = od.team_code
    AND md.custno = od.custno;
SQL;

        DB::unprepared($query);

        if (isset($batch)) {
            $batch->addLog('success', 'Sukses menarik data ZV Summary Team Elite!');
        }
    }
}
