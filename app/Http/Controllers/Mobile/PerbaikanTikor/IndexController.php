<?php

namespace App\Http\Controllers\Mobile\PerbaikanTikor;

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
        
        $outlets = collect();

        if ($salesCode) {
            $outlets = DB::table('frute as f')
                ->selectRaw('
                    f.region as region_code,
                    md.region_name,
                    f.cabang as area_code,
                    md.area_name,
                    f.kodecabang as distributor_code,
                    md.distributor_name,
                    f.slsno as sales_code,
                    fs."SLSNAME" as sales_name,
                    f.custno as customer_code,
                    cpe.custname as customer_name,
                    cpe.custadd1 as address,
                    cpe.la as latitude,
                    cpe.lg as longitude,
                    ptt.latitude as audit_latitude,
                    ptt.longitude as audit_longitude,
                    ptt.foto as foto_audit,
                    CASE
                        WHEN ptt.customer_code IS NOT NULL THEN \'Sudah\'
                        ELSE \'Belum\'
                    END AS status_audit,
                    ptt.status as status_perbaikan,
                    ptt.keterangan as keterangan_perbaikan
                ')
                ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.kodecabang')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->leftJoin('fsalesman as fs', function($join) {
                    $join->on('fs.SLSNO', '=', 'f.slsno')
                         ->on('fs.KD', '=', 'f.kodecabang');
                })
                ->leftJoin('customer_prc_eska as cpe', function($join) {
                    $join->on('cpe.kodecabang', '=', 'f.kodecabang')
                         ->on('cpe.custno', '=', 'f.custno');
                })
                ->leftJoin('perbaikan_tikor_toko as ptt', function($join) {
                    $join->on('ptt.distributor_code', '=', 'f.kodecabang')
                         ->on('ptt.customer_code', '=', 'f.custno');
                })
                ->where('md.is_active', true)
                ->where('md.region_code', '<>', 'HOINA')
                ->where('f.slsno', $salesCode)
                ->distinct()
                ->get();
        }

        return Inertia::render('Mobile/PerbaikanTikor/Index', [
            'outlets' => $outlets,
            'sessionSalesCode' => $salesCode,
            'sessionSalesName' => $salesName,
        ]);
    }

    public function searchSales(Request $request)
    {
        $q = strtolower($request->query('q', ''));
        
        $query = DB::table('frute as f')
            ->selectRaw('f.slsno as sales_code, max(fs."SLSNAME") as sales_name')
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.kodecabang')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('fsalesman as fs', function($join) {
                $join->on('fs.SLSNO', '=', 'f.slsno')
                     ->on('fs.KD', '=', 'f.kodecabang');
            })
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA')
            ->whereNotNull('f.slsno')
            ->where('f.slsno', '<>', '');

        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where(DB::raw('LOWER(f.slsno)'), 'like', "%$q%")
                  ->orWhere(DB::raw('LOWER(fs."SLSNAME")'), 'like', "%$q%");
            });
        }

        $sales = $query->groupBy('f.slsno')->limit(15)->get();
        
        return response()->json($sales);
    }

    public function loginSales(Request $request)
    {
        $request->validate(['sales_code' => 'required|string']);
        
        $sales = DB::table('frute as f')
            ->selectRaw('max(fs."SLSNAME") as sales_name')
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.kodecabang')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('fsalesman as fs', function($join) {
                $join->on('fs.SLSNO', '=', 'f.slsno')
                     ->on('fs.KD', '=', 'f.kodecabang');
            })
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA')
            ->where(DB::raw('LOWER(f.slsno)'), strtolower($request->sales_code))
            ->first();

        if (!$sales || !$sales->sales_name) {
            // Also accept if they just exist in fsalesman but maybe not correctly joined yet
            $exists = DB::table('frute as f')->where(DB::raw('LOWER(f.slsno)'), strtolower($request->sales_code))->exists();
            if (!$exists) {
                return redirect()->back()->withErrors(['sales_code' => 'Kode sales tidak ditemukan atau tidak aktif.']);
            }
        }

        session([
            'sales_code' => strtoupper($request->sales_code),
            'sales_name' => $sales->sales_name ?? strtoupper($request->sales_code)
        ]);
        return redirect()->back();
    }

    public function logoutSales(Request $request)
    {
        session()->forget(['sales_code', 'sales_name']);
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required',
            'distributor_code' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'foto' => 'nullable|image|max:5120',
        ]);

        $data = [
            'region_code' => $request->region_code,
            'area_code' => $request->area_code,
            'distributor_code' => $request->distributor_code,
            'sales_code' => session('sales_code') ?? $request->sales_code,
            'customer_code' => $request->customer_code,
            'latitude' => ($request->latitude && $request->latitude !== '0') ? $request->latitude : null,
            'longitude' => ($request->longitude && $request->longitude !== '0') ? $request->longitude : null,
            'status' => 'Pending',
            'keterangan' => null,
            'timestamp' => now(),
            'updated_at' => now(),
        ];

        // Handle File Uploads
        if ($request->hasFile('foto')) {
            $extension = $request->file('foto')->getClientOriginalExtension();
            $filename = "{$request->distributor_code}_{$request->customer_code}_foto_" . time() . ".{$extension}";
            $path = $request->file('foto')->storeAs('perbaikan_tikor', $filename, 'public');
            $data['foto'] = $path;
        }

        $existing = DB::table('perbaikan_tikor_toko')
            ->where('distributor_code', $request->distributor_code)
            ->where('customer_code', $request->customer_code)
            ->first();

        if (!$existing) {
            $data['created_at'] = now();
        }

        DB::table('perbaikan_tikor_toko')->updateOrInsert(
            [
                'distributor_code' => $request->distributor_code,
                'customer_code' => $request->customer_code
            ],
            $data
        );

        return redirect()->back()->with('success', 'Perbaikan koordinat toko berhasil disimpan.');
    }
}
