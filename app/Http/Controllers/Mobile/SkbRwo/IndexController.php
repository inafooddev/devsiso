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
        $sessionSupervisorCode = $request->session()->get('sessionSupervisorCode');
        $sessionSupervisorName = $request->session()->get('sessionSupervisorName');

        $listPotensi = [];
        $listSkb = [];
        $listPlan = [];

        if ($sessionSupervisorCode) {
            $currentQuarter = ceil(date('n') / 3);

            // 1. Data List Potensi RWO
            $listPotensi = DB::table('list_potensi_rwo as l')
                ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'l.distributor_code')
                ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
                ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
                ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                    $join->on('skb.customer_code', '=', 'l.customer_code')
                         ->on('skb.distributor_code', '=', 'l.distributor_code')
                         ->on('skb.kuartal', '=', 'l.kuartal');
                })
                ->where('te.team_elite_code', $sessionSupervisorCode)
                ->where('l.kuartal', $currentQuarter)
                ->select(
                    'l.*', 
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
                ->distinct()
                ->get();

            // 2. Data SKB
            $listSkb = DB::table('surat_kesepakatan_bersama_rwo as skb')
                ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'skb.distributor_code')
                ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
                ->leftJoin('list_potensi_rwo as l', function($join) {
                    $join->on('l.customer_code', '=', 'skb.customer_code')
                         ->on('l.distributor_code', '=', 'skb.distributor_code');
                })
                ->where('te.team_elite_code', $sessionSupervisorCode)
                ->where('skb.kuartal', $currentQuarter)
                ->select('skb.*', 'l.customer_name', 'l.alamat as address')
                ->distinct()
                ->get();

            // 3. Data Plan Kunjungan (jks_team_elite)
            try {
                 $listPlan = DB::table('jks_team_elite as j')
                    ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                        $join->on('l.distributor_code', '=', 'j.distributor_code')
                             ->on('l.customer_code_prc', '=', 'j.custno');
                    })
                    ->leftJoin('reward_outlet as r', 'r.eskalink_code', '=', 'j.custno')
                    ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) use ($currentQuarter) {
                        $join->on('skb.customer_code', '=', 'j.custno')
                             ->on('skb.distributor_code', '=', 'j.distributor_code')
                             ->on('skb.kuartal', '=', DB::raw($currentQuarter));
                    })
                    ->where('l.pilar', '1. RWO')
                    ->where('j.kode_team', $sessionSupervisorCode)
                    ->select(
                        'j.tanggal',
                        'j.distributor_code',
                        'j.custno as customer_code',
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
                    )
                    ->get();
            } catch (\Exception $e) {
                 // Fallback jika terjadi error query
                 $listPlan = [];
            }
        }

        return Inertia::render('Mobile/SkbRwo/Index', [
            'listPotensi' => $listPotensi,
            'listSkb' => $listSkb,
            'listPlan' => $listPlan,
            'sessionSupervisorCode' => $sessionSupervisorCode,
            'sessionSupervisorName' => $sessionSupervisorName,
        ]);
    }

    public function loginSupervisor(Request $request)
    {
        $request->validate([
            'supervisor_code' => 'required|string'
        ]);

        $code = strtoupper(trim($request->supervisor_code));

        $sales = DB::table('team_elite_code_mappings as t')
            ->selectRaw('max(f."SLSNAME") as sales_name')
            ->leftJoin('master_distributors as md', 'md.supervisor_code', '=', 't.siso_code')
            ->leftJoin('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
            ->where('md.is_active', true)
            ->where(DB::raw('LOWER(t.team_elite_code)'), strtolower($code))
            ->first();

        if (!$sales || !$sales->sales_name) {
            $exists = DB::table('team_elite_code_mappings as t')
                ->where(DB::raw('LOWER(t.team_elite_code)'), strtolower($code))
                ->exists();
                
            if (!$exists) {
                return back()->withErrors(['supervisor_code' => 'Kode tim elite tidak ditemukan atau tidak aktif.']);
            }
        }

        $request->session()->put('sessionSupervisorCode', $code);
        $request->session()->put('sessionSupervisorName', $sales?->sales_name ?? $code);

        return back();
    }

    public function logoutSupervisor(Request $request)
    {
        $request->session()->forget(['sessionSupervisorCode', 'sessionSupervisorName']);
        return back();
    }

    public function submitSkb(Request $request)
    {
        $sessionSupervisorCode = $request->session()->get('sessionSupervisorCode');
        if (!$sessionSupervisorCode) {
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
        $sessionSupervisorCode = $request->session()->get('sessionSupervisorCode');
        if (!$sessionSupervisorCode) {
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
