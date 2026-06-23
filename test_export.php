<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $query = \Illuminate\Support\Facades\DB::table('rpt_visit_an_h as rvah')
        ->leftJoin('team_elite_code_mappings as t', 't.team_elite_code', '=', 'rvah.MUID')
        ->select(
            'rvah.ID as id',
            'rvah.MUID as supervisor_code',
            'rvah.MUNAME as supervisor_name',
            'rvah.CUSTNO as custno',
            'rvah.CUSTNAME as custname',
            'rvah.CUSTADD1 as address',
            'l.pilar as pilar',
            'l.target as target',
            \Illuminate\Support\Facades\DB::raw('rvah."TANGGAL"::date as tanggal'),
            \Illuminate\Support\Facades\DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
            \Illuminate\Support\Facades\DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
            \Illuminate\Support\Facades\DB::raw('rvah."TIME_CONSUME"::time as time_consume'),
            \Illuminate\Support\Facades\DB::raw('rvah."TIME_TRAVEL"::time as time_travel'),
            \Illuminate\Support\Facades\DB::raw('rvah."TIME_PAUSE"::time as time_pause'),
            'rvah.ORDER_QTY as qty_order',
            'rvah.ORDER_VAL as val_order',
            'rvah.FLAG_PJP as flag_pjp',
            'rvah.FLAG_VISIT as flag_visit',
            'rvah.FLAG_EC as flag_ec',
            'rvah.FLAG_BUY as flag_buy',
            'rvah.FLAG_PAUSE as flag_pause',
            'rvah.V_LA as visit_lat',
            'rvah.V_LG as visit_lon',
            'rvar.REASON_TYPE as reason_type',
            'rvar.REASON_DESC as reason_desc',
            'rak.remark as action_remark'
        )
        ->leftJoin('rpt_visit_an_r as rvar', function($join) {
            $join->on('rvah.ID', '=', 'rvar.HID')
                 ->on('rvah.MUID', '=', 'rvar.MUID')
                 ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                 ->on(\Illuminate\Support\Facades\DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
        })
        ->leftJoin('remark_analisa_kunjungan as rak', function($join) {
            $join->on('rvah.ID', '=', 'rak.visit_id')
                 ->on('rvah.MUID', '=', 'rak.muid')
                 ->on('rvah.CUSTNO', '=', 'rak.custno')
                 ->on(\Illuminate\Support\Facades\DB::raw('rvah."TANGGAL"::date'), '=', 'rak.tanggal');
        })
        ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
        ->orderBy(\Illuminate\Support\Facades\DB::raw('rvah."TANGGAL"::date'), 'asc')
        ->orderBy('rvah.ID', 'asc')
        ->limit(10);
        
    $export = new \App\Exports\AnalisaKunjunganExport($query);
    \Maatwebsite\Excel\Facades\Excel::store($export, 'test.xlsx');
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
