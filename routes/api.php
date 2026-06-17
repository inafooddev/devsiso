<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// API Khusus GET Master Distributors
Route::middleware(['auth:sanctum', 'ability:read:distributors'])->get('/distributors', function () {
    return \App\Models\MasterDistributor::select(
        'distributor_code',
        'distributor_name',
        'branch_name'
    )->get();
});

// API Khusus GET List Toko Pareto Team Elite
Route::middleware(['auth:sanctum', 'ability:read:toko_pareto'])->get('/toko-pareto', function () {
    return \App\Models\ListTokoParetoTeamElite::select(
        'distributor_code',
        'customer_code_prc',
        'customer_name',
        'pilar',
        'target'
    )->get();
});

// API Khusus GET JKS Team Elite
Route::middleware(['auth:sanctum', 'ability:read:jks_team_elite'])->get('/jks-team-elite', function () {
    return \App\Models\JksTeamElite::select(
        'kode_team',
        'distributor_code',
        'tanggal',
        'custno'
    )->get();
});

// =========================================================================
// REGISTER DYNAMIC APIs (Berasal dari Dynamic API Builder UI)
// =========================================================================
if (\Illuminate\Support\Facades\Schema::hasTable('dynamic_apis')) {
    try {
        $dynamicApis = \App\Models\DynamicApi::all();
        foreach ($dynamicApis as $api) {
            // Kita buat nama ability berdasarkan endpoint. Contoh: read:laporan_harian
            $abilityName = 'ability:manage:' . str_replace('-', '_', $api->endpoint);
            $method = strtoupper($api->method ?? 'GET');
            
            Route::middleware(['auth:sanctum', $abilityName])->match([$method], '/' . $api->endpoint, function (\Illuminate\Http\Request $request) use ($api, $method) {
                // Ekstrak parameter binding (contoh: :id, :name) dari SQL
                // Menghindari double colon PostgreSQL (::)
                preg_match_all('/(?<!:):([a-zA-Z0-9_]+)/', $api->sql_query, $matches);
                $expectedParams = $matches[1];
                
                $bindings = [];
                foreach ($expectedParams as $param) {
                    // Ambil dari request (query string atau form body)
                    $bindings[$param] = $request->input($param);
                }

                // Eksekusi raw query sesuai method
                if ($method === 'GET') {
                    $results = \Illuminate\Support\Facades\DB::select($api->sql_query, $bindings);
                    return response()->json($results);
                } else {
                    \Illuminate\Support\Facades\DB::statement($api->sql_query, $bindings);
                    return response()->json(['message' => 'Raw query executed successfully.']);
                }
            });
        }
    } catch (\Exception $e) {
        // Abaikan jika terjadi error saat booting (misal saat proses migrasi awal)
    }
}
