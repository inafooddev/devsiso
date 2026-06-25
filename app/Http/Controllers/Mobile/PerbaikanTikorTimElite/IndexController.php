<?php

namespace App\Http\Controllers\Mobile\PerbaikanTikorTimElite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $salesCode = session('sales_code');
        $salesName = session('sales_name');

        if ($salesCode && !$salesName) {
            $salesName = DB::table('fsalesman')->where('SLSNO', $salesCode)->value('SLSNAME') ?? $salesCode;
        }
        
        $tokoList = collect();
        $riwayatPerbaikan = collect();

        if ($salesCode) {
            $tokoList = DB::table('list_toko_pareto_team_elite as l')
                ->selectRaw('
                    md.region_code,
                    md.region_name,
                    md.area_code,
                    md.area_name,
                    l.distributor_code,
                    md.distributor_name,
                    t.team_elite_code as sales_code,
                    f."SLSNAME" as sales_name,
                    l.customer_code_prc as customer_code,
                    l.customer_name,
                    l.customer_address as address,
                    l.latitude,
                    l.longitude,
                    ptt.status as status_perbaikan
                ')
                ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
                ->leftJoin('team_elite_code_mappings as t', 'md.supervisor_code', '=', 't.siso_code')
                ->leftJoin('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
                ->leftJoinSub(
                    DB::table('perbaikan_tikor_toko')
                        ->whereIn('id', function($q) {
                            $q->select(DB::raw('MAX(id)'))
                              ->from('perbaikan_tikor_toko')
                              ->groupBy('distributor_code', 'customer_code');
                        }),
                    'ptt',
                    function($join) {
                        $join->on('ptt.distributor_code', '=', 'l.distributor_code')
                             ->on('ptt.customer_code', '=', 'l.customer_code_prc');
                    }
                )
                ->where('md.is_active', true)
                ->where('t.team_elite_code', $salesCode)
                ->distinct()
                ->orderBy('l.customer_code_prc')
                ->limit(500)
                ->get();

            $riwayatPerbaikan = DB::table('perbaikan_tikor_toko as ptt')
                ->selectRaw('
                    ptt.id,
                    ptt.region_code,
                    ptt.area_code,
                    ptt.distributor_code,
                    ptt.sales_code,
                    ptt.customer_code,
                    ptt.latitude as audit_latitude,
                    ptt.longitude as audit_longitude,
                    ptt.foto as foto_audit,
                    ptt.status as status_perbaikan,
                    ptt.keterangan as keterangan_perbaikan,
                    ptt.created_at,
                    l.customer_name as customer_name,
                    l.customer_address as address,
                    l.latitude as latitude,
                    l.longitude as longitude,
                    md.distributor_name,
                    md.area_name
                ')
                ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                    $join->on('l.distributor_code', '=', 'ptt.distributor_code')
                         ->on('l.customer_code_prc', '=', 'ptt.customer_code');
                })
                ->leftJoin('master_distributors as md', 'ptt.distributor_code', '=', 'md.distributor_code')
                ->where('ptt.sales_code', $salesCode)
                ->orderBy('ptt.created_at', 'desc')
                ->limit(500)
                ->get();
        }

        return Inertia::render('Mobile/PerbaikanTikorTimElite/Index', [
            'tokoList' => $tokoList,
            'riwayatPerbaikan' => $riwayatPerbaikan,
            'sessionSalesCode' => $salesCode,
            'sessionSalesName' => $salesName,
        ]);
    }

    public function searchSales(Request $request)
    {
        $q = strtolower($request->query('q', ''));
        
        $query = DB::table('team_elite_code_mappings as t')
            ->selectRaw('t.team_elite_code as sales_code, max(f."SLSNAME") as sales_name')
            ->leftJoin('master_distributors as md', 'md.supervisor_code', '=', 't.siso_code')
            ->leftJoin('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
            ->where('md.is_active', true)
            ->whereNotNull('t.team_elite_code')
            ->where('t.team_elite_code', '<>', '');

        if ($q) {
            $q = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where(function($w) use ($q) {
                $w->where(DB::raw('t.team_elite_code'), 'ILIKE', "%$q%")
                  ->orWhere(DB::raw('f."SLSNAME"'), 'ILIKE', "%$q%");
            });
        }

        $sales = $query->groupBy('t.team_elite_code')->limit(15)->get();
        
        return response()->json($sales);
    }

    public function loginSales(Request $request)
    {
        $request->validate(['sales_code' => 'required|string']);
        
        $sales = DB::table('team_elite_code_mappings as t')
            ->selectRaw('max(f."SLSNAME") as sales_name')
            ->leftJoin('master_distributors as md', 'md.supervisor_code', '=', 't.siso_code')
            ->leftJoin('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
            ->where('md.is_active', true)
            ->where(DB::raw('LOWER(t.team_elite_code)'), strtolower($request->sales_code))
            ->first();

        if (!$sales || !$sales->sales_name) {
            $exists = DB::table('team_elite_code_mappings as t')->where(DB::raw('LOWER(t.team_elite_code)'), strtolower($request->sales_code))->exists();
            if (!$exists) {
                return redirect()->back()->withErrors(['sales_code' => 'Kode tim elite tidak ditemukan atau tidak aktif.']);
            }
        }

        session([
            'sales_code' => strtoupper($request->sales_code),
            'sales_name' => $sales?->sales_name ?? strtoupper($request->sales_code)
        ]);
        return redirect()->route('mobile.perbaikan.tikor-tim-elite.index');
    }

    public function logoutSales(Request $request)
    {
        session()->forget(['sales_code', 'sales_name']);
        return redirect()->route('mobile.perbaikan.tikor-tim-elite.index');
    }

    public function store(Request $request)
    {
        $salesCode = session('sales_code');
        if (!$salesCode) {
            return redirect()->back()->withErrors(['error' => 'Sesi tidak valid. Harap login kembali.']);
        }

        $request->validate([
            'customer_code' => 'required',
            'distributor_code' => 'required',
            'latitude' => 'required|numeric|between:-90,90|not_in:0',
            'longitude' => 'required|numeric|between:-180,180|not_in:0',
            'accuracy' => 'nullable|numeric',
            'foto' => 'required|image|max:5120',
        ]);

        $isOwner = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as t', 'md.supervisor_code', '=', 't.siso_code')
            ->where('t.team_elite_code', $salesCode)
            ->where('l.customer_code_prc', $request->customer_code)
            ->where('l.distributor_code', $request->distributor_code)
            ->exists();

        if (!$isOwner) {
            return redirect()->back()->withErrors(['error' => 'Toko ini bukan milik Anda atau data tidak valid.']);
        }

        $data = [
            'region_code' => $request->region_code,
            'area_code' => $request->area_code,
            'distributor_code' => $request->distributor_code,
            'sales_code' => $salesCode,
            'customer_code' => $request->customer_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'status' => 'Pending',
            'keterangan' => null,
            'timestamp' => now(),
            'updated_at' => now(),
        ];

        // Handle File Uploads
        if ($request->hasFile('foto')) {
            $extension = $request->file('foto')->extension();
            $filename = "{$request->distributor_code}_{$request->customer_code}_foto_" . time() . ".{$extension}";
            $path = $request->file('foto')->storeAs('perbaikan_tikor', $filename, 'public');
            $data['foto'] = $path;
        }

        $data['created_at'] = now();
        DB::table('perbaikan_tikor_toko')->insert($data);

        return redirect()->back()->with('success', 'Perbaikan koordinat toko berhasil disimpan.');
    }
}
