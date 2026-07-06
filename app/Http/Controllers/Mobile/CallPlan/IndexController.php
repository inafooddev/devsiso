<?php

namespace App\Http\Controllers\Mobile\CallPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $sessionSupervisorCode = $request->session()->get('sessionCallPlanSupervisorCode');
        $sessionSupervisorName = $request->session()->get('sessionCallPlanSupervisorName');

        $listPlan = [];

        if ($sessionSupervisorCode) {
            try {
                 $listPlan = DB::table('jks_team_elite as j')
                    ->where('j.kode_team', $sessionSupervisorCode)
                    ->select(
                        'j.tanggal',
                        'j.distributor_code',
                        'j.custno as customer_code',
                        'j.custname as customer_name',
                        'j.addres as address'
                    )
                    ->get();
            } catch (\Exception $e) {
                 $listPlan = [];
            }
        }

        return Inertia::render('Mobile/CallPlan/Index', [
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
            return back()->withErrors([
                'supervisor_code' => 'Kode SPV tidak ditemukan atau tidak aktif!'
            ]);
        }

        $request->session()->put('sessionCallPlanSupervisorCode', $code);
        $request->session()->put('sessionCallPlanSupervisorName', $sales->sales_name);

        return back()->with('success', 'Berhasil login!');
    }

    public function logoutSupervisor(Request $request)
    {
        $request->session()->forget('sessionCallPlanSupervisorCode');
        $request->session()->forget('sessionCallPlanSupervisorName');
        return back();
    }
}
