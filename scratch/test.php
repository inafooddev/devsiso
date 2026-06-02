<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first(); 
Auth::login($user); 

$query = \App\Models\JksTeamElite::query()
    ->leftJoin('master_calender as mc', 'jks_team_elite.tanggal', '=', 'mc.date')
    ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
        $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
    })
    ->select(
        'jks_team_elite.tanggal',
        'jks_team_elite.kode_region',
        'jks_team_elite.nama_region',
        'jks_team_elite.kode_team',
        'jks_team_elite.nama_team',
        'mc.week_month',
        \Illuminate\Support\Facades\DB::raw('COUNT(jks_team_elite.custno) as total_toko'),
        \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN l.pilar = \'1. RWO\' THEN 1 ELSE 0 END) as total_rwo'),
        \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN l.pilar = \'2. PNR\' THEN 1 ELSE 0 END) as total_pnr'),
        \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN l.pilar = \'3. NGVO\' THEN 1 ELSE 0 END) as total_ngvo')
    )
    ->whereIn('jks_team_elite.kode_team', ['SPI-01'])
    ->whereBetween('jks_team_elite.tanggal', ['2026-06-01', '2026-06-30']);

$query->groupBy(
        'jks_team_elite.tanggal',
        'jks_team_elite.kode_region',
        'jks_team_elite.nama_region',
        'jks_team_elite.kode_team',
        'jks_team_elite.nama_team',
        'mc.week_month'
    )
    ->orderBy('jks_team_elite.tanggal', 'desc');

DB::enableQueryLog();
$res = $query->paginate(10);
dump(DB::getQueryLog());
dump($res->items());
