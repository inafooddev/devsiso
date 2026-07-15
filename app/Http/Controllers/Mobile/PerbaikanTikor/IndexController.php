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
        
        $tokoList = collect();
        $riwayatPerbaikan = collect();

        if ($salesCode) {
            $tokoList = DB::table('frute as f')
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
                    ptt.status as status_perbaikan
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
                ->leftJoinSub(
                    DB::table('perbaikan_tikor_toko')
                        ->whereIn('id', function($q) {
                            $q->select(DB::raw('MAX(id)'))
                              ->from('perbaikan_tikor_toko')
                              ->groupBy('distributor_code', 'customer_code');
                        }),
                    'ptt',
                    function($join) {
                        $join->on('ptt.distributor_code', '=', 'f.kodecabang')
                             ->on('ptt.customer_code', '=', 'f.custno');
                    }
                )
                ->where('md.is_active', true)
                ->where('md.region_code', '<>', 'HOINA')
                ->where('f.slsno', $salesCode)
                ->distinct()
                ->orderBy('f.custno')
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
                    cpe.custname as customer_name,
                    cpe.custadd1 as address,
                    cpe.la as latitude,
                    cpe.lg as longitude,
                    md.distributor_name,
                    md.area_name
                ')
                ->leftJoin('customer_prc_eska as cpe', function($join) {
                    $join->on('cpe.kodecabang', '=', 'ptt.distributor_code')
                         ->on('cpe.custno', '=', 'ptt.customer_code');
                })
                ->leftJoin('master_distributors as md', 'ptt.distributor_code', '=', 'md.distributor_code')
                ->where('ptt.sales_code', $salesCode)
                ->orderBy('ptt.created_at', 'desc')
                ->limit(500)
                ->get();
        }

        return Inertia::render('Mobile/PerbaikanTikor/Index', [
            'tokoList' => $tokoList,
            'riwayatPerbaikan' => $riwayatPerbaikan,
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
            $q = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where(function($w) use ($q) {
                $w->where(DB::raw('f.slsno'), 'ILIKE', "%$q%")
                  ->orWhere(DB::raw('fs."SLSNAME"'), 'ILIKE', "%$q%");
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
            'sales_name' => $sales?->sales_name ?? strtoupper($request->sales_code)
        ]);
        return redirect()->route('mobile.perbaikan.tikor.index');
    }

    public function logoutSales(Request $request)
    {
        session()->forget(['sales_code', 'sales_name']);
        return redirect()->route('mobile.perbaikan.tikor.index');
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

        $isOwner = DB::table('frute as f')
            ->where('f.slsno', $salesCode)
            ->where('f.custno', $request->customer_code)
            ->where('f.kodecabang', DB::raw("(
                SELECT die.eskalink_code FROM distributor_implementasi_eskalink die 
                WHERE die.distributor_code = '{$request->distributor_code}' LIMIT 1
            )"))
            ->exists();

        if (!$isOwner) {
            return redirect()->back()->withErrors(['error' => 'Toko ini bukan milik Anda atau data tidak valid.']);
        }

        // Cek jika sudah ada pengajuan perbaikan yang masih berstatus 'Pending' untuk sales, distributor, dan customer ini
        $isPending = DB::table('perbaikan_tikor_toko')
            ->where('distributor_code', $request->distributor_code)
            ->where('customer_code', $request->customer_code)
            ->where('sales_code', $salesCode)
            ->where('status', 'Pending')
            ->exists();

        if ($isPending) {
            return redirect()->back()->withErrors(['error' => 'Sudah diajukan perbaikan, status masih pending.']);
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
            'source' => 'se',
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
