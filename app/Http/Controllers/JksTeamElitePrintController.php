<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JksTeamElite;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;

class JksTeamElitePrintController extends Controller
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'jks-team-elite.index';

    private function applyHierarchyAccess($query, $distributorCodeColumn = 'jks_team_elite.distributor_code')
    {
        $user = auth()->user();

        // Admin atau tidak ada batasan → tampil semua
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        $isJksQuery = str_contains($distributorCodeColumn, 'jks_team_elite.');

        // Level Supervisor
        if (!empty($user->supervisor_code)) {
            if ($isJksQuery) {
                return $query->where('jks_team_elite.kode_team', $user->supervisor_code);
            } else {
                $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
                    ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                    ->pluck('siso_code')
                    ->toArray();

                if (!empty($sisoCodes)) {
                    return $query->whereExists(function ($sub) use ($sisoCodes, $distributorCodeColumn) {
                        $sub->selectRaw('1')
                            ->from('master_distributors as md_auth')
                            ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                            ->whereIn('md_auth.supervisor_code', $sisoCodes);
                    });
                } else {
                    return $query->whereRaw('1 = 0');
                }
            }
        }

        // Level Area (Array)
        if (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
            if ($isJksQuery) {
                return $query->whereIn('jks_team_elite.kode_area', $user->area_code);
            } else {
                return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md_auth')
                        ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                        ->whereIn('md_auth.area_code', $user->area_code);
                });
            }
        }

        // Level Region (Array)
        if (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
            if ($isJksQuery) {
                return $query->whereIn('jks_team_elite.kode_region', $user->region_code);
            } else {
                return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md_auth')
                        ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                        ->whereIn('md_auth.region_code', $user->region_code);
                });
            }
        }

        // Jika user bukan admin tapi tidak punya akses apa-apa (sup/area/region kosong)
        return $query->whereRaw('1 = 0');
    }

    public function index(Request $request)
    {
        $this->authorizeAction('can_export');

        $filterTeam = $request->input('teams', []);
        $filterStartDate = $request->input('start_date');
        $filterEndDate = $request->input('end_date');
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'tanggal');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (empty($filterTeam) || empty($filterStartDate) || empty($filterEndDate)) {
            abort(400, 'Parameter tidak lengkap (Team dan Tanggal wajib diisi).');
        }

        $query = JksTeamElite::query()
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->select(
                'jks_team_elite.tanggal',
                'jks_team_elite.kode_region',
                'jks_team_elite.nama_region',
                'jks_team_elite.kode_area',
                'jks_team_elite.nama_area',
                'jks_team_elite.kode_team',
                'jks_team_elite.nama_team',
                'jks_team_elite.distributor_name',
                'jks_team_elite.custno',
                'jks_team_elite.custname',
                'l.pilar',
                'l.target'
            )
            ->whereIn('jks_team_elite.kode_team', $filterTeam)
            ->whereBetween('jks_team_elite.tanggal', [$filterStartDate, $filterEndDate])
            ->where(function($q) {
                $q->where('jks_team_elite.custno', 'not ilike', '%BRIF%')
                  ->where('jks_team_elite.custno', 'not ilike', '%BRIEF%')
                  ->where('jks_team_elite.custno', 'not ilike', '%EVAL%');
            });

        $this->applyHierarchyAccess($query, 'jks_team_elite.distributor_code');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('jks_team_elite.nama_region', 'ilike', '%' . $search . '%')
                  ->orWhere('jks_team_elite.kode_region', 'ilike', '%' . $search . '%')
                  ->orWhere('jks_team_elite.nama_team', 'ilike', '%' . $search . '%')
                  ->orWhere('jks_team_elite.kode_team', 'ilike', '%' . $search . '%')
                  ->orWhere('jks_team_elite.distributor_name', 'ilike', '%' . $search . '%')
                  ->orWhere('jks_team_elite.custname', 'ilike', '%' . $search . '%');
            });
        }

        $query->orderBy('jks_team_elite.nama_team', 'asc')
              ->orderBy('jks_team_elite.tanggal', 'asc')
              ->orderBy('l.pilar', 'asc')
              ->orderBy('jks_team_elite.distributor_name', 'asc')
              ->orderBy('jks_team_elite.custname', 'asc');

        $records = $query->get();
        $groupedRecords = $records->groupBy('kode_team');

        return view('jks-team-elite.print', compact('groupedRecords', 'filterTeam', 'filterStartDate', 'filterEndDate'));
    }
}
