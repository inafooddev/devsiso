<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JksMapService
{
    /**
     * Palet warna kontras tinggi untuk identitas unik setiap salesman di peta.
     */
    public const SALESMAN_COLORS = [
        '#2563EB', // Blue 600
        '#059669', // Emerald 600
        '#EA580C', // Orange 600
        '#7C3AED', // Violet 600
        '#DB2777', // Pink 600
        '#0891B2', // Cyan 600
        '#D97706', // Amber 600
        '#4F46E5', // Indigo 600
        '#DC2626', // Red 600
        '#16A34A', // Green 600
        '#9333EA', // Purple 600
        '#0D9488', // Teal 600
        '#CA8A04', // Yellow 600
        '#E11D48', // Rose 600
    ];

    /**
     * Ambil data titik toko JKS beserta koordinat dan metadata untuk MapLibre.
     */
    public function getJksStores(string $distributorCode, string $bulan, ?string $week = null, ?string $day = null, array $salesmanCodes = []): array
    {
        if (empty($distributorCode)) {
            return [];
        }

        $targetBulan = substr($bulan, 0, 7);

        $query = DB::table('jks_salesmans as js')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.customer_code', '=', 'ltpte.uniq_kd')
                     ->on('js.distributor_code', '=', 'ltpte.distributor_code');
            })
            ->leftJoin('salesmans as s', function($join) {
                $join->on('js.salesman_code', '=', 's.salesman_code')
                     ->on('js.distributor_code', '=', 's.distributor_code');
            })
            ->where('js.distributor_code', $distributorCode)
            ->where('js.bulan', 'like', $targetBulan . '%')
            ->select(
                'js.id',
                'js.distributor_code',
                'js.customer_code',
                DB::raw("COALESCE(ltpte.customer_name, js.customer_code) as customer_name"),
                'ltpte.customer_address as alamat',
                'ltpte.kecamatan',
                'ltpte.desa',
                'ltpte.kabupaten',
                'ltpte.latitude',
                'ltpte.longitude',
                'ltpte.channel_outlet',
                'ltpte.classification_outlet',
                'js.salesman_code',
                DB::raw("COALESCE(s.salesman_name, js.salesman_code) as salesman_name"),
                'js.h1', 'js.h2', 'js.h3', 'js.h4', 'js.h5', 'js.h6', 'js.h7',
                'js.w1', 'js.w2', 'js.w3', 'js.w4'
            );

        if (!empty($week) && in_array(strtolower($week), ['w1', 'w2', 'w3', 'w4'])) {
            $w = strtolower($week);
            $query->where("js.{$w}", 'Y');
        }

        if (!empty($day) && in_array(strtolower($day), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7'])) {
            $h = strtolower($day);
            $query->where("js.{$h}", 'Y');
        }

        if (!empty($salesmanCodes)) {
            $query->whereIn('js.salesman_code', $salesmanCodes);
        }

        return $query->get()->toArray();
    }

    /**
     * Ambil data toko Non-JKS (OOL) yang memiliki koordinat di distributor terpilih.
     */
    public function getNonRouteStores(string $distributorCode, string $bulan): array
    {
        if (empty($distributorCode)) {
            return [];
        }

        $targetBulan = substr($bulan, 0, 7);

        $query = DB::table('jks_se_master_toko_ool as mto')
            ->leftJoin('jks_salesmans as js', function($join) use ($targetBulan) {
                $join->on('mto.distributor_code', '=', 'js.distributor_code')
                     ->on('mto.customer_code', '=', 'js.customer_code')
                     ->where('js.bulan', 'like', $targetBulan . '%');
            })
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('mto.customer_code', '=', 'ltpte.uniq_kd')
                     ->on('mto.distributor_code', '=', 'ltpte.distributor_code');
            })
            ->leftJoin('jks_approvals as ja', function($join) use ($targetBulan) {
                $join->on('mto.distributor_code', '=', 'ja.distributor_code')
                     ->where('ja.status', '=', 'PENDING')
                     ->where('ja.action_type', '=', 'TAMBAH_JADWAL')
                     ->where(function($q) use ($targetBulan) {
                         $q->whereRaw("ja.payload->>'bulan' LIKE ?", [$targetBulan . '%'])
                           ->orWhereRaw("ja.payload->>'bulan' IS NULL");
                     })
                     ->where(function($q) {
                         $q->whereRaw("ja.payload->>'customer_code' = mto.customer_code")
                           ->orWhereRaw("(ja.payload::jsonb->'tokos') @> jsonb_build_array(jsonb_build_object('code', mto.customer_code))");
                     });
            })
            ->where('mto.distributor_code', $distributorCode)
            ->whereNull('js.id')
            ->whereNotNull('ltpte.latitude')
            ->whereNotNull('ltpte.longitude')
            ->where('ltpte.latitude', '!=', 0)
            ->where('ltpte.longitude', '!=', 0)
            ->select(
                'mto.id',
                'mto.distributor_code',
                'mto.customer_code',
                'mto.customer_name',
                'mto.customer_eska',
                'mto.alamat',
                'ltpte.latitude',
                'ltpte.longitude',
                'mto.avg_value_net',
                'ja.id as pending_approval_id'
            );

        return $query->get()->toArray();
    }

    /**
     * Ambil koordinat gudang/depot distributor.
     */
    public function getDistributorDepot(string $distributorCode): ?object
    {
        return DB::table('master_distributors')
            ->where('distributor_code', $distributorCode)
            ->select('distributor_code', 'distributor_name', 'latitude', 'longitude')
            ->first();
    }

    /**
     * Ambil daftar salesman distributor yang memiliki jadwal di JKS beserta warna identitas unik dan counter toko.
     * Hanya mengambil salesman yang benar-benar tercatat di tabel jks_salesmans pada periode bulan terkait.
     */
    public function getSalesmanTerritories(string $distributorCode, string $bulan): array
    {
        if (empty($distributorCode)) {
            return [];
        }

        $targetBulan = substr($bulan, 0, 7);

        $salesmen = DB::table('jks_salesmans as js')
            ->leftJoin('salesmans as s', function($join) {
                $join->on('js.salesman_code', '=', 's.salesman_code')
                     ->on('js.distributor_code', '=', 's.distributor_code');
            })
            ->where('js.distributor_code', $distributorCode)
            ->where('js.bulan', 'like', $targetBulan . '%')
            ->select(
                'js.salesman_code',
                DB::raw("COALESCE(s.salesman_name, js.salesman_code) as salesman_name"),
                DB::raw("COUNT(js.id) as total_toko"),
                DB::raw("COUNT(CASE WHEN js.h1 = 'Y' THEN 1 END) as count_h1"),
                DB::raw("COUNT(CASE WHEN js.h2 = 'Y' THEN 1 END) as count_h2"),
                DB::raw("COUNT(CASE WHEN js.h3 = 'Y' THEN 1 END) as count_h3"),
                DB::raw("COUNT(CASE WHEN js.h4 = 'Y' THEN 1 END) as count_h4"),
                DB::raw("COUNT(CASE WHEN js.h5 = 'Y' THEN 1 END) as count_h5"),
                DB::raw("COUNT(CASE WHEN js.h6 = 'Y' THEN 1 END) as count_h6"),
                DB::raw("COUNT(CASE WHEN js.h7 = 'Y' THEN 1 END) as count_h7")
            )
            ->groupBy('js.salesman_code', 's.salesman_name')
            ->orderByRaw("COALESCE(s.salesman_name, js.salesman_code)")
            ->get();

        $result = [];
        $colorIndex = 0;
        $totalColors = count(self::SALESMAN_COLORS);

        foreach ($salesmen as $sm) {
            $color = self::SALESMAN_COLORS[$colorIndex % $totalColors];
            $colorIndex++;

            $result[$sm->salesman_code] = [
                'code' => $sm->salesman_code,
                'name' => $sm->salesman_name,
                'color' => $color,
                'total_toko' => (int) $sm->total_toko,
                'total_stores' => (int) $sm->total_toko,
                'h1' => (int) $sm->count_h1,
                'h2' => (int) $sm->count_h2,
                'h3' => (int) $sm->count_h3,
                'h4' => (int) $sm->count_h4,
                'h5' => (int) $sm->count_h5,
                'h6' => (int) $sm->count_h6,
                'h7' => (int) $sm->count_h7,
            ];
        }

        return $result;
    }

    /**
     * Hitung jarak Haversine antar dua koordinat GPS (dalam kilometer).
     */
    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }
}
