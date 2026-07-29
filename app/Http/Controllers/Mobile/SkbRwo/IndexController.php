<?php

namespace App\Http\Controllers\Mobile\SkbRwo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

        $listPotensi = [];
        $listPlan = [];

        $currentQuarter = ceil(date('n') / 3);

        // 1. Data List Potensi RWO
        $queryPotensi = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoin('list_toko_pareto_team_elite as lt', 'lt.uniq_kd', '=', 'l.customer_code')
            ->where('l.kuartal', $currentQuarter)
            ->select(
                'l.*', 
                'lt.customer_code_prc as customer_prc',
                'md.region_name', 'md.area_name', 'md.supervisor_name', 'md.distributor_name',
                'l.alamat as address',
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3',
                'skb.is_approved', 'skb.foto_skb as skb_foto', 'skb.reason as skb_reason',
                DB::raw("CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END AS status_skb"),
                DB::raw("CASE WHEN 
                    NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                    NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                    NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                    THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap")
            )
            ->distinct();



        // Subquery agregasi tunggal untuk zv_so_per_toko_2026 (menggabungkan achievement, statistik transaksi, dan bulanan)
        $zvCombined = DB::table('zv_so_per_toko_2026')
            ->select(
                'kd_dist', 
                'uniq_kd', 
                DB::raw('CAST(EXTRACT(QUARTER FROM bulan) AS INTEGER) as kuartal'),
                DB::raw('SUM(neto) as total_achievement'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 1 THEN neto ELSE 0 END) as month_1_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 2 THEN neto ELSE 0 END) as month_2_value'),
                DB::raw('SUM(CASE WHEN EXTRACT(MONTH FROM bulan) % 3 = 0 THEN neto ELSE 0 END) as month_3_value'),
                DB::raw('MAX(neto) as max_transaction'),
                DB::raw('AVG(neto) as avg_transaction'),
                DB::raw('SUM(neto) as total_transaction')
            )
            ->groupBy('kd_dist', 'uniq_kd', DB::raw('CAST(EXTRACT(QUARTER FROM bulan) AS INTEGER)'));

        // 3. Data Plan Kunjungan (jks_team_elite)
        $queryPlan = DB::table('jks_team_elite as j')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('l.distributor_code', '=', 'j.distributor_code')
                     ->on('l.customer_code_prc', '=', 'j.custno');
            })
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'j.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.uniq_kd')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) use ($currentQuarter) {
                $join->on('skb.customer_code', '=', 'l.uniq_kd')
                     ->on('skb.distributor_code', '=', 'j.distributor_code')
                     ->on('skb.kuartal', '=', DB::raw($currentQuarter));
            })
            ->leftJoin('list_potensi_rwo as lp', function($join) use ($currentQuarter) {
                $join->on('lp.customer_code', '=', 'l.uniq_kd')
                     ->on('lp.distributor_code', '=', 'j.distributor_code')
                     ->on('lp.kuartal', '=', DB::raw($currentQuarter));
            })
            ->leftJoinSub(
                $zvCombined,
                'zv',
                function($join) use ($currentQuarter) {
                    $join->on('zv.kd_dist', '=', 'j.distributor_code')
                         ->on('zv.uniq_kd', '=', 'l.uniq_kd')
                         ->on(DB::raw('zv.kuartal::text'), '=', DB::raw("'$currentQuarter'"));
                }
            )
            ->where('l.pilar', '1. RWO')
            ->select(
                'j.tanggal',
                'j.distributor_code',
                'l.uniq_kd as customer_code',
                'l.customer_code_prc as customer_prc',
                'md.region_name', 'md.area_name', 'md.supervisor_name', 'md.distributor_name',
                'lp.total_target',
                'j.custname as customer_name',
                'j.addres as address',
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3',
                'skb.is_approved', 'skb.foto_skb as skb_foto', 'skb.reason as skb_reason',
                DB::raw("CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END AS status_skb"),
                DB::raw("CASE WHEN 
                    NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                    NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                    NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                    THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap"),
                'zv.total_achievement',
                'zv.month_1_value',
                'zv.month_2_value',
                'zv.month_3_value',
                'zv.max_transaction',
                'zv.avg_transaction',
                'zv.total_transaction',
                DB::raw("$currentQuarter as kuartal")
            );

        // ==== QUERY MONITORING ====
        $queryMonitoring = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.distributor_code', '=', 'l.distributor_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoin('list_toko_pareto_team_elite as lt', 'lt.uniq_kd', '=', 'l.customer_code')
            ->leftJoinSub(
                $zvCombined,
                'zv',
                function($join) {
                    $join->on('zv.kd_dist', '=', 'l.distributor_code')
                         ->on('zv.uniq_kd', '=', 'l.customer_code')
                         ->on(DB::raw('zv.kuartal::text'), '=', DB::raw('l.kuartal::text'));
                }
            )
            ->select(
                'l.*', 
                'lt.customer_code_prc as customer_prc',
                'md.region_name', 'md.area_name', 'md.supervisor_name', 'md.distributor_name',
                'l.alamat as address',
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3',
                'skb.is_approved', 'skb.foto_skb as skb_foto', 'skb.reason as skb_reason',
                DB::raw("CASE WHEN skb.customer_code IS NOT NULL THEN 'Sudah' ELSE 'Belum' END AS status_skb"),
                DB::raw("CASE WHEN 
                    NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                    NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                    NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                    NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                    NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                    THEN 'Lengkap' ELSE 'Belum' END AS status_data_lengkap"),
                'zv.total_achievement',
                'zv.month_1_value',
                'zv.month_2_value',
                'zv.month_3_value',
                'zv.max_transaction',
                'zv.avg_transaction',
                'zv.total_transaction'
            )
            ->distinct();

        // ==== APLIKASI HAK AKSES (FILTERING) ====
        $userAreaCodes = !empty($user->area_code) ? array_filter((array) $user->area_code) : [];
        if (is_string($user->region_code)) {
            $userRegionCodes = json_decode($user->region_code, true) ?? [];
        } else {
            $userRegionCodes = (array) ($user->region_code ?? []);
        }
        $userRegionCodes = array_filter($userRegionCodes);

        if ($user->supervisor_code) {
            // Level SPV: Pre-collect mapped codes (siso_code, team_elite_code, userid) to utilize B-Tree index scans
            $spvCode = $user->supervisor_code;
            $spvCodes = DB::table('team_elite_code_mappings')
                ->where('siso_code', $spvCode)
                ->orWhere('team_elite_code', $spvCode)
                ->pluck('team_elite_code')
                ->concat(
                    DB::table('team_elite_code_mappings')
                        ->where('siso_code', $spvCode)
                        ->orWhere('team_elite_code', $spvCode)
                        ->pluck('siso_code')
                )
                ->push($spvCode)
                ->push($user->userid)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $queryPotensi->whereIn('md.supervisor_code', $spvCodes);
            $queryMonitoring->whereIn('md.supervisor_code', $spvCodes);
            $queryPlan->where(function($q) use ($spvCodes) {
                $q->whereIn('j.kode_team', $spvCodes)
                  ->orWhereIn('md.supervisor_code', $spvCodes);
            });
        } elseif (!empty($userAreaCodes)) {
            // Level Area Manager
            $queryPotensi->whereIn('md.area_code', $userAreaCodes);
            $queryMonitoring->whereIn('md.area_code', $userAreaCodes);
            $queryPlan->whereIn('md.area_code', $userAreaCodes);
        } elseif (!empty($userRegionCodes)) {
            if (!in_array('HOINA', $userRegionCodes)) {
                $queryPotensi->whereIn('md.region_code', $userRegionCodes);
                $queryMonitoring->whereIn('md.region_code', $userRegionCodes);
                $queryPlan->whereIn('md.region_code', $userRegionCodes);
            }
        }

        $listPotensi = $queryPotensi->get();
        $listMonitoring = $queryMonitoring->get();
        
        try {
            $listPlan = $queryPlan->get();
        } catch (\Exception $e) {
            $listPlan = [];
        }

        return Inertia::render('mobile/Pages/SkbRwo/Index', [
            'listPotensi' => $listPotensi,
            'listMonitoring' => $listMonitoring,
            'listPlan' => $listPlan,
            'sessionSupervisorCode' => $sessionSupervisorCode,
            'sessionSupervisorName' => $sessionSupervisorName,
        ]);
    }

    public function loginSupervisor(Request $request)
    {
        // Fungsi ini tidak dipakai lagi (Deprecated karena menggunakan Unified Login)
        return back();
    }

    public function logoutSupervisor(Request $request)
    {
        // Fungsi ini tidak dipakai lagi (Deprecated karena menggunakan Unified Login)
        return back();
    }

    public function submitSkb(Request $request)
    {
        if (!auth()->check()) {
            return back()->withErrors(['error' => 'Sesi tidak valid. Harap login kembali.']);
        }

        $request->validate([
            'customer_code' => 'required|string',
            'distributor_code' => 'required|string',
            'kuartal' => 'nullable|string',
            'approval_status' => 'required|in:approve,reject',
            'foto_skb' => 'nullable|image',
            'reject_reason' => 'required_if:approval_status,reject|max:500'
        ]);

        $skb = \App\Models\SuratKesepakatanBersamaRwo::firstOrNew([
            'customer_code' => $request->customer_code,
            'distributor_code' => $request->distributor_code,
            'kuartal' => $request->kuartal,
        ]);

        $skb->is_approved = ($request->approval_status === 'approve');
        $skb->reason = ($request->approval_status === 'reject') ? $request->reject_reason : null;

        if ($request->hasFile('foto_skb')) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('foto_skb')->getRealPath());
            $image->scaleDown(width: 1024);
            $filename = uniqid() . '.jpg';
            $path = 'skb_photos/' . $filename;
            Storage::disk('public')->put($path, (string) $image->toJpeg(75));
            $skb->foto_skb = $path;
        } elseif (!$skb->exists) {
            return back()->withErrors(['foto_skb' => 'Foto SKB wajib diunggah.']);
        }

        $skb->save();

        return back()->with('success', 'Data persetujuan SKB berhasil disimpan.');
    }

    public function submitData(Request $request)
    {
        if (!auth()->check()) {
            return back()->withErrors(['error' => 'Sesi tidak valid. Harap login kembali.']);
        }

        $request->validate([
            'customer_code' => 'required|string',
            'no_hp' => 'nullable|string',
            'nama_pemilik_toko' => 'nullable|string',
            'nik_ktp' => 'nullable|string',
            'nama_ktp' => 'nullable|string',
            'nama_bank' => 'nullable|string',
            'no_rekening' => 'nullable|string',
            'nama_pemilik_norek' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'foto_ktp' => 'nullable|image',
            'foto_toko2' => 'nullable|image',
            'foto_toko3' => 'nullable|image',
        ]);

        $outlet = \App\Models\RewardOutlet::firstOrNew(['customer_code' => $request->customer_code]);
        
        foreach (['no_hp', 'nama_pemilik_toko', 'nik_ktp', 'nama_ktp', 'nama_bank', 'no_rekening', 'nama_pemilik_norek', 'latitude', 'longitude'] as $field) {
            if (empty($outlet->$field) && $request->filled($field)) {
                $outlet->$field = $request->$field;
            }
        }

        $manager = new ImageManager(new Driver());

        if (empty($outlet->foto_ktp) && $request->hasFile('foto_ktp')) {
            $image = $manager->read($request->file('foto_ktp')->getRealPath());
            $image->scaleDown(width: 1024);
            $path = 'outlet_photos/' . uniqid() . '.jpg';
            Storage::disk('public')->put($path, (string) $image->toJpeg(75));
            $outlet->foto_ktp = $path;
        }
        if (empty($outlet->foto_toko2) && $request->hasFile('foto_toko2')) {
            $image = $manager->read($request->file('foto_toko2')->getRealPath());
            $image->scaleDown(width: 1024);
            $path = 'outlet_photos/' . uniqid() . '.jpg';
            Storage::disk('public')->put($path, (string) $image->toJpeg(75));
            $outlet->foto_toko2 = $path;
        }
        if (empty($outlet->foto_toko3) && $request->hasFile('foto_toko3')) {
            $image = $manager->read($request->file('foto_toko3')->getRealPath());
            $image->scaleDown(width: 1024);
            $path = 'outlet_photos/' . uniqid() . '.jpg';
            Storage::disk('public')->put($path, (string) $image->toJpeg(75));
            $outlet->foto_toko3 = $path;
        }

        $outlet->save();

        return back()->with('success', 'Data toko berhasil disimpan.');
    }
    public function getHistoryOrder(Request $request, $customer_code)
    {
        $kuartal = $request->input('kuartal', ceil(date('n') / 3));
        $currentYear = date('Y');
        
        $history = DB::table('rpt_visit_an_h as rvah')
            ->leftJoin('customer_map_eska as cme', function($join) {
                $join->on('cme.branch', '=', 'rvah.BID')
                     ->on('cme.custno', '=', 'rvah.CUSTNO');
            })
            ->where('rvah.RID', '<>', 'HOINA')
            ->where('rvah.FLAG_BUY', 'Y')
            ->where(DB::raw("SUBSTRING(cme.branch FROM 3 FOR 3)||'-'||cme.custno_dist"), '=', $customer_code)
            ->whereRaw('EXTRACT(QUARTER FROM rvah."TANGGAL"::date) = ?', [$kuartal])
            ->whereRaw('EXTRACT(YEAR FROM rvah."TANGGAL"::date) = ?', [$currentYear])
            ->select(
                DB::raw('rvah."TANGGAL"::date as tanggal'),
                'rvah.ORDER_VAL as value_order'
            )
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($history);
    }

    public function getHistoryProduk(Request $request)
    {
        $kd_dist = $request->input('kd_dist');
        $uniq_kd = $request->input('uniq_kd');

        // Generate last 6 months headers
        $months = [];
        $headers = [];
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        for ($i = 1; $i <= 6; $i++) {
            $m = $currentMonth - $i;
            $y = $currentYear;
            if ($m <= 0) {
                $m += 12;
                $y -= 1;
            }
            $monthKey = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $months[] = $monthKey; // e.g. "2026-07"
            
            // Format to something like "Jul"
            $monthName = date('M', mktime(0, 0, 0, $m, 1, $y));
            $headers[] = $monthName . ' ' . substr($y, 2);
        }

        $query = DB::table('t_sellingout_y2026')
            ->where('KDDIST', $kd_dist)
            ->where('KDUNIQ', $uniq_kd)
            ->select(
                DB::raw("DATE_TRUNC('month', TO_DATE(\"THN\" || '-' || \"BLN\" || '-01', 'YYYY-MM-DD'))::date AS bulan"),
                'NAMAITEMPRC as produk_subbrand',
                DB::raw('SUM("TTL_QTY_KTN") as qty')
            )
            ->groupBy(
                DB::raw("DATE_TRUNC('month', TO_DATE(\"THN\" || '-' || \"BLN\" || '-01', 'YYYY-MM-DD'))::date"),
                'NAMAITEMPRC'
            )
            ->get();

        $produkStats = [];
        foreach ($query as $row) {
            $subbrand = $row->produk_subbrand;
            $rowMonth = date('Y-m', strtotime($row->bulan)); // "2026-07"
            
            if (!isset($produkStats[$subbrand])) {
                $produkStats[$subbrand] = [
                    'produk_subbrand' => $subbrand,
                    'max_qty' => 0,
                    'sum_qty' => 0,
                    'count_months' => 0,
                    'history' => []
                ];
                // Initialize 6 months with 0
                foreach ($months as $mKey) {
                    $produkStats[$subbrand]['history'][$mKey] = 0;
                }
            }
            
            // Only update history if it falls in the last 6 months
            if (in_array($rowMonth, $months)) {
                $produkStats[$subbrand]['history'][$rowMonth] = $row->qty;
            }
            
            // Max and Avg could be based on ALL history in 2026, or just the last 6 months.
            // Usually, "max" and "avg" in this context applies to all available history data.
            if ($row->qty > $produkStats[$subbrand]['max_qty']) {
                $produkStats[$subbrand]['max_qty'] = $row->qty;
            }
            $produkStats[$subbrand]['sum_qty'] += $row->qty;
            $produkStats[$subbrand]['count_months']++;
        }

        $resultData = [];
        foreach ($produkStats as $stat) {
            $monthlyQty = [];
            foreach ($months as $mKey) {
                $monthlyQty[] = $stat['history'][$mKey];
            }
            
            $resultData[] = [
                'produk_subbrand' => $stat['produk_subbrand'],
                'max_qty' => $stat['max_qty'],
                'avg_qty' => $stat['count_months'] > 0 ? $stat['sum_qty'] / $stat['count_months'] : 0,
                'monthly_qty' => $monthlyQty
            ];
        }

        // Sort alphabetically by subbrand just to be clean
        usort($resultData, function($a, $b) {
            return strcmp($a['produk_subbrand'], $b['produk_subbrand']);
        });

        return response()->json([
            'headers' => $headers,
            'data' => $resultData
        ]);
    }
}
