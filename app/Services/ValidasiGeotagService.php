<?php

namespace App\Services;

use App\Models\User;
use App\Models\ValidasiGeotag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ValidasiGeotagService
{
    /**
     * Dapatkan daftar Region yang dapat diakses oleh user.
     */
    public function getAccessibleRegions(?User $user = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return [];
        }

        $query = DB::table('distributor_implementasi_eskalink as die')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->where('md.is_active', true)
            ->whereNotNull('die.eskalink_code')
            ->where('die.eskalink_code', '!=', '')
            ->whereNotNull('md.region_code')
            ->where('md.region_code', '!=', 'HOINA');

        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';

        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            $query->where('te.team_elite_code', $user->supervisor_code);
        } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
            $query->whereIn('md.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'region' && !empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('md.region_code', $regions);
            }
        }

        return $query->select('md.region_code', 'md.region_name')
            ->distinct()
            ->orderBy('md.region_name')
            ->pluck('md.region_name', 'md.region_code')
            ->filter()
            ->toArray();
    }

    /**
     * Dapatkan daftar Area yang dapat diakses oleh user, difilter berdasarkan Region (bila ada).
     */
    public function getAccessibleAreas(?User $user = null, ?string $regionCode = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return [];
        }

        $query = DB::table('distributor_implementasi_eskalink as die')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->where('md.is_active', true)
            ->whereNotNull('die.eskalink_code')
            ->where('die.eskalink_code', '!=', '')
            ->whereNotNull('md.area_code')
            ->where('md.region_code', '!=', 'HOINA')
            ->where('md.area_code', '!=', 'HOINA');

        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';

        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            $query->where('te.team_elite_code', $user->supervisor_code);
        } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
            $query->whereIn('md.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'region' && !empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('md.region_code', $regions);
            }
        }

        if (!empty($regionCode)) {
            $query->where('md.region_code', $regionCode);
        }

        return $query->select('md.area_code', 'md.area_name')
            ->distinct()
            ->orderBy('md.area_name')
            ->pluck('md.area_name', 'md.area_code')
            ->filter()
            ->toArray();
    }

    /**
     * Dapatkan daftar Distributor yang dapat diakses oleh user, difilter berdasarkan Region & Area (bila ada).
     */
    public function getAccessibleDistributors(?User $user = null, ?string $regionCode = null, ?string $areaCode = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return [];
        }

        $query = DB::table('distributor_implementasi_eskalink as die')
            ->join('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->where('md.is_active', true)
            ->whereNotNull('die.eskalink_code')
            ->where('die.eskalink_code', '!=', '')
            ->where('md.region_code', '!=', 'HOINA')
            ->where('die.eskalink_code', '!=', 'HOINA');

        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';

        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            $query->where('te.team_elite_code', $user->supervisor_code);
        } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
            $query->whereIn('md.area_code', (array) $user->area_code);
        } elseif ($accessLevel === 'region' && !empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $query->whereIn('md.region_code', $regions);
            }
        }

        if (!empty($regionCode)) {
            $query->where('md.region_code', $regionCode);
        }
        if (!empty($areaCode)) {
            $query->where('md.area_code', $areaCode);
        }

        return $query->select('die.eskalink_code', 'die.distributor_name')
            ->distinct()
            ->orderBy('die.eskalink_code')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = $item->eskalink_code . ' - ' . ($item->distributor_name ?: 'Distributor');
                return [$item->eskalink_code => $label];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Dapatkan opsi filter (Region, Area, Distributor) yang dibatasi territory user dan cascading filter.
     */
    public function getFilterOptions(?User $user, ?string $regionCode = null, ?string $areaCode = null): array
    {
        return [
            'regions' => $this->getAccessibleRegions($user),
            'areas' => $this->getAccessibleAreas($user, $regionCode),
            'distributors' => $this->getAccessibleDistributors($user, $regionCode, $areaCode),
        ];
    }

    /**
     * Bangun Master CTE Query SQL dengan territory scope & filter.
     */
    private function buildMasterQuerySql(?User $user, array $filters, array &$bindings): string
    {
        $user = $user ?: auth()->user();
        $accessLevel = $user ? (method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional') : 'nasional';

        $baseRuteWhere = ["md.is_active IS TRUE", "md.region_code != 'HOINA'"];

        // 1. Territory Scope User
        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            $baseRuteWhere[] = "te.team_elite_code = :user_spv";
            $bindings['user_spv'] = $user->supervisor_code;
        } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
            $areas = (array) $user->area_code;
            $placeholders = [];
            foreach ($areas as $idx => $area) {
                $key = "user_area_{$idx}";
                $placeholders[] = ":{$key}";
                $bindings[$key] = $area;
            }
            if (!empty($placeholders)) {
                $baseRuteWhere[] = "md.area_code IN (" . implode(',', $placeholders) . ")";
            }
        } elseif ($accessLevel === 'region' && !empty($user->region_code)) {
            $regions = (array) $user->region_code;
            if (!in_array('HOINA', $regions)) {
                $placeholders = [];
                foreach ($regions as $idx => $reg) {
                    $key = "user_reg_{$idx}";
                    $placeholders[] = ":{$key}";
                    $bindings[$key] = $reg;
                }
                if (!empty($placeholders)) {
                    $baseRuteWhere[] = "md.region_code IN (" . implode(',', $placeholders) . ")";
                }
            }
        }

        // 2. Filter Dropdown UI
        if (!empty($filters['region'])) {
            $baseRuteWhere[] = "md.region_code = :filter_region";
            $bindings['filter_region'] = $filters['region'];
        }
        if (!empty($filters['area'])) {
            $baseRuteWhere[] = "md.area_code = :filter_area";
            $bindings['filter_area'] = $filters['area'];
        }
        if (!empty($filters['distributor'])) {
            $baseRuteWhere[] = "f.kodecabang = :filter_distributor";
            $bindings['filter_distributor'] = $filters['distributor'];
        }

        $baseRuteWhereClause = implode(" AND ", $baseRuteWhere);

        $sql = "
        WITH base_rute AS (
            SELECT DISTINCT
                f.kodecabang,
                f.custno
            FROM frute f
            JOIN distributor_implementasi_eskalink die 
                ON f.kodecabang = die.eskalink_code 
            JOIN master_distributors md 
                ON md.distributor_code = die.distributor_code 
            LEFT JOIN team_elite_code_mappings te 
                ON te.siso_code = md.supervisor_code
            WHERE {$baseRuteWhereClause}
        ),
        latest_customer AS (
            SELECT DISTINCT ON (cpe.kodecabang, cpe.custno)
                cpe.kodecabang,
                cpe.custno,
                cpe.custname,
                cpe.custadd1,
                cpe.ccity,
                cpe.typeout,
                cpe.grupout,
                NULLIF(TRIM(cpe.la), '') AS la_raw,
                NULLIF(TRIM(cpe.lg), '') AS lg_raw
            FROM customer_prc_eska cpe
            JOIN base_rute br ON br.kodecabang = cpe.kodecabang AND br.custno = cpe.custno
            ORDER BY cpe.kodecabang, cpe.custno, cpe.bln DESC NULLS LAST, cpe.id DESC
        ),
        parsed_customer AS (
            SELECT 
                lc.*,
                CASE 
                    WHEN lc.la_raw ~ '^-?[0-9]+(\.[0-9]+)?$' THEN lc.la_raw::numeric 
                    ELSE NULL 
                END AS la_clean,
                CASE 
                    WHEN lc.lg_raw ~ '^-?[0-9]+(\.[0-9]+)?$' THEN lc.lg_raw::numeric 
                    ELSE NULL 
                END AS lg_clean
            FROM latest_customer lc
        ),
        duplicate_counts AS (
            SELECT 
                kodecabang,
                ROUND(la_clean, 6) AS la_round,
                ROUND(lg_clean, 6) AS lg_round,
                COUNT(*) AS total_duplicate_stores
            FROM parsed_customer
            WHERE la_clean IS NOT NULL AND lg_clean IS NOT NULL
              AND NOT (la_clean = 0 AND lg_clean = 0)
              AND NOT (la_clean = 1 AND lg_clean = 1)
            GROUP BY kodecabang, ROUND(la_clean, 6), ROUND(lg_clean, 6)
            HAVING COUNT(*) > 1
        ),
        raw_compiled_stores AS (
            SELECT 
                b.kodecabang,
                b.custno,
                COALESCE(c.custname, '-') AS custname,
                COALESCE(c.custadd1, '-') AS custadd1,
                COALESCE(c.ccity, '-') AS ccity,
                c.typeout,
                c.grupout,
                c.la_clean AS la,
                c.lg_clean AS lg,
                md.latitude AS dist_latitude,
                md.longitude AS dist_longitude,
                md.distributor_name,
                md.branch_name,
                md.region_name,
                md.area_name,
                
                vg.id AS validation_id,
                vg.status AS validation_status,
                vg.catatan AS validation_notes,
                vg.la AS validated_la,
                vg.lg AS validated_lg,
                vg.validator_name,
                vg.validated_at,

                -- Kalkulasi Jarak Haversine ke Gudang Distributor (dalam Meter)
                CASE 
                    WHEN c.la_clean IS NOT NULL AND c.lg_clean IS NOT NULL 
                     AND md.latitude IS NOT NULL AND md.longitude IS NOT NULL THEN
                        ROUND((6371000 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(md.latitude)) * cos(radians(c.la_clean)) *
                                cos(radians(c.lg_clean) - radians(md.longitude)) +
                                sin(radians(md.latitude)) * sin(radians(c.la_clean))
                            ))
                        ))::numeric, 1)
                    ELSE NULL 
                END AS distance_to_dist_meters,

                -- Flag Anomali 1: Titik di Distributor (<= 100 Meter)
                CASE 
                    WHEN c.la_clean IS NOT NULL AND c.lg_clean IS NOT NULL 
                     AND md.latitude IS NOT NULL AND md.longitude IS NOT NULL
                     AND (6371000 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(md.latitude)) * cos(radians(c.la_clean)) *
                                cos(radians(c.lg_clean) - radians(md.longitude)) +
                                sin(radians(md.latitude)) * sin(radians(c.la_clean))
                            ))
                         )) <= 100 THEN true 
                    ELSE false 
                END AS is_at_distributor,

                -- Flag Anomali 2: Duplikasi Titik di Cabang Sama
                COALESCE(dup.total_duplicate_stores, 0) AS duplicate_count,

                -- Flag Anomali 3: Titik Berubah (Drift vs Titik Tervalidasi)
                CASE 
                    WHEN vg.status = 'VALID' 
                     AND (ROUND(vg.la, 6) != ROUND(c.la_clean, 6) OR ROUND(vg.lg, 6) != ROUND(c.lg_clean, 6)) THEN true 
                    ELSE false 
                END AS is_point_changed,

                -- Flag Anomali 4: Belum Ada Titik / Dummy Coordinate
                CASE 
                    WHEN c.la_clean IS NULL OR c.lg_clean IS NULL 
                      OR (c.la_clean = 0 AND c.lg_clean = 0)
                      OR (c.la_clean = 1 AND c.lg_clean = 1) THEN true 
                    ELSE false 
                END AS is_missing_coordinate

            FROM base_rute b
            LEFT JOIN parsed_customer c 
                ON c.kodecabang = b.kodecabang 
               AND c.custno = b.custno
            LEFT JOIN distributor_implementasi_eskalink die 
                ON die.eskalink_code = b.kodecabang
            LEFT JOIN master_distributors md 
                ON md.distributor_code = die.distributor_code
            LEFT JOIN duplicate_counts dup 
                ON dup.kodecabang = c.kodecabang 
               AND dup.la_round = ROUND(c.la_clean, 6) 
               AND dup.lg_round = ROUND(c.lg_clean, 6)
            LEFT JOIN validasi_geotag vg 
                ON vg.kodecabang = b.kodecabang 
               AND vg.custno = b.custno
        ),
        compiled_stores AS (
            SELECT 
                r.*,
                -- Prioritas Tunggal Anomali (Hierarchy: at_distributor > missing_coordinate > duplicate > point_changed > normal)
                CASE 
                    WHEN r.is_at_distributor IS TRUE THEN 'at_distributor'
                    WHEN r.is_missing_coordinate IS TRUE THEN 'missing_coordinate'
                    WHEN r.duplicate_count > 1 THEN 'duplicate'
                    WHEN r.is_point_changed IS TRUE THEN 'point_changed'
                    ELSE 'normal'
                END AS primary_anomaly
            FROM raw_compiled_stores r
        )
        ";

        return $sql;
    }

    /**
     * Dapatkan klausa WHERE luar untuk filtering tabel toko.
     */
    private function buildOuterWhere(array $filters, array &$bindings): string
    {
        $where = ["1 = 1"];

        if (!empty($filters['search'])) {
            $where[] = "(custname ILIKE :search OR custno ILIKE :search OR custadd1 ILIKE :search OR ccity ILIKE :search)";
            $bindings['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['validation_status']) && $filters['validation_status'] !== 'all') {
            if ($filters['validation_status'] === 'unvalidated') {
                $where[] = "validation_status IS NULL";
            } else {
                $where[] = "validation_status = :val_status";
                $bindings['val_status'] = $filters['validation_status'];
            }
        }

        if (!empty($filters['anomaly']) && $filters['anomaly'] !== 'all') {
            if ($filters['anomaly'] === 'no_anomaly' || $filters['anomaly'] === 'normal') {
                $where[] = "primary_anomaly = 'normal'";
            } elseif ($filters['anomaly'] === 'has_anomaly') {
                $where[] = "primary_anomaly != 'normal'";
            } elseif ($filters['anomaly'] === 'at_distributor') {
                $where[] = "primary_anomaly = 'at_distributor'";
            } elseif ($filters['anomaly'] === 'duplicate') {
                $where[] = "primary_anomaly = 'duplicate'";
            } elseif ($filters['anomaly'] === 'point_changed') {
                $where[] = "primary_anomaly = 'point_changed'";
            } elseif ($filters['anomaly'] === 'missing_coordinate') {
                $where[] = "primary_anomaly = 'missing_coordinate'";
            }
        }

        return implode(" AND ", $where);
    }

    /**
     * Dapatkan paginated daftar toko.
     */
    public function getPaginatedStores(?User $user, array $filters, int $perPage = 50, int $page = 1): LengthAwarePaginator
    {
        $bindings = [];
        $cteSql = $this->buildMasterQuerySql($user, $filters, $bindings);
        $outerWhere = $this->buildOuterWhere($filters, $bindings);

        // 1. Hitung total baris yang cocok dengan filter
        $countSql = "{$cteSql} SELECT COUNT(*) as total FROM compiled_stores WHERE {$outerWhere};";
        $totalResult = DB::select($countSql, $bindings);
        $total = $totalResult[0]->total ?? 0;

        // 2. Ambil data dengan paging
        $offset = ($page - 1) * $perPage;
        $dataBindings = $bindings;
        $dataBindings['limit'] = $perPage;
        $dataBindings['offset'] = $offset;

        $dataSql = "
            {$cteSql} 
            SELECT * FROM compiled_stores 
            WHERE {$outerWhere} 
            ORDER BY 
                CASE 
                    WHEN primary_anomaly = 'at_distributor' THEN 1
                    WHEN primary_anomaly = 'duplicate' THEN 2
                    WHEN primary_anomaly = 'point_changed' THEN 3
                    WHEN primary_anomaly = 'missing_coordinate' THEN 4
                    WHEN validation_status IS NULL THEN 5
                    ELSE 6
                END,
                kodecabang ASC, custname ASC
            LIMIT :limit OFFSET :offset;
        ";

        $rows = DB::select($dataSql, $dataBindings);

        return new LengthAwarePaginator($rows, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }

    /**
     * Dapatkan metrik ringkasan (4 Consolidated Summary Cards).
     */
    public function getSummaryMetrics(?User $user, array $filters): array
    {
        $bindings = [];
        // Untuk KPI card, kita hitung metrik populasi dalam territory scope & filter wilayah
        $cteSql = $this->buildMasterQuerySql($user, $filters, $bindings);

        $metricSql = "
        {$cteSql}
        SELECT
            COUNT(*) AS total_stores,
            COUNT(CASE WHEN validation_status IS NULL THEN 1 END) AS unvalidated_stores,
            COUNT(CASE WHEN validation_status = 'VALID' THEN 1 END) AS valid_stores,
            COUNT(CASE WHEN validation_status = 'TIDAK_VALID' THEN 1 END) AS invalid_stores,
            COUNT(CASE WHEN is_missing_coordinate IS FALSE THEN 1 END) AS has_coordinate,
            COUNT(CASE WHEN primary_anomaly = 'missing_coordinate' THEN 1 END) AS no_coordinate,
            COUNT(CASE WHEN primary_anomaly = 'at_distributor' THEN 1 END) AS at_distributor,
            COUNT(CASE WHEN primary_anomaly = 'duplicate' THEN 1 END) AS duplicate_stores,
            COUNT(CASE WHEN primary_anomaly = 'point_changed' THEN 1 END) AS drift_stores,
            COUNT(CASE WHEN primary_anomaly = 'normal' THEN 1 END) AS normal_stores
        FROM compiled_stores;
        ";

        $result = DB::selectOne($metricSql, $bindings);

        $total = $result->total_stores ?? 0;
        $unval = $result->unvalidated_stores ?? 0;
        $unvalPct = $total > 0 ? round(($unval / $total) * 100, 1) : 0;

        return [
            'total_stores' => $total,
            'unvalidated_stores' => $unval,
            'unvalidated_pct' => $unvalPct,
            'valid_stores' => $result->valid_stores ?? 0,
            'invalid_stores' => $result->invalid_stores ?? 0,
            'has_coordinate' => $result->has_coordinate ?? 0,
            'no_coordinate' => $result->no_coordinate ?? 0,
            'at_distributor' => $result->at_distributor ?? 0,
            'duplicate_stores' => $result->duplicate_stores ?? 0,
            'drift_stores' => $result->drift_stores ?? 0,
            'normal_stores' => $result->normal_stores ?? 0,
        ];
    }

    /**
     * Dapatkan matriks ringkasan (Summary) berjenjang per Distributor, Area, atau Region.
     */
    public function getSummaryMatrix(?User $user, array $filters = [], string $groupBy = 'distributor'): array
    {
        $bindings = [];
        $cteSql = $this->buildMasterQuerySql($user, $filters, $bindings);

        if ($groupBy === 'region') {
            $select = "
                region_name,
                NULL as area_name,
                NULL as kodecabang,
                NULL as distributor_name,
            ";
            $group = "region_name";
            $order = "region_name ASC";
        } elseif ($groupBy === 'area') {
            $select = "
                region_name,
                area_name,
                NULL as kodecabang,
                NULL as distributor_name,
            ";
            $group = "region_name, area_name";
            $order = "region_name ASC, area_name ASC";
        } else {
            // Default: per distributor
            $select = "
                region_name,
                area_name,
                kodecabang,
                distributor_name,
            ";
            $group = "region_name, area_name, kodecabang, distributor_name";
            $order = "region_name ASC, area_name ASC, kodecabang ASC";
        }

        $having = "";
        if (!empty($filters['search'])) {
            $bindings['search'] = '%' . trim($filters['search']) . '%';
            if ($groupBy === 'region') {
                $having = "HAVING region_name ILIKE :search";
            } elseif ($groupBy === 'area') {
                $having = "HAVING region_name ILIKE :search OR area_name ILIKE :search";
            } else {
                $having = "HAVING region_name ILIKE :search OR area_name ILIKE :search OR kodecabang ILIKE :search OR distributor_name ILIKE :search";
            }
        }

        $sql = "
            {$cteSql}
            SELECT 
                {$select}
                COUNT(*) AS total_stores,
                COUNT(CASE WHEN is_missing_coordinate IS FALSE THEN 1 END) AS has_coordinate,
                COUNT(CASE WHEN primary_anomaly = 'missing_coordinate' THEN 1 END) AS no_coordinate,
                COUNT(CASE WHEN primary_anomaly = 'at_distributor' THEN 1 END) AS at_distributor,
                COUNT(CASE WHEN primary_anomaly = 'duplicate' THEN 1 END) AS duplicate_stores,
                COUNT(CASE WHEN primary_anomaly = 'point_changed' THEN 1 END) AS drift_stores,
                COUNT(CASE WHEN primary_anomaly = 'normal' THEN 1 END) AS normal_stores,
                COUNT(CASE WHEN validation_status = 'VALID' THEN 1 END) AS valid_stores,
                COUNT(CASE WHEN validation_status = 'TIDAK_VALID' THEN 1 END) AS invalid_stores,
                COUNT(CASE WHEN validation_status IS NULL THEN 1 END) AS unvalidated_stores,
                ROUND(
                    (COUNT(CASE WHEN validation_status IS NOT NULL THEN 1 END)::numeric / NULLIF(COUNT(*), 0) * 100), 
                    1
                ) AS validation_progress_pct
            FROM compiled_stores
            GROUP BY {$group}
            {$having}
            ORDER BY {$order};
        ";

        return DB::select($sql, $bindings);
    }

    /**
     * Dapatkan data detail 1 toko untuk modal verifikasi.
     */
    public function getStoreDetail(?User $user, string $kodecabang, string $custno)
    {
        $filters = ['distributor' => trim($kodecabang)];
        $bindings = [];
        $cteSql = $this->buildMasterQuerySql($user, $filters, $bindings);

        $bindings['target_custno'] = trim($custno);
        $bindings['target_kodecabang'] = trim($kodecabang);
        $detailSql = "{$cteSql} SELECT * FROM compiled_stores WHERE custno = :target_custno AND kodecabang = :target_kodecabang LIMIT 1;";

        return DB::selectOne($detailSql, $bindings);
    }

    /**
     * Dapatkan daftar semua toko yang memiliki koordinat kembar/duplikat di cabang yang sama.
     */
    public function getDuplicateStores(?User $user, string $kodecabang, float|string $la, float|string $lg): array
    {
        $filters = ['distributor' => trim($kodecabang)];
        $bindings = [];
        $cteSql = $this->buildMasterQuerySql($user, $filters, $bindings);

        $bindings['target_la'] = (string) round((float) $la, 6);
        $bindings['target_lg'] = (string) round((float) $lg, 6);
        $sql = "{$cteSql} 
            SELECT * FROM compiled_stores 
            WHERE ROUND(la, 6) = ROUND(CAST(:target_la AS numeric), 6) 
              AND ROUND(lg, 6) = ROUND(CAST(:target_lg AS numeric), 6) 
            ORDER BY custno ASC";

        return DB::select($sql, $bindings);
    }

    /**
     * Eksekusi simpan validasi toko (Manual 1-per-1).
     */
    public function saveValidation(string $kodecabang, string $custno, string $status, ?float $la, ?float $lg, ?string $catatan, ?User $user): ValidasiGeotag
    {
        $user = $user ?: auth()->user();

        return ValidasiGeotag::updateOrCreate(
            ['kodecabang' => $kodecabang, 'custno' => $custno],
            [
                'status' => $status,
                'la' => $la,
                'lg' => $lg,
                'catatan' => $catatan,
                'validated_by' => $user?->id,
                'validator_name' => $user?->name,
                'validated_at' => now(),
            ]
        );
    }
}
