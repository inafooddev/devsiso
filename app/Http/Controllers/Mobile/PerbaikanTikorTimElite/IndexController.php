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
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('mobile.login');
        }

        $sessionSupervisorCode = $user->supervisor_code ?? $user->userid;
        $sessionSupervisorName = $user->name;
        
        $tokoList = collect();
        $riwayatPerbaikan = collect();

        $queryToko = DB::table('list_toko_pareto_team_elite as l')
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
            ->whereRaw("UPPER(l.customer_name) NOT LIKE '%BRIEFING%'")
            ->whereRaw("UPPER(l.customer_name) NOT LIKE '%EVALUASI%'");

        $search = $request->input('search');
        if ($search) {
            $searchUpper = strtoupper($search);
            $queryToko->where(function($q) use ($searchUpper) {
                $q->whereRaw("UPPER(l.customer_name) LIKE ?", ["%{$searchUpper}%"])
                  ->orWhereRaw("UPPER(l.customer_code_prc) LIKE ?", ["%{$searchUpper}%"])
                  ->orWhereRaw("UPPER(md.distributor_name) LIKE ?", ["%{$searchUpper}%"]);
            });
        }

        // ==== APLIKASI HAK AKSES (FILTERING) ====
        if ($user->supervisor_code) {
            // Level SPV / Sales
            $queryToko->where(function($q) use ($user) {
                $q->where('t.team_elite_code', $user->supervisor_code)
                  ->orWhere('md.supervisor_code', $user->supervisor_code);
            });
        } elseif ($user->area_code) {
            // Level Area Manager
            $queryToko->where('md.area_code', $user->area_code);
        } elseif ($user->region_code) {
            // Level Region Manager
            $regions = is_string($user->region_code) ? json_decode($user->region_code, true) : $user->region_code;
            $regions = $regions ?? [];
            if (!in_array('HOINA', $regions)) {
                $queryToko->whereIn('md.region_code', $regions);
            }
        }

        $tokoList = $queryToko
            ->distinct()
            ->orderBy('l.customer_code_prc')
            ->limit(500)
            ->get();

        $queryRiwayat = DB::table('perbaikan_tikor_toko as ptt')
            ->selectRaw('
                ptt.id,
                ptt.region_code,
                ptt.area_code,
                ptt.distributor_code,
                ptt.sales_code,
                CASE 
                    WHEN ptt.source = \'se\' THEN f_se."SLSNAME"
                    WHEN ptt.source = \'elite\' THEN f_elite."SLSNAME"
                    ELSE u.name 
                END as sales_name,
                ptt.customer_code,
                ptt.latitude as audit_latitude,
                ptt.longitude as audit_longitude,
                ptt.foto as foto_audit,
                ptt.status as status_perbaikan,
                ptt.keterangan as keterangan_perbaikan,
                ptt.created_at,
                ptt.updated_at,
                ptt.source,
                CASE 
                    WHEN ptt.source = \'se\' THEN c.custname
                    ELSE l.customer_name 
                END as customer_name,
                CASE 
                    WHEN ptt.source = \'se\' THEN c.custadd1
                    ELSE l.customer_address 
                END as address,
                l.latitude as latitude,
                l.longitude as longitude,
                md.distributor_name,
                md.area_name
            ')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('l.distributor_code', '=', 'ptt.distributor_code')
                     ->on('l.customer_code_prc', '=', 'ptt.customer_code');
            })
            ->leftJoin('customer_prc_eska as c', function($join) {
                $join->on('c.kodecabang', '=', 'ptt.distributor_code')
                     ->on('c.custno', '=', 'ptt.customer_code');
            })
            ->leftJoin('master_distributors as md', 'ptt.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('users as u', 'ptt.sales_code', '=', 'u.userid')
            ->leftJoin('fsalesman as f_se', function($join) {
                $join->on('f_se.SLSNO', '=', 'ptt.sales_code')
                     ->on('f_se.KD', '=', 'ptt.distributor_code');
            })
            ->leftJoin('fsalesman as f_elite', 'f_elite.SLSNO', '=', 'ptt.sales_code')
            ->where(function($q) {
                $q->whereRaw("COALESCE(UPPER(l.customer_name), '') NOT LIKE '%BRIEFING%'")
                  ->whereRaw("COALESCE(UPPER(c.custname), '') NOT LIKE '%BRIEFING%'");
            })
            ->where(function($q) {
                $q->whereRaw("COALESCE(UPPER(l.customer_name), '') NOT LIKE '%EVALUASI%'")
                  ->whereRaw("COALESCE(UPPER(c.custname), '') NOT LIKE '%EVALUASI%'");
            });

        if ($search) {
            $searchUpper = strtoupper($search);
            $queryRiwayat->where(function($q) use ($searchUpper) {
                $q->whereRaw("COALESCE(UPPER(l.customer_name), '') LIKE ?", ["%{$searchUpper}%"])
                  ->orWhereRaw("COALESCE(UPPER(c.custname), '') LIKE ?", ["%{$searchUpper}%"])
                  ->orWhereRaw("UPPER(ptt.customer_code) LIKE ?", ["%{$searchUpper}%"])
                  ->orWhereRaw("UPPER(md.distributor_name) LIKE ?", ["%{$searchUpper}%"]);
            });
        }

        if ($user->supervisor_code) {
            $queryRiwayat->where(function($q) use ($user) {
                $q->where('ptt.sales_code', $user->supervisor_code)
                  ->orWhere('md.supervisor_code', $user->supervisor_code);
            });
        } elseif ($user->area_code) {
            $queryRiwayat->where('md.area_code', $user->area_code);
        } elseif ($user->region_code) {
            $regions = is_string($user->region_code) ? json_decode($user->region_code, true) : $user->region_code;
            $regions = $regions ?? [];
            if (!in_array('HOINA', $regions)) {
                $queryRiwayat->whereIn('md.region_code', $regions);
            }
        }

        $riwayatPerbaikan = $queryRiwayat
            ->orderBy('ptt.created_at', 'desc')
            ->limit(500)
            ->get();

        // ==== QUERY PLAN (VISIT) ====
        $listPlan = [];
        try {
            $queryPlan = DB::table('jks_team_elite as j')
                ->selectRaw('
                    j.tanggal as visit_date,
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
                ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                    $join->on('l.distributor_code', '=', 'j.distributor_code')
                         ->on('l.customer_code_prc', '=', 'j.custno');
                })
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
                ->whereRaw("UPPER(l.customer_name) NOT LIKE '%BRIEFING%'")
                ->whereRaw("UPPER(l.customer_name) NOT LIKE '%EVALUASI%'")
                ->whereRaw('UPPER(j.kode_team) = ?', [strtoupper($user->userid)]);
                
            if ($search) {
                $searchUpper = strtoupper($search);
                $queryPlan->where(function($q) use ($searchUpper) {
                    $q->whereRaw("UPPER(l.customer_name) LIKE ?", ["%{$searchUpper}%"])
                      ->orWhereRaw("UPPER(l.customer_code_prc) LIKE ?", ["%{$searchUpper}%"])
                      ->orWhereRaw("UPPER(md.distributor_name) LIKE ?", ["%{$searchUpper}%"]);
                });
            }

            $listPlan = $queryPlan->get();
        } catch (\Exception $e) {
            $listPlan = [];
        }

        return Inertia::render('mobile/Pages/PerbaikanTikorTimElite/Index', [
            'tokoList' => $tokoList,
            'riwayatPerbaikan' => $riwayatPerbaikan,
            'listPlan' => $listPlan,
            'filters' => [
                'search' => $search
            ],
            'user' => [
                'name' => $sessionSupervisorName,
                'role' => $user->supervisor_code ? 'SPV/Sales' : ($user->area_code ? 'Area Manager' : 'Region Manager'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'Sesi tidak valid. Harap login kembali.']);
        }
        $salesCode = $user->supervisor_code ?? $user->userid;

        $request->validate([
            'customer_code' => 'required',
            'distributor_code' => 'required',
            'latitude' => 'required|numeric|between:-90,90|not_in:0',
            'longitude' => 'required|numeric|between:-180,180|not_in:0',
            'accuracy' => 'nullable|numeric',
            'foto' => 'required|image|max:5120',
        ]);

        $query = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as t', 'md.supervisor_code', '=', 't.siso_code')
            ->where('l.customer_code_prc', $request->customer_code)
            ->where('l.distributor_code', $request->distributor_code);

        if ($user->supervisor_code) {
            $query->where(function($q) use ($user) {
                $q->where('t.team_elite_code', $user->supervisor_code)
                  ->orWhere('md.supervisor_code', $user->supervisor_code);
            });
        } elseif ($user->area_code) {
            $query->where('md.area_code', $user->area_code);
        } elseif ($user->region_code) {
            $regions = is_string($user->region_code) ? json_decode($user->region_code, true) : $user->region_code;
            $regions = $regions ?? [];
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('md.region_code', $regions);
            }
        }

        $isOwner = $query->exists();

        if (!$isOwner) {
            return redirect()->back()->withErrors(['error' => 'Toko ini bukan dalam cakupan wilayah Anda atau data tidak valid.']);
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
            'source' => 'elite',
            'updated_at' => now(),
            'created_at' => now(),
        ];

        // Handle File Uploads
        if ($request->hasFile('foto')) {
            $extension = $request->file('foto')->extension();
            $filename = "{$request->distributor_code}_{$request->customer_code}_foto_" . time() . ".{$extension}";
            $path = $request->file('foto')->storeAs('perbaikan_tikor', $filename, 'public');
            $data['foto'] = $path;
        }

        DB::table('perbaikan_tikor_toko')->insert($data);

        return redirect()->back()->with('success', 'Perbaikan koordinat toko berhasil disimpan.');
    }
}
