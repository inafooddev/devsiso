<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\RwoQueryBuilder;

class RwoMapController extends Controller
{
    use RwoQueryBuilder;

    public function geojson(Request $request)
    {
        $appliedKuartal = $request->input('kuartal', '');
        $appliedRegion = $request->input('region', '');
        $appliedArea = $request->input('area', '');
        $appliedSupervisor = $request->input('supervisor', '');
        $appliedDistributor = $request->input('distributor', '');
        $search = $request->input('search', '');
        
        $appliedStatusSkb = $request->input('statusSkb', 'Semua');
        $appliedStatusData = $request->input('statusData', 'Semua');
        $appliedStatusReward = $request->input('statusReward', 'Semua');
        $appliedStatusProgress = $request->input('statusProgress', 'Semua');

        $query = $this->getStoreQuery();
        $this->applyAccessScope($query);

        if ($appliedKuartal) {
            $query->where('l.kuartal', $appliedKuartal);
        }
        if ($appliedRegion) {
            $query->where('md.region_code', $appliedRegion);
        }
        if ($appliedArea) {
            $query->where('md.area_code', $appliedArea);
        }
        if ($appliedSupervisor) {
            $query->where('md.supervisor_code', $appliedSupervisor);
        }
        if ($appliedDistributor) {
            $query->where('l.distributor_code', $appliedDistributor);
        }

        if ($search) {
            $q = '%' . strtolower($search) . '%';
            $query->where(function($sub) use ($q) {
                $sub->whereRaw('LOWER(l.customer_name) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(l.customer_code) LIKE ?', [$q]);
            });
        }

        if ($appliedStatusSkb !== 'Semua') {
            if ($appliedStatusSkb === 'Sudah') {
                $query->whereNotNull('skb.customer_code');
            } elseif ($appliedStatusSkb === 'Belum') {
                $query->whereNull('skb.customer_code');
            } elseif ($appliedStatusSkb === 'Approve') {
                $query->whereNotNull('skb.customer_code')->where('skb.is_approved', true);
            } elseif ($appliedStatusSkb === 'Reject') {
                $query->whereNotNull('skb.customer_code')->where(function($q) {
                    $q->where('skb.is_approved', false)->orWhereNull('skb.is_approved');
                });
            }
        }

        if ($appliedStatusData !== 'Semua') {
            $fieldsCheck = [
                'r.no_hp', 'r.nama_pemilik_toko', 'r.nik_ktp', 'r.nama_ktp', 'r.foto_ktp', 
                'r.nama_bank', 'r.no_rekening', 'r.nama_pemilik_norek', 'r.latitude', 'r.longitude',
                'r.foto_toko2', 'r.foto_toko3'
            ];
            if ($appliedStatusData === 'Lengkap') {
                foreach ($fieldsCheck as $f) {
                    $query->whereNotNull($f)->where(DB::raw("TRIM($f)"), '!=', '');
                }
            } else {
                $query->where(function($sub) use ($fieldsCheck) {
                    foreach ($fieldsCheck as $f) {
                        $sub->orWhereNull($f)->orWhere(DB::raw("TRIM($f)"), '=', '');
                    }
                });
            }
        }

        if ($appliedStatusReward !== 'Semua') {
            if ($appliedStatusReward === '2.5%') {
                $query->where('l.total_target', '>=', 90000000);
            } elseif ($appliedStatusReward === '2%') {
                $query->where('l.total_target', '>=', 30000000)->where('l.total_target', '<', 90000000);
            } elseif ($appliedStatusReward === '1.5%') {
                $query->where(function($q) {
                    $q->whereNull('l.total_target')->orWhere('l.total_target', '<', 30000000);
                });
            }
        }

        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)($appliedKuartal ?: $currentQuarter);
        
        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if ($multiplier === 1) {
            $achievementSql = "COALESCE(zv.month_1_value, 0)";
        } elseif ($multiplier === 2) {
            $achievementSql = "(COALESCE(zv.month_1_value, 0) + COALESCE(zv.month_2_value, 0))";
        } else {
            $achievementSql = "COALESCE(zv.total_achievement, 0)";
        }
        
        $proratedTargetSql = "((l.total_target / 3.0) * $multiplier)";
        $progressExpr = "($achievementSql / NULLIF($proratedTargetSql, 0)) * 100";

        if ($appliedStatusProgress !== 'Semua') {
            if ($appliedStatusProgress === '1. HIJAU') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 100");
            } elseif ($appliedStatusProgress === '2. KUNING') {
                $query->whereRaw("COALESCE($progressExpr, 0) >= 80 AND COALESCE($progressExpr, 0) < 100");
            } elseif ($appliedStatusProgress === '3. MERAH') {
                $query->whereRaw("COALESCE($progressExpr, 0) < 80");
            }
        }

        // Add filter to only get valid coordinates
        $query->whereNotNull('r.latitude')
              ->whereNotNull('r.longitude')
              ->whereRaw("TRIM(r.latitude) != ''")
              ->whereRaw("TRIM(r.longitude) != ''");

        // Override select to prevent fetching unused heavy columns (saves memory)
        $query->select([
            'l.customer_code',
            'r.latitude',
            'r.longitude',
            'l.total_target'
        ]);

        $stores = $query->get();

        $features = [];
        foreach ($stores as $row) {
            $lat = (float) $row->latitude;
            $lng = (float) $row->longitude;
            
            // Validate coordinates range
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || ($lat == 0 && $lng == 0)) {
                continue;
            }
            
            $target = $row->total_target ?? 0;
            
            $color = '#64748b'; // default slate (for 1.5% or null)
            if ($target >= 90000000) {
                $color = '#eab308'; // yellow / gold
            } elseif ($target >= 30000000) {
                $color = '#3b82f6'; // blue
            }
            
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$lng, $lat]
                ],
                'properties' => [
                    'code' => $row->customer_code,
                    'color' => $color
                ]
            ];
        }

        // Add max-age cache control if needed, but returning json directly is fine
        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }
}
