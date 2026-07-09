<?php

namespace App\Http\Controllers\Mobile\SkbRwo;

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



        // 3. Data Plan Kunjungan (jks_team_elite)
        $queryPlan = DB::table('jks_team_elite as j')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('l.distributor_code', '=', 'j.distributor_code')
                     ->on('l.customer_code_prc', '=', 'j.custno');
            })
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'j.distributor_code')
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
            ->where('l.pilar', '1. RWO')
            ->whereRaw('UPPER(j.kode_team) = ?', [strtoupper($user->userid)])
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
                DB::raw("$currentQuarter as kuartal")
            );

        // ==== APLIKASI HAK AKSES (FILTERING) ====
        if ($user->supervisor_code) {
            // Level SPV: Potensi filter dengan supervisor_code (bisa fallback)
            $queryPotensi->where(function($q) use ($user) {
                $q->where('te.team_elite_code', $user->supervisor_code)
                  ->orWhere('md.supervisor_code', $user->supervisor_code);
            });
        } elseif ($user->area_code) {
            // Level Area Manager
            $queryPotensi->where('md.area_code', $user->area_code);
        } elseif ($user->region_code) {
            // Level Region Manager (JSON Array of Region Codes)
            $regions = is_string($user->region_code) ? json_decode($user->region_code, true) : $user->region_code;
            $regions = $regions ?? [];
            
            if (!in_array('HOINA', $regions)) {
                $queryPotensi->whereIn('md.region_code', $regions);
            }
            // Jika ada 'HOINA', maka ia bisa melihat semuanya secara nasional.
        }

        $listPotensi = $queryPotensi->get();
        
        try {
            $listPlan = $queryPlan->get();
        } catch (\Exception $e) {
            $listPlan = [];
        }

        return Inertia::render('mobile/Pages/SkbRwo/Index', [
            'listPotensi' => $listPotensi,
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
            'foto_skb' => 'nullable|image|max:2048',
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
            $path = $request->file('foto_skb')->store('skb_photos', 'public');
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
            'foto_ktp' => 'nullable|image|max:2048',
            'foto_toko2' => 'nullable|image|max:2048',
            'foto_toko3' => 'nullable|image|max:2048',
        ]);

        $outlet = \App\Models\RewardOutlet::firstOrNew(['customer_code' => $request->customer_code]);
        
        foreach (['no_hp', 'nama_pemilik_toko', 'nik_ktp', 'nama_ktp', 'nama_bank', 'no_rekening', 'nama_pemilik_norek', 'latitude', 'longitude'] as $field) {
            if (empty($outlet->$field) && $request->filled($field)) {
                $outlet->$field = $request->$field;
            }
        }

        if (empty($outlet->foto_ktp) && $request->hasFile('foto_ktp')) {
            $outlet->foto_ktp = $request->file('foto_ktp')->store('outlet_photos', 'public');
        }
        if (empty($outlet->foto_toko2) && $request->hasFile('foto_toko2')) {
            $outlet->foto_toko2 = $request->file('foto_toko2')->store('outlet_photos', 'public');
        }
        if (empty($outlet->foto_toko3) && $request->hasFile('foto_toko3')) {
            $outlet->foto_toko3 = $request->file('foto_toko3')->store('outlet_photos', 'public');
        }

        $outlet->save();

        return back()->with('success', 'Data toko berhasil disimpan.');
    }
}
