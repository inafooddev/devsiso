<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Traits\EnforcesMenuPermissions;

class PlanKunjungan extends Component
{
    use WithPagination;
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';

    public $dateStart = '';
    public $dateEnd = '';
    public $search = '';
    public $statusFilter = ''; // '', 'lengkap', 'belum'
    public $skbFilter = ''; // '', 'sudah', 'approve', 'pending', 'reject', 'belum', 'non_skb'
    public $selectedRegions = [];
    public $selectedAreas = [];
    public $selectedTeams = [];

    public function mount()
    {
        $this->dateStart = date('Y-m-d');
        $this->dateEnd = date('Y-m-d');
    }

    // Reset pagination when filters change
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['dateStart', 'dateEnd'])) {
            if (!empty($this->dateStart) && !empty($this->dateEnd)) {
                $start = \Carbon\Carbon::parse($this->dateStart);
                $end = \Carbon\Carbon::parse($this->dateEnd);

                // Max range 7 days (1 week)
                if ($start->diffInDays($end, false) > 7) {
                    if ($propertyName === 'dateStart') {
                        $this->dateEnd = $start->copy()->addDays(7)->format('Y-m-d');
                    } else {
                        $this->dateStart = $end->copy()->subDays(7)->format('Y-m-d');
                    }
                } elseif ($start->diffInDays($end, false) < 0) {
                    $this->dateEnd = $this->dateStart;
                }
            }
        }

        if (in_array($propertyName, [
            'dateStart',
            'dateEnd',
            'search',
            'statusFilter',
            'skbFilter',
            'selectedRegions',
            'selectedAreas',
            'selectedTeams',
        ])) {
            $this->resetPage();
        }
    }

    public function updatedSelectedRegions()
    {
        $regions = $this->selectedRegions;

        if (!is_array($regions)) {
            $regions = $regions ? [$regions] : [];
        }

        if (!empty($regions)) {
            $validAreas = $this->getAreaOptions()->toArray();
            $this->selectedAreas = array_values(array_intersect($this->selectedAreas, $validAreas));

            $validTeams = $this->getTeamOptions()->toArray();
            $this->selectedTeams = array_values(array_intersect($this->selectedTeams, $validTeams));
        } else {
            $this->selectedAreas = [];
            $this->selectedTeams = [];
        }
    }

    public function updatedSelectedAreas()
    {
        $areas = $this->selectedAreas;

        if (!is_array($areas)) {
            $areas = $areas ? [$areas] : [];
        }

        if (!empty($areas)) {
            $validTeams = $this->getTeamOptions()->toArray();
            $this->selectedTeams = array_values(array_intersect($this->selectedTeams, $validTeams));
        } else {
            $this->selectedTeams = [];
        }
    }

    public function toggleStatusFilter(string $value): void
    {
        $this->statusFilter = ($this->statusFilter === $value) ? '' : $value;
        $this->resetPage();
    }

    public function toggleSkbFilter(string $value): void
    {
        $this->skbFilter = ($this->skbFilter === $value) ? '' : $value;
        $this->resetPage();
    }

    public function clearQuickFilters(): void
    {
        $this->statusFilter = '';
        $this->skbFilter = '';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->dateStart = date('Y-m-d');
        $this->dateEnd = date('Y-m-d');
        $this->search = '';
        $this->statusFilter = '';
        $this->skbFilter = '';
        $this->selectedRegions = [];
        $this->selectedAreas = [];
        $this->selectedTeams = [];
        $this->resetPage();
    }

    public static function applyAccessScope($query, $alias = '')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        $prefix = $alias ? $alias . '.' : '';

        if (!empty($user->supervisor_code)) {
            $query->where($prefix . 'kode_team', $user->supervisor_code);
        } elseif (!empty($user->area_code)) {
            $query->whereIn($prefix . 'kode_area', (array) $user->area_code);
        } elseif (!empty($user->region_code)) {
            $regions = array_values(array_diff((array) $user->region_code, ['HOINA']));
            if (!empty($regions)) {
                $query->whereIn($prefix . 'kode_region', $regions);
            }
        }

        return $query;
    }

    private function getUserScopeCacheKey(): string
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return 'admin';
        }
        return 'u_' . $user->id;
    }

    public function getRegionOptions()
    {
        $cacheKey = 'rwo_plan_regions_' . $this->getUserScopeCacheKey();

        return Cache::remember($cacheKey, 600, function () {
            $query = DB::table('jks_team_elite')
                ->select('nama_region')
                ->whereNotNull('nama_region')
                ->where('nama_region', '!=', '');

            self::applyAccessScope($query);

            return $query->distinct()->orderBy('nama_region')->pluck('nama_region');
        });
    }

    public function getAreaOptions()
    {
        $regionsHash = md5(json_encode($this->selectedRegions));
        $cacheKey = 'rwo_plan_areas_' . $this->getUserScopeCacheKey() . '_' . $regionsHash;

        return Cache::remember($cacheKey, 600, function () {
            $query = DB::table('jks_team_elite')
                ->select('nama_area')
                ->whereNotNull('nama_area')
                ->where('nama_area', '!=', '');

            self::applyAccessScope($query);

            if (!empty($this->selectedRegions)) {
                $query->whereIn('nama_region', $this->selectedRegions);
            }

            return $query->distinct()->orderBy('nama_area')->pluck('nama_area');
        });
    }

    public function getTeamOptions()
    {
        $filterHash = md5(json_encode([$this->selectedRegions, $this->selectedAreas]));
        $cacheKey = 'rwo_plan_teams_' . $this->getUserScopeCacheKey() . '_' . $filterHash;

        return Cache::remember($cacheKey, 600, function () {
            $query = DB::table('jks_team_elite')
                ->select('nama_team')
                ->whereNotNull('nama_team')
                ->where('nama_team', '!=', '');

            self::applyAccessScope($query);

            if (!empty($this->selectedRegions)) {
                $query->whereIn('nama_region', $this->selectedRegions);
            }
            if (!empty($this->selectedAreas)) {
                $query->whereIn('nama_area', $this->selectedAreas);
            }

            return $query->distinct()->orderBy('nama_team')->pluck('nama_team');
        });
    }

    public static function getDataCompleteSql(): string
    {
        return "(
            NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
            NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
            NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
            NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
            NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
            NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
        )";
    }

    /**
     * Build base query joining jks_team_elite -> list_toko_pareto_team_elite (pilar 1. RWO)
     * with dual-fallback uniq_kd resolution (pareto uniq_kd + reward_outlet eskalink_code),
     * then joining reward_outlet, list_potensi_rwo, and surat_kesepakatan_bersama_rwo on uniq_kd.
     */
    public static function buildBaseQueryFromFilters(array $filters)
    {
        // 1. Pareto RWO bridge subquery (1-to-1 per distributor_code + customer_code_prc, resolves uniq_kd 100%)
        $eskalinkBridge = DB::table('reward_outlet')
            ->selectRaw('DISTINCT ON (eskalink_code) eskalink_code, customer_code')
            ->whereNotNull('eskalink_code')
            ->where('eskalink_code', '!=', '')
            ->orderBy('eskalink_code')
            ->orderBy('id', 'desc');

        $paretoSubquery = DB::table('list_toko_pareto_team_elite as lp')
            ->leftJoinSub($eskalinkBridge, 'r_esk', function ($join) {
                $join->on('r_esk.eskalink_code', '=', 'lp.customer_code_prc');
            })
            ->selectRaw("DISTINCT ON (lp.distributor_code, lp.customer_code_prc) lp.distributor_code, lp.customer_code_prc, COALESCE(NULLIF(TRIM(lp.uniq_kd), ''), r_esk.customer_code) AS uniq_kd")
            ->where('lp.pilar', '1. RWO')
            ->orderBy('lp.distributor_code')
            ->orderBy('lp.customer_code_prc');

        // 2. Master Reward Outlet subquery (keyed by customer_code / uniq_kd)
        $rewardSubquery = DB::table('reward_outlet')
            ->selectRaw('DISTINCT ON (customer_code) customer_code, no_hp, nama_pemilik_toko, latitude, longitude, foto_toko2, foto_toko3')
            ->whereNotNull('customer_code')
            ->where('customer_code', '!=', '')
            ->orderBy('customer_code')
            ->orderByRaw('updated_at DESC NULLS LAST')
            ->orderBy('id', 'desc');

        // 3. Potensi RWO subquery (for jenis_reward CB/BB per customer_code + kuartal)
        $potensiSubquery = DB::table('list_potensi_rwo')
            ->selectRaw('DISTINCT ON (customer_code, kuartal) customer_code, kuartal, jenis_reward')
            ->whereNotNull('customer_code')
            ->orderBy('customer_code')
            ->orderBy('kuartal');

        // 4. SKB RWO subquery (bridged via l.uniq_kd + kuartal of j.tanggal)
        $skbSubquery = DB::table('surat_kesepakatan_bersama_rwo')
            ->selectRaw('DISTINCT ON (customer_code, kuartal) customer_code, kuartal, is_approved, foto_skb, reason')
            ->whereNotNull('customer_code')
            ->orderBy('customer_code')
            ->orderBy('kuartal')
            ->orderBy('id', 'desc');

        $query = DB::table('jks_team_elite as j')
            ->joinSub($paretoSubquery, 'l', function ($join) {
                $join->on('l.distributor_code', '=', 'j.distributor_code')
                     ->on('l.customer_code_prc', '=', 'j.custno');
            })
            ->leftJoinSub($rewardSubquery, 'r', function ($join) {
                $join->on('r.customer_code', '=', 'l.uniq_kd');
            })
            ->leftJoinSub($potensiSubquery, 'p', function ($join) {
                $join->on('p.customer_code', '=', 'l.uniq_kd')
                     ->whereRaw('p.kuartal = EXTRACT(QUARTER FROM j.tanggal)::int');
            })
            ->leftJoinSub($skbSubquery, 'skb', function ($join) {
                $join->on('skb.customer_code', '=', 'l.uniq_kd')
                     ->whereRaw('skb.kuartal = EXTRACT(QUARTER FROM j.tanggal)::int');
            });

        self::applyAccessScope($query, 'j');

        $dateStart = $filters['dateStart'] ?? '';
        $dateEnd = $filters['dateEnd'] ?? '';

        if (!empty($dateStart) && !empty($dateEnd)) {
            $query->whereBetween('j.tanggal', [$dateStart, $dateEnd]);
        } elseif (!empty($dateStart)) {
            $query->where('j.tanggal', '>=', $dateStart);
        } elseif (!empty($dateEnd)) {
            $query->where('j.tanggal', '<=', $dateEnd);
        }

        if (!empty($filters['selectedRegions'])) {
            $query->whereIn('j.nama_region', $filters['selectedRegions']);
        }
        if (!empty($filters['selectedAreas'])) {
            $query->whereIn('j.nama_area', $filters['selectedAreas']);
        }
        if (!empty($filters['selectedTeams'])) {
            $query->whereIn('j.nama_team', $filters['selectedTeams']);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('j.custno', 'ILIKE', $term)
                  ->orWhere('l.uniq_kd', 'ILIKE', $term)
                  ->orWhere('j.custname', 'ILIKE', $term)
                  ->orWhere('j.nama_team', 'ILIKE', $term)
                  ->orWhere('j.addres', 'ILIKE', $term)
                  ->orWhere('j.nama_area', 'ILIKE', $term);
            });
        }

        return $query;
    }

    public static function applyQuickFilters($query, array $filters)
    {
        $completeSql = self::getDataCompleteSql();
        $statusFilter = $filters['statusFilter'] ?? '';
        $skbFilter = $filters['skbFilter'] ?? '';

        if ($statusFilter === 'lengkap') {
            $query->whereRaw($completeSql);
        } elseif ($statusFilter === 'belum') {
            $query->whereRaw("NOT $completeSql");
        }

        if ($skbFilter === 'sudah') {
            $query->whereRaw("COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL");
        } elseif ($skbFilter === 'approve') {
            $query->whereRaw("COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL AND skb.is_approved = true");
        } elseif ($skbFilter === 'pending') {
            $query->whereRaw("COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL AND skb.is_approved IS NULL");
        } elseif ($skbFilter === 'reject') {
            $query->whereRaw("COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL AND skb.is_approved = false");
        } elseif ($skbFilter === 'belum') {
            $query->whereRaw("COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NULL");
        } elseif ($skbFilter === 'non_skb') {
            $query->whereRaw("COALESCE(p.jenis_reward, 'CB') = 'BB'");
        }

        return $query;
    }

    private function getCurrentFilters(): array
    {
        return [
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'skbFilter' => $this->skbFilter,
            'selectedRegions' => $this->selectedRegions,
            'selectedAreas' => $this->selectedAreas,
            'selectedTeams' => $this->selectedTeams,
        ];
    }

    public function getSummaryMetrics(): array
    {
        $completeSql = self::getDataCompleteSql();
        $query = self::buildBaseQueryFromFilters($this->getCurrentFilters());

        $row = $query->selectRaw("
            COUNT(*) as total_plan,
            COUNT(CASE WHEN $completeSql THEN 1 END) as total_lengkap,
            COUNT(CASE WHEN NOT $completeSql THEN 1 END) as total_belum,
            COUNT(CASE WHEN COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL THEN 1 END) as sudah_skb,
            COUNT(CASE WHEN COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL AND skb.is_approved = true THEN 1 END) as skb_approve,
            COUNT(CASE WHEN COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL AND skb.is_approved IS NULL THEN 1 END) as skb_pending,
            COUNT(CASE WHEN COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NOT NULL AND skb.is_approved = false THEN 1 END) as skb_reject,
            COUNT(CASE WHEN COALESCE(p.jenis_reward, 'CB') != 'BB' AND skb.customer_code IS NULL THEN 1 END) as belum_skb
        ")->first();

        $total = (int) ($row->total_plan ?? 0);
        $lengkap = (int) ($row->total_lengkap ?? 0);
        $belum = (int) ($row->total_belum ?? 0);
        $sudahSkb = (int) ($row->sudah_skb ?? 0);
        $belumSkb = (int) ($row->belum_skb ?? 0);

        return [
            'total' => $total,
            'lengkap' => $lengkap,
            'belum' => $belum,
            'pct_lengkap' => $total > 0 ? round(($lengkap / $total) * 100, 1) : 0,
            'sudah_skb' => $sudahSkb,
            'skb_approve' => (int) ($row->skb_approve ?? 0),
            'skb_pending' => (int) ($row->skb_pending ?? 0),
            'skb_reject' => (int) ($row->skb_reject ?? 0),
            'belum_skb' => $belumSkb,
        ];
    }

    public function getDataProperty()
    {
        $filters = $this->getCurrentFilters();
        $query = self::buildBaseQueryFromFilters($filters);
        self::applyQuickFilters($query, $filters);

        $query->select(
            'j.tanggal',
            'j.kode_region',
            'j.nama_region',
            'j.kode_area',
            'j.nama_area',
            'j.kode_team',
            'j.nama_team',
            'j.distributor_code',
            'j.distributor_name',
            'j.custno',
            'l.uniq_kd',
            'j.custname',
            'j.addres',
            'p.jenis_reward',
            'skb.customer_code as skb_customer_code',
            'skb.is_approved as skb_is_approved',
            'skb.foto_skb',
            'skb.reason as skb_reason',
            'r.no_hp',
            'r.nama_pemilik_toko',
            'r.latitude',
            'r.longitude',
            'r.foto_toko2 as tampak_depan',
            'r.foto_toko3 as tampak_dalam'
        );

        $query->orderBy('j.tanggal', 'desc')
              ->orderBy('j.nama_region', 'asc')
              ->orderBy('j.nama_area', 'asc')
              ->orderBy('j.nama_team', 'asc');

        return $query->paginate(100);
    }

    public function export()
    {
        \App\Helpers\ActivityLogger::log('Export Plan Kunjungan', "Mengekspor data Plan Kunjungan RWO.");
        $filename = 'plan_kunjungan_export_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PlanKunjunganExport($this->getCurrentFilters()),
            $filename
        );
    }

    public function render()
    {
        return view('livewire.rwo.plan-kunjungan', [
            'regionOptions' => $this->getRegionOptions(),
            'areaOptions' => $this->getAreaOptions(),
            'teamOptions' => $this->getTeamOptions(),
            'summary' => $this->getSummaryMetrics(),
            'records' => $this->data,
        ])->layout('layouts.app');
    }
}


