<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$comp = new class extends App\Livewire\Report\AnalisaKunjungan\Index {
    public function authorizeAction($action = 'can_edit') { return true; }
    public function getAccessLevel() { return 'region'; }
};
$comp->appliedRegion = 'INAJWA1';
$comp->appliedStartDate = '2024-01-01';
$comp->appliedEndDate = '2024-01-02';
$comp->activeTab = 'detail';

$query = $comp->getBaseQuery()
    ->select(
        'rvah.ID as id',
        'rvah.MUID as supervisor_code',
        'rvar.REASON_TYPE as reason_type'
    )
    ->leftJoin('rpt_visit_an_r as rvar', function($join) {
        $join->on('rvah.ID', '=', 'rvar.HID')
             ->on('rvah.MUID', '=', 'rvar.MUID')
             ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
             ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
    })
    ->leftJoin('remark_analisa_kunjungan as rak', function($join) {
        $join->on('rvah.ID', '=', 'rak.visit_id')
             ->on('rvah.MUID', '=', 'rak.muid')
             ->on('rvah.CUSTNO', '=', 'rak.custno')
             ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rak.tanggal');
    })
    ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO');

echo $query->toSql() . "\n";
