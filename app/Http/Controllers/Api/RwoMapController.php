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
        $filters = [
            'kuartal' => $appliedKuartal,
            'region' => $request->input('region', ''),
            'area' => $request->input('area', ''),
            'supervisor' => $request->input('supervisor', ''),
            'distributor' => $request->input('distributor', ''),
            'search' => $request->input('search', ''),
            'statusSkb' => $request->input('statusSkb', 'Semua'),
            'statusData' => $request->input('statusData', 'Semua'),
            'statusReward' => $request->input('statusReward', 'Semua'),
            'statusProgress' => $request->input('statusProgress', 'Semua'),
            'statusTransaksi' => $request->input('statusTransaksi', 'Semua'),
        ];

        $query = $this->getStoreQuery((string)$appliedKuartal, false);
        $this->applyAccessScope($query);
        $this->applyRwoFilters($query, $filters);

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
            'l.total_target',
            'l.jenis_reward'
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
            $jenisReward = $row->jenis_reward ?? '';
            
            $color = '#64748b'; // default slate (for 1.5% or null)
            if ($jenisReward === 'BB') {
                $color = '#10b981'; // emerald green for 1KTN
            } elseif ($target >= 90000000) {
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
