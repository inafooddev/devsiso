<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function getDistance($lat1, $lon1, $lat2, $lon2)
{
    if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
    
    $earthRadius = 6371000; // in meters
    $latFrom = deg2rad((float)$lat1);
    $lonFrom = deg2rad((float)$lon1);
    $latTo = deg2rad((float)$lat2);
    $lonTo = deg2rad((float)$lon2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return round($angle * $earthRadius); // in meters
}

try {
    $start = microtime(true);
    
    $query = \Illuminate\Support\Facades\DB::table('rpt_visit_an_h as rvah')
        ->leftJoin('team_elite_code_mappings as t', 't.team_elite_code', '=', 'rvah.MUID')
        ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
        ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
        ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
        ->where(\Illuminate\Support\Facades\DB::raw('rvah."TANGGAL"::date'), '>=', '2026-06-01')
        ->select(
            'mr.region_name',
            'ma.area_name',
            'rvah.MUNAME as supervisor_name',
            't.team_elite_code as supervisor_code',
            'rvah.FLAG_VISIT as flag_visit',
            'l.target as target',
            'rvah.ORDER_VAL as order_val',
            'l.pilar as pilar',
            'rvah.V_LA as v_la',
            'rvah.V_LG as v_lg',
            'rvah.M_LA as m_la',
            'rvah.M_LG as m_lg'
        );
        
    $rows = $query->get();
    
    $grouped = $rows->groupBy('supervisor_code')->map(function($items) {
        $first = $items->first();
        $pc = $items->count();
        $ac = $items->where('flag_visit', 'Y')->count();
        
        $outOfArea = 0;
        foreach($items as $item) {
            if ($item->flag_visit === 'Y') {
                $dist = getDistance($item->v_la, $item->v_lg, $item->m_la, $item->m_lg);
                if ($dist > 50) $outOfArea++;
            }
        }
        
        return [
            'region_name' => $first->region_name,
            'area_name' => $first->area_name,
            'supervisor_name' => $first->supervisor_name,
            'pc' => $pc,
            'ac' => $ac,
            'pc_ac_pct' => $pc > 0 ? round(($ac / $pc) * 100, 2) : 0,
            'target' => $items->sum('target'),
            'order' => $items->sum('order_val'),
            'target_order_pct' => $items->sum('target') > 0 ? round(($items->sum('order_val') / $items->sum('target')) * 100, 2) : 0,
            'rwo' => $items->where('pilar', '1. RWO')->where('flag_visit', 'Y')->count(),
            'pnr' => $items->where('pilar', '2. PNR')->where('flag_visit', 'Y')->count(),
            'ngvo' => $items->where('pilar', '3. NGVO')->where('flag_visit', 'Y')->count(),
            'out_of_area' => $outOfArea
        ];
    })->values()->toArray();
    
    $time = microtime(true) - $start;
    echo "Rows: " . count($rows) . "\n";
    echo "Groups: " . count($grouped) . "\n";
    echo "Time: {$time}s\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
